
TgmManager.Forms.CostManualAdjustment = TgmManager.System.BaseView.extend({

    template: _.template('\
        <b><%- project_id %></b>, <%- country %>\
        <input\
            type="text" class="form-control" style="text-align:right" id="efInput"\
            value="<%- value %>" autocomplete="off">\
    '),

    render: function () {
        this.model.value = sprintf('%.2f', this.model.value || 0);

        this.$el.html(this.template(this.model));

        return this;
    },

    getValue: function () {
        return Number(this.$('#efInput').val()) || 0;
    }
});