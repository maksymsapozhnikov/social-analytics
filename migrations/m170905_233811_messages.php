<?php

use yii\db\Migration;

class m170905_233811_messages extends Migration
{
    public function safeUp()
    {
        $filename = Yii::getAlias('@app/db/mod_source_message.sql');
        $this->execute(file_get_contents($filename));

        $filename = Yii::getAlias('@app/db/mod_message.sql');
        $this->execute(file_get_contents($filename));
    }

    public function safeDown()
    {
        return false;
    }
}
