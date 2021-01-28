<?php

use yii\db\Migration;

class m170609_083000_responses extends Migration
{
    public function up()
    {
        $this->addColumn('respondent_survey', 'response', $this->text()->comment('Respondent responses for the survey'));

        $this->addColumn('respondent_survey', 'started_at', $this->integer());
        $this->addColumn('respondent_survey', 'finished_at', $this->integer());

        $this->addColumn('respondent', 'registered_at', $this->integer());
        $this->addColumn('respondent', 'last_seen_at', $this->integer()->notNull());

        $this->update('respondent_survey', ['started_at' => time()]);
        $this->update('respondent', ['registered_at' => time()]);

        $this->alterColumn('respondent_survey', 'started_at', $this->integer()->notNull());
        $this->alterColumn('respondent', 'registered_at', $this->integer()->notNull());
    }

    public function down()
    {
        $this->dropColumn('respondent_survey', 'response');
        $this->dropColumn('respondent_survey', 'started_at');
        $this->dropColumn('respondent_survey', 'finished_at');

        $this->dropColumn('respondent', 'registered_at');
        $this->dropColumn('respondent', 'last_seen_at');
    }
}
