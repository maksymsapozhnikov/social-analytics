<?php

use yii\db\Migration;

class m170612_191156_deviceidFix extends Migration
{
    public function up()
    {
        $this->update('respondent', ['device_id' => -1], ['is', 'device_id', null]);
        $this->update('respondent', ['device_id' => -1], ['=', 'device_id', '']);
    }

    public function down()
    {

    }

}
