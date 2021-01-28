<?php
/**
 * @var $this yii\web\View
 * @var $model \app\models\Translation
 */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;

if ($model->isNewRecord) {
    $this->title = 'Creating Translation';
} else {
    $this->title = 'Editing Translation #' . $model->id . ': ' . $model->oldAttributes['lang'];
}

?>

<h1><?= $this->title ?></h1>

<?php $form = ActiveForm::begin([
    'id' => 'survey-form',
    'action' => Url::to(['/control/translation-edit', 'id' => $model->id]),
    'enableClientValidation' => true,
]); ?>

    <div class="panel panel-default">
        <div class="panel-body">
            <div class="row">
                <div class="col-xs-12">
                    <?= $form->field($model, 'lang')->textInput(['maxlength' => 6, 'size' => 6])->label('Language (lang parameter)') ?>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <?= $form->field($model, 'msg_1_hello')->textInput(['maxlength' => true])->label('Message "Looking for Survey"') ?>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <?= $form->field($model, 'msg_2_closed')->widget(\dosamigos\ckeditor\CKEditor::className(), [
                        'options' => [
                            'rows' => 4,
                        ],
                        'preset' => 'basic',
                        'clientOptions' => [
                            'language' => 'en',
                            'allowedContent' => true,
                            'toolbar' => [
                                ['name' => 'document', 'items' => ['Source', '-', 'Preview', ] ],
                                ['name' => 'clipboard', 'items' => ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] ],
                                ['name' => 'basicstyles', 'items' => ['Bold', 'Italic', 'Underline', 'Subscript', 'Superscript', 'NumberedList', 'BulletedList'] ],
                                '/',
                                ['name' => 'styles', 'items' => [ 'Format', 'Font', 'FontSize', 'Styles', ] ],
                                ['name' => 'links', 'items' => [ 'Link', 'Unlink'] ],
                                ['name' => 'paragraph', 'items' => [ 'Outdent', 'Indent', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']],
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <?= $form->field($model, 'msg_3_wrong_phone')->textInput(['maxlength' => true]) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <?= $form->field($model, 'msg_4_wrong_currency')->textInput(['maxlength' => true]) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <?= $form->field($model, 'msg_5_postpaid')->textInput(['maxlength' => true]) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <?= $form->field($model, 'msg_6_payed')->textInput(['maxlength' => true]) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group text-center">
        <?= Html::submitButton($model->isNewRecord ? 'Create Translation' : 'Save Translation', ['class' => 'btn-lg btn-primary']) ?>
    </div>

<?php ActiveForm::end(); ?>