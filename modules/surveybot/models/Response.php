<?php
namespace app\modules\surveybot\models;

use yii\db\ActiveRecord;
use yii\helpers\Json;

/**
 * @author Vlad Ilinyh <v.ilinyh@gmail.com>
 */
class Response extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%surveybot_response}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'sb_survey_id', 'sb_survey_name', 'sb_response_id', 'started_at', 'completed_at', 'sb_respondent_id',
                    'sb_respondent', 'sb_response',
                ],
                'required'
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'sb_survey_id' => 'Survey Identifier (survey.id)',
            'sb_survey_name' => 'Survey Name (survey.name)',
            'sb_response_id' => 'Response Identifier (response.id)',
            'started_at' => 'Survey started at (response.started_at)',
            'completed_at' => 'Survey completed at (response.completed_at)',
            'sb_respondent_id' => 'Respondent Identifier (respondent.id)',
            'sb_respondent' => 'Response (response)',
            'sb_response' => 'Response Attributes (response.attributes)',
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->sb_respondent = Json::encode($this->sb_respondent);
            $this->sb_response = Json::encode($this->sb_response);

            if ($insert) {
                $this->started_at = \DateTime::createFromFormat('d/m/Y h:i A', $this->started_at);
                if ($this->started_at) {
                    $this->started_at = $this->started_at->format('U');
                }

                $this->completed_at = \DateTime::createFromFormat('d/m/Y h:i A', $this->completed_at);
                if ($this->completed_at) {
                    $this->completed_at = $this->completed_at->format('U');
                }
            }

            return true;
        }

        return false;
    }

    public function afterFind()
    {
        $this->sb_respondent = Json::decode($this->sb_respondent);
        $this->sb_response = Json::decode($this->sb_response);
    }

    public static function getLastResponse($respondentId, $surveyId = null)
    {
        return self::find()
            ->where(['sb_respondent_id' => $respondentId])
            ->andFilterWhere(['sb_survey_id' => $surveyId])
            ->orderBy(['id' => SORT_DESC])
            ->one();
    }
}
