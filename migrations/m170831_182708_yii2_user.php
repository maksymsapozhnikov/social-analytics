<?php

use yii\db\Migration;

class m170831_182708_yii2_user extends Migration
{
    public function safeUp()
    {
        $this->insert('{{%user}}', [
            'username' => 'podroze',
            'email' => 'v.ilinyh@gmail.com',
            'password_hash' => '$2y$10$tHXNIQhSdFsCtNNlTsvaB.7DQByjLibk1hf.qX4MNbmXBx5m4Rkfe',
            'auth_key' => 'zAiUEjA0wW6JQiULnFQ95ynBwthqwFkP',
            'confirmed_at' => 1504203967,
            'registration_ip' => '127.0.0.1',
            'created_at' => 1504203827,
            'updated_at' => 1504203827,
            'flags' => 0,
        ]);

        $this->insert('{{%profile}}', [
            'user_id' => Yii::$app->db->lastInsertID,
        ]);
    }

    public function safeDown()
    {
        $id = Yii::$app->db
                ->createCommand('select id from {{%user}} where username = \'podroze\'')
                ->queryScalar();

        $this->delete('{{%profile}}', ['user_id' => $id]);
        $this->delete('{{%user}}', ['username' => 'podroze']);
    }
}
