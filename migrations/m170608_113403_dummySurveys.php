<?php

use app\models\Survey;
use app\models\SurveyStatus;
use yii\db\Migration;

class m170608_113403_dummySurveys extends Migration
{
    public function safeUp()
    {
        return true;
        $dummies = [
            [
                'name' => 'REST API Sample',
                'url' => 'https://www.surveygizmo.com/s3/3618800/REST-API-Sample',
                'status' => SurveyStatus::ACTIVE,
                'country' => 'mock',
                'sample' => 1,
            ],
            [
                'name' => 'VN Recruitment',
                'url' => 'https://www.surveygizmo.com/s3/3615253/S1tvD',
                'status' => SurveyStatus::ACTIVE,
                'country' => 'mock',
                'sample' => 1,
            ],
        ];

        foreach($dummies as $dummy) {
            $survey = new Survey($dummy);
            if (!$survey->save()) {
                echo '*** Error: ' . "\n";
                var_dump($survey->getErrors());

                return false;
            }
        }
    }

    public function down()
    {
        return true;
        $this->delete('respondent_survey');
        $this->delete('survey');
    }
}
