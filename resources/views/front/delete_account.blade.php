<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account delete Request</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <!-- Favicons -->
    <link href="" rel="icon">
    <meta name="_token" content="{{ csrf_token() }}">
    <meta name="project-key" content="{{ env('PROJECT_KEY') }}">
    <meta name="apiUrl" content="{{ env('APP_URL') . 'api/v1/' }}">

    <link href="" rel="apple-touch-icon">
    <link rel="stylesheet" href="https://commodity.ecoex.market/public/material/backend/lightbox/lightbox.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/css/toastr.css" rel="stylesheet" />
    <script type="text/javascript"
        src="https://ff.kis.v2.scr.kaspersky-labs.com/FD126C42-EBFA-4E12-B309-BB3FDD723AC1/main.js?attr=1A9JMWlvLvNu0u-GPLe2pusfM6tZuy-ftpRi_Lfcd9rdSAV9UXLMik163535OXsluPQ8Eo9Jh9coX2nH0xxhsOr3R4L2ytVF4G1vkGwOGx4"
        charset="UTF-8"></script>
    <style type="text/css">
        .toast-success {
            background-color: #000;
            color: #28a745 !important;
        }

        .toast-error {
            background-color: #000;
            color: #dc3545 !important;
        }

        .toast-warning {
            background-color: #000;
            color: #ffc107 !important;
        }

        .toast-info {
            background-color: #000;
            color: #007bff !important;
        }

        .tablelook {
            padding-top: 10px;
            padding-bottom: 10px;
            border: 1px solid #dddd;
        }

        ul.d-flex.whatimgs {
            flex-wrap: wrap;
            list-style: none;
            padding: 0;
        }

        .whatimgs li {
            margin: 5px;
        }

        .whatimgs img.example-image {
            max-width: 60px;
            border: 1px solid #999;
            height: 60px;
            width: 100%;
        }
    </style>
</head>

<body>
    <div class="container mt-4">

        <h3 class="text-center">
            <img src="{{ env('UPLOADS_URL') . $generalSetting->site_logo }}" alt="Net-Works"
                style="width: 100;height:100px">
            <p class="mt-3">Delete Account Request</p>
        </h3>
        <div class=" justify-content-center d-flex">
            <div class="row col-md-8 col-sm-12 col-xs-12">
                <form method="POST" id="form_submit"
                    style="border: 1px solid #48974e73;padding: 15px;border-radius: 5px;">
                    <input type="hidden" name="entity_name" id="entity_name">


                    <div class="form-group mb-3">
                        <div class="row align-items-end">
                            <div class="col-md-10">
                                <label for="phone" class="fw-bold">Phone Number</label>
                                <input type="text" class="form-control" id="phone" name="phone"
                                    placeholder="Phone Number" maxlength="10" minlength="10" required
                                    onkeypress="return isNumber(event)">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-primary btn-sm w-100" id="phone-otp-btn">Get
                                    Phone OTP</button>

                                <input type="hidden" id="user_phone" name="user_phone" value="">
                                <input type="hidden" name="generated_phone_otp" id="generated_phone_otp"
                                    onkeypress="return isNumber(event)">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-3" id="phone_otp_row">
                        <div class="row align-items-end">
                            <div class="col-md-10">
                                <label for="phone_otp" class="fw-bold">Phone OTP</label>
                                <input type="text" class="form-control" id="phone_otp" name="phone_otp"
                                    placeholder="Phone OTP" maxlength="6" minlength="4" required>

                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-info btn-sm w-100"
                                    id="phone-validate-btn">Validate Phone</button>
                                <input type="hidden" name="is_phone_verify" id="is_phone_verify" value="0">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label for="comments" class="fw-bold">Delete Reason</label>
                        <textarea class="form-control" id="reason" name="reason" placeholder="Reason.." required></textarea>
                    </div>
                    <div class="form-group mb-3">
                        <button type="submit" class="btn btn-success" id="submit-btn"><i class="fa fa-paper-plane"></i>
                            Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
    <script src="https://commodity.ecoex.market/public/material/backend/lightbox/lightbox.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/js/toastr.js"></script>



    <script type="text/javascript">
        $(document).ready(function() {
            $('#submit-btn').attr('disabled', true);

            const ApiUrl = $('meta[name="apiUrl"]').attr("content")

            $.ajaxSetup({
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="_token"]').attr("content"),
                    "key": $('meta[name="project-key"]').attr("content") // Custom header
                },
            });

            lightbox.option({
                'resizeDuration': 200,
                'wrapAround': true
            })

            function isNumber(evt) {
                evt = (evt) ? evt : window.event;
                var charCode = (evt.which) ? evt.which : evt.keyCode;
                if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                    return false;
                }
                return true;
            }


            function toastAlert(type, message, redirectStatus = false, redirectUrl = '') {
                toastr.options = {
                    "closeButton": true,
                    "debug": true,
                    "newestOnTop": false,
                    "progressBar": true,
                    "positionClass": "toast-bottom-left",
                    "preventDuplicates": false,
                    "showDuration": "3000",
                    "hideDuration": "1000000",
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showEasing": "swing",
                    "hideEasing": "linear",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                }
                toastr[type](message);
                if (redirectStatus) {
                    setTimeout(function() {
                        window.location = redirectUrl;
                    }, 3000);
                }
            }




            $('#phone-otp-btn').on('click', function() {

                var phone = $('#phone').val();

                if (phone != '') {

                    $.ajax({
                        method: 'POST',
                        url: ApiUrl + 'profile/remove/getOtp',
                        data: {
                            phone: phone
                        },
                        success: function(response) {
                            if (response.status) {
                                toastAlert('success', response.message);
                                // $("#phone").val('');

                                $("#user_phone").val(phone);

                            } else {
                                toastAlert('error', response.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error occurred:', error);
                            toastAlert('error', 'An unexpected error occurred.');
                        }
                    });


                } else {
                    toastAlert('error', 'Please Enter Phone No. !!!');
                }

            });

            $('#phone-validate-btn').on('click', function() {
                let phone_otp = parseInt($('#phone_otp').val());
                let phone = $('#user_phone').val();
                if (phone_otp != '') {

                    $.ajax({
                        method: 'POST',
                        url: ApiUrl + 'profile/remove/validate-otp',
                        data: {
                            otp: phone_otp,
                            phone: phone
                        },
                        success: function(response) {
                            if (response.status) {
                                toastAlert('success', response.message);
                                $('#is_phone_verify').val(response.data.is_phone_verify);

                                $('#submit-btn').attr('disabled', false);
                            } else {
                                toastAlert('error', response.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error occurred:', error);
                            toastAlert('error', 'An unexpected error occurred.');
                        }
                    });



                } else {
                    toastAlert('error', 'Please Enter Otp. !!!');
                }
            });


            $("#form_submit").submit(function(e) {
                e.preventDefault()
                let reason = $("#reason").val();

                let is_phone_verify = $("#is_phone_verify").val();

                if (reason != '') {

                    $.ajax({
                        method: 'POST',
                        url: ApiUrl + 'profile/remove/delete-account',
                        data: {
                            reason: reason,
                            verify:is_phone_verify
                        },
                        success: function(response) {
                            if (response.status) {
                                toastAlert('success', response.message);
                                $('#phone').val("");
                                $('#phone_otp').val("")
                                $("#reason").val("");
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1500);
                            } else {
                                toastAlert('error', response.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error occurred:', error);
                            toastAlert('error', 'An unexpected error occurred.');
                        }
                    });

                }

            })
        })
    </script>
</body>

</html>
