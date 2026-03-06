<?php
$count = $count ?? 0;
$students = $students ?? [];
$color = $color ?? '#343a40';
$display_limit = 10;
$showing = array_slice($students, 0, $display_limit);
?>
@if ($count > 0)
    @include('dashboard._ds-styles')
    <div class="ds-card ds-card--warn" style="--ds-accent: {{ $color }}; border-left: 3px solid #e65100;">
        <div class="ds-card-header" style="background: #fff8f0; border-bottom: 1px solid #ffe0b2;">
            <div class="ds-card-header-left">
                <span class="ds-card-icon" style="background: #e65100;"><i class="fa fa-exclamation-triangle"></i></span>
                <div>
                    <div class="ds-card-title" style="color: #bf360c;">Students Not in Current Year Class</div>
                    <div class="ds-card-subtitle">Active students without a class in the current academic year</div>
                </div>
            </div>
            <span class="ds-badge" style="background: #e65100;">{{ number_format($count) }}</span>
        </div>
        <div class="ds-table-scroll">
            <table class="ds-table">
                <thead>
                    <tr>
                        <th style="width:36px;">#</th>
                        <th>Student Name</th>
                        <th>Last Class</th>
                        <th>Year</th>
                        <th style="width:60px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($showing as $index => $s)
                        <tr>
                            <td class="ds-muted">{{ $index + 1 }}</td>
                            <td>
                                <a class="ds-link" href="{{ admin_url('students/' . $s->id) }}">{{ $s->name }}</a>
                            </td>
                            <td>{{ $s->current_class_name ?? '—' }}</td>
                            <td>
                                <span class="ds-tag" style="background: #fff3e0; color: #e65100; border-color: #ffcc80;">{{ $s->year_name ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <a class="ds-btn-sm" href="{{ admin_url('students/' . $s->id . '/edit') }}">
                                    <i class="fa fa-pencil"></i> Edit
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($count > $display_limit)
            <div class="ds-card-footer">
                <span>Showing {{ count($showing) }} of {{ number_format($count) }}</span>
                <a href="{{ admin_url('students?status[]=1') }}">
                    View all {{ number_format($count) }} <i class="fa fa-angle-right"></i>
                </a>
            </div>
        @endif
    </div>
@endif
