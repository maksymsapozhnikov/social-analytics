<?php

use yii\db\Migration;

/**
 * Class m171226_092501_malaysiaPoscodesReload
 */
class m171226_092501_malaysiaPoscodesReload extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->truncateTable('postcode');
        $this->loadPostCodes();
    }

    protected function loadPostCodes()
    {
        echo '    > loads postcodes ... ';
        $data = array_map('str_getcsv', file(\Yii::getAlias('@app/db/postcodes-malaysia.csv')));
        echo "done\n";

        foreach($data as $key => $row) {
            $row[0] = str_pad($row[0], 5, '0', STR_PAD_LEFT);
            $data[$key] = $row;
        }

        $this->batchInsert('postcode', [
            'postcode', 'town', 'state_short', 'state', 'strata', 'area',
        ], $data);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        /** can't rollback because data csv-file is changed */
        return false;
    }
}
