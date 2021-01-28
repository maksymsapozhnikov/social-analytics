<?php

use yii\db\Expression;
use yii\db\Migration;

/**
 * Class m190226_172309_panel46ResponseProjectId
 */
class m190226_172309_panel46ResponseProjectId extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('respondent_survey', 'jsf_project_id', $this->string()->defaultValue(null));
        $this->addColumn('respondent_survey', 'jpi_hash', 'int unsigned');
        $this->addColumn('respondent_survey', 'jsf_country', $this->string()->defaultValue(null));
        $this->addColumn('respondent_survey', 'jc_hash', 'int unsigned');

        $this->update('respondent_survey', [
            'jsf_project_id' => new Expression($this->sqlJsonExtract('PROJECT_ID')),
            'jpi_hash' => new Expression('crc32(' . $this->sqlJsonExtract('PROJECT_ID') . ')'),
            'jsf_country' => new Expression($this->sqlJsonExtract('COUNTRY')),
            'jc_hash' => new Expression('crc32(' . $this->sqlJsonExtract('COUNTRY') . ')'),
        ]);

        $this->createIndex('idx_jpi_hash', 'respondent_survey', 'jpi_hash');
        $this->createIndex('idx_jc_hash', 'respondent_survey', 'jc_hash');
    }

    /**
     * @param string $param
     * @return string
     */
    protected function sqlJsonExtract($param)
    {
        $item = "'\"{$param}\":\"'";
        $lItem = strlen($item) - 2;

        return <<<SQL
        case when position($item in response) > 0
             then substr(
               response,
               position($item in response) + $lItem,
               position('"' in substr(response, position($item in response) + $lItem)) - 1
             ) else null end
SQL;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('respondent_survey', 'jpi_hash');
        $this->dropColumn('respondent_survey', 'jsf_project_id');
        $this->dropColumn('respondent_survey', 'jc_hash');
        $this->dropColumn('respondent_survey', 'jsf_country');
    }
}
