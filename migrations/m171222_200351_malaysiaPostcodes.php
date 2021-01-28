<?php

use yii\db\Migration;

/**
 * Class m171222_200351_malaysiaPostcodes
 */
class m171222_200351_malaysiaPostcodes extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('postcode', [
            'id' => $this->primaryKey(),
            'postcode' => $this->string(10)->notNull(),
            'town' => $this->string()->notNull()->defaultValue(''),
            'state' => $this->string()->notNull()->defaultValue(''),
            'state_short' => $this->string()->notNull()->defaultValue(''),
            'strata' => $this->string()->notNull()->defaultValue(''),
            'area' => $this->string()->notNull()->defaultValue(''),
        ]);
        $this->loadPostCodes();

        $this->createIndex('idx_postcode', 'postcode', ['postcode']);
    }

    protected function loadPostCodes()
    {
        echo '    > loads postcodes ... ';
        $data = array_map('str_getcsv', file(\Yii::getAlias('@app/db/postcodes-malaysia.csv')));
        echo "done\n";

        $this->batchInsert('postcode', [
            'postcode', 'town', 'strata', 'area', 'state', 'state_short',
        ], $data);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('postcode');
    }
}
