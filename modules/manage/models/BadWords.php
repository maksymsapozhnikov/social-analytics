<?php
namespace app\modules\manage\models;

use yii\db\ActiveRecord;

/**
 * Class BadWords
 * @package Manage
 * @property $id integer
 * @property $country string
 * @property $words string
 */
class BadWords extends ActiveRecord
{
    protected $_words;

    public static function tableName()
    {
        return 'survey_bad_words';
    }

    public function rules()
    {
        return [
            [['country', 'words'], 'required'],
            [['country', 'words'], 'string'],
            ['country', 'unique'],
            [['country', 'words'], 'safe'],
        ];
    }

    public function isContainedAnyWord($text)
    {
        $words = explode(',', $this->words);

        foreach($words as $word) {
            $word = trim($word);
            if (mb_strrpos($text, $word) !== false) {
                return true;
            }
        }

        return false;
    }
}
