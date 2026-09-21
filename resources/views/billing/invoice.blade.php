{{-- PDF wrapper. The body is shared with every on-screen preview. --}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>{{ $inv->number }} {{ $ent->name }}</title>
@include('billing._invoice-css', ['pdf' => true])
</head>
<body>
@include('billing._invoice', ['pdf' => true])
</body>
</html>
