<?php

use yii\db\Migration;

/**
 * Class m190606_175411_mobi57S2sCalled
 */
class m190606_175411_mobi57S2sCalled extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('respondent_survey', 's2s_callback', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('respondent_survey', 's2s_callback');
    }
}