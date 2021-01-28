<?php
namespace app\components\exceptions;

use yii\base\Exception;

/**
 * Class DisqualifiedException
 * @package app\components\exceptions
 */
class DisqualifiedException extends Exception
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Disqualified';
    }
}