<?php

use yii\db\Migration;

class m171018_181708_ipsurveyblock extends Migration
{
    public function safeUp()
    {
        $this->addColumn('respondent_survey', 'ip', $this->string()->comment('IP'));
        $this->addColumn('respondent_survey', 'ip_dec', $this->integer()->comment('ip2long'));

        $query = <<<SQL
        update respondent_survey, respondent set
          respondent_survey.ip = respondent.ip,
          respondent_survey.ip_dec = (
            256 * 256 * 256 * SUBSTRING_INDEX(respondent.ip, '.', 1) +
            256 * 256 * SUBSTRING_INDEX(SUBSTRING_INDEX(respondent.ip, '.', 2), '.', -1) +
            256 * SUBSTRING_INDEX(SUBSTRING_INDEX(respondent.ip, '.', 3), '.', -1) +
            SUBSTRING_INDEX(SUBSTRING_INDEX(respondent.ip, '.', 4), '.', -1)
          )
        where respondent_survey.respondent_id = respondent.id
SQL;
        $this->db->createCommand($query)->execute();
    }

    public function safeDown()
    {
        $this->dropColumn('respondent_survey', 'ip');
        $this->dropColumn('respondent_survey', 'ip_dec');
    }
}
