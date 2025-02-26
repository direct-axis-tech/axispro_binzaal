/**
 * Creates a new popup window
 *
 * @param {string} url The URL to open in the popup
 * @param {string} target The name of the window being opened
 * @param {number} width
 * @param {number} height
 * @param {boolean} scroll
 *
 * @returns {Window}
 */
window.createPopup = function createPopup(
    url = null,
    target = "_blank",
    width = 800,
    height = 600,
    scroll = true
) {
    const left = screen.width ? (screen.width - width) / 2 : 0;
    const top = screen.height ? (screen.height - height) / 2 : 0;
    const _scroll = scroll ? "yes" : "no";
    const config = `height=${height},width=${width},top=${top},left=${left},scrollbars=${_scroll},resizable`;

    return window.open(url, target, config);
};

/**
 * Sets/Unsets the busy state of the document
 * @param {bool} isBusy
 */
 window.setBusyState = (() => {
    // private variable with static memmory
    let busyCount = 0;

    return function setBusyState(isBusy = true) {
        var ajaxMark = document.getElementById('ajaxmark');
        if (busyCount === undefined || busyCount < 0) {
            busyCount = 0;
        }

        if (isBusy) {
            busyCount++;
            document.body.classList.add('is-busy');
            ajaxMark && (ajaxMark.style.visibility = 'visible');
        } else {
            busyCount--;
            if (busyCount <= 0) {
                document.body.classList.remove('is-busy');
                ajaxMark && (ajaxMark.style.visibility = 'hidden');
            }
        }
    }
})();

/**
 * Unsets the busy state of the document
 */
window.unsetBusyState = () => { window.setBusyState(false); }

/**
 * A minimal default error handler
 */
window.defaultErrorHandler = function(xhr = null) {
    let msg = "Something went wrong! Please try again or contact the administrator";
    
    if (xhr && xhr.responseJSON) {
        if (typeof xhr.responseJSON.message == 'string') {
            msg = xhr.responseJSON.message;
        }

        if (xhr.status == 422 && xhr.responseJSON.errors) {
            const errors = xhr.responseJSON.errors;
            if (Array.isArray(errors) && errors.every(el => typeof el === 'string')) {
                msg += '<br>'.concat(errors.join('<br>'));
            }

            else {
                for (const [key, val] of Object.entries(errors)) {
                    msg += '<br>'.concat(key, ': ', val.join('<br>'));
                }
            }
        }
    }
    toastr.error(msg);
}

/**
 * A wrapper function around jquery ajax
 * 
 * **Customisations**  
 * -------------------------------------  
 *  - Adds custom options
 *  - Adds csrf token for requests that are not reading
 *  - Adds an error handle middleware to detect common errors
 * 
 * **Custom options** 
 * -------------------------------------  
 * blocking: {boolean} determines if the ui should be blocked or not. defaults to true
 */
 window.ajaxRequest = (url, options) => {
    // If url is an object, simulate pre-1.5 signature
    if (typeof url === "object") {
        options = url;
        url = undefined;
    }

    // Force options to be an object
    options = options || {};

    const isBlockingRequest = (false !== options.blocking);
    const isEjecting = (true === options.eject)
    const _method = (options.method || options.type || 'get').toUpperCase();

    // delete out custom option before passing it to jquery
    delete options.blocking;

    let _token = document.querySelector('meta[name="csrf-token"]');
    if (_token && _token.getAttribute) {
        _token = _token.getAttribute("content");
    }

    if (
        ['HEAD', 'GET', 'OPTIONS'].indexOf(_method) === -1
        && _token
    ) {
        options.headers = {
            "X-CSRF-TOKEN" : _token,
            ...options.headers
        }
    }

    // Construct the options with defaults
    options = {
        dataType: 'json',
        url,
        ...options,

        // Default Headers
        headers: {
            "Accept": "application/json; charset=utf-8",
            ...options.headers
        }
    }

    if (isEjecting) {
        return options;
    }

    // before processing the request mark the page as busy
    if (isBlockingRequest) setBusyState();

    const xhr = $.ajax(options);

    // first failure interceptor. Checks if this is a login time out error and redirect to login page
    xhr.fail(function (_xhr) {
        // not Logged in
        if (_xhr.status == 401) {
            return setTimeout(() => createPopup(_xhr.responseJSON.redirect_to, "login"), 0);
        }

        // page expired or CSRF mismatch
        if (_xhr.status == 419) {
            return toastr.error('Expired! Please refresh the page')
        }

        // pass it to the next handler
        return $.Deferred().reject(...arguments)
    })

    // after completion of the request unblock the page
    if (isBlockingRequest) xhr.always(unsetBusyState);

    return xhr;
}

/**
 * Empty the HTML Element
 * 
 * @param {HTMLElement} elem
 */
 window.empty = (elem) => {
    while (elem.firstChild)
        elem.removeChild(elem.firstChild);
}

/**
 * Checks if the system is in dark mode or not
 * 
 * @returns {boolean}
 */
window.inDarkMode = () => {
    return false;
}

/**
 * Rounds a given number to specified decimal points
 * 
 * @param {number} number,
 * @param {number} places
 * @returns {number}
 */
window.round = (number, places) => {
    const denom = +(1 + ''.padEnd(places, '0'));
    return Math.round((+(number) + Number.EPSILON) * denom) / denom;
}

/**
 * Converts a hex color code to its rgba form
 * 
 * @param {string} hex The color in the HTML rgba format. Eg: #03f, #0033ff
 * @returns {number[]} The color in the HTML rgba format. Eg: [0, 51, 255]
 */
window.hexToRgb = hex => {
    // Expand shorthand form (e.g. "03F") to full form (e.g. "0033FF")
    let shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;
        hex = hex.replace(shorthandRegex, function(m, r, g, b) {
        return r + r + g + g + b + b;
    });

    let result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);

    return result
        ? [parseInt(result[1], 16), parseInt(result[2], 16), parseInt(result[3], 16)]
        : null;
}

/**
 * Converts a rgba color code to its hex form
 * 
 * @param {string} rgb The color in the HTML rgba format. Eg: rgba(0, 51, 255)
 * @returns {string} The color in the HTML rgba format. Eg: #0033ff
 */
window.rgbToHex = rgb => {
    rgba = rgba.match(/\d+/g);
    return "#" + (1 << 24 | rgba[0] << 16 | rgba[1] << 8 | rgba[2]).toString(16).slice(1);
}

/**
 * Returns the text color based on the background color
 * 
 * @param {string} color The background color in the rgb format
 * @returns {string} The text color in the hex format
 */
window.getTextColor = color => {
    let rgba = color.startsWith('#')
        ? window.hexToRgb(color)
        : color.match(/\d+/g);

    let threshold = (rgba[0]*0.299) + (rgba[1]*0.587) + (rgba[2]*0.114);
    let style = getComputedStyle(document.body);

    return style.getPropertyValue(threshold > 170 ? '--bs-dark' : '--bs-white');
}

/**
 * Returns the date format for specified key
 * 
 * @param {"momentJs|bsDatePicker"} key
 * @returns {string|null}
 */
window.dateFormat = key => {
    let metaTag;
    let format;

    switch (key) {
        case 'momentJs':
            metaTag = document.querySelector('meta[name="moment-date-format"]');
            break;
        case 'bsDatePicker':
            metaTag = document.querySelector('meta[name="bs-date-format"]');
            break;
    }

    if (metaTag && metaTag.getAttribute) {
        format = metaTag.getAttribute("content");
    }

    return format || null;
}

/**
 * Initializes customers select list with pagination
 * 
 * @param {string} selector The element selector
 * @param {object} options The options accepted by select2
 */
function initializeCustomersSelect2(selector, options) {
    // Force options and options.ajax to be objects
    options = options || {};
    options.ajax = options.ajax || {};
    const query = {};

    if (options.whereAccount) {
        query.account = options.whereAccount;
    }

    if (options.showInactive) {
        query.showInactive = options.showInactive;
    }

    if (options.whereDimension) {
        query.dimensionId = options.whereDimension;
    }

    if (options.except) {
        options.except.forEach((customer_id, index) => {
            query[`except[${index}]`] = customer_id;
        });;
    }

    // Extend the objects
    var _options = {
        placeholder: '-- select customer --',
        allowClear: true,
        ...options,
        ajax: {
            url: url(route('api.customers.select2', null, true), query),
            dataType: 'json',
            delay: 250,
            ...options.ajax,
        }
    };
        
    
    $(selector).select2(_options);
}

/**
 * Initializes labours select list with pagination
 * 
 * @param {string} selector The element selector
 * @param {object} options The options accepted by select2
 */
function initializeLaboursSelect2(selector, options) {
    // Force options and options.ajax to be objects
    options = options || {};
    options.ajax = options.ajax || {};
    const query = {};

    if (options.showInactive) {
        query.showInactive = options.showInactive;
    }

    if (options.agentId) {
        query.agentId = options.agentId;
    }

    if (options.nationality) {
        query.nationality = options.nationality;
    }

    if (options.categoryId) {
        query.categoryId = options.categoryId;
    }

    if (options.except) {
        options.except.forEach((customer_id, index) => {
            query[`except[${index}]`] = customer_id;
        });;
    }

    // Extend the objects
    var _options = {
        placeholder: '-- select maid --',
        allowClear: true,
        ...options,
        ajax: {
            url: url(route('api.labours.select2', null, true), query),
            dataType: 'json',
            delay: 250,
            ...options.ajax,
        }
    };

    $(selector).select2(_options);
}