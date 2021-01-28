<?php

use yii\db\Migration;
use app\models\Info;
use app\models\account\AccountTransaction;

class m170715_174030_info extends Migration
{
    public function up()
    {
        $tran = AccountTransaction::find()->andWhere(['IS NOT', 'details', null])->orderBy(['id' => SORT_DESC])->one();
        $json = \yii\helpers\Json::decode($tran->details);

        $this->createTable(Info::tableName(), [
            'id' => $this->primaryKey(),
            'name' => $this->string()->unique(),
            'value' => $this->string(),
            'description' => $this->string()->notNull()->defaultValue(''),
            'dt_created' => $this->integer()->notNull(),
            'dt_modified' => $this->integer()->notNull(),
        ]);

        $info = (new Info([
            'name' => Info::TRANSFERTO_BALANCE,
            'value' => $json['balance'],
            'description' => 'TransferTo Balance',
        ]));
        $info->dt_modified = $tran->dt;
        $info->save();

        $info = (new Info([
            'name' => Info::TRANSFERTO_CURRENCY,
            'value' => $json['originating_currency'],
            'description' => 'TransferTo Balance',
        ]));
        $info->dt_modified = $tran->dt;
        $info->save();
    }

    public function down()
    {
        $this->dropTable('info');
    }
}
