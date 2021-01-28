<?php

use yii\db\Migration;

/**
 * Class m190607_140945_mobi68SurveyPostback
 */
class m190607_140945_mobi68SurveyPostback extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('survey', 'postback_required', $this->boolean()->notNull()->defaultValue(false));
        $this->addColumn('respondent_survey', 's2s_response', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('respondent_survey', 's2s_response');
        $this->dropColumn('survey', 'postback_required');
    }
}