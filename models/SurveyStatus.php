<?php
namespace app\models;

/**
 * Class SurveyStatus
 * @package app\models
 */
class SurveyStatus
{
    const ACTIVE = 1;
    const INACTIVE = 2;
    const TRASH = 3;

    const TITLES = [
        self::ACTIVE => 'Active',
        self::INACTIVE => 'Inactive',
        self::TRASH => 'Trash',
    ];

    const UNDEFINED = 'Unknown';

    /**
     * @param integer $id
     * @return string
     */
    public static function getTitle($id)
    {
        $titles = self::TITLES;

        return isset($titles[$id]) ? $titles[$id] : self::UNDEFINED;
    }
}
