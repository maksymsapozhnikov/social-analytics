<?php

use yii\db\Migration;

class m170612_160217_additionalColumns extends Migration
{
    public function up()
    {
        $this->alterColumn('survey', 'rmsid', $this->string(7)->notNull()->unique()->comment('Unique local string Survey ID, 7 chars'));

        $this->addColumn('survey', 'country', $this->string()->notNull()->defaultValue('undefined')->comment('Country name'));
        $this->addColumn('survey', 'sample', $this->integer()->notNull()->defaultValue(100)->comment('Sample size'));
    }

    public function down()
    {
        $this->dropColumn('survey', 'country');
        $this->dropColumn('survey', 'sample');
    }
}
