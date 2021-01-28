<?php
namespace app\models;

use yii\db\ActiveRecord;
use yii\helpers\Json;

/**
 * Class SurveyOptions
 * @package app\models
 * @property $id integer pk
 * @property $identifier string text human-readable identifier
 * @property
 */
class SurveyOptions extends ActiveRecord
{
    protected $_options;

    public static function tableName()
    {
        return 'survey_options';
    }

    public function filter($opts = [])
    {
        if (!is_array($opts)) {
            return [];
        }

        if (empty($opts)) {
            return $this->_options;
        }

        $result = array_filter($this->_options, function ($e) use ($opts) {
            $count = 0;
            foreach ($opts as $key => $value) {
                if ($value == $e[$key]) {
                    $count += 1;
                }

                return $count == count($opts);
            }
        });

        return $result;
    }

    public function afterFind()
    {
        parent::afterFind();

        $this->_options = Json::decode($this->json);
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        $this->addError('id', 'Save method is not allowed');

        return false;
    }

}
