@extends('around.layouts.base-layout')
@section('base-content')
    @php
        $video_bg = url('public/assets/video-1.mp4');
        $mobile_app = url('app');
    @endphp
    <main class="page-wrapper">
        <div class="d-lg-flex position-relative h-100">

            <!-- Video Background -->
            <div class="video-container w-50 d-none d-lg-block">
                <video autoplay muted loop class="video-bg">
                    <source src="{{ $video_bg }}" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>

            <!-- Sign in form -->
            <div class="d-flex flex-column align-items-center w-lg-50 h-100 px-3 px-lg-5 pt-5">
                <div class="w-100 text-center mb-0">
                    <img src="{{ url('assets/img/logo.png') }}" alt="Project Logo" class="project-logo">
                </div>
                <div class="w-100" style="max-width: 526px;">
                    <h1 class="text-center mb-2 mb-md-4 text-primary fw-bold">{{ env('APP_NAME') }}</h1>
                    <p class="h2 text-primary fs-5 fw-700 pt-2 pt-md-3">Sign in to your account</p>
                    <form action="{{ admin_url('auth/login') }}" method="post">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        <div class="form-group mb-3">
                            <div class="position-relative">
                                <i class="ai-mail fs-lg position-absolute top-50 start-0 translate-middle-y ms-3"></i>
                                <input class="form-control form-control-lg ps-5" type="email" name="email"
                                    id="email" value="{{ old('email') }}" placeholder="Email address" required>
                            </div>
                        </div>
                        <div class="form-group mb-4">
                            <div class="position-relative">
                                <i
                                    class="ai-lock-closed fs-lg position-absolute top-50 start-0 translate-middle-y ms-3"></i>
                                <div class="password-toggle">
                                    <input name="password" id="password" class="form-control form-control-lg ps-5"
                                        type="password" placeholder="Password" required>
                                    <label class="password-toggle-btn" aria-label="Show/hide password">
                                        <input class="password-toggle-check" type="checkbox"><span
                                            class="password-toggle-indicator"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-lg btn-primary w-100 mb-4" type="submit">Sign in</button>
                        <hr>
                        <p class="text-center mt-3">
                            <a class="h2 text-primary text-center fs-5 fw-700 pt-2 pt-md-3" href="{{ $mobile_app }}"
                                target="_blank" rel="noopener">DOWNLOAD LDF MOBILE APP 📱</a>
                        </p>
                    </form>
                </div>
                <p class="nav w-100 fs-sm pt-4 mt-auto mb-1 text-center d-flex justify-content-center align-items-center">
                    <span class="text-body-secondary">&copy; {{ date('Y') }} All rights reserved. Made by</span>
                    <a class="nav-link d-inline-block p-0 ms-1 text-primary fw-bold" href="https://8technologies.net"
                        target="_blank" rel="noopener">Eight Tech Consults</a>
                </p>
            </div>
        </div>
    </main>

    <style>
        .video-container {
            position: relative;
            height: 100vh;
            overflow: hidden;
        }

        .video-bg {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .project-logo {
            max-width: 150px;
            height: auto;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #ddd;
            transition: 0.3s;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }

        .btn-primary {
            background: #007bff;
            border: none;
            transition: 0.3s;
        }

        .btn-primary:hover {
            background: #0056b3;
        }
    </style>
@endsection  
