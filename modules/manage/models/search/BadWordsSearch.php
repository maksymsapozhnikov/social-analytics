<?php
namespace app\modules\manage\models\search;

use app\modules\manage\models\BadWords;
use yii\data\ActiveDataProvider;

class BadWordsSearch extends BadWords
{
    public function search($params = [])
    {
        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->orderBy([
            'country' => SORT_ASC,
        ]);

        return $dataProvider;
    }
}
