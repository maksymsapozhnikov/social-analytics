<?php
namespace app\models;

use app\components\AppHelper;
use app\models\enums\PhoneSystemEnum;
use yii\behaviors\AttributeTypecastBehavior;
use yii\db\ActiveRecord;
use app\components\helpers\TranslateMessage;

/**
 * Class PhoneCache
 * @property string $currency
 * @property integer $phone
 * @property bool $valid
 * @property string $country
 * @property string $countryId
 * @property string $operator
 * @property string $operatorId
 * @property int $payment_system
 */
class PhoneCache extends ActiveRecord
{
    public static function tableName()
    {
        return 'phone_cache';
    }

    public function rules()
    {
        return [
            [['valid', 'phone', 'country', 'countryId', 'operator', 'operatorId', 'currency'], 'safe'],
        ];
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        if ($this->isNewRecord) {
            $this->dt_create = AppHelper::timeUtc();
            $this->dt_modify = null;
        } else {
            $this->dt_create = $this->oldAttributes['dt_create'];
            $this->dt_modify = AppHelper::timeUtc();
        }

        return parent::save($runValidation, $attributeNames);
    }

    public function behaviors()
    {
        return [
            'typecast' => [
                'class' => AttributeTypecastBehavior::className(),
                'attributeTypes' => [
                    'valid' => AttributeTypecastBehavior::TYPE_BOOLEAN,
                ],
                'typecastAfterFind' => true,
            ],
        ];
    }

    protected function isPostpaid()
    {
        return $this->payment_system == PhoneSystemEnum::POSTPAID;
    }

    public function canPay()
    {
        return $this->valid && !$this->isPostpaid();
    }

    public function getReason()
    {
        if (!$this->canPay()) {
            $message = $this->isPostpaid() ?
                'Please provide prepaid phone number' :
                'The phone number doesn\'t exist. Please correct the number and check again.';
            return [
                'valid' => false,
                'message' => TranslateMessage::t('survey-process', $message),
            ];
        }

        return [
            'valid' => true
        ];
    }
}
