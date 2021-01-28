<?php
namespace app\components;

use app\models\Info;
use app\models\RespondentSurveyStatus;
use app\modules\manage\models\Campaign;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * @todo Temporary solution, please refactor
 */
class QueriesHelper
{
    /**
     * Countries present in respondent responses
     * @return array
     * @throws \yii\db\Exception
     */
    public static function getResponseCountries()
    {
        $query = <<<SQL
        select distinct jsf_country
        from respondent_survey
        where jc_hash is not null
          and jpi_hash is not null
        order by 1
SQL;
        $list = \Yii::$app->db->createCommand($query)->queryColumn();

        return array_combine($list, $list);
    }

    /**
     * @return array
     * @throws \yii\db\Exception
     */
    public static function getResponseProjectIdentifiers()
    {
        $query = <<<SQL
        select distinct jsf_project_id
        from respondent_survey
        where jpi_hash is not null
        order by 1
SQL;
        $list = \Yii::$app->db->createCommand($query)->queryColumn();

        return array_combine($list, $list);
    }

    public static function getAllCampaigns()
    {
        $campaignsModels = Campaign::find()->select(['id', 'name'])->orderBy('name')->asArray()->all();

        return ArrayHelper::map($campaignsModels, 'id', 'name');
    }

    /**
     * @param $query
     * @return array
     * @throws \yii\db\Exception
     */
    protected static function select2ize($query)
    {
        $list = \Yii::$app->db->createCommand($query)->queryAll();

        $list = ArrayHelper::map($list, 'id', 'name');

        return $list;
    }

    public static function select2Surveys()
    {
        return self::select2ize('select rmsid as id, concat(name, \' (\', rmsid ,\')\') as name from survey order by 2');
    }

    public static function select2Aliases()
    {
        return static::select2ize('select rmsid as id, rmsid as name from survey_alias order by 2');
    }

    public static function select2Countries()
    {
        return static::select2ize('select country as id, country as name from survey group by country order by 2');
    }

    public static function getSurveysList()
    {
        return static::select2ize('select id, concat(name, \' (\', rmsid ,\')\') as name from survey order by 2');
    }

    public static function getSurveysId()
    {
        return self::select2ize('select id, concat(name, \' (\', rmsid ,\')\') as name from survey order by 2');
    }

    public static function getAllCountries()
    {
        $list = \Yii::$app->db->createCommand('select name from country order by 1')->queryAll();

        $list = ArrayHelper::getColumn($list, 'name');

        return array_combine($list, $list);
    }

    public static function getAnswersValues($question)
    {
        $query = <<<SQL
        select
          distinct substring_index(substr(response from position('"{$question}":"' in response) + length('"{$question}":"')), '"', 1) as "value"
        from respondent_survey rs where status <> :status
        and position('"{$question}":"' in response) > 0
        order by 1
SQL;

        $exceptStatus = RespondentSurveyStatus::ACTIVE;
        $list = \Yii::$app->db->createCommand($query)->bindParam('status', $exceptStatus)->queryAll();
        if ('Country' == $question) {
            $list = ArrayHelper::getColumn($list, 'value', false);
            $list = array_map(['app\components\FormatHelper', 'clearResponse'], $list);
            $list = array_combine($list, $list);
            asort($list);

            return $list;
        }

        return ArrayHelper::map($list, 'value', 'value');
    }

    public static function getAllCurrencies()
    {
        return self::select2ize('select code as id, concat(code, \' (\', name ,\')\') as name from currency order by 1');
    }

    /**
     * @return array
     * @throws \Exception
     */
    public static function getAnswersKeys()
    {
        $overallQuestions = Json::decode(Info::value(Info::QUESTIONNAIRE_QUESTIONS));
        $overallQuestions = array_values(array_unique(array_map('strtoupper', $overallQuestions)));

        return $overallQuestions;
    }

    /**
     * @param array $keys
     * @throws \Exception
     */
    public static function addAnswerKeys(array $keys)
    {
        $overallQuestions = self::getAnswersKeys();

        /** checks if anything changed. */
        if (empty(array_diff($keys, $overallQuestions))) {
            return;
        }

        $questions = array_unique(array_merge($overallQuestions, $keys));
        sort($questions);

        Info::value(Info::QUESTIONNAIRE_QUESTIONS, Json::encode($questions));
    }
}
