<?php

use yii\db\Migration;
use app\models\RespondentLog;

/**
 * Class m180228_194724_logStatus
 */
class m180228_194724_logStatus extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(RespondentLog::tableName(), 'status', $this->integer());
        $this->addColumn(RespondentLog::tableName(), 'status_message', $this->string());

        $this->addColumn(RespondentLog::tableName(), 'survey_id', $this->integer());

        $this->createIndex('idx_RespondentSurvey_ids', RespondentLog::tableName(), [
            'respondent_id', 'survey_id',
        ]);

        $query = <<<'SQL'
        UPDATE respondent_log, survey s SET
        respondent_log.survey_id = s.id
        WHERE s.rmsid = respondent_log.survey_rmsid
SQL;
        $this->db->createCommand($query)->execute();
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropIndex('idx_RespondentSurvey_ids', RespondentLog::tableName());

        $this->dropColumn(RespondentLog::tableName(), 'survey_id');

        $this->dropColumn(RespondentLog::tableName(), 'status_message');
        $this->dropColumn(RespondentLog::tableName(), 'status');
    }
}
