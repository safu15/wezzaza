// create Agora client
var client = AgoraRTC.createClient({mode: "live", codec: "vp8"});
var localTracks = {
    videoTrack: null,
    audioTrack: null
};

var remoteTracks = {
    videoTrack: null,
    audioTrack: null
};

var localTrackState = {
    videoTrackEnabled: true,
    audioTrackEnabled: true
}

var remoteUsers = {};
// Agora client options
var options = {
    appid: null,
    channel: null,
    uid: null,
    token: null,
    role: "audience" // host or audience

};

var btn = {
    AudioMute: $('#muteAudio').val(),
    AudioUnmute: $('#unmuteAudio').val(),
    videoMute: $('#muteVideo').val(),
    videoUnmute: $('#unmuteVideo').val(),
};

var cams = []; // all cameras devices you can use
var currentCam; // the camera you are using




// the demo can auto join channel with params in url
$(() => {
    var urlParams = new URL(location.href).searchParams;
    options.appid = urlParams.get("appid");
    options.channel = urlParams.get("channel");
    options.token = urlParams.get("token");

    //   await mediaDeviceTest();

    if (options.appid && options.channel) {
        $("#appid").val(options.appid);
        $("#token").val(options.token);
        $("#channel").val(options.channel);
        $("#join-form").submit();
    }
})

$("#host-join").click(function (e) {
    options.role = "host"

    options.status = $("#status").val();

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    var _token = $('input[name="_token"]').val();
    $.ajax({
        type: "post",
        url: 'startStreaming',
        datatype: "json",
        data: {status: options.status, _token: _token},
        success: function (response) {
            console.log(response.message);
        }
    });

})

$("#audience-join").click(function (e) {
    options.role = "audience"

})

//async function mediaDeviceTest() {
//  // create local tracks
//  [ localTracks.audioTrack, localTracks.videoTrack ] = await Promise.all([
//    // create local tracks, using microphone and camera
//    AgoraRTC.createMicrophoneAudioTrack(),
//    AgoraRTC.createCameraVideoTrack()
//  ]);
//
//  // play local track on device detect dialog
//  localTracks.videoTrack.play("full-screen-video");
//  // localTracks.audioTrack.play();
//
//  // get cameras
//  cams = await AgoraRTC.getCameras();
//  currentCam = cams[0];
//  $(".cam-input").val(currentCam.label);
//  cams.forEach(cam => {
//    $(".cam-list").append(`<a class="dropdown-item" href="#">${cam.label}</a>`);
//  });
//}

$("#join-form").submit(async function (e) {

    e.preventDefault();
    // $("#host-join").attr("disabled", true);
    // $("#audience-join").attr("disabled", true);

    try {
        options.appid = $("#appid").val();
        options.token = $("#token").val();
        options.channel = $("#channel").val();
        options.streamername = $("#streamername").val();


        await join();
        if (options.role === "host") {
            $("#success-alert a").attr("href", '/liveStreaming');
            if (options.token) {
                $("#success-alert-with-token").css("display", "block");
            } else {
                $("#success-alert a").attr("href", '/liveStreaming');
                $("#success-alert").css("display", "block");
            }
        }
    } catch (error) {
        console.error(error);
    } finally {
        $("#leave").attr("disabled", false);
    }
})

//$("#leave").click(function (e) {
//    leave();
//});

// $(".coHostLeave").click(function (e) {
//     leaveCohost();
//  
// });

// $(".cohost-remove").click(function (e) {
//     hostleaveCohost();
//  
// });


$("#mute-audio").click(function (e) {
    if (localTrackState.audioTrackEnabled) {
        muteAudio();
        $("#mute-audio").html("<img src=" + btn.AudioMute + "  />");
    } else {
        unmuteAudio();
        $("#mute-audio").html("<img src=" + btn.AudioUnmute + "  />");
    }
});

$("#mute-video").click(function (e) {
    if (localTrackState.videoTrackEnabled) {
        muteVideo();
        $("#mute-video").html("<img src=" + btn.videoMute + " />");
    } else {
        unmuteVideo();
        $("#mute-video").html("<img src=" + btn.videoUnmute + " />");
    }
})




$("#switchBtn1").click(function (e) {

    var camId = $(this).attr("data-camid");
    var camLabel = $(this).attr("data-camlabel");

    $("#switchBtn1").css("display", "none");
    $("#switchBtn2").css("display", "inline-block");


    console.log('btn1:' + camId);
    switchCamera(camLabel);
})

$("#switchBtn2").click(function (e) {


    var camId = $(this).attr("data-camid");
    var camLabel = $(this).attr("data-camlabel");

    $("#switchBtn2").css("display", "none");
    $("#switchBtn1").css("display", "inline-block");


    console.log('btn2:' + camId);
    switchCamera(camLabel);
})


$(".cam-list").delegate("a", "click", function (e) {
    switchCamera(this.text);
});


async function join() {
    // create Agora client
    client.setClientRole(options.role);

    // get cameras
    cams = await AgoraRTC.getCameras();
    console.log('camera :' + JSON.stringify(cams));
    currentCam = cams[0];
    //   $(".cam-input").val(currentCam.label);

//    $("#switchBtn1").attr('data-camlabel', cams[0].label);
//    $("#switchBtn1").attr('data-camid', cams[0].deviceId);
//    
//    // $("#switchBtn1").text(cams[0].deviceId);
//    if (cams.length > 2) {
//        
//         $("#switchBtn2").attr('data-camlabel', cams[0].label);
//    $("#switchBtn2").attr('data-camid', cams[0].deviceId);
//   
//    }
    //  var add = 0;
//    cams.forEach(cam => {
//        
//        if(add == '0') {
//          $("#switchBtn1").attr('data-camlabel', cam.label);
//          $("#switchBtn1").attr('data-camid', cam.deviceId);
//          add++;
//          console.log("check camera first id" +add);
//        }if(add == '1') {
//            $("#switchBtn2").attr('data-camlabel', cam.label);
//          $("#switchBtn2").attr('data-camid', cam.deviceId);
//          add++;
//          console.log("check camera second id" +add);
//        }
//        
////        $(".switchCameraBtn").append(` <button id="switchBtn1" type="button" class="btn btn-primary btn-sm" data-camid="${cam.deviceId}" data-camlabel="${cam.label}">
////                                    <img src="public/img/camera.png" />
////                                </button>`);
//        $(".cam-list").append(`<a class="dropdown-item" href="#">${cam.label}</a>`);
//    });


    Object.entries(cams).forEach(([key, cam]) => {

        //   $("#switchBtn1").text(cam.deviceId);
        if (key == 0) {
            $("#switchBtn1").attr('data-camlabel', cam.label);
            $("#switchBtn1").attr('data-camid', cam.deviceId);


            //console.log(`key ${key}: ${cam.label}`);

        }
        if (key == 1) {

            $("#switchBtn2").attr('data-camlabel', cam.label);
            $("#switchBtn2").attr('data-camid', cam.deviceId);

            //      console.log(`key 2: ${cam.label}`);
    }
    });


    if (options.role === "audience") {
        // add event listener to play remote tracks when remote user publishs.
        client.on("user-published", handleUserPublished);
        client.on("user-joined", handleUserJoined);
        client.on("user-left", handleUserLeft);
        client.on("user-unpublished", handleUserUnpublished);
        //  client.on("user-unpublished", handleUserUnpublished);
    }
    // join the channel
    options.uid = await client.join(options.appid, options.channel, options.token || null);

    if (options.role === "host") {

        if (!localTracks.videoTrack) {
            [localTracks.videoTrack] = await Promise.all([
                // create local tracks, using microphone and camera
                AgoraRTC.createCameraVideoTrack({cameraId: currentCam.deviceId})
            ]);
        }
        // create local audio and video tracks
        localTracks.audioTrack = await AgoraRTC.createMicrophoneAudioTrack();
        // localTracks.videoTrack = await AgoraRTC.createCameraVideoTrack();
        // play local video track
        // localTracks.videoTrack.play("local-player");

        // play local track on device detect dialog
        localTracks.videoTrack.play("full-screen-video");
        // localTracks.audioTrack.play();



        //   localTracks.videoTrack.play("full-screen-video");
        // $("#local-player-name").text(`localTrack(${options.uid})`);
        $("#streamer-name").text(`${options.streamername}`);
        // publish local tracks to channel
        await client.publish(Object.values(localTracks));

        console.log("publish success");



    }




    initStats();
    showMuteButton();

}



async function switchCamera(label) {
    currentCam = cams.find(cam => cam.label === label);
    // $(".cam-input").val(currentCam.label);

    // switch device of local video track.
    await localTracks.videoTrack.setDevice(currentCam.deviceId);
    console.log('camero jova deviceId : ' + currentCam.deviceId);
}


//$("#tipBtn").click(function (e) {
//    
//    var sender_name = $("#cardholder-name").val();
//    var amount = $("#onlyNumber").val();
//
//
//    var view = '<p>' + sender_name + ' send you a tip amount $' + amount + '.</p>';
// 
//        
//      $('#tip_notification').append(view);
//    
//});


async function leave() {
    for (trackName in localTracks) {
        var track = localTracks[trackName];
        if (track) {
            track.stop();
            track.close();
            localTracks[trackName] = undefined;
        }
    }


    // remove remote users and player views
    remoteUsers = {};
    $("#remote-playerlist").html("");
    $(".chatbox").css("display", "none");

    // leave the channel
    await client.leave();

    $("#local-player-name").text("");
    $(".chat").css("display", "none");
    $("#full-screen-video").addClass('col-md-12');
    $("#full-screen-video").removeClass('col-md-6');
    $("#full-screen-video").removeClass('host-live');
    hideMuteButton();


    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    var _token = $('input[name="_token"]').val();
    $.ajax({
        type: "post",
        url: 'startStreaming',
        datatype: "json",
        data: {status: 0, _token: _token},
        success: function (response) {
            console.log(response.message);
        }
    });

    console.log("client leaves channel success");
}


async function leaveCohost() {
    for (trackName in localTracks) {
        var track = localTracks[trackName];
        if (track) {
            track.stop();
            track.close();
            localTracks[trackName] = undefined;
        }
    }


    // remove remote users and player views
    remoteUsers = {};
    $("#remote-playerlist").html("");

    // leave the channel
    await client.leave();

    $("#full-screen-video-user").addClass('col-md-12');
    $("#full-screen-video-user").addClass('host-live-full-screen');
    $("#full-screen-video-user").removeClass('col-md-6');
    $("#full-screen-video-user").removeClass('host-live');
    $("#videobutton").removeClass('cohost-video-btn');
    options.role = "audience"
    client.setClientRole(options.role);

    if (options.role === "audience") {
        // add event listener to play remote tracks when remote user publishs.
        client.on("user-published", handleUserPublished);
        client.on("user-joined", handleUserJoined);
        client.on("user-left", handleUserLeft);
        client.on("user-unpublished", handleUserUnpublished);
    }
    // join the channel
    options.uid = await client.join(options.appid, options.channel, options.token || null);



    console.log("CoHost leaves channel success");
}

async function audienceRemove(){
    client.on("user-published", handleUserPublished);
    client.on("user-unpublished", handleUserUnpublished);
     client.on("user-left", handleUserLeft);
}
async function hostleaveCohost() {
    //await client.leave( function(){ 
    for (trackName in remoteTracks) {
        var track = remoteTracks[trackName];
        if (track) {
            track.stop();
            track.close();
            //  client.unpublish(track);
            remoteTracks[trackName] = undefined;
        }
    }
    //  });

    //   await join();

    // remove remote users and player views
    // remoteUsers = {};
    //  $("#remote-playerlist").html("");

    $(".player").remove();
    $("#co-host-video").html("");
    // leave the channel
    await client.leave();

    //  client.unpublish(remoteTracks.videoTrack);

    $("#full-screen-video").addClass('col-md-12');
    $("#full-screen-video").removeClass('col-md-6');
    $("#videobtn").removeClass('cohost-video-btn');
    $("#videobtn").css("display", "none");


    console.log("CoHost leaves channel successed");
}


async function subscribe(user, mediaType) {
    const uid = user.uid;
    // subscribe to a remote user
    await client.subscribe(user, mediaType);
    console.log("subscribe success");


    // if the video wrapper element is not exist, create it.

    if (!user.audioTrack) {
        $("#mute-audio-host").html("<img src=" + btn.AudioMute + " />");
    } else {
        $("#mute-audio-host").html("<img src=" + btn.AudioUnmute + " />");
    }
    if (!user.videoTrack) {
        $("#mute-video-host").html("<img src=" + btn.videoMute + " />");
    } else {
        $("#mute-video-host").html("<img src=" + btn.videoUnmute + " />");
    }

    if (mediaType === 'video') {

        if ($(`#player-wrapper-${uid}`).length === 0) {
            const player = $(`<div class="audienceSide">
                    <div id="player-wrapper-${uid}" class="mainPlayer">
                        <div id="player-${uid}" class="player"></div>
                    </div>
                    </div>
                
      `);
            var DivSize = $("#full-screen-video-user div.mainPlayer").length;
            if (DivSize == 1) {
                $("#full-screen-video-user").addClass("twoHostJoin");
            } else {
                $("#full-screen-video-user").removeClass("twoHostJoin");
            }
            
            var DivSizeCheck = $(".checkhost div.mainPlayer").length;
            if (DivSizeCheck == 1) {
                $(".checkhost").addClass("twoCoHostJoin");
            } else {
                $(".checkhost").removeClass("twoCoHostJoin");
            }
            
            var VideoSize = document.getElementsByTagName("video").length;
            
            console.log('video length :' + VideoSize);
            
            

            $("#full-screen-video-user").append(player);
            //  $("#remote-playerlist").append(player);
//            <p class="streamer-name">remoteUser(${user.streamername})</p>
        }

        $("#full-screen-video").removeClass('col-md-12');
        $("#full-screen-video").addClass('col-md-6');
        $("#full-screen-video").addClass('host-live');
        $("#full-screen-video").removeClass('host-live-full-screen');
        $(".cohost").addClass('Cohost-live');
        $("#videobtn").addClass('cohost-video-btn');
        $("#videobutton").css("display", "none");
        $(".cohost-video-btn").css("display", "inline-block");

        // play the remote video.
        user.videoTrack.play(`player-${uid}`);
    }
    if (mediaType === 'audio') {
        user.audioTrack.play();
    }
}

async function unsubscribe(user, mediaType) {

    if (!user.audioTrack) {
        $("#mute-audio-host").html("<img src=" + btn.AudioMute + " />");
    } else {
        $("#mute-audio-host").html("<img src=" + btn.AudioUnmute + " />");
    }
    if (!user.videoTrack) {
        $("#mute-video-host").html("<img src=" + btn.videoMute + " />");
    } else {
        $("#mute-video-host").html("<img src=" + btn.videoUnmute + " />");
    }

}

function handleUserJoined(user) {
    const id = user.uid;
    remoteUsers[id] = user;
}

function handleUserLeft(user) {
    const id = user.uid;
    delete remoteUsers[id];
    $(`#player-wrapper-${id}`).remove();
}

function handleUserPublished(user, mediaType) {
    subscribe(user, mediaType);
}

//function handleUserUnpublished(user) {
//    const id = user.uid;
//    delete remoteUsers[id];
//    $(`#player-wrapper-${id}`).remove();
//}

function handleUserUnpublished(user, mediaType) {
    unsubscribe(user, mediaType);

}


function hideMuteButton() {
    $("#host-join").css("display", "none");
    $("#audience-join").css("display", "none");
    $(".chat").css("display", "none");
    $(".videButton").css("display", "none");
    $("#mute-video").css("display", "none");
    $("#mute-audio").css("display", "none");
    $("#full-video").css("display", "none");
    $("#switchBtn").css("display", "none");
    $("#leave").css("display", "none");
}

function showMuteButton() {
    $('#loading').hide();
    $('#loader').css("display", "none");
    $('#streaming').css("display", "none");
    $("#host-join").css("display", "none");
    $("#audience-join").css("display", "none");
    $(".chat").css("display", "inline-block");
    $(".host-video-btn").css("display", "inline-block");
    $("#mute-video").css("display", "inline-block");
    $("#mute-audio").css("display", "inline-block");
    $("#switchBtn").css("display", "inline-block");
    $(".mute-video-host").css("display", "inline-block");
    $(".mute-audio-host").css("display", "inline-block");
    $("#full-video").css("display", "inline-block");
    $("#half-screen-video").css("display", "inline-block");
    $(".tip-btn").css("display", "inline-block");
    $(".leave-btn").css("display", "inline-block");
    $("#leave").css("display", "inline-block");
    $(".chatbox").css("display", "inline-block");


}

async function muteAudio() {
    if (!localTracks.audioTrack)
        return;
    await localTracks.audioTrack.setEnabled(false);
    localTrackState.audioTrackEnabled = false;
    //$("#mute-audio").text("Unmute Audio");
    $("#mute-audio").html("<img src=" + btn.AudioMute + " />");
}

async function muteVideo() {
    if (!localTracks.videoTrack)
        return;
    await localTracks.videoTrack.setEnabled(false);
    localTrackState.videoTrackEnabled = false;
    //$("#mute-video").text("Unmute Video");
    $("#mute-video").html("<img src=" + btn.videoMute + " />");
}

async function unmuteAudio() {
    if (!localTracks.audioTrack)
        return;
    await localTracks.audioTrack.setEnabled(true);
    localTrackState.audioTrackEnabled = true;
    //$("#mute-audio").text("Mute Audio");
    $("#mute-audio").html("<img src=" + btn.AudioUnmute + " />");
}

async function unmuteVideo() {
    if (!localTracks.videoTrack)
        return;
    await localTracks.videoTrack.setEnabled(true);
    localTrackState.videoTrackEnabled = true;
    //$("#mute-video").text("Mute Video");
    $("#mute-video").html("<img src=" + btn.videoUnmute + " />");
}

$("#full-video").click(function () {

    $('.navbar').css("display", "none");
    $('footer').css("display", "none");
    $('#hide-full-screen').css("display", "none");
    $('#full-screen-video').css("display", "inline-block");
    localTracks.videoTrack.play("full-screen-video");

//	var elem = document.getElementById(document.querySelector('.agora_video_player').id);
//	if (elem.requestFullscreen) {
//		elem.requestFullscreen();
//	} else if (elem.webkitRequestFullscreen) { /* Safari */
//		elem.webkitRequestFullscreen();
//	} else if (elem.msRequestFullscreen) { /* IE11 */
//		elem.msRequestFullscreen();
//	}
});

$('#half-screen-video').click(function () {
    $('.navbar').css("display", "inline-block");
    $('footer').css("display", "inline-block");
    $('#hide-full-screen').css("display", "inline-block");
    $('#full-screen-video').css("display", "none");
    $('#full-screen-video-user').css("display", "none");
    $('#remote-player').css("display", "none");
    localTracks.videoTrack.play("local-player");

    //  user.videoTrack.play(`player-${uid}`)

});

function initStats() {
    statsInterval = setInterval(flushStats, 2000);
}

function flushStats() {
    // get the client stats message
    client.on("user-published", handleUserPublished);
    client.on("user-unpublished", handleUserUnpublished);

}


async function CoHost() {

    options.role = "host";
    $("#join-host-form").submit();


}

$("#join-host-form").submit(async function (e) {

    e.preventDefault();

    try {
        options.appid = $("#app_Id").val();
        options.token = $("#hosttoken").val();
        options.channel = $("#hostchannel").val();
        options.streamername = $("#streamername").val();

        leave()

        client.setClientRole(options.role);

        // get cameras
        cams = await AgoraRTC.getCameras();
        console.log('camera :' + JSON.stringify(cams));
        currentCam = cams[0];


        Object.entries(cams).forEach(([key, cam]) => {

            //   $("#switchBtn1").text(cam.deviceId);
            if (key == 0) {
                $("#switchBtn1").attr('data-camlabel', cam.label);
                $("#switchBtn1").attr('data-camid', cam.deviceId);


                //console.log(`key ${key}: ${cam.label}`);

            }
            if (key == 1) {

                $("#switchBtn2").attr('data-camlabel', cam.label);
                $("#switchBtn2").attr('data-camid', cam.deviceId);

                //      console.log(`key 2: ${cam.label}`);
        }
        });

        // join the channel
        options.uid = await client.join(options.appid, options.channel, options.token || null);

        if (options.role === "host") {
            // create local audio and video tracks
            localTracks.audioTrack = await AgoraRTC.createMicrophoneAudioTrack();
            localTracks.videoTrack = await AgoraRTC.createCameraVideoTrack();
            // play local video track
            // localTracks.videoTrack.play("local-player");
            remoteTracks.audioTrack = localTracks.audioTrack
            remoteTracks.videoTrack = localTracks.videoTrack
            localTracks.videoTrack.play("co-host-video");


            $("#local-player-name").text(`remoteTracks(${options.uid})`);
            $("#streamer-name").text(`${options.streamername}`);
            // publish local tracks to channel
            await client.publish(Object.values(remoteTracks));

            console.log("publish success");


        }

        //initStats();
        $("#videobutton").addClass('cohost-video-btn');
        $(".cohost-video-btn").css("display", "inline-block");

        showMuteButton();


    } catch (error) {
        console.error(error);
    } finally {
        $("#leave").attr("disabled", false);
    }
})


