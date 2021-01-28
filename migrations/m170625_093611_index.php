<?php

use yii\db\Migration;

class m170625_093611_index extends Migration
{
    public function up()
    {
        $this->createIndex('idx_results_status', 'respondent_survey', ['status']);
    }

    public function down()
    {
        $this->dropIndex('idx_results_status', 'respondent_survey');
    }
}
