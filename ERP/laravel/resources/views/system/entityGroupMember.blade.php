@extends('layout.app')

@section('title', 'Entity Group Members')

@section('page')

<style>
    .select2-container .select2-selection--multiple {
        min-height: 100px; 
    }
</style>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <h2>Manage System Entity Group Members</h2>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success m-10 text-center">
                {{ session('success') }}
            </div>
        @endif

        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="row badge-secondary min-h-500px">
                        <div class="col-4 border">
                            <div class="card-header">
                                <div class="card-title w-100">
                                    <h2 class="text-center w-100">System Reserved Groups</h2>
                                </div>
                            </div>

                            <div class="list-group align-items-center mt-10" id="list-tab" role="tablist">
                                @foreach ($flowGroups as $group)
                                    <a class="list-group-item list-group-item-action btn btn-lg m-2 w-75 p-6" id="group-{{ $group->id }}" data-toggle="list" href="#group-tab-{{ $group->id }}" role="tab" >{{ $group->name }} 
                                        <span class="float-end">
                                            <i class="fa fa-arrow-right"></i>
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-8 border">
                            <div class="card-header">
                                <div class="card-title w-100">
                                    <h2 class="text-center w-100">Group Members</h2>
                                </div>
                            </div>

                            <div class="tab-content" id="nav-tabContent">
                                @foreach ($flowGroups as $group)
                                    <div class="tab-pane fade show p-4" id="group-tab-{{ $group->id }}" role="tabpanel" aria-labelledby="group-{{ $group->id }}">
                                        <div class="row form-group mt-5">
                                            <div class="col-md-12 text-center">
                                                <button class="btn btn-primary save-group float-end" data-group-id="{{ $group->id }}">Update Group Members</button>
                                            </div>
                                        </div>
                                        @foreach ($entityTypes as $type)
                                            <div class="row form-group mb-10">
                                                <div class="col-md-12">
                                                    <label for="group-entity-{{ $group->id. '-'.$type->id }}">{{ $type->name }}</label>
                                                    <select name="group-entity-{{ $group->id. '-'.$type->id }}" id="group-entity-{{ $group->id. '-'.$type->id }}" class="form-select select2" group-id="{{ $group->id }}" type-id="{{ $type->id }}" multiple >
                                                        <option value="" disabled > -- Select -- </option> 
                                                        @foreach (($entities[$type->id] ?? []) as $entity)
                                                            <option value="{{ $entity->id }}" {{ in_array($entity->id, data_get(data_get($groupMembers, $group->id, []), $type->id, [])) ? 'selected' : '' }} >{{ $entity->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>                   
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')

<script>

$(function () {
    
    $('.select2').select2();

    // $('#list-tab a:first-child').tab('show');

    $('#list-tab a').on('click', function (e) {
        e.preventDefault()
        $(this).tab('show');
    });

    route.push('updateGroupMembers', '{{ rawRoute('entityGroupMembers.update') }}');

    $(document).on('click', '.save-group', function() {

        var groupId = $(this).data('group-id');
        var groupData = [];

        $('#group-tab-' + groupId + ' select').each(function() {

            var entityTypeId = $(this).attr('type-id');
            var selectedValues = $(this).val();
            var data = {
                groupId: groupId,
                entityTypeId: entityTypeId,
                groupMembers: selectedValues
            };

            groupData.push(data);
        });

        ajaxRequest({
            url: route('updateGroupMembers'),
            method: 'POST',
            data: {
                groupData: JSON.stringify(groupData)
            }
        }).done((data, msg, resp) => {
            if (resp.status == 200) {
                Swal.fire(
                    'Success',
                    'Group Members Updated Successfully !',
                    'success'
                )
                window.location.reload();
            } else {
                defaultErrorHandler()
            }

        }).fail((xhr) => {
            if (xhr.status == 422 && xhr.responseJSON && xhr.responseJSON.message) {
                return toastr.error(xhr.responseJSON.message);
            }
            return defaultErrorHandler();
        });

    });


});


</script>
@endpush
