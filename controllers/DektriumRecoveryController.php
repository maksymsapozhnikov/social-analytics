<?php
namespace app\controllers;

use dektrium\user\models\RecoveryForm;
use dektrium\user\controllers\RecoveryController as BaseRecoveryController;
use dektrium\user\models\Token;
use yii\web\NotFoundHttpException;

class DektriumRecoveryController extends BaseRecoveryController
{
    use UserLanguageTrait;

    public function actionRequest()
    {
        if (!$this->module->enablePasswordRecovery) {
            throw new NotFoundHttpException();
        }

        /** @var RecoveryForm $model */
        $model = \Yii::createObject([
            'class'    => RecoveryForm::className(),
            'scenario' => RecoveryForm::SCENARIO_REQUEST,
        ]);
        $event = $this->getFormEvent($model);

        $this->trigger(self::EVENT_BEFORE_REQUEST, $event);

        $this->performAjaxValidation($model);

        if ($model->load(\Yii::$app->request->post()) && $model->sendRecoveryMessage()) {
            $this->trigger(self::EVENT_AFTER_REQUEST, $event);

            return $this->render('@app/views/public/dektrium/common', [
                'title'  => \Yii::t('user', 'Recover your password'),
                'message' => \Yii::t('user', 'Recovery message sent'),
            ]);
        }

        return $this->render('request', [
            'model' => $model,
        ]);
    }

    public function actionReset($id, $code)
    {
        if (!$this->module->enablePasswordRecovery) {
            throw new NotFoundHttpException();
        }

        /** @var Token $token */
        $token = $this->finder->findToken(['user_id' => $id, 'code' => $code, 'type' => Token::TYPE_RECOVERY])->one();
        $event = $this->getResetPasswordEvent($token);

        $this->trigger(self::EVENT_BEFORE_TOKEN_VALIDATE, $event);

        if ($token === null || $token->isExpired || $token->user === null) {
            $this->trigger(self::EVENT_AFTER_TOKEN_VALIDATE, $event);

            return $this->render('@app/views/public/dektrium/common', [
                'title'  => \Yii::t('user', 'Invalid or expired link'),
                'message' => \Yii::t('user', 'Recovery link is invalid or expired. Please try requesting a new one.'),
            ]);
        }

        /** @var RecoveryForm $model */
        $model = \Yii::createObject([
            'class'    => RecoveryForm::className(),
            'scenario' => RecoveryForm::SCENARIO_RESET,
        ]);
        $event->setForm($model);

        $this->performAjaxValidation($model);
        $this->trigger(self::EVENT_BEFORE_RESET, $event);

        if ($model->load(\Yii::$app->getRequest()->post()) && $model->resetPassword($token)) {
            $this->trigger(self::EVENT_AFTER_RESET, $event);

            return $this->render('@app/views/public/dektrium/common', [
                'title'  => \Yii::t('user', 'Password has been changed'),
                'message' => \Yii::t('user', 'Password has been changed'),
            ]);
        }

        return $this->render('reset', [
            'model' => $model,
        ]);
    }
}
