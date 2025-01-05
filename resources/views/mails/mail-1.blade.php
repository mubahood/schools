<?php
if (!isset($body)) {
    $body = 'Hello Muhindo Mubarka, Use the following to reset your taskease password.';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ env('APP_NAME') }}</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css"
        integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">

    <!-- Custom Styles -->
    <style>
        /* create css var primary color as rgb(230, 207, 0) */
        :root {
            --primary-color: rgb(0, 145, 235);
        }

        body {
            font-family: Arial, sans-serif;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 10px;
        }

        .header {
            background-color: var(--primary-color);
            color: #fff;
            text-align: center;
            padding: 10px;
        }

        .content {
            padding: 20px;
            background-color: #fff;
            color: #424649;
        }

        .footer {
            background-color: white;
            text-align: center;
            padding: 10px;
            padding-top: 20px;
        }

        .text-primary {
            color: var(--primary-color) !important;
        }

        .text-primary-2 {
            color: white !important;
            font-weight: 900;
        }

        .my-hr {
            margin: 0;
            height: 0px;
        }

        .my-title {
            font-size: 1.5rem;
            font-weight: 900;
            line-height: 1;
            text-transform: uppercase;
        }
    </style>
</head>

<body style="background-color: #f7efdd">

    <div class="email-container" style="background-color: #f7efdd;">
        <!-- Header -->
        <div class="footer" {{-- style="border-bottom: 2px solid  #AB7602" --}}>
            <h2 style="color: rgb(0, 0, 50);" class="my-title">{{ env('APP_NAME') }}</h2>
        </div>
        <div class="my-hr" style="border: 3px solid  black"></div>
        <!-- Content -->
        <div class="content"
            style="
        font-family: 'Trebuchet MS', 'Lucida Sans Unicode', 'Lucida Grande', 'Lucida Sans', Arial, sans-serif;
        font-size: 16px;
        ">
            {!! $body !!}
        </div>
        <a href="https://schooldynamics.ug/">
            <div class="header small">
                <div class="d-flex justify-content-center" style="font-size: 12px">
                    <a href="https://schooldynamics.ug"
                        class=" mx-2 text-primary-2 text-uppercase">{{ strtoupper('About School Dyamics') }}</a>
                    &nbsp;•&nbsp;
                    <a href="https://schooldynamics.ug"
                        class=" mx-2 text-primary-2 text-uppercase">{{ strtoupper('Mobile App') }}</a> &nbsp;•&nbsp;
                    <a href="https://schooldynamics.ug"
                        class=" mx-2 text-primary-2 text-uppercase">{{ strtoupper('Web Portal') }}</a> &nbsp;•&nbsp;
                    <a href="https://schooldynamics.ug"
                        class=" mx-2 text-primary-2 text-uppercase">{{ strtoupper('Contact Us') }}
                    </a>
                </div>
            </div>
        </a>
    </div>
</body>

</html>
