<?php
//use Utils model
use App\Models\Utils;

?>@extends('layouts.base-layout')
{{-- Custom CSS for Enhancements --}}
<style>
    /* Keyframes for Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(40px);
            /* Slightly increased distance */
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

    /* Base Animation Class */
    .section-animate {
        opacity: 0;
        /* Start hidden */
        animation: fadeInUp 0.9s ease-out forwards;
        animation-play-state: paused;
        /* Controlled by JS */
    }

    .section-animate.animated {
        animation-play-state: running;
    }

    /* Staggered Animation Delays */
    #features .col:nth-child(1) .card.section-animate {
        animation-delay: 0.1s;
    }

    #features .col:nth-child(2) .card.section-animate {
        animation-delay: 0.2s;
    }

    #features .col:nth-child(3) .card.section-animate {
        animation-delay: 0.3s;
    }

    #features .col:nth-child(4) .card.section-animate {
        animation-delay: 0.4s;
    }

    #features .col:nth-child(5) .card.section-animate {
        animation-delay: 0.5s;
    }

    #features .col:nth-child(6) .card.section-animate {
        animation-delay: 0.6s;
    }

    #key-modules .col:nth-child(1) .card.section-animate {
        animation-delay: 0.1s;
    }

    #key-modules .col:nth-child(2) .card.section-animate {
        animation-delay: 0.2s;
    }

    #key-modules .col:nth-child(3) .card.section-animate {
        animation-delay: 0.3s;
    }

    #key-modules .col:nth-child(4) .card.section-animate {
        animation-delay: 0.4s;
    }

    #key-modules .col:nth-child(5) .card.section-animate {
        animation-delay: 0.5s;
    }

    #key-modules .col:nth-child(6) .card.section-animate {
        animation-delay: 0.6s;
    }

    #key-modules .col:nth-child(7) .card.section-animate {
        animation-delay: 0.7s;
    }

    #key-modules .col:nth-child(8) .card.section-animate {
        animation-delay: 0.8s;
    }


    /* Card Hover Effects */
    #features .card-hover:not(.bg-transparent),
    #key-modules .card-hover:not(.bg-transparent),
    #schools .card-hover {
        transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
    }

    #features .card-hover:not(.bg-transparent):hover,
    #key-modules .card-hover:not(.bg-transparent):hover,
    #schools .card-hover:hover {
        transform: translateY(-6px);
        /* Slightly more lift */
        box-shadow: 0 0.5rem 1.8rem -0.4rem rgba(11, 15, 25, 0.1), 0 0.3rem 1rem -0.15rem rgba(11, 15, 25, 0.07) !important;
    }

    #schools .card img {
        transition: transform 0.3s ease-in-out;
    }

    #schools .card:hover img {
        transform: scale(1.1);
    }

    /* Feature Icon Hover/Animation */
    #features .card-icon-wrapper,
    #key-modules .card-icon-wrapper {
        transition: transform 0.3s ease-in-out;
    }

    #features .card:hover .card-icon-wrapper,
    #key-modules .card:hover .card-icon-wrapper {
        transform: scale(1.1) rotate(-5deg);
    }

    #features .card.section-animate.animated .card-icon-wrapper,
    #key-modules .card.section-animate.animated .card-icon-wrapper {
        animation: subtleBob 1.5s ease-in-out 1 forwards;
        animation-delay: inherit;
        /* Inherit delay from parent card */
    }


    /* Button Pulse Animation */
    .btn-primary.pulse-animation {
        animation: pulse 2.2s infinite cubic-bezier(0.66, 0, 0, 1);
    }

    /* Gradient Text for App Name */
    .app-name-gradient {
        background: linear-gradient(90deg, var(--si-info), var(--si-primary), #d946ef);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        display: inline;
        /* Use inline for flow within text */
        font-weight: 800;
        /* Make it slightly bolder */
    }

    .hero-text-gradient {
        /* Specific for hero if needed */
        background: linear-gradient(90deg, var(--si-info), var(--si-primary), #d946ef);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        display: inline-block;
    }

    /* Hero Background subtle movement */
    .hero-bg-animation {
        background: linear-gradient(-45deg, #0b0f19, #1f2233, #0d121f, #33354d);
        background-size: 400% 400%;
        animation: gradientBG 15s ease infinite;
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


    /* Testimonial card styling */
    #testimonials .card {
        transition: box-shadow .3s ease-in-out;
    }

    #testimonials .card:hover {
        box-shadow: var(--si-shadow-lg) !important;
    }

    /* Key Module Icons Styling */
    #key-modules .card-icon-wrapper i {
        font-size: 2.5rem;
        /* Make icons slightly larger */
        color: var(--si-primary);
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
                        <h1 class="display-4 text-light pt-sm-2 pb-1 pb-sm-3 mb-3">Automate all your school Processes with
                            <span class="hero-text-gradient">{{ Utils::app_name() }}</span>!
                        </h1>
                        <p class="fs-lg text-light opacity-70 pb-2 pb-sm-0 mb-4 mb-sm-5">Eliminate your school administrative
                            headaches - Smoothly manage admissions, academics, marks, report-cards generation, school fees
                            and much more in one powerful system.</p>
                        <a href="https://forms.gle/NP8RXx7YcpPbfi6b8" class="btn btn-primary shadow-primary btn-lg">Request a
                            Demo</a>
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
                    <h2 class="h1 mb-lg-4">Empowering Education, One Click at a Time</h2>
                    <p class="fs-lg text-muted mb-0">
                        Simplify every aspect of your school's operations.
                        <span class="hero-text-gradient">{{ Utils::app_name() }}</span> helps you save time, reduce effort,
                        and cut costs—so you can focus on what matters most.
                    </p>
                </div>
            </div>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-0 pb-xl-3">

                <!-- Item -->
                <div class="col position-relative">
                    <div class="card border-0 bg-transparent rounded-0 p-md-1 p-xl-3">
                        <div class="d-table bg-secondary rounded-3 p-3 mx-auto mt-3 mt-md-4">
                            <img src="silicon/assets/img/landing/saas-2/features/6.png" width="40" alt="Comments">
                        </div>
                        <div class="card-body text-center">
                            <h3 class="h5 pb-1 mb-2">Effortless Student Enrollment</h3>
                            <p class="mb-0">
                                Overwhelmed by paperwork and manual record-keeping?
                                <b class="hero-text-gradient">{{ Utils::app_name() }}</b> streamlines the entire admissions
                                process—from online applications to batch imports—so you can enroll students with ease.
                            </p>
                        </div>
                    </div>
                    <hr class="position-absolute top-0 end-0 w-1 h-100 d-none d-sm-block">
                    <hr class="position-absolute top-100 start-0 w-100 d-none d-sm-block">
                </div>

                <!-- Item -->
                <div class="col position-relative">
                    <div class="card border-0 bg-transparent rounded-0 p-md-1 p-xl-3">
                        <div class="d-table bg-secondary rounded-3 p-3 mx-auto mt-3 mt-md-4">
                            <img src="silicon/assets/img/landing/saas-2/features/5.png" width="40" alt="Analytics">
                        </div>
                        <div class="card-body text-center">
                            <h3 class="h5 pb-1 mb-2">Smart Fees & Billing</h3>
                            <p class="mb-0">
                                Tired of chasing late payments and juggling financial tracking?
                                <b class="hero-text-gradient">{{ Utils::app_name() }}</b> automates invoices, follows up on
                                payments in real time, and integrates with <b>School Pay</b> for stress-free fee collection.
                            </p>
                        </div>
                    </div>
                    <hr class="position-absolute top-0 end-0 w-1 h-100 d-none d-md-block">
                    <hr class="position-absolute top-100 start-0 w-100 d-none d-sm-block">
                </div>

                <!-- Item -->
                <div class="col position-relative">
                    <div class="card border-0 bg-transparent rounded-0 p-md-1 p-xl-3">
                        <div class="d-table bg-secondary rounded-3 p-3 mx-auto mt-3 mt-md-4">
                            <img src="silicon/assets/img/landing/saas-2/features/4.png" width="40" alt="Group">
                        </div>
                        <div class="card-body text-center">
                            <h3 class="h5 pb-1 mb-2">Advanced Academics & Reports</h3>
                            <p class="mb-0">
                                Spending hours on grading and report generation?
                                <b class="hero-text-gradient">{{ Utils::app_name() }}</b> automates marks entry, manages
                                grading
                                scales, and creates report cards—so you can keep everyone informed without the headache.
                            </p>
                        </div>
                    </div>
                    <hr class="position-absolute top-0 end-0 w-1 h-100 d-none d-sm-block d-md-none">
                    <hr class="position-absolute top-100 start-0 w-100 d-none d-sm-block">
                </div>

                <!-- Item -->
                <div class="col position-relative">
                    <div class="card border-0 bg-transparent rounded-0 p-md-1 p-xl-3">
                        <div class="d-table bg-secondary rounded-3 p-3 mx-auto mt-3 mt-md-4">
                            <img src="silicon/assets/img/landing/saas-2/features/3.png" width="40" alt="Security">
                        </div>
                        <div class="card-body text-center">
                            <h3 class="h5 pb-1 mb-2">Convenient Mobile App</h3>
                            <p class="mb-0">
                                Want instant access to your school's data, anytime? Our iOS and Android apps give parents,
                                teachers, and admins quick access to everything from student records to fee updates—wherever
                                they are.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Item -->
                <div class="col position-relative">
                    <div class="card border-0 bg-transparent rounded-0 p-md-1 p-xl-3">
                        <div class="d-table bg-secondary rounded-3 p-3 mx-auto mt-3 mt-md-4">
                            <img src="silicon/assets/img/landing/saas-2/features/2.png" width="40" alt="Security">
                        </div>
                        <div class="card-body text-center">
                            <h3 class="h5 pb-1 mb-2">Instant Notifications</h3>
                            <p class="mb-0">
                                Having a hard time keeping parents and staff up to speed? Send bulk messages, announcements,
                                and automated alerts through SMS, email, or push notifications—so everyone stays in the
                                loop.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Item -->
                <div class="col position-relative">
                    <div class="card border-0 bg-transparent rounded-0 p-md-1 p-xl-3">
                        <div class="d-table bg-secondary rounded-3 p-3 mx-auto mt-3 mt-md-4">
                            <img src="silicon/assets/img/landing/saas-2/features/1.png" width="40" alt="Security">
                        </div>
                        <div class="card-body text-center">
                            <h3 class="h5 pb-1 mb-2">And So Much More</h3>
                            <p class="mb-0">
                                Looking for a full-featured school management system?
                                <b>{{ Utils::app_name() }}</b> also covers hostels, transport, library, inventory,
                                visitor logs, and more—everything you need in one platform.
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


    {{-- Contact Section --}}
    <section id="contact" class="container py-5">
        <div class="row justify-content-center text-center mb-5">
            <div class="col-lg-8">
                <h2 class="display-5 section-animate">Reach Out to Us</h2>
                <p class="lead text-muted section-animate" style="animation-delay:0.2s;">
                    Connect with us easily! Use our form, call, email or WhatsApp—whatever you prefer.
                </p>
            </div>
        </div>

        <div class="row gy-4">
            {{-- Contact Form --}}
            <div class="col-md-6 section-animate" style="animation-delay:0.3s;">
                <div class="card border-0 shadow-sm p-4">
                    <form action="{{ url('contact.send') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Your Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea name="message" rows="4" class="form-control" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 pulse-animation">
                            Send Message
                        </button>
                    </form>
                </div>
            </div>

            {{-- Contact Details & Map --}}
            <div class="col-md-6">
                <div class="row g-4">
                    {{-- WhatsApp --}}
                    <div class="col-sm-6 section-animate" style="animation-delay:0.4s;">
                        <div class="card h-100 border-0 shadow-sm p-3 text-center">
                            <div class="mb-2">
                                <i class="bx bxl-whatsapp fs-2 text-success"></i>
                            </div>
                            <h5 class="mb-1">WhatsApp</h5>
                            <a href="https://wa.me/256779490831?text=Hello%20{{ Utils::app_name() }}%2C%20I%20would%20like%20to%20get%20in%20touch."
                                class="stretched-link text-decoration-none">
                                +256 779 490 831
                            </a>
                        </div>
                    </div>

                    {{-- Phone --}}
                    <div class="col-sm-6 section-animate" style="animation-delay:0.5s;">
                        <div class="card h-100 border-0 shadow-sm p-3 text-center">
                            <div class="mb-2">
                                <i class="bx bxs-phone fs-2 text-primary"></i>
                            </div>
                            <h5 class="mb-1">Call Us</h5>
                            <p class="mb-0">
                                <a href="tel:+256778167775">+256 778 167 775</a><br>
                                <a href="tel:+256393256165">+256 393 256 165</a>
                            </p>
                        </div>
                    </div>

                    {{-- Email --}}
                    <div class="col-sm-6 section-animate" style="animation-delay:0.6s;">
                        <div class="card h-100 border-0 shadow-sm p-3 text-center">
                            <div class="mb-2">
                                <i class="bx bxs-envelope fs-2 text-danger"></i>
                            </div>
                            <h5 class="mb-1">Email</h5>
                            <p class="mb-0">
                                <a href="mailto:cto@8technologies.net">cto@8technologies.net</a><br>
                                <a href="mailto:bm@8technologies.net">bm@8technologies.net</a>
                            </p>
                        </div>
                    </div>

                    {{-- Website --}}
                    <div class="col-sm-6 section-animate" style="animation-delay:0.7s;">
                        <div class="card h-100 border-0 shadow-sm p-3 text-center">
                            <div class="mb-2">
                                <i class="bx bx-globe fs-2 text-info"></i>
                            </div>
                            <h5 class="mb-1">Website</h5>
                            <a href="https://8technologies.net/" target="_blank" class="stretched-link">
                                8technologies.net
                            </a>
                        </div>
                    </div>

                    {{-- Location --}}
                    <div class="col-12 section-animate" style="animation-delay:0.8s;">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-0">
                                <iframe
                                    src="https://www.google.com/maps?q=Eight+Tech+Corporation+Towers,+Palm+Springs+Estates,+Kitagobwa,+Nangabo,+Kasangati+Town+Council,+Wakiso+District&output=embed"
                                    width="100%" height="250" style="border:0;" allowfullscreen=""
                                    loading="lazy"></iframe>
                            </div>
                            <div class="card-footer bg-white text-center">
                                <i class="bx bxs-map fs-4 me-1"></i>
                                Eight Tech Corporation Towers, Palm Springs Estates, Kitagobwa, Nangabo, Kasangati Town
                                Council, Wakiso District
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="bg-secondary py-5" id="get_started">
        <div class="container text-center py-1 py-md-4 py-lg-5">
            <h2 class="h1 mb-4">Ready to Get Started?</h2>
            <p class="lead pb-3 mb-3">Organize your tasks with a 14-day free trial</p>
            <a href="https://forms.gle/NP8RXx7YcpPbfi6b8" class="btn btn-primary shadow-primary btn-lg mb-1">Request a
                demo</a>
        </div>
    </section>
@endsection
