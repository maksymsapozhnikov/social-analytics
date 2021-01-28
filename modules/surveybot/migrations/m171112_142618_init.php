<?php

use yii\db\Migration;
use app\modules\surveybot\models\Response;

/**
 * @author Vlad Ilinyh <v.ilinyh@gmail.com>
 */
class m171112_142618_init extends Migration
{

    public function safeUp()
    {
        $this->createTable(Response::tableName(), [
            'id' => $this->primaryKey(),

            'sb_survey_id' => $this->string()->notNull()->comment('Surveybot survey string identifier (key:survey.id)'),
            'sb_survey_name' => $this->string()->notNull()->comment('Surveybot survey name (key:survey.name)'),
            'sb_response_id' => $this->string()->notNull()->comment('Surveybot response string identifier (key:response.id)'),

            'started_at' => $this->integer()->notNull()->comment('Survey started at (key:response.started_at)'),
            'completed_at' => $this->integer()->defaultValue(null)->comment('Survey finished at (key:response.completed_at)'),

            'sb_respondent_id' => $this->integer()->notNull()->comment('Surveybot respondent integer identifier (key:respondent.id)'),
            'sb_respondent' => $this->text()->notNull()->comment('Respondent details as JSON (key:respondent)'),
            'sb_response' => $this->text()->notNull()->comment('Response as JSON (key:)'),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable(Response::tableName());
    }

}
