<?php

use yii\db\Migration;
use app\models\RespondentSurvey;

class m170712_183503_capitalizeAnswers extends Migration
{
    public function up()
    {
        $this->alterColumn(RespondentSurvey::tableName(), 'phone', $this->decimal(20)->defaultValue(null)->comment('Phone number'));

        $results = RespondentSurvey::find()->all();

        $tran = \Yii::$app->db->beginTransaction();
        try {

            foreach ($results as $result) {
                /** @var $result RespondentSurvey */

                $result->capitalizeResponseKeys();

                $json = \yii\helpers\Json::decode($result->response);
                if (isset($json['Mobile'])) {
                    $json['Phone'] = $json['Mobile'];
                    unset($json['Mobile']);

                    if (preg_match('/(\d+)/i', $json['Phone'], $matches) && isset($matches[1])) {
                        $result->phone = $matches[1];
                        $json['Phone'] = $matches[1];
                    }
                }
                $result->response = \yii\helpers\Json::encode($json);

                if (!$result->save()) {
                    $errors = $result->getFirstErrors();
                    throw new \Exception(array_pop($errors));
                }
            }

            $tran->commit();
        } catch (\Exception $e) {
            $tran->rollBack();
            echo $e->getTraceAsString();
            throw $e;
        }
    }

    public function down()
    {

    }
}
