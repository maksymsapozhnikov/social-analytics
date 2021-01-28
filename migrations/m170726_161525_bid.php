<?php

use yii\db\Migration;
use app\models\RespondentSurvey;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class m170726_161525_bid extends Migration
{
    public function up()
    {
        $this->dropColumn(RespondentSurvey::tableName(), 'bd');
        $this->addColumn(RespondentSurvey::tableName(), 'bid', $this->decimal(15,2)->comment('Bid value'));

        $surveys = RespondentSurvey::find()
            ->where(['status' => \app\models\RespondentSurveyStatus::FINISHED])
            ->andWhere(['like', 'response', '"Bid":'])
            ->all();

        foreach($surveys as $survey) {
            $bid = floatval(ArrayHelper::getValue(Json::decode($survey->response), 'Bid', 0));

            if($bid > 0) {
                $survey->bid = $bid;
                $survey->save();
            }
        }
    }

    public function down()
    {
        echo "Can't rollback m170726_161525_bid migration.";

        return false;
    }
}
