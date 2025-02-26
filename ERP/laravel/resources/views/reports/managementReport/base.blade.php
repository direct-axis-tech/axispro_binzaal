@extends('layout.app')

@section('title', 'Management Report')

@php
    use App\Permissions as P;

    $reports = array_filter(
        [
            [
                'link' => route('reports.sales.services'),
                'name' => "Service List",
                'canAccess' => auth()->user()->hasPermission(P::SA_SERVICEMSTRREP)
            ],
            [
                'link' => route('reports.sales.invoices'),
                'name' => "Invoice Report",
                'canAccess' => auth()->user()->hasPermission(P::SA_INVOICEREP)
            ],
            [
                'link' => route('reports.sales.invoicesPayments'),
                'name' => "Invoice Payment Report",
                'canAccess' => auth()->user()->hasPermission(P::SA_INVOICEPMTREP)
            ],
            [
                'link' => route('reports.sales.serviceTransactions'),
                'name' => "Service Report",
                'canAccess' => auth()->user()->hasAnyPermission(P::SA_SERVICETRANSREP_OWN, P::SA_SERVICETRANSREP_ALL)
            ],
            [
                'link' => route('reports.sales.voidedTransactions'),
                'name' => "Voided Transaction Report",
                'canAccess' => auth()->user()->hasPermission(P::SA_VOIDEDTRANSACTIONS)
            ],
        ],
        function ($r) { return $r['canAccess']; }
    );

    $currentUrl = url()->current();
@endphp

@push('styles')
<style>
    #reports-nav li.active {
        border-left: 5px solid var(--bs-primary);
    }
</style>
@endpush

@section('page')
<!--begin:ContentContainer-->
<div id="kt_content_container" class="w-100 position-relative offset-content-padding">
    <div class="d-none position-absolute h-100 d-inline-block p-3 ps-0 bg-body">
        <ul class="list-unstyled ps-0" id="reports-nav">
            @foreach ($reports as $report)
            <li class="{{
                class_names([
                    'mb-3',
                    'border-bottom' => !$loop->last,
                    'active' => $currentUrl == $report['link']
                ])
            }}">
                <a
                    href="{{ $report['link'] }}"
                    class="btn btn-toggle rounded">
                    <span class="la la-file-invoice me-3"></span>{{ $report['name'] }}
                </a>
            </li>
            @endforeach
        </ul>
    </div>
    <div class="p-10">
        @yield('report')
    </div>
</div>
<!--end:ContentContainer-->
@endsection

@prepend('scripts')
<script>
    $(function() {
        const form = document.querySelector('#filterForm');
        let formData = new FormData(form);
        const table = document.querySelector('[data-control="dataTable"]');
        let dataTable = null;

        $('[name="submit"]').on('click', e => {
            e.preventDefault();
            formData = new FormData(form);
            dataTable.draw();
        })

        $('button[data-export]').on('click', e => {
            const btn = e.target;
            const formData = new FormData(form);
            formData.append('to', btn.dataset.export);

            ajaxRequest({
                url: btn.dataset.url,
                contentType: false,
                processData: false,
                method: 'post',
                data: formData
            }).done(resp => {
                if (resp && resp.redirect_to) {
                    window.location = resp.redirect_to;
                } else {
                    defaultErrorHandler();
                }
            }).fail(defaultErrorHandler);
        })

        window.ServerSideDataTable = function ServerSideDataTable(columns) {
            dataTable = $(table).DataTable({
                processing: true,
                serverSide: true,
                ajax: ajaxRequest({
                    url: table.dataset.url,
                    method: 'post',
                    contentType: false,
                    processData: false,
                    eject: true,
                    data: function (data) {
                        buildURLQuery(data).forEach((value, key) => formData.set(key, value));
                        return formData;
                    }
                }),
                columns
            })

            return dataTable;
        }
    })
</script>
@endprepend
