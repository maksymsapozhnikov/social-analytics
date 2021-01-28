<?php
namespace app\models;

use yii\base\BaseObject;

/**
 * Class PostbackResponse
 * @package app\models
 */
class PostbackResponse extends BaseObject
{
    /** @var boolean */
    public $isCalled;

    /** @var string */
    public $callback;

    /** @var string */
    public $response;

    /** @var boolean */
    public $success;

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        parent::init();

        $this->isCalled = $this->callback !== null;
        $this->success = $this->isCalled ? $this->success : false;
    }
}