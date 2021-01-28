<?php

use app\models\enums\PanelRegisterType;
use yii\db\Migration;

/**
 * Class m190811_193108_panel544AutoRegistration
 */
class m190811_193108_panel544AutoRegistration extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('survey', 'panel_register_type', $this->integer()->defaultValue(PanelRegisterType::NONE)->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('survey', 'panel_register_type');
    }
}
