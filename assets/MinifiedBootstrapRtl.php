<?php
namespace app\assets;

use yii\web\AssetBundle;

/**
 * Class MinifiedBootstrapRtl
 * @package app\assets
 */
class MinifiedBootstrapRtl extends AssetBundle
{
    public $sourcePath = '@bower/bootstrap-rtl/dist';

    public $css = [
        'css/bootstrap-rtl.css',
    ];
}