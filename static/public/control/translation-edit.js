function toggleMessagesVisibility() {
    if ($('#show-selector').prop('checked')) {
        $('#translations-grid tbody tr td:nth-child(3)').each(function (k, val) {
            if ($(this).text() !== '') {
                $(this).closest('tr').addClass('hide');
            } else {
                $(this).closest('tr').removeClass('hide');
            }
        });
    } else {
        $('#translations-grid tbody tr').removeClass('hide');
    }
}

window.translationModal = $('#translation-modal');

function onEditClick(event) {
    var url = '/translation/update-message';
    var rowContext = $(event.target).closest('tr');
    var translationId = rowContext.data('id');
    var previousValue = $('td:nth-child(3)', rowContext).text();

    $('#message-modal__source-value').html($('td:nth-child(2)', rowContext).text());
    $('#message-modal__value').val(previousValue);
    $('#message-modal__submit').off('click').on('click', function() {
        translationModal.modal('hide');
        $('td:nth-child(3)', rowContext).off('click');
        $('td:nth-child(3)', rowContext).html('<ul class="fa-ul" style="margin-bottom:0"><li><i class="fa-li fa fa-spinner fa-spin"></i> <i>Saving, please wait...</i></li></ul>');
        $.post(url, {
            id: translationId,
            message: $('#message-modal__value').val(),
            lang: appRms.lang
        }, function(response) {
            if (!response.isError) {
                showMessage('Translation saved', 'Translation has been saved successfully.');
                $('td:nth-child(3)', rowContext).off('click').on('click', onEditClick);
                $('td:nth-child(3)', rowContext).html(response.translation.translation);
                toggleMessagesVisibility();
            } else {
                showMessage('Save error', 'Error occured:<p>' + response.message + '</p>');
                $('td:nth-child(3)', rowContext).off('click').on('click', onEditClick);
                $('td:nth-child(3)', rowContext).html(previousValue);
            }
        }, 'json')
            .fail(function(response) {
                showMessage('Save error', 'Error occured:<p>' + response.statusText + '</p>');
                $('td:nth-child(3)', rowContext).off('click').on('click', onEditClick);
                $('td:nth-child(3)', rowContext).html(previousValue);
            });
    });

    $('#message-modal__cancel').off('click').on('click', function() {
        translationModal.modal('hide');
    });

    translationModal.modal('show');
}

$('#translations-grid tbody tr td:nth-child(3)').click(onEditClick);
$('#show-selector').change(toggleMessagesVisibility);
$('#translations-grid__close-button').click(function() {
    document.location = '/translation';
});
toggleMessagesVisibility();
