<?php

use yii\db\Migration;

/**
 * Class m190606_191002_mobi58StrictRecruitment
 */
class m190606_191002_mobi58StrictRecruitment extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('survey', 'strict_recruitment', $this->boolean()->notNull()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('survey', 'strict_recruitment');
    }
}