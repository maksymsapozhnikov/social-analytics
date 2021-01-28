<?php
namespace app\models;

/**
 * Class RespondentSurveyStatus
 * @package app\models
 */
class RespondentSurveyStatus
{
    const ALL = 0;
    const ACTIVE = 1;
    const SCREENED_OUT = 2;
    const DISQUALIFIED = 3;
    const FINISHED = 4;

    const TITLES = [
        self::ACTIVE => 'In Progress',
        self::SCREENED_OUT => 'Screened out',
        self::DISQUALIFIED => 'Disqualified',
        self::FINISHED => 'Finished',
    ];

    const SHORT_TITLES = [
        self::ACTIVE => 'In Progress',
        self::SCREENED_OUT => 'SCR OUT',
        self::DISQUALIFIED => 'DSQ',
        self::FINISHED => 'Finished',
    ];

    const UNDEFINED = 'undefined';

    public static function getTitle($id)
    {
        $titles = self::TITLES;

        return isset($titles[$id]) ? $titles[$id] : self::UNDEFINED;
    }

    public static function getShortTitle($id)
    {
        $titles = self::SHORT_TITLES;

        return isset($titles[$id]) ? $titles[$id] : self::UNDEFINED;
    }
}
