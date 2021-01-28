<?php
namespace app\modules\manage\models\search;

use app\models\Alias;
use app\models\Survey;
use app\models\SurveyStatus;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * Class AliasSearch
 * @package app\modules\manage\models\search
 */
class AliasSearch extends Alias
{
    /** @var array */
    public $cn;

    /** @var array */
    public $par;

    /** @var array */
    public $st;

    /** @var string */
    public $srh;

    /** @var string */
    public $survid;

    /**
     * @param array $data
     * @return ActiveDataProvider
     */
    public function search($data)
    {
        $this->load($data);

        $query = Alias::find()->joinWith('survey');

        $query->andFilterWhere(['IN', Alias::tableName() . '.`status`', $this->st]);

        $this->addCountriesFilter($query);
        $this->addParametersFilter($query);

        $this->addSearchFilter($query);
        $this->addSearchFilterBySurveyId($query);

        $dataProvider = new ArrayDataProvider(['allModels' => $query->all()]);
        $dataProvider->setSort($this->getSorting());

        return $dataProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function load($data, $formName = null)
    {
        $this->cn = array_filter(ArrayHelper::getValue($data, 'cn', []));

        $this->st = array_filter(ArrayHelper::getValue($data, 'st', []));
        $this->st = empty($this->st) ? [SurveyStatus::ACTIVE] : $this->st;

        $this->srh = ArrayHelper::getValue($data, 'srh', '');
        $this->par = array_filter(explode('&', ArrayHelper::getValue($data, 'par', '')));
        $this->survid = trim(ArrayHelper::getValue($data, 'survid', ''));
    }

    /**
     * @param Query $query
     */
    protected function addSearchFilter(Query $query)
    {
        $srhConditional = ['AND'];

        $items = array_filter(array_unique(explode(' ', $this->srh)), function ($value) {
            return $value !== '';
        });

        foreach ($items as $item) {
            $srhConditional[] = [
                'OR',
                ['like', 'survey_alias.note', $item],
                ['like', 'survey.name', $item],
            ];
        }

        $query->andWhere($srhConditional);
    }

    /**
     * @param Query $query
     */
    protected function addSearchFilterBySurveyId(Query $query)
    {
        if ($this->survid != '') {
            $srhConditional = ['like', 'survey_alias.rmsid', $this->survid];
            $query->andWhere($srhConditional);
        }
    }

    /**
     * @param Query $query
     */
    protected function addCountriesFilter(Query $query)
    {
        if (!empty($this->cn)) {
            $subQuery = new Query();
            $subQuery->select('id')
                ->from(Survey::tableName())
                ->where(['IN', 'country', $this->cn]);
            $query->andFilterWhere(['IN', 'survey_id', $subQuery]);
        }
    }

    /**
     * @param Query $query
     */
    protected function addParametersFilter(Query $query)
    {
        $compClause = ['OR'];

        if (!empty($this->par)) {
            $parClause = ['AND'];

            foreach ($this->par as $item) {
                $parClause[] = ['LIKE', 'params', $item];
            }

            $compClause[] = $parClause;
        }

        $query->andWhere($compClause);
    }

    /**
     * @return array
     */
    protected function getSorting()
    {
        return [
            'defaultOrder' => ['id' => SORT_DESC],
            'attributes' => [
                'id' => [
                    'desc' => ['is_sticked' => SORT_DESC, 'id' => SORT_DESC],
                ],
                'rmsid' => [
                    'asc' => ['is_sticked' => SORT_DESC, 'rmsid' => SORT_ASC],
                    'desc' => ['is_sticked' => SORT_DESC, 'rmsid' => SORT_DESC],
                ],
                'used' => [
                    'asc' => ['is_sticked' => SORT_DESC, 'used' => SORT_ASC],
                    'desc' => ['is_sticked' => SORT_DESC, 'used' => SORT_DESC],
                ],
                'cnt_finished' => [
                    'asc' => ['is_sticked' => SORT_DESC, 'cnt_finished' => SORT_ASC],
                    'desc' => ['is_sticked' => SORT_DESC, 'cnt_finished' => SORT_DESC],
                ],
                'status' => [
                    'asc' => ['is_sticked' => SORT_DESC, 'status' => SORT_ASC],
                    'desc' => ['is_sticked' => SORT_DESC, 'status' => SORT_DESC],
                ],
                'note' => [
                    'asc' => ['is_sticked' => SORT_DESC, 'note' => SORT_ASC],
                    'desc' => ['is_sticked' => SORT_DESC, 'note' => SORT_DESC],
                ],
                'survey.rmsid' => [
                    'asc' => ['is_sticked' => SORT_DESC, 'survey.rmsid' => SORT_ASC],
                    'desc' => ['is_sticked' => SORT_DESC, 'survey.rmsid' => SORT_DESC],
                ],
                'survey.name' => [
                    'asc' => ['is_sticked' => SORT_DESC, 'survey.name' => SORT_ASC],
                    'desc' => ['is_sticked' => SORT_DESC, 'survey.name' => SORT_DESC],
                ],
                'survey.country' => [
                    'asc' => ['is_sticked' => SORT_DESC, 'survey.country' => SORT_ASC],
                    'desc' => ['is_sticked' => SORT_DESC, 'survey.country' => SORT_DESC],
                ],
                'bid' => [
                    'asc' => ['is_sticked' => SORT_DESC, 'bidParams' => SORT_ASC],
                    'desc' => ['is_sticked' => SORT_DESC, 'bidParams' => SORT_DESC],
                ],
            ],
        ];
    }
}