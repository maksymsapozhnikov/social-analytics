<?php

use yii\db\Migration;
use app\components\enums\Roles;

class m170902_182829_authRoles extends Migration
{
    public function safeUp()
    {
        $now = time();

        $users_columns = ['name', 'type', 'description', 'rule_name', 'data', 'created_at', 'updated_at',];
        $users = [
            [Roles::ADMIN, 1, 'Administrator', null, null, $now, $now, ],
        ];

        $this->delete('{{%auth_assignment}}');
        $this->delete('{{%auth_item}}');

        $this->batchInsert('{{%auth_item}}', $users_columns, $users);

        $admin = \dektrium\user\models\User::findOne(['username' => 'podroze']);

        $this->insert('{{%auth_assignment}}', [
            'item_name' => Roles::ADMIN,
            'user_id' => $admin->id,
            'created_at' => $now,
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%auth_assignment}}');
        $this->delete('{{%auth_item}}');
    }
}
