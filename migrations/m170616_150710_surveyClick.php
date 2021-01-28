<?php

use yii\db\Migration;

class m170616_150710_surveyClick extends Migration
{
    public function up()
    {
        $this->createTable('respondent_log', [
            'id' => $this->primaryKey(),
            'create_dt' => $this->integer(),
            'modify_dt' => $this->integer(),

            'respondent_id' => $this->integer()->defaultValue(null)->comment('Identified respondent id'),

            'survey_rmsid' => $this->string()->defaultValue(null),

            // useful parameters
            'device_id' => $this->string()->comment('DeviceAtlas Device ID'),
            'fingerprint_id' => $this->string()->comment('Fingerprintjs2 ID'),
            'ip' => $this->string()->comment('User IP address'),
            'referrer' => $this->text()->comment('Referrer'),

            // saved details
            'deviceatlas_details' => $this->text()->comment('JSON, DeviceAtlas details'),
            'ip_details' => $this->text()->comment('JSON, IP Details'),
            'request_details' => $this->text()->comment('JSON, Request details'),
        ]);

        $this->addForeignKey('fk_respondent_log_respondent', 'respondent_log', 'respondent_id', 'respondent', 'id');

        $this->ip();
    }

    protected function ip()
    {
        $query = <<<SQL
        CREATE TABLE ip2location_db (
            ip_from INT(10) UNSIGNED,
            ip_to INT(10) UNSIGNED,
            country_code CHAR(2),
            country_name VARCHAR(64),
            region_name VARCHAR(128),
            city_name VARCHAR(128),

            INDEX idx_ip_from(ip_from),
            INDEX idx_ip_to(ip_to),
            INDEX idx_ip_from_to(ip_from, ip_to)
        );
SQL;

        $this->db->createCommand($query)->execute();
    }

    public function down()
    {
        $this->dropTable('respondent_log');
    }
}
