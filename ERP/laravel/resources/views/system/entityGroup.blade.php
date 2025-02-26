@extends('layout.app')
@section('title', 'Entity Group')
@section('page')
    <div class="container mw-1200px">
        <div class="card">
            <div class="card-header border">
                <div class="card-title">
                    <h2>Manage Entity Groups</h2>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-12">
                        <form action="" id="form-entity-group" method="post">
                            <input type="hidden" name="category" value="{{ \App\Models\EntityGroupCategory::WORK_FLOW_RELATED }}">
                            <div class="row">
                                <div class="col-lg-4">
                                    <label for="name">Name</label>
                                    <input required type="text" name="name" id="name" class="form-control">
                                </div>
                                <div class="col-lg-5">
                                    <label for="description">Description</label>
                                    <input required type="text" class="form-control" name="description" id="description">
                                </div>
                                <div class="col-lg-1">
                                    <button type="submit" class="btn btn-primary mt-5">Save</button>
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
                                <th>#</th>
                                <th>NAME</th>
                                <th>DESCRIPTION</th>
                                <th></th>
                            </thead>
                            <tbody>
                                @foreach ($entityGroups as $entityGroup)
                                    <tr>
                                        <td>{{ $entityGroup->id }}</td>
                                        <td>{{ $entityGroup->name }}</td>
                                        <td>{{ $entityGroup->description }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-danger btn-delete" data-id="{{ $entityGroup->id }}">
                                                <span class="fas fa-trash-alt"></span>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="col-12">
                        <div class="pagination d-flex justify-around">
                            <p>Page {{ $entityGroups->currentPage() }} of {{ $entityGroups->lastPage() }}<br></p>
                            {{ $entityGroups->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        route.push('entityGroup.destroy', '{{ rawRoute('entityGroup.destroy') }}')
        $(function() {
            parsleyForm = $('#form-entity-group').parsley();

            parsleyForm.on('form:submit', (event) => {
                ajaxRequest({
                    url: '{{ route('entityGroup.store') }}',
                    method: 'post',
                    data: parsleyForm.$element.serialize()
                }).done(function(res){
                    if (res.status == 201) {
                        Swal.fire(
                            'Success',
                            'New entity group has been added!',
                            'success'
                        )
                        window.location.reload()
                    } else {
                        defaultErrorHandler();
                    }
                }).fail(defaultErrorHandler);

                return false;
            });

            $(document).on('click', '.btn-delete', function() {
                ajaxRequest({
                    url: route('entityGroup.destroy', {entityGroup: this.dataset.id}),
                    method: 'delete',
                }).done(function(res){
                    if (res.status == 204) {
                        toastr.success('Entity group has been deleted')
                        window.location.reload();
                    } else {
                        defaultErrorHandler();
                    }
                }).fail(defaultErrorHandler)
            })
        });
    </script>
@endpush
