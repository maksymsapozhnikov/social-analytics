<?php
namespace app\components;

use app\models\Info;
use app\models\logs\TransfertoLog;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class TransferTo extends Component
{
    public $login;
    public $token;
    public $url = 'https://airtime-api.dtone.com/cgi-bin/shop/topup';

    public $errorCode = null;
    public $errorMessage = null;

    const CMD_SIMULATION = 'simulation';
    const CMD_TOPUP = 'topup';
    const CMD_CHECKWALLET = 'check_wallet';
    const CMD_INFO = 'msisdn_info';

    protected $lastResponse;

    protected $responseLists = [
        'pricelist' => ['country', 'countryid', 'operator', 'operatorid'],
    ];

    public function isError()
    {
        return !is_null($this->errorCode);
    }

    public function sendRequest($request)
    {
        $this->errorCode = null;
        $this->errorMessage = null;

        $url = $this->getUrl($request);

        $response = file_get_contents($url);

        $result = $this->parseResponse($request['action'], $response);

        if ($result['error_code'] != 0) {
            $this->errorCode = $result['error_code'];
            $this->errorMessage = $result['error_txt'];
        }

        $this->lastResponse = $this->unsetExtras($result);

        return $this->lastResponse;
    }

    protected function unsetExtras($result)
    {
        $extras = ['error_code', 'error_txt', 'authentication_key'];

        foreach($extras as $extra) {
            if (isset($result[$extra])) {
                unset($result[$extra]);
            }
        }

        return $result;
    }

    protected function getUrl($params)
    {
        $params['login'] = $this->login;

        if (!isset($params['key'])) {
            $params['key'] = time();
        }

        $params['md5'] = md5($this->login . $this->token . $params['key']);

        $url = $this->url . '?' . http_build_query($params);

        return $url;
    }

    protected function parseResponse($action, $rawResponse)
    {
        $response = [];
        $rows = explode("\n", $rawResponse);

        foreach ($rows as $row) {
            $row = trim($row);

            if ($row) {
                list($key, $value) = explode('=', $row);

                $isPredefinedArray = isset($this->responseLists[$action]) && in_array($key, $this->responseLists[$action]);

                if ($isPredefinedArray || preg_match('/\_list$/', $key)) {
                    $response[$key] = explode(',', $value);
                } else {
                    $response[$key] = $value;
                }
            }
        }

        if (!$this->isError() && 'pricelist' === $action) {
            if (isset($response['country']) && isset($response['countryid'])) {
                $response['country'] = array_combine($response['countryid'], $response['country']);
                unset($response['countryid']);
            }

            if (isset($response['operator']) && isset($response['operatorid'])) {
                $response['operator'] = array_combine($response['operatorid'], $response['operator']);
                unset($response['operatorid']);
            }
        }

        return $response;
    }

    public function checkPhone($phone)
    {
        static $details = [];

        if (!isset($details[$phone])) {
            $details[$phone] = $this->sendRequest([
                'action' => self::CMD_INFO,
                'destination_msisdn' => $phone,
            ]);
        }

        return $details[$phone];
    }

    public function sendTopUps($phone, $amount, $sms = false)
    {
        $action = $phone == '79039826880' ? self::CMD_SIMULATION : self::CMD_TOPUP;
        $request = [
            'action' => $action,
            'destination_msisdn' => $phone,
            'msisdn' => $phone,
            'product' => $amount,
            'send_sms' => $sms ? 'yes' : 'no',
        ];

        if ($sms) {
            $request['sms'] = htmlspecialchars($sms);
        }

        $log = new TransfertoLog([
            'action' => $action,
            'phone' => $phone,
            'request_json' => Json::encode($request),

        ]);
        $log->save();

        $response = $this->sendRequest($request);

        $log->code = $this->errorCode;
        $log->message = $this->errorMessage;
        $log->response_json = Json::encode($response);
        $log->save();

        if (!$this->isError() && self::CMD_TOPUP == $action) {
            Info::value(Info::TRANSFERTO_BALANCE, $response['balance']);
        }

        return $response;
    }

    public function checkWallet()
    {
        $response = $this->sendRequest([
            'action' => self::CMD_CHECKWALLET,
        ]);

        if ($this->isError()) {
            return false;
        }

        return $response;
    }

    public function getSpentValue()
    {
        return ArrayHelper::getValue($this->lastResponse, 'wholesale_price');
    }
}
