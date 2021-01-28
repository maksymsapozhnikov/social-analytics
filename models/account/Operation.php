<?php
namespace app\models\account;

class Operation
{
    const INCENTIVE = 1;
    const TRANSFER = 2;

    public static function getName($operation)
    {
        $names = [
            self::INCENTIVE => 'Survey incentive',
            self::TRANSFER => 'Top ups transfer',
        ];

        $defaultName = 'Unknown';

        return isset($names[$operation]) ? $names[$operation] : $defaultName;
    }

    public static function getType($operation)
    {
        $names = [
            self::INCENTIVE => +1,
            self::TRANSFER => -1,
        ];

        $defaultName = 0;

        return isset($names[$operation]) ? $names[$operation] : $defaultName;
    }
}
