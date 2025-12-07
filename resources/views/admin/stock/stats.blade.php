@php
    function fmt($n)
    {
        return number_format($n, 0, '.', ',');
    }
@endphp
<style>
    body {
        background: #f6f8fa;
        font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
        color: #222;
        line-height: 1.6;
    }

    .dashboard-summary {
        display: flex;
        flex-wrap: wrap;
        gap: 24px;
        margin-bottom: 32px;
        justify-content: space-between;
    }

    .summary-card {
        flex: 1 1 220px;
        min-width: 220px;
        background: linear-gradient(135deg, #fff 80%, #f3f6fa 100%);
        border: 1px solid #e5e9f2;
        border-radius: 14px;
        padding: 28px 24px 24px 24px;
        /* Slightly more padding */
        box-shadow: 0 4px 24px 0 rgba(60, 72, 88, 0.07);
        transition: box-shadow 0.2s;
        position: relative;
        overflow: hidden;
    }

    .summary-card h4 {
        margin-bottom: 20px;
        color: #222;
        border-bottom: 3px solid {{ Admin::user()->ent->color }};
        display: inline-block;
        padding-bottom: 8px;
        font-weight: 700;
        letter-spacing: .5px;
    }

    .summary-card ul {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .summary-card li {
        color: #444;
        margin-bottom: 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .summary-card strong {
        color: {{ Admin::user()->ent->color }};
        font-weight: 700;
        letter-spacing: .5px;
    }

    .summary-card:last-child ul li {
        color: #555;
    }

    .summary-card:last-child ul li strong {
        color: #1a1a1a;
    }

    .summary-card:before {
        content: '';
        position: absolute;
        right: -40px;
        top: -40px;
        width: 90px;
        height: 90px;
        background: {{ Admin::user()->ent->color }}22;
        border-radius: 50%;
        z-index: 0;
    }

    .summary-card>* {
        position: relative;
        z-index: 1;
    }

    /* Data Panels */
    .data-panels {
        display: flex;
        gap: 32px;
        margin-top: 36px;
        flex-wrap: wrap;
    }

    .table-panel {
        flex: 1 1 340px;
        min-width: 320px;
        background: #fff;
        border: 1px solid #e5e9f2;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 2px 12px 0 rgba(60, 72, 88, 0.06);
        display: flex;
        flex-direction: column;
    }

    .table-panel h5 {
        background: linear-gradient(90deg, {{ Admin::user()->ent->color }}, {{ Admin::user()->ent->color }}cc 80%);
        color: #fff;
        margin: 0;
        padding: 22px 26px 18px 26px;
        /* More padding */
        font-weight: 700;
        letter-spacing: .5px;
        border-bottom: 1px solid #e5e9f2;
    }

    .table-panel table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
    }

    .table-panel th,
    .table-panel td {
        padding: 14px 18px;
        /* More padding */
        border-bottom: 1px solid #f0f2f6;
        font-size: 1.05rem;
        /* Increased */
    }

    .table-panel th {
        background: #f8fafc;
        text-align: left;
        color: #333;
        font-weight: 700;
        letter-spacing: .3px;
    }

    .table-panel tbody tr:hover {
        background: #f3f6fa;
        transition: background 0.15s;
    }

    .table-panel td {
        color: #444;
    }

    .table-panel .text-center {
        text-align: center;
        color: #aaa;
        font-style: italic;
        padding: 28px 0;
    }

    /* Responsive */
    @media (max-width: 1100px) {

        .dashboard-summary,
        .data-panels {
            flex-direction: column;
            gap: 18px;
        }

        .summary-card,
        .table-panel {
            min-width: 0;
            width: 100%;
        }
    }
</style>


<div class="dashboard-summary">
    <div class="summary-card">
        <h4>Overview</h4>
        <ul>
            <li><span>Categories</span> <strong>{{ fmt($totalCategories) }}</strong></li>
            <li><span>Batches</span> <strong>{{ fmt($totalBatches) }}</strong></li>
            <li><span>Records</span> <strong>{{ fmt($totalRecords) }}</strong></li>
        </ul>
    </div>
    <div class="summary-card">
        <h4>Movement</h4>
        <ul>
            <li><span>In Records</span> <strong>{{ fmt($inRecordsCount) }}</strong></li>
            <li><span>Out Records</span> <strong>{{ fmt($outRecordsCount) }}</strong></li>
            <li><span>Avg. Value/Cat</span> <strong>UGX {{ fmt($avgValuePerCategory) }}</strong></li>
        </ul>
    </div>
    <div class="summary-card">
        <h4>Inventory</h4>
        <ul>
            <li><span>Total Value</span> <strong>UGX {{ fmt($currentValue) }}</strong></li>
            <li><span>Total Qty</span> <strong>{{ fmt($currentQuantity) }}</strong></li>
            <li><span>Out-of-Stock Cats</span> <strong>{{ fmt($outOfStockCats) }}</strong></li>
            <li><span>Low-Stock Cats</span> <strong>{{ fmt($lowStockCats) }}</strong></li>
        </ul>
    </div>
    <div class="summary-card">
        <h4>Top 3 by Value</h4>
        <ul>
            @foreach ($topCategories as $cat)
                <li><span>{{ $cat->name }}</span> <strong>UGX {{ fmt($cat->total_worth) }}</strong></li>
            @endforeach
        </ul>
    </div>
</div>

{{-- SERVICE SUBSCRIPTION INVENTORY SECTION --}}
<div style="margin-top: 30px; margin-bottom: 20px;">
    <h3 style="color: {{ Admin::user()->ent->color }}; border-bottom: 3px solid {{ Admin::user()->ent->color }}; padding-bottom: 10px; display: inline-block; margin-bottom: 20px;">
        <i class="fa fa-cubes"></i> Service Subscription Inventory
    </h3>
</div>

{{-- Subscription Summary Cards --}}
<div class="dashboard-summary">
    {{-- Subscription Overview --}}
    <div class="summary-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <h4><i class="fa fa-clipboard"></i> Subscription Overview</h4>
        <ul>
            <li>
                <span>Total Managed</span>
                <strong>{{ fmt($totalInventorySubscriptions) }}</strong>
            </li>
            <li>
                <span>Completed</span>
                <strong style="color: #10b981;">{{ fmt($inventoryCompleted) }}</strong>
            </li>
            <li>
                <span>Incomplete</span>
                <strong style="color: #f59e0b;">{{ fmt($inventoryIncomplete) }}</strong>
            </li>
            @if($totalInventorySubscriptions > 0)
            <li style="border-top: 1px solid rgba(255,255,255,0.2); margin-top: 8px; padding-top: 8px;">
                <span>Completion Rate</span>
                <strong>{{ number_format(($inventoryCompleted / $totalInventorySubscriptions) * 100, 1) }}%</strong>
            </li>
            @endif
        </ul>
    </div>

    {{-- Service Status --}}
    <div class="summary-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
        <h4><i class="fa fa-tasks"></i> Service Status</h4>
        <ul>
            <li>
                <span><i class="fa fa-check-circle text-success"></i> Offered</span>
                <strong>{{ fmt($inventoryOffered) }}</strong>
            </li>
            <li>
                <span><i class="fa fa-clock-o text-warning"></i> Pending</span>
                <strong>{{ fmt($inventoryPending) }}</strong>
            </li>
            <li>
                <span><i class="fa fa-times-circle text-danger"></i> Cancelled</span>
                <strong>{{ fmt($inventoryCancelled) }}</strong>
            </li>
        </ul>
    </div>

    {{-- Quantity Allocated --}}
    <div class="summary-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
        <h4><i class="fa fa-box"></i> Quantity Metrics</h4>
        <ul>
            <li>
                <span>Total Allocated</span>
                <strong>{{ fmt($totalAllocatedQuantity) }}</strong>
            </li>
            <li>
                <span>Services Pending</span>
                <strong>{{ $pendingServices->count() }}</strong>
            </li>
            @if($pendingServices->count() > 0)
            <li>
                <span>Items Needed</span>
                <strong>{{ fmt($pendingServices->sum('quantity_needed')) }}</strong>
            </li>
            @endif
        </ul>
    </div>

    {{-- Quick Stats --}}
    <div class="summary-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
        <h4><i class="fa fa-bolt"></i> Quick Stats</h4>
        <ul>
            <li>
                <span>Avg. Items/Subscription</span>
                <strong>{{ $totalInventorySubscriptions > 0 ? number_format($totalAllocatedQuantity / ($inventoryOffered ?: 1), 1) : '0' }}</strong>
            </li>
            <li>
                <span>Stock Utilization</span>
                <strong>{{ $currentQuantity > 0 ? number_format(($totalAllocatedQuantity / $currentQuantity) * 100, 1) : '0' }}%</strong>
            </li>
            <li>
                <span>Active Requests</span>
                <strong style="color: #ef4444;">{{ fmt($inventoryIncomplete) }}</strong>
            </li>
        </ul>
    </div>
</div>

{{-- Subscription Data Panels --}}
<div class="data-panels">
    {{-- Services Pending Inventory --}}
    @if($pendingServices->count() > 0)
    <div class="table-panel">
        <h5 style="display: flex; justify-content: space-between; align-items: center;">
            <span><i class="fa fa-bell"></i> Services Pending Inventory</span>
            <span class="label label-warning">{{ $pendingServices->count() }} Service(s)</span>
        </h5>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Service Name</th>
                    <th>Pending Count</th>
                    <th>Quantity Needed</th>
                    <th>Available Stock</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pendingServices as $index => $pending)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $pending['service_name'] }}</strong>
                    </td>
                    <td>
                        <span style="display:inline-block;padding:4px 10px;border-radius:12px;background:#e3f2fd;color:#1976d2;font-weight:600;">
                            {{ fmt($pending['pending_count']) }}
                        </span>
                    </td>
                    <td>{{ fmt($pending['quantity_needed']) }}</td>
                    <td>
                        <span style="color: {{ $pending['available_stock'] > 0 ? '#10b981' : '#ef4444' }}; font-weight: bold;">
                            {{ fmt($pending['available_stock']) }}
                        </span>
                    </td>
                    <td>
                        @if($pending['status'] === 'sufficient')
                            <span style="display:inline-block;padding:2px 10px;border-radius:12px;background:#e6f9f0;color:#1bbf7a;font-weight:600;">
                                <i class="fa fa-check-circle"></i> Sufficient
                            </span>
                        @else
                            <span style="display:inline-block;padding:2px 10px;border-radius:12px;background:#fff3f3;color:#e74c3c;font-weight:600;">
                                <i class="fa fa-exclamation-triangle"></i> Insufficient
                            </span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Latest Incomplete Subscriptions --}}
    @if($latestInventorySubscriptions->count() > 0)
    <div class="table-panel">
        <h5 style="display: flex; justify-content: space-between; align-items: center;">
            <span><i class="fa fa-hourglass-half"></i> Latest Incomplete Subscriptions</span>
            <span class="label label-warning">{{ $latestInventorySubscriptions->count() }} Pending</span>
        </h5>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Student</th>
                    <th>Service</th>
                    <th>Term</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($latestInventorySubscriptions as $sub)
                <tr>
                    <td>
                        <small>{{ \App\Models\Utils::my_date_time($sub->created_at) }}</small>
                    </td>
                    <td>
                        <strong>{{ $sub->sub->name ?? 'N/A' }}</strong>
                    </td>
                    <td>{{ $sub->service->name ?? 'N/A' }}</td>
                    <td>
                        <small>{{ $sub->due_term->name_text ?? 'N/A' }}</small>
                    </td>
                    <td>
                        @if($sub->is_service_offered === 'Pending')
                            <span style="display:inline-block;padding:2px 10px;border-radius:12px;background:#fff4e6;color:#f59e0b;font-weight:600;">
                                <i class="fa fa-clock-o"></i> Pending
                            </span>
                        @elseif($sub->is_service_offered === 'No')
                            <span style="display:inline-block;padding:2px 10px;border-radius:12px;background:#f3f4f6;color:#6b7280;font-weight:600;">
                                <i class="fa fa-minus-circle"></i> Not Offered
                            </span>
                        @else
                            <span style="display:inline-block;padding:2px 10px;border-radius:12px;background:#e3f2fd;color:#1976d2;font-weight:600;">
                                {{ $sub->is_service_offered }}
                            </span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ admin_url('inventory-subscriptions/' . $sub->id . '/edit') }}" 
                           style="display:inline-block;padding:4px 12px;background:{{ Admin::user()->ent->color }};color:#fff;border-radius:4px;text-decoration:none;font-size:0.9em;"
                           title="Manage Inventory">
                            <i class="fa fa-edit"></i> Manage
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

<div class="data-panels">
    {{-- Recent Records --}}
    <div class="table-panel">
        <h5>Recent Stock Records</h5>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Qty</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentRecords as $rec)
                    <tr>
                        <td>{{ \App\Models\Utils::my_date_time($rec->record_date) }}</td>
                        <td>
                            <span
                                style="display:inline-block;padding:2px 10px;border-radius:12px;font-size:.92em;
              background:{{ $rec->type == 'IN' ? '#e6f9f0' : '#fff3f3' }};
              color:{{ $rec->type == 'IN' ? '#1bbf7a' : '#e74c3c' }};
              font-weight:600;">
                                {{ $rec->type }}
                            </span>
                        </td>
                        <td>{{ optional($rec->batch->cat)->name }}</td>
                        <td>{{ fmt($rec->quantity) }}</td>
                        <td>{{ Str::limit($rec->description, 30) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No recent records</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Running-Low Categories --}}
    <div class="table-panel">
        <h5>Running-Low Categories</h5>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th>On-Hand Qty</th>
                    <th>Reorder Level</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lowCats as $c)
                    <tr>
                        <td>{{ $c['name'] }}</td>
                        <td>
                            <span
                                style="display:inline-block;padding:2px 10px;border-radius:12px;font-size:.97em;
              background:#fffbe6;color:#e67e22;font-weight:600;">
                                {{ fmt($c['total_qty']) }}
                            </span>
                        </td>
                        <td>{{ fmt($c['reorder_level']) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center">None running low</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
