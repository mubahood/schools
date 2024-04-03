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
    @foreach ($users as $user)
        @include('id_cards.template-1', [
            'user' => $user,
        ])
    @endforeach
</body>

</html>
