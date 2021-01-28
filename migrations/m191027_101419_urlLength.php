<?php

use yii\db\Migration;

/**
 * Class m191027_101419_urlLength
 */
class m191027_101419_urlLength extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('respondent_survey', 'uri', 'text');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return false;
    }
}
