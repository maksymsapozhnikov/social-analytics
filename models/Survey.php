<?php
namespace app\models;

use app\components\AppHelper;
use app\components\behaviors\SurveySettingsBehavior;
use app\components\RespondentIdentity;
use app\modules\manage\models\Campaign;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\db\Query;

/**
 * Class Survey
 * @property integer $id
 * @property string $rmsid
 * @property string $name
 * @property string $has_topup
 * @property string $topup_currency
 * @property double $topup_value
 * @property double $topup_sms
 * @property double $spent Spent value, USD
 * @property double $strict Strict respondent checking
 * @property integer $campaign_id
 * @property Campaign $campaign
 * @property boolean $tgm_recruitment
 * @property integer $status
 * @property float $topup_spent
 * @property integer $dt_created
 * @property float $stat_dirty_score
 * @property float $stat_time_score
 * @property float $stat_avg_time_score
 * @property integer $stat_count_act
 * @property integer $stat_count_scr
 * @property integer $stat_count_dsq
 * @property integer $stat_count_fin
 * @property integer $stat_count_all
 * @property float $stat_bid_summa
 * @property string $url
 * @property string $url_end
 * @property string $country
 * @property integer $strict_recruitment
 * @property integer $postback_required
 * @property array $settings
 * @property string $project_id
 * @property integer $panel_register_type
 */
class Survey extends ActiveRecord
{
    const RMSID_LENGTH = 7;

    public static function tableName()
    {
        return 'survey';
    }

    public function rules()
    {
        return [
            [['name', 'url', 'country', 'sample', 'status'], 'required'],
            [['topup_value', 'topup_currency'], 'required',
                'when' => function($model) { return $model->has_topup; },
                'whenClient' => "function (attribute, value) {return $('#survey__has_topup').prop('checked');}",
            ],
            [['topup_sms'], 'string',
                'max' => 30,
                'when' => function($model) { return $model->has_topup; },
                'whenClient' => "function (attribute, value) {return $('#survey__has_topup').prop('checked');}",
            ],
            [['name', 'country'], 'string', 'max' => 255],
            [['url'], 'url'],
            [['url_end'], 'url'],
            [['sample', 'campaign_id'], 'integer'],
            [['rmsid'], 'string', 'max' => self::RMSID_LENGTH],
            [['status'], 'in', 'range' => [SurveyStatus::ACTIVE, SurveyStatus::INACTIVE, SurveyStatus::TRASH]],
            [['topup_value'], 'double', 'min' => 0],
            [['topup_currency'], 'string', 'max' => 3],
            [['project_id'], 'trim'],
            [['name', 'url', 'url_end', 'country', 'sample', 'has_topup', 'topup_value', 'topup_currency', 'strict', 'campaign_id',
                'tgm_recruitment', 'strict_recruitment', 'settings', 'project_id', 'panel_register_type'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'topup_sms' => 'SMS',
            'campaign_id' => 'Campaign',
            'project_id' => 'Project ID',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        if ($this->isNewRecord && is_null($this->rmsid)) {
            /** @todo check rmsid uniqueness before save & generate the new one if required */
            $this->rmsid = $this->createRmsid(self::RMSID_LENGTH);
            $this->dt_created = AppHelper::timeUtc();
        }

        if (!$this->has_topup) {
            $this->topup_value = null;
            $this->topup_currency = null;
        }

        return parent::save($runValidation, $attributeNames);
    }

    public static function getActiveSurvey($rmsid)
    {
        return Survey::findOne(['rmsid' => $rmsid, 'status' => SurveyStatus::ACTIVE]);
    }

    protected function createRmsid($length = 5)
    {
        $result = '';

        $capitals = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for($i = 0; $i < 3; ++$i) {
            $result.= $capitals{rand(0, strlen($capitals) - 1)};
        }

        $numbers = '0987654321';
        for($i = 0; $i < 4; ++$i) {
            $result.= $numbers{rand(0, strlen($numbers) - 1)};
        }

        return $result;
    }

    public function delete()
    {
        if ($this->status != SurveyStatus::TRASH) {
            throw new \Exception('Can\'t delete this survey.');
        }

        /** @todo add transaction, find way to use ActiveRecord to delete relatives */

        \Yii::$app->db->createCommand('DELETE FROM respondent_survey WHERE survey_id = :id')
            ->bindValues(['id' => $this->id])
            ->execute();

        return parent::delete();
    }

    public function toTrash()
    {
        $this->status = SurveyStatus::TRASH;

        return $this->save();
    }

    public function isTrash()
    {
        return $this->status === SurveyStatus::TRASH;
    }

    public function restore()
    {
        if (!$this->isTrash()) {
            return false;
        }

        $this->status = SurveyStatus::INACTIVE;

        return $this->save();
    }

    /**
     * @param RespondentIdentity $identity
     * @return string
     */
    public function buildRespondentUrl($identity)
    {
        return $this->requiresRecruitment($identity) ?
            $this->getLocalRecruitmentUrl($identity) :
            $this->getExternalSurveyUrl($identity);
    }

    /**
     * @param RespondentIdentity $identity
     * @return bool
     */
    public function requiresRecruitment(RespondentIdentity $identity)
    {
        return $this->tgm_recruitment && !$identity->hasFinishedRecruitmentSurvey($this);
    }

    /**
     * @param RespondentIdentity $identity
     * @return string
     */
    protected function getLocalRecruitmentUrl(RespondentIdentity $identity)
    {
        return '/rcs/' . $this->rmsid;
    }

    /**
     * @param RespondentIdentity $identity
     * @return string
     */
    protected function getExternalSurveyUrl(RespondentIdentity $identity)
    {
        $resp = $identity->respondent;

        $params = [
            'sglocale' => '{lang}',
            'sguid' => '{sguid}',
            'sur' => '{sur}',
            'kn' => '{kn}',
        ];
        $additional = $this->getAdditionalQueryString($identity->getRms('uri'));
        $parts = parse_url($this->url);
        parse_str($parts['query'], $query);

        $query = array_merge($query, $additional, $params);

        $replacements = [
            '{lang}' => $resp->language,
            '{sguid}' => $resp->rmsid,
            '{sur}' => $this->rmsid,
            '{kn}' => $this->country,
        ];

        $query = array_map(function (&$element) use ($replacements) {
            return str_replace(array_keys($replacements), array_values($replacements), $element);
        }, $query);

        $parts['query'] = http_build_query($query);

        return AppHelper::unparseUrl($parts);
    }

    /**
     * @todo move this properly class
     */
    protected function getAdditionalQueryString($url, $except = ['rmsid', 'lang', 'sglocale', 'sguid', 'kn', 'user_id',])
    {
        $result = parse_url($url);

        if (!isset($result['query'])) {
            return '';
        }

        $query = explode('&', $result['query']);

        $items = [];

        foreach($query as $value) {
            list($key, $val) = explode('=', $value);
            if (!in_array($key, $except)) {
                $items[$key] = $val;
            }
        }

        return $items;
    }

    public function updateSpent($value)
    {
        \Yii::$app->db->createCommand()
            ->update(static::tableName(), [
                'topup_spent' => new Expression('topup_spent + :spent')
            ], 'id = :id')
            ->bindValue(':spent', $value)
            ->bindValue(':id', $this->id)
            ->execute();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCampaign()
    {
        return $this->hasOne(Campaign::className(), ['id' => 'campaign_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['id' => 'country_id']);
    }

    /**
     * Returns average dirty-score for the survey.
     * @return float|null
     */
    public function getAvgDirtyScore()
    {
        $query = (new Query())
                    ->select('1.0 * sum(dirty_score) / count(*)')
                    ->from(RespondentSurvey::tableName())
                    ->where([
                        'survey_id' => $this->id,
                        'status' => RespondentSurveyStatus::FINISHED,
                    ]);

        return $query->scalar();
    }

    /**
     * Returns average dirty-score for the survey.
     * @return float|null
     */
    public function getAvgTimingScore()
    {
        $query = (new Query())
            ->select('1.0 * sum(timing_score_avg) / count(*)')
            ->from(RespondentSurvey::tableName())
            ->where([
                'survey_id' => $this->id,
                'status' => RespondentSurveyStatus::FINISHED,
            ]);

        return $query->scalar();
    }

    /**
     * Returns average dirty-score for the survey.
     * @return float|null
     */
    public function getSumTimingScore()
    {
        $query = (new Query())
            ->select('1.0 * sum(timing_score_sum) / count(*)')
            ->from(RespondentSurvey::tableName())
            ->where([
                'survey_id' => $this->id,
                'status' => RespondentSurveyStatus::FINISHED,
            ]);

        return $query->scalar();
    }

    /**
     * @param integer $surveyId
     * @throws
     */
    public static function updateStatistics($surveyId)
    {
        $rsTable = RespondentSurvey::tableName();

        $stFin = RespondentSurveyStatus::FINISHED;
        $stScr = RespondentSurveyStatus::SCREENED_OUT;
        $stAct = RespondentSurveyStatus::ACTIVE;
        $stDsq = RespondentSurveyStatus::DISQUALIFIED;

        $query = <<<SQL
        select count(*)                                                             as "stat_count_all",
               sum(case when status = {$stFin} then bid else null end)              as "stat_bid_summa",
               sum(case when status = {$stFin} then 1 else null end)                as "stat_count_fin",
               sum(case when status = {$stDsq} then 1 else null end)                as "stat_count_dsq",
               sum(case when status = {$stScr} then 1 else null end)                as "stat_count_scr",
               sum(case when status = {$stAct} then 1 else null end)                as "stat_count_act",
               avg(case when status = {$stFin} then timing_score_avg else null end) as "stat_avg_time_score",
               avg(case when status = {$stFin} then timing_score_sum else null end) as "stat_time_score",
               avg(case when status = {$stFin} then dirty_score else null end)      as "stat_dirty_score"
        from {$rsTable}
        where survey_id = {$surveyId}
        group by survey_id
SQL;

        $data = static::getDb()->createCommand($query)->queryOne();
        if (!empty($data)) {
            static::getDb()->createCommand()->update(static::tableName(), $data, ['id' => $surveyId])->execute();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => SurveySettingsBehavior::class,
            ],
        ];
    }
}
