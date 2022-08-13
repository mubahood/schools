<?php
$canvas_id = 'canvas_' . rand(10000, 1000000);
$_labels = [];
$_title = '';
$_colors = ['#3e95cd', '#8e5ea2', '#3cba9f', '#e8c3b9', '#c45850'];
if (isset($colors)) {
    $_colors = $colors;
}
if (isset($title)) {
    $_title = $title;
}
if (isset($labels)) {
    $_labels = $labels;
}
if (!isset($data)) {
    $data = [];
}
$_data = [];
foreach ($data as $key => $d) {
    $_data[] = (int) $d;
}

?><div class="table-responsive">
    <p class="my-box-title">{{ $_title }}</p>
    <canvas id="{{ $canvas_id }}" width="250px" height="250px"></canvas>
    <br>
    <p><a href="#">View All</a></p>
</div>



<script>
    $(function() {

        new Chart(document.getElementById("{{ $canvas_id }}"), {
            type: 'pie',
            data: {
                labels: JSON.parse('<?= json_encode($_labels) ?>'),
                datasets: [{
                    label: "Population (millions)",
                    backgroundColor: JSON.parse('<?= json_encode($_colors) ?>'),
                    data: JSON.parse('<?= json_encode($_data) ?>'),
                }]
            },
            options: {
                title: {
                    display: true,
                    text: 'Predicted world population (millions) in 2050'
                }
            }
        });


    });
</script>
