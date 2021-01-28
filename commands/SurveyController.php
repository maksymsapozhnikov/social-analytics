<?php
namespace app\commands;

use app\models\RespondentSurvey;
use app\models\Survey;
use yii\console\Controller;
use yii\db\Connection;

/**
 * Class SurveyController
 * @package app\commands
 */
class SurveyController extends Controller
{
    /** @var Connection */
    protected $db;

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        parent::init();

        $this->db = \Yii::$app->db;
    }

    /**
     * Updates empty jpi and jpf fields in RespondentSurvey model.
     * @throws
     */
    public function actionUpdateProject()
    {
        $tran = $this->db->beginTransaction();
        $t = $this->beginCommand('Lists surveys');
        $surveyIds = $this->getSurveyIds();
        $this->endCommand($t, count($surveyIds) . ' records');

        foreach ($surveyIds as $surveyId) {
            $survey = Survey::findOne($surveyId);

            $t = $this->beginCommand('Lists responses');
            $ids = $this->getEmptyRespondentSurvey($surveyId);
            $this->endCommand($t, count($ids) . ' records');

            $t = $this->beginCommand('Updates responses for ' . $survey->rmsid);
            foreach ($ids as $id) {
                $rs = RespondentSurvey::findOne($id);
                if (!$rs->jsf_country && !$rs->jsf_project_id) {
                    $rs->jsf_country = $rs->survey->country;
                    $rs->jsf_project_id = $rs->survey->project_id;
                    $rs->save(false);
                }
            }
            $this->endCommand($t);
        }

        $tran->commit();

    }

    /**
     * @return array
     * @throws
     */
    protected function getSurveyIds()
    {
        $query = <<<'SQL'
        select id from survey where project_id > ''
SQL;
        return $this->db->createCommand($query)->queryColumn();
    }

    /**
     * @param integer $surveyId
     * @return array
     * @throws
     */
    protected function getEmptyRespondentSurvey($surveyId)
    {
        $query = <<<'SQL'
        select id from respondent_survey where jpi_hash is null and survey_id = :survey_id
SQL;
        return $this->db->createCommand($query)->bindValue(':survey_id', $surveyId)->queryColumn();
    }

    /**
     * Prepares for a command to be executed, and outputs to the console.
     *
     * @param string $description the description for the command, to be output to the console.
     * @return float the time before the command is executed, for the time elapsed to be calculated.
     */
    protected function beginCommand($description)
    {
        echo "    > $description ...";
        return microtime(true);
    }

    /**
     * Finalizes after the command has been executed, and outputs to the console the time elapsed.
     *
     * @param float $time the time before the command was executed.
     * @param string
     */
    protected function endCommand($time, $note = 'done')
    {
        $note = $note ?: 'done';

        echo ' ' . $note . ' (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }
}