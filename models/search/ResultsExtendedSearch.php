<?php
namespace app\models\search;

use app\components\AppHelper;
use app\models\Respondent;
use app\models\RespondentSurvey;
use app\models\RespondentSurveyStatus;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Query;

class ResultsExtendedSearch extends ResultsSearch
{
    const TYPE = 'ext';

    public $type = self::TYPE;

    public $filters = [];

    public $suspicious;

    public $resp;

    public $start_date;
    public $end_date;

    public function formName()
    {
        return 'res';
    }

    public function rules()
    {
        return [
            [['rmsid', 'statuses', 'filters', 'resp', 'start_date', 'end_date', 'suspicious'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [

            'rmsid' => 'Survey',
        ];
    }

    /**
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = RespondentSurvey::find()->joinWith(['respondent', 'survey']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pagesize' => \Yii::$app->request->get('per-page', ResultsSearch::DEFAULT_PAGESIZE),
            ],
            'sort' => [
                'attributes' => [
                    'started_at', 'finished_at', 'status', 'tryings',
                    'respondent' => [
                        'asc' => ['respondent.rmsid' => SORT_ASC],
                        'desc' => ['respondent.rmsid' => SORT_DESC],
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
                ]
            ],
        ]);

        $query->addSelect(['respondent_survey.*', 'time_sec' => '(case when finished_at > 0 then finished_at - started_at else null end)']);

        foreach(['Age', 'Country', 'City', 'Gender', 'Mobile', 'Email'] as $selectedValue) {
            $subQuery = "substring_index(substr(response from position('\"{$selectedValue}\":\"' in response) + length('\"{$selectedValue}\":\"')), '\"', 1)";
            $query->addSelect([
                'respondent_survey.*',
                'response_' . $selectedValue => '(' . $subQuery . ')'
            ]);
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

        $query->andFilterWhere(['IN', 'respondent_survey.suspicious', $this->suspicious ?: null]);

        $orClause = ['OR'];
        $this->countries = $this->countries ?: [];
        foreach ($this->countries as $country) {
            $orClause[] = ['LIKE', 'respondent_survey.response', '"Country":"' . $country . '"'];
        }
        $query->andFilterWhere($orClause);

        $this->addDateFilters($query);

        if ($this->resp) {
            $subQuery = new Query();
            $subQuery->select('id')
                ->from(Respondent::tableName())
                ->where(['=', 'rmsid', $this->resp]);

            $query->andFilterWhere(['IN', 'respondent_survey.respondent_id', $subQuery]);
        }

        foreach($this->filters as $key => $filter) {
            $filter = array_filter($filter ?: [], function($val) { return $val > "";});
            if (!empty($filter)) {
                $orClause = ['OR'];
                foreach($filter as $k => $v) {
                    $v = str_replace('\'', '\'\'', $v);
                    $orClause[] = [
                        '>',
                        "position('\"{$key}\":\"$v\"' in response)",
                        0
                    ];
                }
                $query->andFilterWhere($orClause);
            }
        }

        $this->prepareColumns($query);

        return $dataProvider;
    }

    protected function addDateFilters(ActiveQuery $query)
    {
        if (!$this->isLoaded) {
            $this->start_date = $this->start_date ?: date('d.m.Y', strtotime('today'));
            $this->end_date = $this->end_date ?: date('d.m.Y', strtotime('today'));
        }

        if ($this->start_date) {
            $query->andFilterWhere(['>=', 'started_at', AppHelper::timeUtc(strtotime($this->start_date . '00:00:00'))]);
        }

        if ($this->end_date) {
            $query->andFilterWhere(['<=', 'started_at', AppHelper::timeUtc(strtotime($this->end_date . '23:59:59'))]);
        }
    }
}
