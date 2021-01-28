<?php

use yii\db\Migration;

class m170901_192501_profileNames extends Migration
{
    public function up()
    {
        $this->addColumn('{{%profile}}', 'last_name', $this->string(255)->notNull()->defaultValue('')->comment('Last name'));
    }

    public function down()
    {
        $this->dropColumn('{{%profile}}', 'last_name');
    }
}
