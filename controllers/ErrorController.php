<?php
namespace app\controllers;

use yii\web\Controller;
use app\components\helpers\TranslateMessage;

/**
 * Class ErrorController
 * @package app\controllers
 */
class ErrorController extends Controller
{
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
     * Displays the page Survey finished
     * @return string
     */
    public function actionFinished()
    {
        $this->layout = 'error';

        return $this->render('/site/prepared', [
            'title' => TranslateMessage::t('survey-process', 'Survey closed'),
            'html' => TranslateMessage::t('survey-process', 'Thank you for joining our survey, but unfortunately it is now closed. Please look out for other surveys in the future.'),
        ]);
    }

    public function actionDsq()
    {
        $this->layout = 'error';

        return $this->render('/site/prepared', [
            'title' => TranslateMessage::t('survey-process', 'You don\'t qualify for this project'),
            'html' => TranslateMessage::t('survey-process', 'Thanks! Unfortunately, you don\'t qualify for this project. However, look out for our next project, it\'s coming soon!'),
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
