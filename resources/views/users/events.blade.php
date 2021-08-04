@extends('layouts.app')

@section('title') {{trans('general.our_events')}} -@endsection

@section('javascript')
<!--<script type="text/javascript">
   
window.onload = function () {
    
    @if(Session::has('success'))
        var message = Session::get('success');
        toastr.success(message);
     @endif
    }
    
</script>-->

@endsection

@section('content')
<section class="section section-sm">
    <div class="container">

        <div class="row justify-content-center text-center mb-sm">
            <div class="col-lg-12 py-5">
                
                @if(Session::has('error'))
                <div class="alert alert-danger">
                    {{ Session::get('error') }}
                </div>
                
                @endif
                
                <h2 class="mb-0 text-break">{{trans('general.our_events')}}</h2>
                <p class="lead text-muted mt-0">

                </p>
                @if(Session::has('success'))
                <p>{{ Session::get('success') }}</p>
                @endif
                
               
                
            </div>
        </div>

        <div class="row">


            @foreach($events as $response)
            <div class="col-md-4 mb-4">
                @include('includes.listing-events')
            </div><!-- end col-md-4 -->
            @endforeach


        </div>
    </div>
</section>
@endsection
