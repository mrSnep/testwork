@extends('layouts.app')
@section('styles')
    <link href="//cdn.datatables.net/1.10.19/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('assets/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/summernote/summernote.css') }}" rel="stylesheet">
@endsection
@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12 ">
            <div class="panel panel-default">
                <div class="panel-heading">Manage files</div>

                <div class="panel-body">

                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif
                    <div class="text-center">
                        <button onclick="addForm()" type="button" class="btn btn-primary" >
                            Upload New File
                        </button>
                    </div>
                    <hr>
                    <table id="files-table" class="table table-striped">
                        <thead>
                        <tr>
                            <th width="30">#</th>
                            <th>Title</th>
                            <th>Extention</th>
                            <th>Size</th>
                            <th>Upload time</th>
                            <th>Downloaded</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal -->

@include('layouts.modal')

@endsection

@section('scripts')
    <script src="//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
    <script src="//cdn.datatables.net/1.10.19/js/dataTables.bootstrap.min.js"></script>
    <script src="{{ asset('assets/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/summernote/summernote.js') }}"></script>
    <script src="{{ asset('assets/validator/validator.min.js') }}"></script>

    <script type="text/javascript">
        var table = $('#files-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('api.files') }}",
                columns: [
                    {data: 'DT_Row_Index', name: 'DT_Row_Index'},
                    {data: 'title', name: 'title'},
                    {data: 'type_file', name: 'type_file'},
                    {data: 'size', name: 'size'},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'downloaded', name: 'downloaded'},
                    {data: 'smalldescription', name: 'smalldescription'},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ]
            });

        function addForm() {
            save_method = "add";
            $('input[name=_method]').val('POST');
            $('#modal-form form .foto_group').show();
            $("#file").prop('required',true);
            $('#description').summernote('destroy');
            $('#modal-form form').get(0).reset();
            $('#description').val('');
            $('#description').summernote({height: 150});
            $('.modal-title').text('Add File');
            $('#modal-form').modal('show');
        }
        function editForm(id) {
            save_method = 'edit';
            $('input[name=_method]').val('PATCH');
            $('#modal-form form').get(0).reset();
            $("#file").prop('required',false);
            $('#modal-form form .foto_group').hide();
            $.ajax({
                url: "{{ url('file') }}" + '/' + id + "/edit",
                type: "GET",
                dataType: "JSON",
                success: function(data) {
                    $('#modal-form').modal('show');
                    $('.modal-title').text('Edit File');
                    $('#id').val(data.id);
                    $('#title').val(data.title)
                    $('#description').summernote('destroy');;
                    $('#description').val(data.description);
                    $('#description').summernote({height: 150});
                },
                error : function() {
                    swal({text:"File Not Found"});
                }
            });
        }
        function getFullDescription(id) {
            $.get( "{{url('api/description/')}}/" + id , function( data ) {
                swal({
                    html: data,
                    showCloseButton: true,
                })
            });
        }
        function deleteFile(id){
            var csrf_token = $('meta[name="csrf-token"]').attr('content');
            swal({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: '#d33',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then(function () {
                $.ajax({
                    url : "{{ url('file') }}" + "/" + id,
                    type : "POST",
                    data : {'_method' : 'DELETE', '_token' : csrf_token},
                    success : function(data) {
                        if(data.error){
                            swal({
                                title: 'Oops...',
                                text: data.message,
                                type: 'error',
                                timer: '1500'
                            })
                        }
                        else {
                            table.ajax.reload();
                            swal({
                                title: 'Success!',
                                text: data.message,
                                type: 'success',
                                timer: '1500'
                            })
                        }
                    },
                    error : function () {
                        swal({
                            title: 'Oops...',
                            text: data.message,
                            type: 'error',
                            timer: '1500'
                        })
                    }
                });
            });
        }
        function reloadTable() {
            setTimeout(function(){

                table.ajax.reload();
            }, 500);


        }

        $(function(){
            $('#modal-form form').validator().on('submit', function (e) {
                if (!e.isDefaultPrevented()){
                    var id = $('#id').val();
                    if (save_method == 'add') url = "{{ url('file') }}";
                    else url = "{{ url('file') . '/' }}" + id;

                    $.ajax({
                        url : url,
                        type : "POST",
                        data: new FormData($("#modal-form form").get(0)),
                        contentType: false,
                        processData: false,
                        success : function(data) {
                            if(data.error){
                                swal({
                                    title: 'Oops...',
                                    text: data.message,
                                    type: 'error',
                                    timer: '1500'
                                })
                            }
                            else {
                                $('#modal-form').modal('hide');
                                table.ajax.reload();
                                swal({
                                    title: 'Success!',
                                    text: data.message,
                                    type: 'success',
                                    timer: '1500'
                                })
                            }
                        },
                        error : function(data){
                            swal({
                                title: 'Oops...',
                                text: data.message,
                                type: 'error',
                                timer: '1500'
                            })
                        }
                    });
                    return false;
                }
            });
        });

    </script>
@endsection
