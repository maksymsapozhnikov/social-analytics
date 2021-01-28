<?php
namespace app\components;

use app\components\helpers\TranslateMessage;
use app\models\EmailCache;
use NeverBounce\Auth as NBAuth;
use NeverBounce\Object\VerificationObject;
use NeverBounce\Single as NBClient;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\ServerErrorHttpException;

/**
 * Class EmailChecker
 * @package app\components
 */
class EmailChecker extends Component
{
    public static $nbAllowed = VerificationObject::VALID;

    /**
     * @param string $email
     * @return array
     */
    public static function getDetails($email)
    {
        $result = EmailCache::findOne(['email' => $email]) ?? static::checkEmail($email);

        return static::cacheToResult($result);
    }

    /**
     * @param EmailCache $cache
     * @return array
     */
    protected static function cacheToResult(EmailCache $cache)
    {
        return [
            'email' => $cache->email,
            'valid' => $cache->valid,
            'score' => $cache->score,
        ];
    }

    /**
     * @param string $email
     * @return EmailCache
     * @throws
     */
    protected static function checkEmail($email)
    {
//        $emailCheck = static::sendRequestMailboxlayer($email);

        $emailCheck = ['email' => $email, 'valid' => false, 'score' => 0, 'format_valid' => false];
        if (static::checkEmailNeverbounce($email)) {
            $emailCheck = ['email' => $email, 'valid' => true, 'score' => 1, 'format_valid' => true];
        }

        return static::saveCache($emailCheck);
    }

    /**
     * @param array $emailCheck
     * @return EmailCache
     */
    protected static function saveCache($emailCheck)
    {
        $emailCache = new EmailCache();
        $emailCache->load($emailCheck);
        $emailCache->save();

        return $emailCache;
    }

    /**
     * @param $email
     * @return boolean
     * @throws ServerErrorHttpException
     */
    public static function checkEmailNeverbounce($email)
    {
        $testDomain = explode('@', $email);
        $whiteList = AppHelper::param('neverbounce.whiteList', []);
        if (ArrayHelper::isIn($testDomain[1], $whiteList)) {
            return true;
        }

        try {
            $apiKey = AppHelper::param('neverbounce.apiKey');
            NBAuth::setApiKey($apiKey);
            $verificationObject = NBClient::check($email, true, true);
        } catch (\Throwable $e) {
            throw new ServerErrorHttpException(TranslateMessage::t('user', 'An error occurred. Please try again.'));
        }

        if ($verificationObject->not(static::$nbAllowed)) {
            return false;
        }

        return true;
    }

    /**
     * Checks email using Mailboxlayer
     *
     * @param string $email
     * @return mixed
     */
    protected static function sendRequestMailboxlayer($email)
    {
        $accessKey = AppHelper::param('mailboxlayer.key');
        $url = 'http://apilayer.net/api/check?access_key=' . $accessKey . '&email=' . $email;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $json = curl_exec($ch);
        curl_close($ch);

        return Json::decode($json);
    }
}
