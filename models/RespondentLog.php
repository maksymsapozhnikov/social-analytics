<?php
namespace app\models;

use app\components\AppHelper as App;
use yii\db\ActiveRecord;
use yii\helpers\Json;

/**
 * Class RespondentLog
 * @package app\models
 * @property integer $status
 * @property string $status_message
 * @property Survey $survey
 * @property Respondent $respondent
 * @property string $fingerprint_id
 * @property string $end_url
 */
class RespondentLog extends ActiveRecord
{
    public static function tableName()
    {
        return 'respondent_log';
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        if ($this->isNewRecord) {
            $this->create_dt = App::timeUtc();
        }

        $this->stringify('deviceatlas_details');
        $this->stringify('ip_details');
        $this->stringify('request_details');

        $this->modify_dt = App::timeUtc();

        return parent::save($runValidation, $attributeNames);
    }

    public function rules()
    {
        return [
            [
                ['respondent_id', 'survey_rmsid', 'device_id', 'fingerprint_id',
                 'ip', 'referrer', 'deviceatlas_details', 'ip_details',
                 'request_details', 'survey_id'], 'safe'
            ],
        ];
    }

    protected function stringify($attribute)
    {
        $this->{$attribute} = is_string($this->{$attribute}) ? $this->{$attribute} : Json::encode($this->{$attribute});
    }

    public function ipDetails($attribute)
    {
        $value = Json::decode($this->ip_details);

        return isset($value[$attribute]) ? $value[$attribute] : null;
    }

    public function requestDetails($attribute)
    {
        $value = Json::decode($this->request_details);

        return isset($value[$attribute]) ? $value[$attribute] : null;
    }

    public function deviceatlasDetails($attribute)
    {
        $value = Json::decode($this->deviceatlas_details);

        return isset($value[$attribute]) ? $value[$attribute] : null;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRespondent()
    {
        return $this->hasOne(Respondent::class, ['id' => 'respondent_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSurvey()
    {
        return $this->hasOne(Survey::class, ['rmsid' => 'survey_rmsid']);
    }
}
