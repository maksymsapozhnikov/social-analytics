<?php

use yii\db\Migration;

class m170709_204216_alterAccTransaction extends Migration
{
    public function up()
    {
        $this->addColumn('account_transaction', 'details', $this->text());
    }

    public function down()
    {
        $this->dropColumn('account_transaction', 'details');
    }
}
