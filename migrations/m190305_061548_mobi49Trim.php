<?php

use yii\db\Migration;

/**
 * Class m190305_061548_mobi49Trim
 */
class m190305_061548_mobi49Trim extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $query = <<<'SQL'
        update respondent_survey
        set jsf_country    = trim(jsf_country),
            jc_hash        = crc32(trim(jsf_country)),
            jsf_project_id = trim(jsf_project_id),
            jpi_hash       = crc32(trim(jsf_country))
        where (jpi_hash > 0 or jc_hash > 0)
          and (
            jsf_project_id like ' %' or jsf_project_id like '% '
            or jsf_country like ' %' or jsf_country like '% '
          )
SQL;
        $this->execute($query);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }
}