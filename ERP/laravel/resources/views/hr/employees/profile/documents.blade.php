@extends('hr.employees.profile.base')

@section('slot')
    <div class="card mb-5 mb-xl-10" id="kt_profile_details_view">
        <!--begin::Card body-->
        <div class="card-body p-9">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="fw-bold text-muted">{{ 'DOCUMENT TYPE' }}</th>
                        <th class="fw-bold text-muted text-center">{{ 'ISSUED ON' }}</th>
                        <th class="fw-bold text-muted text-center">{{ 'EXPIRE ON' }}</th>
                        <th class="fw-bold text-muted">{{ 'REFERENCE' }}</th>
                        <th class="fw-bold text-muted">{{ 'DOCUMENT' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($documents as $document)
                    <tr>
                        <td class="fw-bolder fs-6 text-dark">
                            {{ $document->type->name }}
                        </td>
                        <td class="fw-bolder fs-6 text-dark text-center">
                            {{ $document->issued_on ? $document->issued_on->format(dateformat()) : '--' }}
                        </td>
                        <td class="fw-bolder fs-6 text-dark text-center">
                            {{ $document->expires_on ? $document->expires_on->format(dateformat()) : '--' }}
                        </td>
                        <td class="fw-bolder fs-6 text-dark">
                            {{ $document->reference }}
                        </td>
                        <td class="fw-bolder fs-6 text-dark">
                            <a href="{{ route("file.download", ['type' => $document->type->name, 'file' => $document->file]) }}" target="_blank">Download</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <!--end::Card body-->
    </div>
@endsection
