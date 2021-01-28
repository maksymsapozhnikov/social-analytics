<?php
namespace app\controllers;

use app\components\AppLogger;
use app\components\enums\Roles;
use app\components\WebEndUrl;
use app\models\Alias;
use app\models\Respondent;
use app\models\RespondentSurvey;
use app\models\RestError;
use app\models\Survey;
use yii;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

class SiteController extends Controller
{
    use UserLanguageTrait;

    // 5 * 365 * 24 * 3600
    const COOKIE_EXPIRE = 15120000;

    /**
     * {@inheritDoc}
     * @throws yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        /** @todo have to think how to avoid this */
        if ($action->id == 'respondent' || $action->id == 'response') {
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => yii\filters\AccessControl::className(),
                'only' => ['logout', 'cleanup', 'test', 'logs'],
                'rules' => [
                    [
                        'actions' => ['logout', 'cleanup', 'test', 'logs'],
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'respondent' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $user = \Yii::$app->user;

        if ($user->can(Roles::ADMIN)) {
            return $this->redirect(['/control']);
        }

        if (!$user->isGuest) {
            return $this->redirect(['/surveys']);
        }

        $this->layout = 'public/index';

        return $this->render('index');
    }

    /**
     * Logs response status and redirects to a landing page
     * @param string $status
     * @return Response
     */
    public function actionStatus($status)
    {
        list($respondentRmsid, $surveyRmsid) = $this->getRmsids();
        $survey = Survey::findOne(['rmsid' => $surveyRmsid]);
        $respondent = Respondent::findOne(['rmsid' => $respondentRmsid]);
        $respSurvey = RespondentSurvey::findByRmsids($respondentRmsid, $surveyRmsid);

        $aliasModel = Alias::findOne($respSurvey->alias_id);
        if($aliasModel){
            $aliasModel->checkAndAddCounter($status);
        }

        return $this->redirect(WebEndUrl::getEndPageUrlStartedSurvey($survey, $respondent, $status));
    }

    /**
     * @return array [0 => respondentRmsid, 1 => surveyRmsid]
     */
    protected function getRmsids()
    {
        $request = \Yii::$app->request;
        $mixedRmsid = $request->get('id');

        if ($mixedRmsid) {
            $respondentRmsid = substr($mixedRmsid, 0, Respondent::RMSID_LENGTH);
            $surveyRmsid = substr($mixedRmsid, Respondent::RMSID_LENGTH, Survey::RMSID_LENGTH);
        } else {
            $respondentRmsid = $request->get('sguid');
            $surveyRmsid = $request->get('rmsid');
        }

        return [
            $respondentRmsid,
            $surveyRmsid,
        ];
    }

    public function actionResponse()
    {
        $this->layout = false;
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $request = \Yii::$app->request;

        if (!$request->isPost) {
            AppLogger::error('Bad Request: should be POST');
            return (new RestError(['code' => 400, 'message' => 'Bad request']))->asArray();
        }

        $survey_rmsid = $request->post('survey_rmsid');
        preg_match('/([\w\d]{7})/', $survey_rmsid, $matches);
        $survey_rmsid = ArrayHelper::getValue($matches, 1, $survey_rmsid);

        $survey = Survey::findOne(['rmsid' => $survey_rmsid]);
        $respondent = Respondent::findOne(['rmsid' => $request->post('rmsid')]);

        if (is_null($survey) || is_null($respondent)) {
            $survey_rmsid = $request->post('survey_rmsid');
            $respon_rmsid = $request->post('rmsid');
            $message = "Response Processing Error: Entity not found. Survey: {$survey_rmsid}, Respondent: $respon_rmsid";

            AppLogger::error($message);

            return (new RestError(['code' => 404, 'message' => 'Entity not found']))->asArray();
        }

        $respondent_survey = RespondentSurvey::getStartedSurvey($survey, $respondent);

        try {
            $respondent_survey->saveResponse($request->post());
            $respondent->seen();
        } catch (\Exception $e) {
            $survey_rmsid = $request->post('survey_rmsid');
            $respon_rmsid = $request->post('rmsid');
            $message = "Response Processing Error: Savings not found. Survey: {$survey_rmsid}, Respondent: $respon_rmsid";

            AppLogger::error($message);
            AppLogger::error(var_export($e->getMessage(), true));

            return (new RestError(['code' => $e->getCode() >= 400 ? $e->getCode() : 500, 'message' => $e->getMessage()]))->asArray();
        }

        /** @todo fix the class name if thing going this way */
        return (new RestError([
            'code' => 200,
            'message' => 'Ok',
            'suspicious' => $respondent_survey->isSuspicious(),
        ]))->asArray();
    }
}
