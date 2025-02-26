@extends('layout.app')

@section('page')

<div class="container">
    <form action="{{ erp_url('/ERP/sales/labour_contract.php', ['action' => 'createContract']) }}" method="post" id="create_contract_form" enctype="multipart/form-data">
        <div class="card mw-850px mx-auto">
            <div class="card-header">
                <div class="card-title">
                    <h2>New Contract</h2>
                </div>
            </div>
            <div class="card-body">                
                <div class="row mb-5">
                    <label for="reference" class="col-sm-3 col-form-label required">Reference</label>
                    <div class="col-sm-9">
                        <input class="form-control" type="text" name="reference" id="reference" value="{{ $reference }}" readonly>
                        <small class="text-muted fs-5">The actual reference may increment automatically</small>
                        <span data-error-message class="text-danger"></span>
                    </div>
                </div>

                <div class="row mb-5">
                    <label for="debtor_no" class="col-sm-3 col-form-label required">Customer</label>
                    <div class="col-sm-9">
                        <select class="form-select" name="debtor_no" id="debtor_no" required>
                            <option value="">-- select customer --</option>
                        </select>
                        <span data-error-message class="text-danger"></span>
                    </div>
                </div>

                <div class="row mb-5 d-none">
                    <label for="type" class="col-sm-3 col-form-label required">Type</label>
                    <div class="col-sm-9">
                        {{-- <select name="type" id="type" required class="form-select">
                            @foreach ($labour_contract_types as $key => $val)
                                <option @if ($key == \App\Models\Labour\Contract::CONTRACT) selected @endif
                                    value="{{ $key }}"> {{ $val }}</option>
                            @endforeach
                        </select> --}}
                        <input type="hidden" name="type" id="type" value="{{ \App\Models\Labour\Contract::CONTRACT }}">
                        <span data-error-message class="text-danger"></span>
                    </div>
                </div>
                
                <div class="row mb-5">
                    <label for="category_id" class="col-sm-3 col-form-label required">Category</label>
                    <div class="col-sm-9">
                        <select class="form-select" name="category_id" id="category_id" required data-control="select2">
                            <option value="">-- select --</option>
                            @foreach ($categories as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </select>
                        <span data-error-message class="text-danger"></span>
                    </div>
                </div>

                <div class="row mb-5">
                    <label for="stock_id" class="col-sm-3 col-form-label required">Item</label>
                    <div class="col-sm-9">
                        <select class="form-select" name="stock_id" id="stock_id" required data-control="select2">
                            <option value="">-- select --</option>
                            @foreach ($stockItems as $stockItem)
                                <option value="{{ $stockItem->stock_id }}"  data-nationality="{{ $stockItem->nationality }}" data-category-id="{{ $stockItem->category_id }}">
                                    {{ $stockItem->description }}
                                </option>
                            @endforeach
                        </select><span data-error-message class="text-danger"></span>
                    </div>
                </div>

                <div class="row mb-5">
                    <label for="labour_id" class="col-sm-3 col-form-label required">Domestic Worker</label>
                    <div class="col-sm-9">
                        <select class="form-select" name="labour_id" id="labour_id" required>
                            <option value="">-- select --</option>
                        </select>
                        <span data-error-message class="text-danger"></span>
                    </div>
                </div>
                
                <div class="row mb-5">
                    <label for="order_date" class="col-sm-3 col-form-label required">Order Date</label>
                    <div class="col-sm-9">
                        <input
                            type="text"
                            required
                            data-parsley-trigger-after-failure="change"
                            class="form-control"
                            data-control="bsDatepicker"
                            autocomplete="off"
                            name="order_date"
                            id="order_date"
                            value="{{ Today() }}"
                            placeholder="{{ getBSDatepickerDateFormat() }}">
                        <span data-error-message class="text-danger"></span>
                    </div>
                </div>

                <div class="row mb-5">
                    <label for="contract_from" class="col-sm-3 col-form-label required">Contract Period</label>
                    <div class="col-sm-9">
                        <div
                            id="daterange_picker"
                            class="input-group input-daterange"
                            data-control="bsDatepicker">
                            <input
                                type="text"
                                required
                                data-parsley-trigger-after-failure="change"
                                class="form-control"
                                autocomplete="off"
                                name="contract_from"
                                id="contract_from"
                                value="{{ Today() }}"
                                placeholder="{{ getBSDatepickerDateFormat() }}">
                            <div class="input-group-text input-group-addon parsley-indicator px-4 rounded-0 border-left-0 border-right-0">to</div>
                            <input
                                required
                                type="text" 
                                data-parsley-trigger-after-failure="change"
                                name="contract_till" 
                                id="contract_till"
                                class="form-control"
                                autocomplete="off"
                                value="{{ Today() }}"
                                placeholder="{{ getBSDatepickerDateFormat() }}">
                        </div>
                        <small class="text-muted">Difference: <span data-diff-in-days></span></small>
                        <span data-error-message class="text-danger"></span>
                    </div>
                </div>

                <div class="row mb-5">
                    <label for="amount" class="col-sm-3 col-form-label required">Contract Amount {{ $dimension->is_invoice_tax_included ? '(inc. VAT)' : '' }}</label>
                    <div class="col-sm-9">
                        @if ($dimension->is_invoice_tax_included)
                        <small class="text-danger">Amount is considered vat inclusive</small>
                        @endif
                        <input type="number" class="form-control" step="0.01" min="0" name="amount" id="amount" required>
                        <span data-error-message class="text-danger"></span>
                    </div>
                </div>

                <div class="row mb-5">
                    <label for="prep_amount" class="col-sm-3 col-form-label required">Initial Payment Required Before Delivery</label>
                    <div class="col-sm-9">
                        <input type="number" class="form-control" step="0.01" min="0" name="prep_amount" id="prep_amount" value="0" required>
                        <span data-error-message class="text-danger"></span>
                    </div>
                </div>

                <div class="row mb-5">
                    <label for="memo" class="col-sm-3 col-form-label">Notes</label>
                    <div class="col-sm-9">
                        <textarea
                            name="memo"
                            class="form-control"
                            id="memo"
                            cols="30"
                            rows="3"></textarea>
                        <span data-error-message class="text-danger"></span>
                    </div>
                </div>
            </div>
            <div class="card-footer text-center">
                <button type="submit" class="btn btn-success">Save</button>
            </div>
        </div>

        <input type="hidden" name="dimension_id" value="{{ $dimension->id }}">
        <input type="hidden" name="sales_type" value="{{ $dimension->is_invoice_tax_included ? SALES_TYPE_TAX_INCLUDED : SALES_TYPE_TAX_EXCLUDED }}">
    </form>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    route.push('reference.next', '{{ rawRoute("api.reference.next", ["transType"]) }}');
    var parsleyForm = $("#create_contract_form").parsley();
    var stockItems = [];

    // read and initialise the stock items data from the dom
    (function() {
        var stockItemOptions = document.getElementById('stock_id').options;
        for (var i = 0; i < stockItemOptions.length; i++) {
            var stockItem = stockItemOptions[i];
            stockItems[i] = {
                id: stockItem.value,
                name: stockItem.text,
                category: stockItem.dataset.categoryId,
                nationality: stockItem.dataset.nationality
            }
        }
    })();

    calculateDifferenceInDays();
    regenerateItems();
    $('#category_id').on('change', regenerateItems);

    $('#stock_id, #category_id').on('change', function() {
        var stockId = $('#stock_id').val() || -1;
        var categoryId = $('#category_id').val() || -1;
        var nationality = stockId == -1 ? null : (stockItems.find(i => i.id === stockId).nationality || null);

        if ($('#labour_id').hasClass("select2-hidden-accessible")) {
            $('#labour_id').select2('destroy');
        }
        empty(document.querySelector('#labour_id'));
        initializeLaboursSelect2('#labour_id', {
            category_id: [categoryId],
            nationality: nationality ? [nationality] : []
        });
    });

    $('#type, #contract_from').on('change', function(e) {
        ajaxRequest({
            'url': route('reference.next', { transType: $('#type').val() }),
            'method': 'post',
            'data': {
                'context[date]': $('#contract_from').val()
            }
        }).done(function(resp) {
            if (resp && resp.data) {
                return $('#reference').val(resp.data);
            }

            defaultErrorHandler();
        }).fail(defaultErrorHandler);
    });

    $('#category_id, #amount').on('change', function(){
        if($('#category_id').val() == '{{ \App\Models\Inventory\StockCategory::DWD_PACKAGEONE }}')
            $('#prep_amount').val($('#amount').val());
        else
            $('#prep_amount').val('');
    });

    $('#contract_from, #contract_till').on('change', calculateDifferenceInDays);

    initializeCustomersSelect2('#debtor_no', {
        except: ['{{ \App\Models\Sales\Customer::WALK_IN_CUSTOMER }}']
    });

    

    parsleyForm.on('form:submit', function(e) {
        var form = parsleyForm.element;
        var formData = new FormData(form);
        var actionUrl = form.getAttribute('action');

        ajaxRequest({
            method: 'post',
            url: actionUrl,
            data: formData, 
            processData: false,
            contentType: false,
            dataType: "json",
            success: function(data, textStatus, xhr) {
                if (data && data.message) {
                    toastr.success(data.message);
                    form.reset();
                    return;
                }

                defaultErrorHandler()
            },
            error: function(xhr, desc, err) {
                if (xhr.status == 422) {
                    toastr.error(typeof xhr.responseJSON.message == 'string' ? xhr.responseJSON.message : 'Invalid Data');

                    if (xhr.responseJSON.errors) {
                        for (const [key, val] of Object.entries(xhr.responseJSON.errors)) {
                            $(`input[name="${key}"]`)
                                .closest('.row')
                                .find('span[data-error-message]')
                                .html(val.join('<br>'));
                        }
                    }
                    return;
                }
                    
                defaultErrorHandler()
            },
        });

        return false;
    });

    // resets the form as well as any validation errors
    parsleyForm.$element.on('reset', () => {
        parsleyForm.reset();
        setTimeout(() => {
            $('select').trigger('change.select2');
            $('[data-parsley-trigger-after-failure]').datepicker('update').trigger('changeDate').trigger('change')
        }, 0);
    })

    function regenerateItems() {
        var stockItemsSelect = document.getElementById('stock_id') ;
        var category = $('#category_id').val();
            
        // filter the stock items as per the category id
        var filteredItems = stockItems.filter(function(stockItem) {
            return stockItem.category === category
        });

        // prepare the dataSource for the select element
        var dataSource = [{id: '', text: '-- select --'}].concat(
            filteredItems.map(function(stockItem) {
                return {
                    id: stockItem.id,
                    text: stockItem.name,
                }
            })
        )
        
        if ($(stockItemsSelect).hasClass('select2-hidden-accessible')) {
            $(stockItemsSelect).select2('destroy');   
        }
        empty(stockItemsSelect);
        $(stockItemsSelect).select2({data: dataSource})
    }

    function calculateDifferenceInDays() {
        const fromDate = $('#contract_from').datepicker('getDate'); 
        const tillDate = $('#contract_till').datepicker('getDate'); 

        if (fromDate && tillDate) {
            const durationInDays = moment(tillDate).diff(moment(fromDate), 'days') + 1;
            let helpText = `${durationInDays} days`
            if (durationInDays == 1) {
                helpText = "1 day";
            }
            return $('[data-diff-in-days]').text(helpText);
        }

        $('[data-diff-in-days]').text('Please select both dates');
    }
})
</script>
@endpush