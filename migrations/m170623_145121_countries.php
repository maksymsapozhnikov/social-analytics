<?php

use yii\db\Migration;

class m170623_145121_countries extends Migration
{
    public function up()
    {
        $this->createTable('country', [
            'name' => $this->string(255)->notNull(),
        ]);
        $this->addPrimaryKey('pk_country', 'country', ['name']);
    }

    public function down()
    {
        $this->dropTable('country');
    }
}
