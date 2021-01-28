<?php

use yii\db\Migration;

/**
 * Class m180217_191142_dirtyScore
 */
class m180217_191142_dirtyScore extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $rss = \app\models\RespondentSurvey::find()
            ->where(['IS NOT', 'dirty_score_json', null])
            ->andWhere(['<>', 'dirty_score_json', '[]'])
            ->all();

        foreach ($rss as $rs) {
            $rs->save();
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        return true;
    }

}
