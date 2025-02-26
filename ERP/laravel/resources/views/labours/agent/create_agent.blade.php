@extends('layout.app')

@section('page')

<div class="container">
    <form action="{{ $url }}" method="post" id="create_agent_form" enctype="multipart/form-data">
        <input type="hidden" name="_method", value="{{ $method }}">
        <div class="card mw-850px mx-auto">
            <div class="card-header">
                <div class="card-title">
                    <h2>{{ $title }}</h2>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <label for="name" class="col-sm-2 col-form-label required">Agent Name</label>
                    <div class="col-sm-9">
                        <input required type="text" class="form-control" name="supp_name" id="name" value="{{ $inputs['supp_name'] }}">
                        <span class="text-danger"></span>
                    </div>
                </div>

                <div class="row mb-2">
                    <label for="contact_person" class="col-sm-2 col-form-label required">Contact Person</label>
                    <div class="col-sm-9">
                        <input required type="text" class="form-control" name="contact_person" id="contact_person" value="{{ $inputs['contact_person'] }}">
                        <span class="text-danger"></span>
                    </div>
                </div>

                <div class="row mb-2">
                    <label for="ref" class="col-sm-2 col-form-label required">Agent Ref. No.</label>
                    <div class="col-sm-9">
                        <input required type="text" class="form-control" name="supp_ref" id="ref" value="{{ $inputs['supp_ref'] }}">
                        <span class="text-danger"></span>
                    </div>
                </div>
                
                <div class="row mb-2">
                    <label for="tax_group_id" class="col-sm-2 col-form-label required">Tax Group</label>
                    <div class="col-sm-9">
                        <select required class="form-select" name="tax_group_id" id="tax_group_id">
                            <option value="">-- Select --</option>
                            @foreach ($taxGroups as $taxGroup)
                            <option @if ($taxGroup->id == $inputs['tax_group_id']) selected @endif
                                value="{{ $taxGroup->id }}">{{ $taxGroup->name }}</option>
                            @endforeach
                        </select>
                        <span class="text-danger"></span>
                    </div>
                </div>

                <div class="row mb-2">
                    <label for="arabic_name" class="col-sm-2 col-form-label">Arabic Name</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="arabic_name" id="arabic_name" value="{{ $inputs['arabic_name'] }}">
                        <span class="text-danger"></span>
                    </div>
                </div>

                <div class="row mb-2">
                    <label for="mobile_number" class="col-sm-2 col-form-label required">Mobile Number</label>
                    <div class="col-sm-9">
                        <input
                            required                            
                            type="text"
                            value="{{ $inputs['contact'] }}"
                            class="form-control"
                            name="contact"
                            id="mobile_number">
                        <span class="text-danger"></span>
                    </div>
                    
                </div>
                <div class="row mb-2">
                    <label for="email" class="col-sm-2 col-form-label required">E-mail</label>
                    <div class="col-sm-9">
                        <input required type="email" class="form-control" name="email" id="email" value="{{ $inputs['email'] }}">
                        <span class="text-danger"></span>
                    </div>
                </div>

                <div class="row mb-2">
                    <label for="location" class="col-sm-2 col-form-label">Location</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="location" id="location" value="{{ $inputs['location'] }}">
                        <span class="text-danger"></span>
                    </div>
                </div>
                
                <div class="row mb-2">
                    <label for="address" class="col-sm-2 col-form-label">Address</label>
                    <div class="col-sm-9">
                        <textarea
                            type="text"
                            maxlength="255"
                            class="form-control"
                            name="address"
                            id="address">{{ $inputs['address'] }}</textarea>
                        <span class="text-danger"></span>
                    </div>
                </div>

                <div class="row mb-2">
                    <label for="agent_photo" class="col-sm-2 col-form-label">Agent photo</label>
                    <div class="col-sm-9">
                        <input
                            type="file"
                            name="photo"
                            data-parsley-max-file-size="2"
                            accept="image/png, image/jpeg, image/jpg"
                            class="form-control"
                            id="agent_photo">
                        <span class="text-danger"></span>
                    </div>
                </div>
            </div>
            <div class="card-footer text-center">
                <button type="submit" class="btn btn-success">Save</button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    var parsleyForm = $("#create_agent_form").parsley();

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
                toastr.success(data.message);

                form.reset();
            },
            error: function(xhr, desc, err) {
                if (xhr.status == 422) {
                    toastr.error('Invalid data');
                    $.each(xhr.responseJSON.data, function(index, value) {
                        $('input[name='+index+']').parent().find('span').html(value[0]);
                    });
                } else {
                    defaultErrorHandler()
                }
            },
        });

        return false;
    });

    // resets the form as well as any validation errors
    parsleyForm.$element.on('reset', () => {
        parsleyForm.reset();
    })
</script>
@endpush