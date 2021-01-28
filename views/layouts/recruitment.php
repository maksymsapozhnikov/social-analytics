<?php
/**
 * @var $this \yii\web\View
 * @var $content string
 */

use app\assets\RecruitmentAsset;
use app\assets\RecruitmentAssetRtl;
use app\models\Language;
use yii\helpers\Html;

$lang = Language::findOne(['lang' => Yii::$app->language]);
$isRtl = $lang ? $lang->is_rtl : false;

if ($isRtl) {
    RecruitmentAssetRtl::register($this);
} else {
    RecruitmentAsset::register($this);
}

$this->beginPage();

?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" dir="<?= $isRtl ? 'rtl' : 'lrt' ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow" />
    <link rel="icon" href="/favicon.ico?v=<?= Yii::$app->version ?>" type="image/x-icon" />
    <link href="https://tgmpanel.com/tgmmobi.css?v=<?= Yii::$app->version ?>" rel="stylesheet" type="text/css"/>
    <title><?= Html::encode($this->title) ?></title>
    <?= $this->render('@app/views/public/fb-pixel') ?>
    <?php $this->head() ?>
    <style>
        body,html{
            height:100%;font-family: Arial,"Helvetica Neue",Helvetica,sans-serif;
            font-size: 18px !important;line-height: 1.42857143 !important;
            color: #333 !important;background-color: #fff !important;
        }
        label {
            font-weight: 400 !important;
        }
        input,button {
            font-size: 16px !important;
        }
        .wrap{min-height:100%;height:auto;margin:0 auto -60px;padding:0 0 60px}.wrap>.container{padding:70px 15px 20px}.footer{height:60px;background-color:#f5f5f5;border-top:1px solid #ddd;padding-top:20px}.jumbotron{text-align:center;background-color:transparent}.jumbotron .btn{font-size:21px;padding:14px 24px}.not-set{color:#c55;font-style:italic}a.asc:after,a.desc:after{position:relative;top:1px;display:inline-block;font-family:'Glyphicons Halflings';font-style:normal;font-weight:400;line-height:1;padding-left:5px}a.asc:after{content:"\e151"}a.desc:after{content:"\e152"}.sort-numerical a.asc:after{content:"\e153"}.sort-numerical a.desc:after{content:"\e154"}.sort-ordinal a.asc:after{content:"\e155"}.sort-ordinal a.desc:after{content:"\e156"}.grid-view th{white-space:nowrap}.hint-block{display:block;margin-top:5px;color:#999}.error-summary{color:#a94442;background:#fdf7f7;border-left:3px solid #eed3d7;padding:10px 20px;margin:0 0 15px}.nav li>form>button.logout{padding:15px;border:none}@media(max-width:767px){.nav li>form>button.logout{display:block;text-align:left;width:100%;padding:10px 15px}}.nav>li>form>button.logout:focus,.nav>li>form>button.logout:hover{text-decoration:none}.nav>li>form>button.logout:focus{outline:0}.btn-danger li a{color:#fff!important}.variables-group .row:nth-child(odd){background-color:#d3d3d3}.variables-group .row:nth-child(even){background-color:#fff}.kv-grid-table tbody tr:hover{cursor:pointer;background-color:#d6e9c6}.sk-circle{margin:100px auto;width:80px;height:80px;position:relative}.sk-circle .sk-child{width:100%;height:100%;position:absolute;left:0;top:0}.sk-circle .sk-child:before{content:'';display:block;margin:0 auto;width:15%;height:15%;background-color:#0084B0;border-radius:100%;-webkit-animation:sk-circleBounceDelay 1.2s infinite ease-in-out both;animation:sk-circleBounceDelay 1.2s infinite ease-in-out both}.sk-circle .sk-circle2{-webkit-transform:rotate(30deg);-ms-transform:rotate(30deg);transform:rotate(30deg)}.sk-circle .sk-circle3{-webkit-transform:rotate(60deg);-ms-transform:rotate(60deg);transform:rotate(60deg)}.sk-circle .sk-circle4{-webkit-transform:rotate(90deg);-ms-transform:rotate(90deg);transform:rotate(90deg)}.sk-circle .sk-circle5{-webkit-transform:rotate(120deg);-ms-transform:rotate(120deg);transform:rotate(120deg)}.sk-circle .sk-circle6{-webkit-transform:rotate(150deg);-ms-transform:rotate(150deg);transform:rotate(150deg)}.sk-circle .sk-circle7{-webkit-transform:rotate(180deg);-ms-transform:rotate(180deg);transform:rotate(180deg)}.sk-circle .sk-circle8{-webkit-transform:rotate(210deg);-ms-transform:rotate(210deg);transform:rotate(210deg)}.sk-circle .sk-circle9{-webkit-transform:rotate(240deg);-ms-transform:rotate(240deg);transform:rotate(240deg)}.sk-circle .sk-circle10{-webkit-transform:rotate(270deg);-ms-transform:rotate(270deg);transform:rotate(270deg)}.sk-circle .sk-circle11{-webkit-transform:rotate(300deg);-ms-transform:rotate(300deg);transform:rotate(300deg)}.sk-circle .sk-circle12{-webkit-transform:rotate(330deg);-ms-transform:rotate(330deg);transform:rotate(330deg)}.sk-circle .sk-circle2:before{-webkit-animation-delay:-1.1s;animation-delay:-1.1s}.sk-circle .sk-circle3:before{-webkit-animation-delay:-1s;animation-delay:-1s}.sk-circle .sk-circle4:before{-webkit-animation-delay:-.9s;animation-delay:-.9s}.sk-circle .sk-circle5:before{-webkit-animation-delay:-.8s;animation-delay:-.8s}.sk-circle .sk-circle6:before{-webkit-animation-delay:-.7s;animation-delay:-.7s}.sk-circle .sk-circle7:before{-webkit-animation-delay:-.6s;animation-delay:-.6s}.sk-circle .sk-circle8:before{-webkit-animation-delay:-.5s;animation-delay:-.5s}.sk-circle .sk-circle9:before{-webkit-animation-delay:-.4s;animation-delay:-.4s}.sk-circle .sk-circle10:before{-webkit-animation-delay:-.3s;animation-delay:-.3s}.sk-circle .sk-circle11:before{-webkit-animation-delay:-.2s;animation-delay:-.2s}.sk-circle .sk-circle12:before{-webkit-animation-delay:-.1s;animation-delay:-.1s}@-webkit-keyframes sk-circleBounceDelay{0%,100%,80%{-webkit-transform:scale(0);transform:scale(0)}40%{-webkit-transform:scale(1);transform:scale(1)}}@keyframes sk-circleBounceDelay{0%,100%,80%{-webkit-transform:scale(0);transform:scale(0)}40%{-webkit-transform:scale(1);transform:scale(1)}}
        h1, h2 {font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;font-weight: 500;line-height: 1.1;color: inherit;font-size: 36px;}
    </style>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <div class="text-center" style="background-color: #6849A2">
        <img alt="tgm_logo350x653"
             src="https://surveygizmolibrary.s3.amazonaws.com/library/560867/tgm_logo350x653.jpg"/>
    </div>
    <div class="container">
        <?= $content ?>
    </div>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
