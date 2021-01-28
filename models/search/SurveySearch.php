<?php
namespace app\models\search;

use app\components\FormatHelper;
use app\models\Survey;
use app\models\SurveyStatus;
use yii\data\ActiveDataProvider;
use yii\grid\SerialColumn;

/**
 * Class SurveySearch
 * @package app\models\search
 */
class SurveySearch extends Survey
{
    public $order;

    public $countActive;
    public $countScreenedOut;
    public $countDisqualified;
    public $countFinished;
    public $countAll;

    public $fieldTrustScore;
    public $fieldTimeScore;
    public $fieldAvgTimeScore;

    public $bid_summa;

    public $cid;

    /**
     * @return array
     */
    public static function columns()
    {
        return [
            ['class' => SerialColumn::class],
            'id', 'rmsid', 'name',
            [
                'label' => 'Campaign Name',
                'value' => 'campaign.name',
            ],
            [
                'label' => 'Created',
                'value' => function(Survey $data) {
                    return FormatHelper::toDate($data->dt_created);
                },
            ],
            [
                'label' => 'Currency',
                'value' => 'topup_currency'
            ],
            [
                'label' => 'Incentive',
                'value' => 'topup_value'
            ],
            'country',
            [
                'label' => 'Status',
                'value' => function($data) {
                    return SurveyStatus::getTitle($data->status);
                },
            ],
            'url', 'sample', 'countActive', 'countScreenedOut',
            'countDisqualified', 'countFinished', 'countAll',
            [
                'label' => 'TrustSc',
                'value' => function(SurveySearch $data) {
                    return FormatHelper::dirtyScore(100 - $data->fieldTrustScore);
                },
            ],
            [
                'label' => 'TimeSc',
                'value' => function(SurveySearch $data) {
                    return FormatHelper::timingScoreSum($data->fieldTimeScore);
                },
            ],
            [
                'label' => 'avgTime',
                'value' => function(SurveySearch $data) {
                    return FormatHelper::timingScoreAvg($data->fieldAvgTimeScore);
                },
            ],
            'bid_summa',
            [
                'label' => 'Spent',
                'value' => function(Survey $data) {
                    return $data->topup_spent > 0 ? $data->topup_spent : null;
                }
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['rmsid', 'country', 'status', 'cid',], 'safe'],
        ];
    }

    /**
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = SurveySearch::find();

        if (!$this->load($params)) {
            $this->status = [SurveyStatus::ACTIVE];
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'dt_created' => SORT_DESC,
                    'id' => SORT_DESC,
                ],
                'attributes' => [
                    'id', 'rmsid', 'name', 'country', 'status', 'dt_created', 'topup_spent',
                    'campaign.name' => [
                        'asc' => ['campaign.name' => SORT_ASC, 'dt_created' => SORT_DESC, 'id' => SORT_DESC,],
                        'desc' => ['campaign.name' => SORT_DESC, 'dt_created' => SORT_DESC, 'id' => SORT_DESC,],
                    ],
                    'countFinished' => [
                        'asc' => ['stat_count_fin' => SORT_ASC],
                        'desc' => ['stat_count_fin' => SORT_DESC],
                    ],
                    'countAll' => [
                        'asc' => ['stat_count_all' => SORT_ASC],
                        'desc' => ['stat_count_all' => SORT_DESC],
                    ],
                    'countActive' => [
                        'asc' => ['stat_count_act' => SORT_ASC],
                        'desc' => ['stat_count_act' => SORT_DESC],
                    ],
                    'countDisqualified' => [
                        'asc' => ['stat_count_dsq' => SORT_ASC],
                        'desc' => ['stat_count_dsq' => SORT_DESC],
                    ],
                    'countScreenedOut' => [
                        'asc' => ['stat_count_scr' => SORT_ASC],
                        'desc' => ['stat_count_scr' => SORT_DESC],
                    ],
                    'bid_summa' => [
                        'asc' => ['stat_bid_summa' => SORT_ASC],
                        'desc' => ['stat_bid_summa' => SORT_DESC],
                    ],
                    'incentive' => [
                        'asc' => ['topup_currency' => SORT_ASC, 'topup_value' => SORT_ASC],
                        'desc' => ['topup_currency' => SORT_DESC, 'topup_value' => SORT_DESC],
                    ],
                    'fieldTrustScore' => [
                        'asc' => ['stat_dirty_score' => SORT_ASC],
                        'desc' => ['stat_dirty_score' => SORT_DESC],
                    ],
                    'fieldTimeScore' => [
                        'asc' => ['stat_time_score' => SORT_ASC],
                        'desc' => ['stat_time_score' => SORT_DESC],
                    ],
                    'fieldAvgTimeScore' => [
                        'asc' => ['stat_avg_time_score' => SORT_ASC],
                        'desc' => ['stat_avg_time_score' => SORT_DESC],
                    ],
                ]
            ],
            'pagination' => [
                'pagesize' => 25,
            ],
        ]);

        $query->select([
            Survey::tableName() . ".*",
        ]);

        $query->joinWith(['campaign']);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $surveyTab = Survey::tableName();
        $query->andFilterWhere(['IN', "{$surveyTab}.country", $this->country]);
        $query->andFilterWhere(['IN', 'status', $this->status]);

        $query->andFilterWhere(['IN', 'rmsid', $this->rmsid]);
        $query->andFilterWhere(['IN', 'campaign_id', $this->cid]);

        $query->andFilterWhere(["{$surveyTab}.id" => $this->id]);

        return $dataProvider;
    }
}
