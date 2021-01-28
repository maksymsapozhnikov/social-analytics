<?php

use yii\db\Migration;
use app\models\RespondentSurvey;

class m170715_170431_phoneAlters extends Migration
{
    public function up()
    {
        $this->alterColumn(\app\models\RespondentSurvey::tableName(), 'phone', $this->decimal(20)->defaultValue(null)->comment('Phone number'));
        $this->createIndex('idx_survey', RespondentSurvey::tableName(), ['survey_id']);
        $this->createIndex('idx_phone', RespondentSurvey::tableName(), ['phone']);
    }

    public function down()
    {
        $this->dropIndex('idx_phone', RespondentSurvey::tableName());
        $this->dropIndex('idx_survey', RespondentSurvey::tableName());
    }
}
