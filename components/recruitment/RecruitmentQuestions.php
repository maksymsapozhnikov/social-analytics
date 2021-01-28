<?php
namespace app\components\recruitment;

use app\components\enums\ProfileProperty;
use app\components\enums\QuestionType;
use app\components\exceptions\DisqualifiedException;
use app\components\helpers\RecruitmentDefaultsHelper;
use app\components\PortalValidationEmail;
use app\components\RecruitmentConsentValidator;
use app\components\RecruitmentDobValidator;
use app\models\enums\PanelRegisterType;
use app\models\RecruitmentProfile;
use app\models\Survey;

/**
 * Class Question
 * @package app\components\recruitment
 */
class RecruitmentQuestions
{
    /**
     * @return array
     */
    public static function data()
    {
        return [
            [
                'uuid' => 'a5b5f047-bc78-11e9-aea5-309c232a16d8',
                'code' => ProfileProperty::CONSENT,
                'title' => '',
                'type' => QuestionType::CONSENT,
                'skip' => function (RecruitmentProfile $profile) {
                    $survey = isset(\Yii::$app->controller->survey) ? \Yii::$app->controller->survey : null;
                    $shouldConsent = $survey && $survey->panel_register_type != PanelRegisterType::NONE;

                    return !$shouldConsent;
                },
                'validation' => [
                    ['value', RecruitmentConsentValidator::class],
                ],
            ],
            [
                'uuid' => 'e988c45b-2dd8-11e9-a731-309c232a16d8',
                'code' => ProfileProperty::GENDER,
                'title' => 'Are you?',
                'type' => QuestionType::OPTIONS,
                'randomize' => true,
                'values' => [
                    0 => 'Male',
                    1 => 'Female',
                ],
                'choose' => function (RecruitmentProfile $profile) {
                    return RecruitmentDefaultsHelper::getDefaultsGender($profile);
                },
            ],
            [
                'uuid' => '192c9bc5-2dd9-11e9-a731-309c232a16d8',
                'code' => ProfileProperty::AGE,
                'title' => 'How old are you?',
                'type' => QuestionType::TEXT,
                'validation' => [
                    ['value', 'integer', 'min' => 7, 'max' => 150, 'tooSmall' => 'Invalid value.', 'tooBig' => 'Invalid value.'],
                ],
                'choose' => function (RecruitmentProfile $profile) {
                    return RecruitmentDefaultsHelper::getDefaultsAge($profile);
                },
            ],
            [
                'uuid' => 'f2448d10-2ef8-11e9-a731-309c232a16d8',
                'code' => ProfileProperty::HAVE_CHILDREN,
                'title' => 'Do you have any children?',
                'type' => QuestionType::OPTIONS,
                'values' => [
                    'Yes' => 'Yes - I have children',
                    'No' => 'No - I don\'t have children yet',
                ],
                'choose' => function (RecruitmentProfile $profile) {
                    return RecruitmentDefaultsHelper::getDefaultsChildren($profile);
                },
            ],
            [
                'uuid' => '1ee38236-2dd9-11e9-a731-309c232a16d8',
                'code' => ProfileProperty::MARTIAL,
                'title' => 'What is your marital status?',
                'type' => QuestionType::OPTIONS,
                'values' => [
                    0 => 'Living with partner / Domestic partnership',
                    1 => 'Married',
                    2 => 'Single (never married)',
                    3 => 'Separated',
                    4 => 'Divorced',
                    5 => 'Widowed',
                    6 => 'Prefer not to answer',
                ],
                'randomize' => true,
                'choose' => function (RecruitmentProfile $profile) {
                    return RecruitmentDefaultsHelper::getDefaultsMartial($profile);
                },
            ],
            [
                'uuid' => '997c60d4-2ef8-11e9-a731-309c232a16d8',
                'code' => ProfileProperty::FOCUS_ANSWER,
                'title' => 'How much is five + two',
                'hint' => 'We just check if you are still being focused',
                'type' => QuestionType::OPTIONS,
                'randomize' => true,
                'values' => [
                    0 => '4',
                    1 => '7',
                    2 => '5',
                ],
            ],
            [
                'uuid' => 'a69b985d-2ef8-11e9-a731-309c232a16d8',
                'code' => ProfileProperty::DOB,
                'title' => 'Enter please your date of birth.',
                'type' => QuestionType::DATE,
                'defaultValue' => function () {
                    /** @var ProfileQuestion $this */
                    $age = $this->profile->content[ProfileProperty::AGE];
                    $datetime = new \DateTime("{$age} years ago 00:00:00+0");

                    return $datetime->format('d.m.Y');
                },
                'validation' => [
                    ['value', RecruitmentDobValidator::class],
                    ['value', function($attribute, $params, $validator) {
                        $trustInterval = 7;
                        $survey = isset(\Yii::$app->controller->survey) ? \Yii::$app->controller->survey : null;

                        /** @var ProfileQuestion $this */
                        $dob = $this->{$attribute} . ' 00:00:00';
                        $uDob = \DateTime::createFromFormat('d.m.Y H:i:s', $dob, new \DateTimeZone('UTC'));
                        if (!$uDob) {
                            $this->addError('value', 'Invalid date format');
                        }

                        $ageB = $this->profile->content[ProfileProperty::AGE] + $trustInterval;
                        $cDobB = new \DateTime("{$ageB} years ago 00:00:00+0");
                        $ageE = $this->profile->content[ProfileProperty::AGE] - $trustInterval;
                        $cDobE = new \DateTime("{$ageE} years ago 00:00:00+0");

                        if ($uDob < $cDobB || $uDob > $cDobE) {
                            if ($survey->strict_recruitment) {
                                throw new DisqualifiedException();
                            }
                        }
                    }],
                ],
            ],
            [
                'uuid' => '55e22917-55f7-11e9-879e-309c232a16d8',
                'code' => ProfileProperty::AGE_CALCULATED,
                'title' => 'Age calculated',
                'type' => QuestionType::TEXT,
                'choose' => function (RecruitmentProfile $profile) {
                    return RecruitmentDefaultsHelper::getDefaultsAgeCalculated($profile);
                },
            ],
            [
                'uuid' => '283ed3f4-5620-11e9-879e-309c232a16d8',
                'code' => ProfileProperty::AGE_DIFFERENCE,
                'title' => 'Age difference',
                'type' => QuestionType::TEXT,
                'choose' => function (RecruitmentProfile $profile) {
                    return RecruitmentDefaultsHelper::getDefaultsAgeDifference($profile);
                },
            ],
            [
                'uuid' => 'ae04c639-42b0-11e9-a6ad-06126aef74ba',
                'code' => ProfileProperty::GENDER_CHECK,
                'title' => 'Are you?',
                'type' => QuestionType::OPTIONS,
                'randomize' => true,
                'values' => [
                    0 => 'Male',
                    1 => 'Female',
                ],
                'skip' => function (RecruitmentProfile $profile) {
                    /** @var Survey $survey */
                    $survey = isset(\Yii::$app->controller->survey) ? \Yii::$app->controller->survey : null;

                    return $survey && !$survey->strict_recruitment;
                },
                'validation' => [
                    ['value', function($attribute, $params, $validator) {
                        /** @var Survey $survey */
                        $survey = isset(\Yii::$app->controller->survey) ? \Yii::$app->controller->survey : null;

                        /** @var ProfileQuestion $this */
                        if ($this->values[$this->{$attribute}] !== $this->profile->content[ProfileProperty::GENDER]) {
                            if ($survey && $survey->strict_recruitment) {
                                throw new DisqualifiedException();
                            }
                        }
                    }],
                ],
            ],
            [
                'uuid' => 'acfc51ba-bc78-11e9-aea5-309c232a16d8',
                'code' => ProfileProperty::EMAIL,
                'title' => 'Email',
                'type' => QuestionType::EMAIL,
                'skip' => function (RecruitmentProfile $profile) {
                    /** @var Survey $survey */
                    $survey = isset(\Yii::$app->controller->survey) ? \Yii::$app->controller->survey : null;
                    $shouldAsk = $survey && $survey->panel_register_type == PanelRegisterType::ASK_EMAIL;

                    return !$shouldAsk;
                },
                'validation' => [
                    ['value', 'required', 'message' => ''],
                    ['value', 'email', 'message' => ''],
                    ['value', PortalValidationEmail::class],
                ],
            ],
        ];
    }
}