$(function () {

    var initGridviewEvents = function(e, xhr, pjax) {
        $.pjax.defaults.timeout = 10000;
        $.pjax.defaults.url = pjax && pjax.url || $.pjax.defaults.url;
    };

    initGridviewEvents();
    $('#pjax-surveys-list').on('pjax:end', initGridviewEvents);

});

var reloadGridView = function () {
    $.pjax.defaults.scrollTo = false;
    $.pjax.defaults.timeout = 10000;

    $.pjax({container: '#pjax-surveys-list'});
};