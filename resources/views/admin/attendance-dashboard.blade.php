@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<style>
    :root {
        --att-primary-color: #2c3e50;
        --att-success-color: #27ae60;
        --att-danger-color: #e74c3c;
        --att-warning-color: #f39c12;
        --att-info-color: #3498db;
        --att-light-bg: #f8f9fa;
        --att-border-color: #dee2e6;
        --att-text-color: #2c3e50;
        --att-text-muted: #6c757d;
    }

    .att-dashboard-container {
        padding: 0;
        margin: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: var(--att-text-color);
        background: transparent;
    }

    .att-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .att-stat-item {
        text-align: center;
        padding: 20px;
        background: #fff;
        border: 1px solid var(--att-border-color);
        border-radius: 8px;
    }

    .att-stat-number {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .att-stat-label {
        color: var(--att-text-muted);
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .att-charts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 30px;
        margin-bottom: 30px;
    }

    .att-chart-container {
        height: 340px;
        background: #fff;
        border: 1px solid var(--att-border-color);
        border-radius: 8px;
        padding: 20px;
        position: relative;
    }

    .att-chart-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--att-text-color);
        margin-bottom: 15px;
        text-align: center;
        border-bottom: 2px solid var(--att-border-color);
        padding-bottom: 8px;
    }

    .att-chart-canvas {
        height: 260px;
    }

    .att-table-container {
        background: #fff;
        border: 1px solid var(--att-border-color);
        border-radius: 8px;
        margin-bottom: 30px;
        overflow-x: auto;
    }

    .att-table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
    }

    .att-table th,
    .att-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid var(--att-border-color);
    }

    .att-table th {
        background: var(--att-light-bg);
        font-weight: 600;
        color: var(--att-text-color);
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .att-table tbody tr:hover {
        background: var(--att-light-bg);
    }

    .att-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .att-badge-success {
        background: var(--att-success-color);
        color: white;
    }

    .att-badge-danger {
        background: var(--att-danger-color);
        color: white;
    }

    .att-badge-warning {
        background: var(--att-warning-color);
        color: white;
    }

    .att-badge-info {
        background: var(--att-info-color);
        color: white;
    }

    .att-progress {
        background: #e9ecef;
        border-radius: 4px;
        height: 20px;
        position: relative;
        overflow: hidden;
    }

    .att-progress-bar {
        height: 100%;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .att-filter-container {
        background: var(--att-light-bg);
        padding: 20px;
        border-radius: 8px;
        margin-top: 30px;
        border: 1px solid var(--att-border-color);
    }

    .att-filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .att-filter-group {
        display: flex;
        flex-direction: column;
    }

    .att-filter-label {
        font-weight: 500;
        margin-bottom: 5px;
        color: var(--att-text-color);
        font-size: 0.9rem;
    }

    .att-form-control {
        padding: 8px 12px;
        border: 1px solid var(--att-border-color);
        border-radius: 4px;
        font-size: 0.9rem;
        background: white;
    }

    .att-form-control:focus {
        outline: none;
        border-color: var(--att-info-color);
        box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
    }

    .att-btn {
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.9rem;
        font-weight: 500;
        text-decoration: none;
        display: inline-block;
        text-align: center;
    }

    .att-btn-primary {
        background: var(--att-info-color);
        color: white;
    }

    .att-btn-primary:hover {
        background: #2980b9;
    }

    .att-two-column {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        align-items: start;
    }

    @media print {
        .att-filter-container {
            display: none !important;
        }

        .att-dashboard-container {
            padding: 0 !important;
        }
    }

    @media (max-width: 768px) {
        .att-stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .att-charts-grid {
            grid-template-columns: 1fr;
        }

        .att-filter-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="att-dashboard-container">

    <!-- Key Statistics -->
    <div class="att-stats-grid">
        <div class="att-stat-item">
            <div class="att-stat-number" style="color: var(--att-info-color);">
                {{ number_format($overall_stats->total_sessions) }}</div>
            <div class="att-stat-label">Total Sessions</div>
        </div>
        <div class="att-stat-item">
            <div class="att-stat-number" style="color: var(--att-success-color);">
                {{ number_format($overall_stats->total_present) }}</div>
            <div class="att-stat-label">Present</div>
        </div>
        <div class="att-stat-item">
            <div class="att-stat-number" style="color: var(--att-danger-color);">
                {{ number_format($overall_stats->total_absent) }}</div>
            <div class="att-stat-label">Absent</div>
        </div>
        <div class="att-stat-item">
            <div class="att-stat-number" style="color: var(--att-warning-color);">
                {{ number_format($overall_stats->attendance_rate, 1) }}%</div>
            <div class="att-stat-label">Attendance Rate</div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="att-charts-grid">
        <div class="att-chart-container">
            <div class="att-chart-title">Gender-Based Attendance Distribution</div>
            <div class="att-chart-canvas">
                <canvas id="genderChart"></canvas>
            </div>
        </div>
        <div class="att-chart-container">
            <div class="att-chart-title">Attendance Trend Over Time</div>
            <div class="att-chart-canvas">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

    </div>

    <!-- 3-Column Bootstrap Layout -->
    <div class="row" style="margin-bottom: 30px;">
        <!-- Column 1: Roll-Call Performance Comparison -->
        <div class="col-md-4">
            <div class="att-chart-container">
                <div class="att-chart-title">Roll-Call Type Performance</div>
                <div class="att-chart-canvas">
                    <canvas id="typeChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Column 2: Secular Classes -->
        <div class="col-md-4">
            <div class="att-table-container">
                <table class="att-table">
                    <thead>
                        <tr>
                            <th colspan="4"
                                style="background: var(--att-primary-color); color: white; text-align: center;">Secular
                                Classes</th>
                        </tr>
                        <tr>
                            <th>Class</th>
                            <th>Present</th>
                            <th>Absent</th>
                            <th>Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($secular_class_stats as $stat)
                            <tr>
                                <td>{{ $stat->class_name ?? 'Unknown' }}</td>
                                <td><span
                                        class="att-badge att-badge-success">{{ number_format($stat->present_count) }}</span>
                                </td>
                                <td><span
                                        class="att-badge att-badge-danger">{{ number_format($stat->absent_count) }}</span>
                                </td>
                                <td>
                                    <div class="att-progress">
                                        <div class="att-progress-bar"
                                            style="width: {{ $stat->attendance_rate }}%; background: var(--att-success-color);">
                                            {{ number_format($stat->attendance_rate, 1) }}%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4"
                                    style="text-align: center; color: var(--att-text-muted); padding: 20px;">
                                    No secular class data available
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Column 3: Theology Classes -->
        <div class="col-md-4">
            <div class="att-table-container">
                <table class="att-table">
                    <thead>
                        <tr>
                            <th colspan="4"
                                style="background: var(--att-warning-color); color: white; text-align: center;">Theology
                                Classes</th>
                        </tr>
                        <tr>
                            <th>Class</th>
                            <th>Present</th>
                            <th>Absent</th>
                            <th>Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($theology_class_stats as $stat)
                            <tr>
                                <td>{{ $stat->class_name ?? 'Unknown' }}</td>
                                <td><span
                                        class="att-badge att-badge-success">{{ number_format($stat->present_count) }}</span>
                                </td>
                                <td><span
                                        class="att-badge att-badge-danger">{{ number_format($stat->absent_count) }}</span>
                                </td>
                                <td>
                                    <div class="att-progress">
                                        <div class="att-progress-bar"
                                            style="width: {{ $stat->attendance_rate }}%; background: var(--att-success-color);">
                                            {{ number_format($stat->attendance_rate, 1) }}%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4"
                                    style="text-align: center; color: var(--att-text-muted); padding: 20px;">
                                    No theology class data available
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Student Rankings -->
    <div class="att-two-column">
        <div class="att-table-container">
            <table class="att-table">
                <thead>
                    <tr>
                        <th colspan="4" style="background: var(--att-danger-color); color: white;">Students with High
                            Absences</th>
                    </tr>
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>Absences</th>
                        <th>Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($top_absent_students as $index => $student)
                        <tr>
                            <td><strong>{{ $index + 1 }}</strong></td>
                            <td>{{ $student->display_name ?? ($student->student_name ?? 'Unknown') }}</td>
                            <td><span
                                    class="att-badge att-badge-danger">{{ number_format($student->absent_count) }}</span>
                            </td>
                            <td>{{ number_format($student->attendance_rate, 1) }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--att-text-muted); padding: 20px;">
                                No absence data available
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="att-table-container">
            <table class="att-table">
                <thead>
                    <tr>
                        <th colspan="4" style="background: var(--att-success-color); color: white;">Students with
                            Best Attendance</th>
                    </tr>
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>Present</th>
                        <th>Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($top_present_students as $index => $student)
                        <tr>
                            <td><strong>{{ $index + 1 }}</strong></td>
                            <td>{{ $student->display_name ?? ($student->student_name ?? 'Unknown') }}</td>
                            <td><span
                                    class="att-badge att-badge-success">{{ number_format($student->present_count) }}</span>
                            </td>
                            <td>{{ number_format($student->attendance_rate, 1) }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--att-text-muted); padding: 20px;">
                                No attendance data available
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Filters at Bottom -->
    <div class="att-filter-container">
        <form method="GET" action="{{ route('.attendance.dashboard') }}" id="filterForm">
            <div class="att-filter-grid">
                <div class="att-filter-group">
                    <label class="att-filter-label">Start Date</label>
                    <input type="date" name="start_date" class="att-form-control" value="{{ $start_date }}">
                </div>
                <div class="att-filter-group">
                    <label class="att-filter-label">End Date</label>
                    <input type="date" name="end_date" class="att-form-control" value="{{ $end_date }}">
                </div>
                <div class="att-filter-group">
                    <label class="att-filter-label">Term</label>
                    <select name="term_id" class="att-form-control">
                        <option value="">All Terms</option>
                        @foreach ($terms as $term)
                            <option value="{{ $term->id }}" {{ $term_id == $term->id ? 'selected' : '' }}>
                                {{ $term->name_text }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="att-filter-group">
                    <label class="att-filter-label">Class</label>
                    <select name="class_id" class="att-form-control">
                        <option value="">All Classes</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}" {{ $class_id == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="att-filter-group">
                    <label class="att-filter-label">Roll-Call Type</label>
                    <select name="attendance_type" class="att-form-control">
                        <option value="">All Types</option>
                        @foreach ($attendance_types as $type_key => $type_name)
                            <option value="{{ $type_key }}"
                                {{ $attendance_type == $type_key ? 'selected' : '' }}>
                                {{ $type_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="text-align: center; margin-top: 20px;">
                <button type="submit" class="att-btn att-btn-primary">
                    <i class="fa fa-filter"></i> Apply Filters
                </button>
                <button type="button" onclick="window.print()" class="att-btn att-btn-primary"
                    style="margin-left: 10px;">
                    <i class="fa fa-print"></i> Print
                </button>
                <button type="button" onclick="exportData()" class="att-btn att-btn-primary"
                    style="margin-left: 10px;">
                    <i class="fa fa-download"></i> Export
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script data-exec-on-popstate>
        // Check if Chart.js is loaded
        console.log('Chart.js available:', typeof Chart !== 'undefined');
        
        // Store chart instances globally
        let genderChart, trendChart, typeChart;
        
        function destroyExistingCharts() {
            if (genderChart) {
                genderChart.destroy();
                genderChart = null;
            }
            if (trendChart) {
                trendChart.destroy();
                trendChart = null;
            }
            if (typeChart) {
                typeChart.destroy();
                typeChart = null;
            }
        }
        
        function initializeCharts() {
            console.log('Initializing charts...');
            
            // Check if Chart.js is available
            if (typeof Chart === 'undefined') {
                console.error('Chart.js not loaded! Retrying in 1 second...');
                setTimeout(initializeCharts, 1000);
                return;
            }
            
            // Destroy any existing charts first
            destroyExistingCharts();
            
            // Gender Chart
            const genderCtx = document.getElementById('genderChart');
            if (!genderCtx) {
                console.log('Gender chart canvas not found');
                return; // Exit if element doesn't exist
            }
            console.log('Creating gender chart...');
            
            const genderData = @json($gender_stats);

            genderChart = new Chart(genderCtx, {
                type: 'doughnut',
                data: {
                    labels: genderData.map(item =>
                        `${item.gender || 'Unknown'} (${item.percentage}%)`),
                    datasets: [{
                        data: genderData.map(item => parseFloat(item.percentage)),
                        backgroundColor: ['#3498db', '#e74c3c', '#f39c12'],
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const item = genderData[context.dataIndex];
                                    return `${context.label}: ${item.student_count} students (${item.percentage}% of total)`;
                                }
                            }
                        }
                    }
                }
            });

            // Trend Chart
            const trendCtx = document.getElementById('trendChart');
            if (!trendCtx) {
                console.log('Trend chart canvas not found');
                return; // Exit if element doesn't exist
            }
            console.log('Creating trend chart...');
            
            const trendData = @json($attendance_trend);

            // Function to get day name from date
            function getDayName(dateStr) {
                const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                const date = new Date(dateStr);
                return days[date.getDay()];
            }

            // Function to format date without year
            function formatDateWithoutYear(dateStr) {
                const date = new Date(dateStr);
                return `${getDayName(dateStr)}, ${date.getDate()}/${date.getMonth() + 1}`;
            }

            trendChart = new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: trendData.map(item => formatDateWithoutYear(item.date)),
                    datasets: [{
                            label: 'Present',
                            data: trendData.map(item => item.present_count),
                            borderColor: '#27ae60',
                            backgroundColor: 'rgba(39, 174, 96, 0.1)',
                            tension: 0.4
                        },
                        {
                            label: 'Absent',
                            data: trendData.map(item => item.absent_count),
                            borderColor: '#e74c3c',
                            backgroundColor: 'rgba(231, 76, 60, 0.1)',
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Type Chart - Secular vs Theology
            const typeCtx = document.getElementById('typeChart');
            if (!typeCtx) {
                console.log('Type chart canvas not found');
                return; // Exit if element doesn't exist
            }
            console.log('Creating type chart...');
            
            const secularTheologyData = @json($secular_theology_stats);

            typeChart = new Chart(typeCtx, {
                type: 'bar',
                data: {
                    labels: secularTheologyData.map(item => item.type_name),
                    datasets: [{
                        label: 'Attendance Rate %',
                        data: secularTheologyData.map(item => parseFloat(item.attendance_rate)),
                        backgroundColor: ['#3498db', '#9b59b6'],
                        borderColor: '#fff',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const item = secularTheologyData[context.dataIndex];
                                    return `${context.label}: ${item.present_count}/${item.total_records} (${item.attendance_rate}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        function exportData() {
            alert('Export functionality will be implemented soon!');
        }

        // Initialize charts - handle both normal page load and pjax
        function initChartsWhenReady() {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initializeCharts);
            } else {
                initializeCharts();
            }
        }
        
        // Initialize on page load
        console.log('Setting up chart initialization...');
        initChartsWhenReady();
        
        // Reinitialize charts after pjax navigation
        if (typeof $ !== 'undefined') {
            $(document).on('pjax:complete', function() {
                console.log('Pjax complete - reinitializing charts...');
                setTimeout(initializeCharts, 100); // Small delay to ensure DOM is ready
            });
            
            // Also handle pjax:end for better compatibility
            $(document).on('pjax:end', function() {
                console.log('Pjax end - reinitializing charts...');
                setTimeout(initializeCharts, 200);
            });
        } else {
            console.log('jQuery not available for pjax handling');
        }
        
        // Also handle regular page navigation events
        window.addEventListener('popstate', function() {
            setTimeout(initializeCharts, 300);
        });
    </script>
</div>
