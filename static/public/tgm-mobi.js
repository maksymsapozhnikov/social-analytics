/**
 * @var string surveyRmsid global variable points to the survey
 */

tgmData && tgmData.SENTRY && Raven.config(tgmData.SENTRY).install();

function findGetParameter(parameterName) {
    var result = null,
        tmp = [];

    location.search
        .substr(1)
        .split("&")
        .forEach(function (item) {
            tmp = item.split("=");
            if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
        });

    return result;
}

$(function() {
    var options = {};

    var optin = window.rmsApp ? (window.rmsApp.optin || {}) : {};
    window.rmsApp = {
        appKey: '_rms',
        ls: $.localStorage,
        co: $.cookieStorage,
        optin: optin,
        preloadData: function() {
            window.rmsApp.co.setExpires(365).setPath('/');

            try {
                window.rmsApp.optin = JSON.parse(
                    base64.decode(window.rmsApp.ls.get(window.rmsApp.appKey))
                    || base64.decode(window.rmsApp.co.get(window.rmsApp.appKey))
                    || '{}'
                );
            } catch (e) {
                window.rmsApp.optin = {};
            }

            /** @todo remove before production */
            if (!!findGetParameter('test')) {
                delete window.rmsApp.optin.rmsid;
                window.rmsApp.optin.test = true;
            } else {
                if (window.rmsApp.optin.test) {
                    delete window.rmsApp.optin.test;
                }
            }
        },

        saveData: function(optin) {

            if (undefined !== optin) {
                if (!!optin.test) {
                    window.rmsApp.optin.test = true;
                }

                if (optin.rmsid) {
                    window.rmsApp.optin.rmsid = optin.rmsid;
                }
            }

            var encoded = base64.encode(JSON.stringify(window.rmsApp.optin));

            try {
                window.rmsApp.ls.set(window.rmsApp.appKey, encoded);
            } catch (e) {

            }

            window.rmsApp.co.set(window.rmsApp.appKey, encoded);
        },

        done: function(fp) {
            if (window._rmsTimeout) {
                clearTimeout(window._rmsTimeout);
            }

            window.rmsApp.optin.fp = fp;

            window.rmsApp.optin.uri = document.location.href;
            window.rmsApp.saveData();

            var script = document.createElement('script');
            var _t = Math.round(new Date() / 1000);

            script.type = 'text/javascript';
            script.src = '/survey/mobi-app2.js?t=' + _t;

            script.onload = function() {
                setTimeout(function() {
                    $.cookieStorage.remove('DAPROPS');
                    document.location = '/go/' + surveyRmsid + '?t=' + _t;
                }, 50);
            };

            document.body.appendChild(script);
        },

        timeout: function() {
            var wdef = window.rmsApp.def;
            for(var def in wdef) {
                if (wdef.hasOwnProperty(def) && wdef[def].state() !== 'resolved') {
                    wdef[def].resolve('-1');
                }
            }
        }
    };

    window.rmsApp.preloadData();
    window.rmsApp.def = {
        fp: new $.Deferred()
    };

    var defs = window.rmsApp.def;

    $.when(defs.fp).done(window.rmsApp.done);

    new Fingerprint2(options).get(function(fingerprint_id, components) {
        window.rmsApp.def.fp.resolve(fingerprint_id);
    });

    window._rmsTimeout = setTimeout(window.rmsApp.timeout, 2000);
});
