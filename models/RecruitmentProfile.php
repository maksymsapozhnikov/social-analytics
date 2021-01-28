<?php
namespace app\models;

use app\components\enums\ProfileProperty;
use app\components\exceptions\DisqualifiedException;
use app\components\recruitment\ProfileQuestion;
use app\components\recruitment\RecruitmentQuestions;
use paulzi\jsonBehavior\JsonBehavior;
use paulzi\jsonBehavior\JsonField;
use yii\db\ActiveRecord;

/**
 * Class RecruitmentProfile
 * @package app\models
 * @property integer $respondent_id
 * @property JsonField $content
 * @property Respondent $respondent
 * @property boolean $isFilled
 */
class RecruitmentProfile extends ActiveRecord
{
    const RMSID = '0000000';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'recruitment_profile';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['respondent_id', 'required'],
            ['respondent_id', 'exist', 'targetClass' => Respondent::class, 'targetAttribute' => 'id'],
            ['respondent_id', 'unique'],
            [['respondent_id', 'content'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => JsonBehavior::class,
                'attributes' => ['content'],
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRespondent()
    {
        return $this->hasOne(Respondent::class, ['id' => 'respondent_id']);
    }

    /**
     * @param Respondent $respondent
     * @param Survey $initialSurvey
     * @return boolean|RecruitmentProfile
     */
    public static function createRespondentProfile(Respondent $respondent, $initialSurvey = null)
    {
        $profile = static::findOne(['respondent_id' => $respondent->id]);
        if (!$profile) {
            $profile = new static(['respondent_id' => $respondent->id]);

            $profile->content->set([
                ProfileProperty::RMSID => $respondent->rmsid,
                ProfileProperty::COUNTRY => $initialSurvey->country,
                ProfileProperty::IP => \Yii::$app->request->userIP,
                ProfileProperty::SURVEY_RMSID => $initialSurvey->rmsid,
                ProfileProperty::LANG => \Yii::$app->language,
            ]);

            if (!$profile->save()) {
                return false;
            }
        }

        return $profile;
    }

    /**
     * Checks if the questions is answered.
     * @param ProfileQuestion $question
     * @return boolean
     */
    public function isAnswered(ProfileQuestion $question)
    {
        return !is_null($this->content[$question->code]) || $question->checkIfSkip($this);
    }

    /**
     * Checks if the question is required.
     * @param ProfileQuestion $question
     * @return boolean
     */
    public function isRequired(ProfileQuestion $question)
    {
        $profileIsNotFilled = !$this->content[ProfileProperty::FILLED];
        $questionIsNotAnswered = !$this->isAnswered($question);
        $autoValue = $question->chooseValue($this);

        $questionHasValue = !is_null($autoValue);
        if ($questionHasValue) {
            $this->content[$question->code] = $question->getResponse();
            $this->save();
        }

        return $profileIsNotFilled && $questionIsNotAnswered && !$questionHasValue;
    }

    /**
     * @return bool
     */
    public function getIsFilled()
    {
        return !!$this->content[ProfileProperty::FILLED] || is_null($this->getNextQuestion());
    }

    /**
     * @return ProfileQuestion|null
     */
    public function getNextQuestion()
    {
        foreach (RecruitmentQuestions::data() as $questionConfig) {
            $question = new ProfileQuestion($questionConfig);
            $question->profile = $this;
            if ($this->isRequired($question)) {
                return $question;
            }
        }

        return null;
    }

    /**
     * Loads response to profile.
     * @param ProfileQuestion $question
     * @return ProfileQuestion
     * @throws DisqualifiedException
     */
    public function setRecruitmentResponse($question)
    {
        $question->isLoaded = false;

        try {
            if (!$question->validate()) {
                return $question;
            }
        } catch (DisqualifiedException $e) {
            $this->content[$question->code] = $question->getResponse();
            $this->save();

            throw $e;
        }

        $nextQuestion = $this->getNextQuestion();
        if ($nextQuestion->uuid !== $question->uuid) {
            $nextQuestion->addError('value', 'Invalid value');
            return $nextQuestion;
        }

        $this->content[$question->code] = $question->getResponse();
        $this->content[ProfileProperty::FILLED] = $this->content[ProfileProperty::FILLED] || $question->checkIfFilled($this);

        if ($this->save()) {
            $question->isLoaded = true;
        } else {
            $question->addError('value', 'Internal error. Please try again');
        }

        return $question;
    }

    /**
     * @param string $rmsid
     * @return array
     */
    public static function getProfileByRmsid($rmsid)
    {
        $respondentQuery = Respondent::find()->select('id')->where(['rmsid' => $rmsid]);
        $profile = RecruitmentProfile::findOne(['respondent_id' => $respondentQuery]);

        return $profile ? $profile->content->toArray() : [];
    }
}