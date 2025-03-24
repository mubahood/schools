<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<!-- Mirrored from silicon.createx.studio/landing-saas-v2.html by HTTrack Website Copier/3.x [XR&CO'2014], Fri, 21 Mar 2025 21:47:56 GMT -->
<!-- Added by HTTrack -->
<meta http-equiv="content-type" content="text/html;charset=utf-8" /><!-- /Added by HTTrack -->

<head>
    <meta charset="utf-8">
    <title>Silicon | SaaS Landing v.2</title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="Silicon - Multipurpose Technology Bootstrap Template">
    <meta name="keywords"
        content="bootstrap, business, creative agency, mobile app showcase, saas, fintech, finance, online courses, software, medical, conference landing, services, e-commerce, shopping cart, multipurpose, shop, ui kit, marketing, seo, landing, blog, portfolio, html5, css3, javascript, gallery, slider, touch, creative">
    <meta name="author" content="Createx Studio">

    <!-- Viewport -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Theme switcher (color modes) -->
    <script src="silicon/assets/js/theme-switcher.js"></script>

    <!-- Favicon and Touch Icons -->
    <link rel="apple-touch-icon" sizes="180x180" href="silicon/assets/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="silicon/assets/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="silicon/assets/favicon/favicon-16x16.png">
    <link rel="manifest" href="silicon/assets/favicon/site.webmanifest">
    <link rel="mask-icon" href="silicon/assets/favicon/safari-pinned-tab.svg" color="#6366f1">
    <link rel="shortcut icon" href="silicon/assets/favicon/favicon.ico">
    <meta name="msapplication-TileColor" content="#080032">
    <meta name="msapplication-config" content="silicon/assets/favicon/browserconfig.xml">
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
                <a href="index-2.html" class="navbar-brand pe-3">
                    <img src="silicon/assets/img/logo.svg" width="47" alt="Silicon">
                    Silicon
                </a>
                <div id="navbarNav" class="offcanvas offcanvas-end bg-dark">
                    <div class="offcanvas-header border-bottom border-light">
                        <h5 class="offcanvas-title text-white">Menu</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
                            aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body">
                        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                            <li class="nav-item dropdown">
                                <a href="#" class="nav-link dropdown-toggle active" data-bs-toggle="dropdown"
                                    aria-current="page">Landings</a>
                                <div class="dropdown-menu dropdown-menu-dark p-0">
                                    <div class="d-lg-flex">
                                        <div class="mega-dropdown-column d-flex justify-content-center align-items-center rounded-3 rounded-end-0 px-0"
                                            style="margin: -1px; background-color: #f3f6ff;">
                                            <img src="silicon/assets/img/landings.jpg" alt="Landings">
                                        </div>
                                        <div class="mega-dropdown-column pt-lg-3 pb-lg-4"
                                            style="--si-mega-dropdown-column-width: 15rem;">
                                            <ul class="list-unstyled mb-0">
                                                <li><a href="index-2.html" class="dropdown-item">Template Intro Page</a>
                                                </li>
                                                <li><a href="landing-mobile-app-showcase-v1.html"
                                                        class="dropdown-item">Mobile App Showcase v.1</a></li>
                                                <li><a href="landing-mobile-app-showcase-v2.html"
                                                        class="dropdown-item">Mobile App Showcase v.2</a></li>
                                                <li><a href="landing-mobile-app-showcase-v3.html"
                                                        class="dropdown-item">Mobile App Showcase v.3<span
                                                            class="badge bg-success ms-2">New</span></a></li>
                                                <li><a href="landing-product.html"
                                                        class="dropdown-item d-flex align-items-center">Product
                                                        Landing</a></li>
                                                <li><a href="landing-saas-v1.html" class="dropdown-item">SaaS v.1</a>
                                                </li>
                                                <li><a href="landing-saas-v2.html" class="dropdown-item">SaaS v.2</a>
                                                </li>
                                                <li><a href="landing-saas-v3.html" class="dropdown-item">SaaS v.3</a>
                                                </li>
                                                <li><a href="landing-saas-v4.html" class="dropdown-item">SaaS v.4</a>
                                                </li>
                                                <li><a href="landing-saas-v5.html" class="dropdown-item">SaaS v.5<span
                                                            class="badge bg-success ms-2">New</span></a></li>
                                            </ul>
                                        </div>
                                        <div class="mega-dropdown-column pt-lg-3 pb-lg-4">
                                            <ul class="list-unstyled mb-0">
                                                <li><a href="landing-startup.html"
                                                        class="dropdown-item d-flex align-items-center">Startup</a>
                                                </li>
                                                <li><a href="landing-financial.html" class="dropdown-item">Financial
                                                        Consulting</a></li>
                                                <li><a href="landing-online-courses.html" class="dropdown-item">Online
                                                        Courses</a></li>
                                                <li><a href="landing-medical.html" class="dropdown-item">Medical</a>
                                                </li>
                                                <li><a href="landing-software-dev-agency-v1.html"
                                                        class="dropdown-item">Software Dev Agency v.1</a></li>
                                                <li><a href="landing-software-dev-agency-v2.html"
                                                        class="dropdown-item">Software Dev Agency v.2</a></li>
                                                <li><a href="landing-software-dev-agency-v3.html"
                                                        class="dropdown-item">Software Dev Agency v.3</a></li>
                                                <li><a href="landing-conference.html"
                                                        class="dropdown-item">Conference</a></li>
                                                <li><a href="landing-digital-agency.html"
                                                        class="dropdown-item">Digital Agency</a></li>
                                                <li><a href="landing-blog.html" class="dropdown-item">Blog
                                                        Homepage</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="nav-item dropdown">
                                <a href="#" class="nav-link dropdown-toggle"
                                    data-bs-toggle="dropdown">Pages</a>
                                <div class="dropdown-menu dropdown-menu-dark">
                                    <div class="d-lg-flex pt-lg-3">
                                        <div class="mega-dropdown-column">
                                            <h6 class="text-light px-3 mb-2">About</h6>
                                            <ul class="list-unstyled mb-3">
                                                <li><a href="about-v1.html" class="dropdown-item py-1">About v.1</a>
                                                </li>
                                                <li><a href="about-v2.html" class="dropdown-item py-1">About v.2</a>
                                                </li>
                                                <li><a href="about-v3.html" class="dropdown-item py-1">About v.3</a>
                                                </li>
                                            </ul>
                                            <h6 class="text-light px-3 mb-2">Blog</h6>
                                            <ul class="list-unstyled mb-3">
                                                <li><a href="blog-list-with-sidebar.html"
                                                        class="dropdown-item py-1">List View with Sidebar</a></li>
                                                <li><a href="blog-grid-with-sidebar.html"
                                                        class="dropdown-item py-1">Grid View with Sidebar</a></li>
                                                <li><a href="blog-list-no-sidebar.html"
                                                        class="dropdown-item py-1">List View no Sidebar</a></li>
                                                <li><a href="blog-grid-no-sidebar.html"
                                                        class="dropdown-item py-1">Grid View no Sidebar</a></li>
                                                <li><a href="blog-simple-feed.html" class="dropdown-item py-1">Simple
                                                        Feed</a></li>
                                                <li><a href="blog-single.html" class="dropdown-item py-1">Single
                                                        Post</a></li>
                                                <li><a href="blog-podcast.html" class="dropdown-item py-1">Podcast</a>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="mega-dropdown-column">
                                            <h6 class="text-light px-3 mb-2">Portfolio</h6>
                                            <ul class="list-unstyled mb-3">
                                                <li><a href="portfolio-grid.html" class="dropdown-item py-1">Grid
                                                        View</a></li>
                                                <li><a href="portfolio-list.html" class="dropdown-item py-1">List
                                                        View</a></li>
                                                <li><a href="portfolio-slider.html" class="dropdown-item py-1">Slider
                                                        View</a></li>
                                                <li><a href="portfolio-courses.html"
                                                        class="dropdown-item py-1">Courses</a></li>
                                                <li><a href="portfolio-single-project.html"
                                                        class="dropdown-item py-1">Single Project</a></li>
                                                <li><a href="portfolio-single-course.html"
                                                        class="dropdown-item py-1">Single Course</a></li>
                                            </ul>
                                            <h6 class="text-light px-3 mb-2">Services</h6>
                                            <ul class="list-unstyled mb-3">
                                                <li><a href="services-v1.html" class="dropdown-item py-1">Services
                                                        v.1</a></li>
                                                <li><a href="services-v2.html" class="dropdown-item py-1">Services
                                                        v.2</a></li>
                                                <li><a href="services-single-v1.html"
                                                        class="dropdown-item py-1">Service Details v.1</a></li>
                                                <li><a href="services-single-v2.html"
                                                        class="dropdown-item py-1">Service Details v.2</a></li>
                                            </ul>
                                        </div>
                                        <div class="mega-dropdown-column">
                                            <h6 class="text-light px-3 mb-2">Pricing</h6>
                                            <ul class="list-unstyled mb-3">
                                                <li><a href="pricing.html" class="dropdown-item py-1">Pricing Page</a>
                                                </li>
                                            </ul>
                                            <h6 class="text-light px-3 mb-2">Contacts</h6>
                                            <ul class="list-unstyled mb-3">
                                                <li><a href="contacts-v1.html" class="dropdown-item py-1">Contacts
                                                        v.1</a></li>
                                                <li><a href="contacts-v2.html" class="dropdown-item py-1">Contacts
                                                        v.2</a></li>
                                                <li><a href="contacts-v3.html" class="dropdown-item py-1">Contacts
                                                        v.3</a></li>
                                            </ul>
                                            <h6 class="text-light px-3 mb-2">Specialty</h6>
                                            <ul class="list-unstyled">
                                                <li><a href="404-v1.html" class="dropdown-item py-1">404 Error v.1</a>
                                                </li>
                                                <li><a href="404-v2.html" class="dropdown-item py-1">404 Error v.2</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="nav-item dropdown">
                                <a href="#" class="nav-link dropdown-toggle"
                                    data-bs-toggle="dropdown">Account</a>
                                <ul class="dropdown-menu dropdown-menu-dark">
                                    <li><a href="account-details.html" class="dropdown-item">Account Details</a></li>
                                    <li><a href="account-security.html" class="dropdown-item">Security</a></li>
                                    <li><a href="account-notifications.html" class="dropdown-item">Notifications</a>
                                    </li>
                                    <li><a href="account-messages.html" class="dropdown-item">Messages</a></li>
                                    <li><a href="account-saved-items.html" class="dropdown-item">Saved Items</a></li>
                                    <li><a href="account-collections.html" class="dropdown-item">My Collections</a>
                                    </li>
                                    <li><a href="account-payment.html" class="dropdown-item">Payment Details</a></li>
                                    <li><a href="account-signin.html" class="dropdown-item">Sign In</a></li>
                                    <li><a href="account-signup.html" class="dropdown-item">Sign Up</a></li>
                                </ul>
                            </li>
                            <li class="nav-item">
                                <a href="components/typography.html" class="nav-link">UI Kit</a>
                            </li>
                            <li class="nav-item">
                                <a href="docs/getting-started.html" class="nav-link">Docs</a>
                            </li>
                        </ul>
                    </div>
                    <div class="offcanvas-header border-top border-light">
                        <a href="https://themes.getbootstrap.com/product/silicon-business-technology-template-ui-kit/"
                            class="btn btn-primary w-100" target="_blank" rel="noopener">
                            <i class="bx bx-cart fs-4 lh-1 me-1"></i>
                            &nbsp;Buy now
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
                <a href="https://themes.getbootstrap.com/product/silicon-business-technology-template-ui-kit/"
                    class="btn btn-primary btn-sm fs-sm rounded d-none d-lg-inline-flex" target="_blank"
                    rel="noopener">
                    <i class="bx bx-cart fs-5 lh-1 me-1"></i>
                    &nbsp;Buy now
                </a>
            </div>
        </header>

        @yield('content')

    </main>


    <!-- Footer -->
    <footer class="footer bg-dark pt-5 pb-4 pb-lg-5" data-bs-theme="dark">
        <div class="container text-center pt-lg-3">
            <div class="navbar-brand justify-content-center text-dark mb-2 mb-lg-4">
                <img src="silicon/assets/img/logo.svg" class="me-2" width="60" alt="Silicon">
                <span class="fs-4">Silicon</span>
            </div>
            <ul class="nav justify-content-center pt-3 pb-4 pb-lg-5">
                <li class="nav-item"><a href="#" class="nav-link">Home</a></li>
                <li class="nav-item"><a href="#" class="nav-link">Features</a></li>
                <li class="nav-item"><a href="#" class="nav-link">Overview</a></li>
                <li class="nav-item"><a href="#" class="nav-link">Blog</a></li>
                <li class="nav-item"><a href="#" class="nav-link">Contacts</a></li>
                <li class="nav-item"><a href="#" class="nav-link">Account</a></li>
            </ul>
            <div class="d-flex flex-column flex-sm-row justify-content-center">
                <a href="#" class="btn btn-dark btn-lg px-3 py-2 me-sm-4 mb-3">
                    <img src="silicon/assets/img/market/appstore-light.svg" class="light-mode-img" width="124"
                        alt="App Store">
                    <img src="silicon/assets/img/market/appstore-dark.svg" class="dark-mode-img" width="124"
                        alt="App Store">
                </a>
                <a href="#" class="btn btn-dark btn-lg px-3 py-2 mb-3">
                    <img src="silicon/assets/img/market/googleplay-light.svg" class="light-mode-img" width="139"
                        alt="Google Play">
                    <img src="silicon/assets/img/market/googleplay-dark.svg" class="dark-mode-img" width="139"
                        alt="Google Play">
                </a>
            </div>
            <div class="d-flex justify-content-center pt-4 mt-lg-3">
                <a href="#" class="btn btn-icon btn-secondary btn-facebook mx-2" aria-label="Facebook">
                    <i class="bx bxl-facebook"></i>
                </a>
                <a href="#" class="btn btn-icon btn-secondary btn-instagram mx-2" aria-label="Instagram">
                    <i class="bx bxl-instagram"></i>
                </a>
                <a href="#" class="btn btn-icon btn-secondary btn-twitter mx-2" aria-label="Twitter">
                    <i class="bx bxl-twitter"></i>
                </a>
                <a href="#" class="btn btn-icon btn-secondary btn-youtube mx-2" aria-label="YouTube">
                    <i class="bx bxl-youtube"></i>
                </a>
            </div>
            <p class="nav d-block fs-sm text-center pt-5 mt-lg-4 mb-0">
                <span class="text-light opacity-60">&copy; All rights reserved. Made by </span>
                <a class="nav-link d-inline-block p-0" href="https://createx.studio/" target="_blank"
                    rel="noopener">Createx Studio</a>
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
