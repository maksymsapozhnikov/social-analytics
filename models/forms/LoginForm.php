<?php
namespace app\models\forms;

use app\components\helpers\TranslateMessage;
use dektrium\user\models\LoginForm as BaseLoginForm;
use himiklab\yii2\recaptcha\ReCaptchaValidator;

class LoginForm extends BaseLoginForm
{
    public $recaptcha;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = [
            ['recaptcha'],
            ReCaptchaValidator::class,
            'secret' => \Yii::$app->reCaptcha->secret,
            'message' => TranslateMessage::t('app', 'Please confirm that you are not a robot'),
        ];

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();

        $labels['login'] = \app\components\helpers\TranslateMessage::t('user', 'Email');

        return $labels;
    }
}
