<?php

use app\models\Info;
use yii\db\Migration;
use yii\helpers\Json;

/**
 * Class m190228_194007_mobi45Adjustments
 */
class m190228_194007_mobi45Adjustments extends Migration
{
    const REPORT_COST_ADJUSTMENTS = 'ReportCost:Adjustment';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $info = new Info([
            'name' => self::REPORT_COST_ADJUSTMENTS,
            'value' => Json::encode([]),
            'description' => 'Report Cost, manual adjustments',
        ]);

        $info->save();
    }

    /**
     * {@inheritdoc}
     * @throws
     */
    public function safeDown()
    {
        $info = Info::findOne(['name' => self::REPORT_COST_ADJUSTMENTS]);
        if ($info) {
            $info->delete();
        }
    }
}
