<?php

use yii\db\Migration;

class m170715_201724_aliasesDetails extends Migration
{
    public function up()
    {
        $this->addColumn(\app\models\Alias::tableName(), 'used', $this->integer()->notNull()->defaultValue(0)->comment('Counter, how many times alias was used'));

        $aliases = \app\models\Alias::find()->all();
        foreach($aliases as $alias) {
            $alias->used = $this->countUsed($alias->rmsid);
            $alias->save();
        }
    }

    protected function countUsed($alias)
    {
        $query = "select count(*) from respondent_log where request_details like '%\"url\":\"/sa/{$alias}\"%'";
        $count = \Yii::$app->db->createCommand($query)->queryScalar();

        return $count ?: 0;
    }

    public function down()
    {
        $this->dropColumn(\app\models\Alias::tableName(), 'used');
    }
}
