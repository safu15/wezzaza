@extends('layouts.app')

@section('title') {{trans('Live Streaming')}} -@endsection

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
    #full-screen-video-user:first-child:nth-last-child(3) .player {
        height: 100vh;
    }

    #full-screen-video-user:first-child:nth-last-child(4) .player,
    #full-screen-video-user:first-child:nth-last-child(4) ~ #full-screen-video-user .player {
        height: 50vh;
    }

</style>

@section('javascript')
<script type="text/javascript">

    window.onload = function () {
        $("#host-join").click();
        $('#login').click();
    }


    $(document).ready(function () {

        setTimeout(function () {
            $('#join').trigger('click');
        }, 10000);
        setTimeout(function () {
            $('#join').trigger('click');
        }, 25000);
        setInterval(function () {
            $("#activeusr").load(window.location.href + " #activeusr");
            //    console.log('check');
        }, 4000);
    });

//    $("#leave").click(function (e) {
//        var retVal = confirm("Are you sure you want to end your live video ?");
//        if (retVal == true) {
//            leave();
//            location.href = " {{ url('/') }}";
//            return true;
//        } else {
//            return false;
//        }
//
//
//    });

    $("#cohost-name").click(function (e) {
        var name = $(this).text();
        $(".cohost-remove").css("display", "inline-block");
        $(".cohost-remove").attr('data-cohostname', name);
    });
    
    $("#cohost-names").click(function (e) {
        var name = $(this).text();
        $(".cohost-remove").css("display", "inline-block");
        $(".cohost-remove").attr('data-cohostname', name);
    });

    $(".cohost-remove").click(function (e) {
        $(".cohost-remove").css("display", "none");

        var cohostName = $(this).attr("data-cohostname");


        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var _token = $('input[name="_token"]').val();
        $.ajax({
            type: "post",
            url: "{{route('hostleaveCohost')}}",
            datatype: "json",
            data: {status: 0,cohostName: cohostName, _token: _token},
            success: function (response) {
                console.log(response.message);
                $("#full-screen-video-user").removeClass("twoHostJoin");
                audienceRemove();
//                $("#full-screen-video").removeClass('col-md-6');
//                $("#full-screen-video").addClass('col-md-12');
//                $("#videobtn").removeClass('cohost-video-btn');
//                $("#videobtn").css("display", "none");

//                var CheckDivSize = $("#full-screen-video-user div.mainPlayer").length;
//                if (CheckDivSize == 1) {
//                    $(".audienceSide").html("");
//                    $(".twoHostJoin").removeClass('audienceSide');
//                    $(".audienceSide").html("");
//                } else {
//                    $(".audienceSide").html("");
//                }


                //  hostleaveCohost();
            }
        });
    });

    function requestCoHost(hostId, appid, token, channel) {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var _token = $('input[name="_token"]').val();
        $.ajax({
            type: "POST",
            url: 'requestCoHost',
            datatype: "json",
            data: {requestCoHostId: hostId, appid: appid, token: token, channel: channel, _token: _token},
            success: function (response) {
                if (response.result == 'success') {
                    toastr.success(response.message);
                    console.log(response.message);
                } else {
                    toastr.error(response.message);
                    console.log(response.message);
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
//                    $.each(response.cohostname, function (key, value) {
//                        $("#cohost-name").text(value);
//                    });
                    if (nameArr.length == 1){

                        $("#cohost-name").text(nameArr[0]);
                        $("#cohost-names").text('');
                        $("#seperate-cohost").css('display','none');
                        
                        $("#full-screen-video").removeClass('col-md-12');
                        $("#full-screen-video").addClass('col-md-6');
                        $("#full-screen-video-user").addClass('col-md-6');
                        $("#full-screen-video-user").removeClass('twoHostJoin');
                        $("#full-screen-video-user").removeClass('threeJoin');
                    }

                    if (nameArr.length == 2) {
                         $("#cohost-name").text(nameArr[0]);
                        $("#cohost-names").text(nameArr[1]);
                        $("#seperate-cohost").css('display','inline-block');
                        
                        $("#full-screen-video").removeClass('col-md-12');
                        $("#full-screen-video").addClass('col-md-6');
                        $("#full-screen-video-user").addClass('col-md-6');
                        $("#full-screen-video-user").addClass('twoHostJoin');
                        $("#full-screen-video-user").addClass('threeJoin');
                        
                    }

                    console.log(response.result);
                    console.log(response.cohostname);
                } else if (response.result == 'notanycohost') {
                    
//                    $('.audienceSide').remove();
                    $("#full-screen-video").addClass('col-md-12');
                    $("#full-screen-video").addClass('host-live-full-screen');
                    $("#full-screen-video").removeClass('col-md-6');
                    $("#videobtn").removeClass('cohost-video-btn');
                    $("#full-screen-video-user").removeClass('threeJoin');
                    $("#videobtn").css("display", "none");
                    
                    $("#cohost-name").text(response.cohostname);
                    $("#cohost-names").text(response.cohostname);
                    console.log(response.result);
                }
            }
        });
    }


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
    
    
     function checkCohost() {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var _token = $('input[name="_token"]').val();

        $.ajax({
            type: "POST",
            url: "{{route('checkCohost')}}",
            datatype: "json",
            data: {_token: _token},
            success: function (response) {

        if (response.result == 'hide') {

                    console.log('hide');
                    $('.co-host').css('display', 'none');
                } else {
                    
                 console.log('show');
                 $('.co-host').css('display', 'inline-block');
                }
            }
        });
    }
    
    
//    function checkvideo() {
//    var CheckVideo = $("video").length;
//    console.log('total video'+CheckVideo);
//            if (CheckVideo == '3') {
//                
//                $("#full-screen-video-user").addClass("threeJoin");
//               
//            } else {
//                $("#full-screen-video-user").removeClass("threeJoin");
//            }
//
//}

    $(document).ready(function () {
        setInterval(function () {
            acceptreq();
        }, 3000);
//        setInterval(function () {
//            checkvideo();
//        }, 3000);
        setInterval(function () {
            audienceRemove();
        }, 3000);
        
        setInterval(function () {
            checkCohost();
        }, 3000);
        setInterval(function () {
            checkNotification();
        }, 1000);
    });</script>

<script>
    document.getElementById('channelMessage').addEventListener('keypress', function (event) {
        if (event.keyCode == 13) {
            event.preventDefault();
            document.getElementById('send_channel_message').click();
        }
    });
    $(window).on("beforeunload", function () {
        //return confirm("Do you really want to close?"); 

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
    })

    function trim(string) {
        return string.replace(/^\s+/g, '').replace(/\s+$/g, '')
    }

    $(function () {

        $('#searchCreators').on('keyup', function (e) {
            e.preventDefault();
            var $string = $(this).val();
            var appId = $("#appid").val();
            var token = $("#token").val();
            var channelName = $("#channel").val();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var _token = $('input[name="_token"]').val();
            $('#searchSpinner').show();
            $('#creatorsContainer').html('');
            $(this).waiting(500).done(function () {


                $.ajax({
                    type: "post",
                    url: '{{ route("searchCoHost") }}',
                    data: {'user': trim($string), 'appId': appId, 'token': token, 'channelName': channelName, _token: _token},
                    success: function (response) {
                        if (response) {

                            $('#creatorsContainer').html(response);
                            $('#searchSpinner').hide();
                        } else {
                            $('#creatorsContainer').html('<span class="p-2 text-center w-100 d-block">' + no_results + '</span>');
                            $('#searchSpinner').hide();
                        }
                    }
                });
            });
        });
    });</script>

<script>
    var deleter = {

        linkSelector : "#leave",

        init: function() {
            $(this.linkSelector).on('click', {self:this}, this.handleClick);
        },

        handleClick: function(event) {
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
            function(isConfirm){
              
                if(isConfirm){
                    
                    leave();
                    location.href = " {{ url('/') }}";
                    return true;
                }
                else{
                    return false;
                }
            });

        },
    };

    deleter.init();
</script>

@endsection

@section('content')

<center>
    <div id="loading" style="margin:80px auto;">
        <img id="loading-image" src="{{ asset('public/img/loader1.gif') }}" /><p></p>
    </div>
</center>    

<section class="section ">



    <div class="container" id="hide-full-screen" style="display:none;">
        <div class="row">
            <div class="col-md-8 mb-lg-0 py-5 wrap-post">

                <form id="join-form" method="post" action="">
                    @csrf
                    
                    <div class="row join-info-group">
                        <div class="col-sm">
                            <input id="appid" type="hidden" value="{{ $appID }}" required>
                        </div>
                        <div class="col-sm">
                            <input id="token" name="token" type="hidden" value="{{ $token }}">
                        </div>
                        <div class="col-sm">
                            <input id="channel" type="hidden" value="{{ $channelName }}" required>
                            <input id="status" name="status" type="hidden" value="1" >
                            <input id="streamername" name="streamername" type="hidden" value="{{ auth()->user()->name }}" >
                            <input id="streamerid" name="streamerid" type="hidden" value="{{ auth()->user()->id }}" >
                        </div>
                    </div>

                    <div class="button-group">
                        <button id="host-join" type="submit" class="btn btn-primary btn-sm" style="display:none">{{trans('general.start_streaming')}}</button>
                    </div>
                </form>


            </div>

            <div class="col-md-4 pb-4 py-lg-5 chat" style="display:none;" >


            </div>

        </div>
    </div>

    <div class="row video-screen-card">

        <div id="full-screen-video" class="col-md-12 host-live-full-screen">


            <div class="row video-group livesreaming-popup">
                <div class="col">
                    <input type="hidden" id="muteAudio" value="{{ asset('public/img/mute.png') }}">
                    <input type="hidden" id="unmuteAudio" value="{{ asset('public/img/unmute.png') }}">
                    <input type="hidden" id="muteVideo" value="{{ asset('public/img/play.png') }}">
                    <input type="hidden" id="unmuteVideo" value="{{ asset('public/img/pause.png') }}">

                    <div class="" id="local-player">
                        <div class="videButton host-video-btn" style="display:none">

                            <div class="popup-btn">

                                <button id="mute-audio" type="button" class="btn btn-primary btn-sm" style="display:none">
                                    <img src="{{ asset('public/img/unmute.png') }}" />
                                </button>
                                <p>Mute</p>
                            </div>
                            <div class="popup-btn">
                                <button id="mute-video" type="button" class="btn btn-primary btn-sm" style="display:none">
                                    <img src="{{ asset('public/img/pause.png') }}" />
                                </button>
                                <p>Video</p>
                            </div>
                            <!--                            <div class="popup-btn switchCameraBtn">
                                                            
                                                        </div>-->
                            <div class="popup-btn switch-camera">
                                <button id="switchBtn1" type="button" class="btn btn-primary btn-sm" style="display:none" data-camid="" data-camlabel="">
                                    <img src="{{ asset('public/img/camera.png') }}" />
                                </button>
                                <button id="switchBtn2" type="button" class="btn btn-primary btn-sm" data-camid="" data-camlabel="">
                                    <img src="{{ asset('public/img/camera.png') }}" />
                                </button>
                                <p>camera</p>
                            </div>
                            <!--                            <div class="popup-btn">
                                                            <button id="full-video" type="button" class="btn btn-primary btn-sm" style="display:none">
                                                                <img src="{{ asset('public/img/full-screen.png') }}" />
                                                            </button>
                                                            <p>Full Screen</p>
                                                        </div>-->
                            <div class="popup-btn leave-btn">
                                <button id="leave" type="button" class="btn btn-primary btn-sm" style="display:none">
                                    <img src="{{ asset('public/img/leave-call.png') }}" />
                                </button>
                                <p>Leave</p>
                            </div>
                        </div>


                    </div>
                    <div id="local-stats" class="stream-stats stats"></div>

                </div>
                <div class="w-100"></div>
                <div class="col">
                    <div id="remote-playerlist"></div>
                </div>
            </div>



            <div class="chating-wrp chat" style="z-index: 9;">
                <div class="chating-box" id="log">

                </div>
                <div class="comment-wrp">

                    <form id="loginForm" method="post" action="">
                        @csrf
                        
                        <input id="appId" name="appId" type="hidden" value="{{ $appID }}" required>
                        <input id="accountName" name="accountName"  type="hidden" value="{{ $username }}">
                        <input id="usrtoken" name="token" type="hidden" value="{{ $chattoken }}">
                        <input id="channelName" name="channelName" type="hidden" value="{{ $channelName }}" required>
<!--                        <input id="countUser" name="countUser" type="text" value="0" >-->

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
        <div id="full-screen-video-user" class="col-md-6 cohost">

            <div class="top-left-part">
                <ul>
                    <li>
                        <a href="javascript:;">
                            <div class="user">
        <!--                        <img src="https://webmobdemo.xyz/sponzydev/sponzy/public/uploads/avatar/5da8273e-41616952448bwjv1ivj8q.png">-->
                                <h4 id="cohost-name"></h4>
                                <h4 id="seperate-cohost" style="display:none;"> , </h4>
                                <h4 id="cohost-names"></h4>
                                <button id="leave" type="button" class="btn btn-primary btn-sm cohost-remove" data-cohostname="" style="display:none;"><img src="{{ asset('public/img/leave-call.png') }}" /></button>

                            </div>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="row video-group livesreaming-popup">
                <div class="col">

                    <div class="" id="local-player">
                        <div class="videButton" id="videobtn" style="display:none">

                            <div class="popup-btn">
                                <button id="mute-audio-host" type="button" class="btn btn-primary btn-sm mute-audio-cohost" disabled>
                                    <img src="{{ asset('public/img/unmute.png') }}" />
                                </button>
                                <p>Mute</p>
                            </div>
                            <div class="popup-btn">
                                <button id="mute-video-host" type="button" class="btn btn-primary btn-sm mute-video-cohost" disabled>
                                    <img src="{{ asset('public/img/pause.png') }}" />
                                </button>
                                <p>Video</p>
                            </div>

                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="top-left-part">
        <ul>
            <li>
<!--                <a href="{{url(auth()->user()->username)}}">-->
                <a href="javascript:;">
                    <div class="user">
                        <img src="{{Helper::getFile(config('path.avatar').auth()->user()->avatar)}}" class="img-user-small">
                        <h4 id="streamer-name"></h4>
                    </div>
                </a>
            </li>

            <li>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Cams</button>
                        <div class="cam-list dropdown-menu"></div>
                    </div>
                    <input type="text" class="cam-input form-control" aria-label="Text input with dropdown button" readonly>
                </div>
            </li>
            <!--            <li>
                            <a href="javascript:;">
                                <div class="user">
                                    <img src="https://webmobdemo.xyz/sponzydev/sponzy/public/uploads/avatar/5da8273e-41616952448bwjv1ivj8q.png">
                                    <h4 id="cohost-name"></h4>
                                    <button id="leave" type="button" class="btn btn-primary btn-sm cohost-remove" style="display:none;"><img src="{{ asset('public/img/leave-call.png') }}" /></button>
            
                                </div>
                            </a>
                        </li>-->
        </ul>
    </div>

    <div class="top-right-part">
        <ul>
            <li class="live"><span class="blink">Live</span></li>
            <!--            <li class="co-host">
            
                            <div class="dropdown ">
                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <img src="https://webmobdemo.xyz/sponzydev/sponzy/public/uploads/avatar/join.png" style="width:20px;">
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            
                                    <div id="activeusr">
            
                                        @foreach ($cohostusr as $user)
                                        @if ( Cache::has('is-online-' . $user->user()->id) && $user->user()->status == 'active' && $user->user()->verified_id == 'yes')
            
                                        <a class="dropdown-item dropdown-navbar dropdown-lang " href="javascript:;" onclick="requestCoHost('{{$user->user()->id}}','{{$appID}}','{{$token}}','{{$channelName}}');">
                                            {{ $user->user()->name }}
                                        </a>
            
                                        @endif
                                        @endforeach
            
                                    </div>
            
                                </div>
                            </div>
            
                        </li>-->
            <li class="co-host">

                <div class="dropdown ">
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img src="https://webmobdemo.xyz/sponzydev/sponzy/public/uploads/avatar/join.png" style="width:20px;">
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">

                        <input id="searchCreators" name="searchCreators" placeholder="{{ trans('general.find_cohost') }}" type="text" autocomplete="off">
                        <button  class="button-search" style="top:10px;">
                            <i class="fa fa-search"></i>
                        </button>

                        <div class="w-100 text-center display-none py-2" id="searchSpinner">
                            <span class="spinner-border spinner-border-sm align-middle text-primary"></span>
                        </div>

                        <div id="creatorsContainer"></div>


                    </div>
                </div>

            </li>
            <li class="user"><input id="countUser" class="countUser" name="countUser" type="text" value="0" readonly></li>

        </ul>
    </div>

    <div id="tip_notification"></div>




</section>
@endsection