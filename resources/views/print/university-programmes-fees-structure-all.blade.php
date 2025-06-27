@php
    use App\Models\Service;
    use App\Models\Utils;

    $logoPath   = public_path('storage/' . $ent->logo);
    $activeTerm = $ent->active_term();
    if (! $activeTerm) {
        throw new \Exception("No active term for enterprise.");
    }
    $termName = $activeTerm->name;
    $yearName = $activeTerm->academic_year->name;
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>All Programme Fees Structures</title>
  <link rel="stylesheet" href="{{ public_path('css/bootstrap-print.css') }}">
  <style>
    body, p, table { font-size: 11px; color: #000; }
    th, td      { padding: .3rem .5rem; }
    .subtotal   { font-weight: bold; }
    h4          { font-size: 14px; margin-top: 1rem; margin-bottom: .25rem; color: #000; }
  </style>
  @if($ent->print_water_mark ?? false)
    <style> body::before {
      content:""; position:absolute; inset:0;
      background:url('{{ $logoPath }}') no-repeat center/80%;
      opacity:.05; z-index:-1;
    }</style>
  @endif
</head>
<body>

  <table class="w-100 mb-2">
    <tr>
      <td style="width:15%"><img src="{{ $logoPath }}" style="max-width:100%;"/></td>
      <td class="text-center">
        <h2 class="h5 text-uppercase">{{ $ent->name }}</h2>
        <p class="mb-0">{{ $ent->address }} | P.O. Box {{ $ent->p_o_box }}</p>
        <p class="mb-0">Tel: {{ $ent->phone_number }}@if($ent->phone_number_2), {{ $ent->phone_number_2 }}@endif</p>
        <p class="mb-0">Term {{ $termName }} &nbsp;|&nbsp; {{ $yearName }}</p>
      </td>
      <td style="width:15%"></td>
    </tr>
  </table>

  {{-- coloured separator --}}
  <hr style="border:2px solid {{ $ent->color }}; margin: .5rem 0;" />

  <h3 class="text-center mb-3" style="color:#000; font-size:16px;">All Programme Fees Structures</h3>

  @foreach($programmes as $idx => $programme)

    <h4>{{ $idx+1 }}. {{ $programme->name }} ({{ $programme->code }})</h4>


    @php
      $rows = []; $tuitionTotal = 0;
      for($i=1;$i<=8;$i++){
        if(($programme->{'has_semester_'.$i} ?? 'No')==='Yes'){
          $amt = $programme->{'semester_'.$i.'_bill'} ?? 0;
          $tuitionTotal += $amt;
          $rows[] = ['item'=>"Semester {$i} Tuition", 'desc'=>"Tuition for Semester {$i}", 'amt'=>$amt];
        }
      }
      $rows[] = ['item'=>'Tuition Subtotal', 'desc'=>'','amt'=>$tuitionTotal,'sub'=>true];

      $svcTotal = 0;
      Service::where('enterprise_id',$ent->id)
        ->where('is_compulsory','Yes')->get()
        ->each(function($s)use(&$rows,&$svcTotal,$programme,$termName){
          $toAllC = $s->is_compulsory_to_all_courses==='Yes';
          $toAllS = $s->is_compulsory_to_all_semesters==='Yes';
          $courses = $s->applicable_to_courses;
          $sems    = $s->applicable_to_semesters;
          if(
            ($toAllC||in_array($programme->id,$courses))
            && ($toAllS||in_array((string)$termName,$sems))
          ){
            $svcTotal += $s->fee;
            $reason = $toAllC
              ? "Compulsory for all programmes"
              : "Compulsory for this programme";
            if(! $toAllS) $reason .= " in Semester {$termName}";
            $rows[] = ['item'=>$s->name,'desc'=>$reason,'amt'=>$s->fee];
          }
        });
      $rows[] = ['item'=>'Compulsory Services Subtotal','desc'=>'','amt'=>$svcTotal,'sub'=>true];

      $grand = $tuitionTotal + $svcTotal;
    @endphp

    <table class="table table-bordered table-sm mb-4">
      <thead>
        <tr style="background:#f8f9fa; color:#000; font-size:12px;">
          <th style="width:40%">Item</th>
          <th>Description</th>
          <th class="text-right" style="width:20%">Amount (UGX)</th>
        </tr>
      </thead>
      <tbody>
        @foreach($rows as $r)
          <tr @if(!empty($r['sub'])) class="subtotal" @endif>
            <td>{{ $r['item'] }}</td>
            <td>{{ $r['desc'] }}</td>
            <td class="text-right">{{ number_format($r['amt'],2) }}</td>
          </tr>
        @endforeach
        <tr class="subtotal">
          <td colspan="2">Grand Total</td>
          <td class="text-right">{{ number_format($grand,2) }}</td>
        </tr>
      </tbody>
    </table>

  @endforeach

  @if($ent->print_footer ?? false)
    <hr style="border:1px solid {{ $ent->color }}; margin:.5rem 0;" />
    <p class="text-center small" style="color:#000;">{{ $ent->name }} &mdash; {{ $ent->website }}</p>
  @endif

</body>
</html>