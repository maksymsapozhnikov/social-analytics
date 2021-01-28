<?php

use yii\db\Migration;
use app\models\RespondentLog;

/**
 * Class m180302_001954_logsIndex
 */
class m180302_001954_logsIndex extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('idxRespondentLogs_CreatedDt', RespondentLog::tableName(), ['create_dt']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idxRespondentLogs_CreatedDt', RespondentLog::tableName());
    }
}
