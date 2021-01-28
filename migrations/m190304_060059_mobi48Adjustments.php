<?php

use yii\db\Migration;

/**
 * Class m190304_060059_mobi48Adjustments
 */
class m190304_060059_mobi48Adjustments extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $query = <<<'SQL'
alter table respondent_log modify status_message text null;
alter table respondent_survey modify ip_dec bigint null comment 'ip2long';
alter table block_log modify ip_dec bigint;
alter table ip_blacklist modify ip_dec bigint;
SQL;
        $this->execute($query);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }
}