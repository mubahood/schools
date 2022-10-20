<?php
$school_name = 'KIIRA JUNIOR PRIMARY SCHOOL';
$school_address = 'Bwera Kasese Uganda';
$school_tel = '+256783204665';
$report_title = 'END OF TERM III REPORT CARD  2019';
$school_email = 'admin@kjs.com';

?><article>

    <div class="row">
        <div class="col-2">
            <img width="120px" class="img-fluid" src="{{ url('assets/logo.jpeg') }}">
        </div>

        <div class="col-8">

            <h1 class="text-center h3 p-0 m-0">{{ $school_name }}</h1>
            <p class="text-center p font-serif  fs-3 m-0 p-0 mt-2 title-2"><b class="m-0 p-0">{{ $school_address }}</b>
            </p>
            <p class="text-center p font-serif mt-0 mb-0 title-2"><b>TEL:</b> {{ $school_tel }}</p>
            <p class="text-center p font-serif mt-0 title-2 mb-2"><b>EMAIL:</b> {{ $school_email }}</p>
            <p class="text-center p font-serif  fs-3 m-0 p-0"><u><b>{{ $report_title }}</b></u></p>

        </div>

        <div class="col-2 float-right text-right">
            <img width="120px" class="img-fluid float-right text-right" src="{{ url('assets/student.jpg') }}">
        </div>

    </div>

    <hr style="border: solid green 1px;">
    <div class="row mb-2">
        <div class="col-3">
            <b>NAME</b> MUHINDO MUBARAKA
        </div>
        <div class="col-3 text-center">
            <b>GENDER</b> MALE
        </div>
        <div class="col-3 text-center">
            <b>AGE</b> 19
        </div>
        <div class="col-3 text-right">
            <b>REG NO.</b> 12101201200
        </div>
    </div>

    <div class="container mt-2">
        <div class="row ">
            <div class="col-6 border pt-1">
                <h2 class="text-center text-uppercase h2" >secular studies</h2>
            </div>
            <div class="col-6 border pt-1">
                <h2 class="text-center">دراسات اللاهوت</h2>
            </div> 
        </div>
    </div>

</article>
