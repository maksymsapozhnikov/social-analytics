
TgmManager.Forms.Alias = TgmManager.System.BaseView.extend({

    survey: {
        id: null,
        name: null
    },

    events: {
        'change .alias-input-field': 'updateModel',
        'focusout .alias-input-field': 'updateModel'
    },

    template: _.template('\
        <div class="row">\
            <input type="hidden" value="<%- model.get("id") %>" class="alias-id">\
            <div class="col-xs-12">\
                <div class="form-group required alias-survey__div">\
                    <label class="control-label">Survey</label>\
                    <select class="control-label alias-survey alias-input-field">\
                        <% if (survey) { %>\
                        <option value="<%- survey.id %>" selected="selected"><%- survey.name %> (<%- survey.rmsid %>)</option>\
                        <% } %>\
                    </select>\
                 </div>\
             </div>\
            <div class="col-xs-12">\
                <div class="form-group required alias-query__div">\
                    <label class="control-label">Query Params</label>\
                    <input type="text" class="form-control alias-query alias-input-field"\
                           maxlength="255" aria-required="true" value="<%- model.get("shortParams") %>">\
                    <p class="help-block">For example: <b>kn=Vietman&amp;s=tapjoy</b></p>\
                </div>\
            </div>\
        </div>\
        <div class="row">\
            <div class="col-md-6 col-sm-6 col-xs-12">\
                <div class="form-group required alias-lang__div">\
                    <label class="control-label">Language</label>\
                    <input type="text" class="form-control alias-lang alias-input-field"\
                        maxlength="255" aria-required="true" value="<%- model.get("lang") %>">\
                </div>\
            </div>\
            <div class="col-md-6 col-sm-6 col-xs-12">\
                <div class="form-group required alias-bd__div">\
                    <label class="control-label">Bid</label>\
                    <input type="number" class="form-control alias-bid alias-input-field"\
                        min="0.001" step="0.001" aria-required="true" value="<%- model.get("bd") %>">\
                </div>\
            </div>\
            <div class="col-md-6 col-sm-6 col-xs-12">\
                <div class="form-group required alias-source__div">\
                    <label class="control-label">Source</label>\
                    <input type="text" class="form-control alias-source alias-input-field"\
                        maxlength="255" aria-required="true" value="<%- model.get("s") %>">\
                </div>\
            </div>\
            <div class="col-md-6 col-sm-6 col-xs-12">\
                <div class="form-group required alias-utm_medium__div">\
                    <label class="control-label">Utm medium</label>\
                    <input type="text" class="form-control alias-utm_medium alias-input-field"\
                        maxlength="255" aria-required="true" value="<%- model.get("utm_medium") %>">\
                </div>\
            </div>\
            <div class="col-md-12 col-sm-12 col-xs-12">\
                <p class="help-block">Result query: <span id="result-query"><%- model.get("params") %></span></p>\
                <div class="form-group alias-note__div">\
                    <label class="control-label">Note</label>\
                    <input type="text" class="form-control alias-note alias-input-field"\
                           maxlength="255" aria-required="true" value="<%- model.get("note") %>">\
                </div>\
           </div>\
        </div>\
    '),

    render: function () {
        this.$el.html(this.template({
            model: this.model,
            survey: this.model.get('survey')
        }));
        var filterParams = 'active';
        var sortParams = 'date_create_DESC';
        var url = TgmManager.Api.surveySearchByParams();
        this.$('.alias-survey').select2({
            ajax: {
                url: url,
                dataType: 'json',
                data: function (params) {
                    var query = {
                        term: params.term,
                        status: filterParams,
                        sortParams: sortParams
                    };

                    return query;
                }
            },
            width: '100%',
            placeholder: 'Select Survey',
            selectOnClose: false
        });

        return this;
    },

    updateModel: function () {
        var resultQuery = '';
        var aliasQuery = this.$('.alias-query').val();
        aliasQuery = aliasQuery != '' ? '&' + aliasQuery: '';

        resultQuery += this.makeQueryParam('lang', 'lang');
        resultQuery += this.makeQueryParam('bid', 'bd');
        resultQuery += this.makeQueryParam('source', 's');
        resultQuery += this.makeQueryParam('source', 'utm_source', true);
        resultQuery += this.makeQueryParam('utm_medium', 'utm_medium');
        resultQuery = (resultQuery + aliasQuery).substr(1);
        this.$('#result-query').html(resultQuery);

        this.model.set({
            survey_id: this.$('.alias-survey').val(),
            short_params: aliasQuery,
            params: resultQuery,
            bd: this.$('.alias-bid').val(),
            lang: this.$('.alias-lang').val(),
            s: this.$('.alias-source').val(),
            utm_source: this.ucFirst(this.$('.alias-source').val()),
            utm_medium: this.$('.alias-utm_medium').val(),
            note: this.$('.alias-note').val()
        });

        this.checkErrors();
    },

    checkErrors: function () {
        this.model.isValid = true;
        this.removeError('survey');
        this.removeError('bd');
        this.removeError('lang');
        this.removeError('source');

        if(!this.model.get('survey_id')) {
            this.model.isValid = false;
            this.addError('survey', 'Survey cannot be blank');
        }

        if(!this.model.get('bd')) {
            this.model.isValid = false;
            this.addError('bd', 'Bid cannot be blank');
        }

        if(!this.model.get('lang')) {
            this.model.isValid = false;
            this.addError('lang', 'Language cannot be blank');
        }

        if(!this.model.get('s')) {
            this.model.isValid = false;
            this.addError('source', 'Source cannot be blank');
        }
    },

    _getElement: function (attribute) {
        return this.$('.alias-' + attribute + '__div');
    },

    makeQueryParam: function (attributeClass, attributeKey, uppercase) {
        uppercase = typeof uppercase !== 'undefined' ? true: false;
        var currentVal = this.$('.alias-' + attributeClass).val();
        if(currentVal != '') {
            currentVal = uppercase ? this.ucFirst(currentVal): currentVal;
            return '&' + attributeKey + '=' + currentVal;
        }
        return '';
    },

    ucFirst: function (str) {
        if(!str) {
            return str;
        }

        return str[0].toUpperCase() + str.slice(1);
    }

});
//$(function () {
    $('#alias-form .form-control').focusout(function(){
        makeQueryParam();
    });

    var makeQueryParam = function () {
        var shortParams = $('#shortparams').val();
        var lang = $('#lang').val();
        var bid = $('#bid').val();
        var source = $('#source').val();
        var utmMedium = $('#utmmedium').val();
        var resultQuery = 'lang=' + lang + '&bd=' + bid + '&s=' + source + '&utm_source=' + source[0].toUpperCase() + source.slice(1) + '&utm_medium=' + utmMedium;
        resultQuery += shortParams != '' ? '&' + shortParams: '';
        $('#result-query').html(resultQuery);
        $('#params').val(resultQuery);
    }

//});