<?php
namespace app\models;

use yii\base\BaseObject;
use yii\web\Response;

class RestError extends BaseObject
{
    public $code;
    public $message;
    public $suspicious = null;

    public function init()
    {
        parent::init();

        \Yii::$app->response->format = Response::FORMAT_JSON;

        \Yii::$app->response->statusCode = $this->code;
    }

    public function asArray()
    {
        $result = [
            'code' => $this->code,
            'message' => $this->message,
        ];

        if (!is_null($this->suspicious)) {
            $result['suspicious'] = $this->suspicious;
        }

        return $result;
    }
}
