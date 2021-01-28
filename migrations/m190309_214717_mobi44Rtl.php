<?php

use yii\db\Migration;

/**
 * Class m190309_214717_mobi44Rtl
 */
class m190309_214717_mobi44Rtl extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('language', 'is_rtl', $this->boolean()->comment('RTL option'));

        $this->update('language', ['is_rtl' => 1], ['lang' => 'ar']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('language', 'is_rtl');
    }
}