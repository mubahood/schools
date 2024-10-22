<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roll Calling</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <style>
        .container {
            margin-top: 50px;
        }

        .centered {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
    </style>
</head>

<body>
    <div class="container centered">
        <h1>Roll Calling</h1>
        <div class="form-group">
            <label for="studentId">Student ID</label>
            <input type="text" class="form-control" id="studentId" placeholder="Enter Student ID">
        </div>
        <ul class="list-group" id="studentList"></ul>
    </div>

    <script>
        $(document).ready(function() {
            $('#studentId').on('change', function() {
                var studentId = $(this).val();
                $.ajax({
                    url: 'http://localhost:8888/schools/api/get-student-details',
                    method: 'POST',
                    data: {
                        id: studentId
                    },
                    success: function(response) {
                        var studentDetails = '<li class="list-group-item">' + response.data.name +
                            ' - ' + response.data.created_at + '</li>';
                        $('#studentList').append(studentDetails);
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
