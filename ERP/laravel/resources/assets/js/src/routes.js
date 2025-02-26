(() => {
    "use strict";

    /**
     * Build a fully qualified url
     *
     * @param {string} path
     * @param {object} query query parameters if any
     * @param {boolean} full whether to return the full url or just the path
     * @param {boolean} secure whether the url should be secure or not
     * @returns {string}
     */
    window.url = function(path, query, full, secure) {
        const sUrl = path.startsWith('http')
            ? path
            : joinPaths(config('root.url'), path);
        const oUrl = new URL(sUrl, (new URL(window.location.href)).origin);
        const hostName = oUrl.host;

        if (secure !== undefined) {
            oUrl.protocol = secure ? 'https:' : 'http:';
        }

        if (query) {
            buildURLQuery(query, null, oUrl.searchParams);
        }

        let result = oUrl.toString();

        if (!full) {
            result = result.substring(result.indexOf(hostName) + hostName.length);

            if (!result.startsWith('/')) {
                result = '/' + result;
            }
        }

        return result;
    }

    /**
     * Build a fully qualified url to the erp directory
     *
     * @param {string} path
     * @returns {string}
     */
    window.erpUrl = function(path) {
        return url(joinPaths('/ERP', path));
    }

    /**
     * Build a fully qualified url to the v3 route
     *
     * @param {string} path
     * @returns {string}
     */
    window.v3Url = function(path) {
        return  url(joinPaths('/v3', path));
    }

    /**
     * Build a url to the media path
     *
     * @param {string} path
     * @returns {string}
     */
    window.mediaUrl = function(path) {
        return url(joinPaths('/v3/media', path));
    }

    /**
     * Build a url to the themed image
     *
     * @param {string} path
     * @param {trur} themed if the image url should be themed or not
     */
    window.imageUrl = function(path, themed = true) {
        if (themed && inDarkMode()) {
            const dark = new RegExp(/-dark\.(jpg|svg|png)$/);
            const light = new RegExp(/\.(jpg|svg|png)$/);

            path = dark.test(path) ? path.replace(dark, ".$1") : path.replace(light, "-dark.$1");
        }

        return window.mediaUrl(path);
    }

    /**
     * Build a url to the illustration
     *
     * @param {string} path
     * @param {boolean} themed
     * @returns {string}
     */
    window.illustrationUrl = function(path, themed = true) {
        return window.imageUrl(
            joinPaths('illustrations/sketchy-1', path),
            themed
        );
    }

    /**
     * Build a fully qualified url to the given route
     *
     * @param {string} key The name of the route
     * @param {object} context The variables to replace the placeholders in the route
     * @return {string}
     * @throws {Error} thrown when the key is invalid or the context is missing but required
     */
    window.route = (function() {
        const routes = {
            'API_Call':                                         '/ERP/API/hub.php?method={method}&format=json',
            'API_Sales_Call':                                   '/ERP/API/hub.php?method={method}&format=json',
            'api.customers.select2':                            '/v3/api/customers/select2',
            'api.labours.select2':                              '/v3/api/labours/select2',
            'api.employees.documents.exists':                   '/v3/api/employees/documents/exists',
            'api.users.notifications':                          '/v3/api/user/notifications',
            'api.users.unreadNotifications':                    '/v3/api/user/unread-notifications',
            'broadcasting.auth':                                '/v3/broadcasting/auth',
            'api.users.readNotification':                       '/v3/api/user/notifications/{notification}/read',
            'api.users.unreadNotification':                     '/v3/api/user/notifications/{notification}/unread',
            'api.users.deleteNotification':                     '/v3/api/user/notifications/{notification}',
            'api.autofetch.pending':                            '/v3/api/autofetch/{systemId}/pending',
            'api.reception.createToken':                        '/v3/api/reception/token',
            'api.sales.invoice.findByReference':                '/v3/api/sales/invoice/by-reference/{reference}',
            'api.sales.reports.todaysInvoices':                 '/v3/api/sales/reports/todays-invoices',
            'api.sales.reports.todaysReceipts':                 '/v3/api/sales/reports/todays-receipts',
            'api.sales.reports.categoryGroupWiseDailyReport':   '/v3/api/sales/reports/category-group-wise-daily-sales',
            'api.sales.reports.categoryGroupWiseMonthlyReport': '/v3/api/sales/reports/category-group-wise-monthly-sales',
            'api.sales.reports.bankBalanceReportForManagement': '/v3/api/sales/reports/custom/bank-balances',
            'api.sales.reports.departmentWiseDailyCollection':  '/v3/api/sales/reports/department-wise-daily-collection',
            'api.sales.reports.departmentWiseMonthlyCollection':'/v3/api/sales/reports/department-wise-monthly-collection',
            'api.sales.reports.customerBalanceInquiry':         '/v3/api/sales/reports/customer-balance-inquiry',
            'api.sales.reports.dailyCollectionBreakdown':       '/v3/api/sales/reports/daily-collection-breakdown',
            'api.users.readAllNotification':                    '/v3/api/user/notifications/read-all',
            'api.system.amc.expiry':                            '/v3/api/system/amc/expiry',
            'api.system.amc.expiry.acknowledge':                '/v3/api/system/amc/expiry/acknowledge',
        }

        const Route = function (key, context, full, secure) {
            let route = routes[key];

            if (route === undefined) {
                throw new Error(`Argument 'key'(${key}) is invalid`);
            }

            if (route.indexOf('{') == -1) {
                return url(route, {}, full, secure);
            }

            if (context === undefined) {
                throw new Error(`Argument 'context' is undefined`)
            }

            const regexp = new RegExp('\{([^\}]*?)\}', 'g');
            const matches = route.matchAll(regexp);

            for (const match of matches) {
                const placeHolder = match[0];
                const _key = match[1];

                if (context[_key] === undefined || context[_key] === null) {
                    throw new Error(`Missing ${_key} in the context`);
                }

                route = route.replaceAll(placeHolder, context[_key])
            }

            return url(route, {}, full, secure);
        };

        Route.push = function(key, value) {
            routes[key] = value;
        }

        return Route;
    })();

    window.buildURLQuery = buildURLQuery;

    /**
     * Builds a query string
     *
     * @param {object} query
     * @param {string} prefix
     * @param {URLSearchParams} urlSearchParams
     *
     * @returns {URLSearchParams}
     */
    function buildURLQuery(query, prefix, urlSearchParams) {
        if (urlSearchParams === undefined) {
            urlSearchParams = new URLSearchParams();
        }

        for (const prop in query) {
            if (query.hasOwnProperty(prop)) {
                const key = prefix ? prefix + "[" + prop + "]" : prop;
                const val = query[prop];

                (val !== null && typeof val === "object")
                    ? buildURLQuery(val, key, urlSearchParams)
                    : urlSearchParams.set(key, val);
            }
        }

        return urlSearchParams;
    }

    /**
     * Join multiple path segments
     *
     * @param  {...string} paths
     * @returns {string}
     */
    function joinPaths(...paths) {
        if (paths[paths.length - 1] == undefined) {
            paths[paths.length - 1] = '';
        }

        const hasLeadingSlash = paths[0].startsWith('/');
        const hasTrailingSlash = paths[paths.length - 1].endsWith('/');

        let path = paths
            .map(
                _path => _path
                    .trim()
                    .replace(/^\/+/, '')
                    .replace(/\/+$/, '')
            )
            .filter(_path => _path && _path.length)
            .join('/');

        hasLeadingSlash && (path = '/'.concat(path));
        hasTrailingSlash && (path = path.concat('/'));

        return path;
    }
})();
