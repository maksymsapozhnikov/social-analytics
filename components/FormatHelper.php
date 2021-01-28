<?php
namespace app\components;

use app\models\Respondent;
use app\models\RespondentStatus;
use app\models\Survey;
use app\models\SurveyStatus;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class FormatHelper
{
    public static function percent($value, $number, $format = '%.1f')
    {
        $formatted = sprintf($format, $number ? 100 * $value / $number : 0);
        $value = is_null($value)
                ? "<span class=\"text-muted\" style=\"font-size:large\">0</span>"
                : "<b style=\"font-size:large\">{$value}</b>";

        return "{$value}<br><span class=\"text-muted small\">{$formatted}%</span>";
    }

    public static function surveyHtmlStatus($value)
    {
        $statuses = [
            SurveyStatus::ACTIVE => '<b class="text-success">active</b>',
            SurveyStatus::INACTIVE => '<span class="text-muted">inactive</span>',
            SurveyStatus::TRASH => '<span class="text-muted">trash</span>',
        ];
        $unknown = 'N/A';

        return isset($statuses[$value]) ? $statuses[$value] : $unknown;
    }

    public static function iconSurveyStatus($value)
    {
        $statuses = [
            SurveyStatus::ACTIVE => '<b class="glyphicon glyphicon-play" style="font-size:larger;color: #27ae60 !important;"></b>',
            SurveyStatus::INACTIVE => '<span class="glyphicon glyphicon-pause" style="font-size:larger;color: #7f8c8d !important;"></span>',
            SurveyStatus::TRASH => '<span class="glyphicon glyphicon-trash" style="font-size:larger"></span>',
        ];
        $unknown = '<span class="glyphicon glyphicon-question-sign"></span>';

        return isset($statuses[$value]) ? $statuses[$value] : $unknown;
    }

    /**
     * @param $dateTime int Unix epoch UTC
     * @param string $format date format
     * @return string
     * @throws
     */
    public static function toDate($dateTime, $format = 'd.m.Y H:i')
    {
        return \Yii::$app->formatter->asDatetime($dateTime + date('Z'), 'php:' . $format);
    }

    public static function clearResponse($response)
    {
        return trim(str_replace('Other, please provide:', '', $response));
    }

    public static function formatCampaignName($campaignName)
    {
        $result = null;

        if ($campaignName) {
            $result = '<span class="text-muted text-small text-bold">' . $campaignName . '</span>';
        }

        return $result;
    }

    public static function surveyFormatName(Survey $data)
    {
        $iconEdit = Html::tag('span', '', ['class' => 'glyphicon glyphicon-pencil']);
        $iconCopy = Html::tag('span', '', ['class' => 'glyphicon glyphicon-copy']);
        $iconClean = Html::tag('span', '', ['class' => 'glyphicon glyphicon-erase']);

        return static::surveyName($data) . '<br/>'
            . '<div class="control-buttons" style="display:none;margin-top:5px;margin-bottom:-5px;">'
            . Html::button($iconEdit, ['class' => 'btn btn-xs btn-primary', 'onClick' => 'onNameClick(event)', 'title' => 'Edit Survey',])
            . Html::button($iconCopy, ['class' => 'btn btn-xs btn-default', 'onClick' => 'onCopySurvey(event)', 'style' => 'margin-left:5px; margin-right:5px;', 'title' => 'Copy Survey',])
            . Html::button($iconClean, ['class' => 'btn btn-xs btn-default pull-right', 'onClick' => 'onCleanSurvey(event)', 'style' => 'margin-left:5px; margin-right:5px;', 'title' => 'Clean Survey results',])
            . '</div>';
    }

    public static function surveyFormatDeletedName(Survey $data)
    {
        $wrench = Html::tag('span', '', ['class' => 'glyphicon glyphicon-wrench']);

        return static::surveyName($data) . '<br/>'
            . '<div class="control-buttons" style="display:none;margin-top:5px;margin-bottom:-5px;">'
            . Html::button($wrench, ['class' => 'btn btn-xs btn-success', 'onClick' => 'onRestoreSurvey(event)', 'title' => 'Restore Survey',])
            . '</div>';
    }

    public static function surveyName(Survey $data)
    {
        return $data->name;
    }

    public static function surveyCreated($date)
    {
        return '<span class="text-muted small">' . static::toDate($date, 'd.m.y H:i') . '</span>';
    }

    public static function dirtyScore($value)
    {
        return \Yii::$app->formatter->asDecimal($value, 0);
    }

    public static function timingScoreSum($value)
    {
        return $value > 0 ? \Yii::$app->formatter->asDecimal($value, 0) : null;
    }

    public static function timingScoreAvg($value)
    {
        return $value > 0 ? \Yii::$app->formatter->asDecimal($value, 1) : null;
    }

    /**
     * @param Respondent $respondent
     * @return string
     */
    public static function respondentStatus(Respondent $respondent)
    {
        $unknown = 'Unknown';
        $statuses = [
            RespondentStatus::ACTIVE => 'Active',
            RespondentStatus::DISQUALIFIED => 'Disqualified',
        ];

        return ArrayHelper::getValue($statuses, $respondent->status, $unknown);
    }

    /**
     * @param Survey $model
     * @return string
     */
    public static function surveyCampaignName(Survey $model)
    {
        $lines = [];

        if ($model->campaign) {
            $lines[] = $model->campaign->name;
        }

        if ($model->tgm_recruitment) {
            $lines[] = Html::tag('span', 'requires reqruitment', ['class' => 'text-info']);
        }

        return implode('<br>', $lines);
    }

    /**
     * @param integer $start timestamp
     * @param integer $end timestamp
     * @return integer
     */
    public static function getDaysBetween($start, $end)
    {
        $_s = \DateTime::createFromFormat('U', $start)->setTime(0,0,0,0);
        $_e = \DateTime::createFromFormat('U', $end)->setTime(23,59,59,999999);

        return $_e->diff($_s)->days + 1;
    }

    public static function reportNum($value)
    {
        return intval($value) ? sprintf('%d', $value) : '&nbsp;';
    }

    public static function reportCost($value)
    {
        return floatval($value) ? sprintf('%.2f', $value) : '&nbsp;';
    }

    /**
     * Generates class name
     *
     * @param mixed $data
     * @param string $prefix
     * @return string
     */
    public static function attrClass($data, $prefix = '')
    {
        return $prefix . md5(serialize($data));
    }
}