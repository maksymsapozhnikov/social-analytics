<?php

use yii\db\Migration;
use app\models\Info;
use yii\helpers\Json;
use app\models\RespondentSurvey;
use yii\helpers\ArrayHelper;

class m170804_172736_questionnaireInfo extends Migration
{
    public function safeUp()
    {
        $info = (new Info([
            'name' => Info::QUESTIONNAIRE_QUESTIONS,
            'value' => Json::encode([]),
            'description' => 'Questionnaire questions list',
        ]));
        $info->save();

        $questions = [];

        $results = RespondentSurvey::find()
            ->where(['=', 'status', \app\models\RespondentSurveyStatus::FINISHED])
            ->all();

        foreach($results as $record) {
            $recordQuestions = array_keys(Json::decode($record->response));
            $questions = array_unique(array_merge($questions, $recordQuestions));
        }

        ArrayHelper::removeValue($questions, 'Question_SKU');
        ArrayHelper::removeValue($questions, '');

        $questions = array_values($questions);
        sort($questions);

        Info::value(Info::QUESTIONNAIRE_QUESTIONS, Json::encode($questions));
    }

    public function safeDown()
    {
        $info = Info::findOne(['name' => Info::QUESTIONNAIRE_QUESTIONS]);
        if (!is_null($info)) {
            $info->delete();
        }
    }
}
