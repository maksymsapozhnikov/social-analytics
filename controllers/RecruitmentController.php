<?php
namespace app\controllers;

use app\components\enums\EndpageStatusEnum;
use app\components\enums\ProfileProperty;
use app\components\enums\SessionStatusEnum;
use app\components\enums\SpecialUrl;
use app\components\exceptions\DisqualifiedException;
use app\components\recruitment\ProfileQuestion;
use app\components\RespondentIdentity;
use app\components\WebEndUrl;
use app\models\Alias;
use app\models\enums\PanelRegisterType;
use app\models\enums\SuspiciousStatus;
use app\models\RecruitmentProfile;
use app\models\RespondentSurvey;
use app\models\Survey;
use yii\helpers\Url;
use yii\web\Controller;

/**
 * Class RecruitmentController
 * @package app\controllers
 */
class RecruitmentController extends Controller
{
    /** @var RespondentIdentity */
    protected $rIdentity;

    /** @var string */
    public $layout = 'recruitment';

    /** @var Survey */
    public $survey;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        $this->rIdentity = \Yii::$app->respondentIdentity;

        \Yii::$app->language = $this->rIdentity->language ?: \Yii::$app->language;
    }

    /**
     * Renders layout.
     */
    public function actionIndex($rmsid = null)
    {
        /** @var RecruitmentProfile $profile */
        $profile = $this->rIdentity->respondent->recruitmentProfile;
        $profile->content[ProfileProperty::SOURCE] = $this->rIdentity->getUriParam('s');
        $survey = $this->loadSurvey($rmsid);

        if (!$profile || !$survey || $this->rIdentity->respondent->isBlocked()) {
            return $this->redirect([SpecialUrl::SURVEY_FINISHED]);
        }

        $this->survey = $survey;
        $question = $profile->getNextQuestion();

        if (\Yii::$app->request->isPost) {
            $question->scenario = ProfileQuestion::SCENARIO_RESPONSE;
            $question->load(\Yii::$app->request->post());

            try {
                $profile->setRecruitmentResponse($question);
            } catch (DisqualifiedException $e) {
                $this->rIdentity->respondent->addToBlacklist(SuspiciousStatus::RECRUITMENT_DISQ, $survey->id);
                $respSurvey = RespondentSurvey::findByRmsids($this->rIdentity->respondent->rmsid, $survey->rmsid);
                $url = WebEndUrl::getEndPageUrlStartedSurvey($respSurvey->survey, $respSurvey->respondent, EndpageStatusEnum::DSQ);

                $aliasModel = Alias::findOne($respSurvey->alias_id);
                if($aliasModel){
                    $aliasModel->countDsq();
                }
                $this->rIdentity->logSession->setEndUrl($url);
                $this->rIdentity->logSession->set(SessionStatusEnum::ST_BLOCKED, 'Recruitment disqualification');
                return $this->redirect($url);
            }

            if ($question->isLoaded) {
                return $this->redirect(['/rcs/' . $survey->rmsid]);
            }
        }

        if (!$survey->requiresRecruitment($this->rIdentity)) {
            if ($survey->panel_register_type === PanelRegisterType::NONE) {
                $url = $survey->buildRespondentUrl($this->rIdentity);

                $this->rIdentity->logSession->setEndUrl($url);
                $this->rIdentity->logSession->set(SessionStatusEnum::ST_SURVEYGIZMO);

                return $this->redirect($url);
            }

            return $this->redirect(['/portal']);
        }

        return $this->render('index', [
            'question' => $question,
            'survey' => $survey,
        ]);
    }

    /**
     * @param string $rmsid
     * @return Survey|null
     */
    protected function loadSurvey($rmsid)
    {
        return Survey::findOne(['rmsid' => $rmsid]);
    }
}