
$(function () {
    var modal;
    var campaignModel;

    var saveCampaign = function () {
        if (!campaignModel.isValid) {
            return;
        }

        modal.close();

        TgmManager.System.blockUI();

        campaignModel.save(null, {
            success: function () {
                /** @todo send event */
                var $select2 = $('#survey-campaign_id');
                var newId = campaignModel.get('id');
                if ($select2.find("option[value='" + newId + "']").length) {
                    $select2.val(newId).trigger('change');
                } else {
                    var newOption = new Option(campaignModel.get('name'), newId, true, true);
                    $select2.append(newOption).trigger('change');
                }

                TgmManager.System.unblockUI(function () {
                    new TgmManager.System.ModalInfo({
                        title: 'Success',
                        content: '<p>New campaign has been created successfully.</p>',
                        showFooter: false
                    }).open();
                });
            },
            error: function (model, response) {
                var errorMessage = response.responseJSON.message || 'Internal Server Error';
                var content = new TgmManager.Forms.Campaign({
                    model: model
                });

                modal = new TgmManager.System.Modal({
                    title: 'New Campaign',
                    content: content,
                    okCloses: false
                });
                modal.on('ok', saveCampaign);

                TgmManager.System.unblockUI();
                modal.open();
                content.addError('name', errorMessage);
            }
        });
    };

    var onClickNewCampaign = function () {
        campaignModel = new TgmManager.Models.Campaign({
            name: ''
        });

        modal = new TgmManager.System.Modal({
            title: 'New Campaign',
            content: new TgmManager.Forms.Campaign({
                model: campaignModel
            }),
            okCloses: false
        });

        modal.on('ok', saveCampaign);

        modal.open();

    };

    $('.btn-new-campaign').click(onClickNewCampaign);

    if (!!window.opener) {
        $('#navbar-top').hide();
    }
});
