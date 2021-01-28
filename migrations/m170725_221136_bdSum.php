<?php

use yii\db\Migration;
use app\models\RespondentSurvey;

class m170725_221136_bdSum extends Migration
{
    public function up()
    {
        $this->addColumn(RespondentSurvey::tableName(), 'bd', $this->decimal(15, 2));
    }

    public function down()
    {
        $this->dropColumn(RespondentSurvey::tableName(), 'bd');
    }
}
