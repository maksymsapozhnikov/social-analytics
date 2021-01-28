<?php
namespace app\components;

use app\components\enums\SessionStatusEnum;
use app\models\Respondent;
use app\models\RespondentLog;
use app\models\Survey;
use yii;

class LogSession extends yii\base\BaseObject
{
    /** @var RespondentLog */
    protected $log = null;

    public $logSessionId = 'app_components_LogSession';

    /** @var Respondent */
    public $respondent;

    /** @var Survey */
    public $survey;

    /** @var RespondentIdentity */
    protected $_identity;

    public function init()
    {
        parent::init();

        $this->_identity = \Yii::$app->respondentIdentity;
    }

    /**
     * @param $statusId
     * @return RespondentLog
     * @throws \Exception
     */
    public function findLog($statusId)
    {
        if ($this->log) {
            return $this->log;
        }

        switch($statusId) {
            case SessionStatusEnum::ST_ALIAS:
            case SessionStatusEnum::ST_PRELOADING:
            case SessionStatusEnum::ST_IDENTIFIED:
            case SessionStatusEnum::ST_SURVEYGIZMO:
            case SessionStatusEnum::ST_BLOCKED:
            case SessionStatusEnum::ST_RECRUITMENT:
                $sessionId = \Yii::$app->session->get($this->logSessionId);
                if ($sessionId) {
                    $this->log = $this->findLogById($sessionId);
                }

                if (!$this->log) {
                    $this->log = $this->startLogSession();
                }

                break;

            case SessionStatusEnum::ST_PROGRESS:
            case SessionStatusEnum::ST_SCREENED_OUT:
            case SessionStatusEnum::ST_DISQUALIFIED:
            case SessionStatusEnum::ST_FINISHED:
                if ($this->respondent && $this->survey) {
                    $this->log = $this->findLogByResponse();
                } else {
                    throw new \Exception('[LogSession error] Respondent or Survey is not set');
                }

                break;

            default:
                throw new \Exception('[LogSession error] Unknown Session status');
        }
    }

    /**
     * @param integer $statusId enum SessionStatusEnum
     * @param string $statusMessage
     * @throws \Exception
     */
    public function set($statusId, $statusMessage = null)
    {
        $this->findLog($statusId);

        if ($this->log) {
            $this->log->status = $statusId;
            $this->log->status_message = $statusMessage;

            $this->log->save();
        }

        if ($statusId === SessionStatusEnum::ST_BLOCKED || $statusId === SessionStatusEnum::ST_SURVEYGIZMO) {
            $this->close();
        }
    }

    public function setEndUrl($url, $status = null)
    {
        $this->findLog($status ?? SessionStatusEnum::ST_IDENTIFIED);

        if ($this->log) {
            $this->log->end_url = is_array($url) ? yii\helpers\Url::to($url) : $url;
            $this->log->save();
        }
    }

    protected function setRespondentSurvey(RespondentLog $log)
    {
        if ($log) {
            if ($this->survey) {
                $log->setAttributes([
                    'survey_id' => $this->survey->id,
                    'survey_rmsid' => $this->survey->rmsid,
                ]);
            }

            if ($this->respondent) {
                $log->setAttributes([
                    'respondent_id' => $this->respondent->id,
                    'respondent_rmsid' => $this->respondent->rmsid,
                ]);
            }
        }

        return $log;
    }

    protected function findLogById($id)
    {
        $log = RespondentLog::findOne($id);

        $log = $this->setRespondentSurvey($log);

        return $log;
    }

    protected function findLogByResponse()
    {
        if (!$this->respondent->id || !$this->survey->id) {
            return null;
        }

        $availableStatuses = [
            SessionStatusEnum::ST_SURVEYGIZMO,
            SessionStatusEnum::ST_PROGRESS,
        ];

        return RespondentLog::find()
            ->where([
                'respondent_id' => $this->respondent->id,
                'survey_id' => $this->survey->id,
            ])
            ->andWhere(['IN', 'status', $availableStatuses])
            ->orderBy(['id' => SORT_DESC])
            ->one();
    }

    protected function startLogSession()
    {
        $ip = Yii::$app->request->userIP;
        $rq = Yii::$app->request;

        $log = new RespondentLog();

        $log->setAttributes([
            'request_details' => [
                'url' => $rq->url,
                'headers' => $rq->headers->toArray(),
            ],
            'ip' => $ip,
            'ip_details' => IpChecker::getDetails($ip),
            'referrer' => $rq->referrer,
            'device_id' => $this->getIdentity()->getDeviceData('properties.id', '-1'),
            'deviceatlas_details' => $this->getIdentity()->getDeviceData('properties', []),
        ]);

        $log = $this->setRespondentSurvey($log);

        $log->save();

        \Yii::$app->session->set($this->logSessionId, $log->id);

        return $log;
    }

    public function update($data = [])
    {
        $attributes = [
            'device_id' => $this->getIdentity()->getDeviceData('properties.id', '-1'),
            'deviceatlas_details' => $this->getIdentity()->getDeviceData('properties', []),
        ];
        if (isset($data['fingerprint_id'])) {
            $attributes['fingerprint_id'] = $data['fingerprint_id'];
        }

        $this->log->setAttributes($attributes);

        $this->log->save();
    }

    /**
     * @return RespondentIdentity
     */
    protected function getIdentity()
    {
        return $this->_identity;
    }

    public function close()
    {
        \Yii::$app->session->remove($this->logSessionId);
    }

    public function saveParameter($name, $value)
    {
        \Yii::$app->session->set('___' . $name, $value);
    }

    public function getParameter($name)
    {
        return \Yii::$app->session->get('___' . $name);
    }
}
