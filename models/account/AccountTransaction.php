<?php
namespace app\models\account;

use app\components\AppHelper;
use app\models\Survey;
use yii\db\ActiveRecord;

/**
 * Class AccountTransaction
 * @property integer $id
 * @property integer $dt
 * @property integer $account_id
 * @property integer $operation
 * @property double $value
 * @property string $currency
 * @property integer $survey_id
 * @property string $note
 * @property Survey $respondentSurvey
 * @property Account $account
 */
class AccountTransaction extends ActiveRecord
{
    public function rules()
    {
        return [
            [['survey_id'], 'validatePassed'],
        ];
    }

    public function validatePassed($attribute, $params, $validator)
    {
        if (!$this->survey_id) {
            return;
        }

        $passed = self::findOne([
            'account_id' => $this->account_id,
            'survey_id' => $this->survey_id,
        ]);

        if (!is_null($passed)) {
            $this->addError($attribute, 'Top ups for this survey have been sent already');
        }
    }

    public static function tableName()
    {
        return 'account_transaction';
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        if (!$this->isNewRecord) {
            $this->addError('id', 'Unable to update account transaction.');
        }

        /** @todo check this transaction already exist */

        $this->dt = AppHelper::timeUtc();

        return parent::save($runValidation, $attributeNames);
    }

    public function getAccount()
    {
        return $this->hasOne(Account::className(), ['id' => 'account_id']);
    }

    public function getRespondentSurvey()
    {
        return $this->hasOne(Survey::className(), ['id' => 'survey_id']);
    }

    public function getSurvey()
    {
        return $this->hasOne(Survey::className(), ['id' => 'survey_id']);
    }
}
