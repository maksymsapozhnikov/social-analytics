<?php
namespace app\components\fraud;

use app\components\RespondentIdentity;
use app\models\enums\SuspiciousStatus;
use app\models\IpBlacklist;
use app\models\Respondent;
use app\models\RespondentStatus;

class RespondentCheck
{
    /** @var RespondentIdentity */
    public $respondentIdentity;

    /** @var boolean */
    public $strict = false;

    /** @var array functions list, each function returns suspicious code */
    protected $rules = [
        /* 'checkUnique', */
        'checkIpBlacklist',
        'checkDisqualified',
        'checkRobot',
        /* 'checkFingerprintStrict', */
        'checkSurveybotIdChanged',
        'checkSurveybotIdAlreadyUsed',
    ];

    protected $code = SuspiciousStatus::LEGAL;

    public function __construct(RespondentIdentity $respondentIdentity, $strict)
    {
        $this->respondentIdentity = $respondentIdentity;
        $this->strict = boolval($strict);
        $this->code = SuspiciousStatus::LEGAL;
    }

    /**
     * Check all respondent rules
     * @throws BlockingException
     * @return int
     */
    public function check()
    {
        if ($this->isTestRespondent()) {
            return SuspiciousStatus::LEGAL;
        }

        foreach ($this->rules as $rule) {
            $code = $this->$rule();
            $this->code = $code === SuspiciousStatus::LEGAL ? $this->code : $code;
        }

        if ($this->code !== SuspiciousStatus::LEGAL) {
            if ($this->respondentIdentity->respondent) {
                $this->respondentIdentity->respondent->suspicious = $this->code;
                $this->respondentIdentity->respondent->save();
            }

            if ($this->strict) {
                throw new BlockingException($this->code);
            }
        }

        return $this->code;
    }

    protected function isTestRespondent()
    {
        $identity = $this->respondentIdentity;

        return $identity->respondent && $identity->respondent->isTest;
    }

    /**
     * Checks respondent is unique (device id, fingerprint and ip) doesn't exist
     * @return int
     */
    protected function checkUnique()
    {
        $identity = $this->respondentIdentity;
        if (!$identity->respondent) {
            return SuspiciousStatus::LEGAL;
        }

        $deviceId = $identity->respondent->device_id;
        $fingerprint = $identity->getRms('fp');

        $query = Respondent::find()
            ->andWhere([
                'device_id' => $deviceId,
                'fingerprint_id' => $fingerprint,
                'ip' => \Yii::$app->request->userIP,
            ])
            ->andWhere(['<>', 'id', $identity->respondent->id])
            ->orderBy(['id' => SORT_DESC])
            ->limit(1);

        $exists = $query->one();

        return !$exists ? SuspiciousStatus::LEGAL : SuspiciousStatus::ID_DUPLICATED;
    }

    /**
     * Checks respondent is in the IP blacklist
     */
    protected function checkIpBlacklist()
    {
        $ip = \Yii::$app->request->userIP;
        $isBlacklisted = !is_null(IpBlacklist::findByIp($ip));

        if ($isBlacklisted) {
            throw new BlockingException(SuspiciousStatus::IP_BLACKLISTED);
        }

        return SuspiciousStatus::LEGAL;
    }

    /**
     * Check respondent is already disqualified.
     * @throws BlockingException
     */
    protected function checkDisqualified()
    {
        $respondent = $this->respondentIdentity->respondent;

        if (!is_null($respondent) && $respondent->status == RespondentStatus::DISQUALIFIED) {
            throw new BlockingException($respondent->suspicious);
        }

        return SuspiciousStatus::LEGAL;
    }

    /**
     * Checks respondent is a robot, uses DeviceAtlas properties.
     * @return int
     * @throws BlockingException
     */
    protected function checkRobot()
    {
        $isRobot = $this->respondentIdentity->getAtlas('properties.isRobot');

        if ($isRobot) {
            throw new BlockingException(SuspiciousStatus::IS_ROBOT);
        }

        return SuspiciousStatus::LEGAL;
    }

    protected function checkFingerprintStrict()
    {
        $identity = $this->respondentIdentity;
        if (!$identity->respondent || !$this->strict) {
            return SuspiciousStatus::LEGAL;
        }

        $exists = Respondent::find()
            ->andWhere(['fingerprint_id' => $this->respondentIdentity->getRms('fp')])
            ->andWhere(['<>', 'suspicious', SuspiciousStatus::LEGAL])
            ->andWhere(['<>', 'id', $this->respondentIdentity->respondent->id])
            ->orderBy(['id' => SORT_DESC])
            ->limit(1)
            ->one();

        if (!!$exists) {
            throw new BlockingException(SuspiciousStatus::STRICT_FP_CHECK);
        }

        return SuspiciousStatus::LEGAL;
    }

    /**
     * Checks respondent's SurveyBot identifier assigned is not changed once have been set
     * @return int
     * @throws BlockingException
     */
    protected function checkSurveybotIdChanged()
    {
        /** @var Respondent $respondent */
        $respondent = $this->respondentIdentity->respondent;

        if (!$this->respondentIdentity->respondent) {
            return SuspiciousStatus::LEGAL;
        }

        if ($respondent->isAttributeChanged('sb_respondent_id') && $respondent->getOldAttribute('sb_respondent_id')) {
            $this->blockRespondent(SuspiciousStatus::SURVEYBOT_ID_CHANGED);
        }

        return SuspiciousStatus::LEGAL;
    }

    /**
     * Checks repondent's SurveyBot identifier has not been used by someone else
     * @return int
     * @throws BlockingException
     */
    protected function checkSurveybotIdAlreadyUsed()
    {
        /** @var Respondent $respondent */
        $respondent = $this->respondentIdentity->respondent;

        if (!$this->respondentIdentity->respondent) {
            return SuspiciousStatus::LEGAL;
        }

        $sbRespondentId = $respondent->getAttribute('sb_respondent_id');
        if ($sbRespondentId && $sbRespondentId <> '-') {
            $exists = Respondent::find()
                ->where(['=', 'sb_respondent_id', $sbRespondentId])
                ->andWhere(['<>', 'id', $respondent->id])
                ->one();

            if ($exists) {
                $this->blockRespondent(SuspiciousStatus::SURVEYBOT_ID_ALREADY_USED);
            }
        }

        return SuspiciousStatus::LEGAL;
    }

    protected function blockRespondent($reason)
    {
        $this->respondentIdentity->respondent->block($reason);

        throw new BlockingException($reason);
    }
}
