"use strict";
$(".btn-find-invoice").click(function(e) {

    var type = $(this).data('method');

    KTApp.blockPage({
        overlayColor: '#000000',
        type: 'v2',
        state: 'success',
        message: 'Please wait...'
    });

    var invoice_number = $("#txt_print_invoice_number").val();

    $.ajax({
        url: erpUrl("sales/read_sales_invoice.php"),
        type: "post",
        dataType: 'JSON',
        data: {
            invoice_ref: invoice_number
        },
        success: function(response) {
            KTApp.unblockPage();

            if(response != 'false' && response.trans_no) {

                toastr.success("Invoice found");

                var edit_url = erpUrl("sales/customer_invoice.php")+"?ModifyInvoice="+response.trans_no;

                if(response.payment_flag != "0" && response.payment_flag != "3") {
                    edit_url += "&is_tadbeer=1&show_items=ts";
                }

                if(response.payment_flag == "4" || response.payment_flag == "5") {
                    edit_url += "&is_tadbeer=1&show_items=tb";
                }

                if(type == 'edit') {
                    window.location.href = edit_url;
                }
                else{
                    var print_params = "PARAM_0="+response.trans_no+"-10&PARAM_1="+
                        response.trans_no+"-10&PARAM_2=&PARAM_3=0&PARAM_4=&PARAM_5=&PARAM_6=&PARAM_7=0&REP_ID=107";

                    var print_link = erpUrl("invoice_print") + "?"+print_params;

                    window.open(
                        print_link,
                        '_blank'
                    );
                }


            }
            else {
                // alert("No invoice found");
                toastr.error("No invoice found!");
            }
        },
        error: function(xhr) {
        }
    });

});


// Class definition

var KTDashboard = function () {

    // Sparkline Chart helper function
    var _initSparklineChart = function (src, data, color, border) {
        if (src.length == 0) {
            return;
        }

        var config = {
            type: 'line',
            data: {
                labels: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October"],
                datasets: [{
                    label: "",
                    borderColor: color,
                    borderWidth: border,

                    pointHoverRadius: 4,
                    pointHoverBorderWidth: 12,
                    pointBackgroundColor: Chart.helpers.color('#000000').alpha(0).rgbString(),
                    pointBorderColor: Chart.helpers.color('#000000').alpha(0).rgbString(),
                    pointHoverBackgroundColor: KTApp.getStateColor('danger'),
                    pointHoverBorderColor: Chart.helpers.color('#000000').alpha(0.1).rgbString(),
                    fill: false,
                    data: data,
                }]
            },
            options: {
                title: {
                    display: false,
                },
                tooltips: {
                    enabled: false,
                    intersect: false,
                    mode: 'nearest',
                    xPadding: 10,
                    yPadding: 10,
                    caretPadding: 10
                },
                legend: {
                    display: false,
                    labels: {
                        usePointStyle: false
                    }
                },
                responsive: true,
                maintainAspectRatio: true,
                hover: {
                    mode: 'index'
                },
                scales: {
                    xAxes: [{
                        display: false,
                        gridLines: false,
                        scaleLabel: {
                            display: true,
                            labelString: 'Month'
                        }
                    }],
                    yAxes: [{
                        display: false,
                        gridLines: false,
                        scaleLabel: {
                            display: true,
                            labelString: 'Value'
                        },
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                },

                elements: {
                    point: {
                        radius: 4,
                        borderWidth: 12
                    },
                },

                layout: {
                    padding: {
                        left: 0,
                        right: 10,
                        top: 5,
                        bottom: 0
                    }
                }
            }
        };

        return new Chart(src, config);
    }


    var expensesChart = function () {
        if ($('#kt_chart_expenses').length == 0) {
            return;
        }

        var ctx = document.getElementById("kt_chart_expenses").getContext("2d");

        var gradient = ctx.createLinearGradient(0, 0, 0, 240);
        gradient.addColorStop(0, Chart.helpers.color('#d1f1ec').alpha(1).rgbString());
        gradient.addColorStop(1, Chart.helpers.color('#d1f1ec').alpha(0.3).rgbString());


        $.getJSON(route('API_Call', {method: 'expenses'}), {})
            .done(function (data) {

                var labels = [];
                var values = [];

                $.each(data, function (key, value) {
                    labels.push(value.name);
                    values.push(value.balance);
                });

                var config = {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: "Amount",
                            backgroundColor: gradient,
                            borderColor: KTApp.getStateColor('success'),

                            pointBackgroundColor: Chart.helpers.color('#000000').alpha(0).rgbString(),
                            pointBorderColor: Chart.helpers.color('#000000').alpha(0).rgbString(),
                            pointHoverBackgroundColor: KTApp.getStateColor('danger'),
                            pointHoverBorderColor: Chart.helpers.color('#000000').alpha(0.1).rgbString(),

                            //fill: 'start',
                            data: values
                        }]
                    },
                    options: {
                        title: {
                            display: false,
                        },
                        tooltips: {
                            mode: 'nearest',
                            intersect: false,
                            position: 'nearest',
                            xPadding: 10,
                            yPadding: 10,
                            caretPadding: 10
                        },
                        legend: {
                            display: false
                        },
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            xAxes: [{
                                display: false,
                                gridLines: false,
                                scaleLabel: {
                                    display: true,
                                    labelString: 'Month'
                                }
                            }],
                            yAxes: [{
                                display: false,
                                gridLines: false,
                                scaleLabel: {
                                    display: true,
                                    labelString: 'Value'
                                },
                                ticks: {
                                    beginAtZero: true
                                }
                            }]
                        },
                        elements: {
                            line: {
                                tension: 0.0000001
                            },
                            point: {
                                radius: 4,
                                borderWidth: 12
                            }
                        },
                        layout: {
                            padding: {
                                left: 15,
                                right: 15,
                                top: 10,
                                bottom: 0
                            }
                        }
                    }
                };

                var chart = new Chart(ctx, config);


            });


    }


    var dailyCategorySalesCount = function () {
        var chartContainer = KTUtil.getByID('kt_chart_category_wise_sales_count');

        if (!chartContainer) {
            return;
        }


        $.getJSON(route('API_Call', {method: 'category_sales_count'}), {})
            .done(function (data) {

                var labels = [];
                var values = [];

                $.each(data, function (key, value) {
                    labels.push(key);
                    values.push(value);
                });


                var chartData = {
                    labels: labels,
                    datasets: [{
                        label: 'Count',
                        backgroundColor: KTApp.getStateColor('success'),
                        data: values
                    }, {
                        label: 'Count',
                        backgroundColor: '#f3f3fb',
                        data: values
                    }]
                };

                var chart = new Chart(chartContainer, {
                    type: 'bar',
                    data: chartData,
                    options: {
                        title: {
                            display: false,
                        },
                        tooltips: {
                            intersect: false,
                            mode: 'nearest',
                            xPadding: 10,
                            yPadding: 10,
                            caretPadding: 10
                        },
                        legend: {
                            display: false
                        },
                        responsive: true,
                        maintainAspectRatio: false,
                        barRadius: 4,
                        scales: {
                            xAxes: [{
                                display: true,
                                gridLines: true,
                                stacked: true
                            }],
                            yAxes: [{
                                display: true,
                                stacked: true,
                                gridLines: true
                            }]
                        },
                        layout: {
                            padding: {
                                left: 0,
                                right: 0,
                                top: 0,
                                bottom: 0
                            }
                        }
                    }
                });


            });


    }


    var top_ten_customers = function () {
        var chartContainer = KTUtil.getByID('kt_top_ten_customers');

        if (!chartContainer) {
            return;
        }


        $.getJSON(route('API_Call', {method: 'top_ten_customers'}), {})
            .done(function (data) {

                var labels = [];
                var values = [];

                $.each(data, function (key, value) {
                    labels.push(value.name);
                    values.push(value.total);
                });


                var chartData = {
                    labels: labels,
                    datasets: [{
                        label: 'Amount',
                        backgroundColor: KTApp.getStateColor('danger'),
                        data: values
                    }]
                };

                var chart = new Chart(chartContainer, {
                    type: 'bar',
                    data: chartData,
                    options: {
                        title: {
                            display: false,
                        },
                        tooltips: {
                            intersect: false,
                            mode: 'nearest',
                            xPadding: 10,
                            yPadding: 10,
                            caretPadding: 10
                        },
                        legend: {
                            display: false
                        },
                        responsive: true,
                        maintainAspectRatio: false,
                        barRadius: 4,
                        scales: {
                            xAxes: [{
                                display: true,
                                gridLines: true,
                                stacked: true
                            }],
                            yAxes: [{
                                display: true,
                                stacked: true,
                                gridLines: true
                            }]
                        },
                        layout: {
                            padding: {
                                left: 0,
                                right: 0,
                                top: 0,
                                bottom: 0
                            }
                        }
                    }
                });


            });


    }


    // Profit Share Chart.
    // Based on Chartjs plugin - http://www.chartjs.org/
    var profitShare = function () {
        if (!KTUtil.getByID('kt_chart_profit_share')) {
            return;
        }

        var randomScalingFactor = function () {
            return Math.round(Math.random() * 100);
        };


        $.getJSON(route('API_Call', {method: 'employee_service_count'}), {})
            .done(function (data) {

                var labels = [];
                var values = [];

                var kt_bgs = [
                    "kt-bg-success",
                    "kt-bg-danger",
                    "kt-bg-brand",
                    "kt-bg-info",
                    "kt-bg-dark",
                ];

                var brief_html = "";
                var i = 0;
                var count_sum = 0;
                $.each(data, function (key, value) {
                    labels.push(key);
                    values.push(value);

                    count_sum += parseFloat(value);


                    brief_html += "<div class=\"kt-widget14__legend\">\n" +
                        "<span class=\"kt-widget14__bullet " + kt_bgs[i] + " \"></span>\n" +
                        "<span class=\"kt-widget14__stats\">" + value + " : " + key + "</span>\n" +
                        "</div>";
                    i++;

                });


                $(".employee_serv_count_brief").html(brief_html);
                $(".employee_serv_count_avg").html("AVG : " + (count_sum / (i + 1)).toFixed(2));


                var config = {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data: values,
                            backgroundColor: [
                                KTApp.getStateColor('success'),
                                KTApp.getStateColor('danger'),
                                KTApp.getStateColor('brand'),
                                KTApp.getStateColor('info'),
                                KTApp.getStateColor('dark')
                            ]
                        }],
                        labels: labels
                    },
                    options: {
                        cutoutPercentage: 75,
                        responsive: true,
                        maintainAspectRatio: false,
                        legend: {
                            display: false,
                            position: 'top',
                        },
                        title: {
                            display: false,
                            text: 'Technology'
                        },
                        animation: {
                            animateScale: true,
                            animateRotate: true
                        },
                        tooltips: {
                            enabled: true,
                            intersect: false,
                            mode: 'nearest',
                            bodySpacing: 5,
                            yPadding: 10,
                            xPadding: 10,
                            caretPadding: 0,
                            displayColors: false,
                            backgroundColor: KTApp.getStateColor('brand'),
                            titleFontColor: '#ffffff',
                            cornerRadius: 4,
                            footerSpacing: 0,
                            titleSpacing: 0
                        }
                    }
                };

                var ctx = KTUtil.getByID('kt_chart_profit_share').getContext('2d');
                var myDoughnut = new Chart(ctx, config);


            });


    }

    // Sales Stats.
    // Based on Chartjs plugin - http://www.chartjs.org/
    var salesStats = function () {
        if (!KTUtil.getByID('kt_chart_sales_stats')) {
            return;
        }

        var config = {
            type: 'line',
            data: {
                labels: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December",
                    "January", "February", "March", "April"
                ],
                datasets: [{
                    label: "Sales Stats",
                    borderColor: KTApp.getStateColor('brand'),
                    borderWidth: 2,
                    //pointBackgroundColor: KTApp.getStateColor('brand'),
                    backgroundColor: KTApp.getStateColor('brand'),
                    pointBackgroundColor: Chart.helpers.color('#ffffff').alpha(0).rgbString(),
                    pointBorderColor: Chart.helpers.color('#ffffff').alpha(0).rgbString(),
                    pointHoverBackgroundColor: KTApp.getStateColor('danger'),
                    pointHoverBorderColor: Chart.helpers.color(KTApp.getStateColor('danger')).alpha(0.2).rgbString(),
                    data: [
                        10, 20, 16,
                        18, 12, 40,
                        35, 30, 33,
                        34, 45, 40,
                        60, 55, 70,
                        65, 75, 62
                    ]
                }]
            },
            options: {
                title: {
                    display: false,
                },
                tooltips: {
                    intersect: false,
                    mode: 'nearest',
                    xPadding: 10,
                    yPadding: 10,
                    caretPadding: 10
                },
                legend: {
                    display: false,
                    labels: {
                        usePointStyle: false
                    }
                },
                responsive: true,
                maintainAspectRatio: false,
                hover: {
                    mode: 'index'
                },
                scales: {
                    xAxes: [{
                        display: false,
                        gridLines: false,
                        scaleLabel: {
                            display: true,
                            labelString: 'Month'
                        }
                    }],
                    yAxes: [{
                        display: false,
                        gridLines: false,
                        scaleLabel: {
                            display: true,
                            labelString: 'Value'
                        }
                    }]
                },

                elements: {
                    point: {
                        radius: 3,
                        borderWidth: 0,

                        hoverRadius: 8,
                        hoverBorderWidth: 2
                    }
                }
            }
        };

        var chart = new Chart(KTUtil.getByID('kt_chart_sales_stats'), config);
    }

    // Sales By KTUtillication Stats.
    // Based on Chartjs plugin - http://www.chartjs.org/
    var salesByApps = function () {
        // Init chart instances
        _initSparklineChart($('#kt_chart_sales_by_apps_1_1'), [10, 20, -5, 8, -20, -2, -4, 15, 5, 8], KTApp.getStateColor('success'), 2);
        _initSparklineChart($('#kt_chart_sales_by_apps_1_2'), [2, 16, 0, 12, 22, 5, -10, 5, 15, 2], KTApp.getStateColor('danger'), 2);
        _initSparklineChart($('#kt_chart_sales_by_apps_1_3'), [15, 5, -10, 5, 16, 22, 6, -6, -12, 5], KTApp.getStateColor('success'), 2);
        _initSparklineChart($('#kt_chart_sales_by_apps_1_4'), [8, 18, -12, 12, 22, -2, -14, 16, 18, 2], KTApp.getStateColor('warning'), 2);

        _initSparklineChart($('#kt_chart_sales_by_apps_2_1'), [10, 20, -5, 8, -20, -2, -4, 15, 5, 8], KTApp.getStateColor('danger'), 2);
        _initSparklineChart($('#kt_chart_sales_by_apps_2_2'), [2, 16, 0, 12, 22, 5, -10, 5, 15, 2], KTApp.getStateColor('dark'), 2);
        _initSparklineChart($('#kt_chart_sales_by_apps_2_3'), [15, 5, -10, 5, 16, 22, 6, -6, -12, 5], KTApp.getStateColor('brand'), 2);
        _initSparklineChart($('#kt_chart_sales_by_apps_2_4'), [8, 18, -12, 12, 22, -2, -14, 16, 18, 2], KTApp.getStateColor('info'), 2);
    }

    // Latest Updates.
    // Based on Chartjs plugin - http://www.chartjs.org/
    var latestUpdates = function () {
        if ($('#bank_balance_brief[data-dashboard="true"]').length == 0) {
            return;
        }

        $.getJSON(route('API_Call', {method: 'bank_balances'}), {})
            .done(function (data) {

                var labels = [];
                var values = [];

                var brief_html = "";

                $.each(data, function (key, value) {
                    labels.push(key);
                    values.push(value);

                    brief_html += "<div class=\"kt-widget4__item\">\n" +
                        "<span class=\"kt-widget4__icon\">\n" +
                        "<i class=\"flaticon2-graphic  kt-font-brand\"></i>\n" +
                        "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>\n" +
                        "<a href=\"#\" class=\"kt-widget4__title\">\n" + key +
                        "</a>\n" +
                        "<span class=\"kt-widget4__number kt-font-brand\">" + value + "</span>\n" +
                        "</div>"

                });


                $("#bank_balance_brief").html(brief_html);

                var config = {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: "Amount",
                            backgroundColor: KTApp.getStateColor('info'), // Put the gradient here as a fill color
                            borderColor: KTApp.getStateColor('info'),
                            pointBackgroundColor: Chart.helpers.color('#000000').alpha(0).rgbString(),
                            pointBorderColor: Chart.helpers.color('#000000').alpha(0).rgbString(),
                            pointHoverBackgroundColor: KTApp.getStateColor('success'),
                            pointHoverBorderColor: Chart.helpers.color('#000000').alpha(0.1).rgbString(),

                            //fill: 'start',
                            data: values
                        }]
                    },
                    options: {
                        title: {
                            display: false,
                        },
                        tooltips: {
                            intersect: false,
                            mode: 'nearest',
                            xPadding: 10,
                            yPadding: 10,
                            caretPadding: 10
                        },
                        legend: {
                            display: false
                        },
                        responsive: true,
                        maintainAspectRatio: false,
                        hover: {
                            mode: 'index'
                        },
                        scales: {
                            xAxes: [{
                                display: false,
                                gridLines: false,
                                scaleLabel: {
                                    display: true,
                                    labelString: 'Month'
                                }
                            }],
                            yAxes: [{
                                display: false,
                                gridLines: false,
                                scaleLabel: {
                                    display: true,
                                    labelString: 'Value'
                                },
                                ticks: {
                                    beginAtZero: true
                                }
                            }]
                        },
                        elements: {
                            line: {
                                tension: 0.0000001
                            },
                            point: {
                                radius: 4,
                                borderWidth: 12
                            }
                        }
                    }
                };

                //var chart = new Chart(ctx, config);


            });


    }

    // Trends Stats.
    // Based on Chartjs plugin - http://www.chartjs.org/
    var trendsStats = function () {
        if ($('#kt_chart_trends_stats').length == 0) {
            return;
        }

        var ctx = document.getElementById("kt_chart_trends_stats").getContext("2d");

        var gradient = ctx.createLinearGradient(0, 0, 0, 240);
        gradient.addColorStop(0, Chart.helpers.color('#00c5dc').alpha(0.7).rgbString());
        gradient.addColorStop(1, Chart.helpers.color('#f2feff').alpha(0).rgbString());

        var config = {
            type: 'line',
            data: {
                labels: [
                    "January", "February", "March", "April", "May", "June", "July", "August", "September", "October",
                    "January", "February", "March", "April", "May", "June", "July", "August", "September", "October",
                    "January", "February", "March", "April", "May", "June", "July", "August", "September", "October",
                    "January", "February", "March", "April"
                ],
                datasets: [{
                    label: "Sales Stats",
                    backgroundColor: gradient, // Put the gradient here as a fill color
                    borderColor: '#0dc8de',

                    pointBackgroundColor: Chart.helpers.color('#ffffff').alpha(0).rgbString(),
                    pointBorderColor: Chart.helpers.color('#ffffff').alpha(0).rgbString(),
                    pointHoverBackgroundColor: KTApp.getStateColor('danger'),
                    pointHoverBorderColor: Chart.helpers.color('#000000').alpha(0.2).rgbString(),

                    //fill: 'start',
                    data: [
                        20, 10, 18, 15, 26, 18, 15, 22, 16, 12,
                        12, 13, 10, 18, 14, 24, 16, 12, 19, 21,
                        16, 14, 21, 21, 13, 15, 22, 24, 21, 11,
                        14, 19, 21, 17
                    ]
                }]
            },
            options: {
                title: {
                    display: false,
                },
                tooltips: {
                    intersect: false,
                    mode: 'nearest',
                    xPadding: 10,
                    yPadding: 10,
                    caretPadding: 10
                },
                legend: {
                    display: false
                },
                responsive: true,
                maintainAspectRatio: false,
                hover: {
                    mode: 'index'
                },
                scales: {
                    xAxes: [{
                        display: false,
                        gridLines: false,
                        scaleLabel: {
                            display: true,
                            labelString: 'Month'
                        }
                    }],
                    yAxes: [{
                        display: false,
                        gridLines: false,
                        scaleLabel: {
                            display: true,
                            labelString: 'Value'
                        },
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                },
                elements: {
                    line: {
                        tension: 0.19
                    },
                    point: {
                        radius: 4,
                        borderWidth: 12
                    }
                },
                layout: {
                    padding: {
                        left: 0,
                        right: 0,
                        top: 5,
                        bottom: 0
                    }
                }
            }
        };

        var chart = new Chart(ctx, config);
    }

    // Trends Stats 2.
    // Based on Chartjs plugin - http://www.chartjs.org/
    var trendsStats2 = function () {
        if ($('#kt_chart_trends_stats_2').length == 0) {
            return;
        }

        var ctx = document.getElementById("kt_chart_trends_stats_2").getContext("2d");

        var config = {
            type: 'line',
            data: {
                labels: [
                    "January", "February", "March", "April", "May", "June", "July", "August", "September", "October",
                    "January", "February", "March", "April", "May", "June", "July", "August", "September", "October",
                    "January", "February", "March", "April", "May", "June", "July", "August", "September", "October",
                    "January", "February", "March", "April"
                ],
                datasets: [{
                    label: "Sales Stats",
                    backgroundColor: '#d2f5f9', // Put the gradient here as a fill color
                    borderColor: KTApp.getStateColor('brand'),

                    pointBackgroundColor: Chart.helpers.color('#ffffff').alpha(0).rgbString(),
                    pointBorderColor: Chart.helpers.color('#ffffff').alpha(0).rgbString(),
                    pointHoverBackgroundColor: KTApp.getStateColor('danger'),
                    pointHoverBorderColor: Chart.helpers.color('#000000').alpha(0.2).rgbString(),

                    //fill: 'start',
                    data: [
                        20, 10, 18, 15, 32, 18, 15, 22, 8, 6,
                        12, 13, 10, 18, 14, 24, 16, 12, 19, 21,
                        16, 14, 24, 21, 13, 15, 27, 29, 21, 11,
                        14, 19, 21, 17
                    ]
                }]
            },
            options: {
                title: {
                    display: false,
                },
                tooltips: {
                    intersect: false,
                    mode: 'nearest',
                    xPadding: 10,
                    yPadding: 10,
                    caretPadding: 10
                },
                legend: {
                    display: false
                },
                responsive: true,
                maintainAspectRatio: false,
                hover: {
                    mode: 'index'
                },
                scales: {
                    xAxes: [{
                        display: false,
                        gridLines: false,
                        scaleLabel: {
                            display: true,
                            labelString: 'Month'
                        }
                    }],
                    yAxes: [{
                        display: false,
                        gridLines: false,
                        scaleLabel: {
                            display: true,
                            labelString: 'Value'
                        },
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                },
                elements: {
                    line: {
                        tension: 0.19
                    },
                    point: {
                        radius: 4,
                        borderWidth: 12
                    }
                },
                layout: {
                    padding: {
                        left: 0,
                        right: 0,
                        top: 5,
                        bottom: 0
                    }
                }
            }
        };

        var chart = new Chart(ctx, config);
    }

    // Trends Stats.
    // Based on Chartjs plugin - http://www.chartjs.org/
    var latestTrendsMap = function () {
        if ($('#kt_chart_latest_trends_map').length == 0) {
            return;
        }

        try {
            var map = new GMaps({
                div: '#kt_chart_latest_trends_map',
                lat: -12.043333,
                lng: -77.028333
            });
        } catch (e) {
            console.log(e);
        }
    }

    // Revenue Change.
    // Based on Morris plugin - http://morrisjs.github.io/morris.js/
    var revenueChange = function () {
        if ($('#kt_chart_revenue_change').length == 0) {
            return;
        }


        var kt_bgs = [
            "kt-bg-success",
            "kt-bg-danger",
            "kt-bg-brand",
            "kt-bg-info",
            "kt-bg-dark",
        ];

        $.getJSON(route('API_Call', {method: 'top_five_category'}), {})
            .done(function (data) {

                var dataSet = [];

                var i = 0;
                var brief_html = "";
                $.each(data, function (key, value) {

                    dataSet.push({
                        label: key,
                        value: value
                    });


                    brief_html += "<div class=\"kt-widget14__legend\">\n" +
                        "<span class=\"kt-widget14__bullet " + kt_bgs[i] + " \"></span>\n" +
                        "<span class=\"kt-widget14__stats\">" + value + " : " + key + "</span>\n" +
                        "</div>";

                    i++;

                });

                $(".category_sales_count_brief").html(brief_html);

                Morris.Donut({
                    element: 'kt_chart_revenue_change',
                    data: dataSet,
                    colors: [
                        KTApp.getStateColor('success'),
                        KTApp.getStateColor('danger'),
                        KTApp.getStateColor('brand'),
                        KTApp.getStateColor('info'),
                        KTApp.getStateColor('dark')
                    ],
                });


            });


    }

    // Support Tickets Chart.
    // Based on Morris plugin - http://morrisjs.github.io/morris.js/
    var supportCases = function () {
        if ($('#kt_chart_support_tickets').length == 0) {
            return;
        }

        Morris.Donut({
            element: 'kt_chart_support_tickets',
            data: [{
                label: "Margins",
                value: 20
            },
                {
                    label: "Profit",
                    value: 70
                },
                {
                    label: "Lost",
                    value: 10
                }
            ],
            labelColor: '#a7a7c2',
            colors: [
                KTApp.getStateColor('success'),
                KTApp.getStateColor('brand'),
                KTApp.getStateColor('danger')
            ]
            //formatter: function (x) { return x + "%"}
        });
    }

    // Support Tickets Chart.
    // Based on Chartjs plugin - http://www.chartjs.org/
    var supportRequests = function () {
        var container = KTUtil.getByID('kt_chart_support_requests');

        if (!container) {
            return;
        }

        var randomScalingFactor = function () {
            return Math.round(Math.random() * 100);
        };

        var config = {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [
                        35, 30, 35
                    ],
                    backgroundColor: [
                        KTApp.getStateColor('success'),
                        KTApp.getStateColor('danger'),
                        KTApp.getStateColor('brand')
                    ]
                }],
                labels: [
                    'Angular',
                    'CSS',
                    'HTML'
                ]
            },
            options: {
                cutoutPercentage: 75,
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    display: false,
                    position: 'top',
                },
                title: {
                    display: false,
                    text: 'Technology'
                },
                animation: {
                    animateScale: true,
                    animateRotate: true
                },
                tooltips: {
                    enabled: true,
                    intersect: false,
                    mode: 'nearest',
                    bodySpacing: 5,
                    yPadding: 10,
                    xPadding: 10,
                    caretPadding: 0,
                    displayColors: false,
                    backgroundColor: KTApp.getStateColor('brand'),
                    titleFontColor: '#ffffff',
                    cornerRadius: 4,
                    footerSpacing: 0,
                    titleSpacing: 0
                }
            }
        };

        var ctx = container.getContext('2d');
        var myDoughnut = new Chart(ctx, config);
    }

    // Activities Charts.
    // Based on Chartjs plugin - http://www.chartjs.org/
    var activitiesChart = function () {
        if ($('#kt_chart_activities').length == 0) {
            return;
        }

        var ctx = document.getElementById("kt_chart_activities").getContext("2d");

        var gradient = ctx.createLinearGradient(0, 0, 0, 240);
        gradient.addColorStop(0, Chart.helpers.color('#e14c86').alpha(1).rgbString());
        gradient.addColorStop(1, Chart.helpers.color('#e14c86').alpha(0.3).rgbString());

        var config = {
            type: 'line',
            data: {
                labels: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October"],
                datasets: [{
                    label: "Sales Stats",
                    backgroundColor: Chart.helpers.color('#e14c86').alpha(1).rgbString(),  //gradient
                    borderColor: '#e13a58',

                    pointBackgroundColor: Chart.helpers.color('#000000').alpha(0).rgbString(),
                    pointBorderColor: Chart.helpers.color('#000000').alpha(0).rgbString(),
                    pointHoverBackgroundColor: KTApp.getStateColor('light'),
                    pointHoverBorderColor: Chart.helpers.color('#ffffff').alpha(0.1).rgbString(),

                    //fill: 'start',
                    data: [
                        10, 14, 12, 16, 9, 11, 13, 9, 13, 15
                    ]
                }]
            },
            options: {
                title: {
                    display: false,
                },
                tooltips: {
                    mode: 'nearest',
                    intersect: false,
                    position: 'nearest',
                    xPadding: 10,
                    yPadding: 10,
                    caretPadding: 10
                },
                legend: {
                    display: false
                },
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    xAxes: [{
                        display: false,
                        gridLines: false,
                        scaleLabel: {
                            display: true,
                            labelString: 'Month'
                        }
                    }],
                    yAxes: [{
                        display: false,
                        gridLines: false,
                        scaleLabel: {
                            display: true,
                            labelString: 'Value'
                        },
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                },
                elements: {
                    line: {
                        tension: 0.0000001
                    },
                    point: {
                        radius: 4,
                        borderWidth: 12
                    }
                },
                layout: {
                    padding: {
                        left: 0,
                        right: 0,
                        top: 10,
                        bottom: 0
                    }
                }
            }
        };

        var chart = new Chart(ctx, config);
    }

    // Bandwidth Charts 1.
    // Based on Chartjs plugin - http://www.chartjs.org/
    var bandwidthChart1 = function () {
        if ($('#kt_chart_bandwidth1').length == 0) {
            return;
        }

        var ctx = document.getElementById("kt_chart_bandwidth1").getContext("2d");

        var gradient = ctx.createLinearGradient(0, 0, 0, 240);
        gradient.addColorStop(0, Chart.helpers.color('#d1f1ec').alpha(1).rgbString());
        gradient.addColorStop(1, Chart.helpers.color('#d1f1ec').alpha(0.3).rgbString());


        $.getJSON(route('API_Call', {method: 'daily_sales'}), {})
            .done(function (data) {

                var labels = [];
                var values = [];

                $.each(data, function (key, value) {
                    labels.push(key);
                    values.push(value);
                });


                var config = {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: "Total Sales",
                            backgroundColor: gradient,
                            borderColor: KTApp.getStateColor('success'),

                            pointBackgroundColor: Chart.helpers.color('#000000').alpha(0).rgbString(),
                            pointBorderColor: Chart.helpers.color('#000000').alpha(0).rgbString(),
                            pointHoverBackgroundColor: KTApp.getStateColor('danger'),
                            pointHoverBorderColor: Chart.helpers.color('#000000').alpha(0.1).rgbString(),

                            //fill: 'start',
                            data: values
                        }]
                    },
                    options: {
                        title: {
                            display: false,
                        },
                        tooltips: {
                            mode: 'nearest',
                            intersect: false,
                            position: 'nearest',
                            xPadding: 10,
                            yPadding: 10,
                            caretPadding: 10
                        },
                        legend: {
                            display: false
                        },
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            xAxes: [{
                                display: false,
                                gridLines: false,
                                scaleLabel: {
                                    display: true,
                                    labelString: 'Month'
                                }
                            }],
                            yAxes: [{
                                display: false,
                                gridLines: false,
                                scaleLabel: {
                                    display: true,
                                    labelString: 'Value'
                                },
                                ticks: {
                                    beginAtZero: true
                                }
                            }]
                        },
                        elements: {
                            line: {
                                tension: 0.0000001
                            },
                            point: {
                                radius: 4,
                                borderWidth: 12
                            }
                        },
                        layout: {
                            padding: {
                                left: 11,
                                right: 11,
                                top: 10,
                                bottom: 0
                            }
                        }
                    }
                };

                var chart = new Chart(ctx, config);


            });


    }


    // Bandwidth Charts 2.
    // Based on Chartjs plugin - http://www.chartjs.org/
    var bandwidthChart2 = function () {
        if ($('#kt_chart_bandwidth2').length == 0) {
            return;
        }

        var ctx = document.getElementById("kt_chart_bandwidth2").getContext("2d");

        var gradient = ctx.createLinearGradient(0, 0, 0, 240);
        gradient.addColorStop(0, Chart.helpers.color('#ffefce').alpha(1).rgbString());
        gradient.addColorStop(1, Chart.helpers.color('#ffefce').alpha(0.3).rgbString());

        var config = {
            type: 'line',
            data: {
                labels: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October"],
                datasets: [{
                    label: "Bandwidth Stats",
                    backgroundColor: gradient,
                    borderColor: KTApp.getStateColor('warning'),
                    pointBackgroundColor: Chart.helpers.color('#000000').alpha(0).rgbString(),
                    pointBorderColor: Chart.helpers.color('#000000').alpha(0).rgbString(),
                    pointHoverBackgroundColor: KTApp.getStateColor('danger'),
                    pointHoverBorderColor: Chart.helpers.color('#000000').alpha(0.1).rgbString(),

                    //fill: 'start',
                    data: [
                        10, 14, 12, 16, 9, 11, 13, 9, 13, 15
                    ]
                }]
            },
            options: {
                title: {
                    display: false,
                },
                tooltips: {
                    mode: 'nearest',
                    intersect: false,
                    position: 'nearest',
                    xPadding: 10,
                    yPadding: 10,
                    caretPadding: 10
                },
                legend: {
                    display: false
                },
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    xAxes: [{
                        display: false,
                        gridLines: false,
                        scaleLabel: {
                            display: true,
                            labelString: 'Month'
                        }
                    }],
                    yAxes: [{
                        display: false,
                        gridLines: false,
                        scaleLabel: {
                            display: true,
                            labelString: 'Value'
                        },
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                },
                elements: {
                    line: {
                        tension: 0.0000001
                    },
                    point: {
                        radius: 4,
                        borderWidth: 12
                    }
                },
                layout: {
                    padding: {
                        left: 0,
                        right: 0,
                        top: 10,
                        bottom: 0
                    }
                }
            }
        };

        var chart = new Chart(ctx, config);
    }

    // Bandwidth Charts 2.
    // Based on Chartjs plugin - http://www.chartjs.org/
    var adWordsStat = function () {
        if ($('#kt_chart_adwords_stats').length == 0) {
            return;
        }

        var ctx = document.getElementById("kt_chart_adwords_stats").getContext("2d");

        var gradient = ctx.createLinearGradient(0, 0, 0, 240);
        gradient.addColorStop(0, Chart.helpers.color('#ffefce').alpha(1).rgbString());
        gradient.addColorStop(1, Chart.helpers.color('#ffefce').alpha(0.3).rgbString());

        var config = {
            type: 'line',
            data: {
                labels: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October"],
                datasets: [{
                    label: "AdWord Clicks",
                    backgroundColor: KTApp.getStateColor('brand'),
                    borderColor: KTApp.getStateColor('brand'),

                    pointBackgroundColor: Chart.helpers.color('#000000').alpha(0).rgbString(),
                    pointBorderColor: Chart.helpers.color('#000000').alpha(0).rgbString(),
                    pointHoverBackgroundColor: KTApp.getStateColor('danger'),
                    pointHoverBorderColor: Chart.helpers.color('#000000').alpha(0.1).rgbString(),
                    data: [
                        12, 16, 9, 18, 13, 12, 18, 12, 15, 17
                    ]
                }, {
                    label: "AdWords Views",

                    backgroundColor: KTApp.getStateColor('success'),
                    borderColor: KTApp.getStateColor('success'),

                    pointBackgroundColor: Chart.helpers.color('#000000').alpha(0).rgbString(),
                    pointBorderColor: Chart.helpers.color('#000000').alpha(0).rgbString(),
                    pointHoverBackgroundColor: KTApp.getStateColor('danger'),
                    pointHoverBorderColor: Chart.helpers.color('#000000').alpha(0.1).rgbString(),
                    data: [
                        10, 14, 12, 16, 9, 11, 13, 9, 13, 15
                    ]
                }]
            },
            options: {
                title: {
                    display: false,
                },
                tooltips: {
                    mode: 'nearest',
                    intersect: false,
                    position: 'nearest',
                    xPadding: 10,
                    yPadding: 10,
                    caretPadding: 10
                },
                legend: {
                    display: false
                },
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    xAxes: [{
                        display: false,
                        gridLines: false,
                        scaleLabel: {
                            display: true,
                            labelString: 'Month'
                        }
                    }],
                    yAxes: [{
                        stacked: true,
                        display: false,
                        gridLines: false,
                        scaleLabel: {
                            display: true,
                            labelString: 'Value'
                        },
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                },
                elements: {
                    line: {
                        tension: 0.0000001
                    },
                    point: {
                        radius: 4,
                        borderWidth: 12
                    }
                },
                layout: {
                    padding: {
                        left: 0,
                        right: 0,
                        top: 10,
                        bottom: 0
                    }
                }
            }
        };

        var chart = new Chart(ctx, config);
    }

    // Bandwidth Charts 2.
    // Based on Chartjs plugin - http://www.chartjs.org/
    var financeSummary = function () {
        if ($('#kt_chart_finance_summary').length == 0) {
            return;
        }

        var ctx = document.getElementById("kt_chart_finance_summary").getContext("2d");

        var config = {
            type: 'line',
            data: {
                labels: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October"],
                datasets: [{
                    label: "AdWords Views",

                    backgroundColor: KTApp.getStateColor('success'),
                    borderColor: KTApp.getStateColor('success'),

                    pointBackgroundColor: Chart.helpers.color('#000000').alpha(0).rgbString(),
                    pointBorderColor: Chart.helpers.color('#000000').alpha(0).rgbString(),
                    pointHoverBackgroundColor: KTApp.getStateColor('danger'),
                    pointHoverBorderColor: Chart.helpers.color('#000000').alpha(0.1).rgbString(),
                    data: [
                        10, 14, 12, 16, 9, 11, 13, 9, 13, 15
                    ]
                }]
            },
            options: {
                title: {
                    display: false,
                },
                tooltips: {
                    mode: 'nearest',
                    intersect: false,
                    position: 'nearest',
                    xPadding: 10,
                    yPadding: 10,
                    caretPadding: 10
                },
                legend: {
                    display: false
                },
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    xAxes: [{
                        display: false,
                        gridLines: false,
                        scaleLabel: {
                            display: true,
                            labelString: 'Month'
                        }
                    }],
                    yAxes: [{
                        display: false,
                        gridLines: false,
                        scaleLabel: {
                            display: true,
                            labelString: 'Value'
                        },
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                },
                elements: {
                    line: {
                        tension: 0.0000001
                    },
                    point: {
                        radius: 4,
                        borderWidth: 12
                    }
                },
                layout: {
                    padding: {
                        left: 0,
                        right: 0,
                        top: 10,
                        bottom: 0
                    }
                }
            }
        };

        var chart = new Chart(ctx, config);
    }

    // Order Statistics.
    // Based on Chartjs plugin - http://www.chartjs.org/
    var orderStatistics = function () {
        var container = KTUtil.getByID('kt_chart_order_statistics');

        if (!container) {
            return;
        }

        var MONTHS = ['1 Jan', '2 Jan', '3 Jan', '4 Jan', '5 Jan', '6 Jan', '7 Jan'];

        var color = Chart.helpers.color;
        var barChartData = {
            labels: ['1 Jan', '2 Jan', '3 Jan', '4 Jan', '5 Jan', '6 Jan', '7 Jan'],
            datasets: [
                {
                    fill: true,
                    //borderWidth: 0,
                    backgroundColor: color(KTApp.getStateColor('brand')).alpha(0.6).rgbString(),
                    borderColor: color(KTApp.getStateColor('brand')).alpha(0).rgbString(),

                    pointHoverRadius: 4,
                    pointHoverBorderWidth: 12,
                    pointBackgroundColor: Chart.helpers.color('#000000').alpha(0).rgbString(),
                    pointBorderColor: Chart.helpers.color('#000000').alpha(0).rgbString(),
                    pointHoverBackgroundColor: KTApp.getStateColor('brand'),
                    pointHoverBorderColor: Chart.helpers.color('#000000').alpha(0.1).rgbString(),

                    data: [20, 30, 20, 40, 30, 60, 30]
                },
                {
                    fill: true,
                    //borderWidth: 0,
                    backgroundColor: color(KTApp.getStateColor('brand')).alpha(0.2).rgbString(),
                    borderColor: color(KTApp.getStateColor('brand')).alpha(0).rgbString(),

                    pointHoverRadius: 4,
                    pointHoverBorderWidth: 12,
                    pointBackgroundColor: Chart.helpers.color('#000000').alpha(0).rgbString(),
                    pointBorderColor: Chart.helpers.color('#000000').alpha(0).rgbString(),
                    pointHoverBackgroundColor: KTApp.getStateColor('brand'),
                    pointHoverBorderColor: Chart.helpers.color('#000000').alpha(0.1).rgbString(),

                    data: [15, 40, 15, 30, 40, 30, 50]
                }
            ]
        };

        var ctx = container.getContext('2d');
        var chart = new Chart(ctx, {
            type: 'line',
            data: barChartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: false,
                scales: {
                    xAxes: [{
                        categoryPercentage: 0.35,
                        barPercentage: 0.70,
                        display: true,
                        scaleLabel: {
                            display: false,
                            labelString: 'Month'
                        },
                        gridLines: false,
                        ticks: {
                            display: true,
                            beginAtZero: true,
                            fontColor: KTApp.getBaseColor('shape', 3),
                            fontSize: 13,
                            padding: 10
                        }
                    }],
                    yAxes: [{
                        categoryPercentage: 0.35,
                        barPercentage: 0.70,
                        display: true,
                        scaleLabel: {
                            display: false,
                            labelString: 'Value'
                        },
                        gridLines: {
                            color: KTApp.getBaseColor('shape', 2),
                            drawBorder: false,
                            offsetGridLines: false,
                            drawTicks: false,
                            borderDash: [3, 4],
                            zeroLineWidth: 1,
                            zeroLineColor: KTApp.getBaseColor('shape', 2),
                            zeroLineBorderDash: [3, 4]
                        },
                        ticks: {
                            max: 70,
                            stepSize: 10,
                            display: true,
                            beginAtZero: true,
                            fontColor: KTApp.getBaseColor('shape', 3),
                            fontSize: 13,
                            padding: 10
                        }
                    }]
                },
                title: {
                    display: false
                },
                hover: {
                    mode: 'index'
                },
                tooltips: {
                    enabled: true,
                    intersect: false,
                    mode: 'nearest',
                    bodySpacing: 5,
                    yPadding: 10,
                    xPadding: 10,
                    caretPadding: 0,
                    displayColors: false,
                    backgroundColor: KTApp.getStateColor('brand'),
                    titleFontColor: '#ffffff',
                    cornerRadius: 4,
                    footerSpacing: 0,
                    titleSpacing: 0
                },
                layout: {
                    padding: {
                        left: 0,
                        right: 0,
                        top: 5,
                        bottom: 5
                    }
                }
            }
        });
    }

    // Quick Stat Charts
    var quickStats = function () {
        _initSparklineChart($('#kt_chart_quick_stats_1'), [10, 14, 18, 11, 9, 12, 14, 17, 18, 14], KTApp.getStateColor('brand'), 3);
        _initSparklineChart($('#kt_chart_quick_stats_2'), [11, 12, 18, 13, 11, 12, 15, 13, 19, 15], KTApp.getStateColor('danger'), 3);
        _initSparklineChart($('#kt_chart_quick_stats_3'), [12, 12, 18, 11, 15, 12, 13, 16, 11, 18], KTApp.getStateColor('success'), 3);
        _initSparklineChart($('#kt_chart_quick_stats_4'), [11, 9, 13, 18, 13, 15, 14, 13, 18, 15], KTApp.getStateColor('success'), 3);
    }

    // Daterangepicker Init
    var daterangepickerInit = function () {
        if ($('#kt_dashboard_daterangepicker').length == 0) {
            return;
        }

        var picker = $('#kt_dashboard_daterangepicker');
        var start = moment();
        var end = moment();

        function cb(start, end, label) {
            var title = '';
            var range = '';

            if ((end - start) < 100 || label == 'Today') {
                title = 'Today:';
                range = start.format('MMM D');
            } else if (label == 'Yesterday') {
                title = 'Yesterday:';
                range = start.format('MMM D');
            } else {
                range = start.format('MMM D') + ' - ' + end.format('MMM D');
            }

            $('#kt_dashboard_daterangepicker_date').html(range);
            $('#kt_dashboard_daterangepicker_title').html(title);
        }

       /* picker.daterangepicker({
            direction: KTUtil.isRTL(),
            startDate: start,
            endDate: end,
            opens: 'left',
            ranges: {
                /!* 'Today': [moment(), moment()],
                 'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                 'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                 'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                 'This Month': [moment().startOf('month'), moment().endOf('month')],
                 'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]*!/
            }
        }, cb);*/

        cb(start, end, '');
    }

    // Latest Orders
    var datatableLatestOrders = function () {
        if ($('#kt_datatable_todays_invoices[data-dashboard="true"]').length === 0) {
            return;
        }

        var dataJSONArray;


        $.getJSON(route('API_Call', {method: 'todays_invoices'}), {})
            .done(function (data) {


                dataJSONArray = data;


                var datatable = $('#kt_datatable_todays_invoices').KTDatatable({
                    data: {
                        type: 'local',
                        source: dataJSONArray,
                        pageSize: 10,
                        saveState: {
                            cookie: false,
                            webstorage: true
                        },
                        serverPaging: false,
                        serverFiltering: false,
                        serverSorting: false
                    },

                    layout: {
                        scroll: true,
                        height: 500,
                        footer: false
                    },

                    sortable: true,

                    filterable: false,

                    pagination: true,

                    columns: [

                        {
                            field: "RecordID",
                            title: "#",
                            sortable: false,
                            width: 40,
                            selector: {
                                class: 'kt-checkbox--solid'
                            },
                            textAlign: 'center'
                        }, {
                            field: "invoice_no",
                            title: "INVOICE NUMBER",
                            width: 'auto',
                            autoHide: false,
                            // callback function support for column rendering
                            template: function (data, i) {

                                var response = data;
                                var edit_url = erpUrl("sales/customer_invoice.php")+"?ModifyInvoice="+response.trans_no;

                                if(response.payment_flag != "0" && response.payment_flag != "3") {
                                    edit_url += "&is_tadbeer=1&show_items=ts";
                                }

                                if(response.payment_flag == "4" || response.payment_flag == "5") {
                                    edit_url += "&is_tadbeer=1&show_items=tb";
                                }


                                var output = '\
                        <div class="kt-user-card-v2">\
                            \
                            <div class="kt-user-card-v2__details">\
                                <a href="'+edit_url+'" class="kt-user-card-v2__name">' + data.invoice_no + '</a>\
                               \
                            </div>\
                        </div>';

                                return output;
                            }
                        },

                        {
                            field: "customer_name",
                            title: "CUSTOMER NAME",
                            width: 'auto',
                            autoHide: false,
                            // callback function support for column rendering
                            template: function (data, i) {


                                var output = '\
                        <div class="kt-user-card-v2">\
                            \
                            <div class="kt-user-card-v2__details">\
                                <a href="#" class="kt-user-card-v2__name">' + data.customer_name + '</a>\
                               \
                            </div>\
                        </div>';

                                return output;
                            }
                        },

                        {
                            field: "display_customer",
                            title: "DISPLAY CUSTOMER",
                            width: 'auto',
                            autoHide: false,
                            // callback function support for column rendering
                            template: function (data, i) {


                                var output = '\
                        <div class="kt-user-card-v2">\
                            \
                            <div class="kt-user-card-v2__details">\
                                <a href="#" class="kt-user-card-v2__name">' + data.display_customer + '</a>\
                               \
                            </div>\
                        </div>';

                                return output;
                            }
                        },

                        {
                            field: "invoice_amount",
                            title: "AMOUNT",
                            width: 'auto',
                            autoHide: false,
                            // callback function support for column rendering
                            template: function (data, i) {


                                var output = '\
                        <div class="kt-user-card-v2">\
                            \
                            <div class="kt-user-card-v2__details">\
                                <a href="#" class="kt-user-card-v2__name">' + data.invoice_amount + '</a>\
                               \
                            </div>\
                        </div>';

                                return output;
                            }
                        },

                        {
                            field: "payment_status",
                            title: "PAYMENT STATUS",
                            width: 'auto',
                            autoHide: false,
                            // callback function support for column rendering
                            template: function (data, i) {


                                var output = '\
                        <div class="kt-user-card-v2">\
                            \
                            <div class="kt-user-card-v2__details">\
                                <a href="#" class="kt-user-card-v2__name">' + data.payment_status + '</a>\
                               \
                            </div>\
                        </div>';

                                return output;
                            }
                        },

                        {
                            field: "real_name",
                            title: "CREATED BY",
                            width: 'auto',
                            autoHide: false,
                            // callback function support for column rendering
                            template: function (data, i) {


                                var output = '\
                        <div class="kt-user-card-v2">\
                            \
                            <div class="kt-user-card-v2__details">\
                                <a href="#" class="kt-user-card-v2__name">' + data.real_name + '</a>\
                               \
                            </div>\
                        </div>';

                                return output;
                            }
                        }

                    ]
                });


            });


    }



    var topTenServices = function () {
        if ($('#kt_datatable_top_ten_services').length === 0) {
            return;
        }

        var dataJSONArray;

        var datatable = $('#kt_datatable_top_ten_services').KTDatatable({
            data: {
                type: 'remote',
                source: {
                    read: {
                        url: route('API_Call', {method: 'top_ten_services'}),
                        // sample custom headers
                        headers: {},
                        map: function(raw) {
                            // sample data mapping
                            var dataSet = raw;
                            if (typeof raw.data !== 'undefined') {
                                dataSet = raw.data;
                            }
                            return dataSet;
                        },
                    },
                },
                pageSize: 10,
                serverPaging: true,
                serverFiltering: true,
                serverSorting: true,
            },

            // layout definition
            layout: {
                scroll: false,
                footer: false,
            },

            // column sorting
            sortable: true,

            pagination: false,

            columns: [

                {
                    field: "description",
                    title: "SERVICE NAME",
                    width: 'auto',
                    autoHide: false,
                    // callback function support for column rendering
                    template: function (data, i) {


                        var output = '\
                        <div class="kt-user-card-v2">\
                            \
                            <div class="kt-user-card-v2__details">\
                                <a href="#" class="kt-user-card-v2__name">' + data.description + '</a>\
                               \
                            </div>\
                        </div>';

                        return output;
                    }
                },

                {
                    field: "total",
                    title: "SALES",
                    width: 'auto',
                    autoHide: false,
                    // callback function support for column rendering
                    template: function (data, i) {


                        var output = '\
                        <div class="kt-user-card-v2">\
                            \
                            <div class="kt-user-card-v2__details">\
                                <a href="#" class="kt-user-card-v2__name">' + data.total + '</a>\
                               \
                            </div>\
                        </div>';

                        return output;
                    }
                },

                {
                    field: "qty",
                    title: "QTY",
                    width: 'auto',
                    autoHide: false,
                    // callback function support for column rendering
                    template: function (data, i) {


                        var output = '\
                        <div class="kt-user-card-v2">\
                            \
                            <div class="kt-user-card-v2__details">\
                                <a href="#" class="kt-user-card-v2__name">' + data.qty + '</a>\
                               \
                            </div>\
                        </div>';

                        return output;
                    }
                },


                // {
                //     field: "costs",
                //     title: "COST",
                //     width: 'auto',
                //     autoHide: false,
                //     // callback function support for column rendering
                //     template: function (data, i) {
                //
                //
                //         var output = '\
                //         <div class="kt-user-card-v2">\
                //             \
                //             <div class="kt-user-card-v2__details">\
                //                 <a href="#" class="kt-user-card-v2__name">' + data.costs + '</a>\
                //                \
                //             </div>\
                //         </div>';
                //
                //         return output;
                //     }
                // }


            ]
        });
    }


    var categorySalesReport = function () {
        if ($('#kt_datatable_category_sales_report').length === 0) {
            return;
        }

        var dataJSONArray;


        $.getJSON(route('API_Call', {method: 'category_sales_report'}), {})
            .done(function (data) {


                dataJSONArray = data;


                var datatable = $('#kt_datatable_category_sales_report').KTDatatable({
                    data: {
                        type: 'local',
                        source: dataJSONArray,
                        pageSize: 10,
                        saveState: {
                            cookie: false,
                            webstorage: true
                        },
                        serverPaging: false,
                        serverFiltering: false,
                        serverSorting: false
                    },

                    layout: {
                        scroll: true,
                        height: 500,
                        footer: false
                    },

                    sortable: true,

                    filterable: false,

                    pagination: true,

                    columns: [

                        {
                            field: "RecordID",
                            title: "#",
                            sortable: false,
                            width: 40,
                            selector: {
                                class: 'kt-checkbox--solid'
                            },
                            textAlign: 'center'
                        }, {
                            field: "description",
                            title: "Category",
                            width: 'auto',
                            autoHide: false,
                            // callback function support for column rendering
                            template: function (data, i) {


                                var output = '\
                        <div class="kt-user-card-v2">\
                            \
                            <div class="kt-user-card-v2__details">\
                                <a href="#" class="kt-user-card-v2__name">' + data.description + '</a>\
                               \
                            </div>\
                        </div>';

                                return output;
                            }
                        },

                        {
                            field: "inv_count",
                            title: "QTY",
                            width: 'auto',
                            autoHide: false,
                            // callback function support for column rendering
                            template: function (data, i) {


                                var output = '\
                        <div class="kt-user-card-v2">\
                            \
                            <div class="kt-user-card-v2__details">\
                                <a href="#" class="kt-user-card-v2__name">' + data.inv_count + '</a>\
                               \
                            </div>\
                        </div>';

                                return output;
                            }
                        },

                        {
                            field: "service_charge",
                            title: "Service Charge",
                            width: 'auto',
                            autoHide: false,
                            // callback function support for column rendering
                            template: function (data, i) {


                                var output = '\
                        <div class="kt-user-card-v2">\
                            \
                            <div class="kt-user-card-v2__details">\
                                <a href="#" class="kt-user-card-v2__name">' + data.service_charge + '</a>\
                               \
                            </div>\
                        </div>';

                                return output;
                            }
                        }

                    ]
                });


            });


    }



    // Calendar Init
    var calendarInit = function () {
        if ($('#kt_calendar').length === 0) {
            return;
        }

        var todayDate = moment().startOf('day');
        var YM = todayDate.format('YYYY-MM');
        var YESTERDAY = todayDate.clone().subtract(1, 'day').format('YYYY-MM-DD');
        var TODAY = todayDate.format('YYYY-MM-DD');
        var TOMORROW = todayDate.clone().add(1, 'day').format('YYYY-MM-DD');

        $('#kt_calendar').fullCalendar({
            isRTL: KTUtil.isRTL(),
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay,listWeek'
            },
            editable: true,
            eventLimit: true, // allow "more" link when too many events
            navLinks: true,
            defaultDate: moment('2017-09-15'),
            events: [
                {
                    title: 'Meeting',
                    start: moment('2017-08-28'),
                    description: 'Lorem ipsum dolor sit incid idunt ut',
                    className: "fc-event-light fc-event-solid-warning"
                },
                {
                    title: 'Conference',
                    description: 'Lorem ipsum dolor incid idunt ut labore',
                    start: moment('2017-08-29T13:30:00'),
                    end: moment('2017-08-29T17:30:00'),
                    className: "fc-event-success"
                },
                {
                    title: 'Dinner',
                    start: moment('2017-08-30'),
                    description: 'Lorem ipsum dolor sit tempor incid',
                    className: "fc-event-light  fc-event-solid-danger"
                },
                {
                    title: 'All Day Event',
                    start: moment('2017-09-01'),
                    description: 'Lorem ipsum dolor sit incid idunt ut',
                    className: "fc-event-danger fc-event-solid-focus"
                },
                {
                    title: 'Reporting',
                    description: 'Lorem ipsum dolor incid idunt ut labore',
                    start: moment('2017-09-03T13:30:00'),
                    end: moment('2017-09-04T17:30:00'),
                    className: "fc-event-success"
                },
                {
                    title: 'Company Trip',
                    start: moment('2017-09-05'),
                    end: moment('2017-09-07'),
                    description: 'Lorem ipsum dolor sit tempor incid',
                    className: "fc-event-primary"
                },
                {
                    title: 'ICT Expo 2017 - Product Release',
                    start: moment('2017-09-09'),
                    description: 'Lorem ipsum dolor sit tempor inci',
                    className: "fc-event-light fc-event-solid-primary"
                },
                {
                    title: 'Dinner',
                    start: moment('2017-09-12'),
                    description: 'Lorem ipsum dolor sit amet, conse ctetur'
                },
                {
                    id: 999,
                    title: 'Repeating Event',
                    start: moment('2017-09-15T16:00:00'),
                    description: 'Lorem ipsum dolor sit ncididunt ut labore',
                    className: "fc-event-danger"
                },
                {
                    id: 1000,
                    title: 'Repeating Event',
                    description: 'Lorem ipsum dolor sit amet, labore',
                    start: moment('2017-09-18T19:00:00'),
                },
                {
                    title: 'Conference',
                    start: moment('2017-09-20T13:00:00'),
                    end: moment('2017-09-21T19:00:00'),
                    description: 'Lorem ipsum dolor eius mod tempor labore',
                    className: "fc-event-success"
                },
                {
                    title: 'Meeting',
                    start: moment('2017-09-11'),
                    description: 'Lorem ipsum dolor eiu idunt ut labore'
                },
                {
                    title: 'Lunch',
                    start: moment('2017-09-18'),
                    className: "fc-event-info fc-event-solid-success",
                    description: 'Lorem ipsum dolor sit amet, ut labore'
                },
                {
                    title: 'Meeting',
                    start: moment('2017-09-24'),
                    className: "fc-event-warning",
                    description: 'Lorem ipsum conse ctetur adipi scing'
                },
                {
                    title: 'Happy Hour',
                    start: moment('2017-09-24'),
                    className: "fc-event-light fc-event-solid-focus",
                    description: 'Lorem ipsum dolor sit amet, conse ctetur'
                },
                {
                    title: 'Dinner',
                    start: moment('2017-09-24'),
                    className: "fc-event-solid-focus fc-event-light",
                    description: 'Lorem ipsum dolor sit ctetur adipi scing'
                },
                {
                    title: 'Birthday Party',
                    start: moment('2017-09-24'),
                    className: "fc-event-primary",
                    description: 'Lorem ipsum dolor sit amet, scing'
                },
                {
                    title: 'Company Event',
                    start: moment('2017-09-24'),
                    className: "fc-event-danger",
                    description: 'Lorem ipsum dolor sit amet, scing'
                },
                {
                    title: 'Click for Google',
                    url: 'http://google.com/',
                    start: moment('2017-09-26'),
                    className: "fc-event-solid-info fc-event-light",
                    description: 'Lorem ipsum dolor sit amet, labore'
                }
            ],

            eventRender: function (event, element) {
                if (element.hasClass('fc-day-grid-event')) {
                    element.data('content', event.description);
                    element.data('placement', 'top');
                    KTApp.initPopover(element);
                } else if (element.hasClass('fc-time-grid-event')) {
                    element.find('.fc-title').append('<div class="fc-description">' + event.description + '</div>');
                } else if (element.find('.fc-list-item-title').lenght !== 0) {
                    element.find('.fc-list-item-title').append('<div class="fc-description">' + event.description + '</div>');
                }
            }
        });
    }

    return {
        // Init demos
        init: function () {
            // init charts

            expensesChart();
            top_ten_customers();
            topTenServices();
            categorySalesReport();

            dailyCategorySalesCount();
            profitShare();
            salesStats();
            salesByApps();
            latestUpdates();
            trendsStats();
            trendsStats2();
            latestTrendsMap();
            revenueChange();
            supportCases();
            supportRequests();
            activitiesChart();
            bandwidthChart1();
            bandwidthChart2();
            adWordsStat();
            financeSummary();
            quickStats();
            orderStatistics();

            // init daterangepicker
            daterangepickerInit();

            // datatables
            datatableLatestOrders();

            // calendar
            calendarInit();


            // demo loading
            var loading = new KTDialog({'type': 'loader', 'placement': 'top center', 'message': 'Loading ...'});
            loading.show();

            setTimeout(function () {
                loading.hide();
            }, 3000);
        }
    };
}();

// Class initialization on page load
jQuery(document).ready(function () {
    KTDashboard.init();
});