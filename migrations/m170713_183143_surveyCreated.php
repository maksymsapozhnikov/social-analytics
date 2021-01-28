<?php

use app\components\AppHelper as App;
use app\models\Survey;
use yii\db\Migration;

class m170713_183143_surveyCreated extends Migration
{
    public function up()
    {
        $defaultDate = App::timeUtc();
        $this->addColumn(Survey::tableName(), 'dt_created', $this->integer()->notNull()->defaultValue($defaultDate));

        $query = <<<'SQL'
        UPDATE survey
          JOIN (
                 SELECT
                   survey_id,
                   min(started_at) - 3600 AS dt_created
                 FROM respondent_survey
                 GROUP BY survey_id
               ) s ON survey.id = s.survey_id
        SET survey.dt_created = s.dt_created
        WHERE survey.id = s.survey_id
SQL;
        \Yii::$app->db->createCommand($query)->execute();
    }

    public function down()
    {
        $this->dropColumn(Survey::tableName(), 'dt_created');
    }
}
