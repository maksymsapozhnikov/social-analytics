<?php
namespace app\models;

use app\components\AppHelper;
use yii\db\ActiveRecord;

/**
 * Class Info
 * @property string $name
 * @property string $description
 * @property string $value
 * @property integer $dt_modified
 * @property integer $dt_created
 */
class Info extends ActiveRecord
{
    const TRANSFERTO_BALANCE = 'TransferTo:Balance';
    const TRANSFERTO_CURRENCY = 'TransferTo:Currency';
    const QUESTIONNAIRE_QUESTIONS = 'Questionnaire:Questions';
    const REPORT_COST_ADJUSTMENTS = 'ReportCost:Adjustment';

    public static function tableName()
    {
        return 'info';
    }

    public function rules()
    {
        return [
            [['name', 'value', 'description',], 'safe'],
            [['name', 'value', 'description',], 'string'],
            [['name',], 'unique'],
        ];
    }

    /**
     * @param string $name
     * @param string $value
     * @return string
     * @throws
     */
    public static function value($name, $value = null)
    {
        $info = static::findOne(['name' => $name]);
        if (is_null($info)) {
            throw new \Exception('Undefined name: ' . $name);
        }

        if (!is_null($value)) {
            $info->value = $value;
            $info->save();
        }

        return $info->value;
    }

    public static function modified($name)
    {
        return is_null($info = static::findOne(['name' => $name])) ? null : $info->dt_modified;
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        if($this->isNewRecord) {
            $this->dt_created = AppHelper::timeUtc();
            if (is_null($this->dt_modified)) {
                $this->dt_modified = $this->dt_created;
            }
        } else {
            $this->dt_modified = AppHelper::timeUtc();
        }

        return parent::save($runValidation, $attributeNames);
    }

}
