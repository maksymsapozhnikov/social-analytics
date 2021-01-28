<?php

use yii\db\Migration;
use app\models\Translation;

class m170710_105052_newTranslations extends Migration
{
    public function up()
    {
        $this->addColumn(Translation::tableName(), 'msg_3_wrong_phone',
            $this->string()->notNull()->defaultValue('The phone number doesn\'\'t exist or out of range')
        );

        $this->addColumn(Translation::tableName(), 'msg_4_wrong_currency',
            $this->string()->notNull()->defaultValue('Unable to top up this phone, should use {CURR} as a currency')
        );
    }

    public function down()
    {
        $this->dropColumn(Translation::tableName(), 'msg_3_wrong_phone');
        $this->dropColumn(Translation::tableName(), 'msg_4_wrong_currency');
    }
}
