// Document ready
$(function () {
    'use strict';

    const isUserDashboard = !!document.getElementById('system-user-dashboard');
    const isCashierDashboard = !!document.getElementById('system-cashier-dashboard');

    // Guard against other pages
    if (!isUserDashboard && !isCashierDashboard) {
        return;
    }

    // These widgets are in both cashier-dashboard and main dashboard
    initFindInvoice();
    initTodaysInvoices();
    initTodaysReceipts();

    if (!isUserDashboard) {
        return;
    }

    // initialize
    initDateFilter();
    initCategoryGroupWiseDailyReport();
    initCategoryGroupWiseMonthlyReport();
    initBankBalanceReport();
    initDepartmentWiseDailyCollection();
    initDepartmentWiseMonthlyCollection();
    initCustomerBalanceInquiry();
    initDailyCollectionBreakdown();

    function initFindInvoice() {
        if (!document.getElementById('find-invoice-card')) {
            return;
        }

        $('#find-invoice-card button[data-method]').on('click', (e) => {
            const btn = e.target;
            const input = $('#find-invoice-card [name="reference"]');
            const method = btn.dataset.method;
            const reference = input.val();

            input.parsley().whenValidate().then(handle);

            function handle() {
                ajaxRequest(
                    route('api.sales.invoice.findByReference', {
                        reference,
                    })
                )
                    .done(function (resp) {
                        if (!resp.data) {
                            return defaultErrorHandler();
                        }

                        toastr.success("Invoice found");
                        window.open(
                            (method == 'edit')
                                ? resp.data.update_transaction_id_link
                                : resp.data.print_link,
                            '_blank'
                        );
                    })
                    .fail(function (xhr) {
                        if (xhr.status == 404) {
                            return toastr.error('Could not find the invoice');
                        }

                        defaultErrorHandler();
                    });
            }
        });
    }

    function initTodaysInvoices() {

        new SimpleDashboardReport(
            'todays-invoices-card',
            {
                url: route('api.sales.reports.todaysInvoices'),
                data: undefined
            },
            [
                {
                    data: 'invoice_no',
                    render: function(data, type, row) {
                        if (type == 'display') {
                            return `<a href="${row.update_transaction_id_link}">${data}</a>`
                        }
                        return data;
                    }
                },
                {data: 'token_number'},
                {data: 'customer_name'},
                {data: 'display_customer'},
                {_type: 'amount', data: 'invoice_amount'},
                {
                    data: 'payment_status',
                    class: 'text-center',
                    render: {
                        display: (data) => {
                            const uiClassMap = {
                                'Not Paid': 'danger',
                                'Partially Paid': 'warning',
                                'Fully Paid': 'success'
                            };

                            return `<span class="badge bg-${uiClassMap[data] || 'secondary'}">${data}</span>`;
                        }
                    }
                },
                {data: 'payment_method'},
                {data: 'created_employee'},
                {data: 'transaction_status'},
            ],
            {
                searching: true,
                paging: true,
                ordering: true,
                scrollX: true,
            }
        )
    }
    function initTodaysReceipts() {

        new SimpleDashboardReport(
            'todays-receipts-card',
            {
                url: route('api.sales.reports.todaysReceipts'),
                data: undefined
            },
            [
                {data: 'invoice_no'},
                {data: 'customer_name'},
                {_type: 'amount', data: 'invoice_amount'},
                {data: 'created_employee'}
            ],
            {
                searching: true,
                paging: true,
                ordering: true,
                scrollX: true,
            }
        )
    }

    function initDateFilter() {
        const section = document.getElementById('date-filter');
        if (!section) {
            return;
        }

        // Initialize parsley instance
        const date = $('#date').parsley({
            required: true,
            triggerAfterFailure: 'change'
        });

        // Initialize datepicker
        date.$element.datepicker();

        $(section.querySelector('button')).on('click', function() {
            date.whenValidate()
                .then(function() {
                    const _date = date.element.value;
                    window.location.href = url(window.location.href, {date: _date}, true);
                })
        })
    }

    function initCategoryGroupWiseDailyReport() {
        new SimpleDashboardReport(
            'category-group-wise-daily-sales-card',
            route('api.sales.reports.categoryGroupWiseDailyReport'),
            [
                {className: 'text-start', data: 'description'},
                {_type: 'amount', data: 'quantity'},
                {_type: 'amount', data: 'govt_fee'},
                {_type: 'amount', data: 'service_charge'},
                {_type: 'amount', data: 'credit'},
                {_type: 'amount', data: 'discount'},
                {_type: 'amount', data: 'tax'},
                {_type: 'amount', data: 'line_total'},
            ]
        )
    }

    function initCategoryGroupWiseMonthlyReport() {
        new SimpleDashboardReport(
            'category-group-wise-monthly-sales-card',
            route('api.sales.reports.categoryGroupWiseMonthlyReport'),
            [
                {className: 'text-start', data: 'description'},
                {_type: 'amount', data: 'quantity'},
                {_type: 'amount', data: 'service_charge'},
                {_type: 'amount', data: 'line_total'},
                {_type: 'amount', data: 'credit'},
            ]
        )
    }

    function initBankBalanceReport() {
        new SimpleDashboardReport(
            'bank-transactions-card',
            route('api.sales.reports.bankBalanceReportForManagement'),
            [
                {className: 'text-start', data: 'account_name'},
                {_type: 'amount', data: 'opening_bal'},
                {_type: 'amount', data: 'debit'},
                {_type: 'amount', data: 'credit'},
                {_type: 'amount', data: 'balance'},
            ],
            {
                searching: false,
                ordering: false,
                paging: false,
                scrollX: true
            }
        )
    }

    function initDepartmentWiseDailyCollection() {
        new SimpleDashboardReport(
            'department-wise-daily-sales-card',
            route('api.sales.reports.departmentWiseDailyCollection'),
            [
                {className: 'text-start', data: 'name'},
                {_type: 'amount', data: 'trans_count'},
                {_type: 'amount', data: 'inv_total'},
                {_type: 'amount', data: 'cr_inv_total'},
                {_type: 'amount', data: 'discount'},
                {_type: 'amount', data: 'tax'},
                {_type: 'amount', data: 'gov_fee'},
                {_type: 'amount', data: 'benefits'},
                {_type: 'amount', data: 'commission'},
                {_type: 'amount', data: 'net_benefits'},
            ],
            {
                searching: false,
                ordering: true,
                paging: false,
                scrollX: true,
            }
        )
    }

    function initDepartmentWiseMonthlyCollection() {
        const report = new SimpleDashboardReport(
            'department-wise-monthly-sales-card',
            route('api.sales.reports.departmentWiseMonthlyCollection'),
            [
                {className: 'text-start', data: 'name'},
                {_type: 'amount', data: 'trans_count'},
                {_type: 'amount', data: 'inv_total'},
                {_type: 'amount', data: 'cr_inv_total'},
                {_type: 'amount', data: 'discount'},
                {_type: 'amount', data: 'tax'},
                {_type: 'amount', data: 'gov_fee'},
                {_type: 'amount', data: 'benefits'},
                {_type: 'amount', data: 'commission'},
                {_type: 'amount', data: 'oth_expense'},
                {_type: 'amount', data: 'estimated_expense'},
                {_type: 'amount', data: 'net_benefits'},
                {_type: 'amount', data: 'estimated_net_benefits'},
            ],
            {
                searching: false,
                ordering: true,
                paging: false,
                scrollX: true
            }
        )

        report.on('footer.loaded', appendOtherIncomes)

        /**
         * Appends the other incomes section to the footer
         *
         * @param {HTMLTableSectionElement} tFoot
         */
        function appendOtherIncomes(tFoot) {
            const res = report.responseFromServer;
            const formatter = report.amountFormatter;

            const row = $(
                `<tr>
                    <td class="text-start align-top"><b>Other Incomes</b></td>
                    <td colspan="2" class="text-start">
                        <b
                            class="cursor-pointer"
                            data-bs-toggle="collapse"
                            data-bs-target="#otherIncomeDetails"
                            aria-expanded="false"
                            aria-controls="otherIncomeDetails">
                            ${formatter.format(res.total.other_income)}
                        </b>
                        <div id="otherIncomeDetails" class="w-100 collapse mt-2">
                            <table class="table table-sm table-secondary" style="max-width: fit-content;">
                                <tbody></tbody>
                            </table>
                        </div>
                    </td>
                </tr>`
            )[0];

            const tbody = row.querySelector('tbody');
            res.otherIncomes.forEach(otherIncome => {
                tbody.appendChild($(
                    `<tr>
                        <td>${otherIncome.account_name}</td>
                        <td class="text-end">${formatter.format(otherIncome.amount)}</td>
                    </tr>`
                )[0])
            })

            tFoot.appendChild(row);
        }
    }

    function initCustomerBalanceInquiry() {
        new SimpleDashboardReport(
            'customer-balances-card',
            route('api.sales.reports.customerBalanceInquiry'),
            [
                {className: 'text-start', data: 'name'},
                {className: 'text-start', data: 'salesman_name'},
                {className: 'text-start', data: 'last_inv_date'},
                {className: 'text-start', data: 'last_pmt_date'},
                {_type: 'amount', data: 'opening_bal'},
                {_type: 'amount', data: 'debit'},
                {_type: 'amount', data: 'credit'},
                {_type: 'amount', data: 'closing_bal'}
            ]
        );
    }

    function initDailyCollectionBreakdown() {
        const report = new SimpleDashboardReport(
            'daily-collection-breakdown-card',
            route('api.sales.reports.dailyCollectionBreakdown'),
            [
                {className: 'text-start', data: 'description'},
                {_type: 'amount', data: 'amount'}
            ]
        );

        report.on('footer.loaded', footer => footer.firstElementChild.firstElementChild.innerText = 'Net Total');
    }

    /**
     * Function for similar functions in dashboard
     */
    function SimpleDashboardReport(sectionId, ajaxOptions, columns, dataTableOptions)
    {

        const section = document.getElementById(sectionId);
        const date = $('#date').parsley();
        const eventHandlers = {};
        const formatter = new Intl.NumberFormat('en-US', {maximumFractionDigits: 2});

        // public properties
        this.section = section;
        this.ajaxOptions = _ajaxOptions();
        this.columns = columns;
        this.dataTableOptions = _dataTableOptions();
        this.dataTable = null;
        this.responseFromServer = null;
        this.amountFormatter = formatter;
        this.on = on;

        const instance = this;

        if(!section) {
            return;
        }

        // Initialize the dataTable when the date field is valid
        date.whenValidate().then(initDataTable);

        /*
         |---------------------------------
         | Helper Functions
         |---------------------------------
         */

        /**
         * Initialzes the datatable
         */
        function initDataTable() {
            const table = section.querySelector('[data-control="dataTable"]');
            initializeTfoot(table);
            instance.dataTable = $(table).DataTable(_dataTableOptions())
        }

        /**
         * Fetch the data for datatables
         */
        function fetchData(data, callback, settings) {
            if (instance.responseFromServer !== null) {
                return callback({ data: instance.responseFromServer.data });
            }

            ajaxRequest(instance.ajaxOptions)
                .done(function (resp) {
                    if (!resp.data) {
                        return errorHandler();
                    }

                    instance.responseFromServer = resp;
                    setFooter();
                    callback({ data: instance.responseFromServer.data });
                }).fail(errorHandler);

            function errorHandler() {
                callback({data: []});
                defaultErrorHandler();
            }
        }

        /**
         * Build the footer
         */
        function setFooter() {
            const response = instance.responseFromServer;
            if (!response.total) {
                return;
            }

            const total = response.total;
            const footer = instance.dataTable.table().footer();
            const row = document.createElement('tr');

            if ((response?.data?.length || 0) < 2) {
                return;
            }

            const label = document.createElement('td');
            label.className = 'text-start';
            label.innerText = 'Total';
            row.appendChild(label);

            columns.slice(1).forEach(col => {
                const key = col.data || col.name;
                const type = col._type;
                const td = document.createElement('td');

                if (type == 'amount') {
                    td.innerText = formatter.format(total[key] ?? 0);
                }

                row.appendChild(td);
            })

            footer.replaceChild(row, footer.firstElementChild);

            dispatch('footer.loaded', footer);
        }

        /**
         * Build the dataTable options
         */
        function _dataTableOptions() {
            const defaults = {
                ajax: fetchData,
                searching: false,
                scrollX: true,
                scrollY: "400px",
                scrollCollapse: true,
                scroller: true,
                paging: true,
                ordering: false,
            }
            const _columns = columns.map(col => ({...col}));

            _columns.forEach(col => {
                if (col._type == 'amount') {
                    if (col.render === undefined) {
                        col.render = render;
                    }

                    delete col._type;
                }
            })

            if (dataTableOptions === undefined) {
                return {...defaults, columns: _columns};
            }

            return {
                ...dataTableOptions,
                ajax: fetchData,
                columns: _columns
            }
        }

        /**
         * Build ajax options
         */
        function _ajaxOptions() {
            let url = ajaxOptions;
            let options = undefined;

            // if its not a simple string swap
            if (typeof url === "object") {
                options = url;
                url = undefined;
            }

            // force options to be object
            options = options || {};

            const defaults = {
                url,
                data: {date: date.element.value},
                blocking: false
            };

            return {...defaults, ...options}
        }

        /**
         * Function for amount column rendering
         */
        function render (data, type) {
            if (type == 'display') {
                return formatter.format(data);
            }

            return data;
        };

        /**
         * Initialize the tfoot
         *
         * @param {HTMLTableElement} table
         */
        function initializeTfoot(table) {
            const tfoot = document.createElement('tfoot');
            const tr = document.createElement('tr');

            columns.map(() => {
                tr.appendChild(document.createElement('td'));
            })

            tfoot.appendChild(tr);
            table.appendChild(tfoot);
        }

        /**
         * Simple event handling registrar
         *
         * @param {string} event
         * @param {CallableFunction} handler
         */
        function on(event, handler) {
            if (eventHandlers[event] === undefined) {
                eventHandlers[event] = [];
            }

            eventHandlers[event].push(handler);
        }

        /**
         * Simple event handling
         *
         * @param {string} event
         */
        function dispatch(event, ...args) {
            if (eventHandlers[event] !== undefined) {
                eventHandlers[event]
                    .forEach(handler => handler(...args));
            }
        }
    }
});
