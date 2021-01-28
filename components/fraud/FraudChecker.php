<?php
namespace app\components\fraud;

use app\components\RespondentIdentity;
use app\models\BlockLog;
use app\models\Respondent;
use app\models\RespondentSurvey;
use app\models\Survey;
use yii\base\Component;

class FraudChecker extends Component
{
    /**
     * Performs fraud checking of given respondent identity.
     * @param RespondentIdentity $respondent respondent identity
     * @param Survey $survey strict checking, block respondent on any suspicious reason
     * @return int app\models\enums\SuspiciousStatus
     * @throws BlockingException if respondent should be blocked
     */
    public function checkRespondent(RespondentIdentity $respondent, Survey $survey)
    {
        try {
            $result = (new RespondentCheck($respondent, $survey->strict))->check();
        } catch (BlockingException $e) {
            if ($respondent->respondent instanceof Respondent) {
                $this->logBlock($e->getCode(), $respondent->respondent, $survey);
            }

            throw $e;
        }

        return $result;
    }

    /**
     * Performs fraud checking of given survey response.
     * @param RespondentSurvey $respondentSurvey response to check
     * @return int app\models\enums\SuspiciousStatus
     * @throws BlockingException if response should be blocked
     */
    public function checkResponse(RespondentSurvey $respondentSurvey)
    {
        try {
            $result = (new ResponseCheck($respondentSurvey))->check();
        } catch (BlockingException $e) {
            if (!$respondentSurvey->isFinished()) {
                $this->logBlock($e->getCode(), $respondentSurvey->respondent, $respondentSurvey->survey);
            }

            throw $e;
        }

        return $result;
    }

    /**
     * Logs respondent block
     * @param $code
     * @param Respondent $respondent
     * @param Survey $survey
     */
    protected function logBlock($code, Respondent $respondent, Survey $survey)
    {
        (new BlockLog([
            'code' => $code,
            'respondent_id' => $respondent->id,
            'survey_id' => $survey->id,
            'uri' => \Yii::$app->respondentIdentity->getRms('uri') ?: 'POST /response',
            'ip' => \Yii::$app->request->userIP,
        ]))->save();
    }
}
