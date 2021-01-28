<?php
namespace app\controllers;

use dektrium\user\controllers\SecurityController as BaseSecurityController;

class DektriumSecurityController extends BaseSecurityController
{
    use UserLanguageTrait;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        unset($behaviors['verbs']);

        return $behaviors;
    }

}
