// Document ready
$(function () {
    "use strict;"
    
    const notificationsWrapper = document.getElementById('notification-wrapper');

    // Guard
    if (!notificationsWrapper) return;

    // Define the notification class
    class AppNotification {
        /**
         * Creates a Notification object
         */
        constructor () {
            /**
             * Constants
             */
             this.EMPLOYEE_DOCUMENT_EXPIRING = 'App\\Notifications\\Hr\\DocumentExpiringNotification';
             this.EMPLOYEE_DOCUMENT_EXPIRED = 'App\\Notifications\\Hr\\DocumentExpiredNotification';
             this.LABOUR_INCOME_RECOGNIZED = 'App\\Notifications\\Labour\\IncomeRecognizedNotification';
             this.LABOUR_EXPENSE_RECOGNIZED = 'App\\Notifications\\Labour\\ExpenseRecognizedNotification';
             this.LEAVE_ACCRUAL_NOTIFICATION = 'App\\Notifications\\Hr\\LeaveAccrualNotification';
             this.GRATUITY_ACCRUAL_NOTIFICATION = 'App\\Notifications\\Hr\\GratuityAccrualNotification';
             this.TASK_INITIALIZED_NOTIFICATION = 'App\\Notifications\\TaskInitializedNotification';
             this.TASK_TRANSITIONED_NOTIFICATION = 'App\\Notifications\\TaskTransitionedNotification';
             this.TASK_APPROVED_NOTIFICATION = 'App\\Notifications\\TaskApprovedNotification';
             this.TASK_CANCELLED_NOTIFICATION = 'App\\Notifications\\TaskCancelledNotification';
             this.TASK_REJECTED_NOTIFICATION = 'App\\Notifications\\TaskRejectedNotification';
             this.LABOUR_INSTALLMENT_REMINDER = 'App\\Notifications\\Labour\\InstallmentReminderNotification';
             this.TRANSACTION_NOTIFICATION = 'App\\Notifications\\TransactionAssignedNotification';

            /**
             * Determines if the notification is already initialized
             * 
             * @type {boolean}
             * @private
             */
            this._initialized = false;

            /**
             * Data store
             * @type {object[]}
             * @private
             */
            this._data = [];

            /**
             * The currently authenticated user's id
             * @type {string}
             * @private
             */
            this._userId = null;

            /**
             * The next page's url
             * @type {string}
             * @private
             */
            this._next_page_url = null;

            /**
             * The count of unread notifications
             * @type {number}
             */
            this._unreadNotifications = 0;

            /**
             * All Unread notifications
             * @type {object[]}
             */
            this._unreadNotificationsDetails = [];

            /**
             * The notification wrapper component
             * @type {HTMLDivElement}
             */
            this.wrapper = notificationsWrapper;

            /**
             * The sorting option
             * @type {"time"|"unread"}
             */
            this.sort = "time";
        }

        /**
         * Refresh the UI
         */
        refresh() {
            const wrapper = this.wrapper;
            const data = this._getSorted();
            const scrollTop = this.wrapper.querySelector('.notifications')?.scrollTop;

            wrapper
                .querySelector(['[data-unread-notifications]'])
                .dataset
                .unreadNotifications = this._unreadNotifications;

            // empty the container
            empty(wrapper.querySelector('#alerts'));

            // Checks if there is any new notification
            if (!data.length) {
                wrapper.querySelector('#alerts').appendChild(this._createEmptyChild());
                return;
            }

            // Build the notification stack
            const fragment = this._createFragment();
            data.forEach(notification => {
                const notificationNode = this._createChild(notification);

                fragment.appendChild(notificationNode)
            });

            // Check if there is more
            if (this._next_page_url != null) {
                fragment.classList.add('has-more');
                fragment.appendChild(this._createNextPageControl());
            }

            // append the notification stack
            wrapper.querySelector('#alerts').appendChild(fragment);

            // reset the scroll position
            this._scrollTo(scrollTop);
        }

        /**
         * Initialize the notifications
         */
        init() {
            if (this._initialized) return;

            ajaxRequest({
                url: route('api.users.unreadNotifications'),
                method: 'get',
                blocking: false,
                dataType: 'json'
            })
            .then(unreadNotifications => {
                this._unreadNotifications = unreadNotifications.total || 0;
                this._unreadNotificationsDetails = unreadNotifications.data || [];

                return ajaxRequest({
                    url: route('api.users.notifications'),
                    method: 'get',
                    blocking: false,
                    dataType: 'json'
                })
            })
            .then(notifications => {
                this._data = notifications.data || [];
                this._next_page_url = notifications.next_page_url || null;
                this._userId = this.wrapper.dataset.userId;
                this._initialized = true;

                this._handleEvents();
                this.refresh();
            })
            .catch(defaultErrorHandler);
        }

        /**
         * Pushes a new notification
         * 
         * @param {object} notification 
         */
        push(notification) {
            this._data = [notification, ...this._data];
            this._incrementUnread();

            this.refresh();
        }

        /**
         * Opens the drawer
         */
        show() {
            KTMenu.getInstance(this.wrapper.closest('[data-kt-menu="true"]')).show(this.wrapper);
        }

        /**
         * Register the event handlers
         * 
         * @private
         */
        _handleEvents() {
            $(notificationsWrapper).on('click', '.has-more .btn-fetch-next', this._handleNextBtnClick.bind(this));
            $(notificationsWrapper).on('click', '[data-btn="read"]', this._handleReadBtnClick.bind(this));
            $(notificationsWrapper).on('click', '[data-btn="delete"]', this._handleDeleteBtnClick.bind(this));
            $(notificationsWrapper).on('click', '[data-btn-read]', this._updateReadStatus.bind(this));
            $(notificationsWrapper).on('click', '[data-action]', this._handleActionBtnClick.bind(this));
            $(notificationsWrapper).on('click', '.mark-all-as-read', this._updateReadAllStatus.bind(this));

            echo.private('user.' + this._userId)
                .notification(this.push.bind(this))
                .listen('System.NotificationRead', this._markAsRead.bind(this))
                .listen('System.NotificationUnread', this._markAsUnread.bind(this))
                .listen('System.NotificationDeleted', this._delete.bind(this));
        }

        /**
         * Handles the click for next notifications
         * 
         * @param {Event} event 
         * @private
         */
        _handleNextBtnClick(event) {
            event.stopPropagation();
            const btn = event.target;

            btn.disabled = true;
            btn.textContent = 'Fetching...'

            const spinner = $(`<span class="spinner-border spinner-border-sm me-3"></span>`)[0];
            btn.prepend(spinner);

            ajaxRequest({
                url: this._next_page_url,
                method: 'get',
                blocking: false,
                dataType: 'json'
            })
            .done(notifications => {
                this._data = notifications.data.concat(this._data);
                this._next_page_url = notifications.next_page_url;

                this.refresh();
                this.show();
            })
            .fail(defaultErrorHandler)
        }

        /**
         * Handles the click for marking notification as read or unread
         * 
         * @param {Event} event
         * @private
         */
        _handleReadBtnClick(event) {
            event.stopPropagation();
            const notification = this._getNotificationFromEvent(event);
            this._updateReadStatus(event, notification.read_at == null);
        }

        /**
         * Handles the click for deleting a notification
         * 
         * @param {Event} event
         * @private
         */
        _handleDeleteBtnClick(event) {
            event.stopPropagation();

            const btn = event.target;
            const notificationId = btn.closest('[data-notification-id]').dataset.notificationId;
            const notification = this._data.find(n => n.id == notificationId);
            const errorHandler = () => {
                defaultErrorHandler();
                notification.isBusy = false;
                this.refresh()
            }

            // Guard against multiple simultaneous clicks
            if (notification.isBusy) return;

            notification.isBusy = true;
            this.refresh();

            ajaxRequest({
                url: route('api.users.deleteNotification', {notification: notificationId}),
                method: 'delete',
                dataType: 'json',
                blocking: false
            })
            .done(() => {
                setTimeout(() => {
                    // if we don't receive a reply from the server for more than 5 sec
                    // abort and unblock
                    const notification = this._data.find(n => n.id == notificationId);
                    if (notification) {
                        errorHandler();
                    }
                }, 5000)
            })
            .fail(errorHandler)
        }

        /**
         * Handles the action button click 
         * 
         * @param {Event} event
         * @private
         */
        _handleActionBtnClick(event) {
            createPopup(event.target.dataset.action).focus();
        }

        /**
         * Scroll to the given position
         * 
         * @param {number} scrollTop 
         */
        _scrollTo(scrollTop = 0) {
            const notificationsDiv = this.wrapper.querySelector('.notifications');

            if (notificationsDiv) {
                notificationsDiv.scrollTop = scrollTop;
            }
        }

        /**
         * Increments the count of unread notification
         * 
         * @private
         */
        _incrementUnread() {
            const unread = parseInt(this._unreadNotifications || 0);

            this._unreadNotifications = unread + 1;
        }

        /**
         * Decrements the count of unread notification
         * 
         * @private
         */
        _decrementUnread() {
            const unread = parseInt(this._unreadNotifications || 0);

            if (unread == 0) return;

            this._unreadNotifications = unread - 1;
        }

        /**
         * Update the notification read status in the database
         * 
         * @param {Event} event
         * @param {boolean} isMarkingAsRead 
         * @returns 
         */
        _updateReadStatus(event, isMarkingAsRead = true) {
            event.stopPropagation();

            const notification = this._getNotificationFromEvent(event);
            const isNotificationRead = notification.read_at != null;

            // Guards against unwanted http request
            if (isNotificationRead == isMarkingAsRead) return;
            
            const errorHandler = () => {
                defaultErrorHandler();
                notification.isBusy = false;
                this.refresh()
            }

            const routeTo = isMarkingAsRead
                ? 'api.users.readNotification'
                : 'api.users.unreadNotification';

            // Guard against multiple simultaneous clicks
            if (notification.isBusy) return;

            notification.isBusy = true;

            this.refresh();

            ajaxRequest({
                url: route(routeTo, {notification: notification.id}),
                method: 'post',
                dataType: 'json',
                blocking: false
            })
            .done(() => {
                setTimeout(() => {
                    // if we don't receive a reply from the server for more than 5 sec
                    // abort and unblock
                    if (notification.isBusy == true) {
                        errorHandler();
                    }
                }, 5000)
            })
            .fail(errorHandler)
        }

        /**
         * Update all the notifications as read in the database
         * 
         * @param {Event} event
         * @returns 
         */
        _updateReadAllStatus(event) {
            event.stopPropagation();
            const $button = $(event.currentTarget);
            $button.prop('disabled', true);
            
            let unreadNotificationIds = this._unreadNotificationsDetails.map(notification => notification.id);
                 
            const errorHandler = () => {
                defaultErrorHandler();
                this.refresh()
            }

            ajaxRequest({
                url: route('api.users.readAllNotification'),
                method: 'post',
                dataType: 'json',
                blocking: false,
                data: { notifications: unreadNotificationIds }
            })
            .done(() => {
                this._unreadNotificationsDetails = [];
                this.refresh();
            })
            .fail(errorHandler)
            .always(() => {
                $button.prop('disabled', false);
            });
        }

        /**
         * Returns the notification object from the event
         * 
         * @param {Event} event 
         * @returns 
         */
        _getNotificationFromEvent(event) {
            const source = event.target;
            const notificationId = source.closest('[data-notification-id]').dataset.notificationId;
            return this._data.find(n => n.id == notificationId);
        }

        /**
         * Handles the server event for marked as read
         * 
         * @param {object} event the laravel event
         * @private
         */
        _markAsRead(event) {
            const notification = this._data.find(n => n.id == event.notification.id);
            if(notification) {
                notification.isBusy = false;
                notification.read_at = event.notification.read_at;
            }
            this._decrementUnread();
            this.refresh();
        }

        /**
         * Handles the server event for marked as unread
         * 
         * @param {object} event the laravel event
         * @private
         */
        _markAsUnread(event) {
            const notification = this._data.find(n => n.id == event.notification.id);
            const existsInUnread = this._unreadNotificationsDetails.some(n => n.id == event.notification.id);
            if (!existsInUnread) {
                this._unreadNotificationsDetails.push(notification);
            }
            notification.isBusy = false;
            notification.read_at = null;
            this._incrementUnread();
            this.refresh();
        }

        /**
         * Handles the server event for deleted
         * 
         * @param {object} event the laravel event
         * @private
         */
         _delete(event) {
            const notification = this._data.find(n => n.id == event.notification.id);

            if (notification.read_at == null) {
                this._decrementUnread();
            }

            this._data = this._data.filter(n => n.id != event.notification.id);
            this.refresh();
        }

        /**
         * Creates the child to show: if data is empty
         * 
         * @returns {HTMLDivElement}
         * @private
         */
        _createEmptyChild() {
            return $(
                `<div class="d-flex flex-column px-9">
                    <!--begin::Section-->
                    <div class="pt-10 pb-0">
                        <!--begin::Title-->
                        <h3 class="text-dark text-center fw-bolder">
                            'All Caught Up!'
                        </h3>
                        <!--end::Title-->
                    </div>
                    <!--end::Section-->

                    <!--begin::Illustration-->
                    <img class="mh-200px" alt="metronic" src="${illustrationUrl('1.png')}"/>
                    <!--end::Illustration-->
                </div>`
            )[0];
        }

        /**
         * Creates a button to fetch the next notifications
         * 
         * @returns {HTMLButtonElement}
         * @private
         */
        _createNextPageControl() {
            const btn = document.createElement('button');
            btn.classList.add('btn', 'btn-block', 'btn-light', 'text-primary', 'btn-fetch-next', 'rounded-0', 'w-100');
            btn.name = 'btnFetchNext'
            btn.textContent = "More";
            btn.type = 'button'

            return btn;
        }

        /**
         * Get the data sorted
         * 
         * @returns {object[]}
         * @private
         */
        _getSorted() {
            const data = this._data.slice();

            // If in the future we sort based on unread status we should do it here
            data.sort((a, b) => {
                if (a.created_at == b.created_at) return 0;

                return a.created_at > b.created_at ? -1 : 1; 
            })

            return data;
        }

        /**
         * Returns the wrapper for notifications
         * 
         * @returns {HTMLDivElement}
         * @private
         */
        _createFragment() {
            const div = document.createElement('div');
            div.classList.add('scroll-y', 'mh-325px', 'shadow-none', 'notifications');

            const unread = parseInt(this._unreadNotifications || 0);

            if (unread > 0) {
                const markAllAsReadDiv = document.createElement('div');
                markAllAsReadDiv.classList.add('text-right');
        
                const markAllAsRead = document.createElement('span');
                markAllAsRead.textContent = 'Mark All As Read';
                markAllAsRead.classList.add('mark-all-as-read', 'text-primary', 'cursor-pointer', 'btn', 'btn-bold');
        
                markAllAsReadDiv.appendChild(markAllAsRead);
                div.appendChild(markAllAsReadDiv);
            }

            return div
        }

        /**
         * Returns the UI not for the current notifications
         * 
         * @param {object} notification
         * @returns {HTMLDivElement}
         * @private
         */
        _createChild(notification) {
            const data = notification.data;

            switch (notification.type) {
                case this.EMPLOYEE_DOCUMENT_EXPIRED:
                    return this._template1(
                        notification,
                        'danger',
                        "Document Expired!",
                        `The ${data.docType} of ${data.employee} has expired!`,
                    )
                case this.EMPLOYEE_DOCUMENT_EXPIRING:
                    return this._template1(
                        notification,
                        'warning',
                        "Document Expiring!",
                        `The ${data.docType} of ${data.employee} is about to expire!`
                    )
                case this.LABOUR_INCOME_RECOGNIZED:
                    return this._template1(
                        notification,
                        'info',
                        'Income Recognized',
                        `Income for contract '${data.contractRef}' recognized - ${data.amount}AED (${data.daysRecognized} ${data.daysRecognized == 1 ? 'day' : 'days'}), the remaining balance is ${data.balance}AED`,
                    )
                case this.LABOUR_EXPENSE_RECOGNIZED:
                    return this._template1(
                        notification,
                        'info',
                        'Expense Recognized',
                        `Expense for supplier invoice '${data.invoiceRef}' against contract '${data.contractRef}' recognized - ${data.amount}AED (${data.daysRecognized} ${data.daysRecognized == 1 ? 'day' : 'days'}), the remaining balance is ${data.balance}AED`,
                    )
                case this.LEAVE_ACCRUAL_NOTIFICATION:
                    return this._template1(
                        notification,
                        'info',
                        'Leave Accrual Posted',
                        `Leave accruals as of ${data.as_of_date} posted on ${data.trans_date}`,
                    )
                case this.GRATUITY_ACCRUAL_NOTIFICATION:
                    return this._template1(
                        notification,
                        'info',
                        'Gratuity Accrual Posted',
                        `Gratuity accruals as of ${data.as_of_date} posted on ${data.trans_date}`,
                    )
                case this.TASK_INITIALIZED_NOTIFICATION:
                    return this._template1(
                        notification,
                        'info',
                        ` ${notification.data.title} ${notification.data.description}`,
                        `${notification.data.employee} ${notification.data.description} ${notification.data.title} ${notification.data.reference}`
                    )
                case this.TASK_TRANSITIONED_NOTIFICATION:
                    return this._template1(
                        notification,
                        'info',
                        `${notification.data.title} ${notification.data.description}`,
                        `${notification.data.employee} ${notification.data.description} ${notification.data.title} ${notification.data.reference}`
                    )
                case this.TASK_APPROVED_NOTIFICATION:
                    return this._template1(
                        notification,
                        'info',
                        `${notification.data.title} ${notification.data.description}`,
                        `${notification.data.employee} ${notification.data.description} ${notification.data.title} ${notification.data.reference}`
                    )
                case this.TASK_CANCELLED_NOTIFICATION:
                    return this._template1(
                        notification,
                        'warning',
                        `${notification.data.title} ${notification.data.description}`,
                        `${notification.data.employee} ${notification.data.description} ${notification.data.title} ${notification.data.reference}`
                    )
                case this.TASK_REJECTED_NOTIFICATION:
                    return this._template1(
                        notification,
                        'danger',
                        `${notification.data.title} ${notification.data.description}`,
                        `${notification.data.employee} ${notification.data.description} ${notification.data.title} ${notification.data.reference}`
                    )
                case this.LABOUR_INSTALLMENT_REMINDER:
                    return this._template1(
                        notification,
                        'info',
                        'Installment Reminder',
                        `Installment for contract '${data.contractRef}' reminder - ${data.amount}AED (${data.daysRecognized} ${data.daysRecognized == 1 ? 'day' : 'days'}) is due for invoicing, the remaining balance is ${data.balance}AED`,
                    )
                case this.TRANSACTION_NOTIFICATION:
                    return this._template1(
                        notification,
                        'info',
                        `${notification.data.title} ${notification.data.description}`,
                        `A new ${notification.data.title} (No: ${notification.data.reference}) has been assigned to you. Created By ${notification.data.assigned_by} at ${notification.data.assigned_at}`,
                    )
                default:
                    return this._busyTemplate1(notification, 'error')    
            }
        }

        /**
         * A simple template for the notifications
         * 
         * @param {object} notification
         * @param {"info"|"warning"|"success"|"danger"} state The state of the notification
         * @param {string} title 
         * @param {string} description
         * @param {string} icon 
         * @param {string} link 
         */
        _template1(
            notification,
            state,
            title,
            description,
            icon = null,
            link = null
        ) {
            const _time = moment(new Date(notification.created_at)).fromNow();
            const _icon = icon || this._getIcon(state);
            const _link = link || '#';
            const isRead = notification.read_at != null;
            let actions = '';

            if (notification.isBusy) {
                return this._busyTemplate1(notification, state);
            }

            if (notification.data.actions && notification.data.actions.length) {
                notification.data.actions.forEach(action => {
                    actions += (
                        `<span
                            data-action="${url(action.url)}"
                            class="btn btn-${action.class ? action.class : 'secondary'} notification-action">
                            ${action.title}
                        </span>`
                    );
                })
            }

            return $(
                `<div
                    data-notification-id="${notification.id}"
                    class="d-flex flex-stack bg-light-${state} py-4 px-4 notification-item ${
                    isRead ? "read" : ""
                }">
                    <!--begin::Section-->
                    <div class="d-flex align-items-center flex-grow-1">
                        <!--begin::Symbol-->
                        <div class="me-4 text-center w-35px">
                            <span>${_icon}</span>
                        </div>
                        <!--end::Symbol-->

                        <!--begin::Desc-->
                        <div class="mb-0 w-100">
                            <div>
                                <div class="d-inline-block">
                                    <span class="fs-6 text-hover-accent fw-bolder text-accent">${title}</span>
                                    <span class="fs-9 text-muted mb-2 d-block">${_time}</span>
                                </div>
                                <div class="float-end d-inline-block">
                                    <div>
                                        <button
                                            type="button"
                                            data-btn="read"
                                            title="Mark as ${
                                                isRead ? "unread" : "read"
                                            }"
                                            class="btn py-0 px-1 shadow-none">
                                            <i class="la la-${
                                                isRead
                                                    ? "envelope"
                                                    : "envelope-open"
                                            } text-info align-baseline"></i>
                                        </button>
                                        <button
                                            type="button"
                                            data-btn="delete"
                                            title="Delete"
                                            class="btn py-0 px-1 shadow-none">
                                            <i class="la la-trash text-danger align-baseline"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="fs-7" data-btn-read>
                                ${description}
                                ${actions}
                            </div>
                        </div>
                        <!--end::Desc-->
                    </div>
                    <!--end::Section-->
                </div>`
            )[0];
        }

        /**
         * Template to show when the specific notification is busy
         * 
         * @param {object} notification
         * @param {"info"|"warning"|"success"|"danger"|"error"} state 
         */
        _busyTemplate1(notification, state) {
            const isRead = notification.read_at != null;
            const icon = state == "error" 
                ? `<div class="me-4 text-center w-35px"><span>${this._getIcon(state)}</span></div>`
                : `<div class="me-4 w-35px h-35px bg-${state}"></div>`;

            return $(
                `<div
                    area-hidden="true"
                    data-notification-id="${notification.id}"
                    class="d-flex flex-stack bg-light-${state} py-4 px-4 notification-item ${
                    isRead ? "read" : ""
                } placeholder-wave">
                    <!--begin::Section-->
                    <div class="d-flex align-items-center flex-grow-1">
                        <!--begin::Symbol-->
                        ${icon}
                        <!--end::Symbol-->

                        <!--begin::Desc-->
                        <div class="mb-0 w-100">
                            <div>
                                <div class="d-inline-block">
                                    <span class="fs-6 placeholder bg-accent w-175px"></span>
                                    <span class="fs-9 placeholder bg-gray-400 mb-2 d-block w-75px"></span>
                                </div>
                                <div class="float-end d-inline-block">
                                    <div>
                                        <button
                                            type="button"
                                            data-btn="read"
                                            disabled
                                            class="btn disabled w-20px btn-info placeholder shadow-none py-0 px-1">
                                        </button>
                                        <button
                                            type="button"
                                            data-btn="delete"
                                            disabled
                                            class="btn disabled w-20px btn-danger placeholder shadow-none py-0 px-1">
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="fs-7">
                                <span class="placeholder bg-gray-600" style="width: 75%"></span>
                            </div>
                        </div>
                        <!--end::Desc-->
                    </div>
                    <!--end::Section-->
                </div>`
            )[0];
        }

        /**
         * Returns a suitable default icon for the state
         * 
         * @param {"info"|"warning"|"success"|"danger"|"error"} state 
         * @returns {string}
         */
        _getIcon(state) {
            const icons = {
                info: 'info',
                warning: 'exclamation-triangle',
                danger: 'exclamation',
                success: 'check',
                error: 'spider'
            }

            return `<i class="fa fa-2x fa-${icons[state]} text-${state}"></i>`;
        }
    }

    /**
     * Exposes the Notification API through the object
     * 
     * @type {AppNotification}
     * @global
     */
    window.AppNotification = new AppNotification();

    // Initialize the notifications
    window.AppNotification.init();
});