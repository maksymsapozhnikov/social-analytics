<?php
namespace app\components;

use app\components\portal\PortalHelper;
use yii\validators\Validator;

/**
 * Class PortalRegistrationEmail
 * @package app\components
 *
 * @property string $defaultMessage
 */
class PortalValidationEmail extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function validateValue($value)
    {
        return PortalHelper::validateEmail($value);
    }
}