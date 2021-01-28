<?php
/**
 * Creates required database tables
 */

use yii\db\Migration;
use app\models\RespondentStatus;
use app\models\SurveyStatus;
use app\models\RespondentSurveyStatus;

class m170606_074853_init extends Migration
{
    public function up()
    {
        $this->createTable('respondent', [
            'id' => $this->primaryKey(),
            'rmsid' => $this->string(7)->notNull()->unique()->comment('Unique local string Respondent ID, 7 chars'),
            'device_id' => $this->string()->comment('DeviceAtlas Device ID'),
            'fingerprint_id' => $this->string()->notNull()->comment('Fingerprintjs2 ID'),
            'failed_tryings' => $this->integer()->notNull()->defaultValue(0)->comment('Number of respondent failed tryings'),
            'status' => $this->integer(1)->notNull()->defaultValue(RespondentStatus::ACTIVE)->comment('Respondent status'),

            'language' => $this->string(16)->comment('Language, "lang"'),
            'traffic_source' => $this->string()->comment('Traffic source, "s"'),

            'additional' => $this->text()->notNull()->comment('Additional respondent attributes'),

            'device_vendor' => $this->string()->defaultValue('')->comment('The company/organisation that provides a device, browser or other component'),
            'device_model' => $this->string()->defaultValue('')->comment('The model name of a device, browser or some other component'),
            'device_marketing_name' => $this->string()->defaultValue('')->comment('The marketing name for a device'),
            'device_manufacturer' => $this->string()->defaultValue('')->comment('Primary organisation creating the device'),
            'device_year_released' => $this->integer()->defaultValue(null)->comment('This is the year that the device was released'),

            'os_vendor' => $this->string()->defaultValue('')->comment('The supplier of the operating system'),
            'os_name' => $this->string()->defaultValue('')->comment('The name of the Operating System installed on the device'),
            'os_family' => $this->string()->defaultValue('')->comment('The general group name of the operating system'),
            'os_version' => $this->string()->defaultValue('')->comment('The Operating System initial version installed on the device'),

            'device_atlas' => $this->text()->notNull()->comment('DeviceAtlas all given properties as json'),
        ]);

        $this->createTable('survey', [
            'id' => $this->primaryKey(),
            'rmsid' => $this->string(5)->notNull()->unique()->comment('Unique local string Survey ID, 5 chars'),
            'name' => $this->string(255)->notNull()->comment('Survey name'),
            'url' => $this->string()->notNull()->comment('Surveygizmo URL'),
            'status' => $this->integer(1)->notNull()->defaultValue(SurveyStatus::ACTIVE)->comment('Survey status: active or inactive'),
        ]);

        $this->createTable('respondent_survey', [
            'id' => $this->primaryKey(),
            'respondent_id' => $this->integer()->notNull()->comment('FK respondent.id'),
            'survey_id' => $this->integer()->notNull()->comment('FK survey.id'),
            'status' => $this->integer(1)->notNull()->defaultValue(RespondentSurveyStatus::ACTIVE),
            'uri' => $this->string()->notNull(),
        ]);

        $this->addForeignKey('fk_RespondentSurvey_respondent', 'respondent_survey', 'respondent_id', 'respondent', 'id');
        $this->addForeignKey('fk_RespondentSurvey_survey', 'respondent_survey', 'survey_id', 'survey', 'id');
        $this->createIndex('uq_RespondentSurvey_pair', 'respondent_survey', ['respondent_id', 'survey_id'], true);
    }

    public function down()
    {
        $this->dropTable('respondent_survey');
        $this->dropTable('survey');
        $this->dropTable('respondent');
    }
}
