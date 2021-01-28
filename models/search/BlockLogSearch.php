<?php
namespace app\models\search;

use app\components\AppHelper;
use app\models\BlockLog;
use yii\data\ActiveDataProvider;

class BlockLogSearch extends BlockLog
{
    public $bdt;
    public $edt;

    public $surveys;

    public function rules()
    {
        return [
            [['bdt', 'edt', 'surveys'], 'safe'],
        ];
    }

    /**
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = BlockLog::find();

        $this->load($params);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'dt' => SORT_DESC,
                    'id' => SORT_DESC,
                ],
            ],
            'pagination' => [
                'pagesize' => 25,
            ],
        ]);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $this->bdt = $this->bdt ?: date('d.m.Y', strtotime('today'));
        $this->edt = $this->edt ?: date('d.m.Y', strtotime('today'));

        $query->andFilterWhere(['>=', 'dt', AppHelper::timeUtc(strtotime($this->bdt . '00:00:00'))]);
        $query->andFilterWhere(['<=', 'dt', AppHelper::timeUtc(strtotime($this->edt . '23:59:59'))]);
        $query->andFilterWhere(['IN', 'survey_id', $this->surveys]);

        return $dataProvider;
    }
}
