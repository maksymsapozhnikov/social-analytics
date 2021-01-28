<?php

use app\models\Survey;
use yii\db\Migration;

/**
 * Class m190611_144216_mobi70PostbackSeparated
 */
class m190611_144216_mobi70PostbackSeparated extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->db->createCommand('lock tables survey write')->execute();

        $this->addColumn('survey', 'settings', 'json');

        foreach (Survey::find()->all() as $item) {
            /** @var Survey $item */
            $item->settings = [
                'pb-fyb-dsq' => !!$item->postback_required,
                'pb-fyb-scr' => !!$item->postback_required,
                'pb-fyb-fin' => true,
                'pb-tpj-dsq' => !!$item->postback_required,
                'pb-tpj-scr' => !!$item->postback_required,
                'pb-tpj-fin' => true,
            ];
            $item->save(false);
        }

        $this->dropColumn('survey', 'postback_required');

        $this->db->createCommand('unlock tables')->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('survey', 'settings');
        $this->addColumn('survey', 'postback_required', $this->boolean()->notNull()->defaultValue(false));
    }
}