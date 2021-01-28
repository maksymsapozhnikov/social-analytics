<?php
namespace app\models;

use yii\db\ActiveRecord;

class Currency extends ActiveRecord
{
    public static function tableName()
    {
        return 'currency';
    }

}
