<?php

use yii\db\Migration;

class m170705_191145_topups extends Migration
{
    public function up()
    {
        $this->addColumn('survey', 'has_topup', $this->boolean()->notNull()->defaultValue(0));
        $this->addColumn('survey', 'topup_value', $this->integer()->defaultValue(null));
        $this->addColumn('survey', 'topup_currency', $this->string(3)->defaultValue(null));

        $this->createTable('currency', [
            'id' => $this->primaryKey(),
            'code' => $this->string(3)->unique()->notNull(),
            'name' => $this->string()->notNull(),
        ]);

        $this->loadCurrencies();

        $this->addColumn('respondent_survey', 'phone', $this->decimal(16)->unique());
    }

    public function loadCurrencies()
    {
        $json = json_decode(file_get_contents(__DIR__ . '/../db/Currency.json'), true);
        $values = [];
        foreach($json as $code => $currency) {
            $values[] = [
                $currency['code'],
                $currency['name'],
            ];
        }

        $this->batchInsert('currency', ['code', 'name'], $values);
    }

    public function down()
    {
        $this->dropTable('currency');
        $this->dropColumn('survey', 'topup_currency');
        $this->dropColumn('survey', 'topup_value');
        $this->dropColumn('survey', 'has_topup');
        $this->dropColumn('respondent_survey', 'phone');
    }
}
