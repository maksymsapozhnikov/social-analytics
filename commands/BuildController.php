<?php
namespace app\commands;

use yii\console\Controller;
use MatthiasMullie\Minify\JS;
use yii\helpers\ArrayHelper;

class BuildController extends Controller
{
    public $jsEntries = [
        // SurveyGizmo SDK
        '@app/web/js/app.js' => [
            '@public/app.js',
        ],

        // SurveyGizmo SDK
        /** @todo should be sg-sdk.js but temporary set to sg-sdk-1.9.1.js */
        '@app/web/js/sg-sdk-1.9.1.js' => [
            '@public/config.js',
            '@libs/backbone/underscore-min.js',
            '@libs/sentry/raven.min.js',
            '@libs/momentjs/moment.min.js',
            '@public/sg-sdk.js',
        ],

        // Fingerprinting application
        '@app/web/js/tgm-mobi.js' => [
            '@public/config.js',
            '@libs/sentry/raven.min.js',
            '@libs/jquery/jquery-1.12.4.min.js',
            '@libs/device-atlas/deviceatlas-custom-1.5-170605.min.js',
            '@libs/base64/base64.js',
            '@libs/crypto-js/core.js',
            '@libs/crypto-js/sha1.js',
            '@libs/fingerprint2/fingerprint2.js',
            '@libs/js-cookie/js-cookie.js',
            '@libs/storage/storage.js',

            '@public/tgm-mobi.js',
        ],

        // Translation-controller
        '@app/web/js/control/translation-edit.js' => [
            '@public/control/translation-edit.js',
        ],

        '@app/web/js/control/results-list.js' => [
            '@public/control/results-list.js',
        ],

        // Manage, common lib
        '@app/web/js/manage/_common.js' => [
            '@libs/backbone/underscore-min.js',
            '@libs/backbone/backbone-min.js',
            '@libs/backbone/backbone.bootstrap-modal.js',
            '@libs/jquery/jquery.blockUI.js',
            '@libs/sprintf/sprintf.min.js',
            '@libs/storage/storage.js',

            '@libs/_common/config.js',

            '@libs/_common/base/BaseView.js',

            '@libs/_common/views/modals/Modal.js',
            '@libs/_common/views/modals/ModalConfirmation.js',
            '@libs/_common/views/modals/ModalError.js',
            '@libs/_common/views/modals/ModalInfo.js',

            '@libs/_common/views/modals/ModalInfo.js',

            '@modules/manage/js/_common/models/Alias.js',
            '@modules/manage/js/_common/models/Campaign.js',
            '@modules/manage/js/_common/forms/CostManualAdjustment.js',
            '@modules/manage/js/_common/forms/CostProjectRename.js',

            '@modules/manage/js/_common/forms/Alias.js',
            '@modules/manage/js/_common/forms/Campaign.js',
        ],

        // Manage: Campaigns
        '@app/web/js/manage/campaign.js' => [
            '@modules/manage/js/app/campaign.js',
        ],

        // Manage: Aliases
        '@app/web/js/manage/aliases.js' => [
            '@modules/manage/js/app/aliases.js',
        ],

        '@app/web/js/manage/survey-update.js' => [
            '@modules/manage/js/app/survey-update.js',
        ],

        '@app/web/js/manage/survey-index.js' => [
            '@modules/manage/js/app/survey-index.js',
        ],

        '@app/web/js/manage/report-cost.js' => [
            '@modules/manage/js/app/report-cost.js',
        ],

    ];

    /**
     * Builds required javascript files
     */
    public function actionIndex($file = null)
    {
        \Yii::setAlias('@libs', '@app/static/libs');
        \Yii::setAlias('@public', '@app/static/public');
        \Yii::setAlias('@modules', '@app/modules');

        if ($file) {
            $this->buildFile($file);
        } else {
            foreach ($this->jsEntries as $jsFile => $list) {
                $this->buildFile($jsFile);
            }
        }
    }

    protected function buildFile($file)
    {
        $list = ArrayHelper::getValue($this->jsEntries, $file);
        if (!$list) {
            return;
        }

        $minifier = new JS(array_map(function($item) {
            return \Yii::getAlias($item);
        }, $list));

        echo "$file ... ";

        $filename = \Yii::getAlias($file);
        $minifier->minify($filename);

        system('uglifyjs -m -c unused=false "' . $filename . '" -o "' . $filename . '"');

        echo "ok\n";
    }
}
