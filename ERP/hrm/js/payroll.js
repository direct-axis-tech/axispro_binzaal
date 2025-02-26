$(function() {
    /** @type {HTMLFormElement} */
    var filterFormElem = document.getElementById('filter_form');
    var payrollTableElId = 'payroll_tbl';
    var finalizeBtnId = 'finalize_payroll';
    var postToGlBtnId   = 'post_gl';
    var canRedoPayslip = Boolean(parseInt(document.getElementById('can_redo_payslip').value));
    var storage = {
        configurations: null,
        payElements: null,
        payroll: null,
        payslips: null,
        activeFilters: null,
        handlers: new Handlers()
    };

    // Initialise the main form
    (function() {
        var _findClosestFormGroup = function(field) {
            return field.$element.closest('.form-group')
        };
        var pslyForm = $(filterFormElem).parsley({
            errorClass: 'is-invalid',
            successClass: 'is-valid',
            errorsWrapper: '<ul class="errors-list"></ul>',
            classHandler: _findClosestFormGroup,
            errorsContainer: _findClosestFormGroup
        });
        
        // handle the export button
        $('[data-exports="xls"]:not(select)').on('click', handleExport);
        $('select[data-exports="xls"]').on('change', handleExport);
        
        function handleExport() {
            var method = this.dataset.method;
            var molId = this.value;
            if (storage.activeFilters) {
                exportPayroll($.param(storage.activeFilters), method, molId);
            } else {
                if (pslyForm.isValid({force: true})) {
                    exportPayroll(pslyForm.$element.serialize(), method, molId);
                } else {
                    pslyForm.validate({force: true});
                }
            }
        }

        // handle the submit
        pslyForm.on('form:submit', function() {
            refreshTable(pslyForm.$element.serialize());
            return false;
        })
    })();

    // handle the finalize button that will process the payroll once and for all
    $('#' + finalizeBtnId).on('click', function(ev) {
        if (!storage.activeFilters) {
            return toastr.error('Please select the payroll');
        }

        if (document.getElementById(payrollTableElId).dataset.is_processed == 'true') {
            return toastr.error('This payroll is already finalized');
        }

        var data = {[this.name]: this.value};
        for (var key in storage.activeFilters) {
            data[key] = storage.activeFilters[key];
        }

        setBusyState();
        $.ajax({
            url: filterFormElem.action,
            method: 'post',
            data: data,
            dataType: 'json'
        }).done(function (res) {
            if (res && res.status == 200) {
                toastr.success("Payroll processed successfully");
                refreshTable(storage.activeFilters);
            } else {
                toastr.error((res && res.message) ? res.message : "Something went wrong!");
            }
        }).fail(function (xhr) {
            toastr.error("Somthing went wrong!")
        }).always(unsetBusyState);
    });

    // handle the POST GL button process
    $('#' + postToGlBtnId).on('click', function(ev) {
        if (document.getElementById(payrollTableElId).dataset.is_processed == 'false') {
            return toastr.error('Only Finalized Payroll can POST GL');
        }

        var data = {[this.name]: this.value};
        for (var key in storage.activeFilters) {
            data[key] = storage.activeFilters[key];
        }
        setBusyState();
        $.ajax({
            url: filterFormElem.action,
            method: 'post',
            data: data,
            dataType: 'json'
        }).done(function (res, statusTxt, xhr) {
            if (res && res.status == 200) {
                toastr.success("GL Transactions Posted");
                refreshTable(storage.activeFilters);
            } else {
                defaultErrorHandler(xhr);
            }
        })
        .fail(defaultErrorHandler)
        .always(unsetBusyState);
    });



    // when something in the payroll changes, handle the related change
    $('#' + payrollTableElId + ':not([data-is-processed="true"])').on('change', 'input', function(event) {
        var changeTotalHandlerKey = 'onChangeTotal';
        var map = {
            // holidays_worked: 'onChangeHolidaysWorked',
            // weekends_worked: 'onChangeWeekendsWorked',
            minutes_overtime: 'onChangeMinutesOvertime',
            // minutes_late: 'onChangeMinutesLate',
            // minutes_short: 'onChangeMinutesShort',
            // days_on_leave: 'onChangeLeaveDays',
            // days_absent: 'onChangeAbsentDays',
            // violations: 'onChangeViolations',
            tot_deduction: changeTotalHandlerKey,
            tot_addition: changeTotalHandlerKey,
            net_salary: "onChangeNetSalary"
        }

        // find the correct handler
        var key = this.dataset.key;
        if (key) {
            // We know payelement's key starts with PEL,
            if (key.indexOf('PEL') !== -1) {
                var handlerKey = "onChangePayElement";
            } else if (map[key] !== undefined) {
                var handlerKey = map[key];
            }
        }

        // Call the handler with the required parameters
        if (handlerKey !== undefined) {
            var parentTrEl = $(this).closest('tr')[0];
            var payslip = storage.payslips[parentTrEl.dataset.id];
            var currentValue = parseFloat(this.value ? this.value : 0);
            // ensures that there is always value if user accidently deleted the value
            this.value = currentValue;
            var handler = storage.handlers[handlerKey].bind(this);
            handler(currentValue, parentTrEl, payslip);
        }

        // Adds a marker to show that it was updated
        $(this).closest('td').addClass('modified');
    })

    // handle the process payroll button
    $('#' + payrollTableElId + ':not([data-is-processed="true"])').on(
        'click',
        'tr:not([data-is-processed="true"]) [data-key="process"]',
        function(event) {
            var _this = this;
            Swal.fire({
                title: 'Are you sure?',
                text: "Please make sure that all the information is correct! "
                    + "You won't be able to revert anything later.",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Process!'
            }).then(function(result) {
                if (result.value) {
                    var onClickHandler = onClickProcessPayroll.bind(_this);
                    onClickHandler(event);
                }
            });
        }
    );

    // handle the cancel button click
    if (canRedoPayslip) {
        $('#' + payrollTableElId + ':not([data-is-processed="true"])').on(
            'click',
            '[data-payslip][data-is-processed="true"] [data-key="cancel"]',
            function(event) {
                if (!storage.activeFilters) {
                    return toastr.error('Something went wrong! Please refresh the page [Ctrl+F5]');
                }

                var parentTrEl = $(this).closest('tr')[0];
                var employeeId = parentTrEl.dataset.id;
                var payslipId = storage.payslips[employeeId].id;

                var data = {
                    "redo_payslip": '',
                    "payslip_id": payslipId
                };
                for (var key in storage.activeFilters) {
                    data[key] = storage.activeFilters[key];
                }

                setBusyState();
                $.ajax({
                    method: filterFormElem.method,
                    url: filterFormElem.action,
                    data: data,
                    dataType: 'json'
                }).done(function(res) {
                    try {
                        if (res.status && res.status == 200) {
                            var payslip = res.data;
                            storage.payslips[payslip.employee_id] = payslip;
                            generateTable(storage.payroll, storage.payslips);
                            toastr.success("Reverted payslip Successfully!");
                        } else {
                            var message = res.message ? res.message
                                : "Something went wrong! Please try again later";
                            return toastr.error(message);
                        }
                    } catch (e) {
                        return toastr.error("Something went wrong! Please try again or contact the administrator")
                    }
                }).fail(function(xhr) {
                    return toastr.error("Something went wrong! Please try again or contact the administrator")
                }).always(function() {
                    setBusyState(false);
                });
            }
        )
    }

    /**
     * Refreshes the payroll/payslips table.
     * 
     * @param {string|object} dataToSend The data that needs to be sent to the server
     */
    function refreshTable(dataToSend) {
        setBusyState();
        $.ajax({
            method: filterFormElem.method,
            url: filterFormElem.action,
            data: dataToSend,
            dataType: 'json'
        }).done(function(res) {
            if (res.status && res.status == 200) {
                try {
                    storage.activeFilters = res.data.activeFilters;
                    storage.payroll = res.data.payroll;
                    storage.payslips = res.data.payslips;

                    // Disable the finalize button if already finalized
                    var finalizeBtn = document.getElementById(finalizeBtnId);
                    if (finalizeBtn) finalizeBtn.disabled = storage.payroll.is_processed == '1';

                    // Enable the post to GL button only if payroll is finalized
                    var postToGlBtn = document.getElementById(postToGlBtnId);
                    if (postToGlBtn) postToGlBtn.disabled = storage.payroll.is_processed != '1' || !!storage.payroll.journalized_at;

                    havePayElements()
                        .then(haveConfigurations)
                        .then(verifyAndGenerateTable)
                        .catch(function () {
                            toastr.error('Something went wrong!');
                        });
                } catch (e) {
                    var message = res.message ? res.message
                        : "Something went wrong! Please try again later";
                    return toastr.error(message);
                }
            } else {
                var message = res.message ? res.message
                        : "Something went wrong! Please try again later";
                return toastr.error(message);
            }
        }).fail(function(xhr) {
            toastr.error("Something went wrong! Please try again later or contact the administrator");
        }).always(unsetBusyState);
    }

    /**
     * Handles the process payroll button click
     * 
     * @param {JQuery.ClickEvent<HTMLButtonElement, undefined, any, any>} event The original event
     */
    function onClickProcessPayroll(event) {
        /** @type {HTMLTableRowElement} */
        var parentTrEl = $(this).closest('tr')[0];
        var employeeId = parentTrEl.dataset.id;
        var payslips = [];
        if (employeeId) {
            payslips.push(storage.payslips[employeeId]);
        } else {
            for (var employeeId in storage.payslips) {
                var _payslip = storage.payslips[employeeId];
                if (!Boolean(parseInt(_payslip['is_processed']))) {
                    payslips.push(_payslip);
                }
            }
        }

        var data = {
            process_payslips: '',
            ...storage.activeFilters
        };

        var batchSize = 10;
        var promises = [];

        setBusyState();
        for (i = 0; i < payslips.length; i += batchSize) {
            var payslipsBatch = payslips.slice(i, i + batchSize)
            var _data = {...data};
            payslipsBatch.forEach(payslip => (_data[`payslips[${payslip.employee_id}]`] = payslip));

            promises.push($.ajax({
                method: filterFormElem.method,
                url: filterFormElem.action,
                data: _data,
                dataType: 'json'
            }))
        }

        Promise.all(promises)
            .then(function(responses) {
                var errorMessages = [];
                responses.forEach(res => {
                    if (res.status && res.status == 200) {
                        var payslips = res.data.payslips;
                        for (var employeeId in payslips) {
                            storage.payslips[employeeId] = payslips[employeeId];
                        }
                    }

                    else {
                        errorMessages.push(res.message ? res.message : "Something went wrong! Please try again later")
                    }
                });

                try {
                    generateTable(storage.payroll, storage.payslips);
                } catch (e) {
                    return toastr.error("UI Crashed! Please try refreshing the page or contact the administrator");
                }

                if (errorMessages.length) {
                    var messages = errorMessages
                        .filter((value, index, self) => self.indexOf(value) === index)
                        .join('<br>');

                    return toastr.error(messages);
                }

                else {
                    return toastr.success("Processed Successfully!");
                }
            }).catch(function() {
                return toastr.error("Something went wrong! Please try again or contact the administrator")
            }).finally(unsetBusyState);
    }

    /**
     * Verify all the parameters required for generating table
     * is present and generate the table
     */
    function verifyAndGenerateTable() {
        if (
            storage.payroll
            && storage.payslips
            && storage.payElements
            && storage.configurations
        ) {
            generateTable(storage.payroll, storage.payslips);
        }
    }

    /**
     * Exports the payroll
     * 
     * @param {String} filters A prebuilt query string for the filters
     * @param {"export_payroll"|"export_wps"} method
     * @param {String} molId An optional mol_id if exporting to wps
     */
    function exportPayroll(filters, method, molId = '') {
        var data = filters.length
            ? `${filters}&${method}=`
            : `${method}=`;

        setBusyState();
        $.ajax({
            method: filterFormElem.method,
            url: filterFormElem.action,
            data: `${data}&visa_company_mol_id=${molId}`
        }).done(function(res) {
                try {
                    res = JSON.parse(res);
                    if (res.status && res.status == 422) {
                        return toastr.error(res.message);
                    }
                } catch (e) {
                    try {
                        var doc = (new DOMParser()).parseFromString(res, 'text/html');
                        return window.location = doc.body.querySelector('a[href]').href;
                    } catch (e) { }
                }
                toastr.error("Something went wrong! Please try again later or contact the administrator");
        }).fail(function(xhr) {
            toastr.error("Something went wrong! Please try again later or contact the administrator");
        }).always(function() {
            setBusyState(false);
        });
    }

    /**
     * Generates the payroll table
     * 
     * @param {Object} payroll
     * @param {Object} payslips
     */
    function generateTable(payroll, payslips) {
        /** @type {HTMLTableElement} */
        var payrollTableEl = document.getElementById(payrollTableElId);
        var payrollTableWrapperEl = payrollTableEl.closest('.table-responsive');
        var scrollTop = payrollTableWrapperEl.scrollTop;
        var scrollLeft = payrollTableWrapperEl.scrollLeft;
        var columns = getColumnDefenitions();
        empty(payrollTableEl);
        var isFinalized = Boolean(parseInt(payroll.is_processed));
        payrollTableEl.dataset.isProcessed = isFinalized;
        payrollTableEl.appendChild(generateHead(columns, payslips));
        payrollTableEl.appendChild(generateBody(columns, payslips));
        payrollTableWrapperEl.scrollTop = scrollTop;
        payrollTableWrapperEl.scrollLeft = scrollLeft;

        /**
         * Generates the table's head section
         * @param {[ColDef]} columns 
         * @param {Object} payslips
         * 
         * @returns {HTMLTableSectionElement}
         */
        function generateHead(columns, payslips) {
            var thead = document.createElement('thead');
            var tr = document.createElement('tr');

            columns.forEach(function(column) {
                var th = document.createElement('th');
                th.textContent = column.title;
                if (column.bgClass) {
                    $(th).addClass(column.bgClass);
                }
                tr.appendChild(th);
            })

            var isAllProcessed = true;
            for (var employeeId in payslips) {
                if (!Boolean(parseInt(payslips[employeeId].is_processed))) {
                    isAllProcessed = false;
                    break;
                }
            }

            var th = document.createElement('th');
            var saveButton = actionBtn(
                true,
                'la-check',
                'btn-outline-primary',
                'process',
                'Process All',
                isAllProcessed
            );
            th.appendChild(saveButton);
            tr.appendChild(th);

            thead.appendChild(tr);
            return thead;
        }

        /**
         * Generates the table's body section
         * @param {[ColDef]} columns
         * @param {Object} payslips
         * 
         * @returns {HTMLTableSectionElement}
         */
        function generateBody(columns, payslips) {
            var tbody = document.createElement('tbody');

            for (var employeeId in payslips) {
                var payslip = payslips[employeeId];
                var isProcessed = Boolean(parseInt(payslip['is_processed']));

                var tr = document.createElement('tr');
                tr.dataset.id = employeeId;
                tr.dataset.payslip = true;
                tr.dataset.isProcessed = isProcessed;

                columns.forEach(function (col) {
                    var value = payslip[col.key];
                    var td = document.createElement('td');
                    td.dataset.type = col.type;

                    if (col.type === 'label') {
                        var span = document.createElement('span');
                        span.dataset.value = value;
                        span.textContent = value;

                        td.appendChild(span);
                    } else {
                        var input = document.createElement('input');
                        input.classList.add('payroll-input');
                        input.type = 'number';
                        input.name = `payslip[${employeeId}][${col.key}]`;
                        input.value = value;
                        input.min = 0;
                        input.dataset.key = col.key;
                        input.readOnly = isProcessed || !col.canManipulate;
                        
                        td.appendChild(input);
                    }
                    tr.appendChild(td);
                })

                var td = document.createElement('td');
                var saveButton = actionBtn(
                    false,
                    'la-check',
                    'btn-outline-primary',
                    'process',
                    '',
                    isFinalized || isProcessed
                )
                td.appendChild(saveButton);
                
                if (canRedoPayslip) {
                    td.appendChild($('<div class="vr"></div>')[0]);
                    var cancelButton = actionBtn(
                        false,
                        'la-ban',
                        'btn-outline-warning',
                        'cancel',
                        '',
                        isFinalized || !isProcessed
                    );
                    td.appendChild(cancelButton);
                }

                tr.appendChild(td);

                tbody.appendChild(tr);
            }

            return tbody;
        }

        /**
         * Creates an action button
         * 
         * @param {boolean} isForHead flag to determine if it is for the head tag
         * @param {string} iconClass the icon class
         * @param {string} btnClass the class name for the button
         * @param {string} key the key used to identify the button type
         * @param {string} title the title for the action button
         * @param {boolean} isDisabled the disabled attribute for button
         * 
         * @returns {HTMLButtonElement}
         */
        function actionBtn(isForHead, iconClass, btnClass, key, title, isDisabled) {
            var btn = document.createElement('button');

            var icon = document.createElement('span');
            icon.classList.add('la', iconClass);
            isForHead && icon.classList.add('align-bottom');
            btn.appendChild(icon);

            btn.classList.add('btn', 'shadow-none', btnClass);
            isForHead && btn.classList.add('py-0');
            btn.dataset.key = key;
            btn.title = title;
            btn.disabled = isDisabled;

            return btn;
        }
    }

    /**
     * Gets all the pay elements and caches it if not already there
     * @returns {Promise}
     */
    function havePayElements() {
        return new Promise(function (resolve, reject) {
            if (storage.payElements) {
                return resolve();
            } else {
                setBusyState();
                $.ajax({
                    url: route('API_Call', {method: 'getPayElements'}),
                    method: 'get',
                    dataType: 'json'
                }).done(function(res) {
                    if (res.status && res.status == 200) {
                        storage.payElements = res.data;
                        return resolve();
                    } else {
                        return reject();
                    }
                }).fail(function() {
                    return reject();
                }).always(function() {
                    return setBusyState(false);
                });
            }
        })
    }

    /**
     * Get all the configurations required for the payroll to be processed
     * and caches it
     * 
     * @returns {Promise}
     */
    function haveConfigurations() {
        return new Promise(function(resolve, reject) {
            if (storage.configurations) {
                return resolve();
            } else {
                setBusyState();
                $.ajax({
                    url: route('API_Call', {method: 'getConfigurationsForProcessingPayroll'}),
                    method: 'get',
                    dataType: 'json'
                }).done(function(res) {
                    if (res.status && res.status == 200) {
                        storage.configurations = res.data;
                        return resolve();
                    } else {
                        return reject();
                    }
                }).fail(function() {
                    return reject();
                }).always(function() {
                    return setBusyState(false);
                });
            }
        })
    }

    /**
     * @typedef {{
     *    key: string,
     *    title: string,
     *    type: 'label'|'info'|'metric'|'pay_el'|'summery',
     *    canManipulate: boolean
     *    bgClass?: 'string'
     * }} ColDef
     */

    /**
     * Generates the column defenitions if not already exists.
     * @returns {[ColDef]}
     */
    function getColumnDefenitions() {
        if (!storage.columns) {
            // gets the Object.values()
            var configuredPayElements = [];
            var configs = storage.configurations.payslipElements;
            for (var key in configs) {
                configuredPayElements[configuredPayElements.length] = configs[key];
            }
            
            var payElementsColumns = storage.payElements.map(function(payElement) {
                return {
                    key: 'PEL-' + payElement.id,
                    title: payElement.name,
                    type: 'pay_el',
                    canManipulate: !(configuredPayElements.indexOf(payElement.id) !== -1 || payElement.is_fixed === '1'),
                    bgClass: payElement.is_fixed === '1' ? 'bg-pel-fixed' : (payElement.type === '-1' ? 'bg-pel-ded' : 'bg-pel-alw')
                }
            });
            
            storage.columns = [
                {
                    key: 'id',
                    title: 'ID',
                    type: 'label',
                    canManipulate: false
                },
                {
                    key: 'emp_ref',
                    title: 'Emp. Ref',
                    type: 'label',
                    canManipulate: false
                },
                {
                    key: 'name',
                    title: 'Name',
                    type: 'label',
                    canManipulate: false
                },
                {
                    key: 'working_company',
                    title: 'Working Company',
                    type: 'label',
                    canManipulate: false
                },
                {
                    key: 'visa_company',
                    title: 'Visa Company',
                    type: 'label',
                    canManipulate: false
                },
                {
                    key: 'department',
                    title: 'Department',
                    type: 'label',
                    canManipulate: false
                },
                {
                    key: 'designation',
                    title: 'Designation',
                    type: 'label',
                    canManipulate: false
                },
                {
                    key: 'mode_of_payment',
                    title: 'Payment Mode',
                    type: 'label',
                    canManipulate: false
                },
                {
                    key: 'monthly_salary',
                    title: 'Salary',
                    type: 'info',
                    canManipulate: false
                },
                {
                    key: 'per_day_salary',
                    title: 'Per Day Salary',
                    type: 'info',
                    canManipulate: false
                },
                {
                    key: 'per_hour_salary',
                    title: 'Per Hour Salary',
                    type: 'info',
                    canManipulate: false
                },
                {
                    key: 'pension_employer_share',
                    title: 'Pension Employer Sh.',
                    type: 'metric',
                    canManipulate: false
                },
                {
                    key: 'commission_earned',
                    title: 'Commission Earned',
                    type: 'info',
                    canManipulate: false
                },
                {
                    key: 'expense_offset',
                    title: 'Expense Offset',
                    type: 'info',
                    canManipulate: false
                },
                {
                    key: 'days_not_worked',
                    title: 'Days Not Worked',
                    type: 'info',
                    canManipulate: false
                },
                {
                    key: 'holidays_worked',
                    title: 'Holidays Worked',
                    type: 'metric',
                    canManipulate: false
                },
                {
                    key: 'weekends_worked',
                    title: 'Weekends Worked',
                    type: 'metric',
                    canManipulate: false
                },
                {
                    key: 'minutes_overtime',
                    title: 'Minutes Overtime',
                    type: 'metric',
                    canManipulate: true
                },
                {
                    key: 'minutes_late',
                    title: 'Minutes Late',
                    type: 'metric',
                    canManipulate: false
                },
                {
                    key: 'minutes_short',
                    title: 'Minutes Short',
                    type: 'metric',
                    canManipulate: false
                },
                {
                    key: 'days_on_leave',
                    title: 'Leave Days',
                    type: 'metric',
                    canManipulate: false
                },
                {
                    key: 'days_absent',
                    title: 'Absent Days',
                    type: 'metric',
                    canManipulate: false
                },
            ].concat(
                payElementsColumns,
                [
                    {
                        key: 'tot_addition',
                        title: 'Total Addition',
                        type: 'summery',
                        canManipulate: false,
                        bgClass: 'bg-pel-alw'
                    },
                    {
                        key: 'tot_deduction',
                        title: 'Total Deduction',
                        type: 'summery',
                        canManipulate: false,
                        bgClass: 'bg-pel-ded'
                    },
                    {
                        key: 'net_salary',
                        title: 'Net Salary',
                        type: 'summery',
                        canManipulate: false
                    }
                ]
            );
        }

        return storage.columns;
    }

    /**
     * The handler functions for handling any change in the payroll
     */
    function Handlers() {
        // /**
        //  * Handles when the employee's worked number of holidays changes.
        //  * 
        //  * @this {HTMLInputElement}
        //  * @param {Number} currentValue
        //  * @param {HTMLTableRowElement} parentTrEl The parent row element
        //  * @param {Object} payslip The data associated with the row
        //  */
        // this.onChangeHolidaysWorked = function onChangeHolidaysWorked(currentValue, parentTrEl, payslip) {
        //     var perDaySalary = parseFloat(payslip.per_day_salary);
        //     var previousHolidaysWorked = parseFloat(payslip.holidays_worked)
        //     var holidaysPayElementKey = `PEL-${storage.configurations.payslipElements.holidays_worked}`;

        //     /** @type {HTMLInputElement} */
        //     var holidaysPayElement = parentTrEl.querySelector(`[data-key="${holidaysPayElementKey}"]`);
        //     var publicHolidayRate = parseFloat(storage.configurations.publicHolidayRate);
        //     var previousAddition = previousHolidaysWorked * perDaySalary * (publicHolidayRate - 1);
        //     var currentAddition = currentValue * perDaySalary * (publicHolidayRate - 1);
        //     var totalAddition = parseFloat(holidaysPayElement.value) - previousAddition + currentAddition;
        //     holidaysPayElement.value = totalAddition;
            
        //     payslip.holidays_worked = currentValue;
        //     $(holidaysPayElement).trigger('change');
        // }

        // /**
        //  * Handles when the employee's worked number of weekends changes.
        //  * 
        //  * @this {HTMLInputElement}
        //  * @param {Number} currentValue
        //  * @param {HTMLTableRowElement} parentTrEl The parent row element
        //  * @param {Object} payslip The data associated with the row
        //  */
        // this.onChangeWeekendsWorked = function onChangeWeekendsWorked(currentValue, parentTrEl, payslip) {
        //     var perDaySalary = parseFloat(payslip.per_day_salary);
        //     var previousWeekendsWorked = parseFloat(payslip.weekends_worked)
        //     var weekendsPayElementKey = `PEL-${storage.configurations.payslipElements.weekends_worked}`;

        //     /** @type {HTMLInputElement} */
        //     var weekendsPayElement = parentTrEl.querySelector(`[data-key="${weekendsPayElementKey}"]`);
        //     var weekendRate = parseFloat(storage.configurations.weekendRate);
        //     var previousAddition = previousWeekendsWorked * perDaySalary * (weekendRate - 1);
        //     var currentAddition = currentValue * perDaySalary * (weekendRate - 1);
        //     var totalAddition = parseFloat(weekendsPayElement.value) - previousAddition + currentAddition;
        //     weekendsPayElement.value = totalAddition;
            
        //     payslip.weekends_worked = currentValue;
        //     $(weekendsPayElement).trigger('change');
        // }

        /**
         * Handles when the employee's overtime changes.
         * 
         * @this {HTMLInputElement}
         * @param {Number} currentValue
         * @param {HTMLTableRowElement} parentTrEl The parent row element
         * @param {Object} payslip The data associated with the row
         */
        this.onChangeMinutesOvertime = function onChangeMinutesOvertime(currentValue, parentTrEl, payslip) {
            // we don't want fractions in minutes
            currentValue = Math.round(currentValue);
            this.value = currentValue;

            var currentValueInHour = currentValue / 60;
            var overtimePayElementKey = `PEL-${storage.configurations.payslipElements.minutes_overtime}`;
            /** @type {HTMLInputElement} */
            var overtimePayElement = parentTrEl.querySelector(`[data-key="${overtimePayElementKey}"]`);
            var overtimeRate = parseFloat(storage.configurations.overtimeRate);
            var perHourOvertimeSalary = parseFloat(payslip.per_hour_overtime_salary);
            var previousOvertimeHour = parseInt(payslip.minutes_overtime) / 60;
            var previousOvertimeAlw = previousOvertimeHour * perHourOvertimeSalary * overtimeRate;
            var currentOvertimeAlw = currentValueInHour * perHourOvertimeSalary * overtimeRate;
            var totalOvertimeAlw = parseFloat(overtimePayElement.value) - previousOvertimeAlw + currentOvertimeAlw;
            overtimePayElement.value = totalOvertimeAlw;
            
            payslip.minutes_overtime = currentValue;
            $(overtimePayElement).trigger('change');
        }

        // /**
        //  * Handles when the employee's late minutes changes.
        //  * 
        //  * @this {HTMLInputElement}
        //  * @param {Number} currentValue
        //  * @param {HTMLTableRowElement} parentTrEl The parent row element
        //  * @param {Object} payslip The data associated with the row
        //  */
        //  this.onChangeMinutesLate = function onChangeMinutesLate(currentValue, parentTrEl, payslip) {
        //     var perMinuteSalary = parseFloat(payslip.per_hour_salary) / 60;
        //     var previousMinutesLate = parseInt(payslip.minutes_late);

        //     // we don't want fractions in minutes
        //     currentValue = Math.round(currentValue);
        //     this.value = currentValue;

        //     var latePayElementKey = `PEL-${storage.configurations.payslipElements.minutes_late}`;
        //     /** @type {HTMLInputElement} */
        //     var latePayElement = parentTrEl.querySelector(`[data-key="${latePayElementKey}"]`);
        //     var lateComingRate = parseFloat(storage.configurations.lateComingRate);
        //     var previousLateDed = previousMinutesLate * perMinuteSalary * lateComingRate;
        //     var currentLateDed = currentValue * perMinuteSalary * lateComingRate;
        //     var totalDeduction = parseFloat(latePayElement.value) - previousLateDed + currentLateDed;
        //     latePayElement.value = totalDeduction;
            
        //     payslip.minutes_late = currentValue;
        //     $(latePayElement).trigger('change');
        // }

        // /**
        //  * Handles when the employee's short minutes changes.
        //  * 
        //  * @this {HTMLInputElement}
        //  * @param {Number} currentValue
        //  * @param {HTMLTableRowElement} parentTrEl The parent row element
        //  * @param {Object} payslip The data associated with the row
        //  */
        //  this.onChangeMinutesShort = function onChangeMinutesShort(currentValue, parentTrEl, payslip) {
        //     var perMinuteSalary = parseFloat(payslip.per_hour_salary) / 60;
        //     var previousMinutesShort = parseInt(payslip.minutes_short);

        //     // we don't want fractions in minutes
        //     currentValue = Math.round(currentValue);
        //     this.value = currentValue;

        //     var shortPayElementKey = `PEL-${storage.configurations.payslipElements.minutes_short}`;
        //     /** @type {HTMLInputElement} */
        //     var shortPayElement = parentTrEl.querySelector(`[data-key="${shortPayElementKey}"]`);
        //     var earlyGoingRate = parseFloat(storage.configurations.earlyGoingRate);
        //     var previousDed = previousMinutesShort * perMinuteSalary * earlyGoingRate;
        //     var currentDed = currentValue * perMinuteSalary * earlyGoingRate;
        //     var totalDeduction = parseFloat(shortPayElement.value) - previousDed + currentDed;
        //     shortPayElement.value = totalDeduction;
            
        //     payslip.minutes_short = currentValue;
        //     $(shortPayElement).trigger('change');
        // }

        // /**
        //  * Handles when the employee's absent days changes.
        //  * 
        //  * @this {HTMLInputElement}
        //  * @param {Number} currentValue
        //  * @param {HTMLTableRowElement} parentTrEl The parent row element
        //  * @param {Object} payslip The data associated with the row
        //  */
        // this.onChangeAbsentDays = function onChangeAbsentDays(currentValue, parentTrEl, payslip) {
        //     var perDayDeduction = parseFloat(payslip.per_day_salary);

        //     var absentPayElementKey = `PEL-${storage.configurations.payslipElements.days_absent}`;
        //     /** @type {HTMLInputElement} */
        //     var absentPayElement = parentTrEl.querySelector(`[data-key="${absentPayElementKey}"]`);
        //     var previousAbsentDays = parseFloat(payslip.days_absent);
        //     var totalDeduction = (
        //         parseFloat(absentPayElement.value)
        //         - (previousAbsentDays * perDayDeduction)
        //         + (currentValue * perDayDeduction)
        //     );
        //     absentPayElement.value = totalDeduction;
            
        //     payslip.days_absent = currentValue;
        //     $(absentPayElement).trigger('change');
        // }

        // /**
        //  * Handles when the employee's violations changes.
        //  * 
        //  * @this {HTMLInputElement}
        //  * @param {Number} currentValue
        //  * @param {HTMLTableRowElement} parentTrEl The parent row element
        //  * @param {Object} payslip The data associated with the row
        //  */
        // this.onChangeViolations = function onChangeViolations(currentValue, parentTrEl, payslip) {
        //     var perDayDeduction = parseFloat(payslip.per_day_salary);

        //     var violationsPayElementKey = `PEL-${storage.configurations.payslipElements.violations}`;
        //     /** @type {HTMLInputElement} */
        //     var violationsPayElement = parentTrEl.querySelector(`[data-key="${violationsPayElementKey}"]`);
        //     var previousViolations = parseFloat(payslip.violations);
        //     var totalDeduction = (
        //         parseFloat(violationsPayElement.value)
        //         - (previousViolations * perDayDeduction)
        //         + (currentValue * perDayDeduction)
        //     );
        //     violationsPayElement.value = totalDeduction;
            
        //     payslip.violations = currentValue;
        //     $(violationsPayElement).trigger('change');
        // }

        /**
         * Handles when any of the payElement Changes
         * 
         * @this {HTMLInputElement}
         * @param {Number} currentValue
         * @param {HTMLTableRowElement} parentTrEl The parent row element
         * @param {Object} payslip The data associated with the row
         */
        this.onChangePayElement = function onChangePayElement(currentValue, parentTrEl, payslip) {
            var key = this.dataset.key;
            var previousAmount = parseFloat(payslip[key]);
            var payElementId = key.split('-')[1];
            var payElement = storage.payElements.find(function(payElement) {
                return payElement.id === payElementId;
            });

            // Update the total deduction / total addition based on the type of this payelement
            var totalElKey = payElement.type === "-1" ? 'tot_deduction' : 'tot_addition';
            /** @type {HTMLInputElement} */
            var totalEl = parentTrEl.querySelector(`[data-key="${totalElKey}"]`);
            var total = parseFloat(totalEl.value) - previousAmount + currentValue;
            totalEl.value = total;

            // Now update the current value for this payelement that is in our storage
            payslip[key] = currentValue;

            $(totalEl).trigger('change');
        }

        /**
         * Handles when either total deduction or total addition changes
         * 
         * @this {HTMLInputElement}
         * @param {Number} currentValue
         * @param {HTMLTableRowElement} parentTrEl The parent row element
         * @param {Object} payslip The data associated with the row
         */
        this.onChangeTotal = function onChangeTotal(currentValue, parentTrEl, payslip) {
            var key = this.dataset.key;
            var factor = key === 'tot_deduction' ? -1 : 1;
            var previousAmount = parseFloat(payslip[key]);

            /** @type {HTMLInputElement} */
            var netSalaryEl = parentTrEl.querySelector('[data-key="net_salary"]');
            var netSalary = parseFloat(netSalaryEl.value) - (previousAmount * factor) + (currentValue * factor);
            netSalaryEl.value = netSalary;

            payslip[key] = currentValue;

            $(netSalaryEl).trigger('change');
        }

        /**
         * Handles when net salary changes
         * 
         * @this {HTMLInputElement}
         * @param {Number} currentValue
         * @param {HTMLTableRowElement} parentTrEl The parent row element
         * @param {Object} payslip The data associated with the row
         */
        this.onChangeNetSalary = function onChangeNetSalary(currentValue, parentTrEl, payslip) {
            payslip['net_salary'] = currentValue;
        }
    }
});