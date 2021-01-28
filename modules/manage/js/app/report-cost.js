$(function () {
    var constCostReportKey = 'manage/report/cost';

    $.fn.selectRange = function(start, end) {
        var e = document.getElementById($(this).attr('id')); // I don't know why... but $(this) don't want to work today :-/
        if (!e) return;
        else if (e.setSelectionRange) { e.focus(); e.setSelectionRange(start, end); } /* WebKit */
        else if (e.createTextRange) { var range = e.createTextRange(); range.collapse(true); range.moveEnd('character', end); range.moveStart('character', start); range.select(); } /* IE */
        else if (e.selectionStart) { e.selectionStart = start; e.selectionEnd = end; }
    };

    var updateTotals = function () {
        /* Totals */
        var totalCost = 0;
        var totalDone = Number($('tr.total-row').data('all-done'));

        $('tr.subtotal-row').each(function() {
            totalCost += $(arguments[1]).data('all-cost') + ($(arguments[1]).data('manual-adjustment') || 0);
        });
        $('tr.total-row td.strow-all-cost').text(sprintf('%.2f', totalCost));
        $('tr.total-row td.strow-all-cpi').text(sprintf('%.2f', totalCost / totalDone));

        $('tr.total-row td.strow-all-cost').removeClass('text-danger');
        $('tr.total-row td.strow-all-cpi').removeClass('text-danger');
        if (totalCost < 0) {
            $('tr.total-row td.strow-all-cost').addClass('text-danger');
            $('tr.total-row td.strow-all-cpi').addClass('text-danger');
        }
    };

    var addAdjustmentToCell = function ($cell, value) {
        var $tr = $cell.closest('tr.subtotal-row');

        $cell.text(sprintf('%.2f', value));
        $cell.removeClass('text-danger');
        if (value < 0) {
            $cell.addClass('text-danger');
        }

        $tr.data('manual-adjustment', value);

        var allCost = Number($tr.data('all-cost')) + value;
        var allDone = Number($tr.data('all-done'));
        var allCpi = allCost / allDone;

        $tr.find('td.strow-all-cost').removeClass('text-danger');
        $tr.find('td.strow-all-cpi').removeClass('text-danger');
        if (allCost < 0) {
            $tr.find('td.strow-all-cost').addClass('text-danger');
            $tr.find('td.strow-all-cpi').addClass('text-danger');
        }

        $tr.find('td.strow-all-cost').text(sprintf('%.2f', allCost));
        $tr.find('td.strow-all-cpi').text(sprintf('%.2f', allCpi));

        updateTotals();
    };

    var onManualAdjust = function () {
        var event = arguments[0];

        event.stopPropagation();
        var $cell = $(event.target);
        var $tr = $cell.closest('tr.subtotal-row');

        var form = new TgmManager.Forms.CostManualAdjustment({
            model: {
                project_id: $tr.data('project-id'),
                country: $tr.data('country'),
                value: Number($cell.text()) || 0,
            }});

        var editingModal = new TgmManager.System.Modal({
            title: 'Manual adjustment',
            content: form,
            okCloses: true,
            modalClass: 'modal-sm'
        });

        editingModal.on('shown', function () {
            $('#efInput').focus().selectRange(0, 10000);
        });

        editingModal.onOk = function () {
            setStoredAdjustment($tr.data('project-id'), $tr.data('country'), form.getValue());
            addAdjustmentToCell($cell, form.getValue());
        };

        editingModal.open();
    };

    var setStoredAdjustment = function(projectId, countryName, value) {
        $.post('/manage/report/cost-adjust', {
            project_id: projectId,
            country: countryName,
            value: value
        });
    };

    var onToggleSubtotalRow = function () {
        var toggleClass = $(this).data('toggle-class');

        $('.' + toggleClass).toggle();
    };

    var onClickProjectRename = function () {
        var event = arguments[0];

        event.stopPropagation();

        var $cell = $(event.target);
        var $tr = $cell.closest('tr.subtotal-row');

        var form = new TgmManager.Forms.CostProjectRename({
            model: {
                project_id: $tr.data('project-id'),
                country: $tr.data('country')
            }});

        var editingModal = new TgmManager.System.Modal({
            title: 'Renaming project',
            content: form,
            okCloses: true,
            okText: 'Rename'
        });

        editingModal.on('shown', function () {
            form.$elProject().focus();
        });

        editingModal.onOk = function () {
            TgmManager.System.blockUI();
            $.post('/manage/report/cost-rename', {
                'project-id-old': form.model.project_id,
                'country-old': form.model.country,
                'project-id-new': form.getProject(),
                'country-new': form.getCountry(),
            })
                .done(function(result) {
                    document.location.reload(true);
                })
                .fail(function () {
                    TgmManager.System.unblockUI();
                    new TgmManager.System.ModalError({
                        title: 'Error occurred',
                        content: '<p>' + arguments[0].statusText + '</p>'
                    }).open();
                });
        };

        editingModal.open();
    };

    $('.manual-cell').each(function () {
        var $cell = $(arguments[1]);
        var $tr = $cell.closest('tr.subtotal-row');
        var storedValue = $tr.data('manual-adjustment');

        if (storedValue) {
            addAdjustmentToCell($cell, storedValue);
        }
    });

    $('.project-cell').click(onClickProjectRename);

    $('.subtotal-row').click(onToggleSubtotalRow);
    $('.manual-cell').click(onManualAdjust);
    $('.details-row').hide();

    $('.href-source-toggle').attr({
        href: 'javascript:void(0)'
    });

    $('.href-source-toggle').click(function (e) {
        var $href = $(e.target);
        var source = $href.data('source');

        var cellType = '.cell-type-' + source;

        $href.toggleClass('href-on');
        $(cellType).toggle();

        var stored = $.localStorage.get(constCostReportKey) || {};
        stored.columns = stored.columns || {};
        stored.columns[source] = !$href.hasClass('href-on');
        $.localStorage.set(constCostReportKey, stored);
    });

    var stored = $.localStorage.get(constCostReportKey) || {};
    stored.columns = stored.columns || {};
    $('.href-source-toggle').each(function (i, el) {
        var source = $(el).data('source');
        stored.columns[source] = _.isNull(stored.columns[source]) ? true : stored.columns[source];
        if (!stored.columns[source]) {
            $(el).click();
        }
    })
});