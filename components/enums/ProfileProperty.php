<?php
namespace app\components\enums;

use yii2mod\enum\helpers\BaseEnum;

/**
 * Class ProfileProperty
 * @package app\components\enums
 */
class ProfileProperty extends BaseEnum
{
    const FILLED = 'profile_filled';
    const RMSID = 'rmsid';
    const COMPLETED = 'completed';
    const FOCUS_ANSWER = 'is_focused';
    const SOURCE = 'source';
    const CONSENT = 'consent';
    const EMAIL = 'email';

    const GENDER = 'gender';
    const GENDER_CHECK = 'gender_check';
    const AGE = 'age';
    const MARTIAL = 'martial';
    const YOB = 'yob';
    const DOB = 'dob';
    const HAVE_CHILDREN = 'have_children';
    const PEOPLE = 'people';
    const AGE_CALCULATED = 'age_calculated';
    const AGE_DIFFERENCE = 'age_difference';
    const COUNTRY = 'country';
    const IP = 'ip';
    const SURVEY_RMSID = 'survey_rmsid';
    const LANG = 'lang';
    const PORTAL_RMSID = 'portal_rmsid';

    protected static $list = [
        self::CONSENT => 'Consent',
        self::EMAIL => 'Email',
        self::FILLED => 'Profile filled',
        self::RMSID => 'RMSID',
        self::COMPLETED => 'Completed',
        self::SOURCE => 'Source',
        self::GENDER => 'Gender',
        self::GENDER_CHECK => 'Gender check',
        self::AGE => 'Age',
        self::YOB => 'Year of birth',
        self::DOB => 'Date of birth',
        self::MARTIAL => 'Martial status',
        self::HAVE_CHILDREN => 'Do you have any children',
        self::PEOPLE => 'People live in household',
        self::FOCUS_ANSWER => 'Is being focused',
        self::AGE_CALCULATED => 'Age calculated',
        self::AGE_DIFFERENCE => 'Age difference between calculated and provided',
        self::COUNTRY => 'Country',
        self::IP => 'IP',
        self::SURVEY_RMSID => 'Survey RMSID',
        self::LANG => 'Language',
        self::PORTAL_RMSID => 'Portal Respondent RMSID',
    ];
}