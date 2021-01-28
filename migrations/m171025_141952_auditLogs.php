<?php

use yii\db\Migration;

class m171025_141952_auditLogs extends Migration
{
    public function safeUp()
    {
        $this->createTable('block_log', [
            'id' => $this->primaryKey(),
            'dt' => $this->integer()->notNull()->comment('Date'),
            'ip' => $this->string()->notNull()->comment('IP'),
            'ip_dec' => $this->bigInteger()->notNull()->comment('IP long decimal value'),
            'code' => $this->integer()->notNull()->comment('SuspiciousStatus code'),
            'respondent_id' => $this->integer()->comment('Respondent, FK respondent.id'),
            'survey_id' => $this->integer()->notNull()->comment('Survey, FK survey.id'),
            'uri' => $this->text()->comment('URI requested'),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('block_log');
    }
}
