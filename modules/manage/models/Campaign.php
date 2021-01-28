<?php
namespace app\modules\manage\models;

/**
 * Class Campaign
 * @package app\modules\manage\models
 * @property integer $id
 * @property string $name
 */
class Campaign extends BaseModel
{
    public static function tableName()
    {
        return 'campaign';
    }

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'unique'],
            [['name'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Campaign Name',
            'created_at' => 'Created',
            'updated_at' => 'Updated',
        ];
    }

}
