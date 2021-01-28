<?php

use yii\db\Migration;

class m170630_170120_aliases extends Migration
{
    public function up()
    {
        $this->createTable('survey_alias', [
            'id' => $this->primaryKey()->notNull(),
            'rmsid' => $this->string(7)->notNull()->comment('Alias RmSid'),
            'survey_id' => $this->integer()->notNull(),
            'params' => $this->string(255)->notNull()->comment('Params'),
            'dt_create' => $this->integer()->notNull(),
            'dt_modify' => $this->integer(),
        ]);

        $testAlias = new \app\models\Alias([
            'survey_id' => 4,
            'params' => 'lang=vi&kn=Vietnam',
        ]);

        $testAlias->save();
    }

    public function down()
    {
        $this->dropTable('survey_alias');
    }
}
