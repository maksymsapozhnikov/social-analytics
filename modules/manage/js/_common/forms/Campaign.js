
TgmManager.Forms.Campaign = TgmManager.System.BaseView.extend({

    events: {
        'change .campaign-name': 'updateModel',
        'focusout .campaign-name': 'updateModel'
    },

    template: _.template('\
        <div class="row">\
            <input type="hidden" value="<%- model.get("id") %>" class="campaign-id">\
            <div class="col-xs-12">\
                <div class="form-group required campaign-name__div">\
                    <label class="control-label">Campaign Name</label>\
                    <input type="text" class="form-control campaign-name"\
                           maxlength="255" aria-required="true" value="<%- model.get("name") %>">\
                </div>\
            </div>\
        </div>\
    '),

    render: function () {
        this.$el.html(this.template({
            model: this.model
        }));

        return this;
    },

    updateModel: function () {
        this.model.set({
            name: this.$('.campaign-name').val()
        });

        this.checkErrors();
    },
    
    checkErrors: function () {
        if (!this.model.get('name')) {
            this.model.isValid = false;
            this.addError('name', 'Campaign Name required');
        } else {
            this.model.isValid = true;
            this.removeError('name');
        }
    },

    _getElement: function (attribute) {
        return this.$('.campaign-' + attribute + '__div');
    }

});
