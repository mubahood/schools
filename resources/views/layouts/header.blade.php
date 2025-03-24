<?php
use App\Models\PostCategory;
if (!isset($header_style)) {
    $header_style = 11;
}

?>

@if ($header_style == 1)
    <header class="header navbar navbar-expand-lg bg-light navbar-sticky">
    @elseif($header_style == 2)
        <header class="header navbar navbar-expand-lg position-absolute navbar-sticky">
        @else
            <header class="header navbar navbar-expand-lg bg-light border-bottom border-light shadow-sm fixed-top">
@endif




<div class="container px-3">
    <a href="{{ url('/') }}" class="navbar-brand pe-3">
        <img src="{{ url('assets/img/logo.png') }}" width="200" alt="TaskEase">
    </a>
    <div id="navbarNav" class="offcanvas offcanvas-end">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title">Menu</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                <li class="nav-item">
                    <a href="{{ url('') }}" class="nav-link">Home</a>
                </li>


                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">About</a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="{{ url('about-us') }}" class="dropdown-item">Project Overview</a>
                        </li>
                        <li>
                            <a href="{{ url('vision-mission') }}" class="dropdown-item">Project objectives</a>
                        </li>
                        <li>
                            <a href="{{ url('constitution') }}" class="dropdown-item">Project Profile</a>
                        </li>
                        <li>
                            <a href="{{ url('our-team') }}" class="dropdown-item">Our team</a>
                        </li>
                        <li>
                            <a href="{{ url('ucc') }}" class="dropdown-item">UCC Message</a>
                        </li>
                        <li>
                            <a href="?" class="dropdown-item">Contact us</a>
                        </li>
                    </ul>
                </li>



                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">Services</a>
                    <ul class="dropdown-menu">
                        <li><a href="#" class="dropdown-item">Persons with disabilites - national profiling</a>
                        </li>
                        <li><a href="#" class="dropdown-item">Guidance and counseling </a></li>
                        <li><a href="#" class="dropdown-item">Jobs and opportunities </a></li>
                        <li><a href="#" class="dropdown-item">Shop</a></li>
                        <li><a href="#" class="dropdown-item">Institutions</a></li>
                        <li><a href="#" class="dropdown-item">Innovations</a></li>
                        <li><a href="#" class="dropdown-item">Testimonials</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="{{ url('news') }}" class="nav-link">News</a>
                </li>

                <li class="nav-item">
                    <a href="{{ url('news') }}" class="nav-link">Events</a>
                </li>


                <style>
                    .blink {
                        animation: blinking 1s linear infinite;
                        color: white;
                        border-radius: 2rem;
                        box-shadow: rgb(38, 57, 77) 0px 20px 30px -10px;
                        border: 3px dashed rgb(225, 225, 4);
                        padding: 3px;
                        font-weight: 800;
                    }

                    @keyframes blinking {

                        from,
                        49.9% {
                            background-color: rgb(178, 3, 3);
                        }

                        50%,
                        to {
                            background-color: #3f88f4;
                        }
                    }

                    .blink:hover {
                        background-color: #3f88f4;
                        color: white !important;
                    }
                </style>

                <li class="nav-item">
                    <a href="{{ admin_url() }}" title="Create an account" class="nav-link blink">Register Now</a>
                </li>



            </ul>
        </div>
        <div class="offcanvas-header border-top">

            @guest
                <a href="{{ admin_url() }}" class="btn btn-primary w-100" rel="noopener">
                    <i class="bx bx-cart fs-4 lh-1 me-1"></i> &nbsp;MY DASHBOARD
                </a>
            @endguest
            @auth
                <a href="{{ url('dashboard') }}" class="btn btn-primary w-100" rel="noopener">
                    <i class="bx bx-cart fs-4 lh-1 me-1"></i> &nbsp;MY DASHBOARD
                </a>
            @endauth

        </div>
    </div>
    <div class="form-check form-switch mode-switch pe-lg-1 ms-auto me-4" data-bs-toggle="mode">
        <input type="checkbox" class="form-check-input" id="theme-mode">
        <label class="form-check-label d-none d-sm-block" for="theme-mode">Light</label>
        <label class="form-check-label d-none d-sm-block" for="theme-mode">Dark</label>
    </div>
    <button type="button" class="navbar-toggler" data-bs-toggle="offcanvas" data-bs-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    @guest
        <a href="{{ admin_url('') }}" class="btn btn-primary btn-sm fs-sm rounded d-none d-lg-inline-flex" rel="noopener">
            <i class="bx bx-accessibility fs-5 lh-1 me-1"></i>Observatory
        </a>
    @endguest

    @auth
        <a href="{{ url('dashboard') }}" class="btn btn-primary btn-sm fs-sm rounded d-none d-lg-inline-flex"
            rel="noopener">
            <i class="bx bx-cart fs-5 lh-1 me-1"></i> &nbsp;MY DASHBOARD
        </a>

    @endauth

</div>
</header>
