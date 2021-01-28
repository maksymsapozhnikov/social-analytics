<?php

use yii\db\Migration;
use app\models\Respondent;
use app\models\RespondentLog;

class m170803_212205_additionalFingerprints extends Migration
{
    public function up()
    {
        $this->addColumn(Respondent::tableName(), 'fp_audio', $this->string(50)->comment('Audio Context fingerprint, SHA1'));
        $this->addColumn(Respondent::tableName(), 'fp_rects', $this->string(50)->comment('getClientRects fingerprint, SHA1'));

        $this->addColumn(RespondentLog::tableName(), 'fp_audio', $this->string(50)->comment('Audio Context fingerprint, SHA1'));
        $this->addColumn(RespondentLog::tableName(), 'fp_rects', $this->string(50)->comment('getClientRects fingerprint, SHA1'));
    }

    public function down()
    {
        $this->dropColumn(Respondent::tableName(), 'fp_rects');
        $this->dropColumn(Respondent::tableName(), 'fp_audio');

        $this->dropColumn(RespondentLog::tableName(), 'fp_rects');
        $this->dropColumn(RespondentLog::tableName(), 'fp_audio');
    }
}
