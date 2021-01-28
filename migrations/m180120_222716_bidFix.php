<?php

use yii\db\Migration;
use app\models\RespondentSurvey;
use app\models\RespondentSurveyStatus as Status;
use yii\helpers\ArrayHelper;
use app\components\enums\ResponseField;

class m180120_222716_bidFix extends Migration
{
    public function safeUp()
    {
        $responses = RespondentSurvey::find()
            ->select('id')
            ->andWhere(['status' => Status::FINISHED])
            ->andWhere(['LIKE', 'response', '"BID"'])
            ->andWhere(['OR',
                ['bid' => 0],
                ['IS', 'bid', null],
            ])->asArray()->all();

        foreach ($responses as $item) {
            $response = RespondentSurvey::findOne($item['id']);

            $r = array_change_key_case($response->responseAsArray(), CASE_UPPER);
            $bid = floatval(ArrayHelper::getValue($r, ResponseField::BID));
            if (!$bid) {
                $bid = 0;

                $urlParts = parse_url($response->uri);
                if ($urlParts) {
                    $urlBidKey = 'bd';
                    foreach (explode('&', $urlParts['query']) as $parameter) {
                        list($key, $value) = explode('=', $parameter);
                        if ($key === $urlBidKey) {
                            $bid = floatval($value);
                        }
                    }
                }
            }

            $r[ResponseField::BID] = $bid;
            $response->setResponseOnly($r);
            $response->bid = $bid;

            if (!$response->save()) {
                echo "Save failed\r\n";
                return false;
            }
        }
    }

    public function safeDown()
    {
        return true;
    }
}
