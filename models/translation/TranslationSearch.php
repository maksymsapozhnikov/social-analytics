<?php
namespace app\models\translation;

use app\models\Language;
use yii\data\ActiveDataProvider;

class TranslationSearch extends Language
{
    public $cnt_nottranslated;
    public $cnt_messages;

    public function search($params = [])
    {
        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $subMessages = SourceMessage::find()->select('count(*) as cnt_messages');
        $subTranslat = Message::find()->select('language, sum(case when translation is null then 1 else 0 end) as cnt_nottranslated')->groupBy('language');

        $query->innerJoin(['sm' => $subMessages], '1 = 1');
        $query->leftJoin(['st' => $subTranslat], 'language.lang = st.language');

        $query->addSelect([
            'language.*',
            'cnt_nottranslated' => 'st.cnt_nottranslated',
            'cnt_messages' => 'sm.cnt_messages',
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        return $dataProvider;
    }
}
