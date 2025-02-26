$(function() {
    /** @type {HTMLFormElement} */
    var filterFormElem = document.getElementById('filter_form');
    var timesheetTableId = 'timesheet_tbl';
    var exportBtnId = 'export_timesheet';
    var syncAttendanceBtnId = 'sync';
    var defaultWeekendsElId = 'default_weekends';
    var storage = {
        employees: [],
        timesheet: {},
        activeFilters: null
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
                working_company: employee.dataset.working_company,
                isActive: employee.dataset.isActive
            }
        }
    })();

    // filter the employees when there is a change in department or activeness.
    (function() {
        var showInactiveElemId = 'show_inactive';
        var departmentElemId = 'department';
        var workingCompanyElemId = 'working_company_id';

        /** @type {HTMLSelectElement} */
        var employeesElem = document.getElementById('employees');
        /** @type {HTMLInputElement} */
        var showInactiveElem = document.getElementById(showInactiveElemId);
        /** @type {HTMLSelectElement} */
        var departmentElem = document.getElementById(departmentElemId);
        var workingCompanyElem = document.getElementById(workingCompanyElemId);
        
        // initialise the selects   
        $('#department, #working_company_id').select2();
        regenerateEmployees();
        // add the change listener
        $(`#${showInactiveElemId}, #${departmentElemId}`)
            .on('change', regenerateEmployees);
        $(`#${workingCompanyElemId}`).on('change', regenerateEmployees);

        /**
         * Regenerates the employees HTMLSelectElement based
         * on the department and show_inactive values
         */
        function regenerateEmployees() {
            var department = departmentElem.value;
            var showInactive = showInactiveElem.checked;
            var workingCompanyId = workingCompanyElem.value;
            
            
            // filter the employees as per the department and inactive status
            var filteredEmployees = storage.employees
                .filter(function(employee) {
                    return ((!department.length || employee.department === department)
                        && (!workingCompanyId.length || employee.working_company === workingCompanyId)
                        && (showInactive || employee.isActive === '1'))
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
    $('#daterange_picker').datepicker();

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

        window.Parsley.addValidator('requiredIf', {
            messages: {en: 'This value is required'},
            requirementType: 'string',
            validate: function(_value, requirement) {
                var formEl = document.getElementById(requirement);

                return !formEl.value.length || _value.length != 0;
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

        pslyForm.on('form:submit', refreshTimesheetTable);
        
        // handle the export button
        $('#' + exportBtnId).on('click', function() {
            if (storage.activeFilters) {
                exportTimesheet(storage.activeFilters);
            } else {
                if (pslyForm.isValid({force: true})) {
                    exportTimesheet(pslyForm.$element.serialize());
                } else {
                    pslyForm.validate({force: true});
                }
            }
        });

        // handle the syncronize button
        $('#' + syncAttendanceBtnId).on('click', function() {
            if (storage.activeFilters) {
                syncAttendance(storage.activeFilters);
            } else {
                if (pslyForm.isValid({force: true})) {
                    syncAttendance(pslyForm.$element.serialize());
                } else {
                    pslyForm.validate({force: true});
                }
            }
        });
    })();

    // Handle when the show/hide the punchin out button is clicked
    $('#show_punchinouts').on('change', function() {
        this.checked 
            ? $('#' + timesheetTableId).removeClass('hidden-punches')
            : $('#' + timesheetTableId).addClass('hidden-punches')
    });

    // Initialise the Update attendance controls
    (function() {
        var $updateAttendanceModal = $('#update_attendance_modal');
        if ($updateAttendanceModal.length) {
            /** @type {JQuery<HTMLFormElement>} */
            var $updateAttendanceForm = $('#update_attendace_form');

            $('#' + timesheetTableId).on('click', '.updatable .clickable', function() {
                var $closestTd = $(this).closest('td.emp-workday');
                var inputs = $updateAttendanceForm[0].elements;
                var workRecord = storage.timesheet[$closestTd[0].id];

                [
                    'employee_id',
                    'date',
                    'duty_status',
                    'punchin',
                    'punchout',
                    'punchin2',
                    'punchout2',
                    'attendance_remarks'
                ].forEach(function(_k) {
                    $(inputs[_k]).val(workRecord[_k]);
                })

                $updateAttendanceModal.modal('show');
            })
            
            window.Parsley.addValidator('requiredOnSelect', {
                messages: {en: 'This value is required'},
                validate: function(value, requirements) {
                    requirements = requirements.split(',');
                    var elemName = requirements[0];
                    var requirement = requirements[1];

                    if (requirement == $('select[name="' + elemName + '"]').val() && '' == value) {
                        return false;
                    }
                    return true;
                }
            })

            var pslyForm = $updateAttendanceForm.parsley({
                errorClass: 'is-invalid',
                successClass: 'is-valid',
                errorsWrapper: '<ul class="errors-list"></ul>',
                classHandler: field => field.$element.closest('.form-group')
            })

            pslyForm.on('form:submit', function() {
                ajaxRequest({
                    method: $updateAttendanceForm.prop('method'),
                    url: $updateAttendanceForm.prop('action'),
                    data: $updateAttendanceForm.serialize()
                }).done(res => {
                    $updateAttendanceModal.modal('hide');
                    refreshTimesheetTable();
                    Swal.fire("Success!", res.message, "success");
                }).fail(defaultErrorHandler);

                return false;
            });

            $updateAttendanceModal.on('hide.bs.modal', function() {
                pslyForm.element.reset();
                pslyForm.reset();
            })
        }
    })();

    /**
     * Exports the timesheet
     * 
     * @param {String} filters A prebuilt query string for the filters
     * @returns {void}
     */
    function exportTimesheet(filters) {
        var data = filters.length
            ? filters + '&export_timesheet='
            : 'export_timesheet=';

        setBusyState();
        $.ajax({
            method: filterFormElem.method,
            url: filterFormElem.action,
            data: data
        }).done(function(res) {
            try {
                var doc = (new DOMParser()).parseFromString(res, 'text/html');
                window.location = doc.body.querySelector('a[href]').href;
            } catch (e) {
                toastr.error("Something went wrong! Please try again later or contact the administrator");
            }
        }).fail(function(xhr) {
            toastr.error("Something went wrong! Please try again later or contact the administrator");
        }).always(function() {
            setBusyState(false);
        });
    }

    /**
     * Syncronize the attendance
     * 
     * @param {String} filters A prebuilt query string for the filters
     * @returns {void}
     */
     function syncAttendance(filters) {
        var data = filters.length
            ? filters + '&sync='
            : 'sync=';

        setBusyState();
        $.ajax({
            method: filterFormElem.method,
            url: filterFormElem.action,
            data: data,
            dataType: 'json'
        }).done(function(res) {
            if (res.status && res.status == 204) {
                refreshTimesheetTable(); 
            } else {
                toastr.error("Something went wrong! Please try again later or contact the administrator")
            }
        }).fail(function(xhr) {
            toastr.error("Something went wrong! Please try again later or contact the administrator");
        }).always(function() {
            setBusyState(false);
        });
    }

    /**
     * Refresh the Timesheet table
     * @return {false}
     */
    function refreshTimesheetTable() {
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
                storage.timesheet = {};
                storage.activeFilters = res.filters;
                generateTable(Array.from(res.data));
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
     * Generates the timesheet table
     * @param {Array} timesheet
     */
     function generateTable(timesheet) {
        /** @type {HTMLTableElement} */
        var timesheetTable = document.getElementById(timesheetTableId);
        
        empty(timesheetTable);

        if (!timesheet.length) {
            return;
        }

        timesheetTable.appendChild(generateHead(timesheet[0]));
        timesheetTable.appendChild(generateBody(timesheet));

        /**
         * Generates the table's head section
         * @param {Array} workRecords A sample data of one employee
         * @returns {HTMLTableSectionElement} Generated head section
         */
        function generateHead(workRecords) {
            var thead = document.createElement('thead');
            var tr = document.createElement('tr');

            var th = document.createElement('th');
            th.textContent = 'Employee Name';
            th.style.minWidth = '250px';
            tr.appendChild(th);

            var defaultWeekends = document.getElementById(defaultWeekendsElId).value.split(',');

            workRecords.forEach(function (record) {
                var $th = $(document.createElement('th'));
                $th.text(record.formatted_date);
                defaultWeekends.indexOf(record.day_name) != -1 && $th.addClass('weekend');
                tr.appendChild($th[0]);
            })
            thead.appendChild(tr);

            return thead;
        }

        /**
         * Generates the table's body section
         * @param {Array} timesheet The full timesheet data
         * @returns {HTMLTableSectionElement} Generated body section
         */
        function generateBody(timesheet) {
            var tbody = document.createElement('tbody');

            timesheet.forEach(function(workRecords) {
                var firstRecord = workRecords[0];
                var tr = document.createElement('tr');

                var td = document.createElement('td');
                td.textContent = firstRecord.employee_name;
                tr.appendChild(td);

                workRecords.forEach(function(record) {
                    storage.timesheet[record.custom_id] = record;
                    var $td = $(document.createElement('td'));

                    $td.addClass('emp-workday');
                    if (record.is_updatable) {
                        $td.addClass('updatable');
                    }
                    if (record.attendance_reviewed_at) {
                        $td.addClass('reviewed');
                    }
                    $td.prop("id", record.custom_id);

                    if (record.is_employee_joined) {
                        switch(record.duty_status) {
                            case 'present':
                                if (!record.is_missing_punch && record.punchin && record.punchout) {
                                    add_badge($td[0], 'DURATION : ' + record.formatted_work_duration, 'badge-duration clickable');
                                    add_badge($td[0], 'IN : ' + record.formatted_punchin, 'punch badge-punchin clickable');
                                    add_badge($td[0], 'OUT : ' + record.formatted_punchout, 'punch badge-punchout clickable');
                                } else if (record.is_missing_punch) {
                                    add_badge($td[0], 'Missing Punch', 'badge-missed-punch clickable');
                                    record.punchin && add_badge($td[0], 'IN : ' + record.formatted_punchin, 'punch badge-missed-punch clickable');
                                    record.punchout && add_badge($td[0], 'OUT : ' + record.formatted_punchout, 'punch badge-missed-punch clickable');
                                }

                                if (!record.is_missing_punch2 && record.punchin2 && record.punchout2) {
                                    add_badge($td[0], 'DURATION2 : ' + record.formatted_work_duration2, 'badge-duration clickable');
                                    add_badge($td[0], 'IN2 : ' + record.formatted_punchin2, 'punch badge-punchin clickable');
                                    add_badge($td[0], 'OUT2 : ' + record.formatted_punchout2, 'punch badge-punchout clickable');
                                } else if (record.is_missing_punch2) {
                                    add_badge($td[0], 'Missing Punch2', 'badge-missed-punch clickable');
                                    record.punchin2 && add_badge($td[0], 'IN2 : ' + record.formatted_punchin2, 'punch badge-missed-punch clickable');
                                    record.punchout2 && add_badge($td[0], 'OUT2 : ' + record.formatted_punchout2, 'punch badge-missed-punch clickable');
                                }
                                break;
                            
                            case 'not_present':
                                add_badge($td[0], 'ABSENT', 'badge-not-present clickable');
                                break;

                            case 'holiday':
                                add_detail($td[0], record.holiday_name, 'public-holiday');
                                break;

                            case 'off':
                                add_badge($td[0], 'OFF', 'badge-off');
                                break;
                        }

                        if (['present', 'not_present'].indexOf(record.duty_status) != -1) {
                            add_detail(
                                $td[0],
                                'Shift: ' + record.shift_state,
                                'emp-shift',
                                record.shift_desc ? record.shift_desc : ''
                            );
                        } else if (record.is_on_leave) {
                            add_detail(
                                $td[0],
                                'On ' + record.leave_type,
                                'on-leave',
                                ''
                            );
                        }
                    }

                    tr.appendChild($td[0]);
                })
                tbody.appendChild(tr);
            })

            return tbody;

            /**
             * Adds a new badge to the td element.
             * 
             * @param {HTMLTableCellElement} td The HTML td element where the badge needs to be added
             * @param {String} text The Text to be displayed for the badge
             * @param {String} cl Additional classes to be added
             */
            function add_badge(td, text, cl) {
                cl = 'badge ' + cl;
                add_detail(td, text, cl, '');
            }

            /**
             * Adds a new details to the td element.
             * 
             * @param {HTMLTableCellElement} td The HTML td element where the badge needs to be added
             * @param {String} text The Text to be displayed for the badge
             * @param {String} cl Additional classes to be added
             * @param {String} title Title if any
             */
            function add_detail(td, text, cl, title) {
                var detail = document.createElement('span');
                $(detail).addClass(cl);
                detail.textContent = text;
                detail.title = title;
                td.appendChild(detail);
            }
        }
    }
});