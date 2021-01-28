<?php
namespace app\controllers;

use app\components\portal\PortalHelper;
use app\components\RespondentIdentity;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;

/**
 * Class PortalController
 * @package app\controllers
 */
class PortalController extends Controller
{
    use UserLanguageTrait;

    public $layout = 'portal';

    public $enableCsrfValidation = false;

    /**
     * Displays a loader.
     */
    public function actionIndex()
    {
        /** @var RespondentIdentity $identity */
        $identity = \Yii::$app->respondentIdentity;

        $profile = $identity->respondent->recruitmentProfile;
        if (!$profile->isFilled) {
            /** @todo detect survey and redirect to rcs/{rmsid} */
            return false;
        }

        return $this->render('index');
    }

    /**
     * Registers respondent to the Portal.
     *
     * @return bool|string
     * @throws
     */
    public function actionRegister()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        /** @var RespondentIdentity $identity */
        $identity = \Yii::$app->respondentIdentity;
        $profile = $identity->respondent->recruitmentProfile;
        if (!PortalHelper::registerAccount($profile)) {
            throw new UnprocessableEntityHttpException('The email address is already in use.');
        }

        return '';
    }

    /**
     * Returns a survey url.
     *
     * @return false|string
     * @throws
     */
    public function actionSurvey()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        /** @var RespondentIdentity $identity */
        $identity = \Yii::$app->respondentIdentity;
        if (!$identity) {
            throw new BadRequestHttpException();
        }

        $surveyUrl = PortalHelper::getSurvey($identity->respondent->recruitmentProfile);
        if ($surveyUrl === false) {
            throw new NotFoundHttpException();
        }

        return $surveyUrl;
    }
}