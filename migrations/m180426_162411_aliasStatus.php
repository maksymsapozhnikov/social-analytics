<?php

use yii\db\Migration;
use app\models\Alias;
use app\models\SurveyStatus;

/**
 * Class m180426_162411_aliasStatus
 */
class m180426_162411_aliasStatus extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(Alias::tableName(), 'status', $this->integer()->notNull()->defaultValue(SurveyStatus::ACTIVE));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(Alias::tableName(), 'status');
    }
}