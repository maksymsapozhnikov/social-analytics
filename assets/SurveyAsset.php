<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

class SurveyAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
    ];

    public $js = [
    ];

    public $depends = [
        'app\assets\MinifiedBootstrap',
    ];

    public function init()
    {
        $this->js = [
            'js/tgm-mobi.js?v=' . \Yii::$app->version,
            'survey/mobi-app.js',
        ];
    }
}
