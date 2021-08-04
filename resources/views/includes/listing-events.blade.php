<div class="card card-updates h-100">
    <div class="card-cover" style="background: @if ($response->event_img != '') url({{ Helper::getFile(config('path.event').$response->event_img) }})  @endif #505050 center center; background-size: cover;">

    </div>

    <div class="card-body">




        <h6 class="m-0 pb-1 text-muted card-text">
            <i class="fa fa-calendar-alt mr-1" aria-hidden="true"></i>  {{ $response->start_date }}
        </h6>

        <h6 class="m-0 pb-1 text-muted card-text">
            <i class="fa fa-clock mr-1" aria-hidden="true"></i> {{ $response->end_date }}
        </h6>


        <h6 class="m-0 pb-1 text-muted card-text">
            <i class="fa fa-user mr-1" aria-hidden="true"></i>  {{ $response->event_name }}
        </h6> 
        <h6 class="m-0 pb-1 text-muted card-text">
            <i class="fa fa-globe mr-1" aria-hidden="true"></i>  {{ $response->event_type }}
        </h6>


        <h6 class="m-0 py-1 text-muted card-text">
            <i class="fa fa-map-marker-alt mr-1" aria-hidden="true"></i>  {{ $response->event_place }}
        </h6>

        <div id="showCountedInterest_{{$response->id}}">
            @foreach (DB::table('event_interest')->select('interest', DB::raw('count(interest) as count'))->where('event_id', $response->id)->where('event_user_id', $response->user_id)->groupBy('interest')->get() as $event_interest)
            <small class="m-0  pb-3 text-muted card-text">
                @if($event_interest->interest == 'interested')
                <i class="fa fa-circle pb-3" aria-hidden="true" style="font-size: 4px;"></i>
                {{$event_interest->count}} {{trans('general.interested')}} @endif



                @if($event_interest->interest == 'going')
                <i class="fa fa-circle pb-3" aria-hidden="true" style="font-size: 4px;"></i>
                {{ $event_interest->count }} {{trans('general.going')}}  @endif 

            </small>
            @endforeach
        </div>

        <!--        <a href="javascript:void(0);" class="btn btn-1 btn-sm btn-outline-primary">{{trans('general.interested')}}</a>-->

        <div class="dropdown">
            @php
            $eventsInterest = DB::table('event_interest')->where('event_id', $response->id)->where('event_user_id', $response->user_id)->where('user_id', auth()->user()->id)->get();
            @endphp

            @if(isset($eventsInterest))
            @forelse($eventsInterest As $key => $value)

            <button class="btn btn-secondary dropdown-toggle dropdownMenuButton_{{$response->id}}" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" @if($value->event_id == $response->id && $value->user_id == auth()->user()->id && $value->event_user_id == $response->user_id) style="color:#c56cf0" @endif>

                @if($value->interest == 'interested')
                <i class="fa fa-star" aria-hidden="true"></i>
                {{trans('general.interested')}}
                @elseif($value->interest == 'going')
                <i class="fa fa-check-circle" aria-hidden="true"></i>
                {{trans('general.going')}}
                @elseif($value->interest == 'not_interested')
                <i class="fas fa-times-circle" aria-hidden="true"></i>
                {{trans('general.not_interested')}}
                @endif
            </button>
            @empty
            <button class="btn btn-secondary dropdown-toggle dropdownMenuButton_{{$response->id}}" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="far fa-star" aria-hidden="true"></i>
                {{trans('general.interested')}}

            </button>
            @endforelse

            @endif

            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">

                <div id="activeusr" style="width: 200px;">

                    <a class="dropdown-item dropdown-navbar dropdown-lang " href="javascript:;" onclick="checkInterest('interested','{{ $response->user_id }}','{{ $response->id }}')">
                        <i class="far fa-star" aria-hidden="true"></i> {{trans('general.interested')}}
<!--                        <input type="radio" value="interested" name="interest" id="interested">-->
                    </a>

                    <a class="dropdown-item dropdown-navbar dropdown-lang " href="javascript:;" onclick="checkInterest('going','{{ $response->user_id }}','{{ $response->id }}')">
                        <i class="fa fa-check-circle" aria-hidden="true"></i> {{trans('general.going')}}
<!--                        <input type="radio" value="going" name="interest" id="going">-->
                    </a>

                    <a class="dropdown-item dropdown-navbar dropdown-lang " href="javascript:;" onclick="checkInterest('not_interested','{{ $response->user_id }}','{{ $response->id }}')">
                        <i class="fas fa-times-circle" aria-hidden="true"></i> {{trans('general.not_interested')}}
<!--                        <input type="radio" value="not_interested" name="interest" id="not_interested">-->
                    </a>


                </div>

            </div>
        </div>

    </div>
</div><!-- End Card -->
