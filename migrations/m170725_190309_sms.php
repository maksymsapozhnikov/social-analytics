<?php

use yii\db\Migration;
use app\models\Survey;

class m170725_190309_sms extends Migration
{
    public function up()
    {
        $this->addColumn(Survey::tableName(), 'topup_sms', $this->string(30)->defaultValue(null));
    }

    public function down()
    {
        $this->dropColumn(Survey::tableName(), 'topup_sms');
    }
}
