@extends('layout.app')

@section('title', 'Bulk Employees Upload')

@section('page')

<!--begin: ContentContainer -->
<div class="container mw-900px" id="employee-upload-form">
    <div class="card">
        <div class="card-header">
            <h1 class="card-title">Bulk Employee Upload</h1>
        </div>
        <div class="card-bod mw-800px p-10">
            <!-- Display Success Message -->
            @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
            @endif
            
            <form id="uploadForm" action="{{ route('bulkEmployeeUpload.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group row">
                    <label for="excel_file" class="col-md-3 col-form-label">{{ __('Choose Excel File') }}</label>
                    <div class="col-md-6 @error('excel_file') is-invalid @enderror">
                        <input type="file" id="excel_file" class="form-control" name="excel_file" accept=".csv, .xlsx, .xls">
                        <!-- Display Date Format Error -->
                        @if($errors->has('excel_file'))
                        <ul class="errors-list filled">
                            @foreach ($errors->get('excel_file') as $message)
                            <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                </div>
                <div class="my-4"></div>

                <div class="form-group row">
                    <label for="date_format" class="col-md-3 col-form-label">{{ __('Select Date Format') }}</label>
                    <div class="col-md-6 @error('date_format') is-invalid @enderror">
                        <select name="date_format" id="date_format" class="form-select font-monospace">
                            <option value="">-- Select date format --</option>
                            @foreach ($dateFormats as $format => $sample)
                            <option value="<?= $format ?>"><?= strtr($sample, [' ' => '&nbsp;']) ?></option>
                            @endforeach
                        </select>

                        <!-- Display Date Format Error -->
                        @if($errors->has('date_format'))
                        <ul class="errors-list filled">
                            @foreach ($errors->get('date_format') as $message)
                            <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="form-group mt-7 text-center">
                            <button class="button btn btn-sm btn-success" id="btn-process" type="submit">
                                <span class="fa fa-paper-plane me-2"></span> UPLOAD
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            @if(session('dataErrors'))
            <ul class="list-unstyled alert alert-danger mt-3" role="alert">
                @foreach (session('dataErrors') as $message)
                <li class="mt-2 lh-sm">{!! strtr($message, ["\n" => '<br>', ' ' => '&nbsp;']) !!}</li>
                @endforeach
            </ul>
            @endif
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    $('#uploadForm').on('submit', setBusyState);
</script>
@endpush