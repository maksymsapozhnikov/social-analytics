<?php

use yii\db\Migration;

/**
 * Class m190627_153050_mobi71Changes
 */
class m190627_153050_mobi71Changes extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('survey', 'url_end', $this->string()->defaultValue(null)->comment('TgmMobi survey end URL'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('survey', 'url_end');
    }
}
