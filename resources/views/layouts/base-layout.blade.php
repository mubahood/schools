<?php
//use Utils model
use App\Models\Utils;

?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<!-- Mirrored from silicon.createx.studio/landing-saas-v2.html by HTTrack Website Copier/3.x [XR&CO'2014], Fri, 21 Mar 2025 21:47:56 GMT -->
<!-- Added by HTTrack -->
<meta http-equiv="content-type" content="text/html;charset=utf-8" /><!-- /Added by HTTrack -->

<head>
    <meta charset="utf-8">
    <title>{{ Utils::app_name() }} | School Management System</title>

    <link rel="manifest" href="{{ url('manifest.json') }}">

    <!-- SEO Meta Tags -->
    <meta name="description"
        content="{{ Utils::app_name() }} is a comprehensive school management system designed to streamline administrative tasks, enhance communication, and improve efficiency in schools.">
    <meta name="keywords"
        content="school management system, education software, school administration, student management, teacher tools, school communication, online learning, school software, education technology">
    <meta name="author" content="8Technologies Consults">
    <meta name="robots" content="index, follow">
    <meta property="og:title" content="{{ Utils::app_name() }} | School Management System">
    <meta property="og:description"
        content="{{ Utils::app_name() }} helps schools manage their operations efficiently with advanced tools and features.">
    <meta property="og:image" content="{{ Utils::get_logo() }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ Utils::app_name() }} | School Management System">
    <meta name="twitter:description"
        content="{{ Utils::app_name() }} is a powerful tool for managing school operations effectively.">
    <meta name="twitter:image" content="{{ Utils::get_logo() }}">

    <!-- Viewport -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Theme switcher (color modes) -->
    <script src="silicon/assets/js/theme-switcher.js"></script>

    <!-- Favicon and Touch Icons -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ Utils::get_logo() }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ Utils::get_logo() }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ Utils::get_logo() }}">
    <link rel="manifest" href="{{ Utils::get_logo() }}">
    <link rel="mask-icon" href="{{ Utils::get_logo() }}" color="#6366f1">
    <link rel="shortcut icon" href="{{ Utils::get_logo() }}">
    <meta name="msapplication-TileColor" content="#00b3fa">
    <meta name="msapplication-config" content="{{ Utils::get_logo() }}">
    <meta name="theme-color" content="#ffffff">

    <!-- Vendor Styles -->
    <link rel="stylesheet" media="screen" href="silicon/assets/vendor/boxicons/css/boxicons.min.css">
    <link rel="stylesheet" media="screen" href="silicon/assets/vendor/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" media="screen" href="silicon/assets/vendor/img-comparison-slider/dist/styles.css">

    <!-- Main Theme Styles + Bootstrap -->
    <link rel="stylesheet" media="screen" href="silicon/assets/css/theme.min.css">

    <!-- Page loading styles -->
    <style>
        .page-loading {
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100%;
            -webkit-transition: all .4s .2s ease-in-out;
            transition: all .4s .2s ease-in-out;
            background-color: #fff;
            opacity: 0;
            visibility: hidden;
            z-index: 9999;
        }

        [data-bs-theme="dark"] .page-loading {
            background-color: #0b0f19;
        }

        .page-loading.active {
            opacity: 1;
            visibility: visible;
        }

        .page-loading-inner {
            position: absolute;
            top: 50%;
            left: 0;
            width: 100%;
            text-align: center;
            -webkit-transform: translateY(-50%);
            transform: translateY(-50%);
            -webkit-transition: opacity .2s ease-in-out;
            transition: opacity .2s ease-in-out;
            opacity: 0;
        }

        .page-loading.active>.page-loading-inner {
            opacity: 1;
        }

        .page-loading-inner>span {
            display: block;
            font-size: 1rem;
            font-weight: normal;
            color: #9397ad;
        }

        [data-bs-theme="dark"] .page-loading-inner>span {
            color: #fff;
            opacity: .6;
        }

        .page-spinner {
            display: inline-block;
            width: 2.75rem;
            height: 2.75rem;
            margin-bottom: .75rem;
            vertical-align: text-bottom;
            border: .15em solid #b4b7c9;
            border-right-color: transparent;
            border-radius: 50%;
            -webkit-animation: spinner .75s linear infinite;
            animation: spinner .75s linear infinite;
        }

        [data-bs-theme="dark"] .page-spinner {
            border-color: rgba(255, 255, 255, .4);
            border-right-color: transparent;
        }

        @-webkit-keyframes spinner {
            100% {
                -webkit-transform: rotate(360deg);
                transform: rotate(360deg);
            }
        }

        @keyframes spinner {
            100% {
                -webkit-transform: rotate(360deg);
                transform: rotate(360deg);
            }
        }
    </style>

    <!-- Page loading scripts -->
    <script>
        (function() {
            window.onload = function() {
                const preloader = document.querySelector('.page-loading');
                preloader.classList.remove('active');
                setTimeout(function() {
                    preloader.remove();
                }, 1000);
            };
        })();
    </script>

</head>


<!-- Body -->

<body>


    <!-- Page loading spinner -->
    <div class="page-loading active">
        <div class="page-loading-inner">
            <div class="page-spinner"></div><span>Loading...</span>
        </div>
    </div>


    <!-- Page wrapper for sticky footer -->
    <!-- Wraps everything except footer to push footer to the bottom of the page if there is little content -->
    <main class="page-wrapper">


        <!-- Navbar -->
        <!-- Remove "navbar-sticky" class to make navigation bar scrollable with the page -->
        <header class="header navbar navbar-expand-lg navbar-dark position-absolute navbar-sticky">
            <div class="container px-3">
                <a href="#home" class="navbar-brand pe-3">
                    <img src="{{ Utils::get_logo() }}" width="47" alt="Silicon">
                    {{ Utils::app_name() }}
                </a>
                <div id="navbarNav" class="offcanvas offcanvas-end bg-dark">
                    <div class="offcanvas-header border-bottom border-light">
                        <h5 class="offcanvas-title text-white">Menu</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
                            aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body">
                        <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                            <li class="nav-item">
                                <a href="#features" class="nav-link">Features</a>
                            </li>

                            <li class="nav-item">
                                <a href="#schools" class="nav-link">Schools</a>
                            </li>
                            <li class="nav-item">
                                <a href="#testimonials" class="nav-link">Testimonials</a>
                            </li>

                            <li class="nav-item">
                                <a href="#apps" class="nav-link">Mobile App</a>
                            </li>

                            <li class="nav-item">
                                <a href="https://forms.gle/NP8RXx7YcpPbfi6b8" class="nav-link">Get Started</a>
                            </li>
                        </ul>
                    </div>
                    <div class="offcanvas-header border-top border-light">
                        <a href="https://forms.gle/NP8RXx7YcpPbfi6b8"
                            class="btn btn-primary w-100" target="_blank" rel="noopener">
                            <i class="bx bx-cart fs-4 lh-1 me-1"></i>
                            &nbsp;Request a demo
                        </a>
                    </div>
                </div>
                <div class="pe-lg-1 ms-auto me-4" data-bs-theme="dark">
                    <div class="form-check form-switch mode-switch" data-bs-toggle="mode">
                        <input type="checkbox" class="form-check-input" id="theme-mode">
                        <label class="form-check-label d-none d-sm-block" for="theme-mode">Light</label>
                        <label class="form-check-label d-none d-sm-block" for="theme-mode">Dark</label>
                    </div>
                </div>
                <button type="button" class="navbar-toggler" data-bs-toggle="offcanvas" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <a href="https://forms.gle/NP8RXx7YcpPbfi6b8"
                    class="btn btn-primary btn-sm fs-sm rounded d-none d-lg-inline-flex" target="_blank"
                    rel="noopener">
                    <i class="bx bx-cart fs-5 lh-1 me-1"></i>
                    &nbsp;Request a demo
                </a>
            </div>
        </header>

        @yield('content')

    </main>


    <!-- Footer -->
    <footer class="footer bg-dark pt-5 pb-4 pb-lg-5" data-bs-theme="dark">
        <div class="container text-center pt-lg-3">
            <div class="navbar-brand justify-content-center text-dark mb-2 mb-lg-4">
                <img src="{{ Utils::get_logo() }}" class="me-2" width="60" alt="Silicon">
                <span class="fs-4">{{ Utils::app_name() }}</span>
            </div>
            <ul class="nav justify-content-center pt-3 pb-4 pb-lg-5">
                <li class="nav-item"><a href="#home" class="nav-link">Home</a></li>
                <li class="nav-item"><a href="#features" class="nav-link">Features</a></li>
                <li class="nav-item"><a href="#schools" class="nav-link">Schools</a></li>
                <li class="nav-item"><a href="#testimonials" class="nav-link">Testimonials</a></li>
                <li class="nav-item"><a href="#apps" class="nav-link">Mobile App</a></li>
                <li class="nav-item"><a href="#get_started" class="nav-link">Get Started</a></li>
            </ul>
            <div class="d-flex flex-column flex-sm-row justify-content-center">
                <a href="https://apps.apple.com/us/app/school-dynamics/id6469381244"
                    class="btn btn-dark btn-lg px-3 py-2 me-sm-4 mb-3">
                    <img src="silicon/assets/img/market/appstore-light.svg" class="light-mode-img" width="124"
                        alt="App Store">
                    <img src="silicon/assets/img/market/appstore-dark.svg" class="dark-mode-img" width="124"
                        alt="App Store">
                </a>
                <a href="https://play.google.com/store/apps/details?id=schooldynamics.ug&hl=en"
                    class="btn btn-dark btn-lg px-3 py-2 mb-3">
                    <img src="silicon/assets/img/market/googleplay-light.svg" class="light-mode-img" width="139"
                        alt="Google Play">
                    <img src="silicon/assets/img/market/googleplay-dark.svg" class="dark-mode-img" width="139"
                        alt="Google Play">
                </a>
            </div>
            <div class="d-flex justify-content-center pt-4 mt-lg-3">
                {{--  <a href="https://facebook.com" class="btn btn-icon btn-secondary btn-facebook mx-2"
                    aria-label="Facebook" target="_blank" rel="noopener">
                    <i class="bx bxl-facebook"></i>
                </a> --}}
                {{--    <a href="https://instagram.com" class="btn btn-icon btn-secondary btn-instagram mx-2"
                    aria-label="Instagram" target="_blank" rel="noopener">
                    <i class="bx bxl-instagram"></i>
                </a> --}}
                <a href="https://x.com/8TechConsults" class="btn btn-icon btn-secondary btn-twitter mx-2"
                    aria-label="Twitter" target="_blank" rel="noopener">
                    <i class="bx bxl-twitter"></i>
                </a>
                {{-- <a href="https://youtube.com" class="btn btn-icon btn-secondary btn-youtube mx-2"
                    aria-label="YouTube" target="_blank" rel="noopener">
                    <i class="bx bxl-youtube"></i>
                </a> --}}
            </div>
            <p class="nav d-block fs-sm text-center pt-5 mt-lg-4 mb-0">
                <span class="text-light opacity-60">&copy; All rights reserved. Made by </span>
                <a class="nav-link d-inline-block p-0" href="https://8technologies.net" target="_blank"
                    rel="noopener">8Technologies Consults</a>
            </p>
        </div>
    </footer>


    <!-- Back to top button -->
    <a href="#top" class="btn-scroll-top" data-scroll>
        <span class="btn-scroll-top-tooltip text-muted fs-sm me-2">Top</span>
        <i class="btn-scroll-top-icon bx bx-chevron-up"></i>
    </a>


    <!-- Vendor Scripts -->
    <script src="silicon/assets/vendor/vanilla-tilt/dist/vanilla-tilt.min.js"></script>
    <script src="silicon/assets/vendor/rellax/rellax.min.js"></script>
    <script src="silicon/assets/vendor/swiper/swiper-bundle.min.js"></script>
    <script src="silicon/assets/vendor/img-comparison-slider/dist/index.js"></script>

    <!-- Main Theme Script -->
    <script src="silicon/assets/js/theme.min.js"></script>
</body>

<!-- Mirrored from silicon.createx.studio/landing-saas-v2.html by HTTrack Website Copier/3.x [XR&CO'2014], Fri, 21 Mar 2025 21:48:09 GMT -->

</html>
