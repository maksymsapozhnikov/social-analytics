<?php
namespace app\controllers;

use app\components\enums\EndpageStatusEnum;
use app\components\enums\SessionStatusEnum;
use app\components\enums\SpecialUrl;
use app\components\fraud\FraudChecker;
use app\components\RespondentIdentity;
use app\components\WebEndUrl;
use app\models\Alias;
use app\models\errors\ITgmMobiError;
use app\models\RespondentSurvey;
use app\models\Survey;
use app\modules\surveybot\models\Response;
use yii\helpers\Json;
use yii\log\Logger;
use yii\web\Controller;
use app\components\fraud\BlockingException;

/**
 * Class SurveyController
 * @package app\controllers
 */
class SurveyController extends Controller
{
    public $layout = 'survey';

    /** @var RespondentIdentity */
    protected $rIdentity;

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        parent::init();

        $this->rIdentity = \Yii::$app->respondentIdentity;

        \Yii::$app->language = $this->rIdentity->language ?: \Yii::$app->language;
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        /** @todo have to think how to avoid this */
        if ($action->id == 'check-phone' || $action->id == 'check-email') {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    /**
     * @param $reason
     * @return \yii\web\Response
     * @throws \Exception
     */
    protected function blockRespondentSession($reason)
    {
        $this->rIdentity->logSession->set(SessionStatusEnum::ST_BLOCKED, $reason);

        return $this->redirect([SpecialUrl::SURVEY_FINISHED]);
    }

    /**
     * Redirection to the survey requested by alias identifier
     * @param $rmsid
     * @return \yii\web\Response
     * @throws \Exception
     */
    public function actionAlias($rmsid)
    {
        $alias = Alias::startByAlias($rmsid);

        if ($alias instanceof ITgmMobiError) {
            return $this->blockRespondentSession($alias->message);
        }

        $this->rIdentity->setSurvey($alias->survey);
        $this->rIdentity->_uri = $alias->getSurveyUrl(\Yii::$app->request->queryString);

        try {
            /** @var FraudChecker $fraudChecker */
            $fraudChecker = \Yii::$app->fraudChecker;
            $fraudChecker->checkRespondent($this->rIdentity, $alias->survey);
        } catch (BlockingException $e) {
            $url = WebEndUrl::getEndPageByIdentity($alias->survey, $this->rIdentity->respondent, EndpageStatusEnum::DSQ);
            $this->rIdentity->logSession->setEndUrl($url);
            $this->blockRespondentSession($e->getMessage());
            $alias->checkAndAddCounter(EndpageStatusEnum::DSQ);
            return $this->redirect($url);
        } catch (\Throwable $e) {
            return $this->blockRespondentSession('Internal error: ' . substr($e->getMessage(), 125));
        }

        $this->rIdentity->logSession->set(SessionStatusEnum::ST_ALIAS);
        $this->rIdentity->logSession->saveParameter('alias_id', $alias->id);

        return $this->redirect($this->rIdentity->_uri);
    }

    /**
     * Survey controller, creates loader and identifies respondent
     * @param string $sur
     * @param string $lang
     * @return string|\yii\web\Response
     * @throws \Exception
     */
    public function actionIndex($sur = null, $lang = null)
    {
        $survey = Survey::getActiveSurvey($sur);

        if (is_null($survey)) {
            return $this->blockRespondentSession('Unknown Survey: "' . $sur . '"');
        }

        $this->rIdentity->setSurvey($survey);

        try {
            /** @var FraudChecker $fraudChecker */
            $fraudChecker = \Yii::$app->fraudChecker;
            $fraudChecker->checkRespondent($this->rIdentity, $survey);
        } catch (BlockingException $e) {
            $url = WebEndUrl::getEndPageByIdentity($survey, $this->rIdentity->respondent, EndpageStatusEnum::DSQ);
            $this->rIdentity->logSession->setEndUrl($url);
            $this->blockRespondentSession($e->getMessage());
            $this->rIdentity->_uri = \Yii::$app->request->getAbsoluteUrl();

            $alias_id = $this->rIdentity->logSession->getParameter('alias_id');
            if ($alias_id && $aliasModel = Alias::findOne($alias_id)) {
                $aliasModel->checkAndAddCounter(EndpageStatusEnum::DSQ);
            }
            return $this->redirect($url);
        } catch (\Throwable $e) {
            return $this->blockRespondentSession('Internal error: ' . substr($e->getMessage(), 125));
        }

        $this->rIdentity->logSession->set(SessionStatusEnum::ST_PRELOADING);

        \Yii::$app->language = $lang ?: \Yii::$app->language;

        return $this->render('survey', [
            'survey' => $survey,
        ]);
    }

    /**
     * Renders js file containing identifying object
     * @param integer $t
     * @return string
     * @throws \Exception
     */
    public function actionMobiApp($t = null)
    {
        $this->layout = false;
        \Yii::$app->response->format = 'javascript';

        if (!$this->rIdentity->isTest && $this->isClientCached()) {
            \Yii::$app->response->setStatusCode(304, 'Not Modified');

            return '';
        }

        $isUnknownRespondentFirstRequest = !$t && !$this->rIdentity->respondent;
        $isTestRespondentFirstRequest = !$t && $this->rIdentity->isTest;
        if ($isUnknownRespondentFirstRequest || $isTestRespondentFirstRequest) {
            return $this->createMobiApp();
        }

        if (!$this->rIdentity->respondent || $this->rIdentity->isTest) {
            if (!$this->rIdentity->createNew()) {
                \Yii::getLogger()->log('New respondent creation error', Logger::LEVEL_ERROR);
                \Yii::getLogger()->log(var_export($this->rIdentity->respondent->getErrors(), true), Logger::LEVEL_ERROR);

                return $this->createMobiApp();
            }
        }

        $this->rIdentity->logSession->set(SessionStatusEnum::ST_IDENTIFIED);

        $fingerprint = $this->rIdentity->getRms('fp');
        if ($fingerprint) {
            $this->rIdentity->logSession->update([
                'fingerprint_id' => $this->rIdentity->getRms('fp'),
            ]);
        }

        return $this->createMobiApp($this->rIdentity->getRms(), substr(md5($this->rIdentity->respondent->rmsid), 0, 6));
    }

    protected function createMobiApp($app = [], $etag = null)
    {
        \Yii::$app->params['etag'] = $etag;

        return $this->render('mobi-app', [
            'info' => base64_encode(Json::encode($app)),
        ]);
    }

    protected function isClientCached()
    {
        $headers = \Yii::$app->request->headers;

        return $headers->get('If-Modified-Since') || $headers->get('If-None-Match');
    }

    /**
     * @param string $rmsid
     * @return string|\yii\web\Response
     * @throws \Exception
     */
    public function actionGo($rmsid = null)
    {
        $this->layout = false;

        $identity = $this->rIdentity;

        $surv = Survey::getActiveSurvey($rmsid);

        if (!$surv || !$identity->respondent) {
            return $this->blockRespondentSession('Unknown Survey or Respondent');
        }

        $alias_id = $this->rIdentity->logSession->getParameter('alias_id');
        $sbotRespondentId = $identity->getUriParam('user_id');
        try {
            if ($sbotRespondentId) {
                $sbotLastResponse = Response::getLastResponse($sbotRespondentId);
                if ($sbotLastResponse) {
                    $identity->respondent->setAttribute('sb_respondent_id', (int)$sbotRespondentId);
                }
            }

            /** @var FraudChecker $fraudChecker */
            $fraudChecker = \Yii::$app->fraudChecker;

            $fraudChecker->checkRespondent($identity, $surv);
            $identity->respondent->seen();

            $respSurvey = RespondentSurvey::startSurvey($surv->id, $alias_id);
            $fraudChecker->checkResponse($respSurvey);
            $respSurvey->save();
        } catch (BlockingException $e) {
            $url = WebEndUrl::getEndPageUrlStartedSurvey($respSurvey->survey, $respSurvey->respondent, EndpageStatusEnum::DSQ);
            $this->rIdentity->logSession->setEndUrl($url);
            $this->blockRespondentSession($e->getMessage());
            if ($alias_id && $aliasModel = Alias::findOne($alias_id)) {
                $aliasModel->checkAndAddCounter(EndpageStatusEnum::DSQ);
            }
            return $this->redirect($url);
        } catch (\Throwable $e) {
            return $this->blockRespondentSession('Internal error: ' . substr($e->getMessage(), 125));
        }

        if ($reason = $respSurvey->cantSurvey()) {
            $url = WebEndUrl::getEndPageUrlStartedSurvey($respSurvey->survey, $respSurvey->respondent, EndpageStatusEnum::DSQ);
            $this->rIdentity->logSession->setEndUrl($url);
            $this->blockRespondentSession($reason);
            if ($alias_id && $aliasModel = Alias::findOne($alias_id)) {
                $aliasModel->checkAndAddCounter(EndpageStatusEnum::DSQ);
            }
            return $this->redirect($url);
        }

        $surveyUrl = $surv->buildRespondentUrl($this->rIdentity);

        $status = $surv->requiresRecruitment($this->rIdentity) ? SessionStatusEnum::ST_RECRUITMENT : SessionStatusEnum::ST_SURVEYGIZMO;
        $this->rIdentity->logSession->setEndUrl($surveyUrl, $status);
        $this->rIdentity->logSession->set($status);

        switch($identity->getUriParam('type')) {
            case 'iframe':
                $this->layout = 'iframe';
                return $this->render('survey-iframe', ['surveyUrl' => $surveyUrl]);
                break;
            default:
                return $this->redirect($surveyUrl);
        }
    }
}