function buildUrl(url, params) {
    var result = url;

    for(p in params) {
        if(params.hasOwnProperty(p)) {
            result = result.replace('__' + p + '__', params[p]);
        }
    }

    return result;
}

function showMessage(title, message, isError) {
    if (!!isError) {
        $('#message-modal.modal-header').addClass('bg-danger').removeClass('bg-success');
    } else {
        $('#message-modal.modal-header').addClass('bg-success').removeClass('bg-danger');
    }

    $('#message-title').html(title);
    $('#message-body').html(message);
    $('#message-modal').modal('show');
}
