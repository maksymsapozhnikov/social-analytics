
$(function () {

});

var appUrls = {
    resultDelete: '/control/result-delete?id=<%- id %>',
    resultView: '/manage/results/view?id=<%- id%>',
    respondentLogs: '/control/logs?log[resp]=<%- rmsid %>',
    respondentBlock: '/control/respondent-blacklist-add?rmsid=<%- rmsid %>',
};

_.each(appUrls, function (value, key) {
    appUrls[key] = _.template(value);
});

function onDeleteClick(event) {
    var resultId = $(event).closest('tr').data('id');

    var modal = new TgmManager.System.ModalConfirmation({
        title: 'Deleting',
        content: '<p>Delete this result?</p>',
    });

    modal.onOk = function () {
        document.location = appUrls.resultDelete({id: resultId});
    };

    modal.open();
}

function onRespondentClick(event) {
    var resultId = $(event).closest('tr').data('id');

    TgmManager.System.blockUI();

    $.get(appUrls.resultView({id: resultId}))
        .done(function () {
            TgmManager.System.unblockUI();

            new TgmManager.System.Modal({
                title: 'Survey Response',
                content: arguments[0],
                modalClass: 'modal-xlg',
                cancelText: false
            }).open();
        })
        .fail(function () {
            TgmManager.System.unblockUI();
            new TgmManager.System.ModalError({
                title: 'Error occurred',
                content: '<p>' + arguments[0].statusText + '</p>'
            }).open();
        });
};

function onBlockId(event) {
    var respondentId = $(event).closest('tr').data('respondent-rmsid');
    var blacklistIcon = '<span title="This Respondent is blacklisted" class="text-danger glyphicon glyphicon-alert"></span>';

    var modal = new TgmManager.System.ModalConfirmation({
        title: 'Respondent blocking',
        content: '<p>Block this respondent by ID??</p>',
    });

    modal.onOk = function () {
        TgmManager.System.blockUI();

        $.getJSON(appUrls.respondentBlock({rmsid: respondentId}), function() {
            $(event).closest('tr').children('td:nth-child(2)').html(blacklistIcon);

            TgmManager.System.unblockUI();

            new TgmManager.System.ModalInfo({
                title: 'Respondent blocking',
                content: '<p>' + arguments[0].message + '</p>',
            }).open();
        }).fail(function() {
            TgmManager.System.unblockUI();
            new TgmManager.System.ModalError({
                title: 'Error',
                content: '<p>' + arguments[0].statusText + '</p>',
            }).open();
        });
    };

    modal.open();
}