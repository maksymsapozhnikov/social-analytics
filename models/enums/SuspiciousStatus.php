<?php
namespace app\models\enums;

/**
 * Class SuspiciousStatus
 * @package app\models\enums
 */
class SuspiciousStatus extends BaseEnum
{
    const LEGAL = 0;
    const DISQ_MAXAMOUNT_ACHIVED = 5;
    const ID_DUPLICATED = 10;
    const PHONE_DUPLICATED = 20;
    const ID_TOOKPART_SURVEY = 30;
    const AFFSUB_USED_TWICE = 40;
    const IS_ROBOT = 50;
    const IP_HAS_FINISHED_SURVEY = 60;
    const FP_HAS_FINISHED_SURVEY = 65;
    const IP_BLACKLISTED = 70;
    const STRICT_FP_CHECK = 80;
    const SURVEYBOT_ID_CHANGED = 90;
    const SURVEYBOT_ID_ALREADY_USED = 100;
    const RECRUITMENT_DISQ = 101;

    public static $titles = [
        self::LEGAL => 'No suspicious',
        self::DISQ_MAXAMOUNT_ACHIVED => 'Maximum disqualification amount achieved.',
        self::ID_DUPLICATED => 'Suspicious respondent',
        self::ID_TOOKPART_SURVEY => 'Suspicious respondent',
        self::PHONE_DUPLICATED => 'Phone duplicate',
        self::AFFSUB_USED_TWICE => 'Tried to use the same affsub twice',
        self::IS_ROBOT => 'Respondent is a robot (by DeviceAtlas)',
        self::IP_HAS_FINISHED_SURVEY => 'IP has finished this survey 24 hours',
        self::FP_HAS_FINISHED_SURVEY => 'Fingerprint has finished this survey 48 hours',
        self::IP_BLACKLISTED => 'IP address is blacklisted',
        self::STRICT_FP_CHECK => 'Suspicious fingerprint blocked',
        self::SURVEYBOT_ID_CHANGED => 'Respondent SurveyBot ID changed',
        self::SURVEYBOT_ID_ALREADY_USED => 'Respondent SurveyBot ID used by someone else',
        self::RECRUITMENT_DISQ => 'Recruitment disqualification',
    ];
}