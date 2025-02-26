@extends('layout.app')
@section('title', 'Labour List')
@push('styles')
    <style>
        .avatar {
            border-radius: 45%;
            height: 80px;
            width: 80px;
        }

        .avatar_download {
            border-radius: 45%;
            height: 40px;
            width: 40px;
        }

        .download-image::before {
            content: "\2193";
            /* Unicode for down arrow symbol */
            position: absolute;
            bottom: 0;
            right: 0;
            background-color: #000;
            color: #fff;
            padding: 5px;
            font-size: 20px;
        }

        .table-striped thead tr {
            border-bottom: 2px solid #e7d6d6;
        }

        .table-striped tbody tr {
            border-bottom: 1px solid lightgray;
        }

        .top {
            min-height: 80vh;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #009688;
            font-size: 12pt;
            font-family: 'Poppins';
        }

        .table-striped tbody tr:nth-of-type(even) {
            /* background-color: #009688; */
            font-size: 12pt;
            font-family: 'Poppins';
        }
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
        <div class="card top">
            <div class="card-header">
                <div class="card-title">
                    <h5>List of all maids</h5>
                </div>
            </div>
            <section class="labour_list">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-nowrap table-striped thead-strong" id="labours-table">
                            <!-- Generated through datatable -->
                        </table>
                    </div>
                </div>
                
                <div id="light">
                    <a class="boxclose" id="boxclose" onclick="lightbox_close();"></a>
                    <video id="video-source" src=""  width="600"  controls>
                        {{-- <source  type="video/mp4"> --}}
                        <!--Browser does not support <video> tag -->
                    </video>
                </div>
                <div id="fade" onClick="lightbox_close();"></div>
            </section>
        </div>
    </div>

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

        $(function () {
            route.push('file.view', '{{ rawRoute('file.view') }}');
            route.push('labour.edit', '{{ rawRoute('labour.edit') }}');
            route.push('labour.generateCv', '{{ rawRoute('labour.generateCv') }}');
            const blankAvatarUrl = '{{ asset('media/avatars/blank.png') }}';

            $('#labours-table').DataTable({
                ajax: ajaxRequest({
                    url: '{{ route('api.dataTable.labours') }}',
                    method: 'post',
                    eject: true,
                }),
                processing: true,
                serverSide: true,
                paging: true,
                searchDelay: 1500,
                ordering: true,
                order: [[2, 'asc']],
                rowId: 'id',
                columns: [
                    {
                        data: 'maid_ref',
                        title: 'Maid Code',
                        width: '5%',
                        className: 'ps-3 text-center'
                    },
                    {
                        data: 'profile_photo',
                        title: 'Photo',
                        width: '10%',
                        className: 'text-center',
                        orderable: false,
                        searchable: false,
                        render: {
                            display: path => {
                                return (
                                    `<img
                                        onerror="this.src='${ blankAvatarUrl }'"
                                        class="avatar"
                                        src="${ path ? route('file.view', {name: 'avatar', file: path}) : blankAvatarUrl }"
                                        alt="">`
                                )
                            }
                        }
                    },
                    {
                        data: 'name',
                        title: 'Name',
                        width: '300px',
                        className: 'text-wrap'
                    },
                    {data: 'mobile_number', title: 'Mobile'},
                    {data: 'type_name', title: 'Labour Type'},
                    {data: 'job_type_name', title: 'Job Type'},
                    {data: 'category_name', title: 'Package Name'},
                    {data: 'maid_status', title: 'Status'},
                    {data: 'country_name', title: 'Nationality'},
                    {data: 'creator_name', title: 'Created By'},
                    {data: 'formatted_created_at', title: 'Created At'},
                    {data: 'passport_ref', title: 'Passport #'},
                    {
                        data: null,
                        defaultContent: '',
                        title: '',
                        width: '20px',
                        className: 'pe-3',
                        searchable: false,
                        orderable: false,
                        responsivePriority: 0,
                        render: (data, type, row) => {
                            if (type != 'display') {
                                return null;
                            }

                            const actions = [];

                            actions.push(
                                `<button
                                    type="button"
                                    ${row.video 
                                        ? ("onclick=\"lightbox_open('" + route('file.view', {name: 'video', file: row.video}) + "');\"" )
                                        : 'disabled'
                                    }
                                    class="btn btn-primary btn-sm video"
                                    data-bs-toggle="modal"
                                    data-bs-target="#exampleModal">
                                    <i class="fas fa-video"></i>
                                </button>`
                            );
                            
                            actions.push(
                                `<a
                                    href="${ route('labour.edit', {labour: row.id}) }"
                                    class="btn btn-primary btn-sm mx-2">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>`
                            );

                            actions.push(
                                `<a
                                    href="${ route('labour.generateCv', {labour: row.id}) }"
                                    class="btn btn-primary btn-sm"
                                    target="_blank">CV
                                </a>`
                            );

                            return (
                                `<div class="d-flex justify-content-center">
                                    ${ actions.join("\n") || '' }
                                </div>`
                            )
                        }
                    },
                ]
            })
        })
    </script>
@endpush
