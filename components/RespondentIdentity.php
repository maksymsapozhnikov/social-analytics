<?php
namespace app\components;

use app\models\IpBlacklist;
use app\models\RecruitmentProfile;
use app\models\Respondent;
use app\components\deviceatlas\DeviceAtlasCloudClient;
use app\models\RespondentStatus;
use app\models\Survey;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Class RespondentIdentity
 * @package app\components
 * @property string $ip respondent's IP
 * @property LogSession $logSession
 */
class RespondentIdentity extends Component
{
    /** @var array */
    protected $deviceAtlas = [];

    public $deviceAtlasLicense = null;

    /** @var Respondent */
    public $respondent;

    /** @var Survey */
    public $survey;

    /** @var array rms cookies object */
    protected $rms;

    /** @var string respondent language */
    public $language;

    public $isTest;

    /** @var LogSession */
    protected $logSession;

    /** @var string */
    public $_uri;

    public function init()
    {
        parent::init();

        $this->getDeviceData();
        $this->initRms();

        $this->isTest = $this->getRms('test');
        $this->language = $this->getRms('lang') ?? $this->getUriParam('lang');

        $this->initRespondent();
    }

    /**
     * @param Survey $initialSurvey
     * @return boolean
     */
    public function hasFinishedRecruitmentSurvey($initialSurvey)
    {
        /** @var RecruitmentProfile|null $profile */
        $profile = $this->respondent->getRecruitmentProfile($initialSurvey);

        return $profile && $profile->isFilled;
    }

    /**
     * @param string $attribute
     * @param mixed $default
     * @return mixed
     */
    public function getDeviceData($attribute = null, $default = null)
    {
        if ($this->deviceAtlasLicense && empty($this->deviceAtlas)) {
            try {
                DeviceAtlasCloudClient::$licenseKey = $this->deviceAtlasLicense;
                $this->deviceAtlas = DeviceAtlasCloudClient::getDeviceData();
            } catch (\Throwable $e) {
                $this->deviceAtlas = [];
            }
        }

        return is_null($attribute) ? $this->deviceAtlas : ArrayHelper::getValue($this->deviceAtlas, $attribute, $default);
    }

    /**
     * @return LogSession
     */
    public function getLogSession()
    {
        $this->logSession = $this->logSession ?: new LogSession([
            'survey' => $this->survey,
            'respondent' => $this->respondent,
        ]);

        return $this->logSession;
    }

    public function setSurvey(Survey $survey)
    {
        $this->survey = $survey;

        return $this;
    }

    /**
     *
     * @param $rmsid
     * @return $this
     */
    public function loadRepondent($rmsid)
    {
        $this->respondent = Respondent::findOne(['rmsid' => $rmsid]);

        if ($this->respondent) {
            \Yii::$app->language = $this->respondent->language ?: \Yii::$app->language;
        }

        return $this;
    }

    protected function initRespondent()
    {
        $this->respondent = Respondent::findOne(['rmsid' => $this->getRms('rmsid')]);

        if ($this->respondent instanceof Respondent) {
            if ($this->respondent->isTest && !$this->isTest) {
                $this->respondent = null;
            } else {
                $this->respondent->setAttributes([
                    'fingerprint_id' => $this->getRms('fp'),
                    'traffic_source' => $this->getUriParam('s'),
                    'language' => $this->getUriParam('lang'),
                    'ip' => \Yii::$app->request->getUserIP(),
                ]);

                $this->respondent->save();
            }
        }
    }

    protected function initRms()
    {
        $this->rms = [];

        if (isset($_COOKIE['_rms'])) {
            try {
                $this->rms = Json::decode(base64_decode($_COOKIE['_rms']));
            } catch (\Exception $exception) {
                $this->rms = [];
            }
        }
    }

    public function getUriParam($param)
    {
        $parts = parse_url($this->getRms('uri'));

        parse_str(ArrayHelper::getValue($parts, 'query', ''), $query);

        return ArrayHelper::getValue($query, $param, '-');
    }

    public function getRms($parameter = null)
    {
        if (is_null($parameter)) {
            return $this->rms;
        }

        if ($parameter === 'uri' && $this->_uri) {
            return $this->_uri;
        }

        return ArrayHelper::getValue($this->rms, $parameter);
    }

    public function getAtlas($parameter = null, $default = null)
    {
        if (is_null($parameter)) {
            return $this->deviceAtlas;
        }

        return ArrayHelper::getValue($this->deviceAtlas, $parameter, $default);
    }

    public function isUnique()
    {
        $exists = Respondent::find()
            ->andWhere([
                'device_id' => (string)$this->getAtlas('properties.id', '-1'),
                'fingerprint_id' => $this->getRms('fp'),
                'ip' => \Yii::$app->request->userIP,
                ])
            ->andWhere(['NOT LIKE', 'rmsid', '@%', false])
            ->count('*');

        return !$exists;
    }

    public function createNew()
    {
        $this->respondent = new Respondent();

        $this->respondent->setAttributes(array_merge([
            'device_id' => (string)$this->getAtlas('properties.id', '-1'),
            'fingerprint_id' => $this->getRms('fp'),
            'traffic_source' => $this->getUriParam('s'),
            'language' => $this->language,
            'additional' => '{}', /** @fixme what is this? */
            'registered_at' => AppHelper::timeUtc(),
            'last_seen_at' => AppHelper::timeUtc(),
            'ip' => \Yii::$app->request->getUserIP(),
            'device_atlas' => Json::encode($this->getAtlas('properties')),
            'isTest' => $this->isTest,
            'browser' => $this->getAtlas('useragent'),
        ], $this->extendedAttributes()));

        $saved = $this->respondent->save();
        if ($saved) {
            $this->rms['rmsid'] = $this->respondent->rmsid;
        }

        return $saved;
    }

    protected function extendedAttributes()
    {
        $atlasProperties = $this->getAtlas('properties');
        $result = [];
        $attributes = [
            'device_vendor' => 'vendor',
            'device_model' => 'model',
            'device_marketing_name' => 'marketingName',
            'device_manufacturer' => 'manufacturer',
            'device_year_released' => 'yearReleased',
            'os_vendor' => 'osVendor',
            'os_name' => 'osName',
            'os_family' => 'osFamily',
            'os_version' => 'osVersion',
        ];

        foreach($attributes as $attr => $key) {
            if (isset($atlasProperties[$key])) {
                $result[$attr] = $atlasProperties[$key];
            }
        }

        return $result;
    }

    public function isBlocked()
    {
        $isBlacklisted = !is_null(IpBlacklist::findByIp(\Yii::$app->request->userIP));
        $isDisqualified = !is_null($this->respondent) && $this->respondent->status == RespondentStatus::DISQUALIFIED;

        return $isBlacklisted || $isDisqualified;
    }
}
