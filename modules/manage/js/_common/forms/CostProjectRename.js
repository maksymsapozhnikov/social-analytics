
TgmManager.Forms.CostProjectRename = TgmManager.System.BaseView.extend({

    template: _.template('\
        <h4>Existing Project</h4>\
        <div class="row">\
            <div class="col-xs-3">Project ID:</div>\
            <div class="col-xs-9"><input type="text" class="form-control" autocomplete="off" value="<%- project_id %>"\
                disabled readonly></div>\
        </div>\
        <div class="row">\
            <div class="col-xs-3">Country:</div>\
            <div class="col-xs-9"><input type="text" class="form-control" autocomplete="off" value="<%- country %>"\
                disabled readonly></div>\
        </div>\
        <h4>New Project</h4>\
        <div class="row">\
            <div class="col-xs-3">Project ID:</div>\
            <div class="col-xs-9"><input type="text" class="form-control input-project-id" autocomplete="off" value="<%- project_id %>"></div>\
        </div>\
        <div class="row">\
            <div class="col-xs-3">Country</div>\
            <div class="col-xs-9"><input type="text" class="form-control input-country" autocomplete="off" value="<%- country %>"></div>\
        </div>\
    '),

    render: function () {
        this.$el.html(this.template(this.model));

        return this;
    },

    $elProject: function () {
        return this.$('.input-project-id');
    },

    $elCountry: function () {
        return this.$('.input-country');
    },

    getProject: function () {
        return this.$elProject().val();
    },

    getCountry: function () {
        return this.$elCountry().val();
    }
});