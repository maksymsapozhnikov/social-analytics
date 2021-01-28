<?php

use yii\db\Migration;

class m170709_191641_phoneUnique extends Migration
{
    public function up()
    {
        $this->dropIndex('phone', 'respondent_survey');
    }

    public function down()
    {
        echo "m170709_191641_phoneUnique cannot be reverted.\n";

        return false;
    }
}
