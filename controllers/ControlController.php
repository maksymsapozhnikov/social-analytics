<?php
namespace app\controllers;

use app\components\FormatHelper;
use app\models\account\Account;
use app\models\Alias;
use app\models\IpBlacklist;
use app\models\Respondent;
use app\models\RespondentStatus;
use app\models\RespondentSurvey;
use app\models\search\RespondentLogSearch;
use app\models\search\ResultsExtendedSearch;
use app\models\search\ResultsSearch;
use app\models\search\SurveySearch;
use app\models\Survey;
use app\models\SurveyStatus;
use yii;
use yii\web\Controller;

class ControlController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $actions = ['index', 'survey-list', 'survey-create', 'survey-update', 'result-list', 'logs'];
        return [
            'access' => [
                'class' => yii\filters\AccessControl::className(),
                'only' => $actions,
                'denyCallback' => function($a, $b) {
                    return \Yii::$app->response->redirect(['/login']);
                },
                'rules' => [
                    [
                        'actions' => $actions,
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionSurveyList()
    {
        $search = new SurveySearch();
        $params = Yii::$app->request->queryParams;
        $dataProvider = $search->search($params);

        return $this->render('survey-list', [
            'searchModel' => $search,
            'dataprovider' => $dataProvider,
        ]);
    }

    public function actionSurveyCreate($id = null)
    {
        if (\Yii::$app->request->isAjax) {
            \Yii::$app->response->format = yii\web\Response::FORMAT_JSON;
        }

        $survey = new Survey();

        if ($id) {
            $copied = Survey::findOne($id);
            if (!is_null($copied)) {
                $survey->setAttributes($copied->attributes);
            }
        }

        if ($survey->load(Yii::$app->request->post())) {
            if ($survey->save()) {
                return $survey;
            }

            throw new yii\web\BadRequestHttpException();
        }

        return $this->render('survey-create', [
            'survey' => $survey,
        ]);
    }

    public function actionSurveyClean($id)
    {
        $url = \Yii::$app->request->referrer ?: yii\helpers\Url::to(['/control/survey-list']);
        $survey = Survey::findOne($id);

        if (is_null($survey)) {
            return $this->redirect($url);
        }

        Yii::$app->db->createCommand('DELETE FROM respondent_survey WHERE survey_id = :id')
            ->bindValues(['id' => $survey->id])
            ->execute();

        return $this->redirect($url);
    }

    public function actionSurveyTrash($id)
    {
        $url = \Yii::$app->request->referrer ?: yii\helpers\Url::to(['/control/survey-list']);
        $survey = Survey::findOne($id);

        if (is_null($survey)) {
            return $this->redirect($url);
        }

        $survey->toTrash();

        return $this->redirect($url);
    }

    /**
     * @param $id
     * @param $status
     * @return yii\web\Response
     */
    public function actionSurveySetStatus($id, $status)
    {
        $url = \Yii::$app->request->referrer ?: yii\helpers\Url::to(['/control/survey-list']);
        $survey = Survey::findOne($id);

        if (is_null($survey)) {
            return $this->redirect($url);
        }

        if ($status == SurveyStatus::INACTIVE && $survey->status === SurveyStatus::ACTIVE) {
            $survey->status = SurveyStatus::INACTIVE;
        } elseif ($status == SurveyStatus::ACTIVE && $survey->status === SurveyStatus::INACTIVE) {
            $survey->status = SurveyStatus::ACTIVE;
        }

        $survey->save();

        return $this->redirect($url);
    }

    public function actionSurveyRestore($id)
    {
        $url = \Yii::$app->request->referrer ?: yii\helpers\Url::to(['/control/survey-list']);
        $survey = Survey::findOne($id);

        if (is_null($survey)) {
            return $this->redirect($url);
        }

        $survey->restore();

        return $this->redirect($url);
    }

    public function actionSurveyRemove($id)
    {
        $url = \Yii::$app->request->referrer ?: yii\helpers\Url::to(['/control/survey-list']);
        $survey = Survey::findOne($id);

        if (is_null($survey)) {
            return $this->redirect($url);
        }

        $survey->delete();

        return $this->redirect($url);
    }

    /**
     * JSON
     */
    public function actionSurveyUpdate($id)
    {
        if (\Yii::$app->request->isAjax) {
            \Yii::$app->response->format = yii\web\Response::FORMAT_JSON;
        }

        $survey = Survey::findOne($id);
        if (is_null($survey)) {
            throw new yii\web\NotFoundHttpException('Survey does not exist.');
        }

        if ($survey->load(Yii::$app->request->post())) {
            if (!$survey->save()) {
                throw new yii\web\BadRequestHttpException();
            }
        }

        if (\Yii::$app->request->isAjax) {
            return $survey;
        }

        return $this->render('survey-update', [
            'survey' => $survey,
        ]);
    }

    public function actionResultList()
    {
        set_time_limit(0);
        $params = Yii::$app->request->queryParams;

        $search = new ResultsSearch();
        $extendedSearch = new ResultsExtendedSearch();

        $search->load($params);
        $extendedSearch->load($params);

        $searched = $extendedSearch->isLoaded ? $extendedSearch : $search;

        return $this->render('result-list', [
            'searched' => $searched,
            'searchModel' => $search,
            'extendedSearchModel' => $extendedSearch,
            'dataProvider' => $searched->search($params),
        ]);
    }

    public function actionResultDelete($id)
    {
        $result = RespondentSurvey::findOne($id);

        if ($result) {
            $result->delete();
        }

        $url = \Yii::$app->request->referrer ?: yii\helpers\Url::to(['/control/result-list']);

        $this->redirect($url);
    }

    public function actionLogs()
    {
        $search = new RespondentLogSearch();

        $params = Yii::$app->request->queryParams;

        return $this->render('logs', [
            'searchModel' => $search,
            'dataProvider' => $search->search($params),
        ]);
    }

    public function actionIpBlacklist()
    {
        $dataProvider = new yii\data\ActiveDataProvider([
            'query' => IpBlacklist::find()->orderBy(['since_dt' => SORT_DESC]),
            'pagination' => [
                'pagesize' => 100,
            ]
        ]);

        return $this->render('ip-blacklist', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionIpBlacklistAdd($ip)
    {
        \Yii::$app->response->format = yii\web\Response::FORMAT_JSON;

        $blocked = IpBlacklist::findByIp($ip);
        if (!is_null($blocked)) {
            return ['message' => 'IP <b>' . $ip . '</b> already blocked since <b>' . FormatHelper::toDate($blocked->since_dt, 'd.m.Y H:i:s') . '</b>.'];
        }

        $blacklist = new IpBlacklist(['ip_v4' => $ip]);
        if (!$blacklist->save()) {
            $errors = $blacklist->getFirstErrors();

            return ['message' => 'Error: ' . array_pop($errors)];
        }

        return ['message' => "IP {$ip} has been blocked successfully."];
    }

    public function actionIpBlacklistDelete($id)
    {
        $blacklist = IpBlacklist::findOne($id);
        $blacklist->delete();

        $backUrl = \Yii::$app->request->referrer ?: yii\helpers\Url::to(['/control/ip-blacklist']);

        return $this->redirect($backUrl);
    }

    public function actionRespondentBlacklistAdd($rmsid)
    {
        \Yii::$app->response->format = yii\web\Response::FORMAT_JSON;

        $respondent = Respondent::findOne(['rmsid' => $rmsid]);

        if (!$respondent) {
            return ['message' => 'Unknown respondent: <b>' . $rmsid . '</b>.'];
        }

        if ($respondent->status == RespondentStatus::DISQUALIFIED) {
            return ['message' => 'Respondent <b>' . $rmsid . '</b> is disqualified already.'];
        }

        if (!$respondent->block(null, null)) {
            $errors = $respondent->getFirstErrors();

            return ['message' => '<b class="text-danger">Error.</b><br/>' . array_pop($errors) ];
        }

        return ['message' => 'Respondent <b>' . $rmsid . '</b> disqualified successfully.'];
    }

    public function actionRespondentBlacklistDelete($id)
    {
        $backUrl = \Yii::$app->request->referrer ?: yii\helpers\Url::to(['/control/ip-blacklist']);

        $respondent = Respondent::findOne($id);
        if (!is_null($respondent)) {
            $respondent->status = RespondentStatus::ACTIVE;
            $respondent->dt_blacklist = null;
            $respondent->survey_blacklist = null;
            $respondent->save();
        }

        $this->redirect($backUrl);
    }

    public function actionRespondentBlacklist()
    {
        $dataProvider = new yii\data\ActiveDataProvider([
            'query' => Respondent::find()
                ->where(['status' => RespondentStatus::DISQUALIFIED]),
        ]);

        return $this->render('respondent-blacklist', [
            'dataProvider' => $dataProvider,
        ]);
    }
}
