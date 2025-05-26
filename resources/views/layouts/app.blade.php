<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        Budget Tracking
    </title>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('images/3x3_new.png') }}" rel="icon">

    <style>
        body {
            position: relative;
            margin: 0;
            height: 100vh;
            overflow: hidden;
        }

        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0.3;
            z-index: -1;
        }

        .main-content {
            width: 50%;
            border-radius: 20px;
            box-shadow: 0 5px 5px rgba(0, 0, 0, .4);
            margin: 5em auto;
            display: flex;
        }

        .company__info {
            background-color: rgba(29, 70, 128, 0.6);
            border-top-left-radius: 20px;
            border-bottom-left-radius: 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: #fff;
        }

        .fa-android {
            font-size: 3em;
        }

        @media screen and (max-width: 640px) {
            .main-content {
                width: 90%;
            }

            .company__info {
                display: none;
            }

            .login_form {
                border-top-left-radius: 20px;
                border-bottom-left-radius: 20px;
            }
        }

        @media screen and (min-width: 642px) and (max-width:800px) {
            .main-content {
                width: 70%;
            }
        }

        .row>h2 {
            color: #1d4680;
        }

        .login_form {
            background-color: rgba(255, 255, 255, 0.6);
            border-top-right-radius: 20px;
            border-bottom-right-radius: 20px;
            border-top: 1px solid rgba(204, 204, 204, 0.8);
            border-right: 1px solid rgba(204, 204, 204, 0.8);
        }

        form {
            padding: 0 2em;
        }

        .form__input {
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 25px;
            padding: 1em .5em .5em;
            padding-left: 2em;
            outline: none;
            margin: 1.5em auto;
            transition: all .5s ease;
        }

        .form__input:focus {
            border-bottom-color: #008080;
            box-shadow: 0 0 5px rgba(0, 80, 80, .4);
            border-radius: 4px;
        }

        .btn {
            width: 70%;
            border-radius: 30px;
            color: #fff;
            font-weight: 600;
            background-color: #1d4680;
            border: none;
            margin-top: 1.5em;
            margin-bottom: 1em;
        }

        .btn:hover,
        .btn:focus {
            background-color: #1d4680;
            color: #fff;
        }

        .form__input.error {
            border-color: red;
            background-color: #ffe6e6;
            box-shadow: 0 0 5px rgba(255, 0, 0, 0.5);
        }

        /* Style for error message */
        .error-message {
            color: red;
            font-size: 0.9em;
            display: block;
            margin-top: 0.5em;
        }
    </style>
</head>

<body style="padding-top: 1px;">
    <!-- Main Content -->
    <div class="container-fluid">
        <div class="row main-content text-center">
            <div class="col-md-4 text-center company__info">
                <center><img src="{{ asset('psrti_logo_new.png') }}" alt="PSRTI Logo" width="70%"></center>
            </div>
            <div class="col-md-8 col-xs-12 col-sm-12 login_form">
                <div class="container-fluid"><br>
                    <!-- start content here -->
                    @yield('content')
                    <!-- end content here -->
                </div>
            </div>
        </div>
    </div>
    <!-- Footer -->
    <div class="container-fluid text-center footer" style="font-family: 'Courier New', monospace;">
        v1.0
    </div>
</body>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

</html>
