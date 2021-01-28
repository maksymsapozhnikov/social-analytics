<?php
namespace app\controllers;

use dektrium\user\controllers\RegistrationController as BaseRegistrationController;
use dektrium\user\models\Profile;
use dektrium\user\models\ResendForm;
use dektrium\user\models\User;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

class DektriumRegistrationController extends BaseRegistrationController
{
    use UserLanguageTrait;

    public $layout = '@app/views/layouts/public/index';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    ['allow' => true, 'actions' => ['register', 'connect'], 'roles' => ['?']],
                    ['allow' => true, 'actions' => ['confirm', 'resend'], 'roles' => ['?', '@']],
                    ['allow' => true, 'actions' => ['success'], 'roles' => ['?']],
                ],
            ],
        ];
    }

    public function actionSuccess()
    {
        return $this->render('@app/views/public/dektrium/registration/success');
    }

    /**
     * @inheritdoc
     */
    public function actionConfirm($id, $code)
    {
        $user = $this->finder->findUserById($id);

        if ($user === null || $this->module->enableConfirmation == false) {
            throw new NotFoundHttpException();
        }

        $event = $this->getUserEvent($user);

        $this->trigger(self::EVENT_BEFORE_CONFIRM, $event);

        $title = \Yii::t('user', 'Account confirmation');
        $message = $user->attemptConfirmation($code) ?
                    \Yii::t('user', 'Thank you, registration is now complete.') :
                    \Yii::t('user', 'The confirmation link is invalid or expired. Please try requesting a new one.');

        $this->trigger(self::EVENT_AFTER_CONFIRM, $event);

        return $this->render('@app/views/public/dektrium/common', [
            'title'  => $title,
            'message' => $message,
        ]);
    }

    public function actionResend()
    {
        if ($this->module->enableConfirmation == false) {
            throw new NotFoundHttpException();
        }

        /** @var ResendForm $model */
        $model = \Yii::createObject(ResendForm::className());
        $event = $this->getFormEvent($model);

        $this->trigger(self::EVENT_BEFORE_RESEND, $event);

        $this->performAjaxValidation($model);

        if ($model->load(\Yii::$app->request->post()) && $model->resend()) {
            $this->trigger(self::EVENT_AFTER_RESEND, $event);

            return $this->render('@app/views/public/dektrium/common', [
                'title'  => \Yii::t('user', 'Request new confirmation message'),
                'message' => \Yii::t('user', 'A new confirmation link has been sent'),
            ]);
        }

        return $this->render('resend', [
            'model' => $model,
        ]);
    }

    /** @todo refactoring required */
    public function actionConnect($code)
    {
        $account = $this->finder->findAccount()->byCode($code)->one();

        if ($account === null || $account->getIsConnected()) {
            throw new NotFoundHttpException();
        }

        /** @var User $user */
        $user = \Yii::createObject([
            'class'    => User::className(),
            'scenario' => 'connect',
            'username' => $account->email,
            'email'    => $account->email,
        ]);

        $event = $this->getConnectEvent($account, $user);
        $this->trigger(self::EVENT_BEFORE_CONNECT, $event);

        $user->create();

        /** @var \StdClass $profileData */
        $profileData = $account->decodedData;

        /** @var Profile $profile */
        $user->profile->setAttributes([
            'name' => ArrayHelper::getValue($profileData, 'first_name', ''),
            'last_name' => ArrayHelper::getValue($profileData, 'last_name', ''),
        ]);
        $user->profile->save();

        $account->connect($user);

        $this->trigger(self::EVENT_AFTER_CONNECT, $event);
        \Yii::$app->user->login($user, $this->module->rememberFor);

        return $this->goBack();
    }
}
