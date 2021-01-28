<?php

use yii\db\Migration;

use app\models\Respondent;
use app\models\RespondentLog;

class m170804_164204_newfp extends Migration
{
    public function up()
    {
        $this->addColumn(Respondent::tableName(), 'fp_canvas', $this->string(50)->comment('Canvas fingerprint, SHA1'));
        $this->addColumn(Respondent::tableName(), 'fp_webgl', $this->string(50)->comment('WebGL fingerprint, SHA1'));

        $this->addColumn(RespondentLog::tableName(), 'fp_canvas', $this->string(50)->comment('Canvas fingerprint, SHA1'));
        $this->addColumn(RespondentLog::tableName(), 'fp_webgl', $this->string(50)->comment('WebGL fingerprint, SHA1'));
    }

    public function down()
    {
        $this->dropColumn(Respondent::tableName(), 'fp_webgl');
        $this->dropColumn(Respondent::tableName(), 'fp_canvas');

        $this->dropColumn(RespondentLog::tableName(), 'fp_webgl');
        $this->dropColumn(RespondentLog::tableName(), 'fp_canvas');
    }
}
