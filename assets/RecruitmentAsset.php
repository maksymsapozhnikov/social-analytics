<?php
namespace app\assets;

use yii\web\AssetBundle;

/**
 * Class RecruitmentAsset
 * @package app\assets
 */
class RecruitmentAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
    ];

    public $js = [
    ];

    public $depends = [
        MinifiedBootstrap::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->js = [

        ];
    }
}