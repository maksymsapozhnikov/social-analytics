<?php

use yii\db\Migration;
use app\models\Respondent;
use app\models\RespondentStatus;

class m170715_193545_blacklistModification extends Migration
{
    public function up()
    {
        $this->addColumn(Respondent::tableName(), 'dt_blacklist', $this->integer()->comment('Date added to blacklist'));

        \Yii::$app->db->createCommand('update respondent set dt_blacklist = last_seen_at where status = :status')
            ->bindValue('status', RespondentStatus::DISQUALIFIED)
            ->execute();
    }

    public function down()
    {
        $this->dropColumn(Respondent::tableName(), 'dt_blacklist');
    }
}
