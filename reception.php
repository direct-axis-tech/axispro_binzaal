<?php

use App\Models\Sales\Customer;

 ob_start(); ?>
<style>
    #display_customer:read-only {
        background-color: #EFF2F5;
    }
</style>
<?php $GLOBALS['__HEAD__'][] = ob_get_clean();

include "header.php";

$application = isset($_GET['action']) ? $_GET['action'] : "list"; ?>


<div class="kt-container  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch">
    <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
        <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

            <div class="kt-container  kt-grid__item kt-grid__item--fluid">

                <div class="row">
                    <div class="kt-portlet">
                        <div class="kt-portlet__head border-bottom-0">
                            <div class="kt-portlet__head-label">
                                <h3 class="kt-portlet__head-title">
                                    <?= trans('RECEPTION') ?>
                                    <span
                                        class="ml-3"
                                        id="loading_data"
                                        style="visibility:hidden">
                                        Reading public data ...
                                    </span>
                                </h3>
                            </div>
                        </div>
                        <div class="row px-4">
                            <div class="col-lg-6">
                                <div class="form-group row">
                                    <label class="col-4 col-form-label text-right">
                                        <?= trans('Find customer from list') ?> :
                                    </label>
                                    <div class="col-8">
                                        <select required style="width: 100%" class="form-control ap-select2" id="choose-customer" >
                                            <option value="">-- select --</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <OBJECT id="EIDAWebComponent" style="border:solid 1px gray; display: none"
                                CLASSID="CLSID:A4B3BB86-4A99-3BAE-B211-DB93E8BA008B"
                                width="130" height="154">
                        </OBJECT>

                        <hr class="w-100">
                        <form class="kt-form kt-form--label-right" id="item_form">
                            <div class="kt-portlet__body" style="padding:20px !important">
                                <div class="kt-portlet__body kt-margin-t-20">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="form-group row required">
                                                <label class="col-4 col-form-label">
                                                    <?= trans('Mobile No.') ?> :
                                                </label>
                                                <div class="col-8">
                                                    <div class="input-group">
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">+971</span>
                                                        </div>
                                                        <input
                                                            required
                                                            data-parsley-pattern="(5[024568]|[1234679])\d{7}"
                                                            data-parsley-pattern-message="This is not a valid UAE number"
                                                            type="number"
                                                            id="customer_mobile"
                                                            name="customer_mobile"
                                                            class="form-control"
                                                            placeholder="e.g. 51XXXX123"
                                                            value="">
                                                        <div class="input-group-append">
                                                            <button type="button" id="reselect-customer" class="border">
                                                                <span class="la la-refresh"></span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="form-group row required">
                                                <label class="col-4 col-form-label">
                                                    <?= trans('Token Number') ?> :
                                                </label>
                                                <div class="col-8">
                                                    <input
                                                        type="text"
                                                        id="token"
                                                        name="token"
                                                        class="form-control"
                                                        placeholder="e.g. TK-001"
                                                        required
                                                        value="">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="form-group row required">
                                                <label class="col-4 col-form-label">
                                                    <?= trans('Customer') ?> :
                                                </label>
                                                <div class="col-8">
                                                    <input type="hidden" name="customer_id" id="customer_id" value="<?= Customer::WALK_IN_CUSTOMER ?>">
                                                    <input type="text" readonly id="customer_name" class="form-control-plaintext" value="New - Walk-in Customer">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="form-group row required">
                                                <label class="col-4 col-form-label">
                                                    <?= trans('Display Customer As') ?> :
                                                </label>
                                                <div class="col-8">
                                                    <input
                                                        type="text"
                                                        required
                                                        name="display_customer"
                                                        id="display_customer"
                                                        class="form-control"
                                                        value=""
                                                        placeholder="e.g. Fulan Industries Pvt. Ltd.">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="form-group row required">
                                                <label class="col-4 col-form-label">
                                                    <?= trans('Sub Customer') ?> :
                                                </label>
                                                <div class="col-8">
                                                    <select
                                                        class="form-control custom-select"
                                                        required
                                                        name="sub_customer_id"
                                                        id="sub_customer_id"
                                                        data-selection-css-class="validate">
                                                        <option value="">Choose a Company</option>
                                                        <option value="-1">-- Not Applicable --</option>
                                                    </select>
                                                    <span id="display_cust_err"></span>
                                                    <small>
                                                        <button
                                                            disabled
                                                            type="button"
                                                            id="add-sub-customer"
                                                            class="btn btn-sm btn-link py-0">
                                                            Add New Company
                                                        </button>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="<?= class_names(['form-group row', 'required' => pref('axispro.is_contact_person_mandatory', 0) ]) ?>">
                                                <label class="col-4 col-form-label" for="contact_person">
                                                    <?= trans('Contact Person') ?> :
                                                </label>
                                                <div class="col-8">
                                                    <input
                                                        <?= class_names(['required' => pref('axispro.is_contact_person_mandatory', 0) ]) ?>
                                                        type="text"
                                                        id="contact_person"
                                                        name="contact_person"
                                                        class="form-control"
                                                        placeholder="e.g. Fulan">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="<?= class_names(['form-group row', 'required' => pref('axispro.is_email_mandatory', 0) ]) ?>">
                                                <label class="col-4 col-form-label">
                                                    <?= trans('Email Address') ?> :
                                                </label>
                                                <div class="col-8">
                                                    <input
                                                        <?= class_names(['required' => pref('axispro.is_email_mandatory', 0) ]) ?>
                                                        type="email"
                                                        id="customer_email"
                                                        name="customer_email"
                                                        class="form-control"
                                                        placeholder="e.g. fulan@gmail.com"
                                                        value="">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="form-group row">
                                                <label class="col-4 col-form-label">
                                                    <?= trans('TRN No.') ?> :
                                                </label>
                                                <div class="col-8">
                                                    <input
                                                        type="number"
                                                        id="customer_trn"
                                                        maxlength="15"
                                                        data-parsley-pattern="100\d{12}"
                                                        data-parsley-pattern-message="This does not look like a valid TRN number"
                                                        name="customer_trn"
                                                        class="form-control"
                                                        placeholder="e.g. 100123XXXXXXX12"
                                                        value="">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="<?= class_names([
                                            'col-lg-6',
                                            'd-none' => !pref('axispro.enable_iban_no_column', 0)
                                        ]) ?>">
                                            <div class="form-group row">
                                                <label class="col-4 col-form-label">
                                                    <?= trans('IBAN No.') ?> :
                                                </label>
                                                <div class="col-8">
                                                    <input
                                                        type="text"
                                                        data-parsley-pattern="AE\d{21}"
                                                        data-parsley-pattern-message="This does not look like a valid IBAN number"
                                                        id="customer_iban"
                                                        name="customer_iban"
                                                        class="form-control"
                                                        placeholder="e.g. AE1234XXXXXXXXXXXXXX21"
                                                        value=""
                                                        maxlength="23">
                                                    <small id="iban-help" class="form-text text-muted">
                                                        characters: <span id="iban-count">0</span>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <div class="kt-portlet__foot">
                                <div class="kt-form__actions text-center">
                                    <button type="submit" class="btn btn-primary">
                                        <?= trans('Submit') ?>
                                    </button>
                                    <button type="reset" class="btn btn-secondary">
                                        <?= trans('Reset') ?>
                                    </button>
                                    <button type="button" id="read-emirates_id" class="btn btn-success clsBtn" style="font-size: 10pt;">
                                        READ DATA FROM EID
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- end:: Content -->
        </div>
    </div>
</div>

<div class="modal fade" id="modal-add-subcustomer" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= trans('Add Sub Customer') ?></h5>
                <button
                    type="button"
                    class="close"
                    data-bs-dismiss="modal"
                    aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form id="form-add-subcustomer" method="post">
                    <input type="hidden" id="comp_cust_id" name="comp_cust_id">
                    <div class="row">
                        <div class="col">
                            <div class="form-group required">
                                <label for="comp_name">Company Name</label>
                                <input
                                    required
                                    type="text"
                                    class="form-control"
                                    name="comp_name"
                                    id="comp_name"
                                    placeholder="e.g. Al Fulan Organisation L.L.C">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="comp_email">Company Email</label>
                                <input
                                    type="email"
                                    class="form-control"
                                    name="comp_email"
                                    id="comp_email"
                                    placeholder="e.g. info@alfulanorg.com">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="comp_trn">Company TRN</label>
                                <input
                                    type="number"
                                    maxlength="15"
                                    data-parsley-pattern="100\d{12}"
                                    data-parsley-pattern-message="This does not look like a valid TRN number"
                                    class="form-control"
                                    name="comp_trn"
                                    id="comp_trn"
                                    placeholder="e.g. 100123XXXXXXX12">
                            </div>
                        </div>
                    </div>
                    <div class="<?= class_names([
                        'row',
                        'd-none' => !pref('axispro.enable_iban_no_column', 0)
                    ]) ?>">
                        <div class="col" >
                            <div class="form-group">
                                <label for="comp_iban">Company IBAN No.</label>
                                    <input
                                        type="text"
                                        maxlength="23"
                                        data-parsley-pattern="AE\d{21}"
                                        data-parsley-pattern-message="This does not look like a valid IBAN number"
                                        class="form-control"
                                        name="comp_iban"
                                        id="comp_iban"
                                        placeholder="e.g. AE1234XXXXXXXXXXXXXX21"
                                        value="">
                                    <small id="comp-iban-help" class="form-text text-muted">
                                        characters: <span id="comp-iban-count">0</span>
                                    </small>
                            </div>
                        </div>   
                    </div>   
                </form>
                <div class="modal-footer" id="modal_footer">
                    <button form="form-add-subcustomer" type="submit" class="btn btn-success">Add</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal-select-customers" class="modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Select customer</h5>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table class="table table-sm table-striped w-100">
            <thead>
                <th>Ref#</th>
                <th>Customer Name</th>
                <th>Contact Person</th>
                <th>Mobile Number</th>
                <th>Email Address</th>
                <th>Tax Reg. Number</th>
                <?php if (pref('axispro.enable_iban_no_column', 0)): ?>
                <th>IBAN Number</th>
                <?php endif; ?>
                <th></th>
            </thead>
            <tbody id="tbody-select-customers">

            </tbody>
        </table>
      </div>
    </div>
  </div>
</div>


<?php ob_start(); ?>

<script>
    $(function() {
        var global = {
            customers: [],
            selected_customer: null,
            resolve: null,
            reject: null,
            walkInCustomerId: <?= Customer::WALK_IN_CUSTOMER ?>,
            UAEMobileNoPattern: <?= UAE_MOBILE_NO_PATTERN ?>,
            subCustomers: null,
            currentCustomer: null
        };

        // Initialize the customer's select
        initializeCustomersSelect2('#choose-customer', {
            ajax: {
                data: function(param) {
                    return {
                        'except[]': [global.walkInCustomerId],
                        ...param
                    }
                }
            }
        });

        // Initialize the model
        $('#modal-select-customers').modal({
            backdrop: 'static',
            keyboard: false,
            show: false
        });

        var pslyForm = $('#item_form').parsley();
        var pslySubCustForm = $('#form-add-subcustomer').parsley();

        $('#read-emirates_id').click(function() {
            EmiratesIDReader.read()
            .then(function (result) {
                let mobileNo = getNumberPartFromMobileNumber(result.phoneNumber);

                // check if the customer is already registered with us
                fetchCustomerDetailsFromMobile(mobileNo)
                    .then(() => {
                        let customers = global.customers;
                        
                        // No customer found, use the data that we read from emirates id
                        if (!customers.length) {
                            return setCustomerData({
                                debtor_no: global.walkInCustomerId,
                                debtor_ref: 'New',
                                name: result.fullNameEN,
                                contact_person: result.fullNameEN,
                                debtor_email: result.email,
                                tax_id: '',
                                iban_no: '',
                                mobile: mobileNo
                            })
                        }

                        // Exactly one customer found, populate the data that we already have and discard
                        // the data that we read from emirates id
                        if (customers.length == 1) {
                            return setCustomerData(customers[0]);
                        }

                        // There are multiple customers with the same mobile number, make the user select which customer
                        selectCustomer();
                    })
            });
        });

        $("#customer_mobile").change(function() {
            var pslyInstance = $(this).parsley();

            pslyInstance.whenValidate()
                .then(() => fetchCustomerDetailsFromMobile(pslyInstance.element.value))
                .then(function  () {
                    let customers = global.customers;

                    // No customer found
                    if (!customers.length) {
                        return setCustomerData(newCustomer(pslyInstance.element.value));
                    }

                    // Exactly one customer found
                    if (customers.length == 1) {
                        return setCustomerData(customers[0]);
                    }

                    // There are multiple customers with the same mobile number
                    selectCustomer();
                })
                .catch(function () {});
        });

        $("#reselect-customer").on("click", selectCustomer)

        $("#modal-select-customers").on('hide.bs.modal', function() {
            if (global.selected_customer && global.resolve) {
                global.resolve(global.selected_customer);
            }

            if (!global.selected_customer && global.reject) {
                global.reject();
            }

            global.selected_customer = null;
            global.resolve = null;
            global.reject = null;
        })

        pslySubCustForm.$element.on('reset', function() {
            pslySubCustForm.reset();
        })

        $('#add-sub-customer').on('click', function() {
            var customer_id = document.getElementById('customer_id').value;
            if (customer_id != global.walkInCustomerId) {
                pslySubCustForm.$element.trigger('reset');
                document.getElementById('comp_cust_id').value = customer_id;
                $('#modal-add-subcustomer').modal('show');
            }
        });

        pslySubCustForm.on('form:submit', function() {
            setBusyState();
            $.ajax({
                method: "POST",
                url: route('API_Call', {method: 'addSubCustomer'}),
                data: this.$element.serialize(),
                dataType: 'json'
            }).done(function (res) {
                if (res.status && res.status == 'OK') {
                    swal.fire(
                        'Success!',
                        'Customer Company saved',
                        'success'
                    ).then(function() {
                        loadSubCustomers();
                        $("#modal-add-subcustomer").modal("hide");
                    });
                } else {
                   _err();
                }
            }).fail(_err)
            .always(unsetBusyState);

            return false;
        })

        pslyForm.on('form:submit', function() {
            setBusyState();
            $.ajax({
                method: "POST",
                url: route('api.reception.createToken'),
                data: this.$element.serialize(),
                dataType: 'json'
            }).done(function (res, xhr) {
                if (!xhr.status == 201) {
                    return _err();
                }

                swal.fire(
                    'Token saved successfully!',
                    '',
                    'success'
                ).then(function() {
                    pslyForm.$element.trigger('reset');
                });
            }).fail(_err)
            .always(unsetBusyState);
            return false;
        })

        pslyForm.$element.on('reset', function() {
            pslyForm.reset();
            $('#choose-customer').val('').trigger('change.select2');
        })

        $('#choose-customer').change(function () {
            if (this.value.length) {
                setBusyState();
                $.ajax({
                    method: "GET",
                    url: route('API_Call', {method: 'get_customer'}),
                    data: {
                        id: this.value
                    },
                    dataType: 'json'
                }).done(function(res) {
                    setCustomerData(res.data)
                }).fail(function() {})
                .always(unsetBusyState);
            };
        });

        $('#customer_iban').on('keyup', function(){
            $('#iban-count').text(this.value.length);
        });

        $('#comp_iban').on('keyup', function(){
            $('#comp-iban-count').text(this.value.length);
        });

        $("#sub_customer_id").change(function() {
            var currentCustomer = global.currentCustomer;
            var isNewCustomer = currentCustomer.debtor_no == global.walkInCustomerId;
            var id = this.value;
            var subCustomer = {};
            
            if (id.length && id != '-1') {
                subCustomer = global
                    .subCustomers
                    .find(function (subCustomer) {
                        return subCustomer.id == id;
                    })
            };
            
            var name = subCustomer.name || currentCustomer.name;
            var email = subCustomer.email || currentCustomer.debtor_email;
            var trn = subCustomer.trn || currentCustomer.tax_id;
            var iban = subCustomer.iban || currentCustomer.iban_no;
                
            if (!isNewCustomer) {
                document.getElementById('display_customer').value = name || '';
                document.getElementById('customer_email').value = email || '';
                document.getElementById('customer_trn').value = trn || '';
                document.getElementById('customer_iban').value = iban || '';
            }
        });

        function fetchCustomerDetailsFromMobile(mobileNo) {
            return new Promise((resolve, reject) => {
                ajaxRequest({
                    method: "GET",
                    url: route('API_Call', {method: 'getCustomersByMobile'}),
                    data: {
                        mobile: mobileNo
                    },
                    dataType: 'json'
                })
                .done(function (res, msg, xhr) {
                    if (res.status && res.status == 200) {
                        var customers = res.data;
                        global.customers = customers;

                        return resolve();
                    } else {
                        defaultErrorHandler(xhr);
                    }
                })
                .fail(defaultErrorHandler);
            })
        }

        /**
         * Selects the customer from the list
         *
         * @return void
         */
        function selectCustomer() {
            getSelectedCustomer()
                .then(function(customer) {
                    setCustomerData(customer);
                }).catch(function() {});
        }

        /**
         * Gets the data for new customer
         * @param {string} mobileNo
         * @return {object}
         */
        function newCustomer(mobileNo) {
            return {
                debtor_no: global.walkInCustomerId,
                debtor_ref: 'New',
                name: 'Walk-in Customer',
                contact_person: '',
                debtor_email: '',
                tax_id: '',
                iban_no: '',
                mobile: mobileNo
            }
        }

        /**
         * Invokes the resolver callback when the user selects a customer.
         *
         * @return {Promise}
         */
        function getSelectedCustomer() {
            return new Promise(function(resolve, reject) {
                var tbody = document.getElementById('tbody-select-customers');
                empty(tbody);
                global.customers.forEach(function(customer, index) {
                    tbody.appendChild(createTrFromCustomer(customer, index));
                })

                global.selected_customer = null;
                global.resolve = resolve;
                global.reject = reject;

                $('#modal-select-customers').modal('show');
            })
        }

        /**
         * The handler used to handle the click event when selecting customer.
         */
        function selectCustomerHandler(ev) {
            var key = this.dataset.key;
            global.selected_customer = global.customers[key];
            $('#modal-select-customers').modal('hide');
        }

        /**
         * Creates the table row element from the customers data
         * 
         * @param {object} cust The object containing the customer data
         * @param {string} key The key used to access the data from the customers array
         * 
         * return {HTMLTableRowElement}
         */
        function createTrFromCustomer(cust, key) {
            var tr = document.createElement('tr');
            [
                'debtor_ref',
                'name',
                'contact_person',
                'mobile',
                'debtor_email',
                'tax_id',
                <?php if (pref('axispro.enable_iban_no_column', 0)): ?>
                'iban_no'
                <?php endif; ?>
            ].forEach(function(_key) {
                var td = document.createElement('td');
                td.textContent = cust[_key];
                tr.appendChild(td);
            })

            var button = document.createElement('button');
            button.dataset.key = key;
            button.textContent = 'Select';
            button.onclick = selectCustomerHandler;
            button.className = 'btn btn-primary shadow-none';

            var td = document.createElement('td');
            td.appendChild(button);
            tr.appendChild(td);

            tr.id = 'cust_' + cust.debtor_no;
            return tr;
        };

        /**
         * Sets customer's data in the UI
         * 
         * @param {object} cust An object representing the current customer
         */
        function setCustomerData(cust) {
            global.currentCustomer = cust;
            var isNewCustomer = cust.debtor_no == global.walkInCustomerId;
            let sanitizedName = (cust.name || '').replace(/[^a-z]/ig,'').toLowerCase();

            document.getElementById('customer_mobile').value = getNumberPartFromMobileNumber(cust.mobile);
            document.getElementById("customer_id").value = cust.debtor_no;
            document.getElementById("customer_name").value = cust.debtor_ref + ' - ' + cust.name;
            document.getElementById("display_customer").value = sanitizedName.startsWith('walkin') ? '' : cust.name;
            document.getElementById("customer_email").value = cust.debtor_email;
            document.getElementById("contact_person").value = cust.contact_person;
            document.getElementById("customer_trn").value = cust.tax_id;
            
            var ibanElem = document.getElementById("customer_iban");
            ibanElem.value = '<?= pref('axispro.enable_iban_no_column', 0) ? 'cust.iban_no' : '' ?>';
            $(ibanElem).trigger('keyup');

            setDisabled(false, isNewCustomer);
            loadSubCustomers().then(function() {
                $('#sub_customer_id').val('-1');
            });
        }

        /**
         * Gets customer's data from the UI
         * 
         * @return {object} An object representing the customer
         */
        function getCustomerData() {
            return {
                debtor_no: document.getElementById("customer_id").value,
                name: document.getElementById("display_customer").value,
                mobile: document.getElementById('customer_mobile').value,
                debtor_email: document.getElementById("customer_email").value,
                contact_person: document.getElementById("contact_person").value,
                tax_id: document.getElementById("customer_trn").value,
                iban_no: document.getElementById("customer_iban").value,
            }
        }

        /**
         * Read the public data available from the emirates ID
         *
         * @return {{
         *  err?: string,
         *  data: object 
         * }}
         */
        function getPublicDataFromEmiratesID() {
            if(EIDAWebComponent == null) {
                return {
                    err: "The Webcomponent is not initialized. Please try again"
                };
            }

            document.getElementById("loading_data").innerHTML = "Reading Public Data ...";
            document.getElementById("loading_data").style.visibility = "visible";

            var result = ReadPublicDataEx("true", "false", "true", "true", "false", "true", "true", "true");
            if(result.startsWith("-")) {
                return {
                    err: GetErrorMessage(result)
                };
            }

            document.getElementById("loading_data").style.visibility = "hidden";

            var person = {
                name: GetFullName(),
                emirates_id: GetIDNumber(),
                mobile: GetHomeAddress_MobilePhoneNumber(),
                debtor_email: GetHomeAddress_Email()
            };

            return { data: person };
        }

        /**
         * Sets the disabled state of inputs
         * 
         * @param {boolean} state
         * @param {boolean} isNewCustomer 
         * 
         * @return void
         */
        function setDisabled(state, isNewCustomer) {
            document.getElementById("customer_email").disabled = state;
            document.getElementById("contact_person").disabled = state;
            document.getElementById("customer_trn").disabled = state;
            document.getElementById("customer_iban").disabled = state;
            document.getElementById("token").disabled = state;
            document.getElementById("display_customer").disabled = state;
            document.getElementById("display_customer").readOnly = !isNewCustomer;
            document.getElementById("sub_customer_id").disabled = state;
            document.getElementById("read-emirates_id").disabled = !isNewCustomer;
            document.getElementById('add-sub-customer').disabled = isNewCustomer;
        }

        /**
         * Retrives the number part from the UAE mobile number
         * 
         * @param {string} mobileNo
         * 
         * @return {string}
         */
        function getNumberPartFromMobileNumber(mobileNo) {
            var matches = mobileNo.match(global.UAEMobileNoPattern);

            return (matches && matches[2]) ? matches[2] : '';
        }

        /**
         * Load the subcustomers
         *
         * @return void
         */
        function loadSubCustomers() {
            return new Promise((resolve, reject) => {
                var customer_id = document.getElementById('customer_id').value;
                var subCustomerSel = document.getElementById('sub_customer_id');
                empty(subCustomerSel);
                global.subCustomers = [];

                if (customer_id == global.walkInCustomerId) {
                    subCustomerSel.appendChild(getOptionsForSubCustomers([]));
                    resolve([]);
                } else {
                    setBusyState();
                    $.ajax({
                        method: 'get',
                        url: route('API_Call', {method: 'get_sub_customers'}),
                        data: {
                            customer_id: customer_id
                        },
                        dataType: 'json'
                    }).done(function (res) {
                        if (res.data) {
                            subCustomerSel.appendChild(getOptionsForSubCustomers(res.data));
                            resolve(res.data)
                        } else {
                            reject()
                            _err();
                        }
                    }).fail(() => {
                        _err();
                        reject();
                    })
                    .always(unsetBusyState);
                }
            })
        }

        /**
         * Returns the fragment containing all the options element for sub customers
         * 
         * @param {Array} subCustomers
         * 
         * @return {DocumentFragment}
         */
        function getOptionsForSubCustomers(subCustomers) {
            var fragment = document.createDocumentFragment();
            global.subCustomers = subCustomers;

            var options = [
                {
                    id: '',
                    name: 'Choose a Company',
                    defaultSelected: true
                },
                {
                    id: '-1',
                    name: '-- Not Applicable --'
                }
            ].concat(subCustomers);

            options.forEach(function(option) {
                fragment.appendChild(
                    new Option(
                        option.name,
                        option.id,
                        option.defaultSelected
                    )
                );
            })

            return fragment;
        }

        /**
         * Default error notifier
         *
         * @return void
         */
        function _err(xhr = null) {
            message = '';
            if (xhr && xhr.status == 422) {
                message = xhr.responseJSON.message;
                if (xhr.responseJSON.errors) {
                    $.each(xhr.responseJSON.errors, function(key, val) {
                        message += '<br>'.concat(val.map(err => key.concat(': ', err)).join('<br>'));
                    })
                }
            }

            toastr.error(message || 'Something went wrong!')
        }
    });
</script>

<?php

$GLOBALS['__FOOT__'] = [ob_get_clean()];
include "footer.php";
