<?php
namespace app\components\helpers;

class CarsOptionsHelper
{
    const BRAK_MODELU_ID = 0;
    const INNE_MODELE_ID = 1;

    const BRAK_MODELU = 'brak modelu';
    const INNE_MODELE = 'inne modele';

    /**
     * Performs a sorting and values selecting for the Cars only
     */
    public static function prepareModel($options)
    {
        $result = [];

        foreach ($options as $option) {
            if (!in_array($option['model_id'], [self::BRAK_MODELU_ID, self::INNE_MODELE_ID] )) {
                $result[] = $option['model'];
            }
        }

        sort($result);
        $result[] = self::INNE_MODELE;
        $result[] = self::BRAK_MODELU;

        return array_map(function($value) {
            return ['model' => $value];
        }, $result);
    }
}
