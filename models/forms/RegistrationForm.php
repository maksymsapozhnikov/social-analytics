<?php
namespace app\models\forms;

use app\models\Profile;
use dektrium\user\models\RegistrationForm as BaseRegistrationForm;
use dektrium\user\models\User;
use himiklab\yii2\recaptcha\ReCaptchaValidator;
use app\components\helpers\TranslateMessage;

class RegistrationForm extends BaseRegistrationForm
{
    public $name;
    public $last_name;
    public $recaptcha;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = [['name', 'last_name'], 'required'];
        $rules[] = [['name', 'last_name'], 'string', 'max' => 255];
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

        $labels['name'] = \Yii::t('app', 'First name');
        $labels['last_name'] = \Yii::t('app', 'Last name');


        return $labels;
    }

    /**
     * @inheritdoc
     */
    public function load($data, $formName = null)
    {
        $result = parent::load($data, $formName);

        $this->username = $this->email;

        return $result;
    }


    /**
     * @inheritdoc
     */
    public function loadAttributes(User $user)
    {
        $user->setAttributes([
            'email'    => $this->email,
            'username' => $this->username,
            'password' => $this->password,
        ]);

        /** @var Profile $profile */
        $profile = \Yii::createObject(Profile::className());

        $profile->setAttributes([
            'name' => $this->name,
            'last_name' => $this->last_name,
        ]);

        $user->setProfile($profile);
    }
}
