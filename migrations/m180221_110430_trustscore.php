<?php

use yii\db\Migration;

/**
 * Class m180221_110430_trustscore
 */
class m180221_110430_trustscore extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $rss = \app\models\RespondentSurvey::find()
            ->where(['>', 'dirty_score', 0])
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
