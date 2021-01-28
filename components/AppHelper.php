<?php
/**
 * Application Helper Class
 *
 */

namespace app\components;

use app\components\helpers\TranslateMessage;
use app\models\Info;
use yii\bootstrap\Nav;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * Class AppHelper
 * @package app\components
 */
class AppHelper
{
    /**
     * Returns environment title prefix
     *
     * @return string
     */
    public static function environmentPreffix()
    {
        $preffix = \Yii::$app->params['environment']['title-prefix'];

        return $preffix ? "[{$preffix}] " : "";
    }

    /**
     * Returns environment badge for NavBar
     *
     * @return string
     * @throws
     */
    public static function environmentBadge()
    {
        $preffix = \Yii::$app->params['environment']['title-prefix'];

        if ($preffix) {
            return Nav::widget([
                'encodeLabels' => false,
                'items' => [
                    [
                        'label' => $preffix,
                        'url' => null,
                    ],
                ],
                'options' => [
                    'class' => 'navbar-nav btn-danger',
                ],
            ]);
        }

        return '';
    }

    public static function total($models, $attributes)
    {
        $total=0;

        foreach($models as $item) {
            $total+= $item->{$attributes};
        }

        return $total;
    }

    public static function timeUtc($time = null)
    {
        return ($time ?: time());
    }

    public static function balance()
    {
        return \Yii::$app->formatter->asCurrency(
            Info::value(Info::TRANSFERTO_BALANCE),
            Info::value(Info::TRANSFERTO_CURRENCY)
        );
    }

    public static function getLanguagesItem()
    {
        $languages = TranslateMessage::getLanguages('native_name');

        /** @todo improve default language detection */
        $defaultLanguage = \Yii::$app->request->getPreferredLanguage(array_keys($languages));

        $userLanguage = ArrayHelper::getValue($languages, \Yii::$app->language, $languages[$defaultLanguage]);

        ArrayHelper::removeValue($languages, $userLanguage);

        $item = [
            'label' => $userLanguage,
            'items' => [

            ],
        ];

        ksort($languages);

        foreach($languages as $la => $language) {
            $item['items'][] = [
                'label' => $language,
                'url' => Url::to(['/', 'lang' => $la]),
            ];
        }

        return $item;
    }

    /**
     * @param string $ip
     * @return int
     */
    public static function ip2long($ip)
    {
        $ip = explode(".", $ip);

        return ($ip[3] + $ip[2] * 256 + $ip[1] * 256 * 256 + $ip[0] * 256 * 256 * 256);
    }

    /**
     * Shuffles an associative array.
     *
     * @param array $array an array to shuffle
     * @return bool
     */
    public static function shuffleAssoc(array &$array)
    {
        $keys = array_keys($array);

        shuffle($keys);

        foreach($keys as $key) {
            $new[$key] = $array[$key];
        }

        $array = $new;

        return true;
    }

    /**
     * @param array $parsedUrl
     * @return string
     */
    public static function unparseUrl($parsedUrl)
    {
        $scheme   = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '';
        $host     = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
        $port     = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';
        $user     = isset($parsedUrl['user']) ? $parsedUrl['user'] : '';
        $pass     = isset($parsedUrl['pass']) ? ':' . $parsedUrl['pass']  : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';
        $query    = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';
        $fragment = isset($parsedUrl['fragment']) ? '#' . $parsedUrl['fragment'] : '';

        return "$scheme$user$pass$host$port$path$query$fragment";
    }

    /**
     * @param string $paramName
     * @param mixed $default
     * @return mixed
     */
    public static function param($paramName, $default = null)
    {
        return ArrayHelper::getValue(\Yii::$app->params, $paramName, $default);
    }
}
