<?php

use yii\db\Migration;

class m171111_165711_geolocation extends Migration
{
    public function safeUp()
    {
        $this->addColumn('respondent', 'geo_latitude', $this->decimal(15,9)->comment('Latitude, provided by browser'));
        $this->addColumn('respondent', 'geo_longitude', $this->decimal(15,9)->comment('Longitude, provided by browser'));
        $this->addColumn('respondent', 'geo_address', $this->text()->comment('Address, provided by GoogleAPI'));
    }

    public function safeDown()
    {
        $this->dropColumn('respondent', 'geo_address');
        $this->dropColumn('respondent', 'geo_longitude');
        $this->dropColumn('respondent', 'geo_latitude');
    }
}
