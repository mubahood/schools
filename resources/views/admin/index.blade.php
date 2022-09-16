<style>
    .cpanel-tab {}

    .text-dark {
        color: black;
    }

    .divider {
        background-color: black;
        height: 1px;
        margin-bottom: 1rem;
        margin-left: 1.7rem;
        margin-right: 2rem;
    }
</style>
<div class="p-5 border border-5 border-primary m-1 mt-0 rounded bg-light">

    <div class="row border-primary border-bottom  p-0">
        <div class="col-4 p-0 m-0">
            {{-- <img src="/assets/icons/logo.png"> --}}
        </div>
        <div class="col-md-4  m-0 cpanel-tab ">
            <h2 class="text-center pt-3 pb-1 m-0 bg-primary rounded-top h3">CONTROL PANEL</h2>
        </div>
        <div class="col-4">
            {{-- <img class="float-right" src="/assets/icons/badge.png"> --}}
        </div>
    </div>
    <div class="row  mt-3 ">
        <div class="col-md-6 pl-0 pr-1">
            <div class=" border border-primary m-0 rounded">


                @if ($u->isRole('super-admin'))
                    @include('widgets.dashboard-widget', [
                        'title' => 'System administration',
                        'icon' => url('/assets/icons/settings.png'),
                        'links' => [
                            [
                                'link' => admin_url('auth/users/create'),
                                'text' => 'Create new user',
                            ],
                            [
                                'link' => admin_url('enterprises/create'),
                                'text' => 'Create new enterprise',
                            ],
                            [
                                'link' => admin_url('enterprises'),
                                'text' => 'Manage enterprises',
                            ],
                        ],
                    ])
                @endif



                @if ($u->isRole('admin'))
                    @include('widgets.dashboard-widget', [
                        'title' => 'Human resource management',
                        'icon' => url('/assets/icons/human.png'),
                        'links' => [
                            [
                                'link' => admin_url('employees/create'),
                                'text' => 'Add new employee',
                            ],
                            [
                                'link' => admin_url('employees'),
                                'text' => 'Manage staff bio data',
                            ], 
                        ],
                    ])
                @endif



                @if ($u->isRole('dos'))
                    @include('widgets.dashboard-widget', [
                        'title' => 'Admission & Classes Centre',
                        'icon' => url('/assets/icons/student.png'),
                        'links' => [
                            [
                                'link' => admin_url('students/create'),
                                'text' => 'Admit new student',
                            ],
                            [
                                'link' => admin_url('students'),
                                'text' => 'Manage students bio data & admissions',
                            ],
                            [
                                'link' => admin_url('students-classes'),
                                'text' => 'Students class & stream management',
                            ],
                        ],
                    ])
                @endif


                @if ($u->isRole('dos'))
                    @include('widgets.dashboard-widget', [
                        'title' => 'Examination centre',
                        'icon' => url('/assets/icons/exam.png'),
                        'links' => [
                            [
                                'link' => admin_url('exams'),
                                'text' => 'Exams',
                            ],
                            [
                                'link' => admin_url('marks'),
                                'text' => 'Results entry (Marks)',
                            ],
                            [
                                'link' => admin_url('student-report-cards'),
                                'text' => 'Student report cards',
                            ],
                        ],
                    ])
                @endif

                @if ($u->isRole('bursar'))
                    @include('widgets.dashboard-widget', [
                        'title' => 'School fees managment',
                        'icon' => url('/assets/icons/money.png'),
                        'links' => [
                            [
                                'link' => admin_url('school-fees-payment'),
                                'text' => 'Fees payment',
                            ],
                            [
                                'link' => admin_url('fees'),
                                'text' => 'Fees billing',
                            ],
                            [
                                'link' => admin_url('accounts'),
                                'text' => 'Students fees accounts',
                            ],
                        ],
                    ])
                @endif

                @if ($u->isRole('bursar'))
                    @include('widgets.dashboard-widget', [
                        'title' => 'Accounting & Finance Centre',
                        'icon' => url('/assets/icons/accounts.png'),
                        'links' => [
                            [
                                'text' => 'Financial accounts',
                                'link' => admin_url('accounts'),
                            ],
                            [
                                'link' => admin_url('transactions'),
                                'text' => 'All financial transactions',
                            ],
                            [
                                'link' => '#',
                                'text' => 'Documentation',
                            ],
                        ],
                    ])
                @endif


                @if ($u->isRole('dos'))
                    @include('widgets.dashboard-widget', [
                        'title' => 'System settings & configuration',
                        'icon' => url('/assets/icons/settings.png'),
                        'links' => [
                            [
                                'link' => admin_url('subjects'),
                                'text' => 'Manage subjects',
                            ],
                            [
                                'link' => admin_url('classes'),
                                'text' => 'Manage classes',
                            ],
                            [
                                'link' => admin_url('academic-years'),
                                'text' => 'Manage academic years',
                            ],
                        ],
                    ])
                @endif



            </div>
        </div>


        {{-- <div class="col-md-6 pl-0 pr-1">
            <div class=" border border-primary m-0 rounded">

            


            </div>
        </div> --}}
    </div>
</div>
