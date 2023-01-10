<?php
use App\Models\Utils;
$ent = Utils::ent();

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    {{-- <title>{{ config('admin.title') }} | {{ trans('admin.login') }}</title> --}}
    {{--     <title>School Dynamics | {{ trans('admin.login') }}</title> --}}
    <title>School Dynamics | {{ trans('admin.login') }}</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    {{--  @if (!is_null($favicon = Admin::favicon()))
        <link rel="shortcut icon" href="{{ $favicon }}">
    @endif --}}

    <link rel="shortcut icon" href="https://schooldynamics.ug/assets/logo.png">

    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="{{ admin_asset('vendor/laravel-admin/AdminLTE/bootstrap/css/bootstrap.min.css') }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ admin_asset('vendor/laravel-admin/font-awesome/css/font-awesome.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ admin_asset('vendor/laravel-admin/AdminLTE/dist/css/AdminLTE.min.css') }}">
    <!-- iCheck -->
    <link rel="stylesheet" href="{{ admin_asset('vendor/laravel-admin/AdminLTE/plugins/iCheck/square/blue.css') }}">

    <!--Start of Tawk.to Script-->
    <script type="text/javascript">
        /* var Tawk_API = Tawk_API || {},
                Tawk_LoadStart = new Date();
            (function() {
                var s1 = document.createElement("script"),
                    s0 = document.getElementsByTagName("script")[0];
                s1.async = true;
                s1.src = 'https://embed.tawk.to/6322adcd54f06e12d894cbb7/1gcvndrj2';
                s1.charset = 'UTF-8';
                s1.setAttribute('crossorigin', '*');
                s0.parentNode.insertBefore(s1, s0);
            })(); */
    </script>
    <!--End of Tawk.to Script-->

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
  <script src="//oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="//oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
    <style>
        html,
        body {
            height: 100%;
        }

        .fill {
            min-height: 100%;
            height: 100%;
            display: flex;
            height: 100vh;
            flex-direction: column;
            justify-content: center;
        }

        .center {
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .description {
            padding-left: 5%;
            padding-right: 5%;
            padding-top: 1%;
            color: white;
            font-family: "Source Sans Pro", sans-serif;
            font-weight: 100;
            font-size: 18px;
        }

        .mobo-only {
            display: none;
        }

        @media only screen and (max-width: 768px) {
            .mobo-only {
                display: block;
            }

            .pc-only {
                display: none;
            }
        }
    </style>
</head>


<body class="">
    <div class="row">
        <div class="col-md-6 fill pc-only " style="background-color: {{ $ent->color }}">
            <br>
            <br>
            <img class="img-fluid center " width="25%" src="{{ url("storage/images/$ent->logo") }}" alt="">
            <div class="description">
                <h2>{{ $ent->name }}.</h2>
                {!! $ent->welcome_message !!}
            </div>
        </div>
        <div class="col-md-1 fill pc-only " style="padding: 0px; width: 4rem;">
            <div class="fill pc-only"
                style="width: 3rem; background-image: url({{ url('assets/pattern.png') }});    
            background-size:     cover;
            background-repeat:   no-repeat;
            background-position: center center;
            ">
            </div>
        </div>

        <div class="col-md-5 fill">
            <div class="login-box">
                {{-- <div class="login-logo">
                    <a href="{{ admin_url('/') }}"><b>{{ config('admin.name') }}</b></a>
                </div> --}}

                <img class="img-fluid center mobo-only " width="30%" src="{{ url("storage/$ent->logo") }}"
                    alt="">

                <div class="login-logo">
                    {{-- <h2>Log in to your account</h2> --}}
                    <h2>Log in to your account</h2>
                </div>

                <!-- /.login-logo -->
                <div class="login-box-body">

                    <form action="{{ admin_url('auth/login') }}" method="post">
                        <div class="form-group has-feedback {!! !$errors->has('username') ?: 'has-error' !!}">

                            @if ($errors->has('username'))
                                @foreach ($errors->get('username') as $message)
                                    <label class="control-label" for="inputError"><i
                                            class="fa fa-times-circle-o"></i>{{ $message }}</label><br>
                                @endforeach
                            @endif

                            <input type="text" class="form-control" placeholder="{{ trans('admin.username') }}"
                                name="username" value="{{ old('username') }}">
                            <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                        </div>
                        <div class="form-group has-feedback {!! !$errors->has('password') ?: 'has-error' !!}">

                            @if ($errors->has('password'))
                                @foreach ($errors->get('password') as $message)
                                    <label class="control-label" for="inputError"><i
                                            class="fa fa-times-circle-o"></i>{{ $message }}</label><br>
                                @endforeach
                            @endif

                            <input type="password" class="form-control" placeholder="{{ trans('admin.password') }}"
                                name="password">
                            <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                        </div>
                        <div class="row">
                            <div class="col-xs-8">
                                @if (config('admin.auth.remember'))
                                    <div class="checkbox icheck">

                                        <p><a href="javascript:;" style="color: {{ $ent->color }};">Forgot
                                                password</a></p>
                                        {{-- <label>
                                            <input type="checkbox" name="remember" value="1" 
                                                {{ !old('username') || old('remember') ? 'checked' : '' }}>
                                            {{ trans('admin.remember_me') }}
                                        </label> --}}
                                    </div>
                                @endif
                            </div>
                            <!-- /.col -->
                            <div class="col-xs-4">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <button type="submit" class="btn  btn-block btn-flat"
                                    style="background-color: {{ $ent->color }}; color: white">{{ trans('admin.login') }}</button>
                            </div>
                            <!-- /.col -->
                        </div>
                    </form>

                </div>
                <!-- /.login-box-body -->
            </div>
        </div>
    </div>

    {{--  --}}
    <!-- /.login-box -->

    <!-- jQuery 2.1.4 -->
    <script src="{{ admin_asset('vendor/laravel-admin/AdminLTE/plugins/jQuery/jQuery-2.1.4.min.js') }}"></script>
    <!-- Bootstrap 3.3.5 -->
    <script src="{{ admin_asset('vendor/laravel-admin/AdminLTE/bootstrap/js/bootstrap.min.js') }}"></script>
    <!-- iCheck -->
    <script src="{{ admin_asset('vendor/laravel-admin/AdminLTE/plugins/iCheck/icheck.min.js') }}"></script>
    <script>
        $(function() {
            $('input').iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue',
                increaseArea: '20%' // optional
            });
        });
    </script>
</body>

</html>
