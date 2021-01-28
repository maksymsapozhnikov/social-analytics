<?php

\yii\bootstrap\Modal::begin([
    'options' => [
        'id' => 'message-modal',
        'tabindex' => false,
    ],
    'headerOptions' => [
        'class' => 'bg-success',
    ],
    'header' => '<h4 class="modal-title" id="message-title"></h4>',
    'footer' => ' <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>',
]);

?>
    <p id="message-body"></p>
<?php \yii\bootstrap\Modal::end(); ?>

