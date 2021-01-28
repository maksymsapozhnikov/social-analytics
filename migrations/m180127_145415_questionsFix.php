<?php

use yii\db\Migration;
use app\components\QueriesHelper;
use app\models\Info;
use yii\helpers\Json;

/**
 * Class m180127_145415_questionsFix
 */
class m180127_145415_questionsFix extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $questions = QueriesHelper::getAnswersKeys();

        sort($questions);

        Info::value(Info::QUESTIONNAIRE_QUESTIONS, Json::encode($questions));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        return true;
    }

}
