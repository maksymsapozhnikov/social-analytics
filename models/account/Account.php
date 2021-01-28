<?php
namespace app\models\account;

use app\components\AppHelper;
use app\components\MobileChecker;
use app\components\TransferTo;
use app\models\enums\PhoneSystemEnum;
use app\models\enums\TransfertoError;
use app\models\PhoneCache;
use app\models\RespondentSurvey;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Class Account
 * @property integer $id
 * @property double $phone
 * @property string $currency
 * @property double $value
 * @property integer $dt_create
 * @property integer $dt_modify
 * @property integer $payment_system
 */
class Account extends ActiveRecord
{
    public static function tableName()
    {
        return 'account';
    }

    /**
     * Adds an incentive to account.
     * @param RespondentSurvey $rs finished respondent survey, phone should be set.
     * @param null|double $value if $value is null, then survey topup_value will be used.
     * @throws \Exception when something goes wrong.
     * @return Account
     */
    public static function addSurveyIncentive(RespondentSurvey $rs, $value = null)
    {
        $value = $value ?: $rs->survey->topup_value;

        $db = \Yii::$app->db;
        $transaction = $db->beginTransaction();

        try {
            $account = self::findOne(['phone' => $rs->phone]);
            $phoneDetails = PhoneCache::findOne(['phone' => $rs->phone]);

            if (!is_null($account)) {
                if ($account->currency != $phoneDetails->currency) {
                    throw new \Exception('Account currency (' . $account->currency . ') is not equal phone currency ( ' . $phoneDetails->currency . ' )', 422);
                }
            } else {
                $account = new Account([
                    'phone' => $rs->phone,
                    'currency' => $phoneDetails->currency,
                    'value' => 0,
                ]);

                if (!$account->save()) {
                    throw new \Exception('Unable to create new account', 500);
                }
            }

            $accountTransaction = new AccountTransaction([
                'account_id' => $account->id,
                'operation' => Operation::INCENTIVE,
                'value' => $value,
                'currency' => $account->currency,
                'survey_id' => $rs->survey->id,
                'note' => 'Survey bonus',
            ]);

            if (!$accountTransaction->save()) {
                $errors = $accountTransaction->getFirstErrors();
                throw new \Exception(array_pop($errors), 500);
            }

            $account->value += $accountTransaction->value;

            if (!$account->save()) {
                throw new \Exception('Unable to update account balance', 500);
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $account;
    }

    public function paySurveyIncentive($value = null, $sms = false)
    {
        $db = \Yii::$app->db;
        $transaction = $db->beginTransaction();

        try {
            $details = MobileChecker::getTransferToData($this->phone);

            if (!$value) {
                $value = 0;
                $details['product_list'] = array_map('floatval', $details['product_list']);
                sort($details['product_list']);

                foreach ($details['product_list'] as $product_value) {
                    if ($product_value <= (double)$this->value) {
                        $value = $product_value;
                    } else {
                        break;
                    }
                }
            }

            if (0 === $value) {
                throw new \Exception('Minimum topup is ' . min($details['product_list']), 400);
            }

            $accountTransaction = $this->addAccountTransaction($value);

            /** @var TransferTo $transferTo */
            $transferTo = \Yii::$app->transferTo;
            $ttResponse = $transferTo->sendTopUps($this->phone, $accountTransaction->value, $sms);
            if ($transferTo->isError()) {
                if ($transferTo->errorCode == TransfertoError::POSTPAID) {
                    $this->payment_system = PhoneSystemEnum::POSTPAID;
                }
                throw new \Exception('Error: ' . $transferTo->errorMessage, 500);
            }

            $this->payment_system = PhoneSystemEnum::PREPAID;

            $transaction->commit();

            $accountTransaction->details = Json::encode($ttResponse);
            $accountTransaction->save();

            $this->savePaymentSystem();
        } catch (\Exception $e) {
            $transaction->rollBack();
            $this->savePaymentSystem();

            throw $e;
        }

        return $transferTo->spentValue;
    }

    public function addAccountTransaction($value)
    {
        $accountTransaction = new AccountTransaction([
            'account_id' => $this->id,
            'operation' => Operation::TRANSFER,
            'value' => $value,
            'currency' => $this->currency,
            'survey_id' => null,
            'note' => 'Paying bonus to the mobile phone',
        ]);

        if (!$accountTransaction->save()) {
            $errors = $accountTransaction->getFirstErrors();
            throw new \Exception(array_pop($errors), 500);
        }

        $this->value -= $accountTransaction->value;

        if (!$this->save()) {
            throw new \Exception('Internal error', 500);
        }

        return $accountTransaction;
    }

    protected function savePaymentSystem()
    {
        $this->save(true, ['payment_system']);

        $cached = PhoneCache::findOne(['phone' => $this->phone]);
        $cached->payment_system = $this->payment_system;
        $cached->save();
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

    protected static function logError($details)
    {
        /** @todo implement log audit */
    }

    public function getPhoneDetails()
    {
        return $this->hasOne(PhoneCache::className(), ['phone' => 'phone']);
    }

    public function getTransactions()
    {
        return $this->hasMany(AccountTransaction::className(), ['account_id' => 'id']);
    }

    public static function findOrCreate($attributes)
    {
        $account = self::findOne(['phone' => ArrayHelper::getValue($attributes, 'phone')]);

        if (is_null($account)) {
            $account = new Account($attributes);
            $account->save();
        }

        return $account;
    }
}
