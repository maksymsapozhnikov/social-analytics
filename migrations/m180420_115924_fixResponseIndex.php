<?php

use yii\db\Migration;
use app\models\RespondentSurvey;

/**
 * Class m180420_115924_fixResponseIndex
 */
class m180420_115924_fixResponseIndex extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('idxResponseStartedAt', RespondentSurvey::tableName(), ['started_at']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idxResponseStartedAt', RespondentSurvey::tableName());
    }
}
