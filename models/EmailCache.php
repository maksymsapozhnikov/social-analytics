<?php
namespace app\models;

use app\components\AppHelper;
use yii\behaviors\AttributeTypecastBehavior;
use yii\db\ActiveRecord;

/**
 * Class EmailCache
 * @package app\models
 * @property string $email
 * @property integer $valid
 * @property float $score
 */
class EmailCache extends ActiveRecord
{
    const SCORE_VALIDITY = 0.6;

    public $valid;

    /**
     * {@inheritDoc}
     */
    public function formName()
    {
        return '';
    }

    /**
     * {@inheritDoc}
     */
    public static function tableName()
    {
        return 'email_cache';
    }

    /**
     * {@inheritDoc}
     */
    public function rules()
    {
        return [
            [
                ['email', 'did_you_mean', 'format_valid', 'mx_found', 'catch_all', 'role', 'disposable', 'free', 'score'],
                'safe',
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        $this->loadDefaultValues();

        if ($this->isNewRecord) {
            $this->dt_create = AppHelper::timeUtc();
            $this->dt_modify = null;
        } else {
            $this->dt_create = $this->oldAttributes['dt_create'];
            $this->dt_modify = AppHelper::timeUtc();
        }

        $this->valid = $this->isValid();

        return parent::save($runValidation, $attributeNames);
    }

    /**
     * {@inheritDoc}
     */
    public function afterFind()
    {
        $this->valid = $this->isValid();
    }

    /**
     * {@inheritDoc}
     */
    public function behaviors()
    {
        return [
            'typecast' => [
                'class' => AttributeTypecastBehavior::className(),
                'attributeTypes' => [
                    'valid' => AttributeTypecastBehavior::TYPE_BOOLEAN,
                    'format_valid' => AttributeTypecastBehavior::TYPE_BOOLEAN,
                    'mx_found' => AttributeTypecastBehavior::TYPE_BOOLEAN,
                    'catch_all' => AttributeTypecastBehavior::TYPE_BOOLEAN,
                    'role' => AttributeTypecastBehavior::TYPE_BOOLEAN,
                    'disposable' => AttributeTypecastBehavior::TYPE_BOOLEAN,
                    'free' => AttributeTypecastBehavior::TYPE_BOOLEAN,
                ],
                'typecastAfterFind' => true,
            ],
        ];
    }

    /**
     * Returns if the email is valid
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->format_valid && $this->score >= self::SCORE_VALIDITY;
    }
}
