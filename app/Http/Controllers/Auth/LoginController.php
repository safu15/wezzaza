<?php

namespace App\Http\Controllers\Auth;

use Validator;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\AdminSettings;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller {
    /*
      |--------------------------------------------------------------------------
      | Login Controller
      |--------------------------------------------------------------------------
      |
      | This controller handles authenticating users for the application and
      | redirecting them to your home screen. The controller uses a trait
      | to conveniently provide its functionality to your applications.
      |
     */

use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Guard $auth) {
        $this->auth = $auth;
        $this->middleware('guest')->except('logout');
    }

    /**
     * Show login form.
     */
    public function showLoginForm() {
        $settings = AdminSettings::first();

        if ($settings->home_style == 0) {
            return view('auth.login');
        } else {
            return redirect('/');
        }
    }

    public function login(Request $request) {
        if (!$request->expectsJson()) {
            abort(404);
        }

        $settings = AdminSettings::first();
        $request['_captcha'] = $settings->captcha;

        $messages = [
            'g-recaptcha-response.required_if' => trans('admin.captcha_error_required'),
            'g-recaptcha-response.captcha' => trans('admin.captcha_error'),
        ];

        // get our login input
        $login = $request->input('username_email');
        $urlReturn = $request->input('return');
        $isModal = $request->input('isModal');

        // check login field
        $login_type = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // merge our login field into the request with either email or username as key
        $request->merge([$login_type => $login]);

        // let's validate and set our credentials
        if ($login_type == 'email') {

            $validator = Validator::make($request->all(), [
                        'username_email' => 'required|email',
                        'password' => 'required',
                        'g-recaptcha-response' => 'required_if:_captcha,==,on|captcha'
                            ], $messages);

            if ($validator->fails()) {
                return response()->json([
                            'success' => false,
                            'errors' => $validator->getMessageBag()->toArray(),
                ]);
            }

            $user = \DB::table('users')->where('email', $login)->first();

            if ($user->session_id != '') {
                
                if ($request->input('status') != '1') {
                    
                     return response()->json([
                            'success' => false,
                            'account_errors' => ['error' => 'Your account is logged in to another device'],
                ]);
                     
                } else if ($request->input('status') == '1') {
                    
                     $last_session = \Session::getHandler()->read($user->session_id);

                    if ($last_session) {
                        if (\Session::getHandler()->destroy($user->session_id)) {
                            
                        }
                    }
                }
                   
                }
                
            $credentials = $request->only('email', 'password');
        } else {

            $validator = Validator::make($request->all(), [
                        'username_email' => 'required',
                        'password' => 'required',
                        'g-recaptcha-response' => 'required_if:_captcha,==,on|captcha'
                            ], $messages);

            if ($validator->fails()) {
                return response()->json([
                            'success' => false,
                            'errors' => $validator->getMessageBag()->toArray(),
                ]);
            }

             $user = \DB::table('users')->where('username', $login)->first();
             
             if ($user->session_id != '') {
                
                if ($request->input('status') != '1') {
                    
                     return response()->json([
                            'success' => false,
                            'account_errors' => ['error' => 'Your account is logged in to another device'],
                ]);
                     
                } else if ($request->input('status') == '1') {
                    
                     $last_session = \Session::getHandler()->read($user->session_id);

                    if ($last_session) {
                        if (\Session::getHandler()->destroy($user->session_id)) {
                            
                        }
                    }
                }
                   
                }
                
            $credentials = $request->only('username', 'password');
        }

//    if ($this->auth->attempt($credentials, $request->has('remember'))) {

        if (auth()->guard('web')->attempt($credentials, $request->has('remember'))) {


            if ($this->auth->User()->status == 'active') {

                $new_sessid = \Session::getId(); //get new session_id after user sign in

                if ($user->session_id != '') {
                    
//                    $last_session = \Session::getHandler()->read($user->session_id);
//
//                    if ($last_session) {
//                        if (\Session::getHandler()->destroy($user->session_id)) {
//                            
//                        }
//                    }
                }

                \DB::table('users')->where('id', $user->id)->update(['session_id' => $new_sessid]);

                $user = auth()->guard('web')->user();

                if (isset($urlReturn) && url()->isValidUrl($urlReturn)) {
                    return response()->json([
                                'success' => true,
                                'isLoginRegister' => true,
                                'isModal' => $isModal ? true : false,
                                'url_return' => $urlReturn
                    ]);
                } else {
                    return response()->json([
                                'success' => true,
                                'isLoginRegister' => true,
                                'isModal' => $isModal ? true : false,
                                'url_return' => url('/')
                    ]);
                }
            } else if ($this->auth->User()->status == 'suspended') {

                $this->auth->logout();

                return response()->json([
                            'success' => false,
                            'errors' => ['error' => trans('validation.user_suspended')],
                ]);
            } else if ($this->auth->User()->status == 'pending') {

                $this->auth->logout();

                return response()->json([
                            'success' => false,
                            'errors' => ['error' => trans('validation.account_not_confirmed')],
                ]);
            }
        }

//    if ($this->auth->attempt($credentials, $request->has('remember'))) {
//
//  			if ($this->auth->User()->status == 'active') {
//
//              if (isset($urlReturn) && url()->isValidUrl($urlReturn)) {
//                return response()->json([
//                    'success' => true,
//                    'isLoginRegister' => true,
//                    'isModal' => $isModal ? true : false,
//                    'url_return' => $urlReturn
//                ]);
//                } else {
//                  return response()->json([
//                      'success' => true,
//                      'isLoginRegister' => true,
//                      'isModal' => $isModal ? true : false,
//                      'url_return' => url('/')
//                  ]);
//                }
//
//          } else if ($this->auth->User()->status == 'suspended') {
//
//  			$this->auth->logout();
//
//        return response()->json([
//            'success' => false,
//            'errors' => ['error' => trans('validation.user_suspended')],
//        ]);
//
//      } else if ($this->auth->User()->status == 'pending') {
//
//  			$this->auth->logout();
//
//        return response()->json([
//            'success' => false,
//            'errors' => ['error' => trans('validation.account_not_confirmed')],
//        ]);
//      }
//    }

        return response()->json([
                    'success' => false,
                    'errors' => ['error' => trans('auth.failed')]
        ]);
    }

    public function logout(Request $request) {
        
        
       $logout_user = \DB::table('users')->where('id', $this->auth->User()->id)->first();
       if($logout_user) {
        \DB::table('users')->where('id', $logout_user->id)->update(['session_id' => ""]);
       }
        \Session::flush();
       
        return redirect()->to('/login');
    }

}
