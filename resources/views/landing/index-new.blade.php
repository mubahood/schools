<?php
//use Utils model
use App\Models\Utils;

?>@extends('layouts.base-layout')
@section('content')

{{-- Custom CSS for Enhancements --}}
<style>
    /* Keyframes for Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(40px); /* Slightly increased distance */
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(var(--si-primary-rgb), 0.6); }
        70% { box-shadow: 0 0 0 12px rgba(var(--si-primary-rgb), 0); }
        100% { box-shadow: 0 0 0 0 rgba(var(--si-primary-rgb), 0); }
    }

    @keyframes subtleBob {
        0% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
        100% { transform: translateY(0); }
    }

    /* Base Animation Class */
    .section-animate {
        opacity: 0; /* Start hidden */
        animation: fadeInUp 0.9s ease-out forwards;
        animation-play-state: paused; /* Controlled by JS */
    }
    .section-animate.animated {
        animation-play-state: running;
    }

    /* Staggered Animation Delays */
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


    /* Card Hover Effects */
    #features .card-hover:not(.bg-transparent),
    #key-modules .card-hover:not(.bg-transparent),
    #schools .card-hover {
        transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
    }
    #features .card-hover:not(.bg-transparent):hover,
    #key-modules .card-hover:not(.bg-transparent):hover,
    #schools .card-hover:hover {
        transform: translateY(-6px); /* Slightly more lift */
        box-shadow: 0 0.5rem 1.8rem -0.4rem rgba(11, 15, 25, 0.1), 0 0.3rem 1rem -0.15rem rgba(11, 15, 25, 0.07) !important;
    }
     #schools .card img {
        transition: transform 0.3s ease-in-out;
    }
     #schools .card:hover img {
        transform: scale(1.1);
    }
    /* Feature Icon Hover/Animation */
     #features .card-icon-wrapper, #key-modules .card-icon-wrapper {
        transition: transform 0.3s ease-in-out;
     }
      #features .card:hover .card-icon-wrapper, #key-modules .card:hover .card-icon-wrapper {
         transform: scale(1.1) rotate(-5deg);
     }
      #features .card.section-animate.animated .card-icon-wrapper,
      #key-modules .card.section-animate.animated .card-icon-wrapper {
         animation: subtleBob 1.5s ease-in-out 1 forwards;
         animation-delay: inherit; /* Inherit delay from parent card */
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
        display: inline; /* Use inline for flow within text */
        font-weight: 800; /* Make it slightly bolder */
    }
     .hero-text-gradient { /* Specific for hero if needed */
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
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    /* Ensure parallax images scale nicely */
     .tilt-3d {
        box-shadow: 0 1rem 3rem rgba(0,0,0,0.15);
        border-radius: .75rem; /* Optional: adds rounded corners */
        overflow: hidden; /* Ensures inner layers don't spill out */
    }
     .tilt-3d img {
        display: block;
        width: 100%;
        height: auto;
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
        font-size: 2.5rem; /* Make icons slightly larger */
        color: var(--si-primary);
    }

</style>

    <section class="position-relative overflow-hidden hero-bg-animation" id="home">
        <div class="position-relative bg-dark zindex-4 pt-lg-3 pt-xl-5" style="background: transparent !important;"> {{-- Make container transparent --}}

            <div class="container zindex-5 pt-5">
                <div class="row justify-content-center text-center pt-4 pb-sm-2 py-lg-5">
                    <div class="col-xl-9 col-lg-10 col-md-11 py-5">
                        <h1 class="display-3 text-light pt-sm-2 pb-1 pb-sm-3 mb-3 section-animate" style="animation-delay: 0.1s;">
                            Tired of School Admin Chaos? <br> Meet <span class="hero-text-gradient">{{ Utils::app_name() }}</span> – Your All-in-One Solution.
                        </h1>
                        <p class="fs-lg text-light opacity-80 pb-2 pb-sm-0 mb-4 mb-sm-5 section-animate" style="animation-delay: 0.3s;"> {{-- Increased opacity slightly --}}
                            Reclaim your time and reduce stress. <span class="app-name-gradient">{{ Utils::app_name() }}</span> streamlines everything – from admissions and fees to academics and parent communication – specifically designed for the needs of Ugandan schools. <span class="fw-medium">Focus on what truly matters: education.</span>
                        </p>
                        <div class="section-animate" style="animation-delay: 0.5s;">
                            <a href="#get_started" data-scroll class="btn btn-primary shadow-primary btn-lg pulse-animation">See How It Works (Request Demo)</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex position-absolute top-100 start-0 w-100 overflow-hidden mt-n4 mt-sm-n1"
                style="color: var(--si-body-bg);"> {{-- Changed color to body-bg for seamless transition --}}
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

            <div class="tilt-3d section-animate" data-tilt data-tilt-full-page-listening data-tilt-max="8" data-tilt-perspective="1800" style="animation-delay: 0.2s;"> {{-- Adjusted tilt params --}}
                <img src="{{ url('silicon/assets/img/landing/saas-2/hero/layer01.png') }}" alt="Tusome Dashboard">
                <div class="tilt-3d-inner position-absolute top-0 start-0 w-100 h-100">
                    <img src="{{ url('silicon/assets/img/landing/saas-2/hero/layer02.png') }}" alt="Tusome UI Elements">
                </div>
            </div>
        </div>
        {{-- Removed the overlay div for a cleaner look with the gradient bg --}}
    </section>


    <section class="position-relative py-5 mt-n5" id="features"> {{-- Added negative margin top --}}
        <div class="container position-relative zindex-5 pb-md-4 pt-md-2 pt-lg-3 pb-lg-5">
            <div class="row justify-content-center text-center pb-3 mb-sm-2 mb-lg-3 section-animate">
                <div class="col-xl-8 col-lg-9 col-md-10">
                    <h2 class="h1 mb-lg-4">Stop Managing, Start Leading Your School</h2>
                    <p class="fs-lg text-muted mb-0">
                       Is your day consumed by repetitive tasks? <span class="app-name-gradient">{{ Utils::app_name() }}</span> automates the tedious work, giving you the data and tools to make informed decisions and drive your school forward.
                    </p>
                </div>
            </div>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-0 pb-xl-3">

                <div class="col position-relative">
                    <div class="card border-0 bg-transparent rounded-0 p-md-1 p-xl-3 card-hover section-animate">
                         <div class="card-icon-wrapper d-table bg-secondary rounded-3 p-3 mx-auto mt-3 mt-md-4">
                            <img src="{{ url('silicon/assets/img/landing/saas-2/features/6.png') }}" width="40" alt="Comments Icon"> {{-- Original icon --}}
                        </div>
                        <div class="card-body text-center">
                            <h3 class="h5 pb-1 mb-2">Effortless Admissions</h3>
                            <p class="mb-0">
                                Buried under application forms? <span class="app-name-gradient">{{ Utils::app_name() }}</span> digitizes your entire process, from <span class="fw-medium">online applications to easy batch imports</span>. Onboard students faster and without the usual chaos.
                            </p>
                        </div>
                    </div>
                    <hr class="position-absolute top-0 end-0 w-1 h-100 d-none d-sm-block">
                    <hr class="position-absolute top-100 start-0 w-100 d-none d-sm-block">
                </div>

                <div class="col position-relative">
                    <div class="card border-0 bg-transparent rounded-0 p-md-1 p-xl-3 card-hover section-animate">
                         <div class="card-icon-wrapper d-table bg-secondary rounded-3 p-3 mx-auto mt-3 mt-md-4">
                            <img src="{{ url('silicon/assets/img/landing/saas-2/features/5.png') }}" width="40" alt="Analytics Icon"> {{-- Original icon --}}
                        </div>
                        <div class="card-body text-center">
                            <h3 class="h5 pb-1 mb-2">Stress-Free Fee Management</h3>
                             <p class="mb-0">
                                Chasing payments and reconciling accounts manually? <span class="app-name-gradient">{{ Utils::app_name() }}</span> offers <span class="fw-medium">automated billing, real-time tracking, School Pay integration</span>, and clear financial reporting. Get paid faster, with less effort.
                            </p>
                        </div>
                    </div>
                    <hr class="position-absolute top-0 end-0 w-1 h-100 d-none d-md-block">
                    <hr class="position-absolute top-100 start-0 w-100 d-none d-sm-block">
                </div>

                <div class="col position-relative">
                    <div class="card border-0 bg-transparent rounded-0 p-md-1 p-xl-3 card-hover section-animate">
                         <div class="card-icon-wrapper d-table bg-secondary rounded-3 p-3 mx-auto mt-3 mt-md-4">
                            <img src="{{ url('silicon/assets/img/landing/saas-2/features/4.png') }}" width="40" alt="Group Icon"> {{-- Original icon --}}
                        </div>
                        <div class="card-body text-center">
                            <h3 class="h5 pb-1 mb-2">Simplified Academics & Reports</h3>
                             <p class="mb-0">
                                Spending weekends on report cards? <span class="app-name-gradient">{{ Utils::app_name() }}</span> handles <span class="fw-medium">mark entry (both curricula), grading, and report generation</span> seamlessly. Keep parents informed and teachers focused on teaching.
                            </p>
                        </div>
                    </div>
                    <hr class="position-absolute top-0 end-0 w-1 h-100 d-none d-sm-block d-md-none">
                    <hr class="position-absolute top-100 start-0 w-100 d-none d-sm-block">
                </div>

                 <div class="col position-relative">
                    <div class="card border-0 bg-transparent rounded-0 p-md-1 p-xl-3 card-hover section-animate">
                        <div class="card-icon-wrapper d-table bg-secondary rounded-3 p-3 mx-auto mt-3 mt-md-4">
                             <img src="{{ url('silicon/assets/img/landing/saas-2/features/3.png') }}" width="40" alt="Mobile App Icon"> {{-- Original icon --}}
                        </div>
                        <div class="card-body text-center">
                            <h3 class="h5 pb-1 mb-2">Stay Connected, Anywhere</h3>
                             <p class="mb-0">
                                Need instant school access? Our <span class="fw-medium">dedicated iOS & Android apps</span> give parents, teachers, and admins secure access to records, fees, results, and communication tools right from their pocket.
                            </p>
                        </div>
                    </div>
                    <hr class="position-absolute top-0 end-0 w-1 h-100 d-none d-md-block">
                     <hr class="position-absolute top-100 start-0 w-100 d-none d-md-block">
                </div>

                <div class="col position-relative">
                    <div class="card border-0 bg-transparent rounded-0 p-md-1 p-xl-3 card-hover section-animate">
                        <div class="card-icon-wrapper d-table bg-secondary rounded-3 p-3 mx-auto mt-3 mt-md-4">
                            <img src="{{ url('silicon/assets/img/landing/saas-2/features/2.png') }}" width="40" alt="Communication Icon"> {{-- Original icon --}}
                        </div>
                        <div class="card-body text-center">
                             <h3 class="h5 pb-1 mb-2">Instant Communication</h3>
                             <p class="mb-0">
                                Struggling to reach everyone quickly? Send targeted <span class="fw-medium">bulk SMS, important announcements, and automated alerts</span> for fees or events. Keep your entire school community informed effortlessly.
                            </p>
                        </div>
                    </div>
                     <hr class="position-absolute top-0 end-0 w-1 h-100 d-none d-sm-block d-md-none">
                     <hr class="position-absolute top-100 start-0 w-100 d-none d-md-block">
                 </div>

                 <div class="col position-relative">
                     <div class="card border-0 bg-transparent rounded-0 p-md-1 p-xl-3 card-hover section-animate">
                         <div class="card-icon-wrapper d-table bg-secondary rounded-3 p-3 mx-auto mt-3 mt-md-4">
                            <img src="{{ url('silicon/assets/img/landing/saas-2/features/1.png') }}" width="40" alt="Comprehensive Icon"> {{-- Original icon --}}
                         </div>
                         <div class="card-body text-center">
                            <h3 class="h5 pb-1 mb-2">Beyond the Basics</h3>
                            <p class="mb-0">
                                Need more than just core management? <span class="app-name-gradient">{{ Utils::app_name() }}</span> is a truly <span class="fw-medium">complete platform</span>, covering hostels, transport, library, inventory, sickbay, visitors, and much more.
                            </p>
                        </div>
                     </div>
                 </div>
             </div>
         </div>
         <div class="position-absolute top-0 start-0 w-100 h-100" style="background-color: rgba(255,255,255,.05);"></div>
     </section>

    <section class="container py-5 my-md-3 my-lg-4" id="key-modules">
        <div class="row justify-content-center text-center pb-3 mb-sm-2 mb-lg-3 section-animate">
            <div class="col-xl-8 col-lg-9 col-md-10">
                <h2 class="h1 mb-4">Everything You Need, Integrated</h2>
                <p class="fs-lg text-muted mb-0">
                    <span class="app-name-gradient">{{ Utils::app_name() }}</span> offers dedicated modules to manage every facet of your school efficiently.
                </p>
            </div>
        </div>
        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 g-3 g-sm-4">
             <div class="col">
                 <div class="card card-body card-hover h-100 text-center border-0 shadow-sm p-3 section-animate">
                    <div class="card-icon-wrapper display-4 text-primary mx-auto mb-3"><i class='bx bxs-user-plus'></i></div>
                    <h5 class="fs-sm fw-semibold mb-0">Admissions Management</h5>
                </div>
            </div>
             <div class="col">
                <div class="card card-body card-hover h-100 text-center border-0 shadow-sm p-3 section-animate">
                    <div class="card-icon-wrapper display-4 text-primary mx-auto mb-3"><i class='bx bxs-graduation'></i></div>
                     <h5 class="fs-sm fw-semibold mb-0">Student Records Center</h5>
                 </div>
             </div>
             <div class="col">
                <div class="card card-body card-hover h-100 text-center border-0 shadow-sm p-3 section-animate">
                    <div class="card-icon-wrapper display-4 text-primary mx-auto mb-3"><i class='bx bx-money-withdraw'></i></div>
                     <h5 class="fs-sm fw-semibold mb-0">Fees & Billing System</h5>
                 </div>
             </div>
             <div class="col">
                <div class="card card-body card-hover h-100 text-center border-0 shadow-sm p-3 section-animate">
                     <div class="card-icon-wrapper display-4 text-primary mx-auto mb-3"><i class='bx bxs-report'></i></div>
                     <h5 class="fs-sm fw-semibold mb-0">Exams & Report Cards</h5>
                 </div>
             </div>
             <div class="col">
                <div class="card card-body card-hover h-100 text-center border-0 shadow-sm p-3 section-animate">
                    <div class="card-icon-wrapper display-4 text-primary mx-auto mb-3"><i class='bx bx-calculator'></i></div>
                     <h5 class="fs-sm fw-semibold mb-0">School Finance & Budget</h5>
                 </div>
             </div>
            <div class="col">
                <div class="card card-body card-hover h-100 text-center border-0 shadow-sm p-3 section-animate">
                    <div class="card-icon-wrapper display-4 text-primary mx-auto mb-3"><i class='bx bxs-message-dots'></i></div>
                    <h5 class="fs-sm fw-semibold mb-0">Communication (SMS/App)</h5>
                </div>
            </div>
            <div class="col">
                <div class="card card-body card-hover h-100 text-center border-0 shadow-sm p-3 section-animate">
                    <div class="card-icon-wrapper display-4 text-primary mx-auto mb-3"><i class='bx bxs-bus'></i></div>
                    <h5 class="fs-sm fw-semibold mb-0">Transport Management</h5>
                </div>
            </div>
            <div class="col">
                <div class="card card-body card-hover h-100 text-center border-0 shadow-sm p-3 section-animate">
                    <div class="card-icon-wrapper display-4 text-primary mx-auto mb-3"><i class='bx bxs-bank'></i></div>
                     <h5 class="fs-sm fw-semibold mb-0">Inventory & Assets</h5>
                 </div>
            </div>
            </div>
    </section>


    <section class="d-flex w-100 position-relative overflow-hidden">
         <div class="position-absolute top-0 start-0 w-50 h-100 bg-dark"></div>
         <div class="position-absolute top-0 end-0 w-50 h-100" style="background-color: #f3f6ff;"></div>
         <div class="container position-relative zindex-5 py-5">
             <div class="row justify-content-center text-center pb-3 mb-sm-2 mb-lg-3 section-animate">
                <div class="col-xl-6 col-lg-7 col-md-9">
                     <h2 class="h1 mb-lg-4"><span class="text-light">Light</span> or <span class="text-primary">Dark</span>? You Choose.</h2>
                    <p class="fs-lg text-muted mb-0">
                        Work comfortably, day or night. <span class="app-name-gradient">{{ Utils::app_name() }}</span> adapts to your preference.
                     </p>
                 </div>
            </div>
         </div>

         <div class="position-relative flex-xl-shrink-0 zindex-5 start-50 translate-middle-x mt-n5 mt-lg-0" style="max-width: 1920px;">
            <div class="mx-md-n5 mx-xl-0">
                <div class="mx-n4 mx-sm-n5 mx-xl-0">
                    <div class="mx-n5 mx-xl-0">
                        <img-comparison-slider class="mx-n5 mx-xl-0 rounded-3 shadow-lg section-animate" style="animation-delay: 0.2s;">
                            <img slot="first" src="{{ url('silicon/assets/img/landing/saas-2/dark-mode.jpg') }}" alt="Tusome Dark Mode">
                            <img slot="second" src="{{ url('silicon/assets/img/landing/saas-2/light-mode.jpg') }}" alt="Tusome Light Mode">
                            <div slot="handle" style="width: 40px;"> {{-- Slightly larger handle --}}
                                 <svg class="text-primary shadow-primary rounded-circle" width="40" height="40" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="18" cy="18" r="18" fill="currentColor"/>
                                    <path d="M22.2 17.2H13.9V13.9L9.7 18L13.9 22.1V18.8H22.2V22.1L26.4 18L22.2 13.9V17.2Z" fill="white"/>
                                </svg>
                            </div>
                        </img-comparison-slider>
                    </div>
                </div>
            </div>
        </div>

    </section>


    <section class="container py-5 my-2 my-md-4 my-lg-5" id="testimonials">
        <div class="row pt-2 py-xl-3 section-animate">
            <div class="col-lg-3 col-md-4">
                <h2 class="h1 text-center text-md-start mx-auto mx-md-0 pt-md-2" style="max-width: 300px;">
                    Don't Just Take Our Word For It...
                </h2>

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

                         <div class="swiper-slide h-auto pt-4">
                             <figure class="d-flex flex-column h-100 px-2 px-sm-0 mb-0 mx-2">
                                 <div class="card h-100 position-relative border-0 shadow-sm pt-4">
                                     <span class="btn btn-icon btn-primary shadow-primary pe-none position-absolute top-0 start-0 translate-middle-y ms-4">
                                        <i class="bx bxs-quote-left"></i>
                                    </span>
                                     <blockquote class="card-body pb-3 mb-0">
                                         <p class="mb-0">"<span class="app-name-gradient">{{ Utils::app_name() }}</span> is a lifesaver! Managing admissions and fees used to take days, now it takes minutes. The support team is also incredibly responsive."</p>
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
                                     <img src="{{ url('silicon/assets/img/avatar/1.jpg') }}" width="48" class="rounded-circle" alt="Robert Fox">
                                     <div class="ps-3">
                                         <h6 class="fs-sm fw-semibold mb-0">– School Bursar</h6>
                                         <span class="fs-xs text-muted">BRIGHT FUTURE SS - Kaliro</span>
                                     </div>
                                 </figcaption>
                             </figure>
                         </div>

                         <div class="swiper-slide h-auto pt-4">
                             <figure class="d-flex flex-column h-100 px-2 px-sm-0 mb-0 mx-2">
                                 <div class="card h-100 position-relative border-0 shadow-sm pt-4">
                                     <span class="btn btn-icon btn-primary shadow-primary pe-none position-absolute top-0 start-0 translate-middle-y ms-4">
                                        <i class="bx bxs-quote-left"></i>
                                     </span>
                                     <blockquote class="card-body pb-3 mb-0">
                                         <p class="mb-0">"Communication with parents is so much easier now. Sending updates via SMS and the app keeps everyone informed instantly. <span class="app-name-gradient">{{ Utils::app_name() }}</span> really understands school needs."</p>
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
                                     <img src="{{ url('silicon/assets/img/avatar/2.jpg') }}" width="48" class="rounded-circle" alt="Annette Black">
                                     <div class="ps-3">
                                         <h6 class="fs-sm fw-semibold mb-0">– Head Teacher</h6>
                                         <span class="fs-xs text-muted">LUKMAN PRIMARY SCHOOL</span>
                                     </div>
                                </figcaption>
                             </figure>
                         </div>

                         <div class="swiper-slide h-auto pt-4">
                            <figure class="d-flex flex-column h-100 px-2 px-sm-0 mb-0 mx-2">
                                 <div class="card h-100 position-relative border-0 shadow-sm pt-4">
                                     <span class="btn btn-icon btn-primary shadow-primary pe-none position-absolute top-0 start-0 translate-middle-y ms-4">
                                        <i class="bx bxs-quote-left"></i>
                                    </span>
                                     <blockquote class="card-body pb-3 mb-0">
                                         <p class="mb-0">"The financial module is fantastic. Tracking school fees and managing School Pay transactions is seamless. <span class="app-name-gradient">{{ Utils::app_name() }}</span> provides great value."</p>
                                     </blockquote>
                                     <div class="card-footer border-0 text-nowrap pt-0">
                                         <i class="bx bxs-star text-warning"></i>
                                         <i class="bx bxs-star text-warning"></i>
                                         <i class="bx bxs-star text-warning"></i>
                                         <i class="bx bxs-star text-warning"></i>
                                         <i class="bx bxs-star-half text-warning"></i>
                                     </div>
                                 </div>
                                 <figcaption class="d-flex align-items-center ps-4 pt-4">
                                     <img src="{{ url('silicon/assets/img/avatar/3.jpg') }}" width="48" class="rounded-circle" alt="Jerome Bell">
                                     <div class="ps-3">
                                         <h6 class="fs-sm fw-semibold mb-0">– Finance Manager</h6>
                                         <span class="fs-xs text-muted">Kira Junior School - Kito</span>
                                     </div>
                                 </figcaption>
                             </figure>
                        </div>

                        <div class="swiper-slide h-auto pt-4">
                            <figure class="d-flex flex-column h-100 px-2 px-sm-0 mb-0 mx-2">
                                <div class="card h-100 position-relative border-0 shadow-sm pt-4">
                                    <span class="btn btn-icon btn-primary shadow-primary pe-none position-absolute top-0 start-0 translate-middle-y ms-4">
                                        <i class="bx bxs-quote-left"></i>
                                    </span>
                                     <blockquote class="card-body pb-3 mb-0">
                                        <p class="mb-0">"As a parent, the <span class="app-name-gradient">{{ Utils::app_name() }}</span> app is wonderful. I can easily check my child's performance, fee status, and get school news all in one place. Highly recommended!"</p>
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
                                     <img src="{{ url('silicon/assets/img/avatar/4.jpg') }}" width="48" class="rounded-circle" alt="Albert Flores">
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


    <section class="container" id="apps">
        <div class="bg-secondary rounded-3 overflow-hidden py-5 px-4 ps-lg-0 pe-md-5 pe-lg-0 section-animate">
            <div class="row align-items-center py-sm-2">

                <div class="col-md-7 col-lg-6 offset-xl-1">
                     <div class="position-relative mx-auto mb-5 m-md-0" style="max-width: 526px;">
                        <img src="{{ url('silicon/assets/img/landing/saas-2/device.png') }}" class="d-block" alt="Device showing Tusome App">
                        <div class="rellax d-block position-absolute top-0 end-0 w-100 mt-md-4 me-md-n5"
                             data-rellax-percentage="0.5" data-rellax-vertical-scroll-axis="xy"
                             data-rellax-horizontal-speed="0.6" data-rellax-vertical-speed="-0.6"
                            data-disable-parallax-down="md">
                            <img src="{{ url('silicon/assets/img/landing/saas-2/screen.png') }}" alt="Tusome App Screen">
                        </div>
                     </div>
                 </div>

                 <div class="col-xl-4 col-md-5 mt-n2 mt-md-0">
                     <h2 class="h1 text-center text-md-start mb-4 mb-lg-5">Manage Your School <br>On The Go!</h2>
                     <div class="row">
                         <div class="col-sm-6 col-md-12 pb-4 pb-sm-0">
                            <div class="row row-cols-1 row-cols-lg-2 align-items-end text-center text-md-start pb-md-4 mb-lg-3">
                                <div class="col mb-3 mb-lg-0">
                                    <p class="text-muted mb-1">App Store</p>
                                    <div class="text-nowrap fs-sm pb-lg-1 mb-2">
                                         <i class="bx bxs-star text-warning"></i>
                                         <i class="bx bxs-star text-warning"></i>
                                         <i class="bx bxs-star text-warning"></i>
                                         <i class="bx bxs-star text-warning"></i>
                                         <i class="bx bxs-star-half text-warning"></i>
                                    </div>
                                    <h3 class="h4 mb-1">iOS App</h3>
                                    <p class="fs-sm mb-0">Available for iPhone & iPad</p>
                                 </div>
                                <div class="col d-lg-flex justify-content-end">
                                    <a href="https://apps.apple.com/us/app/school-dynamics/id6469381244" target="_blank"
                                        class="btn btn-dark btn-lg px-3 py-2 card-hover">
                                         <img src="{{ url('silicon/assets/img/market/appstore-light.svg') }}" class="light-mode-img" width="124" alt="App Store">
                                        <img src="{{ url('silicon/assets/img/market/appstore-dark.svg') }}" class="dark-mode-img" width="124" alt="App Store">
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
                                    <h3 class="h4 mb-1">Android App</h3>
                                    <p class="fs-sm mb-0">Get it on Google Play</p>
                                </div>
                                 <div class="col d-lg-flex justify-content-end">
                                    <a href="https://play.google.com/store/apps/details?id=schooldynamics.ug" target="_blank" class="btn btn-dark btn-lg px-3 py-2 card-hover">
                                        <img src="{{ url('silicon/assets/img/market/googleplay-light.svg') }}" class="light-mode-img" width="139" alt="Google Play">
                                         <img src="{{ url('silicon/assets/img/market/googleplay-dark.svg') }}" class="dark-mode-img" width="139" alt="Google Play">
                                     </a>
                                 </div>
                             </div>
                         </div>
                    </div>
                 </div>
             </div>
         </div>
     </section>


    <section class="container mt-n1 mt-md-0 py-5" id="schools">
        <div class="row justify-content-center text-center pt-md-3 pb-4 py-lg-5 mb-1 section-animate">
            <div class="col-xl-8 col-lg-9 col-md-10">
                <h2 class="h1 mb-lg-4">Join a Growing Community of Innovative Schools</h2>
                <p class="fs-lg text-muted mb-0">More than <span class="fw-semibold text-dark">76 Ugandan schools</span> rely on <span class="app-name-gradient">{{ Utils::app_name() }}</span> daily to streamline operations and enhance their educational environment.</p>
            </div>
        </div>
        {{-- Simpler card grid for logos/names --}}
        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 g-4 justify-content-center text-center section-animate" style="animation-delay: 0.2s;">
             <div class="col">
                 <div class="card card-body card-hover bg-light py-4 h-100 border-0 shadow-sm">
                    <img src="{{ url('silicon/assets/img/avatar/1.jpg') }}" class="d-block mx-auto rounded-circle mb-3" width="56" alt="Bright Future SS Logo">
                     <h6 class="fs-sm text-body mb-0">Bright Future SS, Kaliro</h6>
                </div>
            </div>
             <div class="col">
                 <div class="card card-body card-hover bg-light py-4 h-100 border-0 shadow-sm">
                    <img src="{{ url('silicon/assets/img/avatar/2.jpg') }}" class="d-block mx-auto rounded-circle mb-3" width="56" alt="Al-Bushra Islamic Junior School Logo">
                     <h6 class="fs-sm text-body mb-0">Al-Bushra Islamic Junior</h6>
                 </div>
             </div>
             <div class="col">
                 <div class="card card-body card-hover bg-light py-4 h-100 border-0 shadow-sm">
                    <img src="{{ url('silicon/assets/img/avatar/3.jpg') }}" class="d-block mx-auto rounded-circle mb-3" width="56" alt="Kira Junior School Logo">
                    <h6 class="fs-sm text-body mb-0">Kira Junior School, Kito</h6>
                 </div>
             </div>
             <div class="col">
                 <div class="card card-body card-hover bg-light py-4 h-100 border-0 shadow-sm">
                    <img src="{{ url('silicon/assets/img/avatar/4.jpg') }}" class="d-block mx-auto rounded-circle mb-3" width="56" alt="Lukman Primary School Logo">
                     <h6 class="fs-sm text-body mb-0">Lukman Primary, Entebbe</h6>
                 </div>
            </div>
             <div class="col">
                 <div class="card card-body card-hover bg-light py-4 h-100 border-0 shadow-sm">
                    <img src="{{ url('silicon/assets/img/avatar/5.jpg') }}" class="d-block mx-auto rounded-circle mb-3" width="56" alt="Bilal Islamic SS Logo">
                     <h6 class="fs-sm text-body mb-0">Bilal Islamic SS, Bwaise</h6>
                 </div>
            </div>
              <div class="col">
                <div class="card card-body card-hover bg-light py-4 h-100 border-0 shadow-sm">
                    <img src="{{ url('silicon/assets/img/avatar/6.jpg') }}" class="d-block mx-auto rounded-circle mb-3" width="56" alt="Anwar Muslim SS Logo">
                     <h6 class="fs-sm text-body mb-0">Anwar Muslim SS, Mpererwe</h6>
                 </div>
            </div>
             <div class="col">
                 <div class="card card-body card-hover bg-light py-4 h-100 border-0 shadow-sm">
                     <img src="{{ url('silicon/assets/img/avatar/7.jpg') }}" class="d-block mx-auto rounded-circle mb-3" width="56" alt="Queen of Peace School Logo">
                     <h6 class="fs-sm text-body mb-0">Queen of Peace, Kyegegwa</h6>
                 </div>
            </div>
            <div class="col">
                 <div class="card card-body card-hover bg-light py-4 h-100 border-0 shadow-sm">
                    <img src="{{ url('silicon/assets/img/avatar/8.jpg') }}" class="d-block mx-auto rounded-circle mb-3" width="56" alt="Tasneem Junior School Logo">
                     <h6 class="fs-sm text-body mb-0">Tasneem Junior, Nsanji</h6>
                 </div>
             </div>
            {{-- Adding a few more placeholders for visual balance if needed --}}
             <div class="col d-none d-lg-block">
                <div class="card card-body bg-transparent h-100 border-0 p-3">
                    <h6 class="fs-sm text-muted text-center pt-4 mt-1 mb-0">...and many more!</h6>
                 </div>
             </div>
            <div class="col d-none d-lg-block">
                {{-- Empty for grid spacing --}}
            </div>
        </div>
     </section>


    <section class="bg-gradient-primary-translucent py-5" id="get_started"> {{-- Changed BG slightly --}}
        <div class="container text-center py-1 py-md-4 py-lg-5 section-animate">
            <h2 class="h1 mb-4">Ready to See <span class="app-name-gradient">{{ Utils::app_name() }}</span> in Action?</h2>
            <p class="lead pb-3 mb-3">Let us show you how <span class="app-name-gradient">{{ Utils::app_name() }}</span> can transform your school's administration. <br class="d-none d-md-block">Schedule a personalized demo today – no obligation, no hassle.</p>
            <a href="https://forms.gle/NP8RXx7YcpPbfi6b8" target="_blank" class="btn btn-primary shadow-primary btn-lg mb-1 pulse-animation">Request Your Free Demo Now</a>
        </div>
    </section>

{{-- Add Scroll-behavior and Animation Trigger script --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Smooth scroll for anchor links
        const scrollLinks = document.querySelectorAll('a[data-scroll]');
        scrollLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    // Adjust offset based on whether navbar is stuck (you might need a class for the stuck state)
                    const navbar = document.querySelector('.navbar-sticky'); // Use your actual sticky navbar class
                    const offset = navbar ? navbar.offsetHeight : 80; // Default offset or navbar height
                    window.scrollTo({
                        top: targetElement.offsetTop - offset,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Intersection Observer for animations
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.15 // Trigger a bit earlier
        };

        const observerCallback = (entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated'); // Add class to trigger animation
                    observer.unobserve(entry.target); // Stop observing once animated
                }
            });
        };

        const observer = new IntersectionObserver(observerCallback, observerOptions);
        const animatedElements = document.querySelectorAll('.section-animate');
        animatedElements.forEach(el => {
             observer.observe(el);
        });
    });
</script>
@endsection