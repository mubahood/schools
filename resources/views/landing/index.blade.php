@extends('layouts.base-layout')
@section('content')
    <!-- Hero -->
    <section class="position-relative overflow-hidden">
        <div class="position-relative bg-dark zindex-4 pt-lg-3 pt-xl-5">

            <!-- Text -->
            <div class="container zindex-5 pt-5">
                <div class="row justify-content-center text-center pt-4 pb-sm-2 py-lg-5">
                    <div class="col-xl-8 col-lg-9 col-md-10 py-5">
                        <h1 class="display-4 text-light pt-sm-2 pb-1 pb-sm-3 mb-3">Task Management Assistant You
                            Gonna Love</h1>
                        <p class="fs-lg text-light opacity-70 pb-2 pb-sm-0 mb-4 mb-sm-5">We offer you a new
                            generation of task and project management system. Plan, manage and track all your tasks
                            in one flexible software!</p>
                        <a href="#" class="btn btn-primary shadow-primary btn-lg">Get early access</a>
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
    <section class="position-relative py-5">
        <div class="container position-relative zindex-5 pb-md-4 pt-md-2 pt-lg-3 pb-lg-5">
            <div class="row justify-content-center text-center pb-3 mb-sm-2 mb-lg-3">
                <div class="col-xl-6 col-lg-7 col-md-9">
                    <h2 class="h1 mb-lg-4">What Do You Get with Our Tool?</h2>
                    <p class="fs-lg text-muted mb-0">Make sure all your tasks are organized so you can set the
                        priorities and focus on important.</p>
                </div>
            </div>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-0 pb-xl-3">

                <!-- Item -->
                <div class="col position-relative">
                    <div class="card border-0 bg-transparent rounded-0 p-md-1 p-xl-3">
                        <div class="d-table bg-secondary rounded-3 p-3 mx-auto mt-3 mt-md-4">
                            <img src="silicon/assets/img/landing/saas-2/features/comments.svg" width="40"
                                alt="Comments">
                        </div>
                        <div class="card-body text-center">
                            <h3 class="h5 pb-1 mb-2">Comments on Tasks</h3>
                            <p class="mb-0">Id mollis consectetur congue egestas egestas suspendisse blandit
                                justo.</p>
                        </div>
                    </div>
                    <hr class="position-absolute top-0 end-0 w-1 h-100 d-none d-sm-block">
                    <hr class="position-absolute top-100 start-0 w-100 d-none d-sm-block">
                </div>

                <!-- Item -->
                <div class="col position-relative">
                    <div class="card border-0 bg-transparent rounded-0 p-md-1 p-xl-3">
                        <div class="d-table bg-secondary rounded-3 p-3 mx-auto mt-3 mt-md-4">
                            <img src="silicon/assets/img/landing/saas-2/features/analytics.svg" width="40"
                                alt="Analytics">
                        </div>
                        <div class="card-body text-center">
                            <h3 class="h5 pb-1 mb-2">Tasks Analytics</h3>
                            <p class="mb-0">Non imperdiet facilisis nulla tellus Morbi scelerisque eget
                                adipiscing vulputate.</p>
                        </div>
                    </div>
                    <hr class="position-absolute top-0 end-0 w-1 h-100 d-none d-md-block">
                    <hr class="position-absolute top-100 start-0 w-100 d-none d-sm-block">
                </div>

                <!-- Item -->
                <div class="col position-relative">
                    <div class="card border-0 bg-transparent rounded-0 p-md-1 p-xl-3">
                        <div class="d-table bg-secondary rounded-3 p-3 mx-auto mt-3 mt-md-4">
                            <img src="silicon/assets/img/landing/saas-2/features/group.svg" width="40" alt="Group">
                        </div>
                        <div class="card-body text-center">
                            <h3 class="h5 pb-1 mb-2">Multiple Assignees</h3>
                            <p class="mb-0">A elementum, imperdiet enim, pretium etiam facilisi in aenean quam
                                mauris.</p>
                        </div>
                    </div>
                    <hr class="position-absolute top-0 end-0 w-1 h-100 d-none d-sm-block d-md-none">
                    <hr class="position-absolute top-100 start-0 w-100 d-none d-sm-block">
                </div>

                <!-- Item -->
                <div class="col position-relative">
                    <div class="card border-0 bg-transparent rounded-0 p-md-1 p-xl-3">
                        <div class="d-table bg-secondary rounded-3 p-3 mx-auto mt-3 mt-md-4">
                            <img src="silicon/assets/img/landing/saas-2/features/notifications.svg" width="40"
                                alt="Notifications">
                        </div>
                        <div class="card-body text-center">
                            <h3 class="h5 pb-1 mb-2">Notifications</h3>
                            <p class="mb-0">Diam, suspendisse velit cras ac. Lobortis diam volutpat, eget
                                pellentesque viverra.</p>
                        </div>
                    </div>
                    <hr class="position-absolute top-0 end-0 w-1 h-100 d-none d-md-block">
                    <hr class="position-absolute top-100 start-0 w-100 d-none d-sm-block d-md-none">
                </div>

                <!-- Item -->
                <div class="col position-relative">
                    <div class="card border-0 bg-transparent rounded-0 p-md-1 p-xl-3">
                        <div class="d-table bg-secondary rounded-3 p-3 mx-auto mt-3 mt-md-4">
                            <img src="silicon/assets/img/landing/saas-2/features/tasks.svg" width="40" alt="Tasks">
                        </div>
                        <div class="card-body text-center">
                            <h3 class="h5 pb-1 mb-2">Sections &amp; Subtasks</h3>
                            <p class="mb-0">Mi feugiat hac id in. Sit elit placerat lacus nibh lorem ridiculus
                                lectus.</p>
                        </div>
                    </div>
                    <hr class="position-absolute top-0 end-0 w-1 h-100 d-none d-sm-block">
                </div>

                <!-- Item -->
                <div class="col position-relative">
                    <div class="card border-0 bg-transparent rounded-0 p-md-1 p-xl-3">
                        <div class="d-table bg-secondary rounded-3 p-3 mx-auto mt-3 mt-md-4">
                            <img src="silicon/assets/img/landing/saas-2/features/security.svg" width="40"
                                alt="Security">
                        </div>
                        <div class="card-body text-center">
                            <h3 class="h5 pb-1 mb-2">Data Security</h3>
                            <p class="mb-0">Aliquam malesuada neque eget elit nulla vestibulum nunc cras.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="position-absolute top-0 start-0 w-100 h-100" style="background-color: rgba(255,255,255,.05);">
        </div>
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


    <!-- Testimonials -->
    <section class="container py-5 my-2 my-md-4 my-lg-5">
        <div class="row pt-2 py-xl-3">
            <div class="col-lg-3 col-md-4">
                <h2 class="h1 text-center text-md-start mx-auto mx-md-0 pt-md-2" style="max-width: 300px;">What
                    <br class="d-none d-md-inline">People Say <br class="d-none d-md-inline">About App:
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
                                        <p class="mb-0">Id mollis consectetur congue egestas egestas suspendisse
                                            blandit justo. Tellus augue commodo id quis tempus etiam pulvinar at
                                            maecenas.</p>
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
                                    <img src="silicon/assets/img/avatar/16.jpg" width="48" class="rounded-circle"
                                        alt="Robert Fox">
                                    <div class="ps-3">
                                        <h6 class="fs-sm fw-semibold mb-0">Robert Fox</h6>
                                        <span class="fs-xs text-muted">Founder of Lorem Company</span>
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
                                        <p class="mb-0">Phasellus luctus nisi id orci condimentum, at cursus nisl
                                            vestibulum. Orci varius natoque penatibus et magnis dis parturient
                                            montes commodo.</p>
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
                                    <img src="silicon/assets/img/avatar/08.jpg" width="48" class="rounded-circle"
                                        alt="Annette Black">
                                    <div class="ps-3">
                                        <h6 class="fs-sm fw-semibold mb-0">Annette Black</h6>
                                        <span class="fs-xs text-muted">CEO of Ipsum Company</span>
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
                                        <p class="mb-0">Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                                            Mauris ipsum odio, bibendum ornare mi at, efficitur urna.</p>
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
                                    <img src="silicon/assets/img/avatar/13.jpg" width="48" class="rounded-circle"
                                        alt="Jerome Bell">
                                    <div class="ps-3">
                                        <h6 class="fs-sm fw-semibold mb-0">Jerome Bell</h6>
                                        <span class="fs-xs text-muted">Founder of the Agency </span>
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
                                        <p class="mb-0">Pellentesque finibus congue egestas egestas suspendisse
                                            blandit justo. Tellus augue commodo id quis tempus etiam pulvinar at
                                            maecenas.</p>
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
                                    <img src="silicon/assets/img/avatar/09.jpg" width="48" class="rounded-circle"
                                        alt="Albert Flores">
                                    <div class="ps-3">
                                        <h6 class="fs-sm fw-semibold mb-0">Albert Flores</h6>
                                        <span class="fs-xs text-muted">CEO of Dolor Ltd.</span>
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
    <section class="container">
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
                                    <h3 class="h4 mb-1">Editor's Choice</h3>
                                    <p class="mb-0">rating 4.7, 187K+ reviews</p>
                                </div>
                                <div class="col d-lg-flex justify-content-end">
                                    <a href="#" class="btn btn-dark btn-lg px-3 py-2">
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
                                    <h3 class="h4 mb-1">App of the Day</h3>
                                    <p class="mb-0">rating 4.8, 30K+ reviews</p>
                                </div>
                                <div class="col d-lg-flex justify-content-end">
                                    <a href="#" class="btn btn-dark btn-lg px-3 py-2">
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


    <!-- Pricing -->
    <section class="container pt-5">
        <div class="row justify-content-center text-center pt-2 pt-md-4 pt-lg-5 pb-4 pb-lg-5 mb-1">
            <div class="col-xl-6 col-lg-7 col-md-9 col-sm-11 pt-xl-3">
                <h2 class="h1 mb-lg-4">Transparent Pricing for You</h2>
                <p class="fs-lg text-muted mb-0">Varius sed maecenas massa dictum viverra in. Viverra vel in elit,
                    vivamus dui interdum. Nulla congue lobortis amet amet eleifend.</p>
            </div>
        </div>
        <div class="table-responsive-lg">
            <div class="d-flex align-items-center pb-4">

                <!-- Pricing plan -->
                <div class="bg-primary rounded-3 shadow-primary p-4" style="width: 36%; min-width: 18rem;">
                    <div class="card bg-transparent border-light py-3 py-sm-4 py-lg-5">
                        <div class="card-body text-light text-center">
                            <h3 class="text-light mb-2">Team</h3>
                            <div class="fs-lg opacity-70 pb-4 mb-3">Best for small teams</div>
                            <div class="display-5 mb-1">$10</div>
                            <div class="opacity-50 mb-5">per month</div>
                        </div>
                        <div class="card-footer border-0 text-center pt-0 pb-4">
                            <a href="#" class="btn btn-light btn-lg shadow-secondary">Get started now</a>
                        </div>
                    </div>
                </div>
                <div class="row flex-nowrap border rounded-3 rounded-start-0 shadow-sm g-0"
                    style="width: 64%; min-width: 32rem;">

                    <!-- Pricing plan -->
                    <div class="col">
                        <div class="card bg-light h-100 border-0 border-end rounded-0 py-3 py-sm-4 py-lg-5">
                            <div class="card-body text-center">
                                <h3 class="mb-2">Company</h3>
                                <div class="fs-lg pb-4 mb-3">Best for growing teams</div>
                                <div class="display-5 text-dark mb-1">$25</div>
                                <div class="text-muted mb-5">per month</div>
                            </div>
                            <div class="card-footer border-0 text-center pt-0 pb-4">
                                <a href="#" class="btn btn-outline-primary btn-lg">Get started now</a>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing plan -->
                    <div class="col">
                        <div class="card bg-light h-100 border-0 rounded-start-0 py-3 py-sm-4 py-lg-5">
                            <div class="card-body text-center">
                                <h3 class="mb-2">Enterprise</h3>
                                <div class="fs-lg pb-4 mb-3">Best for large teams</div>
                                <div class="display-5 text-dark mb-1">$50</div>
                                <div class="text-muted mb-5">per month</div>
                            </div>
                            <div class="card-footer border-0 text-center pt-0 pb-4">
                                <a href="#" class="btn btn-outline-primary btn-lg">Get started now</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- Integrations -->
    <section class="container mt-n1 mt-md-0 py-5">
        <div class="row justify-content-center text-center pt-md-3 pb-4 py-lg-5 mb-1">
            <div class="col-xl-8 col-lg-9 col-md-10">
                <h2 class="h1 mb-lg-4">Integrate Top Work Tools</h2>
                <p class="fs-lg text-muted mb-0">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin
                    volutpat mollis egestas. Nam luctus facilisis ultrices. Pellentesque volutpat ligula est. Mattis
                    fermentum, at nec lacus.</p>
            </div>
        </div>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-2 g-sm-3 g-lg-4 pb-md-3 pb-lg-5">

            <!-- Item -->
            <div class="col">
                <div class="card card-body card-hover bg-light border-0">
                    <img src="silicon/assets/img/brands/google.svg" class="d-block mb-4" width="56" alt="Google">
                    <p class="mb-0">Lorem magnis pretium sed curabitur nunc facilisi nunc cursus sagittis
                        pretium.</p>
                </div>
            </div>

            <!-- Item -->
            <div class="col">
                <div class="card card-body card-hover bg-light border-0">
                    <img src="silicon/assets/img/brands/zoom.svg" class="d-block mb-4" width="56" alt="Zoom">
                    <p class="mb-0">In eget a mauris quis. Tortor dui tempus quis integer est sit natoque
                        placerat dolor.</p>
                </div>
            </div>

            <!-- Item -->
            <div class="col">
                <div class="card card-body card-hover bg-light border-0">
                    <img src="silicon/assets/img/brands/slack.svg" class="d-block mb-4" width="56" alt="Slack">
                    <p class="mb-0">Id mollis consectetur congue egestas egestas suspendisse blandit justo.</p>
                </div>
            </div>

            <!-- Item -->
            <div class="col">
                <div class="card card-body card-hover bg-light border-0">
                    <img src="silicon/assets/img/brands/gmail.svg" class="d-block mb-4" width="56" alt="Gmail">
                    <p class="mb-0">Rutrum interdum tortor, sed at nulla. A cursus bibendum elit purus cras
                        praesent.</p>
                </div>
            </div>

            <!-- Item -->
            <div class="col">
                <div class="card card-body card-hover bg-light border-0">
                    <img src="silicon/assets/img/brands/trello.svg" class="d-block mb-4" width="56" alt="Trello">
                    <p class="mb-0">Congue pellentesque amet, viverra curabitur quam diam scelerisque fermentum
                        urna.</p>
                </div>
            </div>

            <!-- Item -->
            <div class="col">
                <div class="card card-body card-hover bg-light border-0">
                    <img src="silicon/assets/img/brands/mailchimp.svg" class="d-block mb-4" width="56"
                        alt="Mailchimp">
                    <p class="mb-0">A elementum, imperdiet enim, pretium etiam facilisi in aenean quam mauris
                        integer.</p>
                </div>
            </div>

            <!-- Item -->
            <div class="col">
                <div class="card card-body card-hover bg-light border-0">
                    <img src="silicon/assets/img/brands/dropbox.svg" class="d-block mb-4" width="56" alt="Dropbox">
                    <p class="mb-0">Ut in turpis consequat odio diam lectus elementum. Est faucibus blandit
                        platea.</p>
                </div>
            </div>

            <!-- Item -->
            <div class="col">
                <div class="card card-body card-hover bg-light border-0">
                    <img src="silicon/assets/img/brands/evernote.svg" class="d-block mb-4" width="56"
                        alt="Evernote">
                    <p class="mb-0">Faucibus cursus maecenas lorem cursus nibh. Sociis sit risus id. Sit
                        facilisis dolor arcu.</p>
                </div>
            </div>
        </div>
    </section>


    <!-- CTA -->
    <section class="bg-secondary py-5">
        <div class="container text-center py-1 py-md-4 py-lg-5">
            <h2 class="h1 mb-4">Ready to Get Started?</h2>
            <p class="lead pb-3 mb-3">Organize your tasks with a 14-day free trial</p>
            <a href="#" class="btn btn-primary shadow-primary btn-lg mb-1">Get started</a>
        </div>
    </section>
@endsection
