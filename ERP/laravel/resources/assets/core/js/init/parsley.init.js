"use strict";

(function() {
    var findClosestGroupWrapper = function(field) {
        return field.$element.closest('[data-parsley-form-group], div[class^="col"], div[class*=" col"]');
    }

    $.extend(window.Parsley.options, {
        errorClass: 'is-invalid',
        successClass: 'is-valid',
        errorsWrapper: '<ul class="errors-list"></ul>',
        inputs: 'input, textarea, select, [data-parsley-control]',
        excluded: "input[type=button], input[type=submit], input[type=reset], input[type=hidden], textarea.select2-search__field",
        errorTemplate: '<li></li>',
        classHandler: findClosestGroupWrapper,
        errorsContainer: findClosestGroupWrapper,
    });

    window.Parsley.addValidator('maxFileSize', {
        requirementType: 'number',
        validate: function (value, requirement, parsleyInstance) {
            var file = parsleyInstance.element.files;
            var maxBytes = requirement * 1048576;

            if (file.length == 0) {
                return true;
            }

            return file.length === 1 && file[0].size <= maxBytes;

        },
        messages: {
            en: 'File is too big'
        }
    })

    window.Parsley.addValidator('mimetypes', {
        requirementType: 'string',
        validate: function (value, requirement, parsleyInstance) {
            var file = parsleyInstance.element.files;

            if (file.length == 0) {
                return true;
            }

            var allowedMimeTypes = requirement.replace(/\s/g, "").split(',');
            return allowedMimeTypes.indexOf(file[0].type) !== -1;
        },
        messages: {
            en: 'File mime type not allowed'
        }
    });

    window.Parsley.addValidator('maxDays', {
        requirementType: 'number',
        validate: function(value, maxDays, parsleyInstance) {
            let fromElId = `#${parsleyInstance.element.dataset.parsleyFromDate}`;
            let tillElId = `#${parsleyInstance.element.dataset.parsleyTillDate}`;

            if (!$(fromElId)[0] || !$(tillElId)[0]) {
                throw new Error('FromDate or TillDate control is missing!');
            }

            if (!parsleyInstance.element.dataset.parsleyListenersAdded) {
                $(`${fromElId}, ${tillElId}`).on('change', () => {
                    parsleyInstance.validate();
                })

                parsleyInstance.element.dataset.parsleyListenersAdded = true;
            }

            return Math.abs($(fromElId).datepicker('getDate') - $(tillElId).datepicker('getDate')) / 86400000 <= maxDays;
        },
        messages: {en: 'The date-period cannot exceed %s days'},
    });

    // Adds Custom Validator pattern2
    window.Parsley.addValidator('pattern2', {
        validateString: function validateString(value, regexp, parsleyInstance) {
            if (!value && !parsleyInstance.element.dataset.parsleyValidateIfEmpty) {
                return true;
            }

            var flags = '';
            if (/^\/.*\/(?:[gisumy]*)$/.test(regexp)) {
                flags = regexp.replace(/.*\/([gisumy]*)$/, '$1');
                regexp = regexp.replace(new RegExp('^/(.*?)/' + flags + '$'), '$1');
            } else {
                regexp = '^' + regexp + '$';
            }

            regexp = new RegExp(regexp, flags);
            return regexp.test(value);
        },
        requirementType: 'string',
        messages: {
            en: 'This value seems to be invalid'
        }
    });

    // Adds custom validator required-with
    window.Parsley.addValidator('requiredWith', {
        messages: {en: 'This value is required'},
        requirementType: 'string',
        validate: function(_value, requirement, instance) {
            const $el = $(requirement);
            let isRequired = false;
            
            if (!$el.length) {
                throw new Error(`${requirement} is not an element`);
            }

            if ($el[0].tagName == 'INPUT' && ($el[0].type == 'radio' || $el[0].type == 'checkbox')) {
                isRequired = $(`${requirement}:checked`).length != 0;
            }
            
            else {
                let val = $el.val();
                isRequired = (isNaN(parseFloat(val)) ? val.length : parseFloat(val)) != 0;
            }

            if (!isRequired) return true;

            if (instance.element.tagName == 'INPUT' && (instance.element.type == 'radio' || instance.element.type == 'checkbox')) {
                if (!instance.element.name) return true;

                return $(`${instance.element.name}:checked`).length != 0;
            }

            return (isNaN(parseFloat(_value)) ? _value.length : parseFloat(_value)) != 0;
        }
    });

    $(function () {
        $('[data-parsley-required-with]:not([data-parsley-validate-if-empty])').each(function () {
            this.setAttribute('data-parsley-validate-if-empty', 'true');
        })
    })
})();