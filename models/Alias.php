<?php
namespace app\models;

use app\components\AppHelper;
use app\components\enums\EndpageStatusEnum;
use app\models\errors\InternalError;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Class Alias
 * @property Survey $survey
 * @property integer $used
 * @property integer $dt_create
 * @property integer $dt_modify
 * @property string $rmsid
 * @property boolean $is_sticked
 * @property string $note
 * @property integer $status
 * @property integer $cnt_finished
 * @property string $params
 * @property string $source
 * @property float $bid
 * @property integer $id
 * @property integer $scr
 * @property integer $dsq
 * @property integer $qfl
 * @property integer $block
 * @property integer $lang
 * @property integer $utmMedium
 */
class Alias extends ActiveRecord
{
    public $usedTotal;
    public $lang;
    public $bid;
    public $source;
    public $utmMedium;
    public $shortParams;

    const SCENARIO_EDIT = 'edit';

    /**
     * {@inheritDoc}
     */
    public static function tableName()
    {
        return 'survey_alias';
    }

    /**
     * {@inheritDoc}
     */
    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [['rmsid', 'survey_id', 'params'], 'required'],
            [['lang', 'source', 'bid'], 'required', 'on' => self::SCENARIO_EDIT],
            [['rmsid', 'params', 'shortParams', 'note', 'lang', 'source', 'utmMedium'], 'string'],
            [['bid'], 'double'],
            [['survey_id', 'scr', 'dsq', 'qfl', 'block'], 'integer'],
            [['survey_id', 'params', 'note'], 'safe'],
            [['status'], 'in', 'range' => [SurveyStatus::ACTIVE, SurveyStatus::INACTIVE, SurveyStatus::TRASH]],
        ];
    }

    public function attributeLabels()
    {
        return [
            'survey_id' => 'Survey',
            'params' => 'Query params',
            'shortParams' => 'Query params',
            'lang' => 'Language',
            'bid' => 'Bid',
            'source' => 'Source',
            'utmMedium' => 'Utm Medium',
        ];
    }

    public function fields()
    {
        $fields = parent::fields();

        $fields['survey'] = 'survey';

        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function extraFields()
    {
        return [
            'survey',
        ];
    }

    public static function getAdditionalFields()
    {
        return [
            'usedTotal' => static::getUsedTotal(),
        ];
    }

    protected static function getUsedTotal()
    {
        $subQuery = new Query();

        $subQuery->select('sum(used) as usedTotal')
            ->from(['subq' => static::tableName()])
            ->where(['subq.survey_id' => new Expression(self::tableName() . '.survey_id')]);

        return $subQuery;
    }

    /**
     * @param scr
     * @return integer
     */
    public function getScrTotal()
    {
        return $this->hasOne(RespondentSurvey::className(), ['alias_id' => 'id'])
            ->where([RespondentSurvey::tableName().'.status' => RespondentSurveyStatus::SCREENED_OUT])
            ->count();
    }

    /**
     * @param dsc
     * @return integer
     */
    public function getDscTotal()
    {
        return $this->hasOne(RespondentSurvey::className(), ['alias_id' => 'id'])
            ->where([RespondentSurvey::tableName().'.status' => RespondentSurveyStatus::DISQUALIFIED])
            ->count();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSurvey()
    {
        return $this->hasOne(Survey::className(), ['id' => 'survey_id']);
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        if ($this->isNewRecord) {
            $this->dt_create = AppHelper::timeUtc();
            $this->dt_modify = null;
            $this->rmsid = $this->createRmsid();
        } else {
            $this->dt_create = $this->oldAttributes['dt_create'];
            $this->dt_modify = AppHelper::timeUtc();
            $this->rmsid = $this->oldAttributes['rmsid'];
        }

        return parent::save($runValidation, $attributeNames);
    }

    /**
     * Creates alias RMSID
     * @return string
     */
    protected function createRmsid()
    {
        $result = '';

        $capitals = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for($i = 0; $i < 2; ++$i) {
            $result.= $capitals{rand(0, strlen($capitals) - 1)};
        }

        $numbers = '0987654321';
        for($i = 0; $i < 3; ++$i) {
            $result.= $numbers{rand(0, strlen($numbers) - 1)};
        }

        return $result;
    }

    public function getSurveyUrl($parameters)
    {
        parse_str($parameters, $queryParams);
        parse_str($this->params, $aliasParams);

        $params = array_merge($queryParams, $aliasParams);
        $params[0] = '/survey/' . $this->survey->rmsid;

        return Url::to($params);
    }

    public static function formatSurveyParams(Alias $data)
    {
        $_params = static::parseParamsString($data->params);
        unset($_params['bd']);
        unset($_params['lang']);
        unset($_params['s']);
        unset($_params['utm_source']);

        foreach ($_params as $key => $param) {
            $_params[$key] = "<span class='text-muted'>{$key}=</span><b>$param</b>";
        }

        return implode(' ', $_params);
    }

    public static function parseParamsString($params)
    {
        $_params = [];

        foreach (explode('&', $params) as $param) {
            if ($param) {
                list($key, $value) = explode('=', $param);
                $_params[$key] = $value;
            }
        }

        ksort($_params);

        return $_params;
    }

    public static function extractBidParam(Alias $data)
    {
        $_params = static::parseParamsString($data->params);
        $bid = ArrayHelper::getValue($_params, 'bd', null);

        return !is_null($bid) ? sprintf('%.3f', $bid) : null;
    }

    public static function extractLangParam(Alias $data)
    {
        $_params = static::parseParamsString($data->params);

        return ArrayHelper::getValue($_params, 'lang', null);
    }

    public static function extractSourceParam(Alias $data)
    {
        $_params = static::parseParamsString($data->params);
        $source = ArrayHelper::getValue($_params, 's', null);

        return $source;
    }

    /**
     * @param Alias $data
     * @return string
     */
    public static function formatAliasUrl($data)
    {
        return Html::a(
            '<span class="text-muted">tgm.mobi/sa/</span>' . $data->rmsid . '</b>',
            'javascript:void(0)',
            [
                'class' => 'alias-url',
                'data-href' => 'https://tgm.mobi/sa/' . $data->rmsid . '?test=1',
            ]
        );
    }

    public static function loadOrCreate($id)
    {
        return $id ? self::findOne($id) : new self();
    }

    public function countUsed()
    {
        ++$this->used;

        return $this->save();
    }

    public function resetCounter()
    {
        $this->used = 0;

        return $this->save();
    }

    public function changeStick()
    {
        $this->is_sticked = !$this->is_sticked;

        return $this->save();
    }

    public function isActive()
    {
        return $this->status === SurveyStatus::ACTIVE;
    }

    public function onSurveyFinished()
    {
        ++$this->cnt_finished;

        return $this->save();
    }

    /**
     * @param $rmsid
     * @return InternalError|static
     */
    public static function startByAlias($rmsid)
    {
        $alias = static::findOne(['rmsid' => $rmsid]);

        if (is_null($alias)) {
            return new InternalError(['message' => 'Unknown Alias "' . $rmsid . '"']);
        }

        if (!$alias->isActive()) {
            return new InternalError(['message' => 'Alias "' . $rmsid . '" is inactive']);
        }

        $alias->countUsed();

        return $alias;
    }

    /**
     * @param $bid
     * @return bid from params field
     */
    public function getBidParams()
    {
        $data = self::find()->where(['id' => $this->id])->one();
        $_params = static::parseParamsString($data->params);
        $source = ArrayHelper::getValue($_params, 'bd', null);

        return $source;
    }

    public function moveToTrash(){
        $this->status = SurveyStatus::TRASH;

        return $this->save();
    }

    public function setBidValue($bidValue)
    {
        $_params = static::parseParamsString($this->params);
        $_params['bd'] = $bidValue;
        $this->params = http_build_query($_params);

        return $this->save();
    }

    /**
     * @param $scr
     * Screened out counter
     * @return boolean
     */
    public function countScr()
    {
        ++$this->scr;

        return $this->save();
    }

    public function resetScr()
    {
        $this->scr = 0;

        return $this->save();
    }

    /**
     * @param $dsq
     * Disqualified counter
     * @return boolean
     */
    public function countDsq()
    {
        ++$this->dsq;

        return $this->save();
    }

    public function resetDsq()
    {
        $this->dsq = 0;

        return $this->save();
    }

    /**
     * @param $qfl
     * Quota Full counter
     * @return boolean
     */
    public function countQfl()
    {
        ++$this->qfl;

        return $this->save();
    }

    public function resetQfl()
    {
        $this->qfl = 0;

        return $this->save();
    }

    /**
     * @param $block
     * user is blocked counter
     * @return boolean
     */
    public function countBlock()
    {
        ++$this->block;

        return $this->save();
    }

    public function resetBlock()
    {
        $this->block = 0;

        return $this->save();
    }

    public function checkAndAddCounter($status)
    {
        switch ($status) {
            case EndpageStatusEnum::QFL:
                $this->countQfl();
                break;
            case EndpageStatusEnum::SCR:
                $this->countScr();
                break;
            case EndpageStatusEnum::DSQ:
                $this->countDsq();
                break;
        }
    }

    public function makeCustomParams()
    {
        $paramsArray = static::parseParamsString($this->params);
        $this->lang = ArrayHelper::getValue($paramsArray, 'lang', null);
        $this->bid = static::extractBidParam($this);
        $this->utmMedium = ArrayHelper::getValue($paramsArray, 'utm_medium', null);
        $this->source = ArrayHelper::getValue($paramsArray, 's', null);
        $paramsArray['a'] = !isset($paramsArray['a']) ? $this->rmsid : $paramsArray['a'];
        $paramsCountryData = !isset($paramsArray['c']) ? $this->survey->country.'+'.strtoupper($this->source).'+'.strtoupper($this->lang) : $paramsArray['c'];
        unset($paramsArray['lang']);
        unset($paramsArray['bd']);
        unset($paramsArray['s']);
        unset($paramsArray['c']);
        unset($paramsArray['utm_source']);
        unset($paramsArray['utm_medium']);
        $this->shortParams = http_build_query($paramsArray)."&c=".$paramsCountryData;
    }

}