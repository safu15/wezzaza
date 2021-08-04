@if ($allMessages > 10 && $counter >= 1)
<div class="btn-block text-center wrap-container" data-total="{{ $allMessages }}" data-id="{{ $user->id }}">
  <a href="javascript:void(0)" class="loadMoreMessages d-none" id="paginatorChat">
    — {{ trans('general.load_messages') }}
    (<span class="counter">{{$counter}}</span>)
  </a>
</div>
@endif

@foreach ($messages as $msg)

  @php

  $checkPayPerView = auth()->user()->payPerViewMessages()->where('messages_id', $msg->id)->first();

  if ($msg->from_user_id  == Auth::user()->id) {
     $avatar   = $msg->to()->avatar;
     $name     = $msg->to()->name;
     $userID   = $msg->to()->id;
     $username = $msg->to()->username;

  } else if ($msg->to_user_id  == Auth::user()->id) {
     $avatar   = $msg->from()->avatar;
     $name     = $msg->from()->name;
     $userID   = $msg->from()->id;
     $username = $msg->from()->username;
  }

  if ( ! request()->ajax()) {
    $classInvisible = 'invisible';
  } else {
    $classInvisible = null;
  }

  $imageMsg = url('files/messages', $msg->id).'/'.$msg->file;

  if ($msg->file != '' && $msg->format == 'image') {
    $messageChat = '<a href="'.$imageMsg.'" data-group="gallery'.$msg->id.'" class="js-smartPhoto">
    <div class="container-media-img" style="background-image: url('.$imageMsg.')"></div>
    </a>';
  } elseif ($msg->file != '' && $msg->format == 'video') {
    $messageChat = '<div class="container-media-msg h-auto"><video class="js-player '.$classInvisible.'" controls>
      <source src="'.Helper::getFile(config('path.messages').$msg->file).'" type="video/mp4" />
    </video></div>
    ';
  } elseif ($msg->file != '' && $msg->format == 'music') {
    $messageChat = '<div class="container-media-music"><audio class="js-player '.$classInvisible.'" controls>
      <source src="'.Helper::getFile(config('path.messages').$msg->file).'" type="audio/mp3">
      Your browser does not support the audio tag.
    </audio></div>';
  } elseif ($msg->file != '' && $msg->format == 'zip') {
    $messageChat = '<a href="'.url('download/message/file', $msg->id).'" class="d-block text-decoration-none">
     <div class="card">
       <div class="row no-gutters">
         <div class="col-md-3 text-center bg-primary">
           <i class="far fa-file-archive m-2 text-white" style="font-size: 40px;"></i>
         </div>
         <div class="col-md-9">
           <div class="card-body py-2 px-4">
             <h6 class="card-title text-primary text-truncate mb-0">
               '.$msg->original_name.'.zip
             </h6>
             <p class="card-text">
               <small class="text-muted">'.$msg->size.'</small>
             </p>
           </div>
         </div>
       </div>
     </div>
     </a>';
  } elseif ($msg->tip == 'yes') {
    $messageChat = '<div class="card">
       <div class="row no-gutters">
         <div class="col-md-12">
           <div class="card-body py-2 px-4">
             <h6 class="card-title text-primary text-truncate mb-0">
               <i class="fa fa-donate mr-1"></i> '.__('general.tip'). ' -- ' .Helper::amountWithoutFormat($msg->tip_amount).'
             </h6>
           </div>
         </div>
       </div>
     </div>';
  } else {
    $messageChat = Helper::linkText(Helper::checkText($msg->message));
  }

  if ($msg->file != '') {
    $chatMessage = Helper::linkText(Helper::checkText($msg->message));
  }

@endphp

@if ($msg->from()->id == auth()->user()->id)
<div data="{{$msg->id}}" class="media py-2 chatlist">
<div class="media-body position-relative">
  @if ($msg->tip == 'no')
  <a href="javascript:void(0);" class="btn-removeMsg removeMsg" data="{{$msg->id}}" title="{{trans('general.delete')}}">
    <i class="fa fa-trash-alt"></i>
    </a>
  @endif

  <div class="position-relative text-word-break message @if ($msg->file == '' && $msg->tip == 'no') bg-primary @else media-container @endif text-white m-0 @if ($msg->format == 'zip') w-50 @else w-auto @endif float-right rounded-bottom-right-0">
    {!! $messageChat !!}
  </div>

  @if ($msg->file != '' && $msg->message != '')
    <div class="w-100 d-inline-block">
      <div class="w-auto position-relative text-word-break message bg-primary float-right text-white rounded-top-right-0">
        {!! $chatMessage !!}
      </div>
    </div>
@endif

    <span class="w-100 d-block text-muted float-right text-right pr-1 small">

      @if ($msg->price != 0.00)
        {{ Helper::amountFormatDecimal($msg->price) }} <i class="feather icon-lock mr-1"></i> -
      @endif

      <span class="timeAgo" data="{{ date('c', strtotime($msg->created_at)) }}"></span>
    </span>
</div><!-- media-body -->

<a href="{{url($msg->from()->username)}}" class="align-self-end ml-3 d-none">
  <img src="{{Helper::getFile(config('path.avatar').$msg->from()->avatar)}}" class="rounded-circle" width="50" height="50">
</a>
</div><!-- media -->

@else
<div data="{{$msg->id}}" class="media py-2 chatlist">
<a href="{{url($msg->from()->username)}}" class="align-self-end mr-3">
  <img src="{{Helper::getFile(config('path.avatar').$msg->from()->avatar)}}" class="rounded-circle avatar-chat" width="50" height="50">
</a>

<div class="media-body position-relative">

  @if ($msg->price != 0.00 && ! $checkPayPerView)

    <div class="btn-block p-sm text-center content-locked pt-lg pb-lg px-3 custom-rounded float-left">
    		<span class="btn-block text-center mb-3">
          <i class="feather ico-no-result border-0 icon-lock"></i></span>
        <a href="javascript:void(0);" data-toggle="modal" data-target="#payPerViewForm" data-mediaid="{{$msg->id}}" data-price="{{Helper::amountFormatDecimal($msg->price)}}" data-pricegross="{{$msg->price}}" class="btn btn-primary w-100">
          <i class="feather icon-unlock mr-1"></i> {{ trans('general.unlock_for') }} {{Helper::amountFormatDecimal($msg->price)}}
        </a>

        @if ($msg->format == 'image')
          <h6 class="btn-block mt-2 font-weight-light"><i class="feather icon-image"></i> {{ __('general.photo') }}</h6>
        @endif

        @if ($msg->format == 'video')
          <h6 class="btn-block mt-2 font-weight-light"><i class="feather icon-video"></i> {{ __('general.video') }}</h6>
        @endif

        @if ($msg->format == 'music')
          <h6 class="btn-block mt-2 font-weight-light"><i class="feather icon-mic"></i> {{ __('general.audio') }}</h6>
        @endif

        @if ($msg->format == 'zip')
          <h6 class="btn-block mt-2 font-weight-light"><i class="far fa-file-archive"></i> {{ __('general.file') }} - {{ $msg->size }}</h6>
        @endif

        @if ($msg->file == '')
          <h6 class="btn-block mt-2 font-weight-light"><i class="feather icon-file-text"></i> {{ __('admin.text') }}</h6>
        @endif

      </div>
    @endif

@if ($msg->price == 0.00 || $msg->price != 0.00 && $checkPayPerView)
  <div class="position-relative text-word-break message @if ($msg->file == '' && $msg->tip == 'no') bg-light @else media-container @endif m-0 @if ($msg->format == 'zip') w-50 @else w-auto @endif float-left rounded-bottom-left-0">
    {!! $messageChat !!}
  </div>
  @endif

  @if ($msg->file != '' && $msg->message != '')
    <div class="w-100 d-inline-block">
      <div class="w-auto position-relative text-word-break message bg-light float-left rounded-top-left-0">
        {!! $chatMessage !!}
      </div>
  </div>
@endif

<span class="w-100 d-block text-muted float-left pl-1 small">

    <span class="timeAgo" data="{{ date('c', strtotime($msg->created_at)) }}"></span>

  @if ($msg->price != 0.00)
    - {{ Helper::amountFormatDecimal($msg->price) }} {{ $checkPayPerView ? __('general.paid') : null }} <i class="feather icon-lock mr-1"></i>
  @endif
</span>
</div><!-- media-body -->
</div><!-- media -->
@endif
@endforeach
