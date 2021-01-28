<?php
namespace app\components\helpers;

use app\models\SurveyStatus;
use yii\helpers\ArrayHelper;

class GridViewHelper
{
    public static function columnEditItem()
    {
        return self::columnActionItem([
            'icon' => 'edit',
            'columnClass' => 'info-column item-edit',
        ]);
    }

    public static function columnDeleteItem()
    {
        return self::columnActionItem([
            'icon' => 'trash',
            'columnClass' => 'danger-column item-delete',
        ]);
    }

    public static function columnDeleteOrRecoveryItem()
    {
        return [
            'format' => 'raw',
            'value' => function($model) {
            $icon = $model->status == SurveyStatus::TRASH ? 'open' : 'trash';
                return '<span class="glyphicon glyphicon-'.$icon.'"></span>';
            },
            'headerOptions' => [
                'style' => 'width:0',
                'class' => 'text-center small',
            ],
            'contentOptions' => function($model) {
                $class = $model->status == SurveyStatus::TRASH ? 'info-column item-recovery' : 'danger-column item-delete';
                return [
                    'class' => 'text-center '.$class,
                ];
            },
        ];
    }

    public static function columnCopyItem()
    {
        return self::columnActionItem([
            'icon' => 'duplicate',
            'columnClass' => 'info-column item-duplicate',
        ]);
    }

    public static function columnActionItem($options)
    {
        $icon = ArrayHelper::getValue($options, 'icon', '');
        $label = ArrayHelper::getValue($options, 'label', ' ');
        $columnClass = ArrayHelper::getValue($options, 'columnClass', '');

        return [
            'label' => $label,
            'format' => 'raw',
            'attribute' => ArrayHelper::getValue($options, 'attribute', 'attribute'),
            'value' => function($model) use ($icon) {
                $icon = is_callable($icon) ? $icon($model) : $icon;
                return '<span class="glyphicon glyphicon-' . $icon . '"></span>';
            },
            'headerOptions' => [
                'style' => 'width:0',
                'class' => 'text-center small',
            ],
            'header' => ArrayHelper::getValue($options, 'header'),
            'contentOptions' => [
                'class' => 'text-center ' . $columnClass,
            ],
        ];
    }
}