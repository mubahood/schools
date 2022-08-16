<?php
if (!isset($link)) {
    $link = admin_url();
}
if (!isset($data)) {
    $data = [];
    $data['lables'] = [];
    $data['income'] = [];
    $data['expense'] = [];
}
$view_id = 'id-' . rand(10000, 100000000);
?>
<div class="card  ">
    <div class="card-body ">
        <p class="h3  text-bold mb-2 mb-md-3 text-primary p-0 m-0">Fees collected</p>
        <canvas id="{{ $view_id }}" height="180"></canvas>
        <a href="{{ $link }}">View All</a>
    </div>
</div>

<script>
    $(function() {
        var ctx = document.getElementById("<?= $view_id ?>").getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: JSON.parse('<?= json_encode($data['lables']) ?>'),
                datasets: [{
                        label: 'Inome',
                        data: JSON.parse('<?= json_encode($data['income']) ?>'),
                        borderColor: 'rgba(255,99,132,1)',
                        backgroundColor: 'rgba(255,99,132,1)',
                        borderWidth: 2
                    },
                    {
                        label: 'Expense',
                        data: JSON.parse('<?= json_encode($data['expense']) ?>'),
                        borderColor: 'green',
                        backgroundColor: 'green',
                        borderWidth: 2
                    },
                ]
            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            }
        });
    });
</script>
