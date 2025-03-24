@extends('layouts.base-layout')

@section('base-content')
    <!-- Page wrapper for sticky footer -->
    <!-- Wraps everything except footer to push footer to the bottom of the page if there is little content -->
    <main class="page-wrapper">


        @include('layouts.header')


        @yield('main-content')



    </main>


    <!-- Brands (carousel on screens < 1100px) -->
    <section class="container p-0 mt-md-2  ">
        <h2 class="h1 mb-4 ">Partnership</h2>
        <div class="swiper pt-0 mx-n2 text-center"
            data-swiper-options='{
                  "slidesPerView": 2,
                  "pagination": {
                    "el": ".swiper-pagination",
                    "clickable": true
                  },
                  "breakpoints": {
                    "500": {
                      "slidesPerView": 3,
                      "spaceBetween": 8
                    },
                    "650": {
                      "slidesPerView": 4,
                      "spaceBetween": 8
                    },
                    "900": {
                      "slidesPerView": 5,
                      "spaceBetween": 8
                    },
                    "1100": {
                      "slidesPerView": 6,
                      "spaceBetween": 8
                    }
                  }
                }'>
            <div class="swiper-wrapper">

                <!-- Item -->
                <div class="swiper-slide py-3">
                    <a href="#" class="card card-body card-hover px-2 mx-2">
                        <img src="assets/images/nudipu.png" class="d-block mx-auto my-2" width="154" alt="Brand">
                    </a>
                </div>

                <!-- Item -->
                <div class="swiper-slide py-3">
                    <a href="#" class="card card-body card-hover px-2 mx-2">
                        <img src="assets/images/ucc.png" class="d-block mx-auto my-2" width="154" alt="Brand">
                    </a>
                </div>

                <!-- Item -->
                <div class="swiper-slide py-3">
                    <a href="#" class="card card-body card-hover px-2 mx-2">
                        <img src="assets/images/8tech.png" class="d-block mx-auto my-2" width="154" alt="Brand">
                    </a>
                </div>


            </div>

            <!-- Pagination (bullets) -->
            <div class="swiper-pagination position-relative pt-2 mt-4"></div>
        </div>
    </section>



    <!-- Subscription CTA -->
    <section class="py-0 bg-secondary">
        <div class="container py-md-3 py-lg-5">
            <div class="row justify-content-center pt-2">
                <div class="col-xl-8 col-lg-9 col-md-11">
                    <h2 class="h1 d-md-inline-block position-relative mb-md-5 mb-sm-4 text-sm-start text-center">
                        Don't Want to Miss Anything?

                        <!-- Arrow shape -->
                        <svg class="d-md-block d-none position-absolute top-0 ms-4 ps-1" style="left: 100%;"
                            xmlns="http://www.w3.org/2000/svg" width="65" height="68" fill="#6366f1">
                            <path
                                d="M53.9527 51.0012c8.396-10.5668 2.0302-26.0134-11.7481-26.7511-.6899-.0646-1.4612.0015-2.1258.0431.1243 9.0462-4.1714 18.8896-11.5618 21.3814-6.6695 2.2133-10.3337-4.2224-7.5813-9.676 3.2966-6.4755 9.103-11.8504 16.1678-13.8189-.5654-5.6953-3.3436-10.7672-9.485-12.48517C17.2678 6.8204 6.49364 16.3681 4.98841 26.127c-.09276 1.0297-1.68569.9497-1.59293-.0801C3.98732 12.9139 19.7395 2.55212 31.9628 8.5787c4.7253 2.3813 7.2649 7.3963 7.9368 13.067 7.4237-.9311 14.5154 3.3683 18.3422 9.5422 4.3988 7.1623 2.3584 15.1401-2.6322 21.1108-.7826.9653-2.3331-.3572-1.6569-1.2975zM26.7754 32.1845c-1.9411 2.2411-4.076 5.0872-4.3542 8.1764-.3036 2.9829 3.7601 3.0525 5.4905 2.7645 2.1568-.3863 3.7221-2.3164 4.8863-4.0419 2.6228-3.6308 4.3657-9.0752 4.4844-14.2563-4.0808 1.279-7.6514 4.2327-10.507 7.3573zm24.6311 25.592c-.7061-2.9738-1.2243-6.1031-1.1591-9.143.0423-1.242 1.767-1.0805 1.8313.1372.1284 2.435.815 4.8532 1.4764 7.1651l4.1619-1.4098c1.0153-.4586 2.4373-1.5714 3.6544-1.1804.6087.1954.7347.7264.6475 1.3068-.2302 1.3976-2.4683 1.9147-3.5901 2.398-1.8429.7619-3.6293 1.2865-5.5477 1.7298-.6391.1476-1.3233-.3665-1.4746-1.0037z" />
                        </svg>
                    </h2>

                    <!-- Title + checkboxes -->
                    <div class="row gy-4 align-items-center mb-lg-5 mb-4 pb-3">
                        <div class="col-md-3">
                            <h3 class="h5 mb-0 text-sm-start text-center">Sign up for Newsletters</h3>
                        </div>
                        <div class="col-md-9">
                            <div class="row row-cols-sm-3 row-cols-2 gy-2">
                                <div class="col">
                                    <div class="form-check mb-0">
                                        <input type="checkbox" id="s-daily-newsletter" class="form-check-input">
                                        <label for="s-daily-newsletter" class="form-check-label">Daily Newsletter</label>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-check mb-0">
                                        <input type="checkbox" id="s-advertising-updates" class="form-check-input" checked>
                                        <label for="s-advertising-updates" class="form-check-label">Advertising
                                            Updates</label>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-check mb-0">
                                        <input type="checkbox" id="s-week-in-review" class="form-check-input">
                                        <label for="s-week-in-review" class="form-check-label">Week in Review</label>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-check mb-0">
                                        <input type="checkbox" id="s-event-updates" class="form-check-input">
                                        <label for="s-event-updates" class="form-check-label">Event Updates</label>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-check mb-0">
                                        <input type="checkbox" id="s-startups-weekly" class="form-check-input">
                                        <label for="s-startups-weekly" class="form-check-label">Startups Weekly</label>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-check mb-0">
                                        <input id="s-podcasts" type="checkbox" class="form-check-input">
                                        <label for="s-podcasts" class="form-check-label">Podcasts</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Email field -->
                    <form class="d-flex flex-sm-row flex-column mb-3 needs-validation" novalidate>
                        <div class="input-group me-sm-3 mb-sm-0 mb-3">
                            <i
                                class="bx bx-envelope position-absolute start-0 top-50 translate-middle-y ms-3 zindex-5 fs-5 text-muted"></i>
                            <input type="email" class="form-control form-control-lg rounded-3 ps-5"
                                placeholder="Your email" required>
                            <div class="invalid-tooltip position-absolute start-0 top-0 mt-n4">Please provide a valid email
                                address!</div>
                        </div>
                        <button class="btn btn-lg btn-primary">Subscribe *</button>
                    </form>
                    <div class="form-text fs-sm text-sm-start text-center">
                        * Yes, I agree to the <a href="#">terms</a> and <a href="#">privacy policy</a>.
                    </div>
                </div>
            </div>
        </div>
    </section>



    <!-- Footer -->
    <footer class="footer dark-mode bg-dark pt-5 pb-4 pb-lg-5">
        <div class="container pt-lg-4">
            <div class="row pb-5">
                <div class="col-lg-4 col-md-6">
                    <div class="navbar-brand text-dark p-0 me-0 mb-3 mb-lg-4">
                        <img src="assets/img/logo-1.png" width="260" alt="NUDIPU">
                    </div>
                    <p class="fs-sm pb-lg-3 mb-4 lead">
                        <b class="text-dark">ICT for Persons With disabilities</b> will help you, Enhance your
                        Knowledge Management, ICT Adoption, Digital Skills, And Access To E-Services For Persons With
                        Disabilities.
                    </p>
                    <form class="needs-validation" novalidate>
                        <label for="subscr-email" class="form-label">Subscribe to our newsletter</label>
                        <div class="input-group">
                            <input type="email" id="subscr-email" class="form-control rounded-start ps-5"
                                placeholder="Your email" required>
                            <i
                                class="bx bx-envelope fs-lg text-muted position-absolute top-50 start-0 translate-middle-y ms-3 zindex-5"></i>
                            <div class="invalid-tooltip position-absolute top-100 start-0">Please provide a valid email
                                address.</div>
                            <button type="submit" class="btn btn-primary">Subscribe</button>
                        </div>
                    </form>
                </div>
                <div class="col-xl-6 col-lg-7 col-md-5 offset-xl-2 offset-md-1 pt-4 pt-md-1 pt-lg-0">
                    <div id="footer-links" class="row">
                        <div class="col-lg-4">
                            <h6 class="mb-2">
                                <a href="#useful-links" class="d-block text-primary h5 dropdown-toggle  py-0  "
                                    data-bs-toggle="collapse">Quick Links</a>
                            </h6>
                            <div id="useful-links" class="expanded d-lg-block" data-bs-parent="#footer-links">
                                <ul class="nav flex-column pb-lg-1 mb-lg-3">
                                    <li class="nav-item"><a href="{{ url('') }}"
                                            class="nav-link d-inline-block px-0 pt-1 pb-2">Home</a></li>
                                    <li class="nav-item"><a href="{{ url('about-us') }}"
                                            class="nav-link d-inline-block px-0 pt-1 pb-2">Project Overview</a></li>
                                    <li class="nav-item"><a href="{{ url('vision-mission') }}"
                                            class="nav-link d-inline-block px-0 pt-1 pb-2">Project objectives</a></li>
                                    <li class="nav-item"><a href="{{ url('constitution') }}"
                                            class="nav-link d-inline-block px-0 pt-1 pb-2">Project Profile</a></li>
                                    <li class="nav-item"><a href="{{ url('our-team') }}"
                                            class="nav-link d-inline-block px-0 pt-1 pb-2">Our team</a></li>
                                </ul>
                                <ul class="nav flex-column mb-2 mb-lg-0">
                                    <h6 class="mb-2">More</h6>
                                    <li class="nav-item"><a href="{{ url('news') }}"
                                            class="nav-link d-inline-block px-0 pt-1 pb-2">News</a></li>
                                    <li class="nav-item"><a href="{{ url('news') }}"
                                            class="nav-link d-inline-block px-0 pt-1 pb-2">Events</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-3">
                            <h6 class="mb-2">
                                <a href="#social-links" class="d-block  dropdown-toggle  py-0 text-primary h5"
                                    data-bs-toggle="collapse">Account</a>
                            </h6>
                            <div id="social-links" class="collapse d-lg-block" data-bs-parent="#footer-links">
                                <ul class="nav flex-column mb-2 mb-lg-0">
                                    <li class="nav-item"><a href="{{ admin_url('') }}"
                                            class="nav-link d-inline-block px-0 pt-1 pb-2">Register</a></li>
                                    <li class="nav-item"><a href="{{ admin_url('') }}"
                                            class="nav-link d-inline-block px-0 pt-1 pb-2">Login</a></li>
                                    <li class="nav-item"><a href="{{ admin_url('') }}"
                                            class="nav-link d-inline-block px-0 pt-1 pb-2">Mobile App</a></li>

                                    <h6 class="mb-2 mt-3 text-primary h5 ">Our social media</h6>
                                    <li class="nav-item"><a href="#"
                                            class="nav-link d-inline-block px-0 pt-1 pb-2">Facebook</a></li>
                                    <li class="nav-item"><a href="#"
                                            class="nav-link d-inline-block px-0 pt-1 pb-2">LinkedIn</a></li>
                                    <li class="nav-item"><a href="#"
                                            class="nav-link d-inline-block px-0 pt-1 pb-2">Twitter</a></li>
                                    <li class="nav-item"><a href="#"
                                            class="nav-link d-inline-block px-0 pt-1 pb-2">Instagram</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-5 pt-2 pt-lg-0">
                            <h6 class="mb-2 text-primary h5">Useful links</h6>
                            <ul class="nav flex-column mb-2 mb-lg-0">
                                <li class="nav-item"><a href="https://www.ucc.co.ug" target="_blank"
                                        class="nav-link d-inline-block px-0 pt-1 pb-2">UCC</a></li>

                                <li class="nav-item"><a href="https://www.oic-oci.org/" target="_blank"
                                        class="nav-link d-inline-block px-0 pt-1 pb-2">NUDIPU</a></li>

                                <li class="nav-item"><a href="https://8technologies.net" target="_blank"
                                        class="nav-link d-inline-block px-0 pt-1 pb-2">8technologies Consultants</a></li>
                            </ul>
                            <a href="mailto:info@NUDIPU.org" class="fw-medium">info@ict4personswithdisabilities.org</a>
                        </div>
                    </div>
                </div>
            </div>
            <p class="nav d-block  text-center text-md-start pb-2 pb-lg-0 mb-0">
                Hand-made with ❤️ by
                <a class="nav-link d-inline-block p-0" href="https://twitter.com/8TechConsults" target="_blank"
                    rel="noopener">8Technologies Consults</a>
            </p>
        </div>
    </footer>

    <!-- Back to top button -->
    <a href="#top" class="btn-scroll-top" data-scroll>
        <span class="btn-scroll-top-tooltip text-muted fs-sm me-2">Top</span>
        <i class="btn-scroll-top-icon bx bx-chevron-up"></i>
    </a>
@endsection
