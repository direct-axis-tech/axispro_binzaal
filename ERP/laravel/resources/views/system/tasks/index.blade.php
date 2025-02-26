
@extends('layout.app')
@section('title', 'Manage Tasks')
@section('page')

@push('styles')
    <style>
        .comments-container{
            max-height:250px; 
            overflow-y: auto;
        }
        .comments-container  .media{
            padding: 10px 5px;
            background-color: #fff;
            box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.1);
        }
        .comments-container  .media  img{
            width: 50px; height: 50px;
            margin-right: 25px;
            border: 1px solid #f5f5f5;
        }
        .comments-container  .media  h6{
            font-size: 13px;
        }

        .tasks-table tr {
            position: relative;
        }

        .tasks-table thead th {
            background: #fff;,
            color: #181C32;
        }

        .tasks-table tbody tr:nth-child(odd) td {
            background-color: var(--bs-table-striped-bg);
            color: var(--bs-table-striped-color);
        }

        .tasks-table tbody tr:nth-child(even) td {
            background: #fff;
        }

        .tasks-table thead th:last-child,
        .tasks-table tbody td:last-child {
            position: sticky;
            z-index: 1;
            right: 0;
        }
    </style>
@endpush
    <div class="container-fluid ">
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <h3>Manage Tasks</h3>
                </div>
            </div>
            <div class="card-body">
                <form action="" id="filter-form">
                    <div class="row">
                        <div class="col-lg-2 mt-5">
                            <div class="form-group">
                                <label for="daterange_picker">Date Range: </label>
                                <div 
                                    id="daterange_picker"
                                    class="input-group input-daterange"
                                    data-provide="datepicker"
                                    data-date-format="<?= getDateFormatForBSDatepicker() ?>"
                                    data-date-clear-btn="true">
                                    <input
                                        type="text"
                                        class="form-control"
                                        name="date_from"
                                        id="date_from"
                                        autocomplete="off"
                                        placeholder="{{ dateformat('momentJs') }}"
                                        value="">
                                    <div class="input-group-text input-group-addon px-4 rounded-0 border-left-0 border-right-0">to</div>
                                    <input
                                        type="text"
                                        class="form-control"
                                        name="date_till"
                                        id="date_till"
                                        autocomplete="off"
                                        placeholder="{{ dateformat('momentJs') }}"
                                        value="">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 mt-5">
                            <label for="initiated_group_id">Initiator Group</label>
                            <select data-control="select2" class="form-control" name="initiated_group_id" id="initiated_group_id">
                                <option value="">--Select--</option>
                                @foreach ($flowGroups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 mt-5">
                            <label for="task_type">Task Type</label>
                            <select class="form-select" name="task_type" id="task_type">
                                <option value="">--Select--</option>
                                @foreach ($taskTypes as $types)
                                <option value="{{ $types->id }}"  {{ ($types->id == request('TaskType')) ? 'selected' : '' }}>{{ $types->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 mt-5">
                            <label for="department_id">Department</label>
                            <select data-control="select2" class="form-control" name="department_id" id="department_id">
                                <option value="">--Select--</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 mt-5">
                            <label for="initiated_by">Initiator</label>
                            <select data-control="select2" class="form-control" name="initiated_by" id="initiated_by">
                                <option value="">--Select--</option>
                                @foreach ($initiators as $user)
                                <option value="{{ $user->id }}" {{ ($user->id == request('InitiatedBy')) ? 'selected' : '' }} >{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 mt-5">
                            <label for="assigned_entity_type_id">Assigned Entity Type</label>
                            <select data-control="select2" class="form-control" name="assigned_entity_type_id" id="assigned_entity_type_id">
                                <option value="">--Select--</option>
                                @foreach ($entityTypes as $entityType)
                                    <option value="{{ $entityType->id }}">{{ $entityType->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 mt-5">
                            <label for="assigned_entity_id">Assigned Entity</label>
                            <select data-control="select2" class="form-control" name="assigned_entity_id" id="assigned_entity_id">
                                <option value="">--Select--</option>
                            </select>
                        </div>
                        <div class="col-lg-2 mt-5">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">--All--</option>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status }}">{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 mt-5">
                            <label for="completed_by">Completed By</label>
                            <select data-control="select2" class="form-control" name="completed_by" id="completed_by">
                                <option value="">--Select--</option>
                                @foreach ($performers as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 mt-5">
                            <label for="completed_by">Ref</label>
                            <input
                                type="text"
                                class="form-control"
                                name="reference"
                                id="reference"
                                autocomplete="off"
                                placeholder="Task Reference No."
                                value="{{ request('Ref') ?: '' }}">
                        </div>
                        <div class="col-12 text-center mt-10">
                            <button type="button" data-action="filter" class="btn btn-success">Apply</button>
                        </div>
                    </div>
                </form>
                <hr>
                <div class="row">
                    <div class="col-lg-12">
                        <table class="table table-bordered table-striped gx-3 w-100 min-h-300px text-nowrap thead-strong tasks-table" id="tasks-table"></table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <form id="addCommentForm" enctype="multipart/form-data">
        <div class="modal fade" id="addCommentModal" tabindex="-1" aria-labelledby="commentModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title" id="commentModalLabel">Comments</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-5 bg-light">
                        <div class="border">
                            <div class="comments-container mh-250px scroll-y p-3" id="comment_wrapper">
                            </div>
                        </div>
                        <!-- Second div in the modal body -->
                        <div class="p-3" >
                            <div class="form-group row mt-5">
                                <label class="col-sm-3 col-form-label" for="comment">Comment:</label>
                                <div class="col-sm-9">
                                    <input type="hidden" id="transition" value="">
                                    <input type="hidden" id="action" value="">
                                    <textarea
                                        name="comment"
                                        id="comment"
                                        data-parsley-pattern2="/^[\p{L}\p{M}\p{N}_\- ]+$/u"
                                        data-parsley-pattern2-message="The comment must only contains alphabets, numbers, dashes, underscore or spaces"
                                        class="form-control"
                                        required value=''></textarea>
                                </div>
                            </div>
                            <div class="form-group row mt-5">
                                <label class="col-sm-3 col-form-label" for="attachment">Attachment:</label>
                                <div class="col-sm-9">
                                    <input  type="file"
                                        id="attachment"
                                        name="attachment"
                                        class="form-control"
                                        data-parsley-max-file-size="2"
                                        accept="image/png, image/jpeg, application/pdf">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="CommentsubmitBtn" class="btn btn-info">Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
@push('scripts')
<script>
    $(function() {
        route.push('task.takeAction', '{{ rawRoute('task.takeAction') }}');
        route.push('task.comments.store', '{{ rawRoute('task.comments.store') }}');
        route.push('task.comments.index', '{{ rawRoute('task.comments.index') }}');
        route.push('file.view', '{{ rawRoute('file.view') }}');
        route.push('file.download', '{{ rawRoute('file.download') }}');

        let filters = null;
        let comments = [];

        const ENTITY_USER = {{ App\Models\Entity::USER }};
        const ENTITY_EMPLOYEE = {{ App\Models\Entity::EMPLOYEE }};
        const ENTITY_GROUP = {{ App\Models\Entity::GROUP }};
        const ENTITY_SPECIAL_GROUP = {{ App\Models\Entity::SPECIAL_GROUP }};
        const ENTITY_ACCESS_ROLE = '{{ App\Models\Entity::ACCESS_ROLE }}'

        const storage = {
            [ENTITY_USER]: @json($performers->values()),
            [ENTITY_EMPLOYEE]: @json($employees->values()),
            [ENTITY_GROUP]: @json($assignedGroups->values()),
            [ENTITY_SPECIAL_GROUP]: @json($specialGroups->values()),
            [ENTITY_ACCESS_ROLE]: @json($accessRoles),
            usesFaCode: @json($taskTypes->pluck('uses_fa_code', 'id'))
        }

        @if ($taskTransitionId)
        Task.show({{ $taskTransitionId }});
        @endif

        const parsleyCommentsForm = $('#addCommentForm').parsley(); 

        updateFilter();

        const table = $('#tasks-table').DataTable({
            ajax: fetchData,
            serverSide: true,
            paging: true,
            ordering: true,
            search: {
                return: true
            },
            rowId: 'task_transition_id',
            columns: [
                {
                    title: 'Req #',
                    data: 'task_id',
                    className: 'ps-3'
                },
                {title: 'Referernce No',        data: 'reference'},
                {title: 'Task Type',            data: 'task_type_name'},
                {title: 'Requested At',         data: 'formatted_initiated_at'},
                {title: 'Initiator Group',      data: 'initiated_group_name'},
                {title: 'Initiated By',         data: 'initiator_name'},
                {title: 'Department',           data: 'initiator_department_name'},
                {
                    title: 'Data',
                    data: '_display_data',
                    class: 'min-w-250px text-wrap',
                    width: '250px',
                    searchable: true,
                    orderable: false
                },
                {title: 'Status',  data: 'status'},
                {
                    title: '',
                    width: '20px',
                    data: '_action',
                    searchable: false,
                    orderable: false
                },
            ]
        })

        $('#assigned_entity_type_id').on('change', function() {
            const selectedValue = this.value;
            const assignedEntitySelect = document.getElementById('assigned_entity_id');
            const fragment = document.createDocumentFragment();

            empty(assignedEntitySelect);
            fragment.appendChild(new Option('--Select--', ''));
            (storage[selectedValue] || []).forEach(entity => {
                fragment.appendChild(new Option(entity.name, entity.id));
            });

            assignedEntitySelect.append(fragment);
        })

        $('[data-action="filter"]').on('click', () => {
            updateFilter();
            table.ajax.reload();
        });

        $('#tasks-table').on('click', '[data-action]', function() {
            const btn = this;

            Swal.fire({
                title: 'Are you sure?',
                text: `You are about to ${btn.dataset.action} this!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, I Confirm!'
            }).then(function(result) {
                if (result.value) {
                    const row = table.row(btn.closest('tr'));
                    const payload = {
                        transition: row.id(),
                        action: btn.dataset.action
                    };

                    ajaxRequest({
                        method: 'post',
                        url: parseInt(storage.usesFaCode[row.data().task_type_id])
                            ? url('/ERP/task_controller.php', payload)
                            : route('task.takeAction', payload)
                    }).done((resp, msg, xhr) => {
                        if (xhr.status == 204) {
                            toastr.success('Success');
                            table.ajax.reload();
                            return;
                        }
                        defaultErrorHandler();
                    }).fail(defaultErrorHandler)
                }
            })
        });

        $('#tasks-table').on('click', '[data-btn="view"]', function() {
            const row = table.row(this.closest('tr')).data();
            Task.show(row.task_transition_id);
        });
        
        $('#tasks-table').on('click', '[data-btn="comment"]', function() {
            const row = table.row(this.closest('tr')).data();
            $('#transition').val(row.task_transition_id);
            $('#action').val(this.dataset.method);
            ajaxRequest({
                url: route('task.comments.index', {task: row.task_id}),
                method: 'get',
                dataType: 'json'
            }).done((resp, txt, xhr) => {
                if (!Array.isArray(resp.data)) {
                    return defaultErrorHandler(xhr);
                }

                comments = resp.data;
                displayComments();
                $('#addCommentModal').modal('show');
            }).fail(defaultErrorHandler);
            
        });

        function displayComments() {
            let html = '';
            if (comments.length) {
                comments.forEach(function(comment) {
                    let relativeTime = moment(comment.created_at).calendar(null, {
                        sameDay: function (now) { return `[${this.from(now)}]`; },
                        sameElse: '{{ dateformat('momentJs') }}' + ' [at] h:mm:ss a'
                    });
                    
                    let file_html = (
                        comment.attachment != null
                            ? `
                                <p>
                                    Attachment:
                                    <a
                                        target="_blank"
                                        href="${ route('file.view', {name: 'Attachment', file: comment.attachment}) }"
                                        class="btn btn-sm p-2">
                                        <span class="fas fa-eye"></span>
                                    </a>
                                    <a
                                        href="${ route('file.download', {type: 'Attachment', file: comment.attachment}) }"
                                        class="btn btn-sm p-2">
                                        <span class="fas fa-download"></span>
                                    </a>
                                </p>`
                            : ''
                    );

                    html += (`
                        <div class="media d-flex mb-3">
                            <img src="${comment.user.avatar_url}" alt="" class="rounded-circle" >
                            <div class="media-body ">
                                <h6>Level ${comment.transition.state_id} : ${comment.user.real_name}</h6>
                                <p class="mb-0">${comment.comment}</p>
                                <span class="small text-muted pt-0">${relativeTime}</span>
                                ${file_html}
                            </div>
                        </div>`
                    )

                    // html += (`
                    //     <div class="row mb-3 px-3">
                    //         <div class="col-2">
                    //             <img src="${comment.user.avatar_url}" alt="" class="align-top rounded-circle w-50px h-50px me-3">
                    //         </div>
                    //         <div class="col-10 rounded-2 bg-white shadow-sm">
                    //             <div class="row p-2 border-bottom">
                    //                 <div class="col-6 text-start">
                    //                     <b>${comment.user.real_name}</b>
                    //                 </div>
                    //                 <div class="col-6 text-end small">
                    //                     <span class="text-muted">${relativeTime}</span> <br>
                    //                 </div>
                    //             </div>
                    //             <div class="row">
                    //                 <div class="col-12 p-5 position-relative">
                    //                     ${comment.comment}
                    //                     <span style="bottom: 0; right: 0;" class="position-absolute fs-9 px-5 pb-1 text-muted">Level ${comment.transition.state_id}</span>
                    //                 </div>
                    //             </div>
                    //         </div>
                    //     </div>`
                    // );
                });
            }

            else {
                html += '<div class="min-h-50px py-5 text-center text-muted">No Comments Yet</div>'
            }

            $('#comment_wrapper').html(html);
        }
         
        parsleyCommentsForm.on('form:submit', function() {
            var formData = new FormData(parsleyCommentsForm.element);
            var transitionId = $('#transition').val();
            ajaxRequest({
                method: 'post',
                url: route('task.comments.store', {transition: transitionId}),
                data: formData,
                processData: false,
                contentType: false
            }).done((resp, msg, xhr) => {
                if (!resp.data) {
                    return defaultErrorHandler(xhr);
                }
                
                comments.unshift(resp.data);
                displayComments();
                toastr.success(resp.message);
                resetForm();
            }).fail(defaultErrorHandler)

            return false;
        });

        function updateFilter() {
            filters = $('#filter-form')
                .serializeArray()
                .reduce((acc, curr) => {
                    acc[curr.name] = curr.value;
                    return acc;
                }, {});
        }

        function resetForm() {
            parsleyCommentsForm.element.reset();
            parsleyCommentsForm.reset();
        }

        function fetchData(data, callback, settings) {
            ajaxRequest({
                method: 'post',
                url: '{{ route('api.dataTable.tasks') }}',
                data: {...filters, ...data}
            })
            .done(function (resp) {
                if (!resp.data) {
                    return errorHandler();
                }

                callback(resp);
            })
            .fail(errorHandler);

            function errorHandler() {
                callback({
                    data: [],
                    draw: data.draw,
                    recordsFiltered: 0,
                    recordsTotal: 0
                });
                defaultErrorHandler();
            }
        }
    })
</script>
@endpush
