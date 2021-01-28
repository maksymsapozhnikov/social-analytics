<?php
namespace app\components\behaviors;

use app\components\enums\SurveySettings;
use app\models\Survey;
use yii\base\Behavior;
use yii\db\BaseActiveRecord;

/**
 * Class SurveySettingsBehavior
 * @package app\components\behaviors
 */
class SurveySettingsBehavior extends Behavior
{
    /**
     * {@inheritdoc}
     */
    public function events()
    {
        return [
            BaseActiveRecord::EVENT_BEFORE_INSERT => 'convert',
            BaseActiveRecord::EVENT_BEFORE_UPDATE => 'convert'
        ];
    }

    /**
     * Converts survey settings to boolean.
     */
    public function convert()
    {
        $settings = $this->owner->settings;
        $booleanSettings = [
            SurveySettings::TAPJOY_DSQ,
            SurveySettings::TAPJOY_SCR,
            SurveySettings::TAPJOY_FIN,
            SurveySettings::FYBER_DSQ,
            SurveySettings::FYBER_SCR,
            SurveySettings::FYBER_FIN,
        ];

        foreach ($booleanSettings as $booleanSetting) {
            $settings[$booleanSetting] = !!$settings[$booleanSetting];
        }

        $this->owner->settings = $settings;
    }
}