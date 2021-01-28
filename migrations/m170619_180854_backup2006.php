<?php

use yii\db\Migration;

class m170619_180854_backup2006 extends Migration
{
    public function up()
    {
        $sql = <<<SQL
        TRUNCATE TABLE respondent_log; 
        CREATE TABLE respondent_backup170620 as SELECT * FROM respondent;
        CREATE TABLE respondent_survey_backup170620 as SELECT * FROM respondent_survey;
        CREATE TABLE survey_backup170620 as SELECT * FROM survey;
        DELETE FROM respondent_survey;
        DELETE FROM respondent;
SQL;
        $this->db->createCommand($sql)->execute();
    }

    public function down()
    {
        echo "m170613_102948_backup cannot be reverted.\n";

        return false;
    }
}
