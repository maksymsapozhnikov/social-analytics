<?php
namespace app\models\search;

use app\models\translation\Message;
use yii\data\ActiveDataProvider;

/**
 * Class TranslationMessageSearch
 * @package app\models\search
 */
class TranslationMessageSearch extends Message
{
    public $category;
    public $message;
    public $eng_message;

    /**
     * @param string $lang
     * @return ActiveDataProvider
     */
    public function search($lang)
    {
        $query = static::find()->alias('m')
            ->select([
                'id' => 'm.id',
                'category' => 'sm.category',
                'eng_message' => 'mm.translation',
                'language' => 'm.language',
                'translation' => 'm.translation',
                'message' => 'sm.message',
            ])
            ->joinWith(['sourceMessage as sm'], false)
            ->leftJoin('mod_message mm', 'mm.id = m.id AND mm.language = '. "'en'")
            ->andWhere(['m.language' => $lang]);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);
    }
}
