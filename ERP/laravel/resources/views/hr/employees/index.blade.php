@extends('layout.app')
@section('title', 'Employees List')

@push('styles')
<style>
    table.dataTable > thead .sorting_asc:before,
    table.dataTable > thead .sorting_asc:after,
    table.dataTable > thead .sorting_desc:before,
    table.dataTable > thead .sorting_desc:after {
        position: absolute;
        top: calc(50% - 0.5em);
    }

    .employees-table th, .employees-table td {
        padding-left: 0.75rem;
        padding-right: 1.5rem !important;
    }

    .employees-table th,
    .employees-table td {
        height: initial !important;
        vertical-align: middle;
    }

    .employees-table tr {
        position: relative;
    }

    .employees-table thead th {
        background: #fff;,
        color: #181C32;
    }

    .employees-table tbody tr:nth-child(odd) td {
        background-color: var(--bs-table-striped-bg);
        color: var(--bs-table-striped-color);
    }

    .employees-table tbody tr:nth-child(even) td {
        background: #fff;
    }

    .employees-table thead th:first-child,
    .employees-table tbody td:first-child {
        position: sticky;
        z-index: 1;
        left: 0;
    }
    
    .employees-table thead th:nth-child(2),
    .employees-table tbody td:nth-child(2) {
        position: sticky;
        z-index: 1;
        left: calc(100px + 1.5rem);
    }
    
    .employees-table thead th:last-child,
    .employees-table tbody td:last-child {
        position: sticky;
        z-index: 1;
        right: 0;
    }
</style>
@endpush

@section('page')
    <div class="container-fluid">
        <div class="card top">
            <div class="card-header">
                <div class="card-title">
                    <h3>Employees List</h3>
                </div>
            </div>
            <div class="card-body">
                <div id="employees-table-container">
                    <table class="employees-table table table-bordered text-nowrap table-striped thead-strong" id="employees-table">
                        <!-- Generated through datatable -->
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection
@push('scripts')
<script>
    $(function () {
        const storage = {
            genders: @json($genders),
            maritalStatuses: @json($maritalStatuses),
            modeOfPays: @json($modeOfPays),
            employmentStatuses: @json($employmentStatuses)
        }

        route.push('employee.edit', '/ERP/hrm/employees/edit_employee.php?id={employee}');
        route.push('employeeProfile.personal', '{{ rawRoute('employeeProfile.personal') }}');

        $('#employees-table-container').on('keyup', '[data-filter]', function () {
            $('#employees-table').DataTable()
                .column($(this).parent().index())
                .search(this.value)
                .draw();
        })

        ajaxRequest('{{ route('employees.index') }}')
            .done((respJson, msg, xhr) => {
                if (!respJson.data) {
                    return defaultErrorHandler(xhr);
                }

                const columns = [
                    {
                        data: 'emp_ref',
                        title: 'Employee ID',
                        width: '100px',
                        className: 'ps-3 text-center min-w-100px'
                    },
                    {
                        data: 'name',
                        title: 'Name (En)',
                        width: '175px',
                        className: 'ps-3 text-wrap min-w-175px'
                    },
                    {data: 'ar_name', title: 'Name (Arb)'},
                    {data: 'username', title: 'Username'},
                    {data: 'working_company_name', title: 'Working Company'},
                    {data: 'visa_company_name', title: 'Visa Company'},
                    {data: 'department_name', title: 'Department'},
                    {data: 'designation_name', title: 'Designation'},
                    {data: 'flow_group_name', title: 'Workflow Group'},
                    {data: 'country', title: 'Nationality'},
                    {
                        data: 'gender',
                        title: 'Gender',
                        defaultContent: 'Not Specified',
                        render: data => (storage.genders[data] ?? null)
                    },
                    {
                        data: 'date_of_birth',
                        title: 'DOB',
                        defaultContent: '',
                        render: dateFormatter
                    },
                    {data: 'blood_group', title: 'Blood Group'},
                    {
                        data: 'marital_status',
                        title: 'Marital Status',
                        defaultContent: 'Not Specified',
                        render: data => (storage.maritalStatuses[data] ?? null)
                    },
                    {data: 'email', title: 'Email'},
                    {data: 'mobile_no', title: 'Mobile No.'},
                    {
                        data: 'date_of_join',
                        title: 'Date of join',
                        defaultContent: '',
                        render: dateFormatter
                    },
                    {
                        data: 'mode_of_pay',
                        title: 'Payment Mode',
                        defaultContent: '',
                        render: data => (storage.modeOfPays[data] ?? null)
                    },
                    {data: 'bank_name', title: 'Bank Name'},
                    {data: 'iban_no', title: 'IBAN No.'},
                    {data: 'file_no', title: 'File No.'},
                    {data: 'uid_no', title: 'UID No.'},
                    {data: 'passport_no', title: 'Passport No.'},
                    {data: 'personal_id_no', title: 'Personal ID No.'},
                    {data: 'labour_card_no', title: 'Labour Card No.'},
                    {data: 'emirates_id', title: 'Emirates ID'},
                    {
                        data: 'week_offs',
                        title: 'Week Offs',
                        defaultContent: '',
                        render: data => {
                            try {
                                data = JSON.parse(data);
                                if (data instanceof Array) {
                                    return data.filter((value, index, array) => array.indexOf(value) === index).join(',');
                                }
                            } catch (e) {}

                            return null;
                        }
                    },
                    {data: 'work_hours', title: 'Work Hours'},
                    {
                        data: 'has_commission',
                        title: 'Has Commission',
                        defaultContent: '',
                        render: booleanFormatter
                    },
                    {
                        data: 'has_pension',
                        title: 'Has Pension',
                        defaultContent: '',
                        render: booleanFormatter
                    },
                    {
                        data: 'has_overtime',
                        title: 'Has Overtime',
                        defaultContent: '',
                        render: booleanFormatter
                    },
                    {
                        data: 'commence_from',
                        title: 'Commence From',
                        defaultContent: '',
                        render: dateFormatter
                    },
                    {
                        data: 'require_attendance',
                        title: 'Require Attendance',
                        defaultContent: '',
                        render: booleanFormatter
                    },
                    {data: 'basic_salary', title: 'Basic Salary'},
                    {data: 'monthly_salary', title: 'Monthly Salary'},
                    {
                        data: 'status',
                        title: 'Status',
                        defaultContent: '',
                        render: data => (storage.employmentStatuses[data] ?? null)
                    },
                    {
                        data: null,
                        title: '',
                        defaultContent: '',
                        width: '50px',
                        class: 'w-50px text-center',
                        render: (data, type, row) => {
                            if (type != 'display') {
                                return null;
                            }

                            let actions = [];

                            actions.push(
                                `<a
                                    title="Edit Basic Details"
                                    class="btn btn-text-accent px-3 py-0 fs-3"
                                    href="${ route('employee.edit', {employee: row.id}) }" target="_blank">
                                    <span class="fa fa-pencil-alt" title="Edit Basic Details"></span>
                                </a>`
                            );

                            actions.push(
                                `<a
                                    title="Goto Profile View"
                                    class="btn btn-text-primary px-3 py-0 fs-3"
                                    href="${ route('employeeProfile.personal', {employee: row.id}) }" target="_blank">
                                    <span class="fa fa-eye" title="View Profile"></span>
                                </a>`
                            );

                            return actions.join("\n");
                        }
                    }
                ];

                // Append the header
                let thead = document.createElement('thead');
                let titleTr = document.createElement('tr');
                let searchTr = document.createElement('tr');
                columns.forEach(col => {
                    titleTr.appendChild($(`<th>${col.title}</th>`)[0])
                    
                    let input = col.data
                        ? `<input class="form-control form-control-sm" type="text" placeholder="${col.title}" data-filter="${col.key}"/>`
                        : '';
                    searchTr.appendChild($(`<th>${input}</th>`)[0])
                })
                thead.appendChild(titleTr);
                thead.appendChild(searchTr);
                document.querySelector('#employees-table').appendChild(thead);

                // Instantiate the dataTable
                $('#employees-table').DataTable({
                    data: respJson.data,
                    processing: true,
                    paging: true,
                    searchDelay: 1500,
                    ordering: true,
                    orderCellsTop: true,
                    rowId: 'id',
                    buttons: [
                        'copy', 'csv', 'excel'
                    ],
                    dom:
                        // "<'row'<'col-auto'Q>>" +
                        "<'row'<'col-sm-6 justify-content-end'B><'col-sm-6 justify-content-end'f>>" +
                        "<t>" +
                        "<r>" +
                        "<'row'" +
                        "<'col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start'li>" +
                        "<'col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end'p>" +
                        ">",
                    scrollCollapse: true,
                    scroller: true,
                    scrollY: 400,
                    scrollX: true,
                    deferRender: true,
                    columns
                })
            })
            .fail(defaultErrorHandler);

        function dateFormatter(data) {
            const date = moment(data);
            return date.isValid() ? date.format('{{ getDateFormatForMomentJs() }}') : null;
        }
        
        function booleanFormatter(data) {
            return data == '1' ? 'Yes' : 'No';
        }
    })
</script>
@endpush
