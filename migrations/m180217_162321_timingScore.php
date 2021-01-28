<?php

use yii\db\Migration;
use app\models\RespondentSurvey;

/**
 * Class m180217_162321_timingScore
 */
class m180217_162321_timingScore extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(RespondentSurvey::tableName(), 'timing_score_sum', $this->double(6)->defaultValue(null));
        $this->addColumn(RespondentSurvey::tableName(), 'timing_score_avg', $this->double(6)->defaultValue(null));

        $rs = RespondentSurvey::find()->select('id')->asArray()->all();

        foreach($rs as $r) {
            RespondentSurvey::findOne($r['id'])->save();
        }

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn(RespondentSurvey::tableName(), 'timing_score_sum');
        $this->dropColumn(RespondentSurvey::tableName(), 'timing_score_avg');
    }
}
