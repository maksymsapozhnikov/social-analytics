$(function () {

    var $form = $('#survey-form');

    var submitForm = function() {
        console.log('submitForm');
        if ($form.find('.has-error').length) {
            return;
        }

        TgmManager.System.blockUI();

        $.ajax({
            method: $form.attr('method'),
            url: $form.attr('action'),
            data: $form.serialize(),
        }).done(function () {
            if (window.opener) {
                window.opener.reloadGridView();
                window.close();
            } else {
                window.location = '/control/survey-list';
            }
        }).fail(function () {
            TgmManager.System.unblockUI();
            new TgmManager.System.ModalError({
                title: 'Error',
                content: '<p>An error occurred.</p>',
                showFooter: false
            }).open();
        });
    };

    $('#survey__btn-save').click(function () {
        console.log('#survey__btn-save clicked');
        $form.off('afterValidate').on('afterValidate', submitForm);
        $form.off('beforeSubmit').on('beforeSubmit', function () {
            return false;
        });
        $form.yiiActiveForm('validate', true);
    });
});

function onTopUpsSwitched() {
    $('#survey__topup-row').toggle($('#survey__has_topup').prop('checked'));
}