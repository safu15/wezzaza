<div class="menuMobile w-100 bg-white shadow-lg p-3 border-top">
    <ul class="list-inline d-flex bd-highlight m-0 text-center">

        <li class="list-inline-item flex-fill bd-highlight">
            <a class="p-2 btn-mobile" href="{{url('/')}}" title="{{trans('admin.home')}}">
                <i class="feather icon-home icon-navbar"></i>
            </a>
        </li>

        <li class="list-inline-item flex-fill bd-highlight">
            <a class="p-2 btn-mobile" href="{{url('creators')}}" title="{{trans('general.explore')}}">
                <i class="far	fa-compass icon-navbar"></i>
            </a>
        </li>

        <li class="list-inline-item flex-fill bd-highlight">
            <a href="{{url('messages')}}" class="p-2 btn-mobile position-relative" title="{{ trans('general.messages') }}">

                <span class="noti_msg notify @if (auth()->user()->messagesInbox() != 0) d-block @endif">
                    {{ auth()->user()->messagesInbox() }}
                </span>

                <i class="feather icon-send icon-navbar"></i>
            </a>
        </li>

        <li class="list-inline-item flex-fill bd-highlight">
            <a href="{{url('notifications')}}" class="p-2 btn-mobile position-relative" title="{{ trans('general.notifications') }}">

                <span class="noti_notifications notify @if (auth()->user()->notifications()->where('status', '0')->count()) d-block @endif">
                    {{ auth()->user()->notifications()->where('status', '0')->count() }}
                </span>

                <i class="far fa-bell icon-navbar"></i>
            </a>
        </li>

        <!--        <li class="list-inline-item flex-fill bd-highlight">
                    <a class="p-2 btn-mobile navbar-toggler-mobile" href="#"  data-toggle="collapse" data-target="#navbarCollapseEvent" aria-controls="navbarCollapseEvent" aria-expanded="false" role="button">
                        <img src="{{ asset('public/img/event.png') }}" alt="Event" width="25" height="25">
                    </a>
                </li>-->
        <li class="list-inline-item flex-fill bd-highlight dropdown">
            <a class="p-2 btn-mobile navbar-toggler-mobile" href="#" id="nav-inner-success_dropdown_1" role="button" data-toggle="dropdown">
                <img src="{{ asset('public/img/event.png') }}" alt="Event" width="25" height="25">
                <span class="d-lg-none"></span>
                <i class="feather icon-chevron-down m-0 align-middle"></i>
            </a>
            <div class="dropdown-menu mb-1 dropdown-menu-right dd-menu-user" aria-labelledby="nav-inner-success_dropdown_1">

                <a href="javascript:void(0);" data-toggle="modal" title="{{trans('general.create_event')}}" data-target="#eventForm" class="dropdown-item dropdown-navbar">
                    {{trans('general.create_event')}}
                </a>
                <div class="dropdown-divider dropdown-navbar"></div>

                <a class="dropdown-item dropdown-navbar" href="{{url('events')}}" title="{{trans('general.our_events')}}">
                    {{trans('general.our_events')}}
                </a>
            </div>
        </li>

        <li class="list-inline-item flex-fill bd-highlight">
            <a class="p-2 btn-mobile navbar-toggler-mobile" href="#"  data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" role="button">
                <i class="far fa-user-circle icon-navbar"></i>
            </a>
        </li>
    </ul>
</div>
