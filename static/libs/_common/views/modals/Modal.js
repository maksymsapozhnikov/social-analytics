
TgmManager.System.Modal = Backbone.BootstrapModal.extend({

    defaultOptions: {
        animate: true
    },

    headerClass: 'bg-primary',

    initialize: function(options) {
        options = _.defaults(options || {}, this.defaultOptions);

        Backbone.BootstrapModal.prototype.initialize.apply(this, arguments);
    },

    render: function () {
        Backbone.BootstrapModal.prototype.render.apply(this, arguments);

        this.$('.modal-header').addClass(this.headerClass);

        if (this.options.modalClass) {
            this.$('.modal-dialog').addClass(this.options.modalClass);
        }

        this.on('ok', this.onOk);
        this.on('cancel', this.onCancel);

        this.renderAdditionalButtons();

        return this;
    },

    renderAdditionalButtons: function () {
        _.each(this.options.buttons, function (button, i) {
            var elButton = $('<a href="#"></a>');
            var selector = 'btn-custom-' + button.machineName || i;

            elButton
                .text(button.name)
                .addClass(['btn', selector, button.class].join(' '));

            elButton.on('click', function (e) {
                e.preventDefault();
                this.trigger(selector);
            }.bind(this));

            if (!_.isUndefined(button.position) && button.position === 'left') {
                this.$('.modal-footer').prepend(elButton);
            } else {
                this.$('.modal-footer .btn-primary').before(elButton);
            }
        }.bind(this));
    },

    hide: function () {
        this.$el.hide();
    },

    show: function () {
        this.$el.show();
    },

    onOk: _.nop,

    onCancel: _.nop

});
