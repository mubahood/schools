@include('dashboard._ds-styles')
<?php $color = $color ?? '#343a40'; ?>

<div class="row" style="margin-bottom: 14px;">
    {{-- LEFT: Table --}}
    <div class="col-md-6 col-12 mb-2 mb-md-0">
        <div class="ds-card" style="--ds-accent: {{ $color }};">
            <div class="ds-card-header" style="background: {{ $color }};">
                <div class="ds-card-header-left">
                    <span class="ds-card-icon" style="background: rgba(255,255,255,0.15);"><i class="fa fa-graduation-cap"></i></span>
                    <div class="ds-card-title" style="color: #fff;">Class Enrollment</div>
                </div>
                <span class="ds-badge" style="background: rgba(255,255,255,0.2);">{{ number_format($grandTotal) }} Students</span>
            </div>
            <div class="ds-table-scroll">
                <table class="ds-table">
                    <thead>
                        <tr>
                            <th style="width:30px;">#</th>
                            <th>Class</th>
                            <th class="text-center">Streams</th>
                            <th class="text-center">Boys</th>
                            <th class="text-center">Girls</th>
                            <th class="text-center">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $i => $row)
                        <tr>
                            <td class="ds-muted">{{ $i + 1 }}</td>
                            <td class="ds-class-name">{{ $row->class_name }}</td>
                            <td class="ds-streams">{{ $row->streams }}</td>
                            <td class="text-center ds-boys">{{ $row->boys }}</td>
                            <td class="text-center ds-girls">{{ $row->girls }}</td>
                            <td class="text-center ds-total">{{ $row->total }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3">TOTAL</td>
                            <td class="text-center ds-boys">{{ $grandBoys }}</td>
                            <td class="text-center ds-girls">{{ $grandGirls }}</td>
                            <td class="text-center ds-total">{{ number_format($grandTotal) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- RIGHT: Chart --}}
    <div class="col-md-6 col-12">
        <div class="ds-card" style="--ds-accent: {{ $color }};">
            <div class="ds-card-header" style="background: {{ $color }};">
                <div class="ds-card-header-left">
                    <span class="ds-card-icon" style="background: rgba(255,255,255,0.15);"><i class="fa fa-chart-line"></i></span>
                    <div class="ds-card-title" style="color: #fff;">Enrollment Trend</div>
                </div>
            </div>
            <div style="padding: 10px 14px 14px;">
                <div class="ds-chart-container">
                    <canvas id="enroll-line-chart"></canvas>
                </div>
                <div class="ds-legend">
                    <span class="ds-legend-item"><span class="ds-legend-dot" style="background:#2980b9;"></span> Boys</span>
                    <span class="ds-legend-item"><span class="ds-legend-dot" style="background:#e74c8b;"></span> Girls</span>
                    <span class="ds-legend-item"><span class="ds-legend-dot" style="background:{{ $color }};"></span> Total</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(function () {
    var el = document.getElementById('enroll-line-chart');
    if (!el) return;
    var primaryColor = @json($color);
    new Chart(el.getContext('2d'), {
        type: 'line',
        data: {
            labels: @json($labels),
            datasets: [
                {
                    label: 'Boys',
                    data: @json($boysData),
                    borderColor: '#2980b9',
                    backgroundColor: 'rgba(41,128,185,0.08)',
                    borderWidth: 2,
                    pointBackgroundColor: '#2980b9',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 1,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'Girls',
                    data: @json($girlsData),
                    borderColor: '#e74c8b',
                    backgroundColor: 'rgba(231,76,139,0.08)',
                    borderWidth: 2,
                    pointBackgroundColor: '#e74c8b',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 1,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'Total',
                    data: @json($totalData),
                    borderColor: primaryColor,
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    pointBackgroundColor: primaryColor,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 1,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.3,
                    borderDash: [5, 3],
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#343a40',
                    titleFont: { size: 11, weight: 'bold' },
                    bodyFont: { size: 11 },
                    cornerRadius: 0,
                    padding: 8,
                    displayColors: true,
                    boxWidth: 8,
                    boxHeight: 8
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
