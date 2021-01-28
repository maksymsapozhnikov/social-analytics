<?php
namespace app\commands;

use app\models\Ip2Location;
use yii\console\Controller;

class LoadCountriesController extends Controller
{
    protected $rows;

    const COLUMNS = ['name'];
    const ROWS_PER_BATCH = 10000;

    public function actionIndex()
    {
        set_time_limit(0);
        ini_set('memory_limit','2048M');

        $filename = \Yii::getAlias('@app/db/Countries.txt');

        $db = \Yii::$app->db;

        echo "  Truncating current countries values ... ";
        $db->createCommand()->truncateTable('{{%country}}')->execute();
        echo "done\n";

        echo "  Loading data ... ";

        $transaction = \Yii::$app->db->beginTransaction();

        try {
            $this->rows = [];

            $fp = fopen($filename, 'r');

            $nRowsLoaded = 0;

            while($row = fgetcsv($fp, 1000)) {
                ++$nRowsLoaded;
                $this->rows[] = $row;

                if (count($this->rows) == self::ROWS_PER_BATCH) {
                    \Yii::$app->db->createCommand()->batchInsert('{{%country}}', self::COLUMNS, $this->rows)->execute();
                    $this->rows = [];

                    echo '.';
                }
            }

            if (count($this->rows)) {
                \Yii::$app->db->createCommand()->batchInsert('{{%country}}', self::COLUMNS, $this->rows)->execute();
                $this->rows = [];
            }

            fclose($fp);

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        echo "  {$nRowsLoaded} rows loaded\n";
    }
}
