<?php
namespace app\assets;

use yii\web\AssetBundle;

class PublicAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [

    ];

    public $js = [

    ];

    public $depends = [
        'yii\bootstrap\BootstrapAsset',
    ];

    public function init()
    {
        $version = \Yii::$app->version;
        $this->css = [
            'css/public.css?v=' . $version,
            'css/font-awesome.min.css',
            'css/bootstrap-social.css',
        ];

        parent::init();
    }

}
