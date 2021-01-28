<?php
/**
 * @var array $report
 * @var array $countries
 * @var array $projects
 * @var \app\modules\manage\models\reports\CostReport $r
 */

use app\components\FormatHelper;
use app\components\helpers\WidgetHelper;
use kartik\date\DatePicker;

$this->title = 'Cost calculation';

$countries = array_merge([0 => 'All countries'], $countries);

?>

<h1><?= $this->title ?></h1>
<style>
    th {
        text-align: center !important;
    }
    .details-cell {
        padding-top:1px !important;
        padding-bottom: 1px !important;
        border: 0 !important;
    }
    .subtotal-row {
        cursor: pointer;
    }
    .manual-cell:hover, .project-cell:hover {
        background-color: #d9edf7;
    }
    .href-source-toggle, .href-source-toggle:hover {
        border-bottom: 1px dashed;
        text-decoration: none !important;
    }
    .href-source-toggle.href-on {
        opacity: 0.4;
    }
</style>
<form action="<?= \yii\helpers\Url::to(['/manage/report/cost']) ?>" method="get">
<div class="panel panel-success">
    <div class="panel-heading">
        <div class="row">
            <div class="col-xs-3">
                <label class="control-label">Date</label>
                <?= DatePicker::widget([
                    'name' => 'bd',
                    'name2' => 'ed',
                    'type' => DatePicker::TYPE_RANGE,
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'dd.mm.yyyy',
                        'endDate' => date('d.m.Y'),
                    ],
                    'value' => $r->bd,
                    'value2' => $r->ed,
                ]); ?>
            </div>
            <div class="col-xs-3">
                <label for="filter__countries">Country</label>
                <?= WidgetHelper::select2Widget($countries, [
                        'id' => 'country',
                        'value' => $r->country,
                ]) ?>
            </div>
            <div class="col-xs-5">
                <label for="filter__countries">Project ID</label>
                <?= WidgetHelper::select2Widget($projects, [
                    'id' => 'project',
                    'value' => $r->project,
                    'multiple' => true,
                ]) ?>
            </div>
            <div class="col-xs-1 text-right" style="padding-top:23px">
                <button class="btn btn-primary">Calculate</button>
            </div>
        </div>
    </div>
</div>
</form>
<p class="text-right" style="margin-top:-10px"><b>Toggle source details:
    <a class="href-source-toggle" data-source="tpj">TPJ</a>
        &nbsp;|&nbsp;
    <a class="href-source-toggle" data-source="fyb">FYB</a>
        &nbsp;|&nbsp;
    <a class="href-source-toggle" data-source="cint">Cint</a>
        &nbsp;|&nbsp;
    <a class="href-source-toggle" data-source="tgm">TGM</a>
        &nbsp;|&nbsp;
    <a class="href-source-toggle" data-source="ply">Ply</a>
        &nbsp;|&nbsp;
    <a class="href-source-toggle" data-source="poll">Poll</a>
</b></p>

<table class="table table-bordered">
    <thead>
    <tr>
        <th rowspan="2" title="PROJECT_ID value posted with response">Project ID</th>
        <th rowspan="2" title="COUNTRY value posted with resopnse">Country</th>
        <th rowspan="2" title="A date the first interview started">Started</th>
        <th rowspan="2" title="A date the last interview started">Finished</th>
        <th rowspan="2" title="Days interval between Finished and Started">Days</th>
        <th rowspan="2" title="Completed successfully">Done</th>
        <th rowspan="2" title="Done by day">Done per day</th>
        <th rowspan="2" title="Bid multiplied by Done">Total Cost</th>
        <th rowspan="2" title="Cost per interview (bid)">CPI</th>
        <th rowspan="2" title="Bid multiplied by SCR">SCR Cost</th>
        <th rowspan="2" title="Bid multiplied by DSQ">DSQ Cost</th>
        <th rowspan="2" title="Number of done by done plus screen out">IR%</th>
        <th rowspan="2" title="Number of done by number of started">CR%</th>
        <th rowspan="2" title="Number of disqualified by Started">DSQ%</th>
        <th rowspan="2" title="In progress">INP</th>
        <th rowspan="2" title="Screened out">SCR</th>
        <th rowspan="2" title="Disqualified">DSQ</th>
        <th rowspan="2" title="Started">STA</th>
        <th rowspan="2" title="Time per interview, mean">Time</th>
        <th rowspan="2" title="Time per question, mean">avTim</th>
        <th colspan="3" class="cell-type-tpj">TPJ</th>
        <th colspan="3" class="cell-type-fyb">FYB</th>
        <th colspan="3" class="cell-type-cint">Cint</th>
        <th colspan="3" class="cell-type-tgm">TGM</th>
        <th colspan="3" class="cell-type-ply">Ply</th>
        <th colspan="3" class="cell-type-poll">Poll</th>
        <th rowspan="2">Manual adj</th>
    </tr>
    <tr>
        <th class="cell-type-tpj">Done</th>
        <th class="cell-type-tpj">Cost</th>
        <th class="cell-type-tpj">CPI</th>
        <th class="cell-type-fyb">Done</th>
        <th class="cell-type-fyb">Cost</th>
        <th class="cell-type-fyb">CPI</th>
        <th class="cell-type-cint">Done</th>
        <th class="cell-type-cint">Cost</th>
        <th class="cell-type-cint">CPI</th>
        <th class="cell-type-tgm">Done</th>
        <th class="cell-type-tgm">Cost</th>
        <th class="cell-type-tgm">CPI</th>
        <th class="cell-type-ply">Done</th>
        <th class="cell-type-ply">Cost</th>
        <th class="cell-type-ply">CPI</th>
        <th class="cell-type-poll">Done</th>
        <th class="cell-type-poll">Cost</th>
        <th class="cell-type-poll">CPI</th>
    </tr>
    </thead>
    <tbody>
    <?= $this->render('_total', ['report' => $report]) ?>
    <?php
        $currentGroup = '';
        foreach($report as $item) {
            if (!$item['all_done']) {
                continue;
            }

            $group = $item['project_id'] . $item['country'];
            if ($currentGroup <> $group) {
                $currentGroup = $group;
                $rowClass = FormatHelper::attrClass($group, 'dtr-');

                echo $this->render('_total', [
                    'report' => $report,
                    'project_id' => $item['project_id'],
                    'country' => $item['country'],
                ]);
            }
            ?>
            <tr class="details-row <?= $rowClass ?>">
                <td colspan="5" class="details-cell"></td>
                <td class="text-right details-cell"><?= FormatHelper::reportNum($item['all_done']) ?></td>
                <td class="text-right details-cell details-cell"><?= FormatHelper::reportCost($item['all_done'] / FormatHelper::getDaysBetween($item['started'], $item['finished'])) ?></td>
                <td class="text-right details-cell"><?= FormatHelper::reportCost($item['all_cost']) ?></td>
                <td class="text-right details-cell"><?= FormatHelper::reportCost($item['all_cpi']) ?></td>
                <td class="text-right details-cell"><?= FormatHelper::reportCost($item['scr_cost']) ?></td>
                <td class="text-right details-cell"><?= FormatHelper::reportCost($item['dsq_cost']) ?></td>
                <td class="text-right details-cell"></td>
                <td class="text-right details-cell"></td>
                <td class="text-right details-cell"></td>
                <td class="text-right details-cell"><?= FormatHelper::reportNum($item['all_inp']) ?></td>
                <td class="text-right details-cell"><?= FormatHelper::reportNum($item['all_scr']) ?></td>
                <td class="text-right details-cell"><?= FormatHelper::reportNum($item['all_dsq']) ?></td>
                <td class="text-right details-cell"><?= FormatHelper::reportNum($item['all_started']) ?></td>
                <td class="text-right details-cell"></td>
                <td class="text-right details-cell"></td>
                <td class="text-right details-cell cell-type-tpj"><?= FormatHelper::reportNum($item['tpj_done']) ?></td>
                <td class="text-right details-cell cell-type-tpj"><?= FormatHelper::reportCost($item['tpj_cost']) ?></td>
                <td class="text-right details-cell cell-type-tpj"><?= $item['tpj_done'] ? FormatHelper::reportCost($item['tpj_cpi']) : '' ?></td>
                <td class="text-right details-cell cell-type-fyb"><?= FormatHelper::reportNum($item['fyb_done']) ?></td>
                <td class="text-right details-cell cell-type-fyb"><?= FormatHelper::reportCost($item['fyb_cost']) ?></td>
                <td class="text-right details-cell cell-type-fyb"><?= $item['fyb_done'] ? FormatHelper::reportCost($item['fyb_cpi']) : '' ?></td>
                <td class="text-right details-cell cell-type-cint"><?= FormatHelper::reportNum($item['cint_done']) ?></td>
                <td class="text-right details-cell cell-type-cint"><?= FormatHelper::reportCost($item['cint_cost']) ?></td>
                <td class="text-right details-cell cell-type-cint"><?= $item['cint_done'] ? FormatHelper::reportCost($item['cint_cpi']) : '' ?></td>
                <td class="text-right details-cell cell-type-tgm"><?= FormatHelper::reportNum($item['tgm_done']) ?></td>
                <td class="text-right details-cell cell-type-tgm"><?= FormatHelper::reportCost($item['tgm_cost']) ?></td>
                <td class="text-right details-cell cell-type-tgm"><?= $item['tgm_done'] ? FormatHelper::reportCost($item['tgm_cpi']) : '' ?></td>
                <td class="text-right details-cell cell-type-ply"><?= FormatHelper::reportNum($item['ply_done']) ?></td>
                <td class="text-right details-cell cell-type-ply"><?= FormatHelper::reportCost($item['ply_cost']) ?></td>
                <td class="text-right details-cell cell-type-ply"><?= $item['ply_done'] ? FormatHelper::reportCost($item['ply_cpi']) : '' ?></td>
                <td class="text-right details-cell cell-type-poll"><?= FormatHelper::reportNum($item['poll_done']) ?></td>
                <td class="text-right details-cell cell-type-poll"><?= FormatHelper::reportCost($item['poll_cost']) ?></td>
                <td class="text-right details-cell cell-type-poll"><?= $item['poll_done'] ? FormatHelper::reportCost($item['poll_cpi']) : '' ?></td>
                <td class="details-cell"></td>
            </tr>
            <?php
        }
    ?>
    </tbody>
</table>
<?php

$this->registerJsFile('@web/js/manage/report-cost.js', ['depends' => \app\assets\ManageAsset::class]);