<?php

use yii\db\Migration;
use app\models\Survey;
use app\models\account\AccountTransaction;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class m170729_180305_surveySpent extends Migration
{
    const SIMULATION = 'Change simulation, no credit, debit and database record are performed';

    public function up()
    {
        $this->addColumn(Survey::tableName(), 'topup_spent', $this->decimal(15,2)->notNull()->defaultValue(0)->comment('Spent value, USD, based on TransferTo wholesale prices.'));

        $trans = AccountTransaction::find()
            ->where(['operation' => \app\models\account\Operation::TRANSFER])
            ->all();

        $transaction = $this->db->beginTransaction();

        foreach($trans as $tran) {
            try {
                $logged = Json::decode($tran->details);

                if (ArrayHelper::getValue($logged, 'info_txt') != self::SIMULATION) {
                    /** @var AccountTransaction $prev */
                    $prev = AccountTransaction::find()
                        ->where(['operation' => \app\models\account\Operation::INCENTIVE])
                        ->andWhere(['=', 'account_id', $tran->account_id])
                        ->andWhere(['is not', 'survey_id', null])
                        ->orderBy(['id' => SORT_DESC])
                        ->one();

                        Survey::findOne($prev->survey_id)
                            ->updateSpent(ArrayHelper::getValue($logged, 'wholesale_price', 0));
                }
            } catch (\Exception $e) {

            }
        }

        $transaction->commit();
    }

    public function down()
    {
        $this->dropColumn(Survey::tableName(), 'topup_spent');
    }

}
