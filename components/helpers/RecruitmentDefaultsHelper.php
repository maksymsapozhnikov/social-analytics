<?php
namespace app\components\helpers;

use app\components\enums\ProfileProperty;
use app\components\enums\TrafficSource;
use app\components\RespondentIdentity;
use app\models\RecruitmentProfile;
use app\models\RespondentSurvey;
use app\models\Survey;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class RecruitmentDefaultsHelper
 * @package app\components\helpers
 */
class RecruitmentDefaultsHelper
{
    /** @var RespondentSurvey */
    public static $respondentSurvey;

    /**
     * Loads initial.
     */
    protected static function init()
    {
        self::$respondentSurvey = null;

        /** @var RespondentIdentity $identity */
        $identity = Yii::$app->respondentIdentity;
        $respondent = $identity->respondent;

        /** @var Survey $survey */
        $survey = isset(Yii::$app->controller->survey) ? Yii::$app->controller->survey : null;

        if ($survey) {
            self::$respondentSurvey = RespondentSurvey::findByRmsids($respondent->rmsid, $survey->rmsid);
        }
    }

    /**
     * @param RecruitmentProfile $profile
     * @return mixed|null
     */
    public static function getDefaultsGender(RecruitmentProfile $profile)
    {
        static::init();
        if (!self::$respondentSurvey) {
            return null;
        }

        $genderList = [
            TrafficSource::CINT => [1 => 0, 2 => 1],
            TrafficSource::TGM => [1 => 0, 2 => 1],
            TrafficSource::LUCID => [1 => 0, 2 => 1],
        ];
        $source = static::$respondentSurvey->respondent->traffic_source;
        $gender = self::$respondentSurvey->getUriParam('gender');

        return ArrayHelper::getValue($genderList, "{$source}.{$gender}", null);
    }

    /**
     * @param RecruitmentProfile $profile
     * @return null|int
     */
    public static function getDefaultsAge(RecruitmentProfile $profile)
    {
        static::init();
        if (!self::$respondentSurvey) {
            return null;
        }

        $age = self::$respondentSurvey->getUriParam('age') ?: null;

        return $age;
    }

    /**
     * @param RecruitmentProfile $profile
     * @return string|null
     */
    public static function getDefaultsChildren(RecruitmentProfile $profile)
    {
        return static::isChild($profile) ? 'No' : null;
    }

    /**
     * @param RecruitmentProfile $profile
     * @return int|null
     */
    public static function getDefaultsMartial(RecruitmentProfile $profile)
    {
        return static::isChild($profile) ? 2 : null;
    }

    /**
     * @param RecruitmentProfile $profile
     * @return int
     */
    public static function getDefaultsAgeCalculated(RecruitmentProfile $profile)
    {
        try {
            $dob = $profile->content[ProfileProperty::DOB];
            $uDob = \DateTime::createFromFormat('d.m.Y', $dob, new \DateTimeZone('UTC'));
            $now = new \DateTime();

            return $uDob->diff($now)->y;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * @param RecruitmentProfile $profile
     * @return int
     */
    public static function getDefaultsAgeDifference(RecruitmentProfile $profile)
    {
        try {
            $dob = $profile->content[ProfileProperty::DOB];
            $uDob = \DateTime::createFromFormat('d.m.Y', $dob, new \DateTimeZone('UTC'));
            $now = new \DateTime();

            $ageCalculated = $uDob->diff($now)->y;
            $ageProvided = (int)$profile->content[ProfileProperty::AGE];

            return abs($ageCalculated - $ageProvided);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * @param RecruitmentProfile $profile
     * @return bool
     */
    protected static function isChild(RecruitmentProfile $profile)
    {
        $age = intval($profile->content[ProfileProperty::AGE]) ?: 0;

        return $age < 18;
    }
}