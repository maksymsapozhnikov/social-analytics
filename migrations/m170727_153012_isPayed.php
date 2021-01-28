<?php

use yii\db\Migration;
use app\models\RespondentSurvey;
use app\models\Translation;

class m170727_153012_isPayed extends Migration
{
    public function up()
    {
        $this->addColumn(RespondentSurvey::tableName(), 'is_payed', $this->integer(1)->defaultValue(0));
        $this->addColumn(Translation::tableName(), 'msg_5_postpaid',
            $this->string()->notNull()->defaultValue('Please provide prepaid phone number')
        );
        $this->addColumn(Translation::tableName(), 'msg_6_payed',
            $this->string()->notNull()->defaultValue('This phone number has been payed already for this survey')
        );

        // update is_payed
    }

    public function down()
    {
        $this->dropColumn(RespondentSurvey::tableName(), 'is_payed');
        $this->dropColumn(Translation::tableName(), 'msg_5_postpaid');
        $this->dropColumn(Translation::tableName(), 'msg_6_payed');
    }
}
