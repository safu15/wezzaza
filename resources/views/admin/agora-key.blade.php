@extends('admin.layout')

@section('css')
<link href="{{ asset('public/plugins/iCheck/all.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h4>
            {{ trans('admin.admin') }}
            <i class="fa fa-angle-right margin-separator"></i>
            {{ trans('general.agora_key') }}
        </h4>

    </section>

    <!-- Main content -->
    <section class="content">

        @if (session('success_message'))
        <div class="alert alert-success">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">×</span>
            </button>
            <i class="fa fa-check margin-separator"></i> {{ session('success_message') }}
        </div>
        @endif

        <div class="content">

            <div class="row">

                <div class="box">

                    <div class="box-header">
                        <h3 class="box-title">{{ trans('general.agora_key') }}</h3>
                    </div><!-- /.box-header -->

                    <!-- form start -->
                    <form class="form-horizontal" method="POST" action="{{ url('panel/admin/agora/key') }}" enctype="multipart/form-data">

                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        <!-- Start Box Body -->
                        <div class="box-body">
                            <div class="form-group">
                                <label class="col-sm-2 control-label">{{trans('general.agora_app_id')}}</label>
                                <div class="col-sm-10">
                                    <input type="text" value="{{ $settings->agora_app_id }}" name="AGORA_APP_ID" class="form-control" placeholder="{{trans('general.agora_app_id')}}">
                                </div>
                            </div>
                        </div><!-- /.box-body -->

                        <!-- Start Box Body -->
                        <div class="box-body">
                            <div class="form-group">
                                <label class="col-sm-2 control-label">{{trans('general.agora_app_certificate')}}</label>
                                <div class="col-sm-10">
                                    <input type="text" value="{{ $settings->agora_app_certificate }}" name="AGORA_APP_CERTIFICATE" class="form-control" placeholder="{{trans('general.agora_app_certificate')}}">
                                </div>
                            </div>
                        </div><!-- /.box-body -->

                        <div class="box-footer">
                            <button type="submit" class="btn btn-success" id="saveUpdate">{{ trans('admin.save') }}</button>
                        </div><!-- /.box-footer -->
                    </form>
                </div><!-- /.row -->
            </div><!-- /.content -->
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
@endsection
