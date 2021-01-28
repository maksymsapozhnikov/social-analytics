<?php
namespace app\commands;

use app\components\MobileChecker;
use app\components\TransferTo;
use app\models\Info;
use app\models\PhoneCache;
use app\models\RespondentSurvey;
use app\models\Survey;
use yii\console\Controller;
use yii\db\Query;

class TransferToController extends Controller
{
    public function actionCheckWallet()
    {
        /** @var TransferTo $transferto */
        $transferto = \Yii::$app->transferTo;

        $response = $transferto->checkWallet();

        if (false === $response) {
            throw new \Exception($transferto->errorMessage);
        }

        Info::value(Info::TRANSFERTO_BALANCE, $response['balance']);
        Info::value(Info::TRANSFERTO_CURRENCY, $response['currency']);

        echo '    Current TransferTo balance is ' . $response['balance'] . ' ' . $response['currency'];
    }

    public function actionCheckPhones()
    {
        $subQuery = new Query();
        $subQuery->select('phone')
            ->from(PhoneCache::tableName());

        $phones = RespondentSurvey::find()
            ->where(['NOT IN', 'phone', $subQuery])
            ->andWhere(['IS NOT', 'phone', null])
            ->all();

        $count = count($phones);
        echo "Checking {$count} phones\n";

        foreach ($phones as $phone) {
            echo "    {$phone->phone} ... ";
            $result = MobileChecker::getDetails($phone->phone);
            echo $result['valid'] ? 'valid' : 'not valid';
            echo "\n";
        }
    }
}
