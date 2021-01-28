<?php

use yii\db\Migration;
use yii\helpers\Json;

class m180119_174409_cars extends Migration
{
    const FILENAME = '@app/db/cars01.txt';
    const IDENTIFIER = 'marki_modeli_03_2017';

    public function safeUp()
    {
        $this->createTable('survey_options', [
            'id' => $this->primaryKey()->comment('PK'),
            'identifier' => $this->string()->notNull()->unique()->comment(''),
            'json' => $this->text(),
        ]);

        $filename = \Yii::getAlias(self::FILENAME);
        $lines = file($filename);
        $dataToLoad = [];
        foreach ($lines as $line) {
            list($marka, $marka_id, $model, $model_id) = explode("\t", trim($line));
            $dataToLoad[] = [
                'marka' => trim($marka),
                'marka_id' => trim($marka_id),
                'model' => trim($model),
                'model_id' => trim($model_id),
            ];
        }
        $this->insert('survey_options', [
            'identifier' => self::IDENTIFIER,
            'json' => Json::encode($dataToLoad),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('survey_options');
    }

}
