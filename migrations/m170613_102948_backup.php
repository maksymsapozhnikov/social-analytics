<?php

use yii\db\Migration;

class m170613_102948_backup extends Migration
{
    public function up()
    {
        $sql = <<<SQL
        CREATE TABLE respondent_backup170613 as SELECT * FROM respondent;
        CREATE TABLE respondent_survey_backup170613 as SELECT * FROM respondent_survey;
        CREATE TABLE survey_backup170613 as SELECT * FROM survey;
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
