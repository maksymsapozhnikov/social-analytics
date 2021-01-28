<?php
namespace app\components\enums;

use app\models\enums\BaseEnum;

class SessionStatusEnum extends BaseEnum
{
    /** Respondent requested alias page, redirection */
    const ST_ALIAS = 1;

    /** Respondent clicked on banner and opened /sa or /survey link with preloader */
    const ST_PRELOADING = 2;

    /** Identified */
    const ST_IDENTIFIED = 3;

    /** Respondent has been redirected to SurveyGizmo */
    const ST_SURVEYGIZMO = 4;

    /** Respondent has been blocked on some stage to SurveyGizmo */
    const ST_BLOCKED = 5;

    /** SG API received any request, but the survey has not been finished yet */
    const ST_PROGRESS = 6;

    /** "Standard" response statuses */
    const ST_SCREENED_OUT = 7;
    const ST_DISQUALIFIED = 8;
    const ST_FINISHED = 9;

    const ST_RECRUITMENT = 10;

    /** @var array statuses list */
    public static $titles = [
        self::ST_ALIAS => 'Alias requested',
        self::ST_PRELOADING => 'Preloading screen',
        self::ST_IDENTIFIED => 'Respondent identified',
        self::ST_SURVEYGIZMO => 'Redirected to SurveyGizmo',
        self::ST_BLOCKED => 'Respondent blocked',
        self::ST_PROGRESS => 'In progress',
        self::ST_SCREENED_OUT => 'Screened out',
        self::ST_DISQUALIFIED => 'Disqualified',
        self::ST_FINISHED => 'Finished',
        self::ST_RECRUITMENT => 'Recruitment survey',
    ];
}
