<?php
/**
 * @var array $report
 * @var string $project_id
 * @var string $country
 */

use app\components\FormatHelper;
use app\modules\manage\models\reports\CostReport;
use yii\helpers\Html;

$total = [];
$colGroups = [
    'all', 'scr', 'dsq',
    'tpj', 'fyb', 'cint', 'tgm', 'ply', 'poll',
];
$isSubtotal = $project_id && $country;
$rowClass = $isSubtotal ? FormatHelper::attrClass($project_id . $country, 'dtr-') : '';

foreach($report as $item) {
    if (($isSubtotal && $project_id === $item['project_id'] && $item['country'] == $country) || (!$isSubtotal)) {

        $total['started'] = min($total['started'] ?? PHP_INT_MAX, $item['started']);
        $total['finished'] = max($total['finished'], $item['finished']);

        $total['all_dsq'] += $item['all_dsq'];
        $total['all_inp'] += $item['all_inp'];
        $total['all_scr'] += $item['all_scr'];
        $total['all_started'] += $item['all_started'];

        $total['timing_score_sum'] += $item['timing_score_sum'];
        $total['timing_score_avg'] += $item['timing_score_avg'];

        foreach ($colGroups as $cGrp) {
            $total["{$cGrp}_done"] += $item["{$cGrp}_done"];
            $total["{$cGrp}_cost"] += $item["{$cGrp}_cost"];
            $total["{$cGrp}_cpi"] = $total["{$cGrp}_done"] ? ($total["{$cGrp}_cost"] / $total["{$cGrp}_done"]) : '';
        }

    }
}

if (empty($total)) {
    return;
}

?>
<tr style="background-color: <?= $isSubtotal ? '#f8f8f8' : '#f0f0f0;font-weight: bold' ?>"
    class="<?= $isSubtotal ? 'subtotal-row' : 'total-row' ?>"
    data-toggle-class="<?= $rowClass ?>"
    data-project-id="<?= Html::encode($project_id) ?>"
    data-country="<?= Html::encode($country) ?>"
    data-all-done="<?= $total['all_done'] ?>"
    data-all-cost="<?= $total['all_cost'] ?>"
    data-manual-adjustment="<?= $isSubtotal ? CostReport::adjustment($project_id, $country) : null ?>"
>
    <td style="min-width:205px; max-width:250px"
        class="<?= $isSubtotal ? 'project-cell' : '' ?>"><?= $project_id ?: 'Total' ?></td>
    <td><?= $country ?: '' ?></td>
    <td><?= date('d.m.y', $total['started']) ?></td>
    <td><?= date('d.m.y', $total['finished']) ?></td>
    <td class="text-right"><?= FormatHelper::getDaysBetween($total['started'], $total['finished']) ?></td>
    <td class="text-right"><?= FormatHelper::reportNum($total['all_done']) ?></td>
    <td class="text-right"><?= FormatHelper::reportCost($total['all_done'] / FormatHelper::getDaysBetween($total['started'], $total['finished'])) ?></td>
    <td class="text-right strow-all-cost"><?= FormatHelper::reportCost($total['all_cost']) ?></td>
    <td class="text-right strow-all-cpi"><?= FormatHelper::reportCost($total['all_cpi']) ?></td>
    <td class="text-right"><?= FormatHelper::reportCost($total['scr_cost']) ?></td>
    <td class="text-right"><?= FormatHelper::reportCost($total['dsq_cost']) ?></td>
    <td class="text-right"><?= sprintf('%.2f', 100 * $total['all_done'] / ($total['all_done'] + $total['all_scr'])) ?></td>
    <td class="text-right"><?= sprintf('%.2f', 100 * $total['all_done'] / $total['all_started']) ?></td>
    <td class="text-right"><?= sprintf('%.2f', 100 * $total['all_dsq'] / $total['all_started']) ?></td>
    <td class="text-right"><?= FormatHelper::reportNum($total['all_inp']) ?></td>
    <td class="text-right"><?= FormatHelper::reportNum($total['all_scr']) ?></td>
    <td class="text-right"><?= FormatHelper::reportNum($total['all_dsq']) ?></td>
    <td class="text-right"><?= FormatHelper::reportNum($total['all_started']) ?></td>
    <td class="text-right"><?= sprintf('%d', $total['timing_score_sum'] / $total['all_done']) ?></td>
    <td class="text-right"><?= sprintf('%.1f', $total['timing_score_avg'] / $total['all_done']) ?></td>
    <td class="text-right cell-type-tpj"><?= FormatHelper::reportNum($total['tpj_done']) ?></td>
    <td class="text-right cell-type-tpj"><?= FormatHelper::reportCost($total['tpj_cost']) ?></td>
    <td class="text-right cell-type-tpj"><?= FormatHelper::reportCost($total['tpj_cpi']) ?></td>
    <td class="text-right cell-type-fyb"><?= FormatHelper::reportNum($total['fyb_done']) ?></td>
    <td class="text-right cell-type-fyb"><?= FormatHelper::reportCost($total['fyb_cost']) ?></td>
    <td class="text-right cell-type-fyb"><?= FormatHelper::reportCost($total['fyb_cpi']) ?></td>
    <td class="text-right cell-type-cint"><?= FormatHelper::reportNum($total['cint_done']) ?></td>
    <td class="text-right cell-type-cint"><?= FormatHelper::reportCost($total['cint_cost']) ?></td>
    <td class="text-right cell-type-cint"><?= FormatHelper::reportCost($total['cint_cpi']) ?></td>
    <td class="text-right cell-type-tgm"><?= FormatHelper::reportNum($total['tgm_done']) ?></td>
    <td class="text-right cell-type-tgm"><?= FormatHelper::reportCost($total['tgm_cost']) ?></td>
    <td class="text-right cell-type-tgm"><?= FormatHelper::reportCost($total['tgm_cpi']) ?></td>
    <td class="text-right cell-type-ply"><?= FormatHelper::reportNum($total['ply_done']) ?></td>
    <td class="text-right cell-type-ply"><?= FormatHelper::reportCost($total['ply_cost']) ?></td>
    <td class="text-right cell-type-ply"><?= FormatHelper::reportCost($total['ply_cpi']) ?></td>
    <td class="text-right cell-type-poll"><?= FormatHelper::reportNum($total['poll_done']) ?></td>
    <td class="text-right cell-type-poll"><?= FormatHelper::reportCost($total['poll_cost']) ?></td>
    <td class="text-right cell-type-poll"><?= FormatHelper::reportCost($total['poll_cpi']) ?></td>
    <td class="text-right <?= $isSubtotal ? 'manual-cell' : '' ?>"></td>
</tr>