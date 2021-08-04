@extends('layouts.app')

@section('title') {{trans('Live Streaming')}} -@endsection

<style>
    nav {
        display:none !important;
    }
    footer {
        display:none !important;
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
        }, 4000);

    });

    $("#leave").click(function (e) {
        leave();
        location.href = " {{ url('/') }}";
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
            data: {requestCoHostId: hostId, appid: appid, token: token, channel:channel, _token: _token},
            success: function (response) {
            console.log(response.message);
            }
    });
    }

</script>

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
                       <!--  <div class="col-sm">
                            <input id="channel" type="hidden" value="{{ $channelName }}" required>
                            <input id="status" name="status" type="hidden" value="1" >
                        </div> -->
                    </div>

                    <div class="button-group">
                        <button id="host-join" type="submit" class="btn btn-primary btn-sm" style="display:none">{{trans('general.start_streaming')}}</button>
                    </div>
                </form>


                <!--                <div id="client-stats" class="stats"></div>-->

                <!--                <div class="row video-group livesreaming-popup">
                                    <div class="col">
                
                                        <div class="player" id="local-player">
                                            
                                            <div class="videButton" style="display:none">
                                                <div class="popup-btn">
                                                    <button id="mute-audio" type="button" class="btn btn-primary btn-sm" style="display:none">
                                                        <img src="{{ asset('public/img/unmute.png') }}" />
                                                    </button>
                                                    <p>Mute</p>
                                                </div>
                                                <div class="popup-btn">
                                                    <button id="mute-video" type="button" class="btn btn-primary btn-sm" style="display:none"><img src="{{ asset('public/img/pause.png') }}" /></button>
                                                    <p>Video</p>
                                                </div>
                                                <div class="popup-btn">
                                                    <button id="full-video" type="button" class="btn btn-primary btn-sm" style="display:none"><img src="{{ asset('public/img/full-screen.png') }}" /></button>
                                                    <p>Full Screen</p>
                                                </div>
                                                <div class="popup-btn leave-btn">
                                                    <button id="leave" type="button" class="btn btn-primary btn-sm" style="display:none"><img src="{{ asset('public/img/leave-call.png') }}" /></button>
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
                                </div>-->


            </div>

            <div class="col-md-4 pb-4 py-lg-5 chat" style="display:none;" >


                <form id="loginForm" method="post" action="">
                    @csrf
                    <div class="row join-info-group">
                       
                        <div class="col-sm">
                            <input id="appId" name="appId" type="hidden" value="{{ $appID }}" required>
                        </div>
                        <div class="col-sm">
                            <input id="accountName" name="accountName"  type="hidden" value="{{ $username }}">
                        </div>
                        <div class="col-sm">
                            <input id="usrtoken" name="token" type="hidden" value="{{ $chattoken }}">
                        </div>
                        <div class="col-sm">
                            <input id="channelName" name="channelName" type="hidden" value="{{ $channelName }}" required>
                        </div>
                    </div>

                    <div class="button-group">
                        <button class="btn btn-primary btn-sm" id="login" type="submit" style="display:none;" >LOGIN</button>
                        <button class="btn btn-primary btn-sm" id="logout" style="display:none;" >LOGOUT</button>
                        <button class="btn btn-primary btn-sm" id="join" style="display:none">JOIN</button>
                    </div>

                    <div class="col" id="msgSend">
                        <div class="log-container" id="log">

                        </div>


                        <div class="input-field channel-padding">
                            <input type="text" placeholder="Send Message" name="channelMessage" id="channelMessage">
                            <input type="hidden" name="usrAvatar" id="usrAvatar" value="{{ Helper::getFile(config('path.avatar').auth()->user()->avatar) }}">
                            <button class="btn btn-primary btn-sm" id="send_channel_message">SEND</button>
                        </div>

                    </div>
                </form>





            </div>

        </div>
    </div>

    <div id="full-screen-video" class="livesreaming-popup">

        <a href="{{url($username)}}">

            <img src="{{Helper::getFile(config('path.avatar').auth()->user()->avatar)}}" width="50" height="50" alt="" class="img-user-small">

            <h5>{{ $username }}</h5>

        </a>
        
        <div class="popup-btn" class="videButton">
            <button id="half-screen-video" type="button" class="btn btn-primary btn-sm"><img src="{{ asset('public/img/full-screen.png') }}" style="width:25px;" /></button>

        </div>

          <div class="top-left-part">
                <ul>
                    <li>
                        <a href="#">
                            <div class="user">
                                <img src="https://webmobdemo.xyz/sponzydev/sponzy/public/uploads/avatar/5da8273e-41616952448bwjv1ivj8q.png">
                                <h4>devloper1</h4>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <div class="user">
                                <img src="https://webmobdemo.xyz/sponzydev/sponzy/public/uploads/avatar/5da8273e-41616952448bwjv1ivj8q.png">
                                <h4>devloper1</h4>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="top-right-part">
                <ul>
                    <li class="live"><span class="blink">Live</span></li>
                    <li class="user">7880</li>
                    <li class="close-wrp"><a href="#">&times;</a></li>
                </ul>
            </div>

            <div class="chating-wrp">
                <div class="chating-box">
                    <div class="user-block">
                        <div class="user-img">
                            <img src="https://webmobdemo.xyz/sponzydev/sponzy/public/uploads/avatar/5da8273e-41616952448bwjv1ivj8q.png">
                        </div>
                        <div class="user-dt">
                            <h3>sava6565</h3>
                            <p>whatsapp Me</p>
                        </div>
                    </div>
                    <div class="user-block">
                        <div class="user-img">
                            <img src="https://webmobdemo.xyz/sponzydev/sponzy/public/uploads/avatar/5da8273e-41616952448bwjv1ivj8q.png">
                        </div>
                        <div class="user-dt">
                            <h3>sava6565</h3>
                            <p>whatsapp Me</p>
                        </div>
                    </div>
                    <div class="user-block">
                        <div class="user-img">
                            <img src="https://webmobdemo.xyz/sponzydev/sponzy/public/uploads/avatar/5da8273e-41616952448bwjv1ivj8q.png">
                        </div>
                        <div class="user-dt">
                            <h3>sava6565</h3>
                            <p>whatsapp Me</p>
                        </div>
                    </div>
                    <div class="user-block">
                        <div class="user-img">
                            <img src="https://webmobdemo.xyz/sponzydev/sponzy/public/uploads/avatar/5da8273e-41616952448bwjv1ivj8q.png">
                        </div>
                        <div class="user-dt">
                            <h3>sava6565</h3>
                            <p>whatsapp Me</p>
                        </div>
                    </div>
                    <div class="user-block">
                        <div class="user-img">
                            <img src="https://webmobdemo.xyz/sponzydev/sponzy/public/uploads/avatar/5da8273e-41616952448bwjv1ivj8q.png">
                        </div>
                        <div class="user-dt">
                            <h3>sava6565</h3>
                            <p>whatsapp Me</p>
                        </div>
                    </div>
                    <div class="user-block">
                        <div class="user-img">
                            <img src="https://webmobdemo.xyz/sponzydev/sponzy/public/uploads/avatar/5da8273e-41616952448bwjv1ivj8q.png">
                        </div>
                        <div class="user-dt">
                            <h3>sava6565</h3>
                            <p>whatsapp Me</p>
                        </div>
                    </div>
                    <div class="user-block">
                        <div class="user-img">
                            <img src="https://webmobdemo.xyz/sponzydev/sponzy/public/uploads/avatar/5da8273e-41616952448bwjv1ivj8q.png">
                        </div>
                        <div class="user-dt">
                            <h3>sava6565</h3>
                            <p>whatsapp Me</p>
                        </div>
                    </div>
                    <div class="user-block">
                        <div class="user-img">
                            <img src="https://webmobdemo.xyz/sponzydev/sponzy/public/uploads/avatar/5da8273e-41616952448bwjv1ivj8q.png">
                        </div>
                        <div class="user-dt">
                            <h3>sava6565</h3>
                            <p>whatsapp Me</p>
                        </div>
                    </div>
                    <div class="user-block">
                        <div class="user-img">
                            <img src="https://webmobdemo.xyz/sponzydev/sponzy/public/uploads/avatar/5da8273e-41616952448bwjv1ivj8q.png">
                        </div>
                        <div class="user-dt">
                            <h3>sava6565</h3>
                            <p>whatsapp Me</p>
                        </div>
                    </div>
                    <div class="user-block">
                        <div class="user-img">
                            <img src="https://webmobdemo.xyz/sponzydev/sponzy/public/uploads/avatar/5da8273e-41616952448bwjv1ivj8q.png">
                        </div>
                        <div class="user-dt">
                            <h3>sava6565</h3>
                            <p>whatsapp Me</p>
                        </div>
                    </div>
                </div>
                <div class="comment-wrp">
                    <div class="form-group">
                        <input type="text" name="" placeholder="Add a Comment..." class="form-control">
                        <a href="#"><img src="https://webmobdemo.xyz/sponzydev/sponzy/public/uploads/avatar/more-horizontal.svg"></a>
                    </div>
                    <div class="share-ic">
                        <ul>
                            <li><a href="#"><img src="https://webmobdemo.xyz/sponzydev/sponzy/public/uploads/avatar/help-circle.svg"></a></li>
                            <li><a href="#"><img src="https://webmobdemo.xyz/sponzydev/sponzy/public/uploads/avatar/send.svg"></a></li>
                            <li><a href="#"><img src="https://webmobdemo.xyz/sponzydev/sponzy/public/uploads/avatar/heart.svg"></a></li>
                        </ul>
                    </div>

                </div>
            </div>
        
        <ul class="navbar-nav ml-auto" id="activeusr">
        
        
                    <li class="nav-item mt-1 mr-1 dropdown d-lg-block d-none">
                        <a class="" href="javascript:;" data-toggle="dropdown">
                            Join Co-host
        
                            <i class="feather icon-chevron-down m-0 align-middle"></i>
                        </a>
        
                        <div class="dropdown-menu mb-1 dropdown-menu-right dd-menu-user" aria-labelledby="nav-inner-success_dropdown_1">
                            @foreach ($cohostusr as $user)
                            @if ( Cache::has('is-online-' . $user->user()->id) && $user->user()->status == 'active' && $user->user()->verified_id == 'yes')
        
                            <a class="dropdown-item dropdown-navbar dropdown-lang " href="javascript:;" onclick="requestCoHost('{{$user->user()->id}}','{{$appID}}','{{$token}}','{{$channelName}}');">
                                {{ $user->user()->name }}
                            </a>
        
                            @endif
                            @endforeach
                        </div>
                    </li>
                    
                    
        
        
        
                </ul>
        
        <!-- <br><br><br><br> -->

                

        <!-- <div class="row video-group livesreaming-popup">
            <div class="col">

                <div class="" id="local-player">
                    <div class="videButton" style="display:none">
                        <div class="popup-btn">
                            <button id="mute-audio" type="button" class="btn btn-primary btn-sm" style="display:none">
                                <img src="{{ asset('public/img/unmute.png') }}" />
                            </button>
                            <p>Mute</p>
                        </div>
                        <div class="popup-btn">
                            <button id="mute-video" type="button" class="btn btn-primary btn-sm" style="display:none"><img src="{{ asset('public/img/pause.png') }}" /></button>
                            <p>Video</p>
                        </div>
                        <div class="popup-btn">
                            <button id="full-video" type="button" class="btn btn-primary btn-sm" style="display:none"><img src="{{ asset('public/img/full-screen.png') }}" /></button>
                            <p>Full Screen</p>
                        </div>
                        <div class="popup-btn leave-btn">
                            <button id="leave" type="button" class="btn btn-primary btn-sm" style="display:none"><img src="{{ asset('public/img/leave-call.png') }}" /></button>
                            <p>Leave</p>
                        </div>
                        
                        
                    </div>
                </div>

            </div>

        </div> -->
          
    </div>

    


</section>
@endsection