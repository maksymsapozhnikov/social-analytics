
TgmManager.System.ModalError = TgmManager.System.Modal.extend({

    defaultOptions: {
        animate: true,
        autoclose: false,
        okText: 'OK',
        cancelText: false,
        title: 'Error occurred',
        content: 'Error occurred.'
    },

    headerClass: 'bg-danger text-danger'

});