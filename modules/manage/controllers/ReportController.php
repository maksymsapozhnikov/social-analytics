<?php
namespace app\modules\manage\controllers;

use app\models\Info;
use app\models\RespondentSurvey;
use app\modules\manage\models\reports\CostReport;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\MethodNotAllowedHttpException;

/**
 * Class ReportController
 * @package app\modules\manage\controllers
 */
class ReportController extends BaseManageController
{
    public $enableCsrfValidation = false;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        if ($this->action->id === 'cost-adjust') {
            unset($behaviors['access']);
        }

        return $behaviors;
    }

    /**
     * @return string
     */
    public function actionCost()
    {
        $req = \Yii::$app->request;

        $report = new CostReport([
            'country' => $req->get('country'),
            'bd' => $req->get('bd'),
            'ed' => $req->get('ed'),
            'project' => $req->get('project'),
        ]);

        $c = $report->countries();
        $p = $report->projects();

        return $this->render('cost', [
            'countries' => array_combine($c, $c),
            'projects' => array_combine($p, $p),
            'r' => $report,
            'report' => $report->build(),
        ]);
    }

    /**
     * @throws
     */
    public function actionCostAdjust()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!\Yii::$app->request->isPost) {
            throw new MethodNotAllowedHttpException();
        }

        $projectId = \Yii::$app->request->post('project_id');
        $country = \Yii::$app->request->post('country');
        $value = floatval(\Yii::$app->request->post('value'));

        $costAdjustments = Json::decode(Info::value(Info::REPORT_COST_ADJUSTMENTS));
        ArrayHelper::setValue($costAdjustments, "{$projectId}.{$country}", $value);
        Info::value(Info::REPORT_COST_ADJUSTMENTS, Json::encode($costAdjustments));
    }

    /**
     * @throws
     */
    public function actionCostRename()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!\Yii::$app->request->isPost) {
            throw new MethodNotAllowedHttpException();
        }

        $request = \Yii::$app->request;

        RespondentSurvey::updateAll([
            'jsf_project_id' => trim($request->post('project-id-new')),
            'jpi_hash' => crc32(trim($request->post('project-id-new'))),
            'jsf_country' => trim($request->post('country-new')),
            'jc_hash' => crc32(trim($request->post('country-new'))),
        ],
            'jpi_hash = :old_project_id_hash and jc_hash = :old_country_hash and jsf_project_id = :old_project_id and jsf_country = :old_country',
        [
            ':old_project_id' => $request->post('project-id-old'),
            ':old_project_id_hash' => crc32($request->post('project-id-old')),
            ':old_country' => $request->post('country-old'),
            ':old_country_hash' => crc32($request->post('country-old')),
        ]);
    }
}