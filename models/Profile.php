<?php
namespace app\models;

use dektrium\user\models\Profile as BaseProfile;

class Profile extends BaseProfile
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = [['last_name'], 'required'];
        $rules[] = [['last_name'], 'string', 'max' => 255];

        return $rules;
    }
}
