@extends('layout.app')
@section('title', 'Create New Labour')
@push('styles')
<style>
#fade {
        display: none;
        position: fixed;
        top: 0%;
        left: 0%;
        width: 100%;
        height: 100%;
        background-color: black;
        z-index: 1001;
        -moz-opacity: 0.8;
        opacity: .80;
        filter: alpha(opacity=80);
        }
#light {
    display: none;
    position: absolute;
    top: 50%;
    left: 50%;
    max-width: 600px;
    max-height: 360px;
    margin-left: -300px;
    margin-top: -180px;
    border: 2px solid #FFF;
    background: #FFF;
    z-index: 1002;
    overflow: visible;
}
#boxclose {
            float: right;
            cursor: pointer;
            color: #fff;
            border: 1px solid #AEAEAE;
            border-radius: 3px;
            background: #222222;
            font-size: 31px;
            font-weight: bold;
            display: inline-block;
            line-height: 0px;
            padding: 11px 3px;
            position: absolute;
            right: 2px;
            top: 2px;
            z-index: 1002;
            opacity: 0.9;
        }

        .boxclose:before {
            content: "×";
        }        
</style> 
@endpush
@section('page')
    <div class="container-fluid">
        <form action="{{ $url }}" id="create_labour_form" enctype="multipart/form-data">
            @csrf()
            @if ($is_editing)
                @method('put')
                <input type="hidden" id="labour_id" value="{{ $labour_id }}">
            @endif
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <h2>{{ $title }}</h2>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 col-lg-4">
                            <div class="row mb-3">
                                <label for="name" class="col-sm-3 col-form-label required">Name : </label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="name" name="name" required
                                        value="{{ $inputs['name'] }}">
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="name" class="col-sm-3 col-form-label required">Maid Code : </label>
                                <div class="col-sm-9">
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="maid_ref"
                                        data-parsley-is-ref-unique="true"
                                        name="maid_ref"
                                        required
                                        value="{{ $inputs['maid_ref'] }}">
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="arabic_name" class="col-sm-3 col-form-label">Arabic Name</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="arabic_name" name="arabic_name"
                                        value="{{ $inputs['arabic_name'] }}">
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="mothers_name" class="col-sm-3 col-form-label">Mothers name</label>
                                <div class="col-sm-9">
                                    <input class="form-control" type="text" name="mothers_name" id="mothers_name"
                                        value="{{ $inputs['mothers_name'] }}">
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="mobile_number" class="col-sm-3 col-form-label required">Mobile Number</label>
                                <div class="col-sm-9">
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="mobile_number"
                                        name="mobile_number"
                                        data-parsley-pattern="\d{5,14}"
                                        data-parsley-pattern-message="This seems like not a valid number"
                                        required
                                        value="{{ $inputs['mobile_number'] }}">
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="address" class="col-sm-3 col-form-label">Address</label>
                                <div class="col-sm-9">
                                    <textarea
                                        name="address"
                                        id="address"
                                        cols="30"
                                        rows="1"
                                        class="form-control"
                                        >{{ $inputs['address'] }}</textarea>
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="religion" class="col-sm-3 col-form-label">Religion</label>
                                <div class="col-sm-9">
                                    <select class="form-select" name="religion" id="religion">
                                        <option value="">--SELECT--</option>
                                        @foreach ($religions as $religion)
                                            <option
                                                @if ($religion->id == $inputs['religion'])
                                                selected
                                                @endif
                                                value="{{ $religion->id }}">
                                                {{ $religion->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="nationality" class="col-sm-3 col-form-label required">Nationality</label>
                                <div class="col-sm-9">
                                    <select class="form-select" name="nationality" id="nationality" required>
                                        <option value="">--SELECT--</option>
                                        @foreach ($countries as $country)
                                            <option
                                                @if ($country->code == $inputs['nationality'])
                                                selected
                                                @endif
                                                value="{{ $country->code }}">
                                                {{ $country->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="gender" class="col-sm-3 col-form-label required">Gender</label>
                                <div class="col-sm-9">
                                    <select name="gender" id="gender" class="form-select" required>
                                        <option value="">--SELECT--</option>
                                        @foreach ($genders as $key => $val)
                                            <option
                                                @if ($key == $inputs['gender'])
                                                selected
                                                @endif
                                                value="{{ $key }}">
                                                {{ $val }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>

                            <div class="row mb-3 ">
                                <label for="dob" class="col-sm-3 col-form-label required">DOB:</label>
                                <div class="col-sm-9">
                                    <input
                                        type="text"
                                        required
                                        data-provide="datepicker"
                                        data-date-format="{{ getBSDatepickerDateFormat() }}"
                                        data-date-clear-btn="true"
                                        data-date-autoclose="true"
                                        data-parsley-trigger-after-failure="change"
                                        class="form-control"
                                        name="dob"
                                        id="dob"
                                        placeholder="{{ getBSDatepickerDateFormat() }}"
                                        value="{{ $inputs['dob'] }}"
                                        required>
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="height" class="col-sm-3 col-form-label">Height:</label>
                                <div class="col-sm-9">
                                    <input class="form-control" type="number" name="height" id="height"
                                        value="{{ $inputs['height'] }}">
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="weight" class="col-sm-3 col-form-label">Weight:</label>
                                <div class="col-sm-9">
                                    <input class="form-control" type="number" name="weight" id="weight"
                                        value="{{ $inputs['weight'] }}">
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="row mb-3">
                                <label for="marital_status" class="col-sm-3 col-form-label">Marital Status</label>
                                <div class="col-sm-9">
                                    <select name="marital_status" id="marital_status" class="form-select">
                                        <option value="">--SELECT--</option>
                                        @foreach ($marital_statuses as $key => $val)
                                            <option
                                                @if ($key == $inputs['marital_status'])
                                                selected
                                                @endif
                                                value="{{ $key }}">
                                                {{ $val }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="no_of_children" class="col-sm-3 col-form-label">No of children:</label>
                                <div class="col-sm-9">
                                    <input class="form-control" type="number" name="no_of_children" id="no_of_children"
                                        value="{{ $inputs['no_of_children'] }}">
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="mother_tongue" class="col-sm-3 col-form-label required">Mother Tongue</label>
                                <div class="col-sm-9">
                                    <select name="mother_tongue" id="mother_tongue"  class="form-select" required>
                                        <option value="">--SELECT--</option>
                                        @foreach ($languages as $lang)
                                            <option
                                                @if ($lang->id == $inputs['mother_tongue'])
                                                selected
                                                @endif
                                                value="{{ $lang->id }}">
                                                {{ $lang->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="place_of_birth" class="col-sm-3 col-form-label">Place of birth</label>
                                <div class="col-sm-9">
                                    <input class="form-control" type="text" name="place_of_birth" id="place_of_birth"
                                        value="{{ $inputs['place_of_birth'] }}">
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="education" class="col-sm-3 col-form-label">Education</label>
                                <div class="col-sm-9">
                                    <select class="form-select" type="text" name="education" id="education">
                                        <option value="">--SELECT--</option>
                                        @foreach ($education_levels as $k => $v)
                                            <option
                                                @if ($inputs['education'] == $k)
                                                selected
                                                @endif
                                                value="{{ $k }}">
                                                {{ $v }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-3 col-form-label">Known Languages</label>
                                <div class="col-sm-9" id="known_languages">
                                @foreach ($inputs['languages'] as $i => $knownLanguage)
                                    <div data-parsley-form-group>
                                        <div class="input-group mb-3">
                                            <select data-key="language_id" class="form-select" name="languages[{{ $i }}][id]">
                                                <option value="">--SELECT--</option>
                                                @foreach ($languages as $language)
                                                    <option
                                                        @if ($language->id == $knownLanguage['id'])
                                                        selected
                                                        @endif
                                                        value="{{ $language->id }}">
                                                        {{ $language->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <select
                                                data-parsley-validate-if-empty
                                                data-parsley-required-if-not-empty="languages[{{ $i }}][id]"
                                                data-parsley-required-if-not-empty-message="Please select the proficiency"
                                                data-key="language_proficiency"
                                                class="form-select rounded-end"
                                                name="languages[{{ $i }}][proficiency]">
                                                <option value="">--SELECT--</option>
                                                @foreach ($language_proficiencies as $k => $v)
                                                    <option
                                                        @if ($k == $knownLanguage['proficiency'])
                                                        selected
                                                        @endif
                                                        value="{{ $k }}">{{ $v }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @if ($loop->last)
                                                <button data-action="addKnownLang" type="button" class="btn ps-2 pe-0">
                                                    <span class="fa fa-2x fa-plus-circle text-primary"></span>
                                                </button>
                                            @endif
                                            <span class="error_message text-danger"></span>
                                        </div>
                                    </div>
                                @endforeach
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="skills" class="col-sm-3 col-form-label">Skills</label>
                                <div class="col-sm-9">
                                    <select name="skills[]" multiple id="skills" class="form-select">
                                        <option value="">--SELECT--</option>
                                        @foreach ($labour_skills as $key => $val)
                                            <option
                                                @if (!empty($inputs['skills']) && in_array($key, $inputs['skills']))
                                                selected
                                                @endif
                                                value="{{ $key }}">
                                                {{ $val }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="work_experience" class="col-sm-3 col-form-label">Work Experience</label>
                                <div class="col-sm-9">
                                    <textarea
                                        name="work_experience"
                                        class="form-control"
                                        id="work_experience"
                                        cols="30"
                                        rows="1">{{ $inputs['work_experience'] }}</textarea>
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-9 input-group" >
                                <label for="passport_size_photo" class="{{ class_names([
                                    'col-sm-3',
                                    'col-form-label',
                                    'required' => $pp_photo->is_required && empty($pp_photo->file)
                                ]) }}">Passport size photo</label>
                                    <input {{ $pp_photo->is_required && empty($pp_photo->file) ? 'required' : '' }}
                                        type="file"
                                        id="passport_size_photo"
                                        name="{{ "docs[$pp_photo->id][file]" }}"
                                        class="form-control"
                                        data-parsley-max-file-size="2"
                                        accept="image/png, image/jpeg">
                                    <span class="error_message text-danger"></span>
                                    <div class="input-group-append">
                                            <div class="input-group-text rounded-start-0">
                                                @if ($pp_photo->file)
                                                <a
                                                    href="{{ route('file.view', ['type' => 'document', 'file' => $pp_photo->file]) }}"
                                                    class="p-0"
                                                    {{ $pp_photo->file }}>
                                                    <span class="fa fa-eye"></span>
                                                </a>
                                                @else
                                                <button
                                                    type="button"
                                                    class="btn p-0"
                                                    disabled>
                                                        <span class="fa fa-eye"></span>
                                                </button>
                                                @endif
                                            </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-9 input-group">
                                <label for="full_body_photo" class="{{ class_names([
                                    'col-sm-3',
                                    'col-form-label',
                                    'required' => $fs_photo->is_required && empty($fs_photo->file)
                                ]) }}">Full body photo</label>
                                    <input {{ $fs_photo->is_required && empty($fs_photo->file) ? 'required' : '' }}
                                        type="file"
                                        id="full_body_photo"
                                        name="{{ "docs[$fs_photo->id][file]" }}"
                                        class="form-control"
                                        data-parsley-max-file-size="3"
                                        accept="image/png, image/jpeg">
                                    <span class="error_message text-danger"></span>
                                    <div class="input-group-append">
                                            <div class="input-group-text rounded-start-0">
                                                @if ($fs_photo->file)
                                                <a
                                                    href="{{ route('file.view', ['type' => 'document', 'file' => $fs_photo->file]) }}"
                                                    class="p-0"
                                                    {{ $fs_photo->file }}>
                                                    <span class="fa fa-eye"></span>
                                                </a>
                                                @else
                                                <button
                                                    type="button"
                                                    class="btn p-0"
                                                    disabled>
                                                        <span class="fa fa-eye"></span>
                                                </button>
                                                @endif
                                            </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 ">
                                <div class="col-sm-9 input-group">
                                <label for="video" class="col-sm-3 col-form-label">Video </label>
                                    <input
                                        class="form-control"
                                        type="file"
                                        name="video"
                                        id="video"
                                        accept="video/mp4">
                                    <span class="error_message text-danger"></span>
                                    <button type="button" @if($inputs['video']) onclick="lightbox_open('{{route('file.view', ['name' => 'video', 'file' => $inputs['video']])}}')" @else disabled @endif class="btn btn-primary btn-sm video" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                    <i class="fas fa-video"></i>
                                </button>    
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="row mb-3">
                                <label for="agent_id" class="col-sm-3 col-form-label required">Agent</label>
                                <div class="col-sm-9">
                                    <select required class="form-select" name="agent_id" id="agent_id">
                                        <option value="">--SELECT--</option>
                                        @foreach ($agents as $agent)
                                            <option
                                                @if ($agent->id== $inputs['agent_id'])
                                                selected
                                                @endif
                                                value="{{ $agent->id }}">
                                                {{ $agent->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="job_type" class="col-sm-3 col-form-label">Job Type</label>
                                <div class="col-sm-9">
                                    <select name="job_type" class="form-select" id="job_type">
                                        <option value="">--SELECT--</option>
                                        @foreach ($job_types as $key => $val)
                                            <option
                                                @if ($key == $inputs['job_type'])
                                                selected
                                                @endif
                                                value="{{ $key }}">
                                                {{ $val }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="type" class="col-sm-3 col-form-label">Position</label>
                                <div class="col-sm-9">
                                    <select name="type" id="type" class="form-select">
                                        <option value="">--SELECT--</option>
                                        @foreach ($labour_types as $key => $val)
                                            <option
                                                @if ($key == $inputs['type'])
                                                selected
                                                @endif
                                                value="{{ $key }}">
                                                {{ $val }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="category" class="col-sm-3 col-form-label required">Maid category</label>
                                <div class="col-sm-9">
                                    <select class="form-select" name="category" id="category" required>
                                        <option value="">--SELECT--</option>
                                        @foreach ($labour_categories as $key => $val)
                                            <option
                                                @if ($key == $inputs['category'])
                                                selected
                                                @endif
                                                value="{{ $key }}">
                                                {{ $val }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="category" class="col-sm-3 col-form-label">Maid Status</label>
                                <div class="col-sm-9">
                                    <select class="form-select" name="maid_status" id="maid_status">
                                        <option value="">--SELECT--</option>
                                        @foreach ($maid_status as $key => $val)
                                            <option
                                                @if ($key == $inputs['maid_status'])
                                                    selected
                                                @endif
                                                value="{{ $key }}">
                                                {{ $val }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="location" class="col-sm-3 col-form-label">Location</label>
                                <div class="col-sm-9">
                                    <select name="locations[]" multiple id="location" class="form-select">
                                        <option value="">--SELECT--</option>
                                        @foreach ($emirates as $emirate)
                                            <option
                                                @if (!empty($inputs['locations']) && in_array($emirate->id, $inputs['locations']))
                                                selected
                                                @endif
                                                value="{{ $emirate->id }}">
                                                {{ $emirate->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="application_date" class="col-sm-3 col-form-label">Application Date</label>
                                <div class="col-sm-9">
                                    <input
                                        type="text"
                                        data-provide="datepicker"
                                        data-date-format="{{ getBSDatepickerDateFormat() }}"
                                        data-date-clear-btn="true"
                                        data-date-autoclose="true"
                                        data-parsley-trigger-after-failure="change"
                                        class="form-control"
                                        name="application_date"
                                        id="application_date"
                                        value="{{ $inputs['application_date'] }}"
                                        placeholder="{{ getBSDatepickerDateFormat() }}">
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="date_of_joining" class="col-sm-3 col-form-label">Date of Joining</label>
                                <div class="col-sm-9">
                                    <input
                                        type="text"
                                        data-provide="datepicker"
                                        data-date-format="{{ getBSDatepickerDateFormat() }}"
                                        data-date-clear-btn="true"
                                        data-date-autoclose="true"
                                        data-parsley-trigger-after-failure="change"
                                        class="form-control"
                                        name="date_of_joining"
                                        id="date_of_joining"
                                        value="{{ $inputs['date_of_joining'] }}"
                                        placeholder="{{ getBSDatepickerDateFormat() }}">
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="basic_salary" class="col-sm-3 col-form-label">Basic Salary:</label>
                                <div class="col-sm-9">
                                    <input class="form-control" type="number" step="0.01" name="basic_salary" id="basic_salary"
                                        value="{{ $inputs['basic_salary'] }}" onchange="handleSalary()">
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="accommodation_allowance" class="col-sm-3 col-form-label">Accommodation Allowance:</label>
                                <div class="col-sm-9">
                                    <input class="form-control" type="number" step="0.01" name="accommodation_allowance" id="accommodation_allowance"
                                           value="{{ $inputs['accommodation_allowance'] }}" onchange="handleSalary()">
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="transportation_allowance" class="col-sm-3 col-form-label">Transportation Allowance:</label>
                                <div class="col-sm-9">
                                    <input class="form-control" type="number" step="0.01" name="transportation_allowance" id="transportation_allowance"
                                           value="{{ $inputs['transportation_allowance'] }}" onchange="handleSalary()">
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="allowance" class="col-sm-3 col-form-label">Other Allowance:</label>
                                <div class="col-sm-9">
                                    <input class="form-control" type="number" step="0.01" name="other_allowance" id="other_allowance"
                                           value="{{ $inputs['other_allowance'] }}" onchange="handleSalary()">
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="salary" class="col-sm-3 col-form-label">Total Salary:</label>
                                <div class="col-sm-9">
                                    <input readonly class="form-control" type="number" step="0.01" name="salary" id="salary"
                                           value="{{ $inputs['salary'] }}">
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="mol_id" class="col-sm-3 col-form-label">MOL Number:</label>
                                <div class="col-sm-9">
                                    <input class="form-control" type="text" name="mol_id" id="mol_id"
                                           value="{{ $inputs['mol_id'] }}">
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="bank_id" class="col-sm-3 col-form-label">Bank Name:</label>
                                <div class="col-sm-9">
{{--                                    <input class="form-control" type="text" name="bank_name" id="bank_name"--}}
{{--                                           value="{{ $inputs['bank_name'] }}">--}}
                                    <select class="form-select" name="bank_id" id="bank_id">
                                        <option value="">--SELECT--</option>
                                        @foreach ($banks as $bank)
                                            <option
                                                @if ($bank->id== $inputs['bank_id'])
                                                    selected
                                                @endif
                                                value="{{ $bank->id }}">
                                                {{ $bank->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="branch_name" class="col-sm-3 col-form-label">Branch Name:</label>
                                <div class="col-sm-9">
                                    <input class="form-control" type="text" name="branch_name" id="branch_name"
                                           value="{{ $inputs['branch_name'] }}">
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="iban" class="col-sm-3 col-form-label">IBAN number:</label>
                                <div class="col-sm-9">
                                    <input class="form-control" type="text" name="iban" id="iban"
                                           value="{{ $inputs['iban'] }}">
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="account_number" class="col-sm-3 col-form-label">Bank Account number:</label>
                                <div class="col-sm-9">
                                    <input class="form-control" type="text" name="account_number" id="account_number"
                                           value="{{ $inputs['account_number'] }}">
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="remarks" class="col-sm-3 col-form-label">Remarks</label>
                                <div class="col-sm-9">
                                    <textarea
                                        name="remarks"
                                        class="form-control"
                                        id="remarks"
                                        cols="30"
                                        rows="1">{{ $inputs['remarks'] }}</textarea>
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <legend class="col-form-label col-sm-3 pt-0">&nbsp;</legend>
                                <div class="col-sm-9">
                                    <div class="form-check">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="is_available"
                                            id="is_available"
                                            @if ($inputs['is_available'])
                                            checked
                                            @endif
                                            value="1">
                                        <label class="form-check-label" for="is_available">Is Available</label>
                                    </div>
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <legend class="col-form-label col-sm-3 pt-0">&nbsp;</legend>
                                <div class="col-sm-9">
                                    <div class="form-check">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="inactive"
                                            id="inactive"
                                            @if ($inputs['inactive'])
                                            checked
                                            @endif
                                            value="1">
                                        <label class="form-check-label" for="inactive">Is Inactive</label>
                                    </div>
                                    <span class="error_message text-danger"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Table section --}}
                    <div class="w-100 my-10 text-center border border-success border-start-0 border-end-0">
                        <span class="bg-success rounded p-3 d-inline-block fw-bolder fs-4">Labour Documents</span>
                    </div>
                    <table class="table g-3 thead-strong" id="docs_table">
                        <thead class="table-dark">
                            <th>Type</th>
                            <th>Number</th>
                            <th>Issued Place</th>
                            <th>Issued On</th>
                            <th>Expire On</th>
                            <th>File</th>
                            <th></th>
                        </thead>
                        <tbody>
                        <?php foreach($docTypes as $d): ?>
                            <tr>
                                <td>{{ $d->name }}</td>
                                <td>
                                    <input
                                        type="text"
                                        data-parsley-required-if-has-file="docs[{{ $d->id }}][file]"
                                        data-parsley-validate-if-empty
                                        class="form-control"
                                        name="docs[{{ $d->id }}][reference]"
                                        value="{{ $d->reference }}">
                                </td>
                                <td>
                                    <input
                                        type="text"
                                        data-parsley-required-if-has-file="docs[{{ $d->id }}][file]"
                                        data-parsley-validate-if-empty
                                        class="form-control"
                                        name="docs[{{ $d->id }}][context][issue_place]"
                                        value="{{ data_get($d->context, 'issue_place') }}">
                                </td>
                                <td>
                                    <input type="text"
                                        data-parsley-required-if-has-file="docs[{{ $d->id }}][file]"
                                        data-parsley-validate-if-empty
                                        data-provide="datepicker"
                                        data-date-format="{{ getBSDatepickerDateFormat() }}"
                                        data-date-clear-btn="true"
                                        data-date-autoclose="true"
                                        data-parsley-trigger-after-failure="change"
                                        class="form-control"
                                        name="docs[{{ $d->id }}][issued_on]"
                                        value="{{ $d->issued_on }}"
                                        placeholder="{{ getBSDatepickerDateFormat() }}">
                                </td>
                                <td>
                                    <input type="text"
                                        data-parsley-required-if-has-file="docs[{{ $d->id }}][file]"
                                        data-parsley-validate-if-empty
                                        data-provide="datepicker"
                                        data-date-format="{{ getBSDatepickerDateFormat() }}"
                                        data-date-clear-btn="true"
                                        data-date-autoclose="true"
                                        data-parsley-trigger-after-failure="change"
                                        class="form-control"
                                        value="{{ $d->expires_on }}"
                                        name="docs[{{ $d->id }}][expires_on]"
                                        id="dob"
                                        placeholder="{{ getBSDatepickerDateFormat() }}">
                                </td>
                                <td data-parsley-form-group>
                                    <div class="input-group">
                                        <input {{ $d->is_required && empty($d->file) ? 'required' : '' }}
                                            type="file"
                                            class="form-control"
                                            accept="image/jpeg,application/pdf"
                                            name="docs[{{ $d->id }}][file]">
                                        <div class="input-group-append">
                                            <div class="input-group-text rounded-start-0">
                                                @if ($d->file)
                                                <a
                                                    href="{{ route('file.view', ['type' => 'document', 'file' => $d->file]) }}"
                                                    class="p-0"
                                                    {{ $d->file }}>
                                                    <span class="fa fa-eye"></span>
                                                </a>
                                                @else
                                                <button
                                                    type="button"
                                                    class="btn p-0"
                                                    disabled>
                                                        <span class="fa fa-eye"></span>
                                                </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-center">
                        <button type="submit" class="btn btn-success">Save</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
     <!-- video pop up html                        -->
    <div id="light">
        <a class="boxclose" id="boxclose" onclick="lightbox_close();"></a>
        <video id="video-source" src=""  width="600"  controls>
            {{-- <source  type="video/mp4"> --}}
            <!--Browser does not support <video> tag -->
        </video>
    </div>
    <div id="fade" onClick="lightbox_close();"></div>   
    
@endsection
@push('scripts')
    <script>
        window.document.onkeydown = function(e) {
            if (!e) { e = event; }
            if (e.keyCode == 27) { lightbox_close(); }
        }

        function lightbox_open(url) {
            $('#video-source').attr('src',url)
            var lightBoxVideo = document.getElementById("video-source");
            window.scrollTo(0, 0);
            document.getElementById('light').style.display = 'block';
            document.getElementById('fade').style.display = 'block';
            lightBoxVideo.play();
        }

        function lightbox_close() {
            var lightBoxVideo = document.getElementById("video-source");
            document.getElementById('light').style.display = 'none';
            document.getElementById('fade').style.display = 'none';
            lightBoxVideo.pause();
        }
        function handleSalary() {
            var transportation_allowance = parseFloat($('#transportation_allowance').val()) || 0;
            var accommodation_allowance = parseFloat($('#accommodation_allowance').val()) || 0; // Assuming there's an element with ID 'accommodation_allowance'
            var other_allowance = parseFloat($('#other_allowance').val()) || 0;
            var basic_salary = parseFloat($('#basic_salary').val()) || 0;
            var total_salary = transportation_allowance + accommodation_allowance + other_allowance + basic_salary;
            $('#salary').val(total_salary);
        }
        route.push('labour.index', '{{ route("labour.index") }}');
        route.push('labour.reference.isUnique', '{{ route("labour.reference.isUnique") }}')
    </script>
    <script src="{{ asset('scripts/labour/labour.js') . '?id=v1.0.1' }}"></script>
@endpush
