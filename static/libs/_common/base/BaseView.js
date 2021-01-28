
TgmManager.System.BaseView = Backbone.View.extend({

    className: '',

    templateError: _.template('<span class="text-danger error-message"><%= message%></span>'),

    addError: function (attribute, message) {
        var $el = this._getElement(attribute);

        if (!$el.hasClass('has-error')) {
            $el.addClass('has-error');
        } else {
            $el.children('.error-message').remove();
        }

        $el.append(this.templateError({
            message: message
        }));
    },

    removeError: function (attribute) {
        var $el = this._getElement(attribute);

        $el.removeClass('has-error');
        $el.children('.error-message').remove();
    }

});