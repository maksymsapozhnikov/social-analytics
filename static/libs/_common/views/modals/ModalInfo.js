
TgmManager.System.ModalInfo = TgmManager.System.Modal.extend({

    defaultOptions: {
        animate: true,
        autoclose: 1000,
        cancelText: false
    },

    headerClass: 'bg-primary',

    render: function () {
        if (this.options.autoclose > 0) {
            setTimeout(function () {
                this.close();
            }.bind(this), this.options.autoclose);
        }

        return TgmManager.System.Modal.prototype.render.apply(this, arguments);
    }

});
