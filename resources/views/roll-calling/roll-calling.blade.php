@php
    $ent = $session->ent;
    $participants = $session->participant_items;
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roll Calling</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/jquery-toast-plugin/1.3.2/jquery.toast.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-toast-plugin/1.3.2/jquery.toast.min.js"></script>

    <style>
        /* define primary color */
        :root {
            --primary-color: {{ $ent->color }};
        }

        .container {
            margin-top: 50px;
        }

        .centered {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .header {
            display: flex;
            justify-content: center;
            flex-direction: column;
            background-color: var(--primary-color);
            color: white;
            /* display flex */
        }

        .my-input {
            border: 5px solid var(--primary-color);
            border-radius: 0px;
            padding: 15px !important;
            text-align: center;
        }

        .my-input:focus {
            border: 5px solid var(--primary-color);
            border-radius: 0px;
            padding: 5px;
            margin-top: 10px;
            /* disable shadow */
            -webkit-box-shadow: none;
            box-shadow: none;
            text-align: center;
        }

        .my-item {
            border: 4px solid rgb(195, 188, 188);
            padding-top: 10px;
            padding-left: 13px;
            padding-right: 10px;
            padding-bottom: 10px;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="container p-0 mt-0">
        <div class="header pl-4 pt-2 pb-1">
            <div class=" row">
                <div class="bg-info" style="width: 45px;">
                    <img src="{{ url('storage/' . $ent->logo) }}" alt="{{ $ent->name }}" class="img-fluid">
                </div>
                <div class="col">
                    <h1 class="m-0 p-0 pl-0">
                        {{ strtoupper($ent->name) }}
                    </h1>
                </div>
            </div>
        </div>
        <h5 class="text-center mt-2">
            {{ $session->title }}
        </h5>
        <div class="form-group">
            <input type="text" class="form-control my-input" id="studentId" autofocus
                style="
            border: 5px solid var(--primary-color);
            border-radius: 0px;
            padding: 15px!important; 
            "
                placeholder="Scan or enter student ID">
        </div>


        <h4 class="text-uppercase">{{ 'Students Present:' }} <span
                id="present-count">({{ $participants->count() }})</span></h4>

        <a href="{{ url('roll-calling-close-session?roll_call_session_id=' . $session->id) }}" class="text-danger"><b>Close
                Session</b></a>
        <div id="studentList">
            @php
                $int = $participants->count();
                $int++;
            @endphp
            @foreach ($participants as $participant)
                @php
                    if ($participant->participant == null) {
                        continue;
                    }
                    $int--;
                @endphp
                <div class="my-item">
                    <b>{{ $int }}</b>. {{ $participant->participant->name }}
                </div>
            @endforeach
        </div>
    </div>

    <script>
        $(document).ready(function() {

            $toastParams = {
                heading: '',
                text: 'If set to false, there will be only one toast at once.',
                position: 'top-center',
                bgColor: 'red',
            };
            $('#studentId').on('keyup', function() {
                var studentId = $(this).val().trim();
                if (studentId.length < 12) {
                    return;
                }

                $.ajax({
                    url: '{{ url('api/roll-call-participant-submit') }}',
                    method: 'POST',
                    data: {
                        'user_number': studentId,
                        'session_id': '{{ $session->id }}',
                    },
                    success: function(response) {
                        // reset
                        $('#studentId').val('');
                        if (response.code != 1) {
                            $toastParams.heading = 'Error';
                            $toastParams.text = response.message;
                            $.toast($toastParams);
                            return;
                        }

                        $.toast({
                            heading: 'Success',
                            text: response.data.administrator_text +
                                ' Successfully Registered',
                            position: 'top-center',
                            bgColor: 'green',
                        });
                        var studentDetails = '<div class="my-item">' +
                            '<b>' + response.data.num + '</b>. ' +
                            response.data
                            .administrator_text + '</div>';
                        $('#present-count').text('(' + response.data.num + ')');
                        $('#studentList').prepend(studentDetails);
                    },
                    error: function() {
                        alert('Error fetching student details');
                    }
                });
            });
        });
    </script>
</body>

</html>
