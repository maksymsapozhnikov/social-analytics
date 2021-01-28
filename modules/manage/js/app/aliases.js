$(function () {
    var aliasModel;
    var editingFormModal;

    var reloadGridView = function () {
        $.pjax({container: '#pjax-surveys-list'});
    };

    var onClickDelete = function (event) {
        var aliasId = $(event.target).closest('tr').data('id');

        var modal = new TgmManager.System.ModalConfirmation({
            title: 'Deleting',
            content: '<p>Delete this alias?</p>',
            headerClass: 'bg-danger'
        });

        modal.onOk = function () {
            TgmManager.System.blockUI();

            var url = TgmManager.Api.aliasDelete({id: aliasId});
            $.ajax({
                url: url,
                dataType: 'json',
                success: function () {
                    reloadGridView();

                    TgmManager.System.unblockUI();

                    new TgmManager.System.ModalInfo({
                        title: 'Success',
                        content: '<p>The alias has been deleted successfully.</p>',
                        showFooter: false
                    }).open();
                }
            }).fail(function (r) {
                var message = (r.responseJSON && r.responseJSON.message) || 'Unknown error occurred.';

                TgmManager.System.unblockUI();

                new TgmManager.System.ModalError({
                    content: '<p>' + message + '</p>'
                }).open();
            });
        };
        modal.open();
    };

    var onClickEdit = function (event) {
        //TgmManager.System.blockUI();

        var aliasId = $(event.target).closest('tr').data('id');
        var url = TgmManager.Api.aliasEdit({id: aliasId});
        window.open(url, '_blank');
        return false;
        /*aliasModel = new TgmManager.Models.Alias({id: aliasId});

        aliasModel.fetch()
            .done(function () {
                parseModelIntoChildren(aliasModel);
                TgmManager.System.unblockUI(function () {
                    editingFormModal = new TgmManager.System.Modal({
                        title: 'Editing Alias',
                        content: new TgmManager.Forms.Alias({
                            model: aliasModel
                        }),
                        okCloses: false,
                        modalClass: 'modal-response'
                    });

                    editingFormModal.on('ok', saveAlias);

                    editingFormModal.open();
                });
            })
            .fail(function (r) {
                var message = (r.responseJSON && r.responseJSON.message) || 'Unknown error occurred.';

                TgmManager.System.unblockUI();

                new TgmManager.System.ModalError({
                    content: '<p>' + message + '</p>'
                }).open();
            });*/
    };

    var parseModelIntoChildren = function (aliasModel) {
        var paramValue = aliasModel.attributes.params;
        var paramsAsArray = paramValue.split('&');
        var attributesArray = ['lang', 'bd', 's', 'utm_source', 'utm_medium'];
        for(i in paramsAsArray) {
            var key = paramsAsArray[i].split('=');
            for(j in attributesArray) {
                searchParamsInArray(paramsAsArray, key, i, attributesArray[j]);
            }

        }

        paramsAsArray = paramsAsArray.filter(Boolean);
        aliasModel.attributes.shortParams = paramsAsArray.join('&');
    };

    var searchParamsInArray = function (paramsAsArray, key, i, attribute) {
        if(key[0] == attribute) {
            aliasModel.attributes[attribute] = key[1];
            delete paramsAsArray[i];
        }
    };

    var saveAlias = function () {
        if (!aliasModel.isValid) {
            return;
        }
        var isModelNew = aliasModel.isNew();

        editingFormModal.close();

        TgmManager.System.blockUI();

        aliasModel.save(null, {
            success: function () {
                reloadGridView();
                var content = isModelNew ? '<p>New alias has been created successfully.</p>' : '<p>Alias has been saved successfully.</p>'

                TgmManager.System.unblockUI(function () {
                    new TgmManager.System.ModalInfo({
                        title: 'Success',
                        content: content,
                        showFooter: false
                    }).open();
                });
            },
            error: function (model, response) {
                var errorMessage = (response.responseJSON && response.responseJSON.message) || 'Internal Server Error';
                var content = new TgmManager.Forms.Alias({
                    model: model
                });

                editingFormModal = new TgmManager.System.Modal({
                    title: isModelNew ? 'New Alias' : 'Editing Alias',
                    content: content,
                    okCloses: false,
                    modalClass: 'modal-response'
                });
                editingFormModal.on('ok', saveAlias);

                TgmManager.System.unblockUI();
                editingFormModal.open();
                content.addError('survey', errorMessage);
            }
        });
    };

    var onClickNewAlias = function (event) {
        aliasModel = new TgmManager.Models.Alias({});

        editingFormModal = new TgmManager.System.Modal({
            title: 'New Alias',
            content: new TgmManager.Forms.Alias({
                model: aliasModel
            }),
            okCloses: false,
            modalClass: 'modal-response'
        });

        editingFormModal.on('ok', saveAlias);

        editingFormModal.open();
    };

    var onClickPin = function (event) {
        var aliasId = $(event.target).closest('tr').data('id');

        TgmManager.System.blockUI();

        var url = TgmManager.Api.aliasPin({id: aliasId});
        $.ajax({
            url: url,
            dataType: 'json',
            success: function () {
                reloadGridView();
                TgmManager.System.unblockUI();
            }
        }).fail(function (r) {
            var message = (r.responseJSON && r.responseJSON.message) || 'Unknown error occurred.';

            TgmManager.System.unblockUI();

            new TgmManager.System.ModalError({
                content: '<p>' + message + '</p>'
            }).open();
        });
    };

    var onClickRun = function (event) {
        var ACTIVE = 1;
        var PAUSED = 2;
        var aliasId = $(event.target).closest('tr').data('id');
        var status = $(event.target).closest('tr').data('status');

        var newStatus = status == ACTIVE ? PAUSED : ACTIVE;

        TgmManager.System.blockUI();

        var url = TgmManager.Api.aliasStatus({id: aliasId, status: newStatus});

        $.ajax({
            url: url,
            dataType: 'json',
            success: function () {
                reloadGridView();
                TgmManager.System.unblockUI();
            }
        }).fail(function (r) {
            var message = (r.responseJSON && r.responseJSON.message) || 'Unknown error occurred.';

            TgmManager.System.unblockUI();

            new TgmManager.System.ModalError({
                content: '<p>' + message + '</p>'
            }).open();
        });

    };

    var onClickTestLink = function (event) {
        window.open($(event.currentTarget).data('href'), '_blank');
    };

    var onClickBid = function () {
        if(!$(this).hasClass('active')){
            $(this).addClass('active')
            .html("<input type='text' placeholder='0.001' value='"+this.innerText+"'>" +
                "<span class='glyphicon glyphicon-floppy-disk save-bid' title='Save'></span>");
        }
    };

    var onSaveBid = function (event) {
        event.stopPropagation();
        var aliasId = $(event.target).closest('tr').data('id');
        var tdElem = $(event.target).closest('td');
        var bidVal = $(event.target).siblings('input').val();

        tdElem.removeClass('active').html(bidVal);

        TgmManager.System.blockUI();

        var url = TgmManager.Api.aliasBid({id: aliasId, bid: bidVal});

        $.ajax({
            url: url,
            dataType: 'json',
            success: function () {
                TgmManager.System.unblockUI();
            }
        });
    };

    var onClickRecovery = function (event) {
        var modal = new TgmManager.System.ModalConfirmation({
            title: 'Recovery Alias',
            content: '<p>Recovery current alias to "Active"?</p>'
        });

        modal.onOk = function () {
            var ACTIVE = 1;
            var aliasId = $(event.target).closest('tr').data('id');
            var url = TgmManager.Api.aliasStatus({id: aliasId, status: ACTIVE});

            TgmManager.System.blockUI();

            $.ajax({
                url: url,
                dataType: 'json',
                success: function () {
                    reloadGridView();
                    TgmManager.System.unblockUI();
                }
            });
        };
        modal.open();
    };

    var onClickDuplicate = function (event) {
        TgmManager.System.blockUI();

        var aliasId = $(event.target).closest('tr').data('id');

        aliasModel = new TgmManager.Models.Alias({id: aliasId});

        aliasModel.fetch()
        .done(function () {
            parseModelIntoChildren(aliasModel);
            aliasModel.attributes.id = '';
            TgmManager.System.unblockUI(function () {
                editingFormModal = new TgmManager.System.Modal({
                    title: 'Duplicate Alias',
                    content: new TgmManager.Forms.Alias({
                        model: aliasModel
                    }),
                    okCloses: false,
                    modalClass: 'modal-response'
                });

                editingFormModal.on('ok', saveAlias);

                editingFormModal.open();
            });
        })
        .fail(function (r) {
            var message = (r.responseJSON && r.responseJSON.message) || 'Unknown error occurred.';

            TgmManager.System.unblockUI();

            new TgmManager.System.ModalError({
                content: '<p>' + message + '</p>'
            }).open();
        });
    };

    var ResetOneParam = function (event,param) {
        var aliasId = $(event.target).closest('tr').data('id');
        var $cell = $(event.target).closest('td');

        var modal = new TgmManager.System.ModalConfirmation({
            title: 'Resetting ' + param.title + ' counter',
            content: '<p>Reset ' + param.title + ' counter for this alias?</p>'
        });

        modal.onOk = function () {
            TgmManager.System.blockUI();

            var url = TgmManager.Api.aliasReset({id: aliasId,param:param.data});
            $.ajax({
                url: url,
                dataType: 'json',
                success: function () {
                    $cell.html('0');
                    reloadGridView();

                    TgmManager.System.unblockUI();

                    new TgmManager.System.ModalInfo({
                        title: 'Success',
                        content: '<p>The '+param.title+'usage counter has been reset successfully.</p>',
                        showFooter: false
                    }).open();
                }
            }).fail(function (r) {
                var message = (r.responseJSON && r.responseJSON.message) || 'Unknown error occurred.';

                TgmManager.System.unblockUI();

                new TgmManager.System.ModalError({
                    content: '<p>' + message + '</p>'
                }).open();
            });
        };
        modal.open();
    };

    var onClickReset = function (event) {
        ResetOneParam(event,{title:'usage',data:'used'});
    };

    var onClickResetScr = function (event) {
        ResetOneParam(event,{title:'Screened out',data:'scr'});
    };

    var onClickResetDsq = function (event) {
        ResetOneParam(event,{title:'Disqualified',data:'dsq'});
    };

    var onClickResetQfl = function (event) {
        ResetOneParam(event,{title:'Quota Full',data:'qfl'});
    };

    var onClickResetBlock = function (event) {
        ResetOneParam(event,{title:'User blocked',data:'block'});
    };

    var initGridviewEvents = function(e, xhr, pjax) {
        $.pjax.defaults.timeout = 60000;
        $.pjax.defaults.url = pjax && pjax.url || $.pjax.defaults.url;

        $('.item-delete').click(onClickDelete);
        $('.item-edit').click(onClickEdit);
        $('.item-reset').click(onClickReset);
        $('.item-pin').click(onClickPin);
        $('.item-status').click(onClickRun);
        $('.item-recovery').click(onClickRecovery);
        $('.item-duplicate').click(onClickDuplicate);
        $('.item-reset-scr').click(onClickResetScr);
        $('.item-reset-dsq').click(onClickResetDsq);
        $('.item-reset-qfl').click(onClickResetQfl);
        $('.item-reset-block').click(onClickResetBlock);
        $(document).on('click','.item-bid',onClickBid);
        $(document).on('click','.item-bid .save-bid',onSaveBid);

        if (!e) {
            $('#item-add').click(onClickNewAlias);
        }

        $('.alias-url').click(onClickTestLink);
    };

    var onFilterChanged = function (event) {
        var url = TgmManager.Api.aliases() + '?';

        _.each($('#filter__statuses').val(), function (id) {
            url += '&st[]=' + id;
        });

        var country = $('#filter__countries').val();
        if (country) {
            url += '&cn[]=' + $('#filter__countries').val();
        }

        url += '&par=' + encodeURIComponent($('#filter__parameters').val());

        url += '&srh=' + encodeURIComponent($('#filter__search').val());

        url += '&survid=' + encodeURIComponent($('#filter__search_by_id').val());

        $.pjax.defaults.url = url;
        reloadGridView();
    };

    $('#filter__statuses').on('change.select2', onFilterChanged);
    $('#filter__countries').on('change', onFilterChanged);
    $('#filter__parameters').on('keyup', _.debounce(onFilterChanged, 800));
    $('#filter__search').on('keyup', _.debounce(onFilterChanged, 800));
    $('#filter__search_by_id').on('keyup', _.debounce(onFilterChanged, 800));

    initGridviewEvents();
    $('#pjax-surveys-list').on('pjax:end', initGridviewEvents);
});