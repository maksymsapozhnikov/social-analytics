
var TgmManager = {
    System: {
        blockUI: function (callback) {
            var message = '\
                <h2 style="white-space:nowrap;color:#FFFFFF;margin-bottom:100px;">Please wait</h2>\
                <div class="sk-folding-cube">\
                    <div class="sk-cube1 sk-cube"></div>\
                    <div class="sk-cube2 sk-cube"></div>\
                    <div class="sk-cube4 sk-cube"></div>\
                    <div class="sk-cube3 sk-cube"></div>\
                </div>\
            ';

            return $.blockUI({
                message: message,
                css: {
                    border: '0px solid #000000',
                    backgroundColor: 'transparent',
                    'margin-top': '-100px',
                    'z-index': 100001
                },
                overlayCSS: {
                    backgroundColor: '#000000',
                    opacity: 0.6,
                    cursor: 'wait',
                    'z-index': 100000
                },
                onBlock: callback || _.noop
            });
        },

        unblockUI: function (callback) {
            return $.unblockUI({onUnblock: callback || _.noop});
        }
    },
    Forms: {},
    Models: {},
    Collections: {}
};

TgmManager.Api = {
    aliases: '/manage/aliases',
    aliasRestApi: '/manage/aliases/rest',
    aliasDelete: '/manage/aliases/delete?id=<%- id %>',
    aliasEdit: '/manage/aliases/edit?id=<%- id %>',
    aliasPin: '/manage/aliases/pin?id=<%- id %>',
    aliasReset: '/manage/aliases/reset?id=<%- id %>&param=<%- param %>',
    aliasStatus: '/manage/aliases/status?id=<%- id %>&status=<%- status %>',
    aliasBid: '/manage/aliases/set-bid?id=<%- id %>&bid=<%- bid %>',

    campaign: '/manage/campaign',

    surveySearch: '/manage/surveys/search',
    surveySearchByParams: '/manage/surveys/search-by-params',
    surveyRestApi: '/manage/surveys/rest'
};

_.each(TgmManager.Api, function (value, key) {
    TgmManager.Api[key] = _.template(value);
});