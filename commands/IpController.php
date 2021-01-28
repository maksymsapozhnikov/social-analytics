<?php
namespace app\commands;

use app\models\Ip2Location;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * Class LoadIpController
 * @package app\commands
 */
class IpController extends Controller
{
    const COLUMNS = ['ip_from', 'ip_to', 'country_code', 'country_name', 'region_name', 'city_name'];
    const ROWS_PER_BATCH = 10000;

    protected $rows;

    /**
     * Loads IP2LOCATION-LITE-DB3.CSV
     */
    public function actionLoadDb()
    {
        set_time_limit(0);
        ini_set('memory_limit', '2048M');

        $filename = \Yii::getAlias('@app/db/IP2LOCATION-LITE-DB3.CSV');

        $db = \Yii::$app->db;

        echo "  Truncating current ip values ... ";
        $db->createCommand()->truncateTable(Ip2Location::tableName())->execute();
        echo "done\n";

        echo "  Loading data ... ";

        $transaction = \Yii::$app->db->beginTransaction();

        try {
            $this->rows = [];

            $fp = fopen($filename, 'r');

            $nRowsLoaded = 0;

            while($row = fgetcsv($fp, 1000, ',', '"')) {
                ++$nRowsLoaded;
                $this->rows[] = $row;

                if (count($this->rows) == self::ROWS_PER_BATCH) {
                    \Yii::$app->db->createCommand()->batchInsert(Ip2Location::tableName(), self::COLUMNS, $this->rows)->execute();
                    $this->rows = [];

                    echo '.';
                }
            }

            if (count($this->rows)) {
                \Yii::$app->db->createCommand()->batchInsert(Ip2Location::tableName(), self::COLUMNS, $this->rows)->execute();
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
