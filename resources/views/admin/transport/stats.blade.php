{{-- resources/views/admin/transport/stats.blade.php --}}

@push('styles')
<style>
  .kpi-card { border-radius: 6px; margin-bottom:1rem; }
  .kpi-card .value { font-size: 1.6rem; font-weight: bold; }
  .kpi-card .subtitle { color: #666; }
  .kpi-card .small { font-size: .85rem; color: #999; }
</style>
@endpush

<div class="row">
  {{-- Subscriptions By Type --}}
  @foreach(['To School','From School','Round Trip'] as $type)
    <div class="col-md-3">
      <div class="card kpi-card">
        <div class="card-body">
          <div class="subtitle">{{ $type }} (Curr / Last Mo.)</div>
          <div class="value">
            {{ $currByType[$type] ?? 0 }} / {{ $lastByType[$type] ?? 0 }}
          </div>
        </div>
      </div>
    </div>
  @endforeach

  {{-- New vs Renewals --}}
  <div class="col-md-3">
    <div class="card kpi-card">
      <div class="card-body">
        <div class="subtitle">New / Renewals</div>
        <div class="value">{{ $currNew }} / {{ $currRenew }}</div>
        <div class="small">Last Mo: {{ $lastNew }} / {{ $lastRenew }}</div>
      </div>
    </div>
  </div>

  {{-- Revenue --}}
  <div class="col-md-3">
    <div class="card kpi-card">
      <div class="card-body">
        <div class="subtitle">Revenue (Curr / Last Mo.)</div>
        <div class="value">{{ number_format($currRevenue) }} / {{ number_format($lastRevenue) }}</div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card kpi-card">
      <div class="card-body">
        <div class="subtitle">Top Route</div>
        <div class="value">{{ $currTopName }}</div>
        <div class="small">Last Mo: {{ $lastTopName }}</div>
      </div>
    </div>
  </div>

  {{-- Outstanding Balances --}}
  <div class="col-md-3">
    <div class="card kpi-card">
      <div class="card-body">
        <div class="subtitle">Outstanding Subs</div>
        <div class="value">{{ $currOutCount }} / {{ $lastOutCount }}</div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card kpi-card">
      <div class="card-body">
        <div class="subtitle">Overdue %</div>
        <div class="value">{{ $currOutPercent }}% / {{ $lastOutPercent }}%</div>
      </div>
    </div>
  </div>

  {{-- Trips & Passenger Insights --}}
  <div class="col-md-3">
    <div class="card kpi-card">
      <div class="card-body">
        <div class="subtitle">Trips This Mo.</div>
        <div class="value">{{ $currTotalTrips }} / {{ $lastTotalTrips }}</div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card kpi-card">
      <div class="card-body">
        <div class="subtitle">Avg Load / Trip</div>
        <div class="value">{{ $currAvgLoad }} / {{ $lastAvgLoad }}</div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card kpi-card">
      <div class="card-body">
        <div class="subtitle">No-Show Rate</div>
        <div class="value">{{ $currNoShowRate }}% / {{ $lastNoShowRate }}%</div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card kpi-card">
      <div class="card-body">
        <div class="subtitle">Dir Split (To/From)</div>
        <div class="value">
          {{ $currDirPerc['To School'] }}% / {{ $currDirPerc['From School'] }}%<br>
          <small class="small">
            Last: {{ $lastDirPerc['To School'] }}% / {{ $lastDirPerc['From School'] }}%
          </small>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Two Charts --}}
<div class="row">
  <div class="col-md-6">
    <div class="card kpi-card">
      <div class="card-header"><strong>Daily Boardings (7d)</strong></div>
      <div class="card-body"><canvas id="chartA" style="width:100%"></canvas></div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card kpi-card">
      <div class="card-header"><strong>Revenue by Route (Curr Mo.)</strong></div>
      <div class="card-body"><canvas id="chartB" style="width:100%"></canvas></div>
    </div>
  </div>
</div>

@push('scripts')
<script>
$(function(){
  new Chart($('#chartA')[0].getContext('2d'), {
    type:'line',
    data:{
      labels: @json($chartA_labels),
      datasets:[{
        label:'Boarded',
        data:@json($chartA_data),
        fill:true,
        backgroundColor:'rgba(39,124,97,0.2)',
        borderColor:'#277C61',
        tension:0.3
      }]
    },
    options:{responsive:true,plugins:{legend:{display:false}}}
  });

  new Chart($('#chartB')[0].getContext('2d'), {
    type:'bar',
    data:{
      labels:@json($chartB_labels),
      datasets:[{
        label:'UGX',
        data:@json($chartB_data),
        backgroundColor:'#277C61'
      }]
    },
    options:{responsive:true,plugins:{legend:{display:false}}}
  });
});
</script>
@endpush