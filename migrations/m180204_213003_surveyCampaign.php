<?php

use yii\db\Migration;
use app\modules\manage\models\Campaign;
use app\models\Survey;

/**
 * Class m180204_213003_surveyCampaign
 */
class m180204_213003_surveyCampaign extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable(Campaign::tableName(), [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'created_by' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'updated_by' => $this->integer()->notNull(),
        ]);

        $this->addColumn(Survey::tableName(), 'campaign_id', $this->integer());
        $this->createIndex('idx_campaign', Survey::tableName(), ['campaign_id']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn(Survey::tableName(), 'campaign_id');
        $this->dropTable(Campaign::tableName());
    }

}
