
@push('styles')
<style>
    .comments-container {
        max-height:250px; 
        overflow-y: auto;
    }

    .comments-container .media {
        padding: 10px 5px;
        background-color: #fff;
        box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.1);
    }

    .comments-container .media img {
        width: 50px; height: 50px;
        margin-right: 25px;
        border: 1px solid #f5f5f5;
    }

    .comments-container .media h6 {
        font-size: 13px;
    }

    .button-container {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .button-container button {
        margin: 5px;
    }

    .card-header .outer-div {
        width: 100%;
    }

    .transitions-wrapper {
      background-color: #f8f9fa;
    }
</style> 
@endpush

<div class="modal fade" id="TaskViewModal" tabindex="-1" aria-labelledby="TaskViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="TaskViewModalLabel">Task view</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-5 bg-light">
                <div class="card">
                    <div class="card-header">
                        <div class="row outer-div pt-3">
                            <div class="col-md-6 col-lg-4">
                                <div class="row">
                                    <div class="col-lg-6 col-form-label">Referernce No:</div>
                                    <div class="col-lg-6">
                                        <span class="form-control-plaintext" data-place="reference"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="row">
                                    <div class="col-lg-6 col-form-label">Task Type:</div>
                                    <div class="col-lg-6">
                                        <span class="form-control-plaintext" data-place="task_type_name"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="row">
                                    <div class="col-lg-6 col-form-label">Requested At:</div>
                                    <div class="col-lg-6">
                                        <span class="form-control-plaintext" data-place="formatted_initiated_at"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="row">
                                    <div class="col-lg-6 col-form-label">Initiator Group:</div>
                                    <div class="col-lg-6">
                                        <span class="form-control-plaintext" data-place="initiated_group_name"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="row">
                                    <div class="col-lg-6 col-form-label">Initiated By:</div>
                                    <div class="col-lg-6">
                                        <span class="form-control-plaintext" data-place="initiator_name"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="row">
                                    <div class="col-lg-6 col-form-label">Initiator Department:</div>
                                    <div class="col-lg-6">
                                        <span class="form-control-plaintext" data-place="initiator_department_name"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="bg-light col-lg-6 px-5 py-3 rounded" data-place="viewHtml">
                                <!-- Dynamic -->
                            </div>

                            <div class="col-lg-6">
                                <h6 class="py-3">Comments</h6>
                                <form data-add-comments enctype="multipart/form-data">
                                    <div data-place="comments" class="comments-container mh-250px scroll p-3">
                                        <!-- Dynamic -->
                                    </div>
                                    <div class="p-3" >
                                        <div class="form-group row mt-5">
                                            <label class="col-lg-4 col-form-label" for="comment">Comment:</label>
                                            <div class="col-lg-8">
                                                <textarea
                                                    name="comment"
                                                    data-parsley-pattern2="/^[\p{L}\p{M}\p{N}_\- ]+$/u"
                                                    data-parsley-pattern2-message="The comment must only contains alphabets, numbers, dashes, underscore or spaces"
                                                    class="form-control"
                                                    required value=''></textarea>
                                            </div>
                                        </div>
                                        <div class="form-group row mt-5">
                                            <label class="col-lg-4 col-form-label" for="attachment">Attachment:</label>
                                            <div class="col-lg-8">
                                                <input
                                                    type="file"
                                                    name="attachment"
                                                    class="form-control"
                                                    data-parsley-max-file-size="2"
                                                    accept="image/png, image/jpeg, application/pdf">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="button-container">
                                        <button type="submit" class="btn btn-info btn-sm">Submit</button>
                                    </div>
                                </form>
                            </div>
              
                            <div class="col-md-12 p-5 transitions-wrapper mt-5">
                                <div class="table-responsive">
                                    <table class="table table-bordered gx-3 thead-strong table-striped text-nowrap table-hover">
                                        <thead>
                                            <tr>
                                                <th class="font-weight-bold">State</th>
                                                <th class="font-weight-bold">Assigned To</th>
                                                <th class="font-weight-bold">Assigned At</th>
                                                <th class="font-weight-bold">Action Taken</th>
                                                @if(auth()->user()->hasPermission('HRM_TASK_PERFORMER_DETAILS'))
                                                    <th class="font-weight-bold">Performed By</th>
                                                    <th class="font-weight-bold">Performed At</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody data-place="transitions">
                                            <!-- Dynamic -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
        
                    <div class="card-footer text-center" data-place="actions">
                        <!-- Dynamic -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@prepend('scripts')
<script>
    window.User = {
        canViewPerformerDetails: @json(authUser()->hasPermission(\App\Permissions::HRM_TASK_PERFORMER_DETAILS))
    }
    $(function() {
        route.push('task.comments.store', '{{ rawRoute('task.comments.store') }}');
        route.push('task.takeAction', '{{ rawRoute('task.takeAction') }}');
        route.push('file.view', '{{ rawRoute('file.view') }}');
        route.push('file.download', '{{ rawRoute('file.download') }}');
        route.push('task.show', '{{ rawRoute('task.show') }}');

        const modalId = 'TaskViewModal';
        const modal = new bootstrap.Modal(document.getElementById(modalId));
        const parsleyCommentsFormTaskView = $(`#${modalId} form[data-add-comments]`).parsley(); 
        const storage = {
            usesFaCode: @json(\App\Models\TaskType::all()->pluck('uses_fa_code', 'id')),
            comments : [],
            transitions: [],
            task_type_id : '',
            transition : '',
        };
        const dateFormat = '{{ dateformat('momentJs') }}';
        const dateTimeFormat = `${dateFormat} h:mm A`;

        function showTask(transitionId) {
            ajaxRequest({
                url: route('task.show', {transition: transitionId}),
                method: 'GET',
            })
            .done((respJson, msg, xhr) => {
                if (!respJson) {
                    return defaultErrorHandler(xhr);
                }

                storage.comments = respJson.task.comments;
                storage.transitions = respJson.task.transitions;
                storage.task_type_id = respJson.taskRecord.task_type_id;
                storage.transition = respJson.taskRecord.task_transition_id;
                displayCommentsOnTaskView();
                displayTransitionsOnTaskView();

                let array = [
                    'reference',
                    'task_type_name',
                    'formatted_initiated_at',
                    'initiated_group_name',
                    'initiator_name',
                    'initiator_department_name'
                ];
                array.forEach(key => {
                    document.querySelector(`#${modalId} [data-place="${key}"]`).textContent = respJson.taskRecord[key];
                })

                array = [
                    'viewHtml',
                    'actions'
                ];
                array.forEach(key => {
                    document.querySelector(`#${modalId} [data-place="${key}"]`).innerHTML = respJson[key];
                })

                modal.show();
            })
        }

        function displayCommentsOnTaskView() {
            $(`#${modalId} [data-place="comments"]`).empty();

            if (!storage.comments.length) {
                return;
            }

            let html = '';
            storage.comments.forEach(function(comment) {
                let relativeTime = moment(comment.created_at).calendar(null, {
                    sameDay: function (now) { return `[${this.from(now)}]`; },
                    sameElse: dateFormat + ' [at] h:mm:ss a'
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
                    <div class="media d-flex mb-3 min-w-300px">
                        <img src="${comment.user.avatar_url}" alt="" class="rounded-circle" >
                        <div class="media-body ">
                            <h6>Level ${comment.transition.state_id} : ${comment.user.real_name}</h6>
                            <p class="mb-0">${comment.comment}</p>
                            <span class="small text-muted pt-0">${relativeTime}</span>
                            ${file_html}
                        </div>
                    </div>`
                )
            });
            
            $(`#${modalId} [data-place="comments"]`).append(html);
        }

        function displayTransitionsOnTaskView() {
            $(`#${modalId} [data-place="transitions"]`).empty();

            if (!storage.transitions.length) {
                return;
            }

            let html = '';
            storage.transitions.forEach(function(transition) {
                let assigned_at =  moment(transition.assigned_at).format(dateTimeFormat); 
                let completed_at = transition.completed_at
                    ? moment(transition.completed_at).format(dateTimeFormat)
                    : ''; 

                let performerDetails = window.User.canViewPerformerDetails
                    ? `<td>${transition.performer_name != null ? transition.performer_name : ''}</td>
                       <td>${completed_at}</td>`
                    : '';

                html += `
                    <tr>
                        <td>${transition.state_id}</td>
                        <td>${transition.assigned_entity_name}</td>
                        <td>${assigned_at}</td>
                        <td>${transition.status}</td>
                        ${performerDetails}
                    </tr>`;
            });
            $(`#${modalId} [data-place="transitions"]`).html(html);
        }


        parsleyCommentsFormTaskView.on('form:submit', function() {
            var formData = new FormData(parsleyCommentsFormTaskView.element);
            ajaxRequest({
                method: 'post',
                url: route('task.comments.store', {transition: storage.transition}),
                data: formData,
                processData: false,
                contentType: false
            }).done((resp, msg, xhr) => {
                if (!resp.data) {
                    return defaultErrorHandler(xhr);
                }
                storage.comments.unshift(resp.data);
                displayCommentsOnTaskView();
                toastr.success(resp.message);
                resetFormTaskView();
            }).fail(defaultErrorHandler)

            return false;
        });

        $(`#${modalId}`).on('hidden.bs.modal', function () {
            resetFormTaskView();
        });

        $(`#${modalId} [data-place="actions"]`).on('click', 'button[data-action]', function() {
            const btn = this;
            const payload = {
                transition: storage.transition,
                action: btn.dataset.action
            }
            Swal.fire({
                title: 'Are you sure?',
                text: `You are about to ${btn.dataset.action} this!`,
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, I Confirm!'
            }).then(function(result) {
                if (result.value) {
                    ajaxRequest({
                        method: 'post',
                        url: parseInt(storage.usesFaCode[storage.task_type_id])
                            ? url('/ERP/task_controller.php', payload)
                            : route('task.takeAction', payload),
                    }).done((resp, msg, xhr) => {
                        if (xhr.status == 204) {
                            toastr.success('Success');  
                            modal.hide();
                            location.reload();
                            return;
                        }
                        defaultErrorHandler(xhr);
                    }).fail(defaultErrorHandler)
                }
            })
        });
        
        function resetFormTaskView() {
            parsleyCommentsFormTaskView.element.reset();
            parsleyCommentsFormTaskView.reset();
        }

        window.Task = {show: showTask};
    });
</script>  
@endPrepend