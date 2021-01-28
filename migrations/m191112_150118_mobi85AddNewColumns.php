<?php

use yii\db\Migration;

/**
 * Class m191112_150118_mobi85AddNewColumns
 */
class m191112_150118_mobi85AddNewColumns extends Migration
{
    public function up()
    {
        $this->addColumn('survey_alias', 'scr', $this->integer()->notNull()->defaultValue(0)->comment('Screened out'));
        $this->addColumn('survey_alias', 'dsq', $this->integer()->notNull()->defaultValue(0)->comment('Disqualified'));
        $this->addColumn('survey_alias', 'qfl', $this->integer()->notNull()->defaultValue(0)->comment('Quota Full'));
        $this->addColumn('survey_alias', 'block', $this->integer()->notNull()->defaultValue(0)->comment('When user is blocked from the repeated entrance'));

        $model = \app\models\Alias::find()->all();
        if ($model) {
            foreach ($model as $one) {
                $one->scr = $one->getScrTotal();
                $one->dsq = $one->getDscTotal();
                $one->save();
            }
        }

    }

    public function down()
    {
        $this->dropColumn('survey_alias', 'scr');
        $this->dropColumn('survey_alias', 'dsq');
        $this->dropColumn('survey_alias', 'qfl');
        $this->dropColumn('survey_alias', 'block');
    }
}
