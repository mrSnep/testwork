@extends('layouts.app')
@section('styles')
    <link href="{{ asset('assets/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet">
@endsection
@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Login</div>

                <div class="panel-body">
                    <form class="form-horizontal" id="loginform"  data-toggle="validator" method="POST" action="{{ route('login') }}">
                        {{ csrf_field() }}

                        @if ($message = Session::get('success'))
                            <div class="alert alert-success">
                                <p>
                                    {{ $message }}
                                </p>
                            </div>
                        @endif
                        @if ($message = Session::get('warning'))
                            <div class="alert alert-warning">
                                <p>
                                    {{ $message }}
                                </p>
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="email" class="col-md-4 control-label">E-Mail Address</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus>
                                <span class="help-block with-errors"></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password" class="col-md-4 control-label">Password</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control" name="password" data-minlength="6" required>
                                <span class="help-block with-errors"></span>

                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Remember Me

                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    Login
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
    <script src="{{ asset('assets/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/validator/validator.min.js') }}"></script>

<script>
$(function(){
    $('#loginform').validator().on('submit', function (e) {
        if (!e.isDefaultPrevented()){
            var url = $(this).attr('action');
            var data =  $(this).serialize();
            $.ajax({
                url: url,
                data: data,
                dataType: "json",
                type : "POST",
                success : function(data) {
                    if(data.error){
                        swal({
                            title: 'Bad...',
                            text: data.message,
                            type: 'error',
                            timer: '1500'
                        })
                    }
                    else {
                        swal({
                            title: 'Success!',
                            type: 'success',
                            timer: '1500',
                            onClose: () => {
                              document.location.href=data.location
                            }
                        })
                    }
                },
                error : function(data){
                    swal({
                        title: 'Error...',
                        text: data.error,
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
