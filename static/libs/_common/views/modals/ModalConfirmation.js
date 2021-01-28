
TgmManager.System.ModalConfirmation = TgmManager.System.Modal.extend({

    defaultOptions: {
        animate: true,
        autoclose: false,
        okText: 'OK',
        okCloses: true,
        cancelText: 'Cancel',
        title: 'Confirmation required',
        content: 'Confirmation required.'
    },

    headerClass: 'bg-primary'

});