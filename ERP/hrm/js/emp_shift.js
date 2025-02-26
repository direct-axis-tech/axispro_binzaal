$(function() {
  /** @type {HTMLFormElement} */
    var filterFormElem = document.getElementById('filter_form');
    var shiftFormElem = document.getElementById('emp_shifts_form');
    var empShiftTableId = 'emp_shifts_tbl';
    var saveShiftBtnId = 'save_shifts_btn';
    var defaultWeekends = document.getElementById('default_weekends').value.split(',');
    var canEditShift = Boolean(parseInt(document.getElementById('can_edit_shift').value));
  
    var storage = {
        employees: [],
        activeFilters: {},
        departmentShifts: {},
        shifts: {
            off: {
                text: 'Off',
                colorCode: constants.OFF_COLOR_CODE
            }
        },
        shiftsForCopy: [],
        groupedRecords: []
    };

    // read and initialise the employees data from the dom
    (function() {
        /** @type {HTMLSelectElement} */
        var elem = document.getElementById('employees');

         var employees = elem.options;
        for (var i = 0; i < employees.length; i++) {
            var employee = employees[i];
            storage.employees[i] = {
                id: employee.value,
                name: employee.text,
                department: employee.dataset.department,
                isActive: employee.dataset.isActive,
                working_company: employee.dataset.working_company
            }
        }
    })();

    // read the shifts and initialise the select shift element template
    (function() {
        var elem = document.getElementById('shift_master');

        elem.querySelectorAll('[data-shift-row]')
            .forEach(function(rowEl) {
                var shiftId = rowEl.dataset.shiftId;
                var shift = {
                    text: rowEl.querySelector('[data-shift-code]').textContent,
                    colorCode: rowEl.querySelector('[data-shift-color]').textContent
                };

                storage.shifts[shiftId] = shift;
            })
    })();

    // get the department shifts and store
    $.ajax({
        url: filterFormElem.action,
        method: 'post',
        headers: {
            'Accept': 'application/json'
        },
        dataType: 'json',
        data: {
            method: 'getDepartmentShifts'
        }
    }).done(function (respJson, msg, xhr) {
        if (!respJson.data) {
            return defaultErrorHandler(xhr);
        }

        storage.departmentShifts = respJson.data;
        regenerateGroupShiftSelect();
    }).fail(defaultErrorHandler);

    // filter the employees when there is a change in department or activeness.
    (function() {
        var departmentElemId = 'department';
        var workingCompanyElemId = 'working_company_id';

        /** @type {HTMLSelectElement} */
        var employeesElem = document.getElementById('employees');
        /** @type {HTMLSelectElement} */
        var departmentElem = document.getElementById(departmentElemId);
        var workingCompanyElem = document.getElementById(workingCompanyElemId);
        
        // initialise the selects       
        $('#department, #working_company_id').select2();
        regenerateEmployees();
        
        // add the change listener
        $('#' + departmentElemId).on('change', regenerateEmployees);
        $('#' + departmentElemId).on('change', regenerateGroupShiftSelect);
        $('#' + workingCompanyElemId).on('change', regenerateEmployees);

        /**
         * Regenerates the employees HTMLSelectElement based
         * on the department and show_inactive values
         */
        function regenerateEmployees() {
            var department = departmentElem.value;
            var workingCompanyId = workingCompanyElem.value;
            
            // filter the employees as per the department and inactive status
            var filteredEmployees = storage.employees
                .filter(function(employee) {
                    return ((!department.length || employee.department === department) 
                        && (!workingCompanyId.length || employee.working_company === workingCompanyId)) 
                });
            
            // grab the currently selected employees
            var selectedEmployees = [];
            for (var i = 0; i < employeesElem.options.length; i++) {
                if (employeesElem.options[i].selected) {
                    selectedEmployees[selectedEmployees.length] = employeesElem.options[i].value;
                }
            }

            // prepare the dataSource for the select element
            var dataSource = filteredEmployees.map(function(employee) {
                return {
                    id: employee.id,
                    text: employee.name,
                    selected: selectedEmployees.indexOf(employee.id) !== -1
                }
            })
            
            if ($(employeesElem).hasClass('select2-hidden-accessible')) {
                $(employeesElem).select2('destroy');   
            }
            empty(employeesElem);
            $(employeesElem).select2({data: dataSource})
        }
    })();

    // initialise the date picker
    $('.input-daterange').datepicker();

    // Initialise the main form
    (function() {
        window.Parsley.addValidator('maxDays', {
            messages: {en: 'The date-period cannot exceed 31 days'},
            requirementType: 'integer',
            validate: function(_value, requirement) {
                var fromDate = $('#from').datepicker('getDate');
                var tillDate = $('#till').datepicker('getDate');
                return Math.abs(tillDate - fromDate) / (1000 * 60 * 60 * 24) <= requirement;
            }
        });

        var pslyForm = $(filterFormElem).parsley({
            errorClass: 'is-invalid',
            successClass: 'is-valid',
            errorsWrapper: '<ul class="errors-list"></ul>',
            classHandler: field => field.$element.closest('.form-group'),
            errorsContainer: field => field.$element.closest('.form-group'),
            inputs: Parsley.options.inputs + ',[data-parsley-max-days]'
        });

        pslyForm.on('form:submit', refreshShiftsTable);
    })();

    // handle the background color when the shift gets changed.
    (function() {
        $('#' + empShiftTableId).on(
            'change',
            '[data-shift-select]',
            function(evnt){
                var colorCode = this.selectedOptions[0].dataset.colorCode;
                var parentTdEl = $(this).closest('td.emp-shift')[0];
                if (colorCode && colorCode.length > 0) {
                    parentTdEl.style.backgroundColor = colorCode;
                } else {
                    parentTdEl.style.backgroundColor = '';
                }
            }
        )
    })();
    
    // Initialise employees shift form
    (function() {
        var pslyForm = $(shiftFormElem).parsley({
            errorClass: 'is-invalid',
            successClass: 'is-valid',
            errorsWrapper: '<ul class="errors-list"></ul>',
            classHandler: field => field.$element.closest('.form-group'),
            errorsContainer: field => field.$element.closest('.form-group')
        });

        pslyForm.on('form:submit', function() {
            setBusyState();

            var batchSize = 20;
            var promises = [];
            var data = {...storage.activeFilters};

            var collection = Array.from(shiftFormElem.querySelectorAll('tbody tr'));
            for (i = 0; i < collection.length; i += batchSize) {
                var shiftsBatch = collection.slice(i, i + batchSize)
                var _data = {...data};
                
                shiftsBatch.forEach(tr => {
                    tr.querySelectorAll('[name]').forEach(input => (_data[input.name] = input.value));
                });

                promises.push($.ajax({
                    method: shiftFormElem.method,
                    url: shiftFormElem.action,
                    headers: {
                        'Accept': 'application/json'
                    },
                    dataType: 'json',
                    data: _data
                }))
            }

            Promise.all(promises)
                .then(function(responses) {
                    var errorMessages = [];
                    
                    responses.forEach(res => {
                        if (!res.status || res.status != 200) {
                            errorMessages.push(res.message ? res.message : "Something went wrong! Please try again later")
                        }
                    });

                    refreshShiftsTable();

                    if (errorMessages.length) {
                        var messages = errorMessages
                            .filter((value, index, self) => self.indexOf(value) === index)
                            .join('<br>');

                        return toastr.error(messages);
                    }

                    else {
                        return toastr.success("Saved successfully")
                    }
                })
                .catch(() => toastr.error('Something went wrong! Please try again or contact the administrator'))
                .finally(unsetBusyState);
    
            return false;
        });
    })();

    // select_all checkboxes
    $('#emp_shifts_form').on('change', '#select-all', function (e) {
        if (this.checked) {
            $('#emp_shifts_form input[type=checkbox]').prop('checked', true);
        } else {
            $('#emp_shifts_form input[type=checkbox]').prop('checked', false);
        }
    });

    // when the dates get updated update the group filters also
    $('#from, #till').on('change', function() {
        var startDate = $('#from').datepicker('getDate');
        var endDate = $('#till').datepicker('getDate');

        $('#group_shift_from, #group_shift_till').each(function(i, el) {
            $(el).datepicker('setStartDate', startDate);
            $(el).datepicker('setEndDate', endDate);
        })

        $('#group_shift_from').datepicker('setDate', startDate);
        $('#group_shift_till').datepicker('setDate', endDate);
    })

    // select shift in group
    $('#assign_shift').on('click', function() {
        var selectedShift = $('#group_shift_select').val();
        var from = $('#group_shift_from').datepicker('getDate');
        var till = $('#group_shift_till').datepicker('getDate');
        var collection = $('#emp_shifts_tbl thead th[data-date]')

            .toArray()
            .filter(cell => {
                date = (new Date(cell.dataset.date + 'T00:00:00')).getTime();
                return date >= from.getTime() && date <= till.getTime()
            })
            .map(cell => cell.cellIndex);

        $('#emp_shifts_tbl tbody td:first-child input:checked').each((i, el) => {
            $(el).closest('tr').find('[data-shift-select]').each((i, shift) => {
                if (collection.indexOf(shift.closest('td').cellIndex) != -1) {
                    $(shift).val(selectedShift).trigger('change');
                }
            })
        })
    });

    $('#print_shifts').on('click', function() {
        const el = document.getElementById('emp_shifts_tbl');
        
        // Clone the shifts table
        const clone = el.cloneNode(true);

        // Set the shifts table text to nowrap
        clone.classList.add('text-nowrap');

        // Make the print content
        const body  = document.createElement('body');
        body.appendChild(clone);

        // Remove the check boxes
        body.querySelectorAll('[type="checkbox"]')
            .forEach(el => el.closest('td,th').remove());

        // Remove the select and replace it with label
        el.querySelectorAll('[data-shift-select]')
            .forEach(el => {
                const _el = body.querySelector(`[name="${el.name}"]`).closest('td');
                const classNames = el.className.replaceAll('custom-select', 'form-control')
                _el.style.setProperty('background-color', _el.style.backgroundColor, 'important');
                _el.innerHTML = `<span class="${classNames}">${el.options[el.selectedIndex].textContent}</span>`
            })

        const style = document.createElement('style');
        style.textContent = `
            @media print {
                table#emp_shifts_tbl th,
                table#emp_shifts_tbl td {
                    background-color: initial !important;
                    -webkit-print-color-adjust: exact;
                }
            }
        `;
        const head = document.head.cloneNode(true);
        head.appendChild(style);

        popup = window.open('', 'Print Shifts', `height=${screen.availHeight},width=${screen.availWidth}`);
        popup.document.write('<html>'.concat(head.outerHTML, body.outerHTML, '</html>'));
        popup.document.close();
        popup.onload = function() {
            popup.print();
            popup.onfocus = function() {
                setTimeout(() => popup.close(), 500);
            };
        };
    });

    const pslyCopyShiftForm = $('#copyShiftForm').parsley({
        errorClass: 'invalid',
        successClass: 'valid',
        errorsWrapper: '<ul class="errors-list"></ul>',
        classHandler: findClosestGroupWrapper,
        errorsContainer: findClosestGroupWrapper
    });

    pslyCopyShiftForm.on('form:submit', function () {
        ajaxRequest({
            method: 'POST',
            url: '',
            headers: {
                'Accept': 'application/json'
            },
            dataType: 'json',
            data: {
                ...storage.activeFilters,
                method: 'getShiftsForCopy',
                copy_from: $('#copy_from').val(),
                upto_weeks: $('#upto_weeks').val(),
            }
        }).done(function(resp, msg, xhr) {
            if (!resp.status || resp.status != 200) {
                return defaultErrorHandler(xhr);
            }

            storage.shiftsForCopy = Array.from(resp.data);
            generateCopyShiftsTable();
            document.getElementById('proceedToCopy').disabled = false;
        }).fail(defaultErrorHandler)

        return false;
    })

    pslyCopyShiftForm.$element.on('reset',  function() {
        pslyCopyShiftForm.reset();
        $('#copy_from').datepicker('update', '');
    })

    $('#copy_from').datepicker({
        beforeShowDay: function (date) {
            if ($('#copy_from').val() == moment(date).format(constants.MOMENT_JS_DATE_FORMAT)) {
                return undefined;
            }

            let fromDate = moment(storage.activeFilters.from, constants.MOMENT_JS_DATE_FORMAT);
            let tillDate = moment(storage.activeFilters.till, constants.MOMENT_JS_DATE_FORMAT);
            let included = moment(date).isBetween(fromDate, tillDate, 'days', '[]');

            return included ? 'selected-date' : '';
        },
    })

    $('#copy_shift').on('click', function () {
        if (!storage.activeFilters.from || !storage.activeFilters.till) {
            return;
        }

        pslyCopyShiftForm.element.reset();

        let fromDate = moment(storage.activeFilters.from, constants.MOMENT_JS_DATE_FORMAT);
        let tillDate = moment(storage.activeFilters.till, constants.MOMENT_JS_DATE_FORMAT);
        
        let copyDateEl = $('#copy_from');
        let uptoWeeksEl = document.getElementById('upto_weeks');
        let selectedDayOfWeek = fromDate.day();
        let disabledDaysOfWeek = Array(7).keys().toArray().filter(d => (d != selectedDayOfWeek));
        let noOfWeeksBetween = Math.ceil(tillDate.diff(fromDate, 'days') / 7);

        copyDateEl.datepicker('setDate', fromDate.toDate());
        copyDateEl.datepicker('setDaysOfWeekDisabled', disabledDaysOfWeek);

        let fragment = document.createDocumentFragment();
        fragment.appendChild(new Option('-- select --', '', true, true));
        for (i = 1; i <= noOfWeeksBetween; i++) {
            fragment.appendChild(new Option(`${i} ${i == 1 ? 'Week' : 'Weeks'}`, i));
        }
        empty(uptoWeeksEl);
        uptoWeeksEl.appendChild(fragment);

        // Clear the table
        empty(document.getElementById('copyShiftsTable'));

        // Disable the proceed to copy button
        document.getElementById('proceedToCopy').disabled = true;

        // Clear the previous copy data
        storage.shiftsForCopy = [];

        $('#copyShiftModal').modal('show');
    });

    $('#proceedToCopy').on('click', function () {
        if (!hasActiveShifts(storage.shiftsForCopy)) {
            toastr.error("There are no shifts to copy");
            return false;
        }

        let lastIndexToCopyFrom = storage.shiftsForCopy[0].length - 1;
        let lastIndexToCopyTo = storage.groupedRecords[0].length - 1;
        let indexToCopyUpto = Math.min(lastIndexToCopyFrom, lastIndexToCopyTo);

        if (hasActiveShifts(
            storage.groupedRecords,
            storage.groupedRecords[0][indexToCopyUpto].date
        )) {
            Swal.fire({
                title: "!! Action Required; Shifts Exists !!",
                text: "Some of the employees have shift assigned to them in the"
                    + " selected date period. Do you want to override them ?",
                icon: "warning",
                customClass: 'w-650px min-h-400px',
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, Override existing shifts!",
                cancelButtonText: "No, Only update shifts that are not assigned"
            }).then((result) => {
                proceedToCopy(!!result.value);
            });

            return;
        }

        proceedToCopy();
    });

    /**
     * Proceed to copy the shifts
     * 
     * @param {boolean} shouldOverride 
     */
    function proceedToCopy(shouldOverride = false) {
        let shiftTable = document.getElementById(empShiftTableId);
        let dateCellIndex = shiftTable.querySelector('thead th[data-date]').cellIndex;

        storage.shiftsForCopy.forEach(records => {
            let tr = shiftTable.querySelector(`tr[data-employee-id="${records[0].employee_id}"]`);

            records.forEach((record, i) => {
                if (!record.is_shift_defined) {
                    return;
                }

                let td = tr.cells[dateCellIndex + i];

                if (!shouldOverride && (+td.dataset.isShiftDefined)) {
                    return;
                }

                $(td.querySelector('select[name^="shifts"]'))
                    .val(record.shift_id || 'off')
                    .trigger('change');
            })
        })

        $('#copyShiftModal').modal('hide');
    }

    /**
     * Generate the shifts table for copy
     */
    function generateCopyShiftsTable() {
        /** @type {HTMLTableElement} */
        let copyShiftsTable = document.getElementById('copyShiftsTable');

        empty(copyShiftsTable);

        if (!storage.shiftsForCopy.length) {
            return;
        }

        let tableHead = ((records) => {
            let thead = document.createElement('thead');
            let tr = document.createElement('tr');

            let th = document.createElement('th');
             th.textContent = 'Employee Name';
             th.style.minWidth = '250px';
             tr.appendChild(th);

            records.forEach(function (record) {
                let $th = $(document.createElement('th'));
                
                $th[0].dataset.date = record.date;
                $th.text(record.formatted_date);
                if (defaultWeekends.indexOf(record.day_of_week) != -1) {
                    $th.addClass('weekend');
                }

                tr.appendChild($th[0]);
            })
            thead.appendChild(tr);

            return thead;
        })(storage.shiftsForCopy[0]);

        let tableBody = (groupedRecords => {
            let tbody = document.createElement('tbody');

            groupedRecords.forEach(function(records) {
                let firstRecord = records[0];
                let tr = document.createElement('tr');
                tr.dataset.employeeId = firstRecord.employee_id;

                let td = document.createElement('td');
                td.textContent = firstRecord.employee_ref + ' ' + firstRecord.employee_name;
                tr.appendChild(td);

                records.forEach(function(record) {
                    let td = document.createElement('td');
                    td.classList.add('emp-shift');

                     // If the employee is not yet joined continue on to next day
                    if (!record.is_employee_joined || !record.emp_shift_id) {
                        return tr.appendChild(td);
                    }

                    td.textContent = record.shift_state;
                    
                    let colorCode = storage.shifts[record.custom_shift_id].colorCode;
                    if (colorCode && colorCode.length > 0) {
                        td.style.backgroundColor = colorCode;
                        td.style.color = getTextColor(colorCode);
                    }

                    tr.appendChild(td);
                })
                tbody.appendChild(tr);
            })

            return tbody;
        })(storage.shiftsForCopy);

        copyShiftsTable.appendChild(tableHead);
        copyShiftsTable.appendChild(tableBody);
    }

    /**
     * Checks if the records have any active assigned shifts
     * 
     * @param {Array} groupedWorkRecords
     * @param {string} uptoDate A cutoff date till which needs to be checked in the ISO standard format
     * @returns {boolean}
     */
    function hasActiveShifts(groupedWorkRecords, uptoDate = null) {
        return groupedWorkRecords.some(
            workRecords => workRecords.some(
                record => (record.is_shift_defined && (!uptoDate || record.date <= uptoDate))
            )
        );
    }

    var findClosestGroupWrapper = function(field) {
        return field.$element.closest('div[class^="col"], div[class*=" col"]');
    }
    
    /**
     * Refresh the empShifts table
     * @return {false}
     */
    function refreshShiftsTable() {
        var $form = $(filterFormElem);
        setBusyState();
        $.ajax({
            method: $form.prop('method'),
            url: $form.prop('action'),
            headers: {
                'Accept': 'application/json'
            },
            dataType: 'json',
            data: $form.serialize()
        }).done(function(res) {
            try {
                let groupedWorkRecords = Array.from(res.data);
                storage.activeFilters = res.filters;
                storage.groupedRecords = groupedWorkRecords;
                generateTable(
                    groupedWorkRecords,
                    Array.from(res.department_shifts).map(function(depShift) { return depShift.shift_id })
                );
                document.getElementById(saveShiftBtnId).disabled = false;
                document.getElementById('copy_shift').disabled = false;
            } catch (e) {
                // something serious
                toastr.error('Something went wrong! Please contact the administrator')
            }
        }).fail(function(xhr) {
            toastr.error('Something went wrong! Please try again or contact the administrator')
        }).always(function() {
            setBusyState(false);
        })

        return false;
    }

    /**
     * Generates the empShifts table
     * @param {Array} groupedWorkRecords
     * @param {Array} departmentShifts
     */
     function generateTable(groupedWorkRecords, departmentShifts) {
        /** @type {HTMLTableElement} */
        var empShiftTableEl = document.getElementById(empShiftTableId);
        
        empty(empShiftTableEl);

        if (!groupedWorkRecords.length) {
            return;
        }

        empShiftTableEl.appendChild(generateHead(groupedWorkRecords[0]));
        empShiftTableEl.appendChild(generateBody(groupedWorkRecords, departmentShifts));

        /**
         * Generates the table's head section
         * @param {Array} workRecords A sample data of one employee
         * @returns {HTMLTableSectionElement} Generated head section
         */
        function generateHead(workRecords) {
            var thead = document.createElement('thead');
            var tr = document.createElement('tr');
            
            var th = document.createElement('th');
            var checkbox = document.createElement('input');
            checkbox.type = "checkbox";
            checkbox.id = 'select-all';
            th.append(checkbox);
            tr.appendChild(th);

            var th = document.createElement('th');
             th.textContent = 'Employee Name';
             th.style.minWidth = '250px';
             tr.appendChild(th);

            workRecords.forEach(function (workRecord) {
                var $th = $(document.createElement('th'));
                
                $th[0].dataset.date = workRecord.date;
                $th.text(workRecord.formatted_date);
                if (defaultWeekends.indexOf(workRecord.day_of_week) != -1) {
                    $th.addClass('weekend');
                }

                tr.appendChild($th[0]);
            })
            thead.appendChild(tr);

            return thead;
        }

        /**
         * Generates the table's body section
         * @param {Array} groupedWorkRecords
         * @param {Array} departmentShifts
         * @returns {HTMLTableSectionElement} Generated body section
         */
        function generateBody(groupedWorkRecords, departmentShifts) {
            var tbody = document.createElement('tbody');
            var selectShiftElTemplate = getShiftElementTemplate(departmentShifts);

            groupedWorkRecords.forEach(function(workRecords) {
                var firstRecord = workRecords[0];
                var tr = document.createElement('tr');
                tr.dataset.employeeId = firstRecord.employee_id;
                
                var td = document.createElement('td');
                var checkbox = document.createElement('input');
                checkbox.type = "checkbox";
                checkbox.classList.add('checkbox') ;
                td.append(checkbox);
                tr.appendChild(td);

                var td = document.createElement('td');
                td.textContent = firstRecord.employee_name;
                tr.appendChild(td);

                workRecords.forEach(function(workRecord) {
                    var td = document.createElement('td');
                    td.classList.add('emp-shift');

                    td.dataset.isShiftDefined = (+workRecord.is_shift_defined);

                     // If the employee is not yet joined continue on to next day
                    if (!workRecord.is_employee_joined) {
                        return tr.appendChild(td);
                    }

                    var formGroup = document.createElement('div');
                    formGroup.classList.add('form-group', 'm-0');
                    
                    if (!workRecord.is_shift_defined || canEditShift) {
                        /** @type {HTMLSelectElement} */
                        var control = selectShiftElTemplate.cloneNode(true);
                        control.name = `shifts[${workRecord.employee_id}][${workRecord.date}]`;
                        if (!workRecord.is_shift_defined) {
                            control.classList.add('border-accent', 'border-dashed');
                        }
                        for (var i = 0; i < control.options.length; i++) {
                            var option = control.options[i];
                            if (option.value == workRecord.custom_shift_id) {
                                var colorCode = option.dataset.colorCode;
                                option.selected = true;
                            }
                        }
                    } else {
                        var control = getReadOnlyShift(workRecord);
                        var colorCode = control.dataset.colorCode;
                    }

                    if (colorCode && colorCode.length > 0) {
                        td.style.backgroundColor = colorCode;
                    }
                    formGroup.appendChild(control);
                    td.appendChild(formGroup);

                    tr.appendChild(td);
                })
                tbody.appendChild(tr);
            })

            return tbody;
        }
      
        function getReadOnlyShift(workRecord) {
            var shiftId = workRecord.custom_shift_id;

            var container = document.createElement('span');
            container.classList.add('custom-select', 'custom-select-sm', 'readonly-shift');
            container.dataset.text = storage.shifts[shiftId].text;
            container.dataset.colorCode = storage.shifts[shiftId].colorCode;

            var input = document.createElement('input');
            input.readOnly = true;
            input.hidden = true;
            input.value = shiftId;
            input.name = `shifts[${workRecord.employee_id}][${workRecord.date}]`;

            container.appendChild(input);

            return container;
        }
    }

    function regenerateGroupShiftSelect() {
        const departmentId = document.getElementById('department').value;
        const groupShiftEl = document.getElementById('group_shift_select');
        const departmentShifts = storage.departmentShifts[departmentId] || [];
        const shiftElTemplate = getShiftElementTemplate(departmentShifts.map(item => item.shift_id));

        empty(groupShiftEl);
        groupShiftEl.append(...shiftElTemplate.options);
        groupShiftEl.value = '';
    }

    /**
     * Retreives the shift element template
     * @param {Array} shifts
     * @return {HTMLSelectElement}
     */
    function getShiftElementTemplate(shifts) {
        var selectShiftElTemplate = document.createElement('select');
        selectShiftElTemplate.classList.add('custom-select', 'custom-select-sm')
        selectShiftElTemplate.dataset.shiftSelect = '1';
        selectShiftElTemplate.required = true;

        // build the select options.
        var shiftOptionsCollection = document.createDocumentFragment();
        shiftOptionsCollection.appendChild(new Option('-- select --', ''));

        var offOption = new Option('Off', 'off');
        offOption.dataset.colorCode = constants.OFF_COLOR_CODE;
        shiftOptionsCollection.appendChild(offOption);

        shifts.forEach(function(shiftId) {
            shift = storage.shifts[shiftId];

            var optionEl = new Option(shift.text, shiftId);
            optionEl.dataset.colorCode = shift.colorCode;

            shiftOptionsCollection.appendChild(optionEl);
        });

        selectShiftElTemplate.appendChild(shiftOptionsCollection);

        return selectShiftElTemplate;
    }
});