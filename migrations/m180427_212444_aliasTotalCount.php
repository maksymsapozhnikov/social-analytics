<?php

use yii\db\Migration;
use app\models\Alias;
use app\models\RespondentSurvey;

/**
 * Class m180427_212444_aliasTotalCount
 */
class m180427_212444_aliasTotalCount extends Migration
{
    /**
     * {@inheritdoc}
     * @throws
     */
    public function safeUp()
    {
        $this->prepareColumnsRequired();

        $this->createTempAliasesLog();

        $this->fillRespondentSurveyAliases();

        $this->createTempAliasesCounter();

        $this->updateAliasUsage();

        $this->removeTempTables();
    }

    /**
     *
     */
    protected function prepareColumnsRequired()
    {
        $this->addColumn(RespondentSurvey::tableName(), 'alias_id', $this->integer()->defaultValue(null));
        $this->addColumn(Alias::tableName(), 'cnt_finished', $this->integer()->notNull()->defaultValue(0));
        $this->createIndex('idxAliasRmsid', Alias::tableName(), ['rmsid'], true);
    }

    /**
     * @throws \yii\db\Exception
     */
    protected function createTempAliasesLog()
    {
        $time = $this->beginCommand("create temp aliases log");
        $query = <<<'SQL'
        create table tmp_aliases as
        SELECT
          l.respondent_id as "respondent_id",
          l.survey_id as "survey_id",
          a.id as "alias_id"
        FROM `respondent_log` l
          join survey_alias a on a.rmsid = substr(request_details from position('"url":"/sa/' in request_details) + 11 for 5)
        WHERE (l.`request_details` LIKE '%\"url\":\"/sa/%') AND (l.`respondent_id` > 0) and l.survey_id > 0
        group by l.respondent_id, l.survey_id, a.id
SQL;

        $this->db->createCommand($query)->execute();
        $this->endCommand($time);
    }

    /**
     * @throws \yii\db\Exception
     */
    protected function fillRespondentSurveyAliases()
    {
        $time = $this->beginCommand("fill respondent_survey aliases");
        $query = <<<'SQL'
        update respondent_survey, tmp_aliases set
        respondent_survey.alias_id = tmp_aliases.alias_id
        where respondent_survey.respondent_id = tmp_aliases.respondent_id
        and respondent_survey.survey_id = tmp_aliases.survey_id;
SQL;

        $this->db->createCommand($query)->execute();
        $this->endCommand($time);
    }

    /**
     * @throws \yii\db\Exception
     */
    protected function createTempAliasesCounter()
    {
        $time = $this->beginCommand("count alias usage");
        $query = <<<'SQL'
        create table tmp_aliases_counter as
        select alias_id, count(*) as cnt_finished
        from respondent_survey
        where alias_id > 0 and status = 4
        group by alias_id;
SQL;

        $this->db->createCommand($query)->execute();
        $this->endCommand($time);
    }

    /**
     * @throws \yii\db\Exception
     */
    protected function updateAliasUsage()
    {
        $time = $this->beginCommand("update alias usage");
        $query = <<<'SQL'
        update survey_alias, tmp_aliases_counter set
          survey_alias.cnt_finished = tmp_aliases_counter.cnt_finished
        where survey_alias.id = tmp_aliases_counter.alias_id;
SQL;
        $this->db->createCommand($query)->execute();
        $this->endCommand($time);
    }

    /**
     * @throws \yii\db\Exception
     */
    protected function removeTempTables()
    {
        $time = $this->beginCommand("clean temporary data");
        $query = <<<'SQL'
        drop table tmp_aliases;
        drop table tmp_aliases_counter;
SQL;
        $this->db->createCommand($query)->execute();
        $this->endCommand($time);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idxAliasRmsid', Alias::tableName());

        $this->dropColumn(Alias::tableName(), 'cnt_finished');
        $this->dropColumn(RespondentSurvey::tableName(), 'alias_id');
    }
}