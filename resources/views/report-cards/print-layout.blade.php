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
        <div class="col-5">
            <b>NAME:</b> <span class="value">MUHINDO MUBARAKA</span>
        </div>
        <div class="col-2 text-center">
            <b>GENDER:</b> <span class="value">MALE</span>
        </div>
        <div class="col-2 text-center">
            <b>AGE:</b> <span class="value">18</span>
        </div>
        <div class="col-3 text-right">
            <b>REG NO.:</b> <span class="value">121012012</span>
        </div>
    </div>

    <div class="container mt-2  ">
        <div class="row ">
            <div class="col-6 border border-dark pt-1">
                <h2 class="text-center text-uppercase h2">secular studies</h2>
                <hr class="my-1">
                <div class="row mt-2 d-flex justify-content-between pl-3 pr-3">
                    <span><b>CLASS:</b> <span class="value">P.7</span></span>
                    <span><b>Aggregates:</b> <span class="value">18</span></span>
                    <span><b>Grade:</b> <span class="value">B</span></span>
                    <span><b>Position:</b> <span class="value">B</span></span>
                </div>
                <div class="row mt-2">
                    <div class="col-12">
                        <table class="table table-bordered marks-table p-0 m-0">
                            <thead class="p-0 m-0 text-center">
                                <th class="text-left pl-2">SUBJECTS</th>
                                <th>B.O.T <br> (30)</th>
                                <th>M.O.T <br> (30)</th>
                                <th>E.O.T <br> (30)</th>
                                <th>TOTAL <br> (100%)</th>
                                <th>Aggr</th>
                                <th class="remarks">Remarks</th>
                                <th class="remarks text-center">Initials</th>
                            </thead>
                            <tr class="marks">
                                <th>Maths</th>
                                <td>10</td>
                                <td>43</td>
                                <td>11</td>
                                <td>65</td>
                                <td>D3</td>
                                <td class="remarks">Fair</td>
                                <td class="remarks text-center">M.K</td>
                            </tr>
                            <tr class="marks">
                                <th>SCIENCE</th>
                                <td>10</td>
                                <td>43</td>
                                <td>11</td>
                                <td>65</td>
                                <td>D3</td>
                                <td class="remarks">Fair</td>
                                <td class="remarks text-center">M.K</td>
                            </tr>
                            <tr class="marks">
                                <th>STT</th>
                                <td>10</td>
                                <td>43</td>
                                <td>11</td>
                                <td>65</td>
                                <td>D3</td>
                                <td class="remarks">Fair</td>
                                <td class="remarks text-center">M.K</td>
                            </tr>
                            <tr class="marks">
                                <th>ENGLISH</th>
                                <td>10</td>
                                <td>43</td>
                                <td>11</td>
                                <td>65</td>
                                <td>D3</td>
                                <td class="remarks">Fair</td>
                                <td class="remarks text-center">M.K</td>
                            </tr>
                            <tr class="marks">
                                <th><b>TOTAL</b></th>
                                <td colspan="2"> </td>
                                <td><b>100</b></td>
                                <td><b>400</b></td>
                                <td colspan="3"> </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                
 
            </div>
            <div class="col-6 border border-dark pt-1">
                <h2 class="text-center">دراسات اللاهوت</h2>
            </div>
        </div>
        
        <div class="row mt-3 p-0 -info ">
            <div class="col-12  text-white"
            style="background-color: black"
            >
                <h2 class="p-1 text-center m-0 "
                style="font-size: 14px;"
                >Grading scale</h2>
            </div>
            <div class="col-12 p-0">
                <table class="table table-bordered grade-table">
                    <tbody>
                        <tr class="text-center">
                            <th class="text-left">Mark</th>
                            <th>0 - 49</th>
                            <th>56 - 59</th>
                            <th>60 - 65</th>
                            <th>66 - 69</th>
                            <th>70 - 79</th>
                            <th>80 - 89</th>
                            <th>90 - 94</th>
                            <th>95 - 100</th>
                        </tr>
                        <tr>
                            <th class="text-left">Aggregates</th>
                            <td class="bordered-table text-center value ">F9</td>
                            <td class="bordered-table text-center value">P7</td>
                            <td class="bordered-table text-center value">C6</td>
                            <td class="bordered-table text-center value">C5</td>
                            <td class="bordered-table text-center value">C4</td>
                            <td class="bordered-table text-center value">C3</td>
                            <td class="bordered-table text-center value">D2</td>
                            <td class="bordered-table text-center value">D1</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</article>
