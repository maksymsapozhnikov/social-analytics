<?php

use yii\db\Migration;

class m170613_202219_referrer extends Migration
{
    public function up()
    {
        $this->addColumn('respondent_survey', 'referrer', $this->text()->comment('Referrer'));
        $this->addColumn('respondent_survey', 'tryings', $this->integer()->notNull()->defaultValue(1)->comment('Times surveys has been started.'));

        $this->addColumn('respondent', 'browser', $this->text()->comment('Browser name and version last seen'));
    }

    public function down()
    {
        $this->dropColumn('respondent_survey', 'referrer');
        $this->dropColumn('respondent_survey', 'tryings');

        $this->dropColumn('respondent', 'browser');
    }
}
