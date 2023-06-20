<?php
use App\Models\Utils;
?>
<div class="card  mb-4 mb-md-5 border-0">
    <!--begin::Header-->
    <div class="d-flex justify-content-between px-3 px-md-4 ">
        <h3 class="h3">
            <b>FEES COLLECTION - {{ count($labels) }} DAYS AGO</b>
        </h3>
        <div>
            <a href="{{ url('/school-fees-payment') }}" class="btn btn-sm btn-primary mt-md-4 mt-4">
                View All
            </a>
        </div>
    </div>
    <div class="card-body py-2 py-md-3">


        <canvas id="grapth-fees-collection" style="width: 100%;"></canvas>
        <script>
            $(function() {

                var chartData = {
                    labels: JSON.parse('<?php echo json_encode($labels); ?>'),
                    datasets: [{
                        label: 'Total Collected School Fees',
                        backgroundColor: '#277C61',
                        data: JSON.parse('<?php echo json_encode($data); ?>')
                    }]

                };

                var ctx = document.getElementById('grapth-fees-collection').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: chartData,
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false,
                            },
                        }
                    }
                });
            });
        </script>


    </div>
</div>
