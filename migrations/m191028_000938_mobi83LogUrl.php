<?php

use yii\db\Migration;

/**
 * Class m191028_000938_mobi83LogUrl
 */
class m191028_000938_mobi83LogUrl extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('respondent_log', 'end_url', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('respondent_log', 'end_url');
    }
}
