<?php
namespace app\components\exceptions;

use yii\base\Exception;

class ValidationError extends Exception
{
    public function getName()
    {
        return 'Validation Error';
    }

}
