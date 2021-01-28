<?php
namespace app\modules\manage\models\reports;

use app\models\Info;
use app\models\RespondentSurveyStatus;
use yii\base\Model;
use yii\db\Connection;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Class CostReport
 * @package app\modules\manage\models\reports
 * @property Connection $db
 */
class CostReport extends Model
{
    public $bd;
    public $ed;
    public $country;
    public $project;

    protected $whereClause;
    protected $sqlParams = [];

    /**
     * {@inheritdoc}
     * @throws \yii\db\Exception
     */
    public function init()
    {
        parent::init();

        $this->prepareTempTables();

        $this->buildWhereClause();

        $this->prepareTempTables2();
    }

    /**
     *
     */
    protected function buildWhereClause()
    {
        $whereClauses = [];
        $this->sqlParams = [];

        if (in_array($this->country, $this->countries())) {
            $whereClauses[] = 't.country = :country';
            $this->sqlParams[':country'] = $this->country;
        } else {
            $this->country = null;
        }

        $bd = \DateTime::createFromFormat('d.m.Y H:i:s', $this->bd . ' 00:00:00');
        $ed = \DateTime::createFromFormat('d.m.Y H:i:s', $this->ed . ' 23:59:59');

        if ($bd) {
            $whereClauses[] = 't.dt_started >= :started';
            $this->sqlParams[':started'] = $bd->format('U');
        } else {
            $this->bd = null;
        }

        if ($ed) {
            $whereClauses[] = 't.dt_finished <= :finished';
            $this->sqlParams[':finished'] = $ed->format('U');
        } else {
            $this->ed = null;
        }

        if ($this->project && !empty($this->project)) {
            $i = 0;
            $bindNames = [];
            foreach ($this->project as $project) {
                ++$i;
                $bindName = ":value{$i}";
                $bindNames[] = $bindName;
                $this->sqlParams[$bindName] = $project;
            }
            $whereClauses[] = 't.project_id in (' . implode(',', $bindNames) . ')';
        }

        $this->whereClause = !empty($whereClauses) ? 'where ' . implode (' and ', $whereClauses) : '';
    }

    /**
     * @return \yii\db\Connection
     */
    public function getDb()
    {
        return \Yii::$app->db;
    }

    /**
     *
     *
     * @return array
     * @throws
     */
    public function build()
    {
        return $this->db->createCommand($this->getReportQuery(), $this->sqlParams)->queryAll();
    }

    /**
     * @return array
     * @throws
     */
    public function countries()
    {
        $stFin = RespondentSurveyStatus::FINISHED;
        $query = "select distinct country as country from tCostReport where status = {$stFin} order by 1";
        $countries = $this->db->createCommand($query)->queryColumn();

        return array_filter($countries);
    }

    /**
     * @return array
     * @throws
     */
    public function projects()
    {
        $stFin = RespondentSurveyStatus::FINISHED;
        $query = "select distinct project_id as project from tCostReport where status = {$stFin} order by 1";
        $projects = $this->db->createCommand($query)->queryColumn();

        return array_filter($projects);
    }

    /**
     * Prepares temporary tables.
     * @todo replace substr with json operators
     * @throws \yii\db\Exception
     */
    protected function prepareTempTables()
    {
        $query = <<<SQL
        create temporary table tCostReport as
        select jsf_project_id as "project_id",
               jsf_country as "country",
               cast(bid as decimal(15, 6)) as "bid",
               {$this->sqlJsonExtract('SOURCE')} as "source",
               started_at as "dt_started",
               started_at as "dt_finished",
               status as "status",
               timing_score_sum as "timing_score_sum",
               timing_score_avg as "timing_score_avg"
        from respondent_survey rs
        where jpi_hash > 0
SQL;

        $this->db->createCommand($query)->execute();
    }

    /**
     * Prepares temporary tables.
     * @throws \yii\db\Exception
     */
    protected function prepareTempTables2()
    {
        $query = <<<SQL
        create temporary table tCostCte as
        select t.project_id,
               t.country,
               min(t.dt_started)  as "started",
               max(t.dt_finished) as "finished"
        from tCostReport t
        {$this->whereClause}
        group by project_id, country
SQL;

        $this->db->createCommand($query, $this->sqlParams)->execute();
    }

    /**
     * @return string
     */
    protected function getReportQuery()
    {
        $fin = RespondentSurveyStatus::FINISHED;
        $dsq = RespondentSurveyStatus::DISQUALIFIED;
        $scr = RespondentSurveyStatus::SCREENED_OUT;
        $inp = RespondentSurveyStatus::ACTIVE;

        return <<<SQL
        select t.project_id as "project_id",
               t.country as "country",
               max(ag.started)  as "started",
               max(ag.finished)  as "finished",
               {$this->sqlSourceColumns('tpj')},
               {$this->sqlSourceColumns('fyb')},
               {$this->sqlSourceColumns('cint')},
               {$this->sqlSourceColumns('tgm')},
               {$this->sqlSourceColumns('poll')},
               {$this->sqlSourceColumns('ply')},
               sum(case when t.status = $fin then 1 else 0 end) as "all_done",
               sum(case when t.status = $dsq then 1 else 0 end) as "all_dsq",
               sum(case when t.status = $scr then 1 else 0 end) as "all_scr",
               sum(case when t.status = $inp then 1 else 0 end) as "all_inp",
               count(*) as "all_started",
               t.bid * sum(case when t.status = $fin then 1 else 0 end) as "all_cost",
               t.bid * sum(case when t.status = $scr then 1 else 0 end) as "scr_cost",
               t.bid * sum(case when t.status = $dsq then 1 else 0 end) as "dsq_cost",
               t.bid as "all_cpi",
               sum(case when t.status = $fin then t.timing_score_sum else 0 end) as "timing_score_sum",
               sum(case when t.status = $fin then t.timing_score_avg else 0 end) as "timing_score_avg"
        from tCostReport t
        join tCostCte ag on ag.project_id = t.project_id and ag.country = t.country
        {$this->whereClause}
        group by t.project_id, t.country, t.bid
        order by 4 desc, t.project_id, t.country, t.bid

SQL;
    }

    /**
     * @param string $param
     * @todo replace substr with json operators
     * @return string
     */
    protected function sqlJsonExtract($param)
    {
        $item = "'\"{$param}\":\"'";
        $lItem = strlen($item) - 2;

        return <<<SQL
        case when position($item in response) > 0
             then substr(
               response,
               position($item in response) + $lItem,
               position('"' in substr(response, position($item in response) + $lItem)) - 1
             ) else null end
SQL;
    }

    /**
     * @param string $source
     * @return string
     */
    protected function sqlSourceColumns($source)
    {
        return <<<SQL
               sum(case when source = '{$source}' and status = 4 then 1 else 0 end) as "{$source}_done",
               t.bid * sum(case when source = '{$source}' and status = 4 then 1 else 0 end) as "{$source}_cost",
               t.bid as "{$source}_cpi"
SQL;
    }

    /**
     * @param string $project
     * @param string $country
     * @return float
     * @throws \Exception
     */
    public static function adjustment($project, $country)
    {
        static $info;

        $info = $info ?? Json::decode(Info::value(Info::REPORT_COST_ADJUSTMENTS));

        return floatval(ArrayHelper::getValue($info, "{$project}.{$country}", null));
    }
}