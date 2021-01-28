<?php

use yii\db\Migration;
use app\models\Alias;

/**
 * Class m180401_093736_stickAliases
 */
class m180401_093736_stickAliases extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(Alias::tableName(), 'is_sticked', $this->boolean()->notNull()->defaultValue(false));
        $this->addColumn(Alias::tableName(), 'note', $this->string()->notNull()->defaultValue(''));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(Alias::tableName(), 'note');
        $this->dropColumn(Alias::tableName(), 'is_sticked');
    }
}
