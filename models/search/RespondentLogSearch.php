<?php
namespace app\models\search;

use app\components\AppHelper;
use app\models\RespondentLog;
use app\models\Survey;
use yii\data\ActiveDataProvider;

class RespondentLogSearch extends RespondentLog
{
    public $start_date;
    public $end_date;
    public $survey_rmsid;
    public $resp;

    public function rules()
    {
        return [
            [['survey_rmsid', 'start_date', 'end_date', 'ip', 'fingerprint_id', 'resp'], 'safe'],
        ];
    }

    public function formName()
    {
        return 'log';
    }

    /**
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = RespondentLog::find();
        $query->joinWith(['respondent', 'survey']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pagesize' => 50,
            ],
        ]);

        $loaded = $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        if (!$loaded) {
            $this->start_date = $this->start_date ?: date('d.m.Y', strtotime('today'));
            $this->end_date = $this->end_date ?: date('d.m.Y', strtotime('today'));
        }

        $query->andFilterWhere(['IN', 'respondent_log.survey_rmsid', $this->survey_rmsid])
            ->andFilterWhere(['=', 'respondent_log.ip', $this->ip])
            ->andFilterWhere(['=', 'respondent_log.fingerprint_id', $this->fingerprint_id])
            ->andFilterWhere(['=', 'respondent.rmsid', $this->resp]);

        if ($this->start_date) {
            $query->andFilterWhere(['>=', 'respondent_log.create_dt', AppHelper::timeUtc(strtotime($this->start_date . '00:00:00'))]);
        }

        if ($this->end_date) {
            $query->andFilterWhere(['<=', 'respondent_log.create_dt', AppHelper::timeUtc(strtotime($this->end_date . '23:59:59'))]);
        }

        return $dataProvider;
    }

    public function getExportFilename()
    {
        $this->survey_rmsid = !is_array($this->survey_rmsid) ? [] : $this->survey_rmsid;
        if (count($this->survey_rmsid) > 1) {
            $middleName = implode('_', $this->survey_rmsid);
        } else if (1 == count($this->survey_rmsid)) {
            $name = Survey::findOne(['rmsid' => $this->survey_rmsid])->name;
            $middleName = $this->survey_rmsid[0] . '_' . $name;
        } else {
            $middleName = 'All_Surveys';
        }

        return 'Logs_' . $middleName . '_' . date('ymd-his');
    }
}
