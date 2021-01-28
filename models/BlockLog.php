<?php
namespace app\models;

use app\components\AppHelper;
use app\models\enums\SuspiciousStatus;
use yii\db\ActiveRecord;

/**
 * Class AuditLog
 * @property Survey $survey
 * @property Respondent $respondent
 */
class BlockLog extends ActiveRecord
{
    public static function tableName()
    {
        return 'block_log';
    }

    public function getSurvey()
    {
        return $this->hasOne(Survey::className(), ['id' => 'survey_id']);
    }

    public function getRespondent()
    {
        return $this->hasOne(Respondent::className(), ['id' => 'respondent_id']);
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        if ($this->isNewRecord) {
            $this->dt = AppHelper::timeUtc();
            $this->ip_dec = AppHelper::ip2long($this->ip);
        }

        return parent::save($runValidation, $attributeNames);
    }

    public function getReason()
    {
        return SuspiciousStatus::getTitle($this->code);
    }
}
