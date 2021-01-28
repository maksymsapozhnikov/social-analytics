<?php
namespace app\models\search;

use app\components\FormatHelper;
use app\components\FormatHelper as Format;
use app\models\enums\SuspiciousStatus;
use app\models\Info;
use app\models\Respondent;
use app\models\RespondentStatus;
use app\models\RespondentSurvey;
use app\models\RespondentSurveyStatus;
use app\models\Survey;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\helpers\Json;

/**
 * Class ResultsSearch
 * @package app\models\search
 */
class ResultsSearch extends RespondentSurvey
{
    const DEFAULT_PAGESIZE = 50;

    public $rmsid = [];
    public $statuses = [];
    public $countries = [];
    public $resp;
    public $project_identifiers = [];

    public $isLoaded;

    public $responseColumns = [];

    public $survey_id;

    /**
     * {@inheritdoc}
     */
    public function formName()
    {
        return 'srch';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['rmsid', 'statuses', 'countries', 'resp', 'project_identifiers'], 'safe'],
        ];
    }

    /**
     * @param string $rmsid
     */
    public function validateRmsid($rmsid)
    {
        $survey = Survey::findOne(['rmsid' => $rmsid]);

        if (!is_null($survey)) {
            $this->survey_id[] = $survey->id;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function load($data, $formName = null)
    {
        $this->isLoaded = parent::load($data, $formName);

        return $this->isLoaded;
    }

    public function search($params)
    {
        $query = RespondentSurvey::find()
            ->addSelect([
                'respondent_survey.*',
                'time_sec' => '(case when finished_at > 0 then finished_at - started_at else null end)',
            ]);

        foreach(['Age', 'Country', 'City', 'Gender', 'Mobile', 'Email'] as $selectedValue) {
            $subQuery = "substring_index(substr(response from position('\"{$selectedValue}\":\"' in response) + length('\"{$selectedValue}\":\"')), '\"', 1)";
            $query->addSelect([
                'respondent_survey.*',
                'response_' . $selectedValue => '(' . $subQuery . ')']);
        }

        if (!$this->validate()) {
            return null;
        }

        $this->survey_id = [];
        $this->rmsid = $this->rmsid ?: [];
        foreach($this->rmsid as $rmsid) {
            $this->validateRmsid($rmsid);
        }

        $this->statuses = $this->statuses ?: [];
        if (in_array(RespondentSurveyStatus::ALL, $this->statuses)) {
            $this->statuses = [];
        }

        $this->statuses = empty($this->statuses) || is_null($this->statuses)
                            ? [RespondentSurveyStatus::FINISHED, RespondentSurveyStatus::SCREENED_OUT, RespondentSurveyStatus::DISQUALIFIED]
                            : $this->statuses;

        $query->andWhere(['IN', 'respondent_survey.status', $this->statuses]);
        $query->andFilterWhere(['IN', 'respondent_survey.survey_id', $this->survey_id]);

        $this->countries = $this->countries ?: [];
        if (!empty($this->countries)) {
            $query->andWhere(['>', 'respondent_survey.jc_hash', 0]);
            $query->andWhere(['>', 'respondent_survey.jpi_hash', 0]);
            $query->andWhere(['IN', 'respondent_survey.jsf_country', $this->countries]);
        }

        $this->project_identifiers = $this->project_identifiers ?: [];
        if (!empty($this->project_identifiers)) {
            $query->andWhere(['>', 'respondent_survey.jpi_hash', 0]);
            $query->andWhere(['IN', 'respondent_survey.jsf_project_id', $this->project_identifiers]);
        }

        if ($this->resp) {
            $subQuery = new Query();
            $subQuery->select('id')
                ->from(Respondent::tableName())
                ->where(['=', 'rmsid', $this->resp]);

            $query->andFilterWhere(['IN', 'respondent_survey.respondent_id', $subQuery]);
        }

        $this->prepareColumns($query);

        $queryTotalCount = clone $query;

        $query->joinWith(['respondent', 'survey']);

        return new ActiveDataProvider([
            'query' => $query,
            'totalCount' => $queryTotalCount->count(),
            'pagination' => [
                'pagesize' => \Yii::$app->request->get('per-page', ResultsSearch::DEFAULT_PAGESIZE),
            ],
            'sort' => [
                'defaultOrder' => [
                    'finished_at' => SORT_DESC,
                    'started_at' => SORT_DESC,
                ],
                'attributes' => [
                    'started_at', 'finished_at', 'status', 'tryings',
                    'respondent' => [
                        'asc' => ['respondent.rmsid' => SORT_ASC],
                        'desc' => ['respondent.rmsid' => SORT_DESC],
                    ],
                    'respondent_status' => [
                        'asc' => ['respondent.status' => SORT_ASC],
                        'desc' => ['respondent.status' => SORT_DESC],
                    ],
                    'survey_rmsid' => [
                        'asc' => ['survey.rmsid' => SORT_ASC],
                        'desc' => ['survey.rmsid' => SORT_DESC],
                    ],
                    'survey_name' => [
                        'asc' => ['survey.name' => SORT_ASC, 'survey.rmsid' => SORT_ASC],
                        'desc' => ['survey.name' => SORT_DESC, 'survey.rmsid' => SORT_DESC],
                    ],
                    'time_sec',
                    'response_Age', 'response_Country', 'response_City', 'response_Gender', 'response_Mobile', 'response_Email',
                    'phone', 'suspicious',
                ]
            ],
        ]);;
    }

    protected function getAnswersKeys(ActiveQuery $query)
    {
        $this->responseColumns = Json::decode(Info::value(Info::QUESTIONNAIRE_QUESTIONS));

        return $this->responseColumns;
    }

    protected function prepareColumns(ActiveQuery $query)
    {
        $this->responseColumns = $this->getAnswersKeys($query);

        $this->responseColumns = array_map(function($value) {
            return [
                'attribute' => 'response__' . $value,
                'label' => $value,
                'value' => function($data) use ($value) {
                    $decoded = Json::decode($data->response);
                    return isset($decoded[$value]) ? FormatHelper::clearResponse($decoded[$value]) : null;
                }
            ];
        }, (array)$this->responseColumns);

        return $this->responseColumns;
    }

    public function exportColumns()
    {
        return array_merge([
            ['class' => 'yii\grid\SerialColumn'],
            [
                'label' => 'RMSID',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data->respondent->rmsid;
                },
            ],
            [
                'label' => 'Blacklist',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data->respondent->status == RespondentStatus::DISQUALIFIED ? 'Yes' : '';
                },
            ],
            [
                'label' => 'Survey RMSID',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data->survey->rmsid;
                },
            ],
            [
                'label' => 'Survey Name',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data->survey->name;
                },
            ],
            [
                'label' => 'Started',
                'format' => 'raw',
                'value' => function ($data) {
                    return Format::toDate($data->started_at, 'd.m.Y H:i:s');
                },
            ],
            [
                'label' => 'Finished',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data->finished_at > 0 ? Format::toDate($data->finished_at, 'd.m.Y H:i:s') : null;
                },
            ],
            [
                'label' => 'Time, sec',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data->finished_at > 0 ? $data->finished_at - $data->started_at : null;
                },
            ],
            [
                'label' => 'TimeSc',
                'format' => 'raw',
                'value' => function (RespondentSurvey $data) {
                    return \app\components\FormatHelper::timingScoreSum($data->timing_score_sum ?: null);
                },
            ],
            [
                'label' => 'avgTime',
                'format' => 'raw',
                'value' => function (RespondentSurvey $data) {
                    return \app\components\FormatHelper::timingScoreAvg($data->timing_score_avg ?: null);
                },
            ],
            [
                'label' => 'TrustSc',
                'format' => 'raw',
                'value' => function (RespondentSurvey $data) {
                    return \app\components\FormatHelper::dirtyScore(100 - $data->dirty_score);
                },
            ],
            [
                'label' => 'Tryings',
                'format' => 'raw',
                'value' => 'tryings',
            ],
            [
                'label' => 'Status',
                'format' => 'raw',
                'value' => function($data) {
                    return RespondentSurveyStatus::getShortTitle($data->status);
                },
            ],
            [
                'label' => 'Suspicious',
                'format' => 'raw',
                'value' => function($data) {
                    return $data->suspicious != SuspiciousStatus::LEGAL ? SuspiciousStatus::getTitle($data->suspicious) : null;
                },
            ],
            [
                'label' => 'Geo:Longitude',
                'format' => 'raw',
                'value' => 'respondent.geo_longitude',
            ],
            [
                'label' => 'Geo:Latitude',
                'format' => 'raw',
                'value' => 'respondent.geo_latitude',
            ],
            [
                'label' => 'Geo:Address',
                'format' => 'raw',
                'value' => 'respondent.geo_address',
            ],
            [
                'label' => 'Dirty Score',
                'format' => 'raw',
                'value' => 'dirty_score',
            ],
            [
                'label' => 'DirtyScore JSON',
                'format' => 'raw',
                'value' => 'dirty_score_json',
            ],
        ], $this->responseColumns);
    }

    public function getExportFilename()
    {
        if (count($this->survey_id) > 1) {
            $middleName = implode('_', $this->survey_id);
        } else if (1 == count($this->survey_id)) {
            $s = (array)$this->survey_id;
            $survey = Survey::findOne($s[0]);
            $middleName = $survey->rmsid . '_' . $survey->name;
        } else {
            $middleName = 'All_Surveys';
        }

        return 'Export_' . $middleName . '_' . date('ymd-his');
    }
}
