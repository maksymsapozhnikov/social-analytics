/**
 * tgm.mobi external functions
 * survey-gizmo simplifying library
 */
'use strict';

function TgmMobi(SGAPI, window) {
    var boxnames = {
        phoneDetails: 'phone-details',
        respondent: 'respondent',
        postCodes: 'postcode',
        response: 'response'
    };

    var nextButton = '#sg_NextButton';
    var submitButton = '#sg_SubmitButton';

    var submitButtonId = $(nextButton).length ? nextButton : submitButton;

    var errorMessageId = '#tgm-error-message';

    var APIHOST = tgmData.APIHOST;
    var apiUrls = {
        checkRespondent: APIHOST + '/check-respondent',
        checkPhone: APIHOST + '/check-phone',
        checkEmail: APIHOST + '/check-email',
        checkLocation: APIHOST + '/check-coordinates',
        checkPostCode: APIHOST + '/post-code',
        getResponse: APIHOST + '/get-response',
        checkBadWords: APIHOST + '/check-text',
        loadOptions: APIHOST + '/options',
        saveDirtyScore: APIHOST + '/ds',
        timeScore: APIHOST + '/ts'
    };

    var _rmsidSurvey = null;
    var _rmsidRespondent = null;

    var _timingStarted = null;
    var _timingFinished = null;

    var _timingTimer = null;

    var deferredSubmit = {
        fnOnSubmitHandlers: [],
        deferred: [],

        /**
         * Adds handler on submit event.
         * @param cbFunction
         * @param recallable
         */
        addOnSubmitHandler: function (cbFunction, recallable) {
            if (_isFunction(cbFunction)) {
                this.fnOnSubmitHandlers.push({
                    recallable: recallable || false,
                    isCalled: false,
                    fnCallback: cbFunction
                });
            }
        },

        /**
         * Adds deferred to be resolved until form goes to the next screen.
         * @param def $.Deferred
         */
        addDeferred: function(def) {
            this.deferred.push(def);
        },

        /**
         * Starts added onSubmit handlers.
         * When deferred are done, goes to the next screen.
         */
        submit: function () {
            $.each(this.fnOnSubmitHandlers, function (i, def) {
                var cantCall = def.isCalled && !def.recallable;

                if (!cantCall) {
                    this.addDeferred(def.fnCallback());
                    this.fnOnSubmitHandlers[i].isCalled = true;
                }
            }.bind(this));

            $.when.apply($, this.deferred).done(this._proceedNextButton.bind(this));
            $.when.apply($, this.deferred).fail(this._stopNextButton.bind(this));

            return false;
        },

        _stopNextButton: function () {
            var temp = [];
            $.each(this.deferred, function (i, def) {
                if ('pending' === def.state()) {
                    temp.push(def);
                }
            });
            this.deferred = temp;

            var $nextButton = $('.sg-button.sg-next-button');
            var $submitButton = $('.sg-button.sg-submit-button');

            if ($nextButton.is(':visible')) {
                $nextButton.prop('disabled', false);
            } else if ($(nextButton).is(':visible')) {
                $(nextButton).prop('disabled', false);
            } else {
                $submitButton.prop('disabled', false);
            }
        },

        /**
         * Unconditionally goes to the next screen
         * @private
         * @todo rework required
         */
        _proceedNextButton: function() {
            var $nextButton = $('.sg-button.sg-next-button');
            var $submitButton = $('.sg-button.sg-submit-button');

            if ($('.sg-mobile-next').is(':visible')) {
                //$nextButton.prop('disabled', false);
                var survey = SGAPI.survey.sgSurvey;
                if (!survey.checkValidation()) {
                    this._stopNextButton();
                } else {
                    survey.goMobilePage();
                }
            } else if ($(nextButton).is(':visible')) { /* submit */
                $(nextButton).prop('disabled', false);
                $(nextButton).off('click').trigger('click');
            } else if ($submitButton.is(':visible')) {
                $submitButton.off('click');
                $submitButton.prop('disabled', false);
                $submitButton.trigger('click');
            } else {
                var e1 = setTimeout(function () {
                    $('form').submit();
                }, 500);

                window.onbeforeunload = function () {
                    clearTimeout(e1);
                };

                $(submitButtonId).off('click');
                $(submitButtonId).prop('onclick', null);
                $(submitButtonId).show().trigger('click').hide();
            }
        },

        init: function () {
            $(document.body).off('swipe swipeleft swiperight touchend touchmove touchstart');

            var self = this;
            var fnSubmitEventListener = function (e) {
                $(e.currentTarget).prop('disabled', true);
                return self.submit(e);
            };

            $(function () {
                $('.sg-button.sg-next-button').prop('onclick', null);
                $('.sg-button.sg-submit-button').prop('onclick', null);
                $('.sg-button.sg-next-button').off('click').click(fnSubmitEventListener);
                $('.sg-button.sg-submit-button').off('click').click(fnSubmitEventListener);

                if (_isMobile()) {
                    try {
                        var mobileCallback = function() {
                            self.submit.apply(self);
                        };

                        $('input[type=radio]').off('click').click(mobileCallback);
                        $('select').off('change').change(mobileCallback);
                    } catch (e) {
                    }
                }
            });

        }

    };

    var _checkPhone = function(surveyRmsid, phone, respondentRmsid) {
        return $.post(apiUrls.checkPhone, {
            phone: phone,
            survey_rmsid: surveyRmsid,
            rmsid: respondentRmsid
        });
    };

    var _isFunction = function (obj) {
        return typeof obj === 'function' || false;
    };

    var _checkEmail = function(email) {
        return $.post(apiUrls.checkEmail, {
            email: email
        });
    };

    var _getPhoneNumber = function(inputCodeId, inputPhoneId) {
        var selected = $(inputCodeId + ' option:selected').text();
        var code = selected.match(/\s*\+(\d+)$/) || ['', $(inputCodeId).val() || ''];
        var phoneCode = code[1];
        var phoneNumber = '' + Number($(inputPhoneId).val());

        return phoneNumber.startsWith(phoneCode) ? phoneNumber : phoneCode + phoneNumber;
    };

    var _showErrorMessage = function (message) {
        $('.sg-content').prepend('<div class="sg-question-errorlist" id="tgm-error-message">' + message + '</div>');
        $(submitButtonId).prop('disabled', false);
    };

    this.iframeBackground = '#FFFFFF';

    /**
     *
     * @param {jquery} $elementToValidate
     * @param {object} callback returns $.Deferred object
     */
    var validateElement = function ($elementToValidate, callback) {
        var fnEventListener = function () {
            var def = $.Deferred();

            var $currentPage = $('.sg-page-shown.sg-page-current');
            var paginationEnabled = !!$currentPage.length;
            var elementOnPage = !!$currentPage.find($elementToValidate).length;
            var isElementShown = elementOnPage || (!paginationEnabled && $elementToValidate.length);

            $(errorMessageId).remove();

            if (isElementShown && _isFunction(callback)) {
                callback()
                    .done(function() {
                        def.resolve();
                    })
                    .fail(function() {
                        def.reject();
                    });
            } else {
                def.resolve();
            }

            return def;
        };

        deferredSubmit.addOnSubmitHandler(fnEventListener, true);

    };

    this.getElementId = function(questionId, element, preffix) {
        var surveyId = SGAPI.survey.surveyObject.id;
        var pageId = SGAPI.survey.pageId;
        var idPreffix = '#';
        var sgPreffix = 'sgE-';

        preffix = undefined === preffix ? idPreffix : preffix;
        var suffix = undefined === element ? '-element' : ((element ? '-' : '') + element);

        return preffix + sgPreffix + surveyId + '-' + pageId + '-' + questionId + suffix;
    };

    this.setCountryDefaults = function(countryQuestion, countryName) {
        var surveyId = SGAPI.survey.surveyObject.id;
        var options = SGAPI.surveyData[surveyId].questions[countryQuestion].options;
        var valueSelected = null;

        for(var optionId in options) {
            if (options.hasOwnProperty(optionId)) {
                if (!!options[optionId].title.English.match(countryName)) {
                    valueSelected = optionId;
                }
            }
        }

        $(this.getElementId(countryQuestion)).val(valueSelected);
    };

    this.onSubmitCheckEmail = function(emailQuestion) {
        var $emailNumberId = $(this.getElementId(emailQuestion));

        validateElement($emailNumberId, function() {
            var def = $.Deferred();
            var email = $emailNumberId.val();

            if (email === '') {
                def.resolve();

                return def;
            }

            _checkEmail(email)
            .done(function(result) {
                if (result.valid) {
                    def.resolve();
                    return def;
                }

                def.reject();
                _showErrorMessage(result.message ? result.message : 'Unknown error');
            }).fail(function(response) {
                def.reject();
                _showErrorMessage('An error occured. Code: ' + response.status + '. Message: ' + response.statusText);
            });

            return def;
        });
    };

    this.onSubmitCheckPhone = function(surveyRmsid, areaCodeQuestion, phoneQuestion, mergedPhoneQuestion, respondentRmsid) {
        var areaCodeId = this.getElementId(areaCodeQuestion);
        var phoneNumberId = this.getElementId(phoneQuestion);
        var savePhoneId = this.getElementId(mergedPhoneQuestion);
        var self = this;

        validateElement($(phoneNumberId), function() {
            var def = $.Deferred();

            if ($(phoneNumberId).val() === '') {
                def.resolve();
                return def;
            }

            var phone = _getPhoneNumber(areaCodeId, phoneNumberId);

            if (savePhoneId) {
                $(savePhoneId).val(phone);
            }

            respondentRmsid = (respondentRmsid === void 0) ? null : respondentRmsid;

            _checkPhone(surveyRmsid, phone, respondentRmsid)
                .done(function(result) {
                    if (result.valid) {
                        def.resolve();
                        self.fetchResult(boxnames.phoneDetails, result);

                        return true;
                    }

                    def.reject();
                    _showErrorMessage(result.message ? result.message : 'Unknown error');
                })
                .fail(function(response) {
                    def.reject();
                    _showErrorMessage('An error occured. Code: ' + response.status + '. Message: ' + response.statusText);
                });

            return def;
        });

        return false;
    };

    this.fetchResult = function (blockPreffix, fetchedObject, exceptList) {
        exceptList = exceptList || [];

        var self = this;
        var surveyId = SGAPI.survey.surveyObject.id;
        var questions = SGAPI.surveyData[surveyId].questions;

        var fnGetAttributeName = function (value) {
            if (!value || !value.indexOf) {
                return false;
            }

            if (0 !== value.indexOf(blockPreffix + '.')) {
                return false;
            }

            var attributeName = value.replace(blockPreffix + '.', '');

            return -1 === exceptList.indexOf(attributeName) ? attributeName : false;
        };

        var fnSetAttributeValue = function (elementId, elementName) {
            if ($(elementId).length) {
                var attributeName = fnGetAttributeName(elementName);
                if (attributeName) {
                    $(elementId).val(fetchedObject[attributeName]);
                }
            }
        };

        _.each(questions, function (question) {
            if (question.type === 'MULTI_TEXTBOX') {
                /** iterate over options */
                _.each(question.options, function (option) {
                    var elementId = self.getElementId(question.id + '-' + option.id, '');
                    fnSetAttributeValue(elementId, option.value);
                });
            } else {
                var elementName = question.value
                        || question.title
                        /* for the case when SGAPI is not filled properly, tries to extract a label: */
                        || $(self.getElementId(question.id)).closest('.sg-question').find('label').text().trim();

                fnSetAttributeValue(self.getElementId(question.id), elementName);
            }
        });

        /* combolists */
        /* @todo check does it work */
        $('label').each(function(key, element) {
            var $input = $('#' + $(element).prop('for'));
            var titleName = $(element).text().trim();
            var propTag = $input.prop('tagName');
            var tagName = (propTag && propTag.toLowerCase) ? $input.prop('tagName').toLowerCase() : '';
            var attributeName = titleName.replace(blockPreffix + '.', '');

            if ('select' === tagName && titleName.indexOf(blockPreffix + '.') === 0) {
                var value = fetchedObject[attributeName];
                var valueEscaped = value && value.replace && value.replace('"', '&quot;');
                $input.find('option[label="' + valueEscaped + '"]').prop('selected', true);
            }
        });
    };

    /**
     * Sets iframe styles.
     */
    this.loadIframeStyle = function() {
        $(function() {
            if (!!tgmMobi.iframeBackground) {
                $('body').css('background-color', tgmMobi.iframeBackground);
            }

            $('.sg-question-set').css('height', 'auto');

            try {
                var $nextButton = $('.sg-next-button');
                if (!$nextButton.length) {
                    $nextButton = $('.sg-submit-button');
                    $nextButton.parent().css('margin', 0);
                }
                var nextMessage = SGAPI.surveyData[SGAPI.survey.surveyObject.id].messages.next_button;
                var nextMessagePrevious = $nextButton.val();

                $('.sg-back-button').hide();

                $nextButton.val(nextMessage);
                if (nextMessage !== nextMessagePrevious) {
                    $nextButton.css('width', '100%');
                    $nextButton.css('margin', '0');
                    $nextButton.css('height', 'auto');
                    $('.sg-int-virtual-page .sg-question-set>.sg-question.sg-page-current').css('padding-bottom', '0');
                }
            } catch (e) {
            }
        });
    };

    this.loadRespondentData = function(rmsid) {
        var checkUrl = apiUrls.checkRespondent + '/' + rmsid;
        var self = this;

        var def = $.Deferred();

        deferredSubmit.addDeferred(def);

        $.get(checkUrl, function(){}, 'json')
            .done(function(resp) {
                self.fetchResult(boxnames.respondent, resp, ['rmsid']);
                def.resolve();
            })
            .fail(function () {
                def.resolve();
            });
    };

    this.loadResponseData = function (autoProceed, callbackOnLoaded) {
        var $rmsid = $('input[title="response.rmsid"]');
        console.assert($rmsid.length > 0, 'Element input[title="response.rmsid"] doesn\'t exist.');
        if ($rmsid.length) {
            var checkUrl = apiUrls.getResponse + '/' + $rmsid.val();
            var self = this;

            var def = $.Deferred();

            deferredSubmit.addDeferred(def);

            $.get(checkUrl, function () {}, 'json')
                .done(function (resp) {
                    self.fetchResult(boxnames.response, resp, ['rmsid']);
                    if (_isFunction(callbackOnLoaded)) {callbackOnLoaded();}
                    if (autoProceed || false) {
                        deferredSubmit.submit();
                    }
                    def.resolve();
                })
                .fail(function () {
                    console.warn('Request failed');
                    def.resolve();
                });

        } else {
            deferredSubmit.submit();
        }
    };

    this.nextScreen = function () {
        deferredSubmit.submit();
    };

    /**
     * Checks if the text contains any of bad words list
     * @param text string the text
     * @param country string country
     * @param resultId integer question id to set the result
     * @param autoProceed auto proceed to the next screen when completed
     */
    this.checkBadWords = function (text, country, resultId, autoProceed) {
        var self = this;
        autoProceed = autoProceed || false;

        var def = $.Deferred();

        deferredSubmit.addDeferred(def);

        $.post(apiUrls.checkBadWords, {text: text, country: country})
            .done(function(result) {
                var containsBadWords = result.valid ? 'false' : 'true';

                $(self.getElementId(resultId)).val(containsBadWords);
                def.resolve();
                deferredSubmit.submit();
            })
            .fail(function(response) {
                def.resolve();
                deferredSubmit.submit();
            });
    };

    this.loadPostCode = function(questionId) {
        var $element = $(this.getElementId(questionId));
        var self = this;

        var checkUrl = apiUrls.checkPostCode + '/' + $element.val();
        var def = $.Deferred();

        deferredSubmit.addDeferred(def);

        $.get(checkUrl, function(){}, 'json')
            .done(function(resp) {
                self.fetchResult(boxnames.postCodes, resp);
                def.resolve();
            })
            .fail(function () {
                def.resolve();
            });
    };

    this.chooseOption = function (optionsId, value, conditionals) {
        var surveyId = SGAPI.survey.surveyObject.id;
        var pageId = SGAPI.survey.pageId;
        var optionsName = 'sgE-' + surveyId + '-' + pageId + '-' + optionsId;

        $('[name=' + optionsName + ']').each(function (k, v) {
            var qV = $(v).prop('value');
            var title = SGAPI.surveyData[surveyId].questions[optionsId].options[qV].value;
            var fnCheck = conditionals[title] || function () { return false; };
            if (fnCheck(value)) {
                $(v).prop('checked',  true);
                $(v).trigger('click');
            }
        });
    };

    var _getElementType = function (id) {
        var surveyId = SGAPI.survey.surveyObject.id;

        return SGAPI.surveyData[surveyId].questions[id].type;
    };

    var _removeQuestionOptions = function (questionId) {
        var questionType = _getElementType(questionId);
        var methodsMap = {
            'MENU': _removeDropdownOptions,
            'RADIO': _removeRadioButtonOptions,
            'CHECKBOX': _removeCheckboxOptions
        };
        var removeMethod = methodsMap[questionType] || false;

        if (!removeMethod) {
            throw '[tgmMobi] Cant add an option to Q[ID:' + questionId + '], unknown type: ' + questionType;
        }

        return removeMethod(questionId);
    };

    var _removeDropdownOptions = function (questionId) {
        var $formElement = $(this.getElementId(questionId));
        $formElement.children('option:not(:first)').remove();
    }.bind(this);

    var _removeRadioButtonOptions = function (questionId) {
        var $formElement = $('.sg-question-options ul.sg-list', $(this.getElementId(questionId, 'box')));
        $formElement.children('li').remove();
    }.bind(this);

    var _removeCheckboxOptions = function (questionId) {
        var $formElement = $('.sg-question-options ul.sg-list', $(this.getElementId(questionId, 'box')));
        $formElement.children('li').remove();
    }.bind(this);

    var _addQuestionOption = function (questionId, opt) {
        var questionType = _getElementType(questionId);
        var methodsMap = {
            'MENU': _addDropdownOption,
            'RADIO': _addRadioButtonOption,
            'CHECKBOX': _addCheckboxOption
        };
        var addMethod = methodsMap[questionType] || false;

        if (!addMethod) {
            throw '[tgmMobi] Cant add an option to Q[ID:' + questionId + '], unknown type: ' + questionType;
        }

        return addMethod(questionId, opt);
    };

    var _addDropdownOption = function (questionId, opt) {
        var $formElement = $(this.getElementId(questionId));

        $formElement.append($('<option>', {
            'value': opt.id,
            'label': _getLocalizedTitle(opt),
            'title': _getLocalizedTitle(opt),
            'text': _getLocalizedTitle(opt)
        }));

        return true;
    }.bind(this);

    var _getLocalizedTitle = function(opt) {
        var lang = SGAPI.survey.sgSurvey.language;
        var defaultLanguage = 'English';

        return opt.title[lang] || opt.title[defaultLanguage];
    };

    var _addRadioButtonOption = function (questionId, opt) {
        var $formElement = $('.sg-question-options ul.sg-list', $(this.getElementId(questionId, 'box')));

        var newOption = $('<li>');
        newOption.append($('<input>', {
            'type': 'radio',
            'class': 'sg-input sg-input-radio',
            'name': this.getElementId(questionId, '', ''),
            'id': this.getElementId(questionId, opt.id, ''),
            'value': opt.id,
            'title': _getLocalizedTitle(opt),
            'aria-hidden': opt.value,
            'tabIndex': '-1'
        }));
        newOption.append($('<label>', {
            'for': this.getElementId(questionId, opt.id, ''),
            'aria-hidden': 'true',
            'text': _getLocalizedTitle(opt),
            'tabIndex': '0'
        }));

        $formElement.append(newOption);

        $formElement.children('li:not(:first)').removeClass('sg-first-li');
        $formElement.children('li:not(:last)').removeClass('sg-last-li');
        $formElement.children('li:first').addClass('sg-first-li');
        $formElement.children('li:last').addClass('sg-last-li');

        return true;
    }.bind(this);

    var _addCheckboxOption = function (questionId, opt) {
        var $formElement = $('.sg-question-options ul.sg-list', $(this.getElementId(questionId, 'box')));

        var newOption = $('<li>');
        newOption.append($('<input>', {
            'type': 'checkbox',
            'class': 'sg-input sg-input-checkbox',
            'name': this.getElementId(questionId, '', ''),
            'id': this.getElementId(questionId, opt.id, ''),
            'value': opt.id,
            'title': _getLocalizedTitle(opt),
            'aria-hidden': opt.value,
            'tabIndex': '-1'
        }));
        newOption.append($('<label>', {
            'for': this.getElementId(questionId, opt.id, ''),
            'aria-hidden': 'true',
            'text': _getLocalizedTitle(opt),
            'tabIndex': '0'
        }));

        $formElement.append(newOption);

        $formElement.children('li:not(:first)').removeClass('sg-first-li');
        $formElement.children('li:not(:last)').removeClass('sg-last-li');
        $formElement.children('li:first').addClass('sg-first-li');
        $formElement.children('li:last').addClass('sg-last-li');

        return true;
    }.bind(this);

    /**
     * @param {array|object} options
     * @param {string} sortBy order to sort by asc, desc, shuffle, none
     * @returns {array|object}
     * @private
     */
    var _sortOptions = function (options, sortBy) {
        return _.sortBy(options, function (option) {
            switch (sortBy) {
                case 'asc':
                    return option.value;

                case 'desc':
                    var fnInverseChar = function (c) {
                        var str = 'abcdefghijklmnopqrstuvwxyz';
                        var rev = 'zyxwvutsrqponmlkjihgfedcba';
                        c = c.toLowerCase();
                        return str.indexOf(c) > -1 ? rev.substring(str.indexOf(c), str.indexOf(c)+1) : c;
                    };
                    return _.map(option.value.split(''), fnInverseChar).join('');

                case 'shuffle':
                    return Math.round(Math.random() * 1000000);

                default:
                    return 0;
            }
        })
    };

    /**
     * Sets options depends on value provided.
     * Example: tgmMobi.setOptions()
     * @param questionId integer surveygizmo's question id
     * @param opts array
     */
    this.setOptions = function (questionId, opts) {
        if (!opts.value) {
            throw '[tgmMobi.setOptions] "value" value required.';
        }
        if (!opts.options) {
            throw '[tgmMobi.setOptions] "options" value required.';
        }

        _removeQuestionOptions(questionId);

        var elementsToSet = opts.options[opts.value] || [];

        var surveyId = SGAPI.survey.surveyObject.id;
        var options = SGAPI.surveyData[surveyId].questions[questionId].options;

        var optionsToAdd = [];
        $.each(elementsToSet, function (key, questionIndex) {
            var i = 0;
            $.each(options, function (k, option) {
                ++i;
                if (i === questionIndex) {
                    optionsToAdd.push(option);
                    return false;
                }
            });
        });

        _.each(_sortOptions(optionsToAdd, opts.sort), function(option) {
            _addQuestionOption(questionId, option);
        })
    };

    /**
     * Loads options available into SurveyGizmo's combobox element.
     * Please note, options should be available to save.
     * @param questionId integer a place to load into
     * @param opts options
     */
    this.loadOptions = function(questionId, opts) {
        if (!opts.identifier) {
            throw '[tgmMobi.loadOptions] Options identifier required.';
        }
        console.assert(!!opts.value, '[tgmMobi.loadOptions] Value identifier is not set, a "value" will be used.');
        opts.value = opts.value || 'value';

        var self = this;

        _removeQuestionOptions(questionId);
        $.post(apiUrls.loadOptions, opts).done(function (response) {
            $.each(response, function (i, v) {
                $.each(self.getQuestionOptions(questionId), function (k, option) {
                    if (option.value == v[opts.value]) {
                        _addDropdownOption(questionId, option);
                        return false;
                    }
                });
            });
        });
    };

    this.getQuestionOptions = function(questionId) {
        var surveyId = SGAPI.survey.surveyObject.id;
        var question = SGAPI.surveyData[surveyId].questions[questionId];

        return question ? (question.options || {}) : {};
    };

    var _isMobile = function () {
        return $(document.body).hasClass('sg-mobile');
    };

    var _isQuestionVisible = function (questionId) {
        var $el = $(this.getElementId(questionId, 'box'));
        var elExists = $el.length > 0;

        return elExists && (!_isMobile() || $el.hasClass('sg-page-current'));
    }.bind(this);

    var base64_encode = function(str) {
        return btoa(encodeURIComponent(str).replace(/%([0-9A-F]{2})/g,
            function toSolidBytes(match, p1) {
                return String.fromCharCode('0x' + p1);
            }));
    };

    var _serializeAjaxParameters = function (opts) {
        return {'_': base64_encode(JSON.stringify(opts))};
    };

    var _getCheckedOptions = function (questionId) {
        var questionType = _getElementType(questionId);
        var methodsMap = {
            'MENU': _getCheckedDropdown,
            'RADIO': _getCheckedRadiobuttons,
            'CHECKBOX': _getCheckedCheckboxes
        };
        var checkMethod = methodsMap[questionType] || false;

        if (!checkMethod) {
            throw '[tgmMobi] Cant get checked options for Q[ID:' + questionId + '], unknown type: ' + questionType;
        }

        return checkMethod(questionId);
    };

    var _getCheckedDropdown = function (questionId) {
        var checked = [];
        $('option', $(this.getElementId(questionId))).each(function(key, element) {
            if ($(element).is(':selected')) {
                checked.push(key);
            }
        });

        return checked;
    }.bind(this);

    var _getCheckedCheckboxes = function (questionId) {
        var checked = [];
        $('.sg-question-options ul.sg-list li input', $(this.getElementId(questionId, 'box'))).each(function(key, element) {
            if ($(element).is(':checked')) {
                checked.push(key+1);
            }
        });

        return checked;
    }.bind(this);

    var _getCheckedRadiobuttons = function (questionId) {
        var checked = [];
        $('.sg-question-options ul.sg-list li input', $(this.getElementId(questionId, 'box'))).each(function(key, element) {
            if ($(element).is(':checked')) {
                checked.push(key+1);
            }
        });

        return checked;
    }.bind(this);

    var _setQuestionResult = function (questionId, result) {
        $(this.getElementId(questionId)).val(result);
    }.bind(this);

    /**
     * On page submit sends question result and dirtyOptions to calculate dirty score to the TGM.Mobi Server
     * @param {integer} questionId question ID for the question to check
     * @param {array} dirtyOptions list of dirty options, numeric
     * @param {integer} resultId textifield ID to save a dirty-score for the entire survey calculated
     */
    this.checkDirty = function (questionId, dirtyOptions, resultId) {
        var self = this;

        deferredSubmit.addOnSubmitHandler(function() {
            var parameters = {
                survey: _rmsidSurvey,
                respondent: _rmsidRespondent,
                question: questionId,
                dirty: dirtyOptions,
                selected: _getCheckedOptions(questionId),
                type: _getElementType(questionId)
            };

            var defChecking = $.Deferred();

            $.post(apiUrls.saveDirtyScore, _serializeAjaxParameters(parameters))
                .done(function (result) {
                    if (resultId) {
                        _setQuestionResult(resultId, result.dirtyscore);
                    }
                    defChecking.resolve();
                }).fail(function () {
                if (resultId) {
                    _setQuestionResult(resultId, 'Error');
                }
                defChecking.resolve();
            });

            return defChecking;
        }.bind(this), true);
    };

    this.loadDirty = function (resultId) {
        var parameters = {
            survey: _rmsidSurvey,
            respondent: _rmsidRespondent
        };

        var defChecking = $.Deferred();

        deferredSubmit.addDeferred(defChecking);

        $.post(apiUrls.saveDirtyScore, _serializeAjaxParameters(parameters))
            .done(function (result) {
                if (resultId) {
                    _setQuestionResult(resultId, result.dirtyscore);
                }
                defChecking.resolve(result.dirtyscore);
            }).fail(function () {
            if (resultId) {
                _setQuestionResult(resultId, 'Error');
            }
            defChecking.resolve('An error occurred');
        });
    };

    this.saveOptionsOrder = function (optionsQuestionId, orderQuestionId, separator) {
        var optionsSeparator = separator || ';;;';
        var _order = [];

        $(this.getElementId(optionsQuestionId, 'box') + ' ul > li').each(function() {
            var title = $($(this).find('label')[0]).text();
            _order.push(title);
        });
        $(this.getElementId(orderQuestionId)).val(_order.join(optionsSeparator));
    };

    this.restoreOptionsOrder = function (questionId, orderSaved, separator) {
        var orderSeparator = separator || ';;;';

        var options = orderSaved.split(orderSeparator);
        var items = [];

        var boxIdList = this.getElementId(questionId, 'box') + ' ul';

        $(boxIdList + ' > li').each(function() {
            var title = $($(this).find('label')[0]).text();
            items[title] = this;
            this.remove();
        });

        _.each(options, function(option) {
            $(boxIdList).append(items[option]);
        });

        $(boxIdList + ' > li:last-child').removeClass('sg-last-li');
        $(boxIdList + ' > li:last-child').removeClass('sg-first-li');
        $(boxIdList + ' > li:last-child').addClass('sg-last-li');
        $(boxIdList + ' > li:first-child').addClass('sg-first-li');
    };

    this.getGeolocation = function(rmsid, coordinatesId, addressId) {
        var self = this;

        var onGeolocateSuccess = function(position) {
            var checkUrl = apiUrls.checkLocation;

            var setAddressValue = function (addressValue) {
                $(self.getElementId(addressId)).val(addressValue);
            };

            var coordinates = position.coords.longitude + ',' + position.coords.latitude;
            $(self.getElementId(coordinatesId)).val(coordinates);
            setAddressValue('Loading...');

            var def = $.Deferred();

            deferredSubmit.addDeferred(def);

            $.post(checkUrl, {
                rmsid: rmsid,
                latitude: position.coords.latitude,
                longitude: position.coords.longitude
            }, 'json')
                .done(function(resp) {
                    setAddressValue(resp.address);
                    def.resolve();
                })
                .fail(function () {
                    setAddressValue('A server error occurred');
                    def.resolve();
                }.bind(this));
        };

        var onGeolocateError = function() {
            $(self.getElementId(coordinatesId)).val('');
            $(self.getElementId(addressId)).val('The user has denied access to location service');

        };

        if (!navigator.geolocation || !navigator.geolocation.getCurrentPosition) {
            onGeolocateError();
        }

        navigator.geolocation.getCurrentPosition(onGeolocateSuccess, onGeolocateError);
    };

    this.calculateAge = function(dobId, ageId, yobId) {
        var $dob = $(this.getElementId(dobId));
        var $age = $(this.getElementId(ageId));
        var $yob = $(this.getElementId(yobId));

        $dob.change(function() {
            var dob = $dob.val();

            var momentDob = moment(dob, 'MM/DD/YYYY');
            if (!momentDob.isValid()) {
                momentDob = moment(dob);
                if (!momentDob.isValid()) {
                    momentDob = moment();
                }
            }

            if ($age.length) {
                $age.val(moment().diff(momentDob, 'years'));
            }

            if ($yob.length) {
                $yob.val(momentDob.format('YYYY'));
            }
        });
    };

    var _getQuestionId = function () {
        var id = $('.sg-page-shown.sg-page-current').prop('id');

        return id ? id.split('-')[3] : null;
    };

    this.set = function(survey, respondent) {
        _rmsidSurvey = survey;
        _rmsidRespondent = respondent;
    };

    var _getTime = function () {
        return new Date().getTime()/1e3;
    };
    var _startTimer = function () {
        _timingStarted = _getTime();
        _timingFinished = _timingStarted;
    };
    var _stopTimer = function () {
        clearInterval(_timingTimer);
        var now = _getTime();

        _timingFinished = (now - _timingFinished < 200) ? now : _timingFinished;
    };

    var _round = function (value, prec) {
        var factor = Math.pow(10, prec);
        return Math.round(value * factor) / factor;
    };

    var _setTiming = function () {
        var timerInterval = 200;

        $(function() {
            _startTimer();

            _timingTimer = setInterval(function () {
                _timingFinished += timerInterval/1e3;
            }, timerInterval);

            deferredSubmit.addOnSubmitHandler(function () {
                _stopTimer();

                var parameters = {
                    survey: _rmsidSurvey,
                    respondent: _rmsidRespondent,
                    pageId: SGAPI.survey.pageId,
                    questionId: _getQuestionId(),
                    time: _round(_timingFinished - _timingStarted, 3)
                };

                var defTimings = $.Deferred();

                $.post(apiUrls.timeScore, _serializeAjaxParameters(parameters))
                    .done(function (result) {
                        defTimings.resolve();
                        _startTimer();
                    })
                    .fail(function (result) {
                        defTimings.resolve();
                        _startTimer();
                    });

                return defTimings;
            }.bind(this), true);
        });
    };

    this.loadTimings = function (questionId, options) {
        options = options || {};

        var self = this;
        var proceed = undefined === options.proceed ? true : options.proceed;
        var callback = options.callback || null;
        var parameters = {
            survey: _rmsidSurvey,
            respondent: _rmsidRespondent,
            value: options.value || 'sum',
            questions: options.questions || [],
            pages: options.pages || [],
            exceptQuestions: options.exceptQuestions || [],
            exceptPages: options.exceptPages || []
        };

        var def = $.Deferred();
        deferredSubmit.addDeferred(def);

        $.post(apiUrls.timeScore, _serializeAjaxParameters(parameters))
            .done(function (result) {
                $(self.getElementId(questionId)).val(result.value);
                _isFunction(callback) && callback();
                if (proceed) {
                    deferredSubmit.submit();
                }
                def.resolve();
            }).fail(function (result) {
            _isFunction(callback) && callback();
            if (proceed) {
                deferredSubmit.submit();
            }
            def.resolve();
        });

    };

    this.redirect = function (url, params, delay) {
        params = params || {};
        delay = delay || 0;

        var redirectFunction = function () {
            var u = url;
            _.each(params, function (value, param) {
                u = u.replace('{' + param + '}', encodeURIComponent(value));
            });

            window.location = u;
        };

        if (!!delay) {
            setTimeout(redirectFunction, delay * 1000);
        } else {
            redirectFunction();
        }
    };

    this.init = function() {
        $(function(){
            this.loadIframeStyle();
        }.bind(this));

        deferredSubmit.init();

        _setTiming();

        // comparison functions
        window.between = function (a, b) {
            return function (value) {
                return value >= a && value <= b;
            }
        };
    };

    this.init();
}

tgmData.SENTRY && Raven.config(tgmData.SENTRY).install();
var tgmMobi = new TgmMobi(SGAPI, window);
