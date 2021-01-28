<?php
namespace app\controllers;

use app\components\helpers\TranslateMessage;
use yii\web\Controller;

/**
 * Class SController
 * @package app\controllers
 */
class SController extends Controller
{
    public $layout = 'error';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        $rms = $this->getRms();
        $lang = null;
        if (isset($rms['uri'])) {
            $lang = $this->getUriParam($rms['uri'], 'lang');
        }

        \Yii::$app->language = $lang ?: \Yii::$app->language;
    }

    /**
     * @return string
     */
    public function actionDs()
    {
        return $this->render('/site/prepared', [
            'title' => TranslateMessage::t('survey-process', 'You don\'t qualify for this project'),
            'html' => TranslateMessage::t('survey-process', 'Thanks! Unfortunately, you don\'t qualify for this project. However, look out for our next project, it\'s coming soon!'),
        ]);
    }

    /**
     * @return string
     */
    public function actionSc()
    {
        $session = \Yii::$app->session;
        $urlEnd = $session->get('end_url');

        return $this->render('/site/prepared', [
            'title' => TranslateMessage::t('survey-process', 'You don\'t qualify for this project'),
            'html' => TranslateMessage::t('survey-process', 'Thanks! Unfortunately, you don\'t qualify for this project. However, look out for our next project, it\'s coming soon!'),
            'join' => TranslateMessage::t('survey-process', 'Please join our panel'),
            'urlEnd' => $urlEnd,
        ]);
    }

    /**
     * @return string
     */
    public function actionFn()
    {
        $session = \Yii::$app->session;
        $urlEnd = $session->get('end_url');

        return $this->render('/site/prepared', [
            'title' => TranslateMessage::t('survey-process', 'Good job!'),
            'html' => TranslateMessage::t('survey-process', 'This is the last screen - good job! Your reward is on the way to you!'),
            'join' => TranslateMessage::t('survey-process', 'Please join our panel'),
            'urlEnd' => $urlEnd,
        ]);
    }

    /**
     * @param $uri
     * @param $param
     * @return mixed|string
     */
    protected function getUriParam($uri, $param)
    {
        $parts = parse_url($uri);

        if (isset($parts['query'])) {
            parse_str($parts['query'], $query);
        } else {
            $query = [];
        }

        return isset($query[$param]) ? $query[$param] : '-';
    }

    /**
     * @return array|mixed
     */
    protected function getRms()
    {
        $rms = [];

        if (isset($_COOKIE['_rms'])) {
            try {
                $rms = \yii\helpers\Json::decode(base64_decode($_COOKIE['_rms']));
            } catch (\Exception $exception) {
                $rms = [];
            }
        }

        return $rms;
    }
}