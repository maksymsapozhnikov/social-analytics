<?php

use yii\db\Migration;

class m170625_112453_tapjoy extends Migration
{
    public function up()
    {
        $this->addColumn('respondent_survey', 'tapjoy_subid', $this->string()->comment('TapJoy click key / transaction id'));
    }

    public function down()
    {
        $this->dropColumn('respondent_survey', 'tapjoy_subid');
    }
}
