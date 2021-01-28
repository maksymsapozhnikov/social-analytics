<?php

use yii\db\Migration;

class m171017_135138_emailCache extends Migration
{
    public function safeUp()
    {
        $this->createTable('email_cache', [
            'id' => $this->primaryKey(),
            'email' => $this->string()->notNull(),
            'valid' => $this->integer(1)->notNull()->defaultValue(0),

            // Mailboxlayer values
            'did_you_mean' => $this->string()->notNull()->defaultValue(''),
            'format_valid' => $this->integer(1)->notNull()->defaultValue(0)->comment('The general syntax of the requested email address is valid'),
            'mx_found' => $this->integer(1)->notNull()->defaultValue(0)->comment('MX-Records for the requested domain could be found'),
            'catch_all' => $this->integer(1)->notNull()->defaultValue(0)->comment('Requested email address is found to be part of a catch-all mailbox'),
            'role' => $this->integer(1)->notNull()->defaultValue(0)->comment('Requested email address is a role email address'),
            'disposable' => $this->integer(1)->notNull()->defaultValue(0)->comment('Requested email address is a disposable email address'),
            'free' => $this->integer(1)->notNull()->defaultValue(0)->comment('Requested email address is a free email address'),
            'score' => $this->decimal(5, 3)->notNull()->defaultValue(0)->comment('Value between 0 and 1 reflecting the quality and deliverability of the requested email address'),

            'dt_create' => $this->integer()->notNull(),
            'dt_modify' => $this->integer(),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('email_cache');
    }
}
