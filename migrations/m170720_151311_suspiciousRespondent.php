<?php

use yii\db\Migration;
use app\models\Respondent;
use app\models\enums\SuspiciousStatus;
use app\models\RespondentSurvey;

class m170720_151311_suspiciousRespondent extends Migration
{
    public function up()
    {
        $this->addColumn(Respondent::tableName(), 'suspicious', $this->integer(2)->notNull()->defaultValue(SuspiciousStatus::LEGAL));
        $this->addColumn(RespondentSurvey::tableName(), 'suspicious', $this->integer(2)->notNull()->defaultValue(SuspiciousStatus::LEGAL));
    }

    public function down()
    {
        $this->dropColumn(Respondent::tableName(), 'suspicious');
        $this->dropColumn(RespondentSurvey::tableName(), 'suspicious');
    }
}
