<?php
namespace app\models;

use app\components\AppHelper;
use app\models\enums\SuspiciousStatus;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Class Respondent
 * @property $id integer
 * @property $rmsid string
 * @property $device_id string
 * @property $fingerprint_id string
 * @property $failed_tryings integer
 * @property $status integer
 * @property $language string
 * @property $traffic_source string
 * @property $additional string
 * @property $device_vendor string
 * @property $device_model string
 * @property $device_marketing_name string
 * @property $device_manufacturer string
 * @property $device_year_released integer
 * @property $os_vendor string
 * @property $os_name string
 * @property $os_family string
 * @property $os_version string
 * @property $device_atlas string
 * @property $dt_blacklist integer UTC timestamp
 * @property $survey_blacklist integer survey id
 * @property integer $suspicious SuspiciousStatus
 * @property RecruitmentProfile $recruitmentProfile
 */
class Respondent extends ActiveRecord
{
    const RMSID_LENGTH = 7;
    const FAILED_TRYINGS_LIMIT = 5;

    public $isTest = false;

    public static function tableName()
    {
        return 'respondent';
    }

    public function rules()
    {
        return [
            [['device_id', 'fingerprint_id'], 'string', 'max' => 255],
            [['id', 'failed_tryings', 'survey_blacklist'], 'integer'],
            [['rmsid'], 'string', 'max' => self::RMSID_LENGTH],
            [['status'], 'in', 'range' => [RespondentStatus::ACTIVE, RespondentStatus::DISQUALIFIED]],
            [['rmsid', 'fingerprint_id', 'status'], 'required'],
            [[  'device_atlas', 'additional', 'language', 'traffic_source',
                'device_vendor', 'device_model', 'device_marketing_name', 'device_manufacturer',
                'device_year_released', 'os_vendor', 'os_name', 'os_family',
                'os_version', 'registered_at', 'last_seen_at', 'ip',
                'browser', 'isTest', 'suspicious'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
        ];
    }

    /**
     * @todo code similar with Survey, move to BaseClass
     * @param bool $runValidation
     * @param null $attributeNames
     * @return bool
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        if ($this->isNewRecord && is_null($this->rmsid)) {
            /** @todo check rmsid uniqueness before save & generate the new one if required */
            $this->rmsid = $this->createRmsid(self::RMSID_LENGTH);
            $this->failed_tryings = 0;
            $this->status = RespondentStatus::ACTIVE;
            $this->additional = '[]';
            $this->registered_at = \app\components\AppHelper::timeUtc();
        }

        return parent::save($runValidation, $attributeNames);
    }

    /**
     * @todo duplicated code with Survey, move to BaseClass
     * @param int $length
     * @return string
     */
    protected function createRmsid($length = 5)
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        do {
            $result = '';

            for ($i = 0; $i < $length; ++$i) {
                $result .= $chars{rand(0, strlen($chars) - 1)};
            }

            if ($this->isTest) {
                $result{0} = '@';
                $result{1} = '0';
                $result{2} = '0';
            }
        } while(self::findOne(['rmsid' => $result]));

        return $result;
    }

    public function cantSurvey()
    {
        return $this->status == RespondentStatus::DISQUALIFIED;
    }

    public function seen()
    {
        $this->last_seen_at = \app\components\AppHelper::timeUtc();

        $this->save();
    }

    public function disqualify($surveyId)
    {
        ++$this->failed_tryings;

        if ($this->failed_tryings >= self::FAILED_TRYINGS_LIMIT) {
            return $this->block(SuspiciousStatus::DISQ_MAXAMOUNT_ACHIVED, $surveyId);
        }

        $this->save();

        return false;
    }

    public function block($suspicious = null, $surveyId = null)
    {
        if ($surveyId) {
            $this->survey_blacklist = $surveyId;
        }

        if ($suspicious) {
            $this->suspicious = $suspicious;
        }

        return $this->save();
    }

    public function addToBlacklist($suspiciousCode, $surveyId)
    {
        $this->status = RespondentStatus::DISQUALIFIED;
        $this->dt_blacklist = AppHelper::timeUtc();
        $this->survey_blacklist = $surveyId;
        $this->suspicious = $suspiciousCode;

        return $this->save();
    }

    public function afterFind()
    {
        parent::afterFind();

        $this->isTest = '@' === $this->rmsid{0};
    }

    public function getDeviceAtlasProp($prop, $default = null)
    {
        $deviceAtlas = Json::decode($this->device_atlas);

        return ArrayHelper::getValue($deviceAtlas, $prop, $default);
    }

    /**
     * @return bool
     */
    public function isSuspicious()
    {
        return $this->suspicious > 0;
    }

    /**
     * @return bool
     */
    public function isBlocked()
    {
        return !!$this->dt_blacklist;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSurvey()
    {
        return $this->hasOne(Survey::className(), ['id' => 'survey_blacklist']);
    }

    /**
     * @param Survey $initialSurvey
     * @return RecruitmentProfile|null
     */
    public function getRecruitmentProfile($initialSurvey = null)
    {
        /** @var RecruitmentProfile $profile */
        $profile = $this->hasOne(RecruitmentProfile::class, ['respondent_id' => 'id'])->one();
        if (!$profile) {
            $profile = RecruitmentProfile::createRespondentProfile($this, $initialSurvey);
        }

        return $profile;
    }
}
