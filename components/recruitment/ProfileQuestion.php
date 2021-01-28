<?php
namespace app\components\recruitment;

use app\components\AppHelper;
use app\components\enums\QuestionType;
use app\components\helpers\TranslateMessage;
use app\models\enums\TranslationCategoryEnum;
use app\models\RecruitmentProfile;
use app\models\Survey;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class ProfileQuestion
 * @package app\components\recruitment
 * @property boolean $isLoaded
 * @property mixed $default
 */
class ProfileQuestion extends Model
{
    const SCENARIO_RESPONSE = 'response';

    /** @var RecruitmentProfile */
    public $profile;

    /** @var string */
    public $uuid;

    /** @var string */
    public $code;

    /** @var \Closure */
    public $choose;

    /** @var \Closure */
    public $filledWhen;

    /** @var \Closure */
    public $skip;

    /** @var string */
    public $title;

    /** @var \Closure|mixed */
    public $defaultValue;

    /** @var string */
    public $hint;

    /** @var string */
    public $type;

    /** @var array */
    public $values;

    /** @var mixed */
    public $value;

    /** @var array|null */
    public $validation;

    /** @var bool */
    public $randomize = false;

    /** @var boolean */
    protected $_isLoaded;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if ($this->randomize && is_array($this->values)) {
            AppHelper::shuffleAssoc($this->values);
            foreach ($this->values as $key => $value) {
                if ($value === 'Prefer not to answer') {
                    // moves the option to the last
                    unset($this->values[$key]);
                    $this->values[$key] = $value;
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $requiredMessage = TranslateMessage::t(TranslationCategoryEnum::RECRUITMENT, 'This question is required');

        $coreRules = [
            [['value'], 'required', 'on' => self::SCENARIO_RESPONSE, 'message' => $requiredMessage],
            [['uuid', 'value'], 'safe', 'on' => self::SCENARIO_RESPONSE],
        ];

        $validationRules = $this->validation ?: [];
        foreach ($validationRules as &$validationRule) {
            if (isset($validationRule[1]) && is_callable($validationRule[1])) {
                $validationRule[1] = \Closure::bind($validationRule[1], $this);
            }
        }

        return ArrayHelper::merge($coreRules, $validationRules);
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        switch ($this->type) {
            case QuestionType::OPTIONS:
                $valueLabel = ArrayHelper::getValue($this->values, $this->value, null);
                $hasAssocKeys = ArrayHelper::isAssociative($this->values);
                $isValidAssocKey = $hasAssocKeys && ($valueLabel !== null);

                return $isValidAssocKey ? $this->value : $valueLabel;

            case QuestionType::TEXT:
            case QuestionType::DATE:
            default:
                return $this->value;
        }
    }

    /**
     * @param RecruitmentProfile $profile
     * @return bool
     */
    public function checkIfFilled(RecruitmentProfile $profile)
    {
        if (!is_callable($this->filledWhen)) {
            return false;
        }

        return call_user_func($this->filledWhen, $profile);
    }


    /**
     * @param RecruitmentProfile $profile
     * @return bool
     */
    public function checkIfSkip(RecruitmentProfile $profile)
    {
        if (!is_callable($this->skip)) {
            return false;
        }

        return call_user_func($this->skip, $profile);
    }

    /**
     * @param RecruitmentProfile $profile
     * @return
     */
    public function chooseValue(RecruitmentProfile $profile)
    {
        if (!is_callable($this->choose)) {
            return null;
        }

        $this->value = call_user_func($this->choose, $profile);

        return $this->value;
    }

    /**
     * Returns if the question data successfully loaded.
     *
     * @return boolean
     */
    public function getIsLoaded()
    {
        return $this->_isLoaded && !$this->hasErrors();
    }

    /**
     * Sets if the questions data successfully loaded.
     *
     * @param boolean $value
     */
    public function setIsLoaded($value)
    {
        $this->_isLoaded = $value;
    }

    /**
     * Returns default question value.
     *
     * @return mixed
     */
    public function getDefault()
    {
        if (is_callable($this->defaultValue)) {
            $defaultValue = $this->defaultValue->call($this);
        } else {
            $defaultValue = $this->defaultValue;
        }

        return $defaultValue;
    }
}