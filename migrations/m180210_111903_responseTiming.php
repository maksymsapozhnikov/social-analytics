<?php

use yii\db\Migration;
use app\models\RespondentSurvey;

/**
 * Class m180210_111903_responseTiming
 */
class m180210_111903_responseTiming extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(RespondentSurvey::tableName(), 'timing_score_json', $this->text()->comment('Questions timing data as JSON'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn(RespondentSurvey::tableName(), 'timing_score_json');
    }

}
