<?php

use yii\db\Migration;

class m170706_194456_phoneCache extends Migration
{
    public function up()
    {
        $this->dropTable('phone');

        $this->createTable('phone_cache', [
            'id' => $this->primaryKey(),
            'phone' => $this->decimal(16)->notNull()->unique(),
            'valid' => $this->boolean()->notNull()->defaultValue(false),

            'country' => $this->string(),
            'country_id' => $this->integer(),
            'operator' => $this->string(),
            'operator_id' => $this->integer(),
            'currency' => $this->string(3),

            'dt_create' => $this->integer()->notNull(),
            'dt_modify' => $this->integer(),
        ]);
    }

    public function down()
    {
        echo "m170706_194456_phoneCache cannot be reverted.\n";

        return false;
    }

}
