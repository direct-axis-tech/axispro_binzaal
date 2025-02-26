/**
 * Empty the HTML Element
 * 
 * @param {HTMLElement} elem
 */
 function empty(elem) {
    while (elem.firstChild)
        elem.removeChild(elem.firstChild);
}

/**
 * Sets/Unsets the busy state of the document
 * @param {bool} isBusy
 */
function setBusyState(isBusy = true) {
    var ajaxMark = document.getElementById('ajaxmark');
    if (window.documentBusyCount === undefined || window.documentBusyCount < 0) {
        window.documentBusyCount = 0;
    }

    if (isBusy) {
        window.documentBusyCount++;
        ajaxMark.style.visibility = 'visible';
    } else {
        window.documentBusyCount--;
        if (window.documentBusyCount <= 0) {
            ajaxMark.style.visibility = 'hidden';
        }
    }
}

/**
 * Unsets the busy state of the document
 */
function unsetBusyState() {
    setBusyState(false);
}

$(document).ready(function () {

    // get_current_QMS_token();


    // getUnreadNotificationCount();


    // setInterval(function () {

    //     getUnreadNotificationCount();

    // }, 3000);


    $(".axispro-lang-btn").click(function (e) {

    });


    $("#notification_icon").click(function () {


        getNotifications();

    });


});

$.fn.select2.amd.define(
    "select2/data/customArray",
    [
        'select2/data/array',
        'select2/utils'
    ],
    function(ArrayAdapter, Utils) {
        function CustomArrayAdapter ($element, options) {
            var data = options.get("data");
            var pageLength = options.get("pageLength");

            this._data = data ? Array.from(data) : this._items($element);
            this._pageLength = pageLength || 25;

            CustomArrayAdapter.__super__.constructor.call(this, $element, options);
        };

        Utils.Extend(CustomArrayAdapter, ArrayAdapter);

        CustomArrayAdapter.prototype.query = function (params, callback) {
            var pageLength = this._pageLength;
            var page = params.page || 1;
            var term = params.term;
            var pagedData = [];
            var data = this._data;

            if (!!term) {
                data = data.filter(function (elemData) {
                    return (elemData.text.toLowerCase().indexOf(term.toLowerCase()) > -1)
                });
            }

            pagedData = data.slice(
                (page - 1) * pageLength,
                page * pageLength
            );

            callback({
                results: pagedData,
                pagination: {
                    more: data.length >= (page * pageLength)
                }
            });
        };

        CustomArrayAdapter.prototype._items = function($element) {
            var data = [];
            var self = this;

            var $options = $element.children();

            $options.each(function () {
                if (
                    this.tagName.toLowerCase() !== 'option' &&
                    this.tagName.toLowerCase() !== 'optgroup'
                ) {
                    return;
                }

                var $option = $(this);
                var option = self.item($option);

                data.push(option);
            });

            return data;
        }

        return CustomArrayAdapter;
    }
);

$.fn.select2.amd.define(
    "select2/customResults",
    [
        'select2/utils',
        'select2/results',
        'select2/dropdown/infiniteScroll'
    ],
    function (Utils, ResultsList, InfiniteScroll) {
        var CustomResultsList = ResultsList;

        CustomResultsList = Utils.Decorate(
            CustomResultsList,
            InfiniteScroll
        );

        return CustomResultsList
    }
);

$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
    },
    error: function(xhr) {
        var response = xhr.responseJSON;
        if (xhr.status == 401 && response && response.redirect_to) {
            setTimeout(() => createPopup(response.redirect_to, "login"), 0);
        }
    }
})

window.CustomDataAdapter = $.fn.select2.amd.require("select2/data/customArray");
window.CustomResultAdapter = $.fn.select2.amd.require("select2/customResults");

/**
 * Initialise select2
 * @param {string} selector 
 * @param {object} options 
 */
function initializeSelect2(selector, options = {}) {
    var _options = $.extend(
        {},
        options,
        {
            dataAdapter: window.CustomDataAdapter,
            resultsAdapter: window.CustomResultAdapter
        }
    );

    $(selector).select2(_options);
}

function getNotifications() {


    $.ajax({
        url: route('API_Call', {method: 'getNotifications'}),
        method: 'GET',
        data: {
            status: 0
        },
        dataType: 'json'

    }).done(function (data) {

        if (data.length > 0) {

            $("#notification_popup").html("");

            $("#no_new_notification_div").hide();

            $.each(data, function (key, val) {

                var link = '#';

                if (val.link != '')
                    link = url('/') + val.link;

                var notification_html = '<a href="' + link + '" class="kt-notification__item">' +
                    '                                        <div class="kt-notification__item-icon">' +
                    '                                            <i class="flaticon2-line-chart kt-font-success"></i>' +
                    '                                        </div>' +
                    '                                        <div class="kt-notification__item-details">' +
                    '                                            <div class="kt-notification__item-title">' + val.description + '</div>' +
                    '                                            <div class="kt-notification__item-time">' + val.time_ago + '</div>' +
                    '                                        </div>' +
                    '                                    </a>'

                $("#notification_popup").prepend(notification_html);


            });

        }

        else {
            $("#no_new_notification_div").show();
        }

    });


}

function getUnreadNotificationCount() {
    return;
}


function get_current_QMS_token(callback) {

    //


}

function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}




