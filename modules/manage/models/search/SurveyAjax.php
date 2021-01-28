<?php
namespace app\modules\manage\models\search;

use app\models\Survey;
use app\models\SurveyStatus;
use yii\db\Expression;

class SurveyAjax extends Survey
{
    public function attributes()
    {
        return [
            'id', 'text',
        ];
    }

    public static function search($term)
    {
        $query = SurveyAjax::find()
            ->select([
                'id' => 'id',
                'text' => new Expression("concat(name, ' (', rmsid, ')')")
            ])
            ->where(['like', 'name', $term])
            ->orWhere(['like', 'rmsid', $term])
            ->orderBy(['name' => SORT_ASC])
            ->limit(20);

        return $query->all();
    }

    public static function searchByParams($term, $status = false, $sortParams = false, $limit = 20, $country = false)
    {
        $surveyStatus = false;
        if ($status) {
            switch ($status) {
                case 'active':
                    $surveyStatus = SurveyStatus::ACTIVE;
                    break;
                case 'inactive':
                    $surveyStatus = SurveyStatus::INACTIVE;
                    break;
                default:
                    $surveyStatus = false;
            }
        }

        switch ($sortParams) {
            case 'date_create_DESC':
                $sortOrder = ['dt_created' => SORT_DESC];
                break;
            case 'date_create_ASC':
                $sortOrder = ['dt_created' => SORT_ASC];
                break;
            default:
                $sortOrder = ['name' => SORT_ASC];
        }

        $query = SurveyAjax::find()
            ->select([
                'id' => 'id',
                'text' => new Expression("concat(name, ' (', rmsid, ')')"),
            ]);

        if ($surveyStatus) {
            $query->where(['status' => $surveyStatus]);
        }
        if ($country) {
            $query->andWhere(['country' => $country]);
        }
        $query->andWhere([
                'or',
                ['like', 'name', $term],
                ['like', 'rmsid', $term],
            ])
            ->orderBy($sortOrder);
        if ($limit) {
            $query->limit($limit);
        }

        return $query->all();
    }
}