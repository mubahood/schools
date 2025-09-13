<?php
//use Utils model
use App\Models\Utils;

?>@extends('layouts.base-layout')
{{-- Custom CSS for Enhancements --}}
<style>
    /* Performance optimizations */
    * {
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    /* Critical path CSS - above the fold content */
    .hero-bg-animation {
        background: linear-gradient(-45deg, #0b0f19, #1f2233, #0d121f, #33354d);
        background-size: 400% 400%;
        animation: gradientBG 15s ease infinite;
        will-change: background-position;
    }

    /* Keyframes for Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(40px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(var(--si-primary-rgb), 0.6);
        }
        70% {
            box-shadow: 0 0 0 12px rgba(var(--si-primary-rgb), 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(var(--si-primary-rgb), 0);
        }
    }

    @keyframes subtleBob {
        0% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-5px);
        }
        100% {
            transform: translateY(0);
        }
    }

    @keyframes gradientBG {
        0% {
            background-position: 0% 50%;
        }
        50% {
            background-position: 100% 50%;
        }
        100% {
            background-position: 0% 50%;
        }
    }

    /* Base Animation Class */
    .section-animate {
        opacity: 0;
        animation: fadeInUp 0.9s ease-out forwards;
        animation-play-state: paused;
        will-change: opacity, transform;
    }

    .section-animate.animated {
        animation-play-state: running;
    }

    /* Staggered Animation Delays - Optimized */
    #features .col:nth-child(1) .card.section-animate { animation-delay: 0.1s; }
    #features .col:nth-child(2) .card.section-animate { animation-delay: 0.2s; }
    #features .col:nth-child(3) .card.section-animate { animation-delay: 0.3s; }
    #features .col:nth-child(4) .card.section-animate { animation-delay: 0.4s; }
    #features .col:nth-child(5) .card.section-animate { animation-delay: 0.5s; }
    #features .col:nth-child(6) .card.section-animate { animation-delay: 0.6s; }

    #key-modules .col:nth-child(1) .card.section-animate { animation-delay: 0.1s; }
    #key-modules .col:nth-child(2) .card.section-animate { animation-delay: 0.2s; }
    #key-modules .col:nth-child(3) .card.section-animate { animation-delay: 0.3s; }
    #key-modules .col:nth-child(4) .card.section-animate { animation-delay: 0.4s; }
    #key-modules .col:nth-child(5) .card.section-animate { animation-delay: 0.5s; }
    #key-modules .col:nth-child(6) .card.section-animate { animation-delay: 0.6s; }
    #key-modules .col:nth-child(7) .card.section-animate { animation-delay: 0.7s; }
    #key-modules .col:nth-child(8) .card.section-animate { animation-delay: 0.8s; }

    /* Enhanced Card Hover Effects */
    .card-hover:not(.bg-transparent) {
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), 
                    box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        will-change: transform, box-shadow;
    }

    .card-hover:not(.bg-transparent):hover {
        transform: translateY(-6px);
        box-shadow: 0 0.5rem 1.8rem -0.4rem rgba(11, 15, 25, 0.1), 
                    0 0.3rem 1rem -0.15rem rgba(11, 15, 25, 0.07) !important;
    }

    /* Feature Icon Animations */
    .card-icon-wrapper {
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        will-change: transform;
    }

    .card:hover .card-icon-wrapper {
        transform: scale(1.1) rotate(-5deg);
    }

    .card.section-animate.animated .card-icon-wrapper {
        animation: subtleBob 1.5s ease-in-out 1 forwards;
        animation-delay: inherit;
    }

    /* Button Enhancements */
    .btn-primary.pulse-animation {
        animation: pulse 2.2s infinite cubic-bezier(0.66, 0, 0, 1);
        will-change: box-shadow;
    }

    .btn {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        will-change: transform;
    }

    /* Gradient Text Optimizations */
    .app-name-gradient,
    .hero-text-gradient {
        background: linear-gradient(90deg, var(--si-info), var(--si-primary), #d946ef);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        display: inline;
        font-weight: 800;
    }

    /* Lazy loading placeholder */
    img.lazy {
        background-color: #f8f9fa;
        background-size: cover;
        background-position: center;
    }

    /* Testimonial optimizations */
    #testimonials .card {
        transition: box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        will-change: box-shadow;
    }

    #testimonials .card:hover {
        box-shadow: var(--si-shadow-lg) !important;
    }

    /* Key Module Icons */
    #key-modules .card-icon-wrapper i {
        font-size: 2.5rem;
        color: var(--si-primary);
    }

    /* Responsive improvements */
    @media (max-width: 768px) {
        .display-4 {
            font-size: 2.5rem;
        }
        
        .card-icon-wrapper {
            transform: scale(0.9);
        }
        
        .btn-lg {
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
        }
    }

    @media (max-width: 576px) {
        .display-4 {
            font-size: 2rem;
        }
        
        .hero-text-gradient {
            display: block;
            margin-top: 0.5rem;
        }
        
        .d-flex.flex-column.flex-sm-row {
            gap: 1rem !important;
        }
    }

    /* Reduce motion for accessibility */
    @media (prefers-reduced-motion: reduce) {
        *,
        *::before,
        *::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }
        
        .section-animate {
            opacity: 1;
            animation: none;
        }
    }

    /* Focus improvements for accessibility */
    .btn:focus-visible,
    a:focus-visible {
        outline: 2px solid var(--si-primary);
        outline-offset: 2px;
    }
</style>

@section('content')
    <!-- Hero -->
    <section class="position-relative overflow-hidden" id="home">
        <div class="position-relative bg-dark zindex-4 pt-lg-3 pt-xl-5">

            <!-- Text -->
            <div class="container zindex-5 pt-5">
                <div class="row justify-content-center text-center pt-4 pb-sm-2 py-lg-5">
                    <div class="col-xl-8 col-lg-9 col-md-10 py-5">
                        <h1 class="display-4 text-light pt-sm-2 pb-1 pb-sm-3 mb-3">Transform Your School Management with
                            <span class="hero-text-gradient">{{ Utils::app_name() }}</span>
                        </h1>
                        <p class="fs-lg text-light opacity-70 pb-2 pb-sm-0 mb-4 mb-sm-5">Streamline admissions, academics, financial management, and communication in one powerful platform. Join hundreds of schools that have revolutionized their operations with our comprehensive solution.</p>
                        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                            <a href="{{ url('/access-system') }}" class="btn btn-primary shadow-primary btn-lg pulse-animation">
                                <i class="bx bx-log-in-circle me-2"></i>Access The System
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom shape -->
            <div class="d-flex position-absolute top-100 start-0 w-100 overflow-hidden mt-n4 mt-sm-n1"
                style="color: var(--si-dark);">
                <div class="position-relative start-50 translate-middle-x flex-shrink-0" style="width: 3788px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="3788" height="144" viewBox="0 0 3788 144">
                        <path fill="currentColor" d="M0,0h3788.7c-525,90.2-1181.7,143.9-1894.3,143.9S525,90.2,0,0z" />
                    </svg>
                </div>
            </div>
            <div class="d-none d-lg-block" style="height: 300px;"></div>
            <div class="d-none d-md-block d-lg-none" style="height: 150px;"></div>
        </div>
        <div class="position-relative zindex-5 mx-auto" style="max-width: 1250px; transform: translateZ(-100px);">
            <div class="d-none d-lg-block" style="margin-top: -300px;"></div>
            <div class="d-none d-md-block d-lg-none" style="margin-top: -150px;"></div>

            <!-- Parallax (3D Tilt) gfx -->
            <div class="tilt-3d" data-tilt data-tilt-full-page-listening data-tilt-max="12" data-tilt-perspective="1200">
                <img src="silicon/assets/img/landing/saas-2/hero/layer01.png" alt="Dashboard">
                <div class="tilt-3d-inner position-absolute top-0 start-0 w-100 h-100">
                    <img src="silicon/assets/img/landing/saas-2/hero/layer02.png" alt="Cards">
                </div>
            </div>
        </div>
        <div class="position-absolute top-0 start-0 w-100 h-100" style="background-color: rgba(255,255,255,.05);">
        </div>
    </section>



    <!-- Features -->
    <section class="position-relative py-5" id="features">
        <div class="container position-relative zindex-5 pb-md-4 pt-md-2 pt-lg-3 pb-lg-5">
            <div class="row justify-content-center text-center pb-3 mb-sm-2 mb-lg-3">
                <div class="col-xl-6 col-lg-7 col-md-9">
                    <h2 class="h1 mb-lg-4">Why {{ Utils::app_name() }} is the Smart Choice</h2>
                    <p class="fs-lg text-muted mb-0">
                        Transform your educational institution with cutting-edge technology designed specifically for modern schools.
                        <span class="hero-text-gradient">{{ Utils::app_name() }}</span> empowers you to deliver exceptional education while reducing administrative burden.
                    </p>
                </div>
            </div>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-0 pb-xl-3">

                <!-- Item -->
                <div class="col position-relative">
                    <div class="card border-0 bg-transparent rounded-0 p-md-1 p-xl-3 card-hover section-animate">
                        <div class="d-table bg-secondary rounded-3 p-3 mx-auto mt-3 mt-md-4 card-icon-wrapper">
                            <img src="silicon/assets/img/landing/saas-2/features/6.png" width="40" alt="Student Enrollment">
                        </div>
                        <div class="card-body text-center">
                            <h3 class="h5 pb-1 mb-2">Effortless Student Management</h3>
                            <p class="mb-0">
                                Streamline student enrollment from application to graduation. 
                                <b class="hero-text-gradient">{{ Utils::app_name() }}</b> handles admissions, profile management, 
                                and academic tracking with intelligent automation and bulk import capabilities.
                            </p>
                        </div>
                    </div>
                    <hr class="position-absolute top-0 end-0 w-1 h-100 d-none d-sm-block">
                    <hr class="position-absolute top-100 start-0 w-100 d-none d-sm-block">
                </div>

                <!-- Item -->
                <div class="col position-relative">
                    <div class="card border-0 bg-transparent rounded-0 p-md-1 p-xl-3 card-hover section-animate">
                        <div class="d-table bg-secondary rounded-3 p-3 mx-auto mt-3 mt-md-4 card-icon-wrapper">
                            <img src="silicon/assets/img/landing/saas-2/features/5.png" width="40" alt="Financial Management">
                        </div>
                        <div class="card-body text-center">
                            <h3 class="h5 pb-1 mb-2">Smart Financial Management</h3>
                            <p class="mb-0">
                                End payment tracking nightmares with automated fee collection and real-time financial reporting.
                                <b class="hero-text-gradient">{{ Utils::app_name() }}</b> integrates with School Pay and provides 
                                comprehensive billing management with automated reminders.
                            </p>
                        </div>
                    </div>
                    <hr class="position-absolute top-0 end-0 w-1 h-100 d-none d-md-block">
                    <hr class="position-absolute top-100 start-0 w-100 d-none d-sm-block">
                </div>

                <!-- Item -->
                <div class="col position-relative">
                    <div class="card border-0 bg-transparent rounded-0 p-md-1 p-xl-3 card-hover section-animate">
                        <div class="d-table bg-secondary rounded-3 p-3 mx-auto mt-3 mt-md-4 card-icon-wrapper">
                            <img src="silicon/assets/img/landing/saas-2/features/4.png" width="40" alt="Academic Excellence">
                        </div>
                        <div class="card-body text-center">
                            <h3 class="h5 pb-1 mb-2">Academic Excellence Made Easy</h3>
                            <p class="mb-0">
                                Transform grading and reporting with automated mark calculations, customizable report cards, 
                                and comprehensive performance analytics. 
                                <b class="hero-text-gradient">{{ Utils::app_name() }}</b> makes academic management effortless.
                            </p>
                        </div>
                    </div>
                    <hr class="position-absolute top-0 end-0 w-1 h-100 d-none d-sm-block d-md-none">
                    <hr class="position-absolute top-100 start-0 w-100 d-none d-sm-block">
                </div>

                <!-- Item -->
                <div class="col position-relative">
                    <div class="card border-0 bg-transparent rounded-0 p-md-1 p-xl-3 card-hover section-animate">
                        <div class="d-table bg-secondary rounded-3 p-3 mx-auto mt-3 mt-md-4 card-icon-wrapper">
                            <img src="silicon/assets/img/landing/saas-2/features/3.png" width="40" alt="Mobile Access">
                        </div>
                        <div class="card-body text-center">
                            <h3 class="h5 pb-1 mb-2">Mobile-First Experience</h3>
                            <p class="mb-0">
                                Access your school's complete ecosystem from anywhere with our native iOS and Android apps. 
                                Parents, teachers, and administrators stay connected with real-time updates and instant access 
                                to all essential information.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Item -->
                <div class="col position-relative">
                    <div class="card border-0 bg-transparent rounded-0 p-md-1 p-xl-3 card-hover section-animate">
                        <div class="d-table bg-secondary rounded-3 p-3 mx-auto mt-3 mt-md-4 card-icon-wrapper">
                            <img src="silicon/assets/img/landing/saas-2/features/2.png" width="40" alt="Communication Hub">
                        </div>
                        <div class="card-body text-center">
                            <h3 class="h5 pb-1 mb-2">Seamless Communication Hub</h3>
                            <p class="mb-0">
                                Bridge communication gaps with intelligent messaging systems. Send targeted announcements, 
                                automated fee reminders, and emergency alerts through SMS, email, or push notifications 
                                to keep your entire school community informed.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Item -->
                <div class="col position-relative">
                    <div class="card border-0 bg-transparent rounded-0 p-md-1 p-xl-3 card-hover section-animate">
                        <div class="d-table bg-secondary rounded-3 p-3 mx-auto mt-3 mt-md-4 card-icon-wrapper">
                            <img src="silicon/assets/img/landing/saas-2/features/1.png" width="40" alt="Complete Solution">
                        </div>
                        <div class="card-body text-center">
                            <h3 class="h5 pb-1 mb-2">Complete School Ecosystem</h3>
                            <p class="mb-0">
                                Beyond basics, manage hostels, transport, library, inventory, visitor management, and more. 
                                <b>{{ Utils::app_name() }}</b> is your all-in-one platform for complete institutional 
                                management with enterprise-grade security and reliability.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="position-absolute top-0 start-0 w-100 h-100" style="background-color: rgba(255,255,255,.05);"></div>
    </section>



    <!-- Light / Dark mode (Comparison slider) -->
    <section class="d-flex w-100 position-relative overflow-hidden">
        <div class="position-relative flex-xl-shrink-0 zindex-5 start-50 translate-middle-x" style="max-width: 1920px;">
            <div class="mx-md-n5 mx-xl-0">
                <div class="mx-n4 mx-sm-n5 mx-xl-0">
                    <div class="mx-n5 mx-xl-0">
                        <img-comparison-slider class="mx-n5 mx-xl-0">
                            <img slot="first" src="silicon/assets/img/landing/saas-2/dark-mode.jpg" alt="Dak Mode">
                            <img slot="second" src="silicon/assets/img/landing/saas-2/light-mode.jpg" alt="Light Mode">
                            <div slot="handle" style="width: 36px;">
                                <svg class="text-primary shadow-primary rounded-circle" width="36" height="36"
                                    xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 36 36">
                                    <g>
                                        <circle fill="currentColor" cx="18" cy="18" r="18" />
                                    </g>
                                    <path fill="#fff"
                                        d="M22.2,17.2h-8.3v-3.3L9.7,18l4.2,4.2v-3.3h8.3v3.3l4.2-4.2l-4.2-4.2V17.2z" />
                                </svg>
                            </div>
                        </img-comparison-slider>
                    </div>
                </div>
            </div>
        </div>
        <div class="position-absolute top-0 start-0 w-50 h-100 bg-dark"></div>
        <div class="position-absolute top-0 end-0 w-50 h-100" style="background-color: #f3f6ff;"></div>
    </section>

    <section class="mb-5 pt-md-2 pt-lg-4 pt-xl-5">
        <div class="container mt-4 pt-2">

            <div class="position-relative rounded-5 overflow-hidden">
                <div
                    class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center zindex-5">
                    <a href="https://www.youtube.com/watch?v=-4j5okWNORg"
                        class="btn btn-video btn-icon btn-xl stretched-link bg-white" data-bs-toggle="video"
                        aria-label="Play video" data-lg-id="7b62b2a3-2f2b-4bdf-8c3b-227e3d2d5696">
                        <i class="bx bx-play"></i>
                    </a>
                </div>
                <span class="position-absolute top-0 start-0 w-100 h-100  bg-dark opacity-40 "></span>
                <img src="assets/01-TUSOME.png" alt="Cover  " style="width: 100%" class="img img-rounded rounded">
            </div>
        </div>
    </section>



    <!-- Testimonials -->
    <section class="container py-5 my-2 my-md-4 my-lg-5" id="testimonials">
        <div class="row pt-2 py-xl-3">
            <div class="col-lg-3 col-md-4">
                <h2 class="h1 text-center text-md-start mx-auto mx-md-0 pt-md-2" style="max-width: 300px;">What
                    <br class="d-none d-md-inline">People Say <br class="d-none d-md-inline">About
                    <span class="hero-text-gradient">{{ Utils::app_name() }}</span>:
                </h2>

                <!-- Slider controls (Prev / next buttons) -->
                <div class="d-flex justify-content-center justify-content-md-start pb-4 mb-2 pt-2 pt-md-4 mt-md-5">
                    <button type="button" id="prev-testimonial" class="btn btn-prev btn-icon btn-sm me-2"
                        aria-label="Previous">
                        <i class="bx bx-chevron-left"></i>
                    </button>
                    <button type="button" id="next-testimonial" class="btn btn-next btn-icon btn-sm ms-2"
                        aria-label="Next">
                        <i class="bx bx-chevron-right"></i>
                    </button>
                </div>
            </div>
            <div class="col-lg-9 col-md-8">
                <div class="swiper mx-n2"
                    data-swiper-options='{
              "slidesPerView": 1,
              "spaceBetween": 8,
              "loop": true,
              "navigation": {
                "prevEl": "#prev-testimonial",
                "nextEl": "#next-testimonial"
              },
              "breakpoints": {
                "500": {
                  "slidesPerView": 2
                },
                "1000": {
                  "slidesPerView": 2
                },
                "1200": {
                  "slidesPerView": 3
                }
              }
            }'>
                    <div class="swiper-wrapper">

                        <!-- Item -->
                        <div class="swiper-slide h-auto pt-4">
                            <figure class="d-flex flex-column h-100 px-2 px-sm-0 mb-0 mx-2">
                                <div class="card h-100 position-relative border-0 shadow-sm pt-4">
                                    <span
                                        class="btn btn-icon btn-primary shadow-primary pe-none position-absolute top-0 start-0 translate-middle-y ms-4">
                                        <i class="bx bxs-quote-left"></i>
                                    </span>
                                    <blockquote class="card-body pb-3 mb-0">
                                        <p class="mb-0"><b>{{ Utils::app_name() }}</b> has completely transformed our
                                            school operations. From
                                            admissions to finance, everything is automated, saving us time and reducing
                                            errors!</p>
                                    </blockquote>
                                    <div class="card-footer border-0 text-nowrap pt-0">
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bx-star text-muted opacity-75"></i>
                                        <i class="bx bx-star text-muted opacity-75"></i>
                                    </div>
                                </div>
                                <figcaption class="d-flex align-items-center ps-4 pt-4">
                                    <img src="silicon/assets/img/avatar/1.jpg" width="48" class="rounded-circle"
                                        alt="Robert Fox">
                                    <div class="ps-3">
                                        <h6 class="fs-sm fw-semibold mb-0">– Bursar</h6>
                                        <span class="fs-xs text-muted">BRIGHT FUTURE SS - Kaliro</span>
                                    </div>
                                </figcaption>
                            </figure>
                        </div>

                        <!-- Item -->
                        <div class="swiper-slide h-auto pt-4">
                            <figure class="d-flex flex-column h-100 px-2 px-sm-0 mb-0 mx-2">
                                <div class="card h-100 position-relative border-0 shadow-sm pt-4">
                                    <span
                                        class="btn btn-icon btn-primary shadow-primary pe-none position-absolute top-0 start-0 translate-middle-y ms-4">
                                        <i class="bx bxs-quote-left"></i>
                                    </span>
                                    <blockquote class="card-body pb-3 mb-0">
                                        <p class="mb-0">Managing student records and communication used to be a
                                            nightmare. With <b>{{ Utils::app_name() }}</b>, parents, teachers, and
                                            administrators are always in
                                            sync!.</p>
                                    </blockquote>
                                    <div class="card-footer border-0 text-nowrap pt-0">
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                    </div>
                                </div>
                                <figcaption class="d-flex align-items-center ps-4 pt-4">
                                    <img src="silicon/assets/img/avatar/2.jpg" width="48" class="rounded-circle"
                                        alt="Annette Black">
                                    <div class="ps-3">
                                        <h6 class="fs-sm fw-semibold mb-0">– Head Teacher</h6>
                                        <span class="fs-xs text-muted">LUKMAN PRIMARY SCHOOL</span>
                                    </div>
                                </figcaption>
                            </figure>
                        </div>

                        <!-- Item -->
                        <div class="swiper-slide h-auto pt-4">
                            <figure class="d-flex flex-column h-100 px-2 px-sm-0 mb-0 mx-2">
                                <div class="card h-100 position-relative border-0 shadow-sm pt-4">
                                    <span
                                        class="btn btn-icon btn-primary shadow-primary pe-none position-absolute top-0 start-0 translate-middle-y ms-4">
                                        <i class="bx bxs-quote-left"></i>
                                    </span>
                                    <blockquote class="card-body pb-3 mb-0">
                                        <p class="mb-0">School fees tracking has never been easier! Our collections have
                                            improved significantly since we started using <b>{{ Utils::app_name() }}</b>’s
                                            automated billing system.
                                        </p>
                                    </blockquote>
                                    <div class="card-footer border-0 text-nowrap pt-0">
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bx-star text-muted opacity-75"></i>
                                    </div>
                                </div>
                                <figcaption class="d-flex align-items-center ps-4 pt-4">
                                    <img src="silicon/assets/img/avatar/3.jpg" width="48" class="rounded-circle"
                                        alt="Jerome Bell">
                                    <div class="ps-3">
                                        <h6 class="fs-sm fw-semibold mb-0">– Finance Manager</h6>
                                        <span class="fs-xs text-muted">Kira Junior School - Kito</span>
                                    </div>
                                </figcaption>
                            </figure>
                        </div>

                        <!-- Item -->
                        <div class="swiper-slide h-auto pt-4">
                            <figure class="d-flex flex-column h-100 px-2 px-sm-0 mb-0 mx-2">
                                <div class="card h-100 position-relative border-0 shadow-sm pt-4">
                                    <span
                                        class="btn btn-icon btn-primary shadow-primary pe-none position-absolute top-0 start-0 translate-middle-y ms-4">
                                        <i class="bx bxs-quote-left"></i>
                                    </span>
                                    <blockquote class="card-body pb-3 mb-0">
                                        <p class="mb-0">The mobile app is a game-changer! Parents can check student
                                            progress, fees, and updates in real time, improving engagement and satisfaction.
                                        </p>
                                    </blockquote>
                                    <div class="card-footer border-0 text-nowrap pt-0">
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                    </div>
                                </div>
                                <figcaption class="d-flex align-items-center ps-4 pt-4">
                                    <img src="silicon/assets/img/avatar/4.jpg" width="48" class="rounded-circle"
                                        alt="Albert Flores">
                                    <div class="ps-3">
                                        <h6 class="fs-sm fw-semibold mb-0">- Parent</h6>
                                        <span class="fs-xs text-muted">Al-bushra Islamic junior school</span>
                                    </div>
                                </figcaption>
                            </figure>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- App download CTA -->
    <section class="container" id="apps">
        <div class="bg-secondary rounded-3 overflow-hidden py-5 px-4 ps-lg-0 pe-md-5 pe-lg-0">
            <div class="row align-items-center py-sm-2">

                <!-- Parallax gfx -->
                <div class="col-md-7 col-lg-6 offset-xl-1">
                    <div class="position-relative mx-auto mb-5 m-md-0" style="max-width: 526px;">
                        <img src="silicon/assets/img/landing/saas-2/device.png" class="d-block" alt="Device">
                        <div class="rellax d-block position-absolute top-0 end-0 w-100 mt-md-4 me-md-n5"
                            data-rellax-percentage="0.5" data-rellax-vertical-scroll-axis="xy"
                            data-rellax-horizontal-speed="0.6" data-rellax-vertical-speed="-0.6"
                            data-disable-parallax-down="md">
                            <img src="silicon/assets/img/landing/saas-2/screen.png" alt="App Screen">
                        </div>
                    </div>
                </div>

                <!-- Text + Download buttons -->
                <div class="col-xl-4 col-md-5 mt-n2 mt-md-0">
                    <h2 class="h1 text-center text-md-start mb-4 mb-lg-5">Download Our App for Any Devices:</h2>
                    <div class="row">
                        <div class="col-sm-6 col-md-12 pb-4 pb-sm-0">
                            <div
                                class="row row-cols-1 row-cols-lg-2 align-items-end text-center text-md-start pb-md-4 mb-lg-3">
                                <div class="col mb-3 mb-lg-0">
                                    <p class="text-muted mb-1">App Store</p>
                                    <div class="text-nowrap fs-sm pb-lg-1 mb-2">
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                    </div>
                                    <h3 class="h4 mb-1">iOS</h3>
                                    <p class="mb-0">rating 4.7, 217+ reviews</p>
                                </div>
                                <div class="col d-lg-flex justify-content-end">
                                    <a href="https://apps.apple.com/us/app/school-dynamics/id6469381244" target="_blank"
                                        class="btn btn-dark btn-lg px-3 py-2">
                                        <img src="silicon/assets/img/market/appstore-light.svg" class="light-mode-img"
                                            width="124" alt="App Store">
                                        <img src="silicon/assets/img/market/appstore-dark.svg" class="dark-mode-img"
                                            width="124" alt="App Store">
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-12">
                            <div class="row row-cols-1 row-cols-lg-2 align-items-end text-center text-md-start">
                                <div class="col mb-3 mb-lg-0">
                                    <p class="text-muted mb-1">Google Play</p>
                                    <div class="text-nowrap fs-sm pb-lg-1 mb-2">
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                        <i class="bx bxs-star text-warning"></i>
                                    </div>
                                    <h3 class="h4 mb-1">Android</h3>
                                    <p class="mb-0">rating 4.8, 412+ reviews</p>
                                </div>
                                <div class="col d-lg-flex justify-content-end">
                                    <a href="https://play.google.com/store/apps/details?id=schooldynamics.ug&hl=en"
                                        target="_blank" class="btn btn-dark btn-lg px-3 py-2">
                                        <img src="silicon/assets/img/market/googleplay-light.svg" class="light-mode-img"
                                            width="139" alt="Google Play">
                                        <img src="silicon/assets/img/market/googleplay-dark.svg" class="dark-mode-img"
                                            width="139" alt="Google Play">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- Integrations -->
    <section class="container mt-n1 mt-md-0 py-5" id="schools">
        <div class="row justify-content-center text-center pt-md-3 pb-4 py-lg-5 mb-1">
            <div class="col-xl-8 col-lg-9 col-md-10">
                <h2 class="h1 mb-lg-4">Who's Using <span class="hero-text-gradient">{{ Utils::app_name() }}</span>?</h2>
                <p class="fs-lg text-muted mb-0">Trusted by Schools Everywhere - Empowering Over 76 Schools Across Uganda
                    to Simplify Management and Enhance Learning.</p>
            </div>
        </div>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-2 g-sm-3 g-lg-4 pb-md-3 pb-lg-5">

            <!-- Item -->
            <div class="col">
                <div class="card card-body card-hover bg-light border-1 border-primary">
                    <img src="silicon/assets/img/avatar/1.jpg" class="d-block mb-4" width="56" alt="Google">
                    <p class="mb-0">BRIGHT FUTURE SECONDARY SCHOOL - Kaliro</p>
                </div>
            </div>

            <!-- Item -->
            <div class="col">
                <div class="card card-body card-hover bg-light border-1 border-primary">
                    <img src="silicon/assets/img/avatar/2.jpg" class="d-block mb-4" width="56" alt="Zoom">
                    <p class="mb-0"><span class="text-uppercase">Al-bushra Islamic junior school</span> - Kivebulaya
                        road.</p>
                </div>
            </div>

            <!-- Item -->
            <div class="col">
                <div class="card card-body card-hover bg-light border-1 border-primary">
                    <img src="silicon/assets/img/avatar/3.jpg" class="d-block mb-4" width="56" alt="Slack">
                    <p class="mb-0"><span class="text-uppercase">KIRA Junior School Kito</span> - Kira</p>
                </div>
            </div>

            <!-- Item -->
            <div class="col">
                <div class="card card-body card-hover bg-light border-1 border-primary">
                    <img src="silicon/assets/img/avatar/4.jpg" class="d-block mb-4" width="56" alt="Gmail">
                    <p class="mb-0">LUKMAN PRIMARY SCHOOL - Entebbe</p>
                </div>
            </div>

            <!-- Item -->
            <div class="col">
                <div class="card card-body card-hover bg-light border-1 border-primary">
                    <img src="silicon/assets/img/avatar/5.jpg" class="d-block mb-4" width="56" alt="Trello">
                    <p class="mb-0"><span class="text-uppercase">Bilal Islamic Secondary School</span> - Bwaise</p>
                </div>
            </div>

            <!-- Item -->
            <div class="col">
                <div class="card card-body card-hover bg-light border-1 border-primary">
                    <img src="silicon/assets/img/avatar/6.jpg" class="d-block mb-4" width="56" alt="Mailchimp">
                    <p class="mb-0"><span class="text-uppercase">ANWAR MUSLIM SECONDARY SCHOOL</span> - Mpererwe</p>
                </div>
            </div>

            <!-- Item -->
            <div class="col">
                <div class="card card-body card-hover bg-light border-1 border-primary">
                    <img src="silicon/assets/img/avatar/7.jpg" class="d-block mb-4" width="56" alt="Dropbox">
                    <p class="mb-0">QUEEN OF PEACE NOBLE'S SCHOOL</span> - KYEGEGWA</p>
                </div>
            </div>

            <!-- Item -->
            <div class="col">
                <div class="card card-body card-hover bg-light border-1 border-primary">
                    <img src="silicon/assets/img/avatar/8.jpg" class="d-block mb-4" width="56" alt="Evernote">
                    <p class="mb-0"><span class="text-uppercase">Tasneem Junior School - Nsanji</p>
                </div>
            </div>
        </div>
    </section>


    {{-- CONTACT --}}
    <section id="contact" class="py-5">
        <div class="container">
            {{-- Heading --}}
            <div class="row text-center mb-5">
                <div class="col">
                    <h2 class="h1 mb-2">Reach Out to Us</h2>
                    <p class="fs-lg text-muted">
                        Connect with us easily! Find our location, call or WhatsApp, email, or visit our website below.
                    </p>
                </div>
            </div>

            <div class="row g-4">
                {{-- Info Boxes --}}
                <div class="col-lg-6">
                    {{-- Location --}}
                    <div class="d-flex mb-4">
                        <div class="me-3">
                            <i class="bx bxs-map fs-2 text-primary"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Our Location</h5>
                            <p class="mb-0">
                                {{ Utils::get_company_address() }}
                            </p>
                        </div>
                    </div>

                    {{-- Phone --}}
                    <div class="d-flex mb-4">
                        <div class="me-3">
                            <i class="bx bxs-phone-call fs-2 text-primary"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Phone</h5>
                            <p class="mb-0">
                                <a href="tel:+256778167775"
                                    class="text-decoration-none">+256&nbsp;778&nbsp;167&nbsp;775</a><br>
                                <a href="tel:+256393256165"
                                    class="text-decoration-none">+256&nbsp;393&nbsp;256&nbsp;165</a>
                            </p>
                        </div>
                    </div>

                    {{-- WhatsApp --}}
                    <div class="d-flex mb-4">
                        <div class="me-3">
                            <i class="bx bxl-whatsapp fs-2 text-primary"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">WhatsApp</h5>
                            <p class="mb-0">
                                <a href="{{ Utils::get_whatsapp_link() }}"
                                    target="_blank" class="text-decoration-none">
                                    {{ Utils::get_support_phone() }}
                                </a>
                            </p>
                        </div>
                    </div>

                    {{-- Booking Demo --}}
                    <div class="d-flex mb-4">
                        <div class="me-3">
                            <i class="bx bx-calendar-check fs-2 text-primary"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Book a Demo</h5>
                            <p class="mb-0">
                                <a href="tel:+256779490831" class="text-decoration-none">
                                    +256&nbsp;779&nbsp;490&nbsp;831
                                </a>
                            </p>
                        </div>
                    </div>

                    {{-- Email --}}
                    <div class="d-flex mb-4">
                        <div class="me-3">
                            <i class="bx bxs-envelope fs-2 text-primary"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Email</h5>
                            <p class="mb-0">
                                <a href="mailto:cto@8technologies.net"
                                    class="text-decoration-none">cto@8technologies.net</a><br>
                                <a href="mailto:bm@8technologies.net"
                                    class="text-decoration-none">bm@8technologies.net</a>
                            </p>
                        </div>
                    </div>

                    {{-- Website --}}
                    <div class="d-flex">
                        <div class="me-3">
                            <i class="bx bxl-chrome fs-2 text-primary"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Website</h5>
                            <p class="mb-0">
                                <a href="https://8technologies.net/" target="_blank" class="text-decoration-none">
                                    8technologies.net
                                </a>
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Google Map --}}
                <div class="col-lg-6">
                    <div class="ratio ratio-16x9 rounded-3 overflow-hidden shadow-sm">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3960.0000000000005!2d32.60000000000001!3d0.4000000000000001!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x177db0b000000001%3A0x0000000000000000!2sEight%20Tech%20Corporation%20Towers!5e0!3m2!1sen!2sug!4v1694949083101!5m2!1sen!2sug"
                            allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="bg-secondary py-5" id="get_started">
        <div class="container text-center py-1 py-md-4 py-lg-5">
            <h2 class="h1 mb-4">Ready to Transform Your School?</h2>
            <p class="lead pb-3 mb-4">Join hundreds of schools already using {{ Utils::app_name() }} to streamline their operations and enhance educational excellence.</p>
            <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                <a href="{{ url('/access-system') }}" class="btn btn-primary shadow-primary btn-lg pulse-animation">
                    <i class="bx bx-log-in-circle me-2"></i>Access The System
                </a>
            </div>
            <p class="mt-4 text-muted small">
                <i class="bx bx-shield-check me-1"></i>Secure • 
                <i class="bx bx-mobile me-1"></i>Mobile Ready • 
                <i class="bx bx-support me-1"></i>24/7 Support
            </p>
        </div>
    </section>
@endsection

{{-- Performance and Animation JavaScript --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Intersection Observer for scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animated');
                observer.unobserve(entry.target); // Stop observing once animated
            }
        });
    }, observerOptions);

    // Observe all elements with section-animate class
    document.querySelectorAll('.section-animate').forEach(el => {
        observer.observe(el);
    });

    // Lazy loading for images
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            }
        });
    });

    document.querySelectorAll('img[data-src]').forEach(img => {
        imageObserver.observe(img);
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Enhanced button interactions
    document.querySelectorAll('.btn').forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        btn.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // Preload critical images
    const criticalImages = [
        'silicon/assets/img/landing/saas-2/hero/layer01.png',
        'silicon/assets/img/landing/saas-2/hero/layer02.png'
    ];

    criticalImages.forEach(src => {
        const link = document.createElement('link');
        link.rel = 'preload';
        link.as = 'image';
        link.href = src;
        document.head.appendChild(link);
    });

    // Performance monitoring
    if ('performance' in window) {
        window.addEventListener('load', function() {
            setTimeout(() => {
                const perfData = performance.getEntriesByType('navigation')[0];
                if (perfData && perfData.loadEventEnd - perfData.loadEventStart > 3000) {
                    console.log('Page load might be slow, consider optimizations');
                }
            }, 0);
        });
    }
});
</script>
@endpush
