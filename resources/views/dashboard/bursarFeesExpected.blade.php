<?php
use App\Models\Utils;
?><style>
    .ext-icon {
        color: rgba(0, 0, 0, 0.5);
        margin-left: 10px;
    }

    .installed {
        color: #00a65a;
        margin-right: 10px;
    }

    .card {
        border-radius: 5px;
    }

    .case-item:hover {
        background-color: rgb(254, 254, 254);
    }
</style>
<div class="card  mb-4 mb-md-5 border-0">
    <!--begin::Header-->
    <div class="d-flex justify-content-between px-3 pt-2 px-md-4 border-bottom">
        <h4 style="line-height: 1; margrin: 0; " class="fs-22 fw-800">
            Total expected fees
        </h4>
    </div>
    <div class="card-body py-2 py-md-3">
        <div class="row">
            <div class="col-md-5 pt-2">
                @foreach ($classes as $class)
                    <p class="d-flex justify-content-between fc-gray fs-18 fw-600 pb-3  "
                        style="line-height: 1;
                    border-bottom: dashed 1px black;">
                        <span>{{ $class->name }}</span>
                        <span class="text-dark">UGX {{ number_format($class->amount) }}</span>
                    </p>
                @endforeach

                <p class="d-flex justify-content-between fc-gray fs-18 fw-600 pt-3 pb-3 "
                    style="line-height: 1;
            border-top: solid 2px black;
            border-bottom: solid 2px black;
            ">
                    <span>TOTAL</span>
                    <span class="text-dark" style="font-size: 20px">UGX {{ number_format($total) }}</span>
                </p>
            </div>
            <div class="col-md-7">
                <canvas id="graph_animals" style="width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>


<script>
    $(function() {

        function randomScalingFactor() {
            return Math.floor(Math.random() * 100)
        }

        window.chartColors = {
            red: 'rgb(255, 99, 132)',
            orange: 'rgb(255, 159, 64)',
            yellow: 'rgb(255, 205, 86)',
            green: 'rgb(75, 192, 192)',
            blue: 'rgb(54, 162, 235)',
            purple: 'rgb(153, 102, 255)',
            grey: 'rgb(201, 203, 207)',
            pink: 'pink'
        };

        var config = {
            type: 'pie',
            data: {
                datasets: [{
                    data: JSON.parse('<?php echo json_encode($amounts); ?>'),
                    backgroundColor: [
                        window.chartColors.red,
                        window.chartColors.orange,
                        window.chartColors.yellow,
                        window.chartColors.green,
                        window.chartColors.blue,
                        window.chartColors.purple,
                        window.chartColors.grey,
                        window.chartColors.pink,
                        'green',
                        'black',
                    ],
                    label: 'Dataset 1'
                }],
                labels: JSON.parse('<?php echo json_encode($labels); ?>')
            },
            options: {
                responsive: true,
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Expected school fees'
                },
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        };

        var ctx = document.getElementById('graph_animals').getContext('2d');
        new Chart(ctx, config);
    });
</script>
