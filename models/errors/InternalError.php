<?php
namespace app\models\errors;

use yii\base\BaseObject;

class InternalError extends BaseObject implements ITgmMobiError
{
    /** @var string */
    public $message;
}