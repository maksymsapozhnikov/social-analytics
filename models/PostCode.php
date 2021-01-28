<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * Class PostCode
 * @package app\models
 * @property $id integer
 * @property $postcode string
 * @property $town string
 * @property $state string
 * @property $state_short string
 * @property $strata string
 * @property $area string
 */
class PostCode extends ActiveRecord
{
    public static function tableName()
    {
        return 'postcode';
    }

    public function asArray()
    {
        return $this->getAttributes(['postcode', 'town', 'state', 'state_short', 'strata', 'area']);
    }
}
