<?php

use yii\db\Migration;

class m170621_175725_indexing extends Migration
{
    public function up()
    {
        $this->createIndex('idx_results_rmsid', 'respondent_survey', ['survey_id', 'status']);
    }

    public function down()
    {
        $this->dropIndex('idx_results_rmsid', 'respondent_survey');
    }
}
