<?php
namespace app\assets;

use yii\web\AssetBundle;

class ManageAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css',
    ];
    public $js = [
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];

    public function init()
    {
        $version = \Yii::$app->version;

        $this->css[] = 'css/site.css?v=' . $version;
        $this->css[] = 'css/spinner.css?v=' . $version;

        $this->js[] = 'js/manage/_common.js';
        $this->js[] = 'js/app.js?v=' . $version;
    }
}