<?php

use yii\db\Migration;

class m170909_175025_languages extends Migration
{
    public function safeUp()
    {
        $this->createTable('language', [
            'id' => $this->primaryKey(),
            'lang' => $this->string(10)->notNull()->append('COLLATE utf8_unicode_ci'),
            'name' => $this->string(50)->notNull(),
            'native_name' => $this->string(100)->notNull(),
        ]);

        $this->createIndex('uq_language_lang', 'language', ['lang']);

        $this->batchInsert('language', ['lang', 'name', 'native_name'], [
            ['en', 'English', 'English'],
            ['ar', 'Arabic', 'عربى'],
            ['cs', 'Czech', 'Čeština'],
            ['km', 'Khmer', 'ភាសាខ្មែរ'],
            ['ms', 'Malaysian', 'Melayu'],
            ['pl', 'Polish', 'Polski'],
            ['ru', 'Russian', 'Русский'],
            ['th', 'Thai', 'ไทย'],
            ['vi', 'Vietnamese', 'Tiếng Việt'],
            ['zh', 'Chinese', '中文'],
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('language');
    }

}
