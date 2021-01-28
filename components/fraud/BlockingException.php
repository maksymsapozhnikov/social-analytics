<?php
namespace app\components\fraud;

use app\models\enums\SuspiciousStatus;
use yii\base\Exception;

/**
 * Class BlockingException
 * Exception to be thrown when respondent/response shoul be blocked.
 * @package app\components\fraud
 */
class BlockingException extends Exception
{
    public function __construct($code = 0)
    {
        $message = SuspiciousStatus::getTitle($code);

        parent::__construct($message, $code, null);
    }


    public function getName()
    {
        return 'Blocking exception';
    }

}
