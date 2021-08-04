@extends('layouts.app')

@section('title') {{trans('Join Live Streaming')}} -@endsection


<style>
    nav {
        display:none !important;
    }
    footer {
        display:none !important;
    }
    #removeFooterLive{
        display:none !important;
    }


</style>

@section('javascript')
<script type="text/javascript">

    window.onload = function () {
        $("#audience-join").click();
        $("#login").click();

    }

    $(document).ready(function () {

        setTimeout(function () {
            $('#join').trigger('click');
        }, 5000);

        setTimeout(function () {
            $('#join').trigger('click');
        }, 25000);

    });


    $(".leave_stream").click(function (e) {
        
        $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    var _token = $('input[name="_token"]').val();
    $.ajax({
        type: "post",
        url: "{{route('startStreaming')}}",
        datatype: "json",
        data: {status: 0, _token: _token},
        success: function (response) {
            console.log(response.message);
        }
    });
    leave();

    console.log("client leaves channel success");
        location.href = " {{ url('/') }}";
    });

//    $(".coHostLeave").click(function (e) {
//
//        var retVal = confirm("Are you sure you want to end your live video ?");
//        if (retVal == true) {
//
//            leaveCohost();
//
//
//            $.ajaxSetup({
//                headers: {
//                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//                }
//            });
//            var _token = $('input[name="_token"]').val();
//            var streamerid = $('#streamerid').val();
//            $.ajax({
//                type: "post",
//                url: "{{route('leaveCoHost')}}",
//                datatype: "json",
//                data: {status: 0, streamerid: streamerid, _token: _token},
//                success: function (response) {
//                    console.log(response.message);
//                    $('#videobutton').css('display', 'none');
//                }
//            });
//
//            return true;
//        } else {
//            return false;
//        }
//    });



    function CheckCohostRemove() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var _token = $('input[name="_token"]').val();
        var streamerid = $('#streamerid').val();
        $.ajax({
            type: "POST",
            url: "{{route('CheckCohostRemove')}}",
            datatype: "json",
            data: {streamerid: streamerid, _token: _token},
            success: function (response) {
                //  console.log(response.result);
                if (response.result == 'success') {
                    // console.log(response.result);
                } else if (response.result == 'leave') {
                    // $(".coHostLeave").click(); 
                    console.log(response.result);
                    leaveCohost();
                    $('#videobutton').css('display', 'none');
                    var CheckAvailPlayer = $(".checkhost div.mainPlayer").length;
                    console.log('CheckAvailPlayer' + CheckAvailPlayer);
                    if (CheckAvailPlayer == 2) {
                        $(".checkhost").addClass("twoCoHostJoin");
                    } else if (CheckAvailPlayer == 1) {
                        $(".checkhost").removeClass("twoCoHostJoin");
                        $(".checkhost").removeClass("twoHostJoin");
                    }
                    clearInterval(interval);
                }
            }
        });
    }

    function leavePage() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var _token = $('input[name="_token"]').val();
        var streamerName = $('#streamerName').val();
        $.ajax({
            type: "POST",
            url: "{{route('leavePage')}}",
            datatype: "json",
            data: {streamerName: streamerName, _token: _token},
            success: function (response) {
                //  console.log(response.result);
                if (response.result == 'success') {
                    // console.log(response.result);
                } else if (response.result == 'leave') {
                    location.href = " {{ url('/') }}";
                    //console.log(response.result);
                }
            }
        });
    }

    function checkCoRequest() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var _token = $('input[name="_token"]').val();
        var streamerid = $('#streamerid').val();
        $.ajax({
            type: "POST",
            url: "{{route('checkCoRequest')}}",
            datatype: "json",
            data: {streamerid: streamerid, _token: _token},
            success: function (response) {
                //  console.log(response.result);
                if (response.result == 'refresh') {
                    $("#app_Id").val(response.value.appid);
                    $("#hosttoken").val(response.value.token);
                    $("#hostchannel").val(response.value.channel);
                    $("#streamer_id").val(response.value.streamer_id);
                    $("#accept_join").css("display", "inline-block");


                    // $("#checkreq").load(window.location.href + " #checkreq" )
                    //    console.log(response.result);
                } else if (response.result == 'notrefresh') {
                    //  location.href = " {{ url('/') }}";
                    $("#accept_join").css("display", "none");
                    // console.log(response.result);
                }
            }
        });
    }

    function acceptreq() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var _token = $('input[name="_token"]').val();
        var streamerid = $('#streamerid').val();
        $.ajax({
            type: "POST",
            url: "{{route('acceptreq')}}",
            datatype: "json",
            data: {streamerid: streamerid, _token: _token},
            success: function (response) {
                //  console.log(response.result);
                if (response.result == 'success') {

                    var names = response.cohostname;
                    var nameArr = names.toString().split(',');
                    var coname = "{{ auth()->user()->name }}";

                    if (nameArr.length == 1) {

                        if (nameArr[0] == coname) {

                            $("#cohost-name").text(nameArr[0]);
                            $("#cohost-names").text('');
                            $("#seperate-cohost").css('display', 'none');

                            $("#full-screen-video-user").removeClass("twoHostJoin");
                            $(".checkhost").removeClass("twoCoHostJoin");
                            $(".checkhost").removeClass("threeJoin");

                        } else {
                            $("#cohost-name").text('');
                            $("#cohost-names").text(nameArr[0]);
                            $("#seperate-cohost").css('display', 'inline-block');

                            $(".checkhost").addClass("twoCoHostJoin");
                            $("#full-screen-video-user").removeClass("twoHostJoin");
                            $(".checkhost").removeClass("threeJoin");
                        }
                    }

                    if (nameArr.length == 2) {
                        if (nameArr[0] == coname) {

                            $("#cohost-name").text(nameArr[0]);
                            $("#cohost-names").text(nameArr[1]);
                            $("#seperate-cohost").css('display', 'inline-block');
                        } else {
                            $("#cohost-names").text(nameArr[0]);
                            $("#cohost-name").text(nameArr[1]);
                            $("#seperate-cohost").css('display', 'inline-block');
                        }
                        $(".checkhost").removeClass("twoCoHostJoin");
                        $("#full-screen-video-user").addClass("twoHostJoin");
                        $(".checkhost").addClass("threeJoin");


                    }

//                     var coname = "{{ auth()->user()->name }}";
//                     console.log('hello' +coname);
//                    $("#cohost-name").text(response.cohostname);
//                    console.log(response.result);
                } else if (response.result == 'notanycohost') {

                    $("#full-screen-video-user").removeClass('col-md-6');
                    $("#full-screen-video-user").removeClass('twoHostJoin');
                    $("#full-screen-video-user").removeClass('twoCoHostJoin');
                    $(".checkhost").removeClass("threeJoin");
                    $("#full-screen-video-user").addClass('col-md-12');
                    $("#full-screen-video-user").addClass('host-live-full-screen');
                   

                    $("#cohost-name").text(response.cohostname);
                    $("#seperate-cohost").css('display', 'none');
                    $("#cohost-names").text(response.cohostname);
                    console.log(response.result);
                }
            }
        });
    }

//    $("#tipBtn").click(function (e) {
//    
//    toastr.options = {
//          "closeButton": true,
//          "newestOnTop": true,
//          "positionClass": "toast-top-right"
//        };
//        
//    var sender_name = $("#cardholder-name").val();
//    var amount = $("#onlyNumber").val();
//
//
//    var view = '<p>' + sender_name + ' send you a tip amount $' + amount + '.</p>';
// 
//                toastr.success(view);
//                console.log(view);
//        
//     // $('#tip_notification').append(view);
//    
//});

//    $("#tipBtn").click(function (e) {
//
//        setTimeout(function () {
//            checkNotification();
//        }, 5000);
//    //    checkNotification()
//
//    });
//
    function checkNotification() {

        toastr.options = {
            "closeButton": true,
            "newestOnTop": true,
            "positionClass": "toast-top-right"
        };

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var _token = $('input[name="_token"]').val();
        var streamerid = $('#streamerid').val();
        $.ajax({
            type: "POST",
            url: "{{route('checkNotification')}}",
            datatype: "json",
            data: {streamerid: streamerid, _token: _token},
            success: function (response) {
                //  console.log(response.result);
                if (response.result == 'success') {
                    toastr.success(response.data);
                    console.log(response.data);

                    //  $('#tip_notification').append(response.data);

                    console.log(response.result);
                } else {
                    //     console.log(response.result);
                }
            }
        });
    }

    function checkCountUser() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var _token = $('input[name="_token"]').val();
        var streamerid = $('#streamerid').val();
        $.ajax({
            type: "POST",
            url: "{{route('checkCountUser')}}",
            datatype: "json",
            data: {streamerid: streamerid, _token: _token},
            success: function (response) {
                if (response.message == 'success') {

                    $("#totalUser").val(response.totalCntUser);
                    console.log("total user in livestream is " + response.totalCntUser);
                    //    console.log(response.cohostname);
                }
            }
        });
    }

//    function checkvideo() {
//        var CheckVideo = $("video").length;
//        console.log('total video' + CheckVideo);
//        if (CheckVideo == '3') {
//            $(".checkhost").removeClass("twoCoHostJoin");
//            $(".checkhost").addClass("threeJoin");
//        } else {
//            $(".checkhost").removeClass("threeJoin");
//        }
//
//    }



    var interval;

    $(document).ready(function () {


        $('#accept_join').click(function (e) {

            var CheckPlayer = $(".checkhost div.mainPlayer").length;
            if (CheckPlayer == 1) {
                $(".checkhost").removeClass("twoCoHostJoin");
            }

//    options.role = "host";


            var streamer_id = $("#streamer_id").val();

            $("#accept_join").hide();



            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var _token = $('input[name="_token"]').val();


            $.ajax({
                type: "POST",
                url: "{{route('acceptCoHost')}}",
                datatype: "json",
                data: {streamer_id: streamer_id, _token: _token},
                success: function (response) {
                    console.log(response.message);
                    $("#full-screen-video-user").removeClass('col-md-12');
                    $("#full-screen-video-user").removeClass('host-live-full-screen');
                    $("#full-screen-video-user").addClass('col-md-6');
                    $("#full-screen-video-user").addClass('host-live');
                    $("#co-host-video").addClass('Cohost-live');

                    CoHost();


                    interval = setInterval(function () {
                        CheckCohostRemove();
                    }, 3000);

                }

            });

            //  CoHost();


        });
    });

    $(document).ready(function () {

//                setInterval(function(){
//              $("#checkreq").load(window.location.href + " #checkreq" );
//          //    console.log('check');
//        }, 4000);

        setInterval(function () {
            leavePage();
        }, 3000);


        setInterval(function () {
            checkCoRequest();
        }, 3000);

//        setInterval(function () {
//            checkvideo();
//        }, 3000);

        setInterval(function () {
            acceptreq();
        }, 3000);

        setInterval(function () {
            checkCountUser();
        }, 2000);

//        setInterval(function () {
//            checkNotification();
//        }, 1000);


    });



</script>


<script>
    var deleter = {

        linkSelector: ".coHostLeave",

        init: function () {
            $(this.linkSelector).on('click', {self: this}, this.handleClick);
        },

        handleClick: function (event) {
            event.preventDefault();

            var self = event.data.self;
            var link = $(this);

            swal({
                title: "Confirm Leave",
                text: "Are you sure you want to end your live video ?",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, End it!",
                closeOnConfirm: true
            },
                    function (isConfirm) {

                        if (isConfirm) {
                            leaveCohost();


                            $.ajaxSetup({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                }
                            });
                            var _token = $('input[name="_token"]').val();
                            var streamerid = $('#streamerid').val();
                            $.ajax({
                                type: "post",
                                url: "{{route('leaveCoHost')}}",
                                datatype: "json",
                                data: {status: 0, streamerid: streamerid, _token: _token},
                                success: function (response) {
                                    console.log(response.message);
                                    $('#videobutton').css('display', 'none');
                                }
                            });

                            return true;
                        } else {
                            return false;
                        }
                    });

        },
    };

    deleter.init();
</script>

<script>
    document.getElementById('channelMessage').addEventListener('keypress', function (event) {
        if (event.keyCode == 13) {
            event.preventDefault();

            document.getElementById('send_channel_message').click();
        }
    });
</script>

@endsection


@section('content')

@if(isset($notification))

<center>
    <div id="loader" style="margin:80px auto;">
        <img id="loading-image" src="{{ asset('public/img/loader1.gif') }}" /><p></p>
    </div>
</center>    

<section class="section">

    <div class="container" id="hide-full-screen" style="display:none;">
        <div class="row">
            <div class="col-md-8 mb-lg-0 py-5 wrap-post">

                <form id="join-form" method="post" action="">

                    <input id="streamerName" type="hidden" value="{{ $streamerName }}">

                    @csrf
                    
                    <div class="row join-info-group">
                        <div class="col-sm">
                            <input id="appid" type="hidden" value="{{ $appID }}" required>
                        </div>
                        <div class="col-sm">
                            <input id="token" type="hidden" value="{{ $token }}">
                        </div>
                        <div class="col-sm">
                            <input id="channel" type="hidden" value="{{ $channelName }}" required>
                            <input id="streamerid" type="hidden" value="{{ $user->id }}" required>
                        </div>
                    </div>

                    <div class="button-group">
                        <button id="audience-join" type="submit" class="btn btn-primary btn-sm" style="display:none">{{trans('general.join_streaming')}}</button>

                        <button id="full-video" type="button" class="btn btn-primary btn-sm" style="display:none">full screen</button>


                    </div>
                </form>


                <div class="row video-group">
                    <div class="w-100"></div>
                    <div class="col">
                        <div id="remote-playerlist"></div>
                    </div>
                </div>


            </div>

            <div class="col-md-4 pb-4 py-lg-5 chatbox" style="display:none;" >



            </div>

        </div>
    </div>

    <div class="row video-screen-card">

        <input type="hidden" value="{{ auth()->user()->name }}" id="hosterName">
        <div id="co-host-video" class="col-md-6">
            <div class="top-left-part">
                <ul> 
                    <li>
                        <a href="#">
                            <div class="user">
                                <h4 id="cohost-name"></h4>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>

            <h1 id="local-player-name" style="display: none;"></h1>

            <div class="row video-group livesreaming-popup">
                <div class="col">
                    <input type="hidden" id="muteAudio" value="{{ asset('public/img/mute.png') }}">
                    <input type="hidden" id="unmuteAudio" value="{{ asset('public/img/unmute.png') }}">
                    <input type="hidden" id="muteVideo" value="{{ asset('public/img/play.png') }}">
                    <input type="hidden" id="unmuteVideo" value="{{ asset('public/img/pause.png') }}">

                    <div class="" id="local-player">
                        <div class="videButton" id="videobutton" style="display:none">

                            <div class="popup-btn">
                                <button id="mute-audio" type="button" class="btn btn-primary btn-sm">
                                    <img src="{{ asset('public/img/unmute.png') }}" />
                                </button>
                                <p>Mute</p>
                            </div>
                            <div class="popup-btn">
                                <button id="mute-video" type="button" class="btn btn-primary btn-sm"><img src="{{ asset('public/img/pause.png') }}" /></button>
                                <p>Video</p>
                            </div>

                            <div class="popup-btn switch-camera">
                                <button id="switchBtn1" type="button" class="btn btn-primary btn-sm" style="display:none" data-camid="" data-camlabel="">
                                    <img src="{{ asset('public/img/camera.png') }}" />
                                </button>
                                <button id="switchBtn2" type="button" class="btn btn-primary btn-sm" data-camid="" data-camlabel="">
                                    <img src="{{ asset('public/img/camera.png') }}" />
                                </button>
                                <p>camera</p>
                            </div>

                            <div class="popup-btn leave-btn">
                                <button id="leave" type="button" class="btn btn-primary btn-sm coHostLeave"><img src="{{ asset('public/img/leave-call.png') }}" /></button>
                                <p>Leave</p>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>
        <div id="full-screen-video-user" class="livesreaming-popup checkhost col-md-12 host-live-full-screen"> 

            <div class="top-right-part">
                <ul>
                    <li class="" id="checkreq">

                        <!--        <div id="checkreq">-->


                        <form id="join-host-form" method="post" action="">


                            @csrf
                            
                            <input id="app_Id" type="hidden" value="" required>
                            <input id="hosttoken" type="hidden" value="">
                            <input id="hostchannel" type="hidden" value="" required>
                            <input id="streamer_id" type="hidden" name="streamer_id" value="" >
                            <input id="streamername" name="streamername" type="hidden" value="{{ auth()->user()->name }}" >

                            <button class="btn btn-primary btn-sm" type="button" id="accept_join" style="display: none;">Accept</button>

                        </form>



                        <!--        </div>-->
                    </li>

                    <li class="">
                        @if (auth()->check() && auth()->user()->id != $user->id && $user->updates()->count() <> 0)
                        <a href="javascript:void(0);" data-toggle="modal" title="{{trans('general.tip')}}" data-target="#tipForm" class="btn btn-google btn-profile mr-1" data-cover="{{Helper::getFile(config('path.cover').$user->cover)}}" data-avatar="{{Helper::getFile(config('path.avatar').$user->avatar)}}" data-name="{{$user->hide_name == 'yes' ? $user->username : $user->name}}" data-userid="{{$user->id}}">
                            <i class="fa fa-donate mr-1 mr-lg-0"></i> {{trans('general.tip')}}
                        </a>
                        @elseif (auth()->guest() && $user->updates()->count() <> 0)
                        <a href="{{url('login')}}" data-toggle="modal" data-target="#loginFormModal" class="btn btn-google btn-profile mr-1" title="{{trans('general.tip')}}">
                            <i class="fa fa-donate mr-1 mr-lg-0"></i> {{trans('general.tip')}}
                        </a>
                        @endif
                    </li>

<!--                    <li class="user"><input id="countUser" name="countUser" type="text" value="0" readonly></li>-->
                    <li class="user"><input id="totalUser" class="countUser" name="totalUser" type="text" readonly></li>

                    <li class="close-wrp">
                        <button id="leave" type="button" class="btn btn-primary btn-sm leave_stream">leave</button>
                    </li>

                </ul>
            </div>
            <!--        onclick="acceptJoin('')"-->

            <div class="top-left-part">
                <ul>
                    <li>
                        <!--                        <a href="{{url($user->username)}}">-->
                        <a href="javascript:;">
                            <div class="user">
                                <img src="{{Helper::getFile(config('path.avatar').$user->avatar)}}" alt="" class="img-user-small">
                                <h4>{{ $user->name }}</h4>
                                <h4 id="seperate-cohost" style="display:none;"> , </h4>
                                <h4 id="cohost-names"></h4>
                            </div>
                        </a>
                    </li>
                    <!--                    <li>
                                            <a href="#">
                                                <div class="user">
                                                    <h4 id="cohost-name"></h4>
                                                </div>
                                            </a>
                                        </li>-->
                </ul>
            </div>

            <!--            <div id="tip_notification"></div>-->



            <div class="row video-group livesreaming-popup">
                <div class="col">

                    <div class="" id="local-player">
                        <div class="videButton host-video-btn" style="display:none">

                            <!--                            <div class="popup-btn">
                                                            <button id="mute-audio-host" type="button" class="btn btn-primary btn-sm mute-audio-host" style="display:none" disabled>
                                                                <img src="{{ asset('public/img/unmute.png') }}" />
                                                            </button>
                                                            <p>Mute</p>
                                                        </div>
                                                        <div class="popup-btn">
                                                            <button id="mute-video-host" type="button" class="btn btn-primary btn-sm mute-video-host" style="display:none" disabled><img src="{{ asset('public/img/pause.png') }}" /></button>
                                                            <p>Video</p>
                                                        </div>-->
                        </div>
                    </div>

                </div>

            </div>

            <div class="chating-wrp chat" style="z-index: 9;">
                <div class="chating-box" id="log">

                    @if(isset($liveChat))

                    @foreach($liveChat AS $key => $value)

                    <div class="user-block">

                        <div class="user-img">
                            <img src="{{ $value->user_img }}">
                        </div>

                        <div class="user-dt">
                            <h3> {{ $value->user_name }} </h3>
                            <p> {{ $value->message }} </p> 
                        </div> 

                    </div>

                    @endforeach

                    @endif                 

                </div>
                <div class="comment-wrp">

                    <form id="loginForm" method="post" action="">
                        @csrf
                        <input id="appId" name="appId" type="hidden" value="{{ $appID }}" required>
                        <input id="accountName" name="accountName"  type="hidden" value="{{ $username }}">
                        <input id="usrtoken" name="token" type="hidden" value="{{ $chattoken }}">
                        <input id="channelName" name="channelName" type="hidden" value="{{ $channelName }}" required>
<!--                        <input id="countUser" name="countUser" type="hidden" value="0" >-->

                        <div class="form-group">
                            <button id="login" type="submit" style="display:none;" >LOGIN</button>
                            <button id="logout" style="display:none;" >LOGOUT</button>
                            <button id="join" style="display:none">JOIN</button>
                        </div>


                        <div class="form-group">
                            <input type="text" placeholder="Add a Comment..." class="form-control" name="channelMessage" id="channelMessage">
                            <input type="hidden" name="usrAvatar" id="usrAvatar" value="{{ Helper::getFile(config('path.avatar').auth()->user()->avatar) }}">
                            <button id="send_channel_message"><img src="https://webmobdemo.xyz/sponzydev/sponzy/public/uploads/avatar/send.svg"></button>
                        </div>


                    </form>


                </div>
            </div>


        </div>



    </div>



</section>

@else

<section>
    <p>{{trans('general.end_livestream')}}

        <a href="{{ url('/') }}">{{trans('general.back')}} </a> {{trans('general.home')}}
    </p>
</section>
@endif


@endsection