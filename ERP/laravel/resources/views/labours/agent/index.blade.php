@extends('layout.app')

@push('styles')
<style>
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: #009688;
        font-size: 12pt;
        font-family: 'Poppins';
    }

    .avatar{
        border-radius: 45%;
        height: 80px;
        width: 80px;
    }
        
</style>
@endpush()

@section('page')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <h2>Agent List</h2>
                </div>
            </div>

            <br>

            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover" id="agent_list_table" style="width: 100%;">
                    <thead id="agent_list_thead">
                        <tr>
                            <th style="width: 4%;text-align: center;"><b><?= trans('ID') ?></b></th>
                            <th style="width: 4%;text-align: center;"><b><?= trans('Agent Photo') ?></b></th>
                            <th style="width: 20%;text-align: center;"><b><?= trans('Name') ?></b></th>
                            <th style="width: 10%;text-align: center;"><b><?= trans('Arabic Name') ?></b></th>
                            <th style="width: 10%;text-align: center;"><b><?= trans('Mobile') ?></b></th>
                            <th style="width: 10%;text-align: center;"><b><?= trans('Email') ?></b></th>
                            <th style="width: 10%;text-align: center;"><b><?= trans('Location') ?></b></th>
                            <th style="width: 10%;text-align: center;"><b><?= trans('Address') ?></b></th>
                            <th style="width: 10%;text-align: center;"><b><?= trans('') ?></b></th>
                        </tr>
                    </thead>
                    <tbody id="agent_list_tbody">
                    @foreach ($agents as $key => $agent)
                        <tr>                                   
                            <td class="align-middle" style="width: 4%;text-align: center;">{{ $key+1 }}</td>
                            @if(!empty($agent->photo))    
                                <td class="align-middle" style="width: 10%;text-align: center;" class="text-center"> <img class="avatar"src="{{ route('file.view', ['name' => 'avatar', 'file' => $agent->photo]) }}" alt="" > </td>
                            @else
                                <td class="align-middle" style="width: 10%;text-align: center;" class="text-center"> <img class="avatar"src="{{ url('media/avatars/blank.png') }}" alt="" > </td>
                            @endif
                            <td class="align-middle" style="width: 20%;text-align: center;">{{ $agent->supp_name }}</td>
                            <td class="align-middle" style="width: 10%;text-align: center;">{{ $agent->arabic_name }}</td>
                            <td class="align-middle" style="width: 10%;text-align: center;">{{ $agent->contact }}</td>
                            <td class="align-middle" style="width: 10%;text-align: center;">{{ $agent->email }}</td>
                            <td class="align-middle" style="width: 10%;text-align: center;">{{ $agent->location }}</td>
                            <td class="align-middle" style="width: 10%;text-align: center;">{{ $agent->address }}</td>
                            <td class="align-middle" style="width: 20%;text-align: center;">
                                <ul class="list-inline m-0">
                                    <li class="list-inline-item">
                                        <button class="btn btn-primary align-middle">
                                            <a style="color:black;" href="{{ route('agent.edit', $agent->supplier_id) }}">Edit</a>
                                        </button>
                                    </li>
                                </ul>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div id="pg-link">{{ $agents->links() }}</div>
        </div>
    </div>
@endsection