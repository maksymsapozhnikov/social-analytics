<?php

use yii\db\Migration;

class m170706_203603_accountBalance extends Migration
{
    public function up()
    {
        $this->createTable('account', [
            'id' => $this->primaryKey(),
            'phone' => $this->decimal(16)->unique(),
            'currency' => $this->string(3)->notNull(),
            'value' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'dt_create' => $this->integer()->notNull(),
            'dt_modify' => $this->integer(),
        ]);

        $this->createTable('account_transaction', [
            'id' => $this->primaryKey(),
            'dt' => $this->integer(),
            'account_id' => $this->integer()->notNull(),
            'operation' => $this->integer(2)->notNull(),
            'value' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'currency' => $this->string(3)->notNull(),
            'survey_id' => $this->integer(),
            'note' => $this->string()->notNull()->defaultValue(''),
        ]);

        $this->createIndex('idx_account_id', 'account_transaction', ['account_id']);
        $this->createIndex('idx_survey_id', 'account_transaction', ['survey_id']);
    }

    public function down()
    {
        $this->dropTable('account_transaction');
        $this->dropTable('account');
    }
}
