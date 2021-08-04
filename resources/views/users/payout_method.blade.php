@extends('layouts.app')

@section('title') {{trans('users.payout_method')}} -@endsection

@section('content')
<section class="section section-sm">
    <div class="container">
        <div class="row justify-content-center text-center mb-sm">
            <div class="col-lg-8 py-5">
                <h2 class="mb-0 font-montserrat"><i class="bi bi-credit-card mr-2"></i> {{trans('users.payout_method')}}</h2>
                <p class="lead text-muted mt-0">{{trans('general.default_payout_method')}}:
                    @if(Auth::user()->payment_gateway != '') <strong class="text-success">
                        @if (auth()->user()->payment_gateway == 'PayPal')

                        PayPal

                        @elseif(auth()->user()->payment_gateway == 'Bank')

                        {{ trans('users.bank_transfer') }}

                        @elseif(auth()->user()->payment_gateway == 'FCIB')

                        FCIB 1st Pay (Barbados Only)

                        @endif
                        @else <strong class="text-danger">{{trans('general.none')}}</strong> @endif
                </p>
            </div>
        </div>
        <div class="row">

            @include('includes.cards-settings')

            <div class="col-md-6 col-lg-9 mb-5 mb-lg-0">

                @if (session('status'))
                <div class="alert alert-success">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    {{ session('status') }}
                </div>
                @endif

                @include('errors.errors-forms')

                @if (auth()->user()->verified_id != 'yes')
                <div class="alert alert-danger mb-3">
                    <ul class="list-unstyled m-0">
                        <li><i class="fa fa-exclamation-triangle"></i> {{trans('general.verified_account_info')}} <a href="{{url('settings/verify/account')}}" class="text-white link-border">{{trans('general.verify_account')}}</a></li>
                    </ul>
                </div>
                @endif

                @if (auth()->user()->verified_id == 'yes')
                <div class="row justify-content-center">

                    @php

                    // PayPal
                    $buttonPayPal = null;
                    $formPayPal = null;

                    // Bank
                    $buttonBank = null;
                    $formBank = null;


                    // FCIB
                    $buttonFCIB = null;
                    $formFCIB = null;

                    // WisePayment
                    $buttonWisePayment = null;
                    $formWisePayment = null;
                    
                    // BankWire
                    $buttonBankWire = null;
                    $formBankWire = null;


                    if ($errors->has('bank_details')) {

                    // Bank
                    $buttonBank = ' active';
                    $formBank = ' active show';

                    // PayPal
                    $buttonPayPal = null;
                    $formPayPal = null;

                    // FCIB
                    $buttonFCIB = null;
                    $formFCIB = null;

                    // WisePayment
                    $buttonWisePayment = null;
                    $formWisePayment = null;
                    
                      // BankWire
                    $buttonBankWire = null;
                    $formBankWire = null;


                    }

                    if ($errors->has('email_paypal') || $errors->has('email_paypal_confirmation')) {

                    // PayPal
                    $buttonPayPal = ' active';
                    $formPayPal = ' active show';

                    // Bank
                    $buttonBank = null;
                    $formBank = null;

                    // FCIB
                    $buttonFCIB = null;
                    $formFCIB = null;

                    // WisePayment
                    $buttonWisePayment = null;
                    $formWisePayment = null;
                    
                      // BankWire
                    $buttonBankWire = null;
                    $formBankWire = null;

                    }



                    if ($errors->has('email_fcib') || $errors->has('mobile_fcib')) {

                    // FCIB
                    $buttonFCIB = ' active';
                    $formFCIB = ' active show';

                    // PayPal
                    $buttonPayPal = null;
                    $formPayPal = null;

                    // Bank
                    $buttonBank = null;
                    $formBank = null;

                    // WisePayment
                    $buttonWisePayment = null;
                    $formWisePayment = null;
                    
                      // BankWire
                    $buttonBankWire = null;
                    $formBankWire = null;

                    }

                    if ($errors->has('wise_account_holder_name') || $errors->has('wise_address1') || $errors->has('wise_city') || $errors->has('wise_zip') || $errors->has('wise_countries_id') || $errors->has('wise_account_number') || $errors->has('wise_iban') || $errors->has('wise_bic') || $errors->has('wise_ammount') || $errors->has('wise_currency')) {

                    // WisePayment
                    $buttonWisePayment = ' active';
                    $formWisePayment = ' active show';

                    // PayPal
                    $buttonPayPal = null;
                    $formPayPal = null;

                    // Bank
                    $buttonBank = null;
                    $formBank = null;

                    // FCIB
                    $buttonFCIB = null;
                    $formFCIB = null;
                    
                      // BankWire
                    $buttonBankWire = null;
                    $formBankWire = null;
                    }
                    
                    if ($errors->has('bank_wire_account_holder_name') || $errors->has('bank_wire_address1') || $errors->has('bank_wire_city') || $errors->has('bank_wire_zip') || $errors->has('bank_wire_countries_id') || $errors->has('bank_wire_account_number') || $errors->has('bank_wire_iban') || $errors->has('bank_wire_bic') || $errors->has('bank_wire_currency')) {

                         // BankWire
                    $buttonBankWire = ' active';
                    $formBankWire = ' active show';
                    
                    // WisePayment
                    $buttonWisePayment = null;
                    $formWisePayment = null;

                    // PayPal
                    $buttonPayPal = null;
                    $formPayPal = null;

                    // Bank
                    $buttonBank = null;
                    $formBank = null;

                    // FCIB
                    $buttonFCIB = null;
                    $formFCIB = null;
                    
                 
                    }


                    @endphp

                    <div class="col-md-12">
                        <div class="nav-wrapper">
                            <ul class="nav nav-pills nav-fill flex-md-row" role="tablist">
                                @if( $settings->payout_method_paypal == 'on' )
                                <li class="nav-item">
                                    <a class="nav-link link-nav mb-sm-6 mb-md-6 mb-2 p-4{{$buttonPayPal}}" id="btnPayPal" data-toggle="tab" href="#formPayPal" role="tab" aria-controls="formPayPal" aria-selected="true">
                                        <i class="fab fa-paypal mr-2"></i> PayPal
                                        @if (auth()->user()->payment_gateway == 'PayPal') <span class="badge badge-pill badge-success">{{ __('general.default') }}</span> @endif
                                    </a>
                                </li>
                                @endif

                                @if( $settings->payout_method_bank == 'on' )
                                <li class="nav-item">
                                    <a class="nav-link link-nav mb-sm-6 mb-md-6 mb-2 p-4{{$buttonBank}}" id="btnBank" data-toggle="tab" href="#formBank" role="tab" aria-controls="formBank" aria-selected="false">
                                        <i class="fa fa-university mr-2"></i> {{trans('users.bank_transfer')}}
                                        @if (auth()->user()->payment_gateway == 'Bank') <span class="badge badge-pill badge-success">{{ __('general.default') }}</span> @endif
                                    </a>
                                </li>
                                @endif

                                @if( $settings->payout_method_fcib_pay == 'on' )
                                <li class="nav-item">
                                    <a class="nav-link link-nav mb-sm-6 mb-md-6 mb-2 p-4{{$buttonFCIB}}" id="btnFCIB" data-toggle="tab" href="#formFCIB" role="tab" aria-controls="formFCIB" aria-selected="false">
                                        <i class="fa fa-university mr-2"></i> FCIB 1st Pay (Barbados Only)
                                        @if (auth()->user()->payment_gateway == 'FCIB') <span class="badge badge-pill badge-success">{{ __('general.default') }}</span> @endif
                                    </a>
                                </li>
                                @endif

                                @if( $settings->payout_method_bank_wire == 'on' )
                                <li class="nav-item">
                                    <a class="nav-link link-nav mb-sm-6 mb-md-6 mb-2 p-4{{$buttonBankWire}}" id="btnWisePayment" data-toggle="tab" href="#formBankWire" role="tab" aria-controls="formformBankWire" aria-selected="false">
                                        <i class="fa fa-university mr-2"></i> {{trans('general.bank_wire')}}
                                        @if (auth()->user()->payment_gateway == 'BankWire') <span class="badge badge-pill badge-success">{{ __('general.default') }}</span> @endif
                                    </a>
                                </li>
                                @endif
                                
                                @if( $settings->payout_method_wise_payment == 'on' )
                                <li class="nav-item">
                                    <a class="nav-link link-nav mb-sm-6 mb-md-6 mb-2 p-4{{$buttonWisePayment}}" id="btnWisePayment" data-toggle="tab" href="#formWisePayment" role="tab" aria-controls="formWisePayment" aria-selected="false">
                                        <i class="fa fa-university mr-2"></i> {{trans('general.wise_payment')}}
                                        @if (auth()->user()->payment_gateway == 'WisePayment') <span class="badge badge-pill badge-success">{{ __('general.default') }}</span> @endif
                                    </a>
                                </li>
                                @endif

                            </ul>
                        </div><!-- END COL-MD-12 -->
                    </div><!-- ./ ROW -->
                </div><!-- ./ nav-wrapper -->

                <div class="tab-content">

                    @if( $settings->payout_method_paypal == 'on' )
                    <!-- FORM PAYPAL -->
                    <div id="formPayPal" class="tab-pane fade{{$formPayPal}}" role="tabpanel">
                        <form method="POST" action="{{ url('settings/payout/method/paypal') }}">
                            @csrf

                            <div class="form-group">
                                <div class="input-group mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fab fa-paypal"></i></span>
                                    </div>
                                    <input class="form-control" name="email_paypal" value="{{Auth::user()->paypal_account == '' ? old('email_paypal') : Auth::user()->paypal_account}}" placeholder="{{trans('general.email_paypal')}}" required type="email">
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="input-group mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="far fa-envelope"></i></span>
                                    </div>
                                    <input class="form-control" name="email_paypal_confirmation" placeholder="{{trans('general.confirm_email_paypal')}}" required type="email">
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="text-muted btn-block">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" name="make_default_paypal" value="yes" id="make_default_paypal" @if (auth()->user()->payment_gateway == 'PayPal') checked @endif>
                                        <label class="custom-control-label switch" for="make_default_paypal">{{trans('general.make_default')}}</label>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-1 btn-success btn-block" type="submit">{{trans('general.save_payout_method')}}</button>
                        </form>
                    </div>
                    @endif

                    @if( $settings->payout_method_bank == 'on' )
                    <!-- FORM BANK TRANSFER -->
                    <div id="formBank" class="tab-pane fade{{$formBank}}" role="tabpanel">

                        <form method="POST" action="{{ url('settings/payout/method/bank') }}">
                            @csrf

                            <div class="form-group">
                                <div class="input-group mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-user"></i></span>
                                    </div>
                                    <input class="form-control" name="account_holder_name" value="{{Auth::user()->account_holder_name == '' ? old('account_holder_name') : Auth::user()->account_holder_name}}" placeholder="{{trans('general.account_holder_name')}}" required type="text">
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="input-group mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-university"></i></span>
                                    </div>
<!--                                    <input class="form-control" name="bank_name" value="{{Auth::user()->bank_name == '' ? old('bank_name') : Auth::user()->bank_name}}" placeholder="{{trans('general.bank_name')}}" required type="text">-->

                                    <select class="form-control custom-select browser-default" name="bank_name" required id="bank_name">

                                        <option value="" disabled selected>{{trans('general.bank_name')}}</option>

                                        @foreach (DB::table('bankDetails')->orderBy('id')->get() as $bankdetails)
                                        <option value="{{ $bankdetails->id}}" @if (auth()->user()->bank_name == $bankdetails->id) selected="selected" @endif>{{ $bankdetails->bank_name}}</option>
                                        @endforeach

                                    </select>

                                </div>
                            </div>
                            <div class="form-group">
                                <input class="form-control" name="bic" id="bic" value="{{Auth::user()->bic == '' ? old('bic') : Auth::user()->bic}}" placeholder="{{trans('general.bic')}}" required readonly type="text">
                            </div>
                            <div class="form-group">
                                <div class="input-group mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class=""></i></span>
                                    </div>
<!--                                    <input class="form-control" name="recipient_type" value="{{Auth::user()->recipient_type == '' ? old('recipient_type') : Auth::user()->recipient_type}}" placeholder="{{trans('general.recipient_type')}}" required type="text">-->
                                    <select class="form-control custom-select browser-default" name="recipient_type" required>

                                        <option value="" disabled selected>{{trans('general.recipient_type')}}</option>
                                        <option value="{{trans('general.business')}}" @if (auth()->user()->recipient_type == "Business") selected="selected" @endif>{{trans('general.business')}}</option>
                                        <option value="{{trans('general.personal')}}" @if (auth()->user()->recipient_type == "Personal") selected="selected" @endif>{{trans('general.personal')}}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="input-group mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class=""></i></span>
                                    </div>
<!--                                    <input class="form-control" name="account_type" value="{{Auth::user()->account_type == '' ? old('account_type') : Auth::user()->account_type}}" placeholder="{{trans('general.account_type')}}" required type="text">-->

                                    <select class="form-control custom-select browser-default" name="account_type" required>

                                        <option value="" disabled selected>{{trans('general.account_type')}}</option>
                                        <option value="{{trans('general.savings')}}" @if (auth()->user()->account_type == "Savings") selected="selected" @endif>{{trans('general.savings')}}</option>
                                        <option value="{{trans('general.chequing')}}" @if (auth()->user()->account_type == "Chequing") selected="selected" @endif>{{trans('general.chequing')}}</option>
                                    </select>

                                </div>
                            </div>
                            <div class="form-group">
                                <div class="input-group mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-code-branch"></i></span>
                                    </div>
<!--                                    <input class="form-control" name="branch" value="{{Auth::user()->branch == '' ? old('branch') : Auth::user()->branch}}" placeholder="{{trans('general.branch')}}" required type="text">-->
                                    <select class="form-control custom-select browser-default" name="branch" required id="branch">

                                        <option value="" disabled selected>Select Branch</option>

                                        @foreach (DB::table('bankBranches')->where('bank_id', auth()->user()->bank_name)->orderBy('id')->get() as $bankbranches)

                                        <option value="{{ $bankbranches->id }}" @if (auth()->user()->branch == $bankbranches->id) selected  @endif>{{ $bankbranches->branch }}</option>
                                       
                                        @endforeach
                                       
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="input-group mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-list-ol"></i></span>
                                    </div>
                                    <input class="form-control" name="account_number" value="{{Auth::user()->account_number == '' ? old('account_number') : Auth::user()->account_number}}" placeholder="{{trans('general.account_number')}}" required type="number" onkeypress="return isNumber(event)">
                                </div>
                            </div>
                            <div class="form-group">
<!--                                    <input class="form-control" name="account" value="{{Auth::user()->account == '' ? old('account') : Auth::user()->account}}" placeholder="{{trans('general.account')}}" required type="text">-->
                                <input class="form-control" name="account" value="" placeholder="{{trans('general.account')}}" required type="hidden">
                            </div>
                            <div class="form-group">
                                <input class="form-control" name="currency" value="{{Auth::user()->currency == '' ? 'BBD' : Auth::user()->currency}}" placeholder="{{trans('general.currency')}}" required readonly type="text">
                            </div>
                            <div class="form-group">
                                <label class="w-100 text-muted">
                                    <i class="fa fa-comment text-muted"></i> 
                                    {{trans('general.comment')}}
                                </label>
                                <textarea class="form-control" name="comment" rows="3" cols="40" placeholder="{{trans('general.comment')}}" >{{Auth::user()->comment == '' ? old('comment') : Auth::user()->comment}}</textarea>
                            </div>
                            <div class="form-group">
                                <div class="text-muted btn-block">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" name="make_default_bank" value="yes" id="make_default_bank" @if (auth()->user()->payment_gateway == 'Bank') checked @endif>
                                        <label class="custom-control-label switch" for="make_default_bank">{{trans('general.make_default')}}</label>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-1 btn-success btn-block" type="submit">{{trans('general.save_payout_method')}}</button>
                        </form>


                        <!--                        <form method="POST"  action="{{ url('settings/payout/method/bank') }}">
                        
                                                    @csrf
                                                    <div class="form-group">
                                                        <textarea name="bank_details" rows="5" cols="40" class="form-control" required placeholder="{{trans('users.bank_details')}}">{{Auth::user()->bank == '' ? old('bank_details') : Auth::user()->bank}}</textarea>
                                                    </div>
                                                    <button class="btn btn-1 btn-success btn-block" type="submit">{{trans('general.save_payout_method')}}</button>
                                                </form>-->
                    </div>
                    @endif

                    @if( $settings->payout_method_fcib_pay == 'on' )

                    <!-- FORM FCIB -->
                    <div id="formFCIB" class="tab-pane fade{{$formFCIB}}" role="tabpanel">
                        <form method="POST" action="{{ url('settings/payout/method/FCIB') }}">
                            @csrf

                            <div class="form-group">
                                <div class="input-group mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="far fa-envelope"></i></span>
                                    </div>
                                    <input class="form-control" name="email_fcib" value="{{Auth::user()->email_fcib == '' ? old('email_fcib') : Auth::user()->email_fcib}}" placeholder="{{trans('general.email_fcib')}}" required type="email">
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="input-group mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-phone-square"></i></span>
                                    </div>
                                    <input class="form-control" name="mobile_fcib" value="{{Auth::user()->mobile_fcib == '' ? old('email_fcib') : Auth::user()->mobile_fcib}}" placeholder="{{trans('general.mobile_fcib')}}" required type="number" onkeypress="return isNumber(event)">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="text-muted btn-block">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" name="make_default_fcib" value="yes" id="make_default_fcib" @if (auth()->user()->payment_gateway == 'FCIB') checked @endif >
                                        <label class="custom-control-label switch" for="make_default_fcib">{{trans('general.make_default')}}</label>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-1 btn-success btn-block" type="submit">{{trans('general.save_payout_method')}}</button>
                        </form>
                    </div>
                    @endif
                    
                    
                    @if( $settings->payout_method_bank_wire == 'on' )
                    <!-- FORM WISE PAYMENT -->
                    <div id="formBankWire" class="tab-pane fade{{$formBankWire}}" role="tabpanel">

                        <form method="POST" action="{{ url('settings/payout/method/BankWire') }}">
                            @csrf

                            <div class="form-group">
                                <div class="input-group mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-user"></i></span>
                                    </div>
                                    <input class="form-control" name="bank_wire_account_holder_name" value="{{Auth::user()->bank_wire_account_holder_name == '' ? old('bank_wire_account_holder_name') : Auth::user()->bank_wire_account_holder_name}}" placeholder="{{trans('general.account_holder_name')}}" required type="text">
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="input-group mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-map-marked-alt"></i></span>
                                    </div>
                                    <input class="form-control" name="bank_wire_address1" value="{{Auth::user()->bank_wire_address1 == '' ? old('bank_wire_address1') : Auth::user()->bank_wire_address1}}" placeholder="{{trans('general.address')}} 1" required type="text">
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="input-group mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-map-marked-alt"></i></span>
                                    </div>
                                    <input class="form-control" name="bank_wire_address2" value="{{Auth::user()->bank_wire_address2 == '' ? old('bank_wire_address2') : Auth::user()->bank_wire_address2}}" placeholder="{{trans('general.address')}} 2" required type="text">
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="input-group mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-map-pin"></i></span>
                                    </div>
                                    <input class="form-control" name="bank_wire_city" value="{{Auth::user()->bank_wire_city == '' ? old('bank_wire_city') : Auth::user()->bank_wire_city}}" placeholder="{{trans('general.city')}}" required type="text">
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="input-group mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-map-marker-alt"></i></span>
                                    </div>
                                    <input class="form-control" name="bank_wire_zip" value="{{Auth::user()->bank_wire_zip == '' ? old('bank_wire_zip') : Auth::user()->bank_wire_zip}}" placeholder="{{trans('general.zip')}}" required type="number" onkeypress="return isNumber(event)">
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="input-group mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-globe"></i></span>
                                    </div>
                                    <select name="bank_wire_countries_id" class="form-control custom-select browser-default">
                                        <option value="" disabled>{{trans('general.select_your_country')}} *</option>
                                        @foreach(  Countries::orderBy('country_name')->get() as $country )
                                        <option @if( auth()->user()->countries_id == $country->id ) selected="selected" @endif value="{{$country->id}}">{{ $country->country_name }}</option>
                                        @endforeach
                                    </select>        
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="input-group mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-list-ol"></i></span>
                                    </div>
                                    <input class="form-control" name="bank_wire_account_number" value="{{Auth::user()->bank_wire_account_number == '' ? old('bank_wire_account_number') : Auth::user()->bank_wire_account_number}}" placeholder="{{trans('general.account_number')}}" required type="number" onkeypress="return isNumber(event)">
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="input-group mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class=""></i></span>
                                    </div>
                                    <input class="form-control" name="bank_wire_iban" value="{{Auth::user()->bank_wire_iban == '' ? old('bank_wire_iban') : Auth::user()->bank_wire_iban}}" placeholder="IBAN" required type="text">
                                </div>
                            </div>

                            <div class="form-group">
                                <input class="form-control" name="bank_wire_bic" id="bank_wire_bic" value="{{Auth::user()->bank_wire_bic == '' ? old('bank_wire_bic') : Auth::user()->bank_wire_bic}}" placeholder="{{trans('general.bic')}}" required type="text">
                            </div>

                                <div class="form-group">
                                <input class="form-control" name="bank_wire_currency" value="{{Auth::user()->bank_wire_currency == '' ? old('bank_wire_currency') : Auth::user()->bank_wire_currency}}" placeholder="{{trans('general.currency')}}" required type="text">
                            </div>

                            <div class="form-group">
                                <label class="w-100 text-muted">
                                    <i class="fa fa-comment text-muted"></i> 
                                    {{trans('general.comment')}}
                                </label>
                                <textarea class="form-control" name="bank_wire_comment" rows="3" cols="40" placeholder="{{trans('general.comment')}}" >{{Auth::user()->bank_wire_comment == '' ? old('bank_wire_comment') : Auth::user()->bank_wire_comment}}</textarea>
                            </div>

                            <div class="form-group">
                                <div class="text-muted btn-block">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" name="make_default_bank_wire" value="yes" id="make_default_bank_wire" @if (auth()->user()->payment_gateway == 'BankWire') checked @endif>
                                        <label class="custom-control-label switch" for="make_default_bank_wire">{{trans('general.make_default')}}</label>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-1 btn-success btn-block" type="submit">{{trans('general.save_payout_method')}}</button>
                        </form>

                    </div>
                    @endif    

                    @if( $settings->payout_method_wise_payment == 'on' )
                    <!-- FORM WISE PAYMENT -->
                    <div id="formWisePayment" class="tab-pane fade{{$formWisePayment}}" role="tabpanel">

                        <form method="POST" action="{{ url('settings/payout/method/WisePayment') }}">
                            @csrf

                            <div class="form-group">
                                <div class="input-group mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-user"></i></span>
                                    </div>
                                    <input class="form-control" name="wise_account_holder_name" value="{{Auth::user()->wise_account_holder_name == '' ? old('wise_account_holder_name') : Auth::user()->wise_account_holder_name}}" placeholder="{{trans('general.account_holder_name')}}" required type="text">
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="input-group mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-map-marked-alt"></i></span>
                                    </div>
                                    <input class="form-control" name="wise_address1" value="{{Auth::user()->wise_address1 == '' ? old('wise_address1') : Auth::user()->wise_address1}}" placeholder="{{trans('general.address')}} 1" required type="text">
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="input-group mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-map-marked-alt"></i></span>
                                    </div>
                                    <input class="form-control" name="wise_address2" value="{{Auth::user()->wise_address2 == '' ? old('wise_address2') : Auth::user()->wise_address2}}" placeholder="{{trans('general.address')}} 2" required type="text">
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="input-group mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-map-pin"></i></span>
                                    </div>
                                    <input class="form-control" name="wise_city" value="{{Auth::user()->wise_city == '' ? old('wise_city') : Auth::user()->wise_city}}" placeholder="{{trans('general.city')}}" required type="text">
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="input-group mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-map-marker-alt"></i></span>
                                    </div>
                                    <input class="form-control" name="wise_zip" value="{{Auth::user()->wise_zip == '' ? old('wise_zip') : Auth::user()->wise_zip}}" placeholder="{{trans('general.zip')}}" required type="number" onkeypress="return isNumber(event)">
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="input-group mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-globe"></i></span>
                                    </div>
                                    <select name="wise_countries_id" class="form-control custom-select browser-default">
                                        <option value="" disabled>{{trans('general.select_your_country')}} *</option>
                                        @foreach(  Countries::orderBy('country_name')->get() as $country )
                                        <option @if( auth()->user()->countries_id == $country->id ) selected="selected" @endif value="{{$country->id}}">{{ $country->country_name }}</option>
                                        @endforeach
                                    </select>        
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="input-group mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-list-ol"></i></span>
                                    </div>
                                    <input class="form-control" name="wise_account_number" value="{{Auth::user()->wise_account_number == '' ? old('wise_account_number') : Auth::user()->wise_account_number}}" placeholder="{{trans('general.account_number')}}" required type="number" onkeypress="return isNumber(event)">
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="input-group mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class=""></i></span>
                                    </div>
                                    <input class="form-control" name="wise_iban" value="{{Auth::user()->wise_iban == '' ? old('wise_iban') : Auth::user()->wise_iban}}" placeholder="IBAN" required type="text">
                                </div>
                            </div>

                            <div class="form-group">
                                <input class="form-control" name="wise_bic" id="wise_bic" value="{{Auth::user()->wise_bic == '' ? old('wise_bic') : Auth::user()->wise_bic}}" placeholder="{{trans('general.bic')}}" required type="text">
                            </div>

                            <div class="form-group">
                                <input class="form-control" name="wise_ammount" value="{{Auth::user()->wise_ammount == '' ? old('wise_ammount') : Auth::user()->wise_ammount}}" placeholder="{{trans('Ammount')}}" required type="text">
                            </div>

                            <div class="form-group">
                                <input class="form-control" name="wise_currency" value="{{Auth::user()->wise_currency == '' ? old('wise_currency') : Auth::user()->wise_currency}}" placeholder="{{trans('general.currency')}}" required type="text">
                            </div>

                            <div class="form-group">
                                <label class="w-100 text-muted">
                                    <i class="fa fa-comment text-muted"></i> 
                                    {{trans('general.comment')}}
                                </label>
                                <textarea class="form-control" name="wise_comment" rows="3" cols="40" placeholder="{{trans('general.comment')}}" >{{Auth::user()->wise_comment == '' ? old('wise_comment') : Auth::user()->wise_comment}}</textarea>
                            </div>

                            <div class="form-group">
                                <div class="text-muted btn-block">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" name="make_default_wise" value="yes" id="make_default_wise" @if (auth()->user()->payment_gateway == 'WisePayment') checked @endif>
                                        <label class="custom-control-label switch" for="make_default_wise">{{trans('general.make_default')}}</label>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-1 btn-success btn-block" type="submit">{{trans('general.save_payout_method')}}</button>
                        </form>

                    </div>
                    @endif    


                </div><!-- ./ TAB-CONTENT -->
                @endif

            </div><!-- end col-md-6 -->

        </div>
    </div>
</section>
@endsection
