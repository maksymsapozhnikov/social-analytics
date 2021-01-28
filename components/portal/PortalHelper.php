<?php

namespace app\components\portal;

use app\components\enums\ProfileProperty;
use app\components\helpers\TranslateMessage;
use app\models\enums\TranslationCategoryEnum;
use app\models\RecruitmentProfile;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\httpclient\Client;
use yii\web\ServerErrorHttpException;

class PortalHelper
{
    const PORTAL_API_URL = 'https://gg.tgm.cloud/tgm-api';
    const PORTAL_API_KEY = '89f2e26a-4df7-41fb-8814-c4b82a36d136';

    /**
     * @param string $url
     * @param array $data
     * @return \yii\httpclient\Response
     * @throws
     */
    protected static function sendRequest($url, $data, $method = 'POST')
    {
        $client = new Client();
        $request = $client->createRequest();

        $request->setMethod($method)
            ->setHeaders([
                'X-Api-Key' => self::PORTAL_API_KEY,
                'Accept' => 'application/json',
                'Content-type' => 'application/json',
            ])
            ->setData($data)
            ->setUrl(self::PORTAL_API_URL . $url);

        return $request->send();
    }

    /**
     * @param RecruitmentProfile $profile
     * @return bool
     */
    public static function registerAccount($profile)
    {
        $response = static::sendRequest('/register', [
            'country' => $profile->content[ProfileProperty::COUNTRY],
            'lang' => \Yii::$app->language,
            'email' => $profile->content[ProfileProperty::EMAIL],
            'gender' => static::convertGender($profile->content[ProfileProperty::GENDER]),
            'dob' => static::convertDob($profile->content[ProfileProperty::DOB]),
            'src' => $profile->content[ProfileProperty::SOURCE],
            'ip' => $profile->content[ProfileProperty::IP],
            'sguid' => $profile->content[ProfileProperty::RMSID],
        ]);

        if (!$response->isOk) {
            if ($response->statusCode == 422) {
                return false;
            }

            throw new ServerErrorHttpException();
        }

        try {
            $content = Json::decode($response->getContent());
            $rmsid = ArrayHelper::getValue($content, 'rmsid');
            if (!$rmsid) {
                throw new ServerErrorHttpException();
            }

            $profile->content[ProfileProperty::PORTAL_RMSID] = $rmsid;
            $profile->save(false);

        } catch (\Throwable $e) {
            throw new ServerErrorHttpException();
        }

        return true;
    }

    /**
     * @param string $gender
     * @return string
     */
    protected static function convertGender($gender)
    {
        $genders = ['Male' => 'm', 'Female' => 'f'];

        return ArrayHelper::getValue($genders, $gender, 'm');
    }

    /**
     * @param string $dob format "d.m.Y"
     * @return string|null
     */
    protected static function convertDob($dob)
    {
        $date = \DateTime::createFromFormat('d.m.Y', $dob);
        if (false === $date) {
            return null;
        }

        return $date->format('Y-m-d');
    }

    /**
     * @param $email
     * @return array
     * @throws
     */
    public static function validateEmail($email)
    {
        $response = static::sendRequest('/register/validate', [
            'email' => $email,
            'lang' => \Yii::$app->language,
        ]);

        if (!$response->isOk) {
            switch ($response->getStatusCode()) {
                case 422:
                    $content = Json::decode($response->getContent());
                    return [ArrayHelper::getValue($content, '0.message', static::getDefaultMessage())];

                default:
                    return [static::getDefaultMessage()];
            }
        }

        return null;
    }

    /**
     * @param RecruitmentProfile $profile
     * @return string|false
     */
    public static function getSurvey($profile)
    {
        $response = static::sendRequest('/register/survey', [
            'rmsid' => $profile->content[ProfileProperty::PORTAL_RMSID],
        ], 'GET');

        if (!$response->isOk) {
            return false;
        }

        try {
            $content = Json::decode($response->getContent());
        } catch (\Throwable $e) {
            return false;
        }

        return ArrayHelper::getValue($content, 'url', false);
    }

    /**
     * @return string
     */
    protected static function getDefaultMessage()
    {
        return TranslateMessage::t(TranslationCategoryEnum::RECRUITMENT, 'Registration error. Please try again.');
    }
}