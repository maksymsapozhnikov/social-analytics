<?php

use yii\db\Migration;

/**
 * Class m180127_171859_checkDirty
 */
class m180127_171859_checkDirty extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('respondent_survey', 'dirty_score_json', $this->text());
        $this->addColumn('respondent_survey', 'dirty_score', $this->float()->notNull()->defaultValue(0));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('respondent_survey', 'dirty_score_json');
        $this->dropColumn('respondent_survey', 'dirty_score');
    }

}
