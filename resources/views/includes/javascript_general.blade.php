<script>
    window.paceOptions = {
        ajax: false,
        restartOnRequestAfter: false,
    };
</script>
<script src="{{ asset('public/js/core.min.js') }}?v={{$settings->version}}"></script>
<script src="{{ asset('public/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('public/js/jqueryTimeago_'.Lang::locale().'.js') }}"></script>
<script src="{{ asset('public/js/lazysizes.min.js') }}" async=""></script>
<script src="{{ asset('public/js/plyr/plyr.min.js') }}?v={{$settings->version}}"></script>
<script src="{{ asset('public/js/plyr/plyr.polyfilled.min.js') }}?v={{$settings->version}}"></script>
<script src="{{ asset('public/js/app-functions.js') }}?v={{$settings->version}}"></script>
<script src="{{ asset('public/js/smartphoto.min.js') }}"></script>
<script src="https://download.agora.io/sdk/release/AgoraRTC_N.js"></script>
<script src="{{ asset('public/js/live/basicMute.js') }}"></script>
<script src="{{ asset('public/js/live/rtm.js') }}"></script>
<script src="{{ asset('public/js/bank-details.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>


<script src="{{ asset('public/datetime/bootstrap-datetimepicker.min.js') }}"></script>
<script src="{{ asset('public/plugins/datepicker/bootstrap-datepicker.js') }}"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>


@auth
<script src="https://js.stripe.com/v3/"></script>
<script src='https://checkout.razorpay.com/v1/checkout.js'></script>
<script src='https://js.paystack.co/v1/inline.js'></script>
@if (request()->is('my/wallet'))
<script src="{{ asset('public/js/add-funds.js') }}?v={{$settings->version}}"></script>
@else
<script src="{{ asset('public/js/payment.js') }}?v={{$settings->version}}"></script>
<script src="{{ asset('public/js/payments-ppv.js') }}?v={{$settings->version}}"></script>
@endif
@endauth

@if ($settings->custom_js)
<script type="text/javascript">
    {
        !! $settings - > custom_js !!}
</script>
@endif


<script>
    window.onload = function () {
        if (performance.navigation.type == 2) {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var _token = $('input[name="_token"]').val();
            $.ajax({
                type: "POST",
                url: 'startStreaming',
                datatype: "json",
                data: {status: 0, _token: _token},
                success: function (response) {
                    console.log(response.message);
                }
            });

            console.log("client leaves channel success");

        }
    }

//    $(function () {
//        $("#start-date").datetimepicker({
//            dateFormat: "yyyy-mm-dd hh:ii",
//            startDate: new Date(),
//            autoclose: true,
//        });
//
//        $("#end-date").datetimepicker({
//            dateFormat: "yyyy-mm-dd hh:ii",
//            startDate: new Date(),
//            autoclose: true,
//        });
//
//        $("#birthdate").datepicker({
//            autoclose: true,
//        });
//    });

    $(function () {
        $('#start-date').datetimepicker({
            format: "yyyy-mm-dd hh:ii",
            autoclose: true,
            startDate: new Date(),
            onShow: function (ct) {
                this.setOptions({
                    maxDate: $('#end-date').val() ? $('#end-date').val() : false
                })
            },
        });
//        $('#end-date').datetimepicker({
//            format: "yyyy-mm-dd hh:ii",
//            autoclose: true,
//            startDate: new Date(),
//            onShow: function (ct) {
//                this.setOptions({
//                    minDate: $('#start-date').val() ? $('#start-date').val() : false
//                })
//            },
//        });
    });


   


    function checkInterest(interest, user_id, event_id) {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var _token = $('input[name="_token"]').val();
        $.ajax({
            type: "POST",
            url: "{{route('countAdd')}}",
            datatype: "json",
            data: {interest: interest, user_id: user_id, event_id: event_id, _token: _token},
            success: function (response) {
                if (response.result == '1') {
                    console.log(response.message);

                    if (interest == 'interested') {
                        $('.dropdownMenuButton_' + event_id).html('<i class="fa fa-star" aria-hidden="true"></i> Interested');
                        $('.dropdownMenuButton_' + event_id).css('color', '#c56cf0');

                    } else if (interest == 'going') {
                        $('.dropdownMenuButton_' + event_id).html('<i class="fa fa-check-circle" aria-hidden="true"></i> Going');
                        $('.dropdownMenuButton_' + event_id).css('color', '#c56cf0');

                    } else if (interest == 'not_interested') {
                        $('.dropdownMenuButton_' + event_id).html('<i class="fas fa-times-circle" aria-hidden="true"></i> Not Interested');
                        $('.dropdownMenuButton_' + event_id).css('color', '#c56cf0');
                    }

                    $("#showCountedInterest_" + event_id).load(window.location.href + " #showCountedInterest_" + event_id);


                }
            }
        });


        //  $('#dropdownMenuButton').text(interest);

    }

</script>

@if (auth()->guest()
&& ! request()->is('password/reset')
&& ! request()->is('password/reset/*')
&& ! request()->is('contact')
)
<script type="text/javascript">

    //<---------------- Login Register ----------->>>>

    _submitEvent = function () {
        sendFormLoginRegister();
    };

    if (captcha == false) {

        $(document).on('click', '#btnLoginRegister', function (s) {

            s.preventDefault();
            sendFormLoginRegister();

        });//<<<-------- * END FUNCTION CLICK * ---->>>>
        
        $(document).on('click', '#loginSessionCheck', function (s) {

            s.preventDefault();
            sendFormLoginRegister();

        });
    }
    
    

    function sendFormLoginRegister()
    {
        var element = $(this);
        $('#btnLoginRegister').attr({'disabled': 'true'});
        $('#btnLoginRegister').find('i').addClass('spinner-border spinner-border-sm align-middle mr-1');

        (function () {
            $("#formLoginRegister").ajaxForm({
                dataType: 'json',
                success: function (result) {

                    // success
                    if (result.success == true) {

                        if (result.isModal && result.isLoginRegister) {
                            window.location.reload();
                        }

                        if (result.url_return && !result.isModal) {
                            window.location.href = result.url_return;
                        }

                        if (result.check_account) {
                            $('#checkAccount').html(result.check_account).fadeIn(500);

                            $('#btnLoginRegister').removeAttr('disabled');
                            $('#btnLoginRegister').find('i').removeClass('spinner-border spinner-border-sm align-middle mr-1');
                            $('#errorLogin').fadeOut(100);
                            $("#formLoginRegister").reset();
                        }

                    } else {

                        if (result.errors) {

                            var error = '';
                            var $key = '';

                            for ($key in result.errors) {
                                error += '<li><i class="far fa-times-circle"></i> ' + result.errors[$key] + '</li>';
                            }

                            $('#showErrorsLogin').html(error);
                            $('#errorLogin').fadeIn(500);
                            $('#btnLoginRegister').removeAttr('disabled');
                            $('#btnLoginRegister').find('i').removeClass('spinner-border spinner-border-sm align-middle mr-1');
                        } else if (result.account_errors) {

                            var error = '';
                            var $key = '';

                            for ($key in result.account_errors) {
                                error += '<li><i class="far fa-times-circle"></i> ' + result.account_errors[$key] + '</li>';
                            }
                            error += '<li><button type="submit" id="loginSessionCheck" class="btn btn-secondary">Sign out &amp; Continue</button> \n\
                            <a href="login" class="btn btn-secondary">Okay</a>'

                            $('#status').val(1);
                            
                            $('#showErrorsLogin').html(error);
                            $('#errorLogin').fadeIn(500);
                            $('#btnLoginRegister').removeAttr('disabled');
                            $('#btnLoginRegister').find('i').removeClass('spinner-border spinner-border-sm align-middle mr-1');
                        }
                    }

                },
                error: function (responseText, statusText, xhr, $form) {
                    // error
                    $('#btnLoginRegister').removeAttr('disabled');
                    $('#btnLoginRegister').find('i').removeClass('spinner-border spinner-border-sm align-middle mr-1');
                    swal({
                        type: 'error',
                        title: error_oops,
                        text: error_occurred + ' (' + xhr + ')',
                    });
                }
            }).submit();
        })(); //<--- FUNCTION %
    }// End function sendFormLoginRegister


//    function sendLoginAnotherDevice()
//    {
//        var element = $(this);
//        $('#btnLoginRegister').attr({'disabled': 'true'});
//        $('#btnLoginRegister').find('i').addClass('spinner-border spinner-border-sm align-middle mr-1');
//
//        (function () {
//            $("#formLoginRegister").ajaxForm({
//                dataType: 'json',
//                data: {status: 1},
//                success: function (result) {
//
//                    // success
//                    if (result.success == true) {
//
//                        if (result.isModal && result.isLoginRegister) {
//                            window.location.reload();
//                        }
//
//                        if (result.url_return && !result.isModal) {
//                            window.location.href = result.url_return;
//                        }
//
//                        if (result.check_account) {
//                            $('#checkAccount').html(result.check_account).fadeIn(500);
//
//                            $('#btnLoginRegister').removeAttr('disabled');
//                            $('#btnLoginRegister').find('i').removeClass('spinner-border spinner-border-sm align-middle mr-1');
//                            $('#errorLogin').fadeOut(100);
//                            $("#formLoginRegister").reset();
//                        }
//
//                    } else {
//
//                        if (result.errors) {
//
//                            var error = '';
//                            var $key = '';
//
//                            for ($key in result.errors) {
//                                error += '<li><i class="far fa-times-circle"></i> ' + result.errors[$key] + '</li>';
//                            }
//
//                            $('#showErrorsLogin').html(error);
//                            $('#errorLogin').fadeIn(500);
//                            $('#btnLoginRegister').removeAttr('disabled');
//                            $('#btnLoginRegister').find('i').removeClass('spinner-border spinner-border-sm align-middle mr-1');
//                        } else if (result.account_errors) {
//
//                            var error = '';
//                            var $key = '';
//
//                            for ($key in result.account_errors) {
//                                error += '<li><i class="far fa-times-circle"></i> ' + result.account_errors[$key] + '</li>';
//                            }
//                            error += '<li><a  href="javascript:;" id="loginSessionCheck" class="btn btn-secondary">Sign out &amp; Continue</a>\n\
//                             <a href="login" class="btn btn-secondary">Okay</a>'
//
//                            $('#showErrorsLogin').html(error);
//                            $('#errorLogin').fadeIn(500);
//                            $('#btnLoginRegister').removeAttr('disabled');
//                            $('#btnLoginRegister').find('i').removeClass('spinner-border spinner-border-sm align-middle mr-1');
//                        }
//                    }
//
//                },
//                error: function (responseText, statusText, xhr, $form) {
//                    // error
//                    $('#btnLoginRegister').removeAttr('disabled');
//                    $('#btnLoginRegister').find('i').removeClass('spinner-border spinner-border-sm align-middle mr-1');
//                    swal({
//                        type: 'error',
//                        title: error_oops,
//                        text: error_occurred + ' (' + xhr + ')',
//                    });
//                }
//            }).submit();
//        })(); //<--- FUNCTION %
//    }

</script>

@endif

<script type="text/javascript">

//    $(document).on('click', '#loginSessionCheck', function (s) {
//
//        s.preventDefault();
//        sendLoginAnotherDevice();
//
//    });
    
    
    function sendLoginAnotherDevice() {

            $.ajax({
                type: "POST",
                url: "login",
                datatype: "json",
                data: $('#formLoginRegister').serialize(),
                success: function (result) {

                    // success
                    if (result.success == true) {

                        if (result.isModal && result.isLoginRegister) {
                            window.location.reload();
                        }

                        if (result.url_return && !result.isModal) {
                            window.location.href = result.url_return;
                        }

                        if (result.check_account) {
                            $('#checkAccount').html(result.check_account).fadeIn(500);

                            $('#btnLoginRegister').removeAttr('disabled');
                            $('#btnLoginRegister').find('i').removeClass('spinner-border spinner-border-sm align-middle mr-1');
                            $('#errorLogin').fadeOut(100);
                            $("#formLoginRegister").reset();
                        }

                    } else {

                        if (result.errors) {

                            var error = '';
                            var $key = '';

                            for ($key in result.errors) {
                                error += '<li><i class="far fa-times-circle"></i> ' + result.errors[$key] + '</li>';
                            }

                            $('#showErrorsLogin').html(error);
                            $('#errorLogin').fadeIn(500);
                            $('#btnLoginRegister').removeAttr('disabled');
                            $('#btnLoginRegister').find('i').removeClass('spinner-border spinner-border-sm align-middle mr-1');
                        } else if (result.account_errors) {

                            var error = '';
                            var $key = '';

                            for ($key in result.account_errors) {
                                error += '<li><i class="far fa-times-circle"></i> ' + result.account_errors[$key] + '</li>';
                            }
                            error += '<li><a  href="javascript:;" id="loginSessionCheck" class="btn btn-secondary">Sign out &amp; Continue</a>\n\
                             <a href="login" class="btn btn-secondary">Okay</a>'

                            $('#showErrorsLogin').html(error);
                            $('#errorLogin').fadeIn(500);
                            $('#btnLoginRegister').removeAttr('disabled');
                            $('#btnLoginRegister').find('i').removeClass('spinner-border spinner-border-sm align-middle mr-1');
                        }
                    }

                },
                error: function (responseText, statusText, xhr, $form) {
                    // error
                    $('#btnLoginRegister').removeAttr('disabled');
                    $('#btnLoginRegister').find('i').removeClass('spinner-border spinner-border-sm align-middle mr-1');
                    swal({
                        type: 'error',
                        title: error_oops,
                        text: error_occurred + ' (' + xhr + ')',
                    });
                }
            });

        }



</script>




<script>
//        $(document).ready(function(){
//            $('#submitEvent').click(function(e){
//                e.preventDefault();
//                $.ajaxSetup({
//                    headers: {
//                        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
//                    }
//                });
//                var _token = $('input[name="_token"]').val();
//                var event_image = $("#uploadEventImg").val();
//                var event_name = $("#event-name").val();
//                var start_date = $("#start-date").val();
//                var end_date = $("#end-date").val();
//                var event_place = $("#event-place").val();
//                var event_type = $("#event-type").val();
//                
//                $.ajax({
//                    url: "{{ url('upload/event') }}",
//                    method: 'post',
//                    dataType: 'json',
//                    data: {event_image: event_image, event_name: event_name, start_date: start_date, end_date: end_date, event_place: event_place, event_type: event_type, _token: _token},
//                    success: function(result){
//                        
//                        
//                         if (result.success == true) {
//                             
//                             $('.alert-danger').hide();
//                            $('#eventForm').modal('hide');
//                            console.log('hii');
//                            
//                         }
//                         else {
//
//                        if(result.errors)
//                        {
//                            $('.alert-danger').html('');
//                            
//                            var error = '';
//                            var $key = '';
//
//                            for ($key in result.errors) {
//                                error += '<li><i class="far fa-times-circle"></i> ' + result.errors[$key] + '</li>';
//                            }
//
//                           // $.each(result.errors, function(key, value){
//                                $('.alert-danger').show();
//                                $('.alert-danger').append('<li>'+error+'</li>');
//                           // });
//                        }
//                         
//                        }
//                    }
//                });
//            });
//        });
</script>


<SCRIPT type="text/javascript">
    function ValidateFileUpload() {
        var fuData = document.getElementById('uploadEventImg');
        var FileUploadPath = fuData.value;

//To check if user upload any file
        if (FileUploadPath == '') {
            document.getElementById("event_image_error").innerHTML = "Please upload an image";

        } else {

            var imgwidth = 0;
            var imgheight = 0;
            var maxwidth = 300;
            var maxheight = 150;

            var Extension = FileUploadPath.substring(
                    FileUploadPath.lastIndexOf('.') + 1).toLowerCase();

//The file uploaded is an image

            if (Extension == "gif" || Extension == "png" || Extension == "jpe" || Extension == "jpeg" || Extension == "jpg") {

// To Display
                if (fuData.files && fuData.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function (e) {
                        
                        var image = new Image();
                        
                        //Set the Base64 string return from FileReader as source.
                image.src = e.target.result;
                
                image.onload = function () {

                        imgwidth = this.width;
                        imgheight = this.height;

                        if (imgwidth <= maxwidth && imgheight <= maxheight) {


                            $('#blah').css('display', 'inline-block');
                            $('#blah').attr('src', e.target.result);
                            
                            
                            $(".event_img").css('display', 'none');
                            
                           // document.getElementsByClassName("event_img").style.display = "none";
                            
                            $(".img_remove_btn").css('display', 'inline-block');
                            document.getElementById("event_image_error").innerHTML = "";

                        } else {

                            $('#blah').css('display', 'none');
                            $('#blah').attr('src', '');
                            document.getElementById("event_image_error").innerHTML = "Image size must be " + maxwidth + "X" + maxheight;

                        }
                    };
                    
    }

                    reader.readAsDataURL(fuData.files[0]);
                }

            }

//The file upload is NOT an image
            else {
                $('#blah').css('display', 'none');
                            $('#blah').attr('src', '');
                document.getElementById("event_image_error").innerHTML = "Please select a valid image file";

            }
        }
    }
    
    
    function remove_event_img() {
        
        $('#uploadEventImg').val('');
        $('#blah').attr('src', '');
        $('#blah').css('display', 'none');
        $(".event_img").css('display', 'inline-block');
        $(".img_remove_btn").css('display', 'none');
    }
</SCRIPT>

