<?php

use yii\db\Migration;

class m170613_101953_ips extends Migration
{
    public function up()
    {
        $this->addColumn('respondent', 'ip', $this->string()->comment('Client IP address'));
    }

    public function down()
    {
        $this->dropColumn('respondent', 'ip');
    }
}
