$(function() {
    var storage = {
        employees: [],
        departments: {},
        companies: {},
        activeFilters: '',
        attendanceMetrics: {},
        editing: null,
        reviewStatus: {}
    };
    
    /** @type {HTMLFormElement} */
    var filterFormElem = document.getElementById('filter_form');
    var departmentElemId = 'department_id';
    var workingCompanyElemId = 'working_company_id';
    var showInactiveElemId = 'show_inactive';
    var dataTableElemId = 'emp_attd_metrics_tbl';
    var employeeElemId = 'employee';
    var numberFormatter = new Intl.NumberFormat('en-US', {maximumFractionDigits: 2});
    var dateFormatter = new Intl.DateTimeFormat('en-US', {weekday: 'short', month: 'short', day: 'numeric'});
    var dateTimeFormatter = new Intl.DateTimeFormat('en-US', {
        weekday: 'short',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: 'numeric',
        second: 'numeric'
    });

    // read and initialise the employees data from the dom
    (function() {
        /** @type {HTMLSelectElement} */
        var elem = document.getElementById(employeeElemId);

        var employees = elem.options;
        for (var i = 0; i < employees.length; i++) {
            var employee = employees[i];
            storage.employees[i] = {
                id: employee.value,
                name: employee.text,
                departmentId: employee.dataset.departmentId,
                workingCompanyId: employee.dataset.workingCompanyId,
                isActive: employee.dataset.isActive
            }
        }
    })();

    // read and initialize the department data from the dom
    (function () {
        /** @type {HTMLSelectElement} */
        var elem = document.getElementById(departmentElemId);

        var departments = elem.options;
        for (var i = 0; i < departments.length; i++) {
            var department = departments[i];
            storage.departments[department.value] = department.text;
        }
    })();
    
    // read and initialize the company data from the dom
    (function () {
        /** @type {HTMLSelectElement} */
        var elem = document.getElementById(workingCompanyElemId);

        var companies = elem.options;
        for (var i = 0; i < companies.length; i++) {
            var company = companies[i];
            storage.companies[company.value] = company.text;
        }
    })();

    // initialise the dataTable
    var $empAttdMetricsTbl = $('#' + dataTableElemId).DataTable({
        dom: 'lfBr<"table-responsive"t>ip',
        ajax: getAttendanceMetricsForDataTable,
        buttons: [
            'copy', 'csv', 'excel'
        ],
        rowId: 'id',
        columns: [
            {
                name: 'id',
                data: 'id',
                visible: false,
                orderable: false,
                searchable: false,
            },
            {
                name: 'workingCompanyName',
                data: 'workingCompanyName',
                width: '10%'
            },
            {
                name: 'departmentName',
                data: 'departmentName',
                width: '10%'
            },
            {
                name: 'name',
                data: 'name',
                width: '30%'
            },
            {
                name: 'date',
                data: null,
                render: {
                    _: 'date',
                    display: function (data, type, row, meta) {
                        return dateFormatter.format(new Date(row.date));
                    }
                }
            },
            {name: 'type', data: 'type'},
            {
                name: 'minutes',
                data: 'minutes',
                className: 'text-right'
            },
            {
                name: 'amount',
                data: null,
                className: 'text-right',
                width: '10%',
                render: {
                    _: 'amount',
                    display: function (data, type, row, meta) {
                        return row.id === storage.editing
                            ? '<input class="form-control" type="number" name="amount" value="' + row.amount + '">'
                            : numberFormatter.format(row.amount);
                    }
                }
            },
            {
                name: 'status',
                data: null,
                width: '10%',
                render: {
                    _: 'status',
                    display: function (data, type, row, meta) {
                        return row.id === storage.editing
                            ? selectStatus(row.statusId)
                            : row.status
                    }
                }
            },
            {
                name: 'reviewedBy',
                data: 'reviewedBy',
                defaultContent: 'System'
            },
            {
                name: 'reviewedAt',
                data: null,
                defaultContent: '',
                render: {
                    _: 'reviewedAt',
                    display: function (data, type, row, meta) {
                        return row.reviewedAt
                            ? dateTimeFormatter.format(new Date(row.reviewedAt))
                            : null;
                    }
                }
            },
            {
                name: 'action',
                data: null,
                defaultContent: '',
                className: 'text-nowrap',
                orderable: false,
                searchable: false,
                render: {
                    display: function (data, type, row, meta) {
                        if (row.updatable) {
                            return row.id === storage.editing
                                ? (
                                        '<button class="btn text-success" type="button" data-role="save" title="save"><span class="la la-save"></span></button>'
                                    +   '<button class="btn text-warning" type="button" data-role="cancel" title="cancel"><span class="la la-ban"></span></button>'
                                ) : (
                                        '<button class="btn text-primary" type="button" data-role="edit" title="modify"><span class="la la-edit"></span></button>'
                                    +   '<button class="btn text-danger" type="button" data-role="ignore" title="ignore"><span class="la la-times"></span></button>'
                                );
                        } else {
                            return '';
                        }
                    }
                }
            },
        ],
        order: [[3, 'desc']]
    });

    // handler for editing a metric
    $('#' + dataTableElemId).on('click', '[data-role="edit"]', function() {
        var _this = this;
        haveStatusOptions()
            .then(function () {
                var metricId = $(_this).closest('tr').prop('id');
                storage.editing = metricId;
                $empAttdMetricsTbl.ajax.reload(undefined, false);
            })
    });

    // handler for ignoring a metric
    $('#' + dataTableElemId).on('click', '[data-role="ignore"]', function() {
        updateMetric(
            $(this).closest('tr').prop('id'),
            'I',
            null
        )
    });

    // handler for cancelin the current edit
    $('#' + dataTableElemId).on('click', '[data-role="cancel"]', function() {
        storage.editing = null;
        $empAttdMetricsTbl.ajax.reload(undefined, false);
    });

    // handler for saving the current modification to metric
    $('#' + dataTableElemId).on('click', '[data-role="save"]', function() {
        var $row = $(this).closest('tr')
        var status = $row.find('[name="status"]').val();
        var amount = $row.find('[name="amount"]').val();

        updateMetric(
            $row.prop('id'),
            status,
            amount
        );
    });

    // filter the employees when there is a change in department or working company or activeness.
    (function() {
        /** @type {HTMLSelectElement} */
        var employeesElem = document.getElementById(employeeElemId);
        /** @type {HTMLSelectElement} */
        var departmentElem = document.getElementById(departmentElemId);
        /** @type {HTMLSelectElement} */
        var workingCompanyElem = document.getElementById(workingCompanyElemId);
        /** @type {HTMLInputElement} */
        var showInactiveElem = document.getElementById(showInactiveElemId);
        
        // Initialize the selects
        $(departmentElem).select2()
        regenerateEmployees();
        
        // add the change listener
        $(`#${departmentElemId}, #${showInactiveElemId}, #${workingCompanyElemId}`).on('change', regenerateEmployees);

        /**
         * Regenerates the employees HTMLSelectElement based
         * on the department and show_inactive as working company values
         */
        function regenerateEmployees() {
            var departmentId = departmentElem.value;
            var showInactive = showInactiveElem.checked;
            var workingCompanyId = workingCompanyElem.value;
            
            // filter the employees as per the department and inactive and working company status
            var filteredEmployees = storage.employees
                .filter(function(employee) {
                    return (!departmentId.length || employee.departmentId === departmentId)
                        && (showInactive || employee.isActive === '1')
                        && (!workingCompanyId.length || employee.workingCompanyId === workingCompanyId)
                });

            // prepare the dataSource for the select element
            var dataSource = [{
                id: '',
                text: '-- select employee --',
            }].concat(filteredEmployees.map(function(employee) {
                return {
                    id: employee.id,
                    text: employee.name,
                }
            }));
            
            if ($(employeesElem).hasClass('select2-hidden-accessible')) {
                $(employeesElem).select2('destroy');   
            }
            empty(employeesElem);
            $(employeesElem).select2({
                data: dataSource,
                allowClear: true
            })
        }
    })();

    // initialise the date picker
    $('#daterange_picker').datepicker();

    // initialise the select2
    $('#emplooyee').select2();

    // Initialise the main form
    (function() {
        window.Parsley.addValidator('maxDaysIfSelectedAll', {
            messages: {en: 'The date-period cannot exceed 31 days'},
            requirementType: 'string',
            validate: function(_value, requirement) {
                var requirement = requirement.split(',');
                var selected = document.getElementById(requirement[0]).value;
                var maxDays = requirement[1];

                var fromDate = $('#from').datepicker('getDate');
                var tillDate = $('#till').datepicker('getDate');
                return !!selected.length || (!!fromDate && !!tillDate && Math.abs(tillDate - fromDate) / (1000 * 60 * 60 * 24) <= maxDays) ;
            }
        });

        var pslyForm = $(filterFormElem).parsley({
            errorClass: 'is-invalid',
            successClass: 'is-valid',
            errorsWrapper: '<ul class="errors-list"></ul>',
            classHandler: field => field.$element.closest('.form-group'),
            errorsContainer: field => field.$element.closest('.form-group'),
            inputs: Parsley.options.inputs + ',[data-parsley-max-days-if-selected-all]'
        });

        refreshAttendanceMetricsTable();
        pslyForm.on('form:submit', refreshAttendanceMetricsTable);

        // Validate date period when employee changes
        $('#employee').on('change', function() {
            var pslyDateRange = $('[data-parsley-max-days-if-selected-all]').parsley();
            if (pslyDateRange._failedOnce) {
                pslyDateRange.validate();
            }
        });
    })();

    /**
     * Refresh the Attendance Metrics table
     * @return {false}
     */
    function refreshAttendanceMetricsTable() {
        setBusyState();
        $.ajax({
            method: filterFormElem.method,
            url: filterFormElem.action,
            headers: {
                'Accept': 'application/json'
            },
            dataType: 'json',
            data: new FormData(filterFormElem),
            processData: false,
            contentType: false
        }).done(function(res) {
            storage.attendanceMetrics = res.data.length === 0 ? {} : res.data;
            storage.activeFilters = res.filters;
            $empAttdMetricsTbl.ajax.reload();
        }).fail(function(xhr) {
            toastr.error('Something went wrong! Please try again or contact the administrator')
        }).always(function() {
            setBusyState(false);
        })

        return false;
    }

    /**
     * Retrieves the data for data table
     * 
     * @param {Object} data The data that needs to be sent to the servere
     * @param {CallableFunction} callback The callback that accepts the data for datatable
     * @param {Object} settings The settings object for dataTable
     */
    function getAttendanceMetricsForDataTable(data, callback, settings) {
        var _data = [];

        for (var employeeId in storage.attendanceMetrics) {
            var employee = storage.employees.find(function (e) {
                return e.id == employeeId
            })
            var departmentName = storage.departments[employee.departmentId];
            var workingCompanyName = storage.companies[employee.workingCompanyId];
            var metrics = storage.attendanceMetrics[employeeId];

            for (var i in metrics) {
                var metric = metrics[i];
                _data[_data.length] = {
                    id: metric.id,
                    name: employee.name,
                    workingCompanyName: workingCompanyName,
                    departmentName: departmentName,
                    date: metric.date,
                    type: metric._type,
                    status: metric._status,
                    minutes: metric.minutes,
                    amount: metric.amount,
                    statusId: metric.status,
                    updatable: metric._updatable,
                    reviewedBy: metric.reviewer,
                    reviewedAt: metric.reviewed_at
                }
            }
        }

        callback({"data" : _data});
    }

    /**
     * Returns the selectable status for updating
     * 
     * @param {string} selectedStatusId 
     * @returns 
     */
    function selectStatus(selectedStatusId) {
        var div = $(
                '<div>'
            +       '<select class="custom-select" name="status">'
            +           '<option value="">-- select status --</option>'
            +       '</select>'
            +   '</div>'
        )[0];

        var select = div.firstChild;
        for (var statusId in storage.reviewStatus) {
            var status = storage.reviewStatus[statusId];
            var option = new Option(status, statusId, statusId == selectedStatusId);
            select.appendChild(option);
        }

        return div.innerHTML;
    }

    /**
     * Ensure that the status options are available
     * 
     * @returns {Promise} 
     */
    function haveStatusOptions() {
        return new Promise(function (resolve, reject) {
            if (Object.keys(storage.reviewStatus).length) {
                return resolve();
            }

            var error = function () {
                toastr.error("Something went wrong! Please try again later");
                return reject()
            }

            setBusyState();
            $.ajax({
                url: route('API_Call', {method: 'getAttendanceMetricsStatuses'}),
                method: 'GET',
                dataType: 'json'
            }).done(function(res) {
                if (res.status && res.status == 200) {
                    storage.reviewStatus = res.data
                    return resolve()
                } else {
                    return error();
                }
            }).fail(error)
            .always(unsetBusyState);
        })
    }

    function updateMetric(id, reviewStatus, amount) {
        var error = function () {
            toastr.error("Could not update the metric! Please try again later");
        }
        setBusyState()
        $.ajax({
            url: filterFormElem.action,
            method: 'POST',
            data: {
                'update_metric': 'update_metric',
                'id': id,
                'status': reviewStatus,
                'amount': amount
            },
            dataType: 'json'
        }).done(function (res) {
            if (res.status && res.status == 204) {
                var employeeId = res.data.employee_id;
                var metricId = res.data.id;

                var index = storage
                    .attendanceMetrics[employeeId]
                    .findIndex(function (metric) {
                        return metric.id == metricId;
                    });

                storage.attendanceMetrics[employeeId][index] = res.data;
                storage.editing = null;
                $empAttdMetricsTbl.ajax.reload(undefined, false);
            } else {
                error();
            }
        }).fail(error)
        .always(unsetBusyState);
    }
});