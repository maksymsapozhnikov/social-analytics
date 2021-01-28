<?php

use app\models\Survey;
use yii\db\Migration;

/**
 * Class m190228_165553_mobi47Denormalization
 */
class m190228_165553_mobi47Denormalization extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('survey', 'stat_dirty_score', $this->float());
        $this->addColumn('survey', 'stat_time_score', $this->float());
        $this->addColumn('survey', 'stat_avg_time_score', $this->float());
        $this->addColumn('survey', 'stat_count_act', $this->integer());
        $this->addColumn('survey', 'stat_count_scr', $this->integer());
        $this->addColumn('survey', 'stat_count_dsq', $this->integer());
        $this->addColumn('survey', 'stat_count_fin', $this->integer());
        $this->addColumn('survey', 'stat_count_all', $this->integer());
        $this->addColumn('survey', 'stat_bid_summa', $this->float());

        $this->db->createCommand('lock tables survey write, respondent_survey write')->execute();

        $surveys = Survey::find()->select('id')->orderBy(['id' => SORT_ASC])->column();
        foreach ($surveys as $id) {
            $time = $this->beginCommand('update statistics, id=' . $id);
            Survey::updateStatistics($id);
            $this->endCommand($time);
        }

        $this->db->createCommand('unlock tables')->execute();
    }

    /**
     * @param integer $surveyId
     * @return string
     */
    protected function queryData($surveyId)
    {
        return <<<SQL
        select count(*)                                         as "stat_count_all",
               sum(case when status = 4 then bid else null end) as "stat_bid_summa",
               sum(case when status = 4 then 1 else null end)   as "stat_count_fin",
               sum(case when status = 3 then 1 else null end)   as "stat_count_dsq",
               sum(case when status = 2 then 1 else null end)   as "stat_count_scr",
               sum(case when status = 1 then 1 else null end)   as "stat_count_act",
               avg(case when status = 4 then timing_score_avg else null end) as "stat_avg_time_score",
               avg(case when status = 4 then timing_score_sum else null end) as "stat_time_score",
               avg(case when status = 4 then dirty_score else null end) as "stat_dirty_score"
        from respondent_survey
        where survey_id = {$surveyId}
        group by survey_id
SQL;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('survey', 'stat_dirty_score');
        $this->dropColumn('survey', 'stat_time_score');
        $this->dropColumn('survey', 'stat_avg_time_score');
        $this->dropColumn('survey', 'stat_count_act');
        $this->dropColumn('survey', 'stat_count_scr');
        $this->dropColumn('survey', 'stat_count_dsq');
        $this->dropColumn('survey', 'stat_count_fin');
        $this->dropColumn('survey', 'stat_count_all');
        $this->dropColumn('survey', 'stat_bid_summa');
    }
}
