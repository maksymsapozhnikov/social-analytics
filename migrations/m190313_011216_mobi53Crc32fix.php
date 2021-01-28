<?php

use yii\db\Expression;
use yii\db\Migration;

/**
 * Class m190313_011216_mobi53Crc32fix
 */
class m190313_011216_mobi53Crc32fix extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->db->createCommand('lock tables respondent_survey write')->execute();

        $this->update('respondent_survey', [
            'jsf_project_id' => new Expression('trim(jsf_project_id)'),
            'jpi_hash' => new Expression('crc32(trim(jsf_project_id))'),
            'jsf_country' => new Expression('trim(jsf_country)'),
            'jc_hash' => new Expression('crc32(trim(jsf_country))'),
        ]);

        $this->db->createCommand('unlock tables')->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }
}