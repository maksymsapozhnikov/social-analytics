<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * Class Language
 * @property string $lang
 * @property string $name
 * @property string $native_name
 * @property boolean $is_rtl
 */
class Language extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'language';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['lang', 'name', 'native_name'], 'required'],
            ['lang', 'string', 'min' => 2, 'max' => 10],
            ['name', 'string', 'max' => 50],
            ['native_name', 'string', 'max' => 100],
            [['lang', 'name', 'native_name'], 'safe'],
        ];
    }
}
