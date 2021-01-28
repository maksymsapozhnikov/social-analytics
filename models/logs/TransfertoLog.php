<?php

namespace app\models\logs;

use app\components\AppHelper;
use yii\db\ActiveRecord;

class TransfertoLog extends ActiveRecord
{
    public static function tableName()
    {
        return 'transferto_log';
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        if ($this->isNewRecord) {
            $this->dt = AppHelper::timeUtc();
        }

        return parent::save($runValidation, $attributeNames);
    }


}
