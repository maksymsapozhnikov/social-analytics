<?php
namespace app\components\helpers;

use app\components\enums\EndpageStatusEnum;
use app\components\enums\ProfileProperty;
use app\components\enums\SpecialUrl;
use app\components\enums\TrafficSource;
use app\components\RespondentIdentity;
use app\models\RecruitmentProfile;
use app\models\RespondentSurvey;
use yii\helpers\ArrayHelper;

/**
 * Class EndpageHelper
 * @package app\components\helpers
 */
class EndpageHelper
{
    /**
     * Returns a URL to redirect a user.
     * @param string $status
     * @param RespondentSurvey|null $respondentSurvey
     * @return string URL to redirect to
     */
    public static function getEndpageUrl($status, $respondentSurvey)
    {
        return static::getEndpageUrlCommon(
            $status,
            $respondentSurvey->getUriParam('s'),
            $respondentSurvey->getUriParam('token'),
            $respondentSurvey->getUriParam('RID')
        );
    }

    /**
     * @param string $status
     * @return string
     */
    public static function getEndpageUrlByIdentity($status)
    {
        /** @var RespondentIdentity $identity */
        $identity = \Yii::$app->respondentIdentity;

        return static::getEndpageUrlCommon(
            $status,
            $identity->getUriParam('s'),
            $identity->getUriParam('token'),
            $identity->getUriParam('RID')
        );
    }

    /**
     * @param string $status
     * @param string $uriTrafficSource
     * @param string $uriToken
     * @param string $uriRid
     * @return string
     */
    protected static function getEndpageUrlCommon($status, $uriTrafficSource, $uriToken, $uriRid)
    {
        switch ($uriTrafficSource) {
            case TrafficSource::TGM:
            case TrafficSource::CINT:
                return static::getCintEndpageUrl($status, $uriToken);

            case TrafficSource::POLLFISH:
                return static::getPollfishEndpageUrl($status);

            case TrafficSource::LUCID:
                return static::getLucidEndpageUrl($status, $uriToken, $uriRid);

            default:
                return static::getDefaultEndpageUrl($status);
        }
    }

    /**
     * @param string $status
     * @return string
     */
    protected static function getDefaultEndpageUrl($status)
    {
        $defaultUrl = SpecialUrl::STATUS_DSQ;
        $urlMap = [
            EndpageStatusEnum::QFL => SpecialUrl::STATUS_SCR,
            EndpageStatusEnum::SCR => SpecialUrl::STATUS_SCR,
            EndpageStatusEnum::DSQ => SpecialUrl::STATUS_DSQ,
            EndpageStatusEnum::FIN => SpecialUrl::STATUS_FIN,
        ];

        return ArrayHelper::getValue($urlMap, $status, $defaultUrl);
    }

    /**
     * @param string $status
     * @param string $token
     * @return string
     */
    protected static function getCintEndpageUrl($status, $token)
    {
        $urlMap = [
            EndpageStatusEnum::SCR => 'https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=%s',
            EndpageStatusEnum::QFL => 'https://s.cint.com/Survey/QuotaFull?ProjectToken=%s',
            EndpageStatusEnum::DSQ => 'https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=%s',
            EndpageStatusEnum::FIN => 'https://s.cint.com/Survey/Complete?ProjectToken=%s',
        ];
        $status = isset($urlMap[$status]) ? $status : EndpageStatusEnum::DSQ;

        return sprintf($urlMap[$status], $token);
    }

    /**
     * @param string $status
     * @return string
     */
    protected static function getPollfishEndpageUrl($status)
    {
        $urlMap = [
            EndpageStatusEnum::SCR => 'https://wss.pollfish.com/api/thirdparty/v1/terminate',
            EndpageStatusEnum::QFL => 'https://wss.pollfish.com/api/thirdparty/v1/quotafull',
            EndpageStatusEnum::DSQ => 'https://wss.pollfish.com/api/thirdparty/v1/terminate',
            EndpageStatusEnum::FIN => 'https://wss.pollfish.com/api/thirdparty/v1/complete',
        ];
        $status = isset($urlMap[$status]) ? $status : EndpageStatusEnum::DSQ;

        return $urlMap[$status];
    }

    /**
     * @param string $status
     * @param string $token
     * @param string $ridSt
     * @return string
     */
    protected static function getLucidEndpageUrl($status, $token, $ridSt)
    {
        $urlMap = [
            EndpageStatusEnum::SCR => 'https://www.samplicio.us/s/ClientCallBack.aspx?RIS=20&RID=%s',
            EndpageStatusEnum::QFL => 'https://www.samplicio.us/s/ClientCallBack.aspx?RIS=40&RID=%s',
            EndpageStatusEnum::DSQ => 'https://www.samplicio.us/s/ClientCallBack.aspx?RIS=30&RID=%s',
            EndpageStatusEnum::FIN => 'https://notch.insights.supply/cb?token=' . $token . '&RID=%s',
        ];
        $status = isset($urlMap[$status]) ? $status : EndpageStatusEnum::DSQ;

        return sprintf($urlMap[$status], $ridSt);
    }

    /**
     * Returns URL to redirect user on click (ie register on the Portal link)
     *
     * @param string $url
     * @param RecruitmentProfile $recruitmentProfile
     * @return string
     */
    public static function getSurveyEndUrl($url, $recruitmentProfile)
    {
        return static::replacePlaceholders($url, [
            'gen' => static::profilePropertyToTgm($recruitmentProfile, ProfileProperty::GENDER),
            'age' => static::profilePropertyToTgm($recruitmentProfile, ProfileProperty::AGE),
            'dob' => static::profilePropertyToTgm($recruitmentProfile, ProfileProperty::DOB),
            'src' => static::profilePropertyToTgm($recruitmentProfile, ProfileProperty::SOURCE),
        ], false);
    }

    /**
     * @param RecruitmentProfile|null $profile
     * @param string $property
     * @return string
     */
    protected static function profilePropertyToTgm($profile, $property)
    {
        $propertyValue = $profile ? $profile->content[$property] : null;

        switch ($property) {
            case ProfileProperty::GENDER:
                $genders = ['Male' => 'm', 'Female' => 'f'];
                $propertyValue = ArrayHelper::getValue($genders, $propertyValue);
                break;

            case ProfileProperty::DOB:
                $propertyValue = \DateTime::createFromFormat('d.m.Y', $propertyValue, new \DateTimeZone('UTC')) ?: null;
                if ($propertyValue) {
                    /** @var \DateTime $propertyValue */
                    $propertyValue = $propertyValue->format('Ymd');
                }
                break;

            default:
        }

        return $propertyValue;
    }

    /**
     * Replaces placeholders within the text
     *
     * @param string $text
     * @param array $placeholders
     * @param bool $toUpperCase
     * @return string
     */
    public static function replacePlaceholders($text, $placeholders, $toUpperCase = true)
    {
        if (empty($text) || empty($placeholders)) {
            return $text;
        }

        foreach ($placeholders as $index => $value) {
            $placeholder = $toUpperCase ? strtoupper($index) : $index;
            $text = str_replace('{' . $placeholder . '}', $value, $text);
        }

        return $text;
    }
}