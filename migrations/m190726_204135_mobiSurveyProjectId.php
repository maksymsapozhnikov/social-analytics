<?php

use yii\db\Migration;

/**
 * Class m190726_204135_mobiSurveyProjectId
 */
class m190726_204135_mobiSurveyProjectId extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('survey', 'project_id', $this->string()->defaultValue(null));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('survey', 'project_id');
    }
}
