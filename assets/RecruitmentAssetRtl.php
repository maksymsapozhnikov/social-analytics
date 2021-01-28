<?php
namespace app\assets;

/**
 * Class RecruitmentAsset
 * @package app\assets
 */
class RecruitmentAssetRtl extends RecruitmentAsset
{
    public $depends = [
        MinifiedBootstrapRtl::class,
    ];
}