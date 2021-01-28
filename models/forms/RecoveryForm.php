<?php
namespace app\models\forms;

use dektrium\user\models\RecoveryForm as BaseRecoveryForm;
use himiklab\yii2\recaptcha\ReCaptchaValidator;
use app\components\helpers\TranslateMessage;

class RecoveryForm extends BaseRecoveryForm
{
    public $recaptcha;
    public $confirm_password;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();

        $extended = [
            [
                ['recaptcha'],
                ReCaptchaValidator::class,
                'secret' => \Yii::$app->reCaptcha->secret,
                'message' => TranslateMessage::t('app', 'Please confirm that you are not a robot'),
            ],
            ['confirm_password', 'required'],
            ['confirm_password', 'validateConfirmPassword'],
        ];

        return array_merge($rules, $extended);
    }

    public function validateConfirmPassword($attribute, $params, $validator)
    {
        if ($this->password !== $this->confirm_password) {
            $this->addError($attribute, \Yii::t('app', 'Password and confirmation do not match.'));
        }
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            self::SCENARIO_REQUEST => ['email', 'recaptcha'],
            self::SCENARIO_RESET => ['password', 'confirm_password', 'recaptcha'],
        ];
    }
}
