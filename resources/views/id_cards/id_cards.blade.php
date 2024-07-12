<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>ID Cards</title>
    @include('print.css')
    <style>
        /* margin */
        body {
            margin: 0 !important;
            padding: 0 !important;
            margin-top: 15 !important;
        }
    </style>
    <style>
        .label {
            font-size: 10px;
            font-weight: bold;
            color: #6c757d;
            line-height: 12px;
            padding: 0;
            margin: 0;
            padding-bottom: 2px;
        }

        .value {
            font-size: 14px;
            font-weight: bold;
            color: black;
            line-height: 13px;
            padding: 0;
            margin: 0;
            padding-bottom: 5px;
        }
    </style>

</head>

<body>
    <?php
    $per_page = 0;
    ?>
    @foreach ($users as $user)
        @php
            $per_page++;
            if ($per_page == 5) {
                $per_page = 0;
                echo '<div class="page-break"></div>';
            }
        @endphp
        @if ($idCard->template == 'template_1')
            @include('id_cards.template-1', [
                'user' => $user,
            ])
        @elseif ($idCard->template == 'template_2')
            @include('id_cards.template-2', [
                'user' => $user,
            ])
        @elseif ($idCard->template == 'template_3')
            @include('id_cards.template-3', [
                'user' => $user,
            ])
        @else
            @include('id_cards.template-1', [
                'user' => $user,
            ])
        @endif
    @endforeach 
</body>

</html>
