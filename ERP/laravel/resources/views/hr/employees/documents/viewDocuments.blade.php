@extends('layout.app')

@section('title', 'Manage Employee Documents')

@section('page')



<div class="container mw-1200px">
    <div class="card">
        <div class="card-header border">
            <div class="card-title">
                <h2>Manage Employee Documents</h2>
            </div>
        </div>
        @if(session('success'))
            <div class="alert alert-success m-10 text-center">
                {{ session('success') }}
            </div>
        @endif

        <div class="card-body">
            <div class="row">
                <div class="col-lg-12">
                    <form action="{{ route('employeeDocument.manage') }}" id="form-entity-group" method="">
                    
                        <div class="row">
                            <div class="col-lg-6">
                                <label for="name">Name</label>
                                <select                                
                                    name="entity_id"
                                    class="form-control"
                                    data-placeholder="-- Select Employee --"
                                    data-allow-clear="true"
                                    data-control="select2"
                                    id="employee_id">
                                <option value="" >-- Select Employee --</option>
                                <?php foreach ($employees as $employee): ?>
                                    <option value="<?= $employee->id ?>"<?= (isset($_GET['entity_id']) &&  $_GET['entity_id'] == $employee->id) ? 'selected' : ''; ?>><?= $employee->formatted_name ?></option>
                                <?php endforeach; ?>
                            </select>
                            </div>
                            <div class="col-lg-2">
                                <label for="description">Document Type</label>
                                <select                                
                                name="document_type"
                                class="form-control"
                                data-control="select2"
                                data-parsley-unique="type"
                                data-parsley-trigger-after-failure="change"
                                data-placeholder="-- Select Document Type --"
                                id="document_type">
                                <option value="">-- Select Document Type --</option>
                                <?php foreach ($docTypes as $docType): ?>
                                <option value="<?= $docType->id ?>" <?php echo (isset($_GET['document_type']) && $_GET['document_type'] == $docType->id) ? 'selected' : ''; ?>><?= $docType->name ?></option>
                                <?php endforeach; ?>
                            </select>
                            </div>
                            <div class="col-lg-2">
                                <label for="description">Issued On</label>
                                <input
                                type="text"
                                data-provide="datepicker"
                                data-date-format="d-M-yyyy"
                                data-date-clear-btn="true"
                                data-date-autoclose="true"
                                data-parsley-trigger-after-failure="change"
                                class="form-control"
                                name="issued_on"
                                id="issued_on"
                                placeholder="d-M-yyyy"
                                value=<?php echo (isset($_GET['issued_on'])) ? $_GET['issued_on'] : ''; ?>
                                >
                            </div>
                            <div class="col-lg-2">
                                <label for="description">Expaires On</label>
                                <input
                                type="text"
                                data-provide="datepicker"
                                data-date-format="d-M-yyyy"
                                data-date-clear-btn="true"
                                data-date-autoclose="true"
                                data-parsley-trigger-after-failure="change"
                                class="form-control"
                                name="expires_on"
                                id="expires_on"
                                placeholder="d-M-yyyy"
                                value=<?php echo (isset($_GET['expires_on'])) ? $_GET['expires_on'] : ''; ?>
                                >
                            </div>
                            <div class="col-lg-2">
                                <label for="description">Reference</label>
                                <input
                                type="text"
                                minlength="3"
                                data-parsley-pattern="[a-zA-Z0-9\-_]+"
                                data-parsley-pattern-message="Only alphabets, numbers, dash & underscore are allowed"
                                class="form-control"
                                name="reference"
                                id="reference"
                                placeholder="ABC12345TD"
                                value=<?php echo (isset($_GET['reference'])) ? $_GET['reference'] : ''; ?>
                                >
                            </div>
                            <div class="col-lg-1">
                                <button type="submit" class="btn btn-primary mt-5">Search</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-12">
                    <table class="table table-bordered table-striped text-center" id="table-eg">
                        <thead class="table-dark">
                            <th>Doc.Id</th>
                            <th>Employee Name</th>
                            <th>Doc.Type</th>
                            <th>Issued Date</th>
                            <th>Expiry Date</th>
                            <th>Reference</th>
                            <th></th>
                        </thead>
                        <tbody>
                            @foreach ($doc_details as $doc_detail)
                            <tr>
                                <td>{{ $doc_detail->id ?: '-' }}</td>
                                <td>{{ $doc_detail->employee_name }}</td>
                                <td>{{ $doc_detail->document_type_name }}</td>
                                <td>{{ $doc_detail->issued_on ? $doc_detail->issued_on->format(dateformat()) : '' }}</td> 
                                <td>{{ $doc_detail->expires_on ? $doc_detail->expires_on->format(dateformat()) : '' }}</td> 
                                <td>{{ $doc_detail->reference }}</td>
                                <td>
                                    @if ($doc_detail->id)
                                        @if (authUser()->hasPermission('HRM_EDIT_DOC'))    
                                        <a href="{{ route('employeeDocument.edit', $doc_detail->id) }}" class="btn btn-sm btn-info btn-edit" data-id="{{ $doc_detail->id }}">
                                            <span class="fas fa-pencil-alt"></span>
                                        </a>
                                        @endif

                                        @if (authUser()->hasPermission('HRM_DELETE_DOC'))
                                        <button data-action="delete" type="submit" class="btn btn-sm btn-danger btn-delete" data-id="{{ $doc_detail->id }}">
                                            <span class="fas fa-trash-alt"></span>
                                        </button>
                                        @endif                                  

                                        <a target="_blank" href="{{ route('file.view', ['type' => 'document', 'file' => $doc_detail->file]) }}" class="btn btn-sm btn-info btn-view" data-id="{{ $doc_detail->id }}">
                                            <span class="fas fa-eye"></span>
                                        </a>
                                    
                                        <a href="{{ route("file.download", ['type' => 'shift-report', 'file' => $doc_detail->file]) }}" class="btn btn-sm btn-info btn-download" data-id="{{ $doc_detail->id }}">
                                            <span class="fas fa-download"></span>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="col-12">
                    <div class="pagination d-flex justify-around">
                        <p>Page {{ $doc_details->currentPage() }} of {{ $doc_details->lastPage() }}<br></p>
                        {{ $doc_details->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    route.push('employeeDocument.destroy', '{{ rawRoute('employeeDocument.destroy') }}')
    $(function () {
        $('#table-eg').on('click', '[data-action="delete"]', function () {
            const btn = this;
            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to destroy this document!",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, I Confirm!'
            }).then(function(result) {
                if (result.value) {
                    ajaxRequest({
                        url: route('employeeDocument.destroy', {id: btn.dataset.id}),
                        method: 'post',
                        data: {
                            _method: 'delete'
                        },
                        dataType: 'json'
                    }).done(function (res, msg, xhr) {
                        if (!res.message) {
                            return defaultErrorHandler(xhr);
                        }
                        
                        toastr.success(res.message);
                        window.location.reload();
                    }).fail(defaultErrorHandler)
                }
            });
        })
    });
</script>
@endpush
    