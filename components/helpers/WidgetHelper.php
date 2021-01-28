<?php
namespace app\components\helpers;

use app\components\QueriesHelper;
use app\models\SurveyStatus;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class WidgetHelper
{
    public static function select2Widget($data, $options)
    {
        try {
            $widget = Select2::widget([
                'id' => $options['id'],
                'name' => $options['id'],
                'data' => $data,
                'value' => $options['value'],
                'options' => [
                    'multiple' => $options['multiple'],
                    'placeholder' => $options['placeholder'],
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ]);
        } catch (\Throwable $e) {
            $widget = '';
        }

        return $widget;
    }

    public static function select2AliasStatus(array $options)
    {
        $options['id'] = ArrayHelper::getValue($options, 'id', 'filter__statuses');
        $options['multiple'] = true;
        $options['placeholder'] = 'Active';

        $data = [
            SurveyStatus::ACTIVE => 'Active',
            SurveyStatus::INACTIVE => 'Inactive',
            SurveyStatus::TRASH => 'Deleted',
        ];

        return static::select2Widget($data, $options);
    }

    /**
     * @param array $options
     * @return string
     */
    public static function select2Surveys(array $options)
    {
        $options['id'] = ArrayHelper::getValue($options, 'id', 'filter__surveys');
        $options['multiple'] = true;
        $options['placeholder'] = 'All surveys';

        return static::select2Widget(QueriesHelper::select2Surveys(), $options);
    }

    /**
     * @param array $options
     * @return string
     */
    public static function select2Countries(array $options)
    {
        $options['id'] = ArrayHelper::getValue($options, 'id', 'filter__countries');
        $options['multiple'] = false;
        $options['placeholder'] = 'All countries';

        return static::select2Widget(QueriesHelper::select2Countries(), $options);
    }

    /**
     * @param array $options
     * @return string
     */
    public static function select2Aliases(array $options)
    {
        $options['id'] = ArrayHelper::getValue($options, 'id', 'filter__aliases');
        $options['multiple'] = false;
        $options['placeholder'] = 'All aliases';

        return static::select2Widget(QueriesHelper::select2Aliases(), $options);
    }

    /**
     * @param array $options
     * @return string
     */
    public static function newButton(array $options)
    {
        $title = ArrayHelper::getValue($options, 'title', 'New');

        return Html::button('<span class="glyphicon glyphicon-plus"></span>&nbsp;' . $title,
            [
                'id' => 'item-add',
                'class' => 'btn btn-primary item-add form-control',
            ]
        );
    }
}