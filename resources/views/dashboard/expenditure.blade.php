<?php use App\Models\Utils; ?>
<?php $color = $color ?? '#343a40'; ?>
@include('dashboard._ds-styles')

<div class="ds-card" style="--ds-accent: {{ $color }};">
    <div class="ds-card-header" style="background: {{ $color }};">
        <div class="ds-card-header-left">
            <span class="ds-card-icon" style="background: rgba(255,255,255,0.15);"><i class="fa fa-credit-card"></i></span>
            <div class="ds-card-title" style="color: #fff;">Expenditure — {{ count($labels) }} Days</div>
        </div>
        <a href="{{ url('/financial-records-expenditure') }}" class="ds-btn-sm" style="border-color: rgba(255,255,255,0.4); color: #fff;">View All</a>
    </div>
    <div style="padding: 10px 14px 14px;">
        <div class="ds-chart-container">
            <canvas id="grapth-expenditure"></canvas>
        </div>
    </div>
</div>

<script>
$(function() {
    var el = document.getElementById('grapth-expenditure');
    if (!el) return;
    var primaryColor = @json($color);
    new Chart(el.getContext('2d'), {
        type: 'bar',
        data: {
            labels: @json($labels),
            datasets: [{
                label: 'Total Expense',
                backgroundColor: '#dc3545',
                data: @json($data),
                borderRadius: 0,
                barPercentage: 0.7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#343a40',
                    titleFont: { size: 11, weight: 'bold' },
                    bodyFont: { size: 11 },
                    cornerRadius: 0,
                    padding: 8,
                    callbacks: {
                        label: function(ctx) {
                            return ' ' + Number(ctx.parsed.y).toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false },
                    ticks: { font: { size: 10, weight: '600' }, color: '#868e96', padding: 6 },
                    border: { display: false }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 10, weight: '600' }, color: '#868e96', padding: 4, maxRotation: 45, minRotation: 0 },
                    border: { display: false }
                }
            }
        }
    });
});
</script>
