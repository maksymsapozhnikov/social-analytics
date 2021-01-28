<?php

use yii\db\Migration;
use app\models\PhoneCache;
use app\models\enums\PhoneSystemEnum;
use app\models\account\Account;
use app\models\logs\TransfertoLog;

class m170717_045249_postpaidPhones extends Migration
{
    public function up()
    {
        $this->addColumn(
            PhoneCache::tableName(),
            'payment_system',
            $this->integer()->notNull()->defaultValue(PhoneSystemEnum::UNDEFINED)->comment('Phone payment system (postpaid/prepaid), PhoneSystemEnum')
        );

        $this->addColumn(
            Account::tableName(),
            'payment_system',
            $this->integer()->notNull()->defaultValue(PhoneSystemEnum::UNDEFINED)->comment('Phone payment system (postpaid/prepaid), PhoneSystemEnum')
        );

        $this->createTable(TransfertoLog::tableName(), [
            'id' => $this->primaryKey(),
            'dt' => $this->integer()->notNull()->comment('Action date'),
            'action' => $this->string(25)->notNull()->comment('Action'),
            'phone' => $this->decimal(25)->comment('Destination phone number'),
            'request_json' => $this->text()->notNull()->comment('Request JSON'),
            'code' => $this->integer()->comment('Error code'),
            'message' => $this->integer()->comment('Description of error'),
            'response_json' => $this->text()->comment('Request JSON'),
        ]);
    }

    public function down()
    {
        $this->dropTable(TransfertoLog::tableName());
        $this->dropColumn(PhoneCache::tableName(), 'payment_system');
        $this->dropColumn(Account::tableName(), 'payment_system');
    }
}
