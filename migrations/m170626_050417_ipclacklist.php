<?php

use yii\db\Migration;

class m170626_050417_ipclacklist extends Migration
{
    public function up()
    {
        $this->createTable('ip_blacklist', [
            'id' => $this->primaryKey(),
            'since_dt' => $this->decimal(15, 0)->notNull(),
            'ip_v4' => $this->string(15),
            'ip_dec' => $this->decimal(15, 0)->notNull(),
        ]);

        $this->createIndex('idx_ip_dec', 'ip_blacklist', ['ip_dec']);
    }

    public function down()
    {
        $this->dropTable('ip_blacklist');
    }
}
