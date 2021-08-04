<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Subscriptions;
use App\Models\AdminSettings;
use App\Models\Withdrawals;
use App\Models\Updates;
use App\Models\Like;
use App\Models\Notifications;
use App\Models\Reports;
use App\Models\PaymentGateways;
use App\Models\Transactions;
use App\Models\VerificationRequests;
use App\Models\Deposits;
use App\Models\bankDetails;
use App\Classes\RtcTokenBuilder;
use App\Classes\RtmTokenBuilder;
use App\Classes\AccessToken;
use App\Helper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Yabacon\Paystack;
use Image;
use DB;
use DateTime;
use DateTimeZone;

class UserController extends Controller {

    use Traits\UserDelete;
    use Traits\Functions;

    public function __construct(Request $request, AdminSettings $settings) {
        $this->request = $request;
        $this->settings = $settings::first();
    }

    /**
     * Display dashboard user
     *
     * @return Response
     */
    public function dashboard() {
        $earningNetUser = auth()->user()->myPaymentsReceived()->sum('earning_net_user');

        $subscriptionsActive = auth()->user()
                ->mySubscriptions()
                ->where('stripe_id', '=', '')
                ->whereDate('ends_at', '>=', Carbon::today())
                ->orWhere('stripe_status', 'active')
                ->where('stripe_id', '<>', '')
                ->whereStripePlan(auth()->user()->plan)
                ->orWhere('stripe_id', '=', '')
                ->where('stripe_plan', auth()->user()->plan)
                ->where('free', '=', 'yes')
                ->count();

        $month = date('m');
        $year = date('Y');
        $daysMonth = Helper::daysInMonth($month, $year);
        $dateFormat = "$year-$month-";

        $monthFormat = trans("months.$month");
        $currencySymbol = $this->settings->currency_symbol;

        for ($i = 1; $i <= $daysMonth; ++$i) {

            $date = date('Y-m-d', strtotime($dateFormat . $i));
            $_subscriptions = auth()->user()->myPaymentsReceived()->whereDate('created_at', '=', $date)->sum('earning_net_user');

            $monthsData[] = "'$monthFormat $i'";

            $_earningNetUser = $_subscriptions;

            $earningNetUserSum[] = $_earningNetUser;
        }

        // Today
        $stat_revenue_today = Transactions::where('created_at', '>=', date('Y-m-d H:i:s', strtotime('today')))
                ->whereApproved('1')
                ->whereSubscribed(auth()->user()->id)
                ->sum('earning_net_user');

        // Yesterday
        $stat_revenue_yesterday = Transactions::where('created_at', '>=', Carbon::yesterday())
                ->whereApproved('1')
                ->whereSubscribed(auth()->user()->id)
                ->sum('earning_net_user');

        // Week
        $stat_revenue_week = Transactions::whereBetween('created_at', [
                    Carbon::parse()->startOfWeek(),
                    Carbon::parse()->endOfWeek(),
                ])->whereApproved('1')
                ->whereSubscribed(auth()->user()->id)
                ->sum('earning_net_user');

        // Last Week
        $stat_revenue_last_week = Transactions::whereBetween('created_at', [
                    Carbon::now()->startOfWeek()->subWeek(),
                    Carbon::now()->subWeek()->endOfWeek(),
                ])->whereApproved('1')
                ->whereSubscribed(auth()->user()->id)
                ->sum('earning_net_user');

        // Month
        $stat_revenue_month = Transactions::whereBetween('created_at', [
                    Carbon::parse()->startOfMonth(),
                    Carbon::parse()->endOfMonth(),
                ])->whereApproved('1')
                ->whereSubscribed(auth()->user()->id)
                ->sum('earning_net_user');

        // Last Month
        $stat_revenue_last_month = Transactions::whereBetween('created_at', [
                    Carbon::now()->startOfMonth()->subMonth(),
                    Carbon::now()->subMonth()->endOfMonth(),
                ])->whereApproved('1')
                ->whereSubscribed(auth()->user()->id)
                ->sum('earning_net_user');

        $label = implode(',', $monthsData);
        $data = implode(',', $earningNetUserSum);

        return view('users.dashboard', [
            'earningNetUser' => $earningNetUser,
            'subscriptionsActive' => $subscriptionsActive,
            'label' => $label,
            'data' => $data,
            'month' => $monthFormat,
            'stat_revenue_today' => $stat_revenue_today,
            'stat_revenue_yesterday' => $stat_revenue_yesterday,
            'stat_revenue_week' => $stat_revenue_week,
            'stat_revenue_last_week' => $stat_revenue_last_week,
            'stat_revenue_month' => $stat_revenue_month,
            'stat_revenue_last_month' => $stat_revenue_last_month
        ]);
    }

    public function profile($slug, $media = null) {

        $media = request('media');
        $mediaTitle = null;
        $sortPostByTypeMedia = null;

        if (isset($media)) {
            $mediaTitle = trans('general.' . $media . '') . ' - ';
            $sortPostByTypeMedia = '&media=' . $media;
            $media = '/' . $media;
        }

        // All Payments
        $allPayment = PaymentGateways::where('enabled', '1')->whereSubscription('yes')->get();

        // Stripe Key
        $_stripe = PaymentGateways::whereName('Stripe')->where('enabled', '1')->select('key')->first();

        $user = User::where('username', '=', $slug)->where('status', 'active')->firstOrFail();

        // Hidden Profile Admin
        if (auth()->check() && $this->settings->hide_admin_profile == 'on' && $user->id == 1 && auth()->user()->id != 1) {
            abort(404);
        } elseif (auth()->guest() && $this->settings->hide_admin_profile == 'on' && $user->id == 1) {
            abort(404);
        }

        if (isset($media)) {
            $query = $user->updates();
        } else {
            $query = $user->updates()->whereFixedPost('0');
        }

        //=== Photos
        $query->when(request('media') == 'photos', function ($q) {
            $q->where('image', '<>', '');
        });

        //=== Videos
        $query->when(request('media') == 'videos', function ($q) use ($user) {
            $q->where('video', '<>', '')->orWhere('video_embed', '<>', '')->whereUserId($user->id);
        });

        //=== Audio
        $query->when(request('media') == 'audio', function ($q) {
            $q->where('music', '<>', '');
        });

        //=== Files
        $query->when(request('media') == 'files', function ($q) {
            $q->where('file', '<>', '');
        });

        $updates = $query->orderBy('id', 'desc')->paginate($this->settings->number_posts_show);

        // Check if subscription exists
        if (auth()->check()) {
            $checkSubscription = auth()->user()->checkSubscription($user);

            if ($checkSubscription) {
                // Get Payment gateway the subscription
                $paymentGatewaySubscription = Transactions::whereSubscriptionsId($checkSubscription->id)->first();
            }

            // Check Payment Incomplete
            $paymentIncomplete = auth()->user()
                    ->userSubscriptions()
                    ->where('stripe_plan', $user->plan)
                    ->whereStripeStatus('incomplete')
                    ->first();
        }

        if ($user->status == 'suspended') {
            abort(404);
        }

        //<<<-- * Redirect the user real name * -->>>
        $uri = request()->path();
        $uriCanonical = $user->username . $media;

        if ($uri != $uriCanonical) {
            return redirect($uriCanonical);
        }

        // Find post pinned
        $findPostPinned = $user->updates()->whereFixedPost('1')->paginate($this->settings->number_posts_show);

        // Count all likes
        $likeCount = $user->likesCount();

        // Subscription sActive
        $subscriptionsActive = $user->mySubscriptions()
                ->where('stripe_id', '=', '')
                ->whereDate('ends_at', '>=', Carbon::today())
                ->orWhere('stripe_status', 'active')
                ->where('stripe_id', '<>', '')
                ->whereStripePlan($user->plan)
                ->orWhere('stripe_id', '=', '')
                ->where('stripe_plan', $user->plan)
                ->where('free', '=', 'yes')
                ->count();

        $subscribers = $user->mySubscriptions()
                ->where('stripe_id', '=', '')
                ->whereDate('ends_at', '>=', Carbon::today())
                ->orWhere('stripe_status', 'active')
                ->where('stripe_id', '<>', '')
                ->whereStripePlan($user->plan)
                ->orWhere('stripe_id', '=', '')
                ->where('stripe_plan', $user->plan)
                ->where('free', '=', 'yes')
                ->orderBy('id', 'desc')
                ->get();

        return view('users.profile', [
            'user' => $user,
            'updates' => $updates,
            'findPostPinned' => $findPostPinned,
            '_stripe' => $_stripe,
            'checkSubscription' => $checkSubscription ?? null,
            'media' => $media,
            'mediaTitle' => $mediaTitle,
            'sortPostByTypeMedia' => $sortPostByTypeMedia,
            'allPayment' => $allPayment,
            'paymentIncomplete' => $paymentIncomplete ?? null,
            'likeCount' => $likeCount,
            'paymentGatewaySubscription' => $paymentGatewaySubscription->payment_gateway ?? null,
            'subscriptionsActive' => $subscriptionsActive,
            'subscribers' => $subscribers,
        ]);
    }

//<--- End Method

    public function postDetail($slug, $id) {

        $user = User::where('username', '=', $slug)->where('status', 'active')->firstOrFail();
        $updates = $user->updates()->whereId($id)->orderBy('id', 'desc')->paginate(1);

        $users = $this->userExplore();

        if ($user->status == 'suspended' || $updates->count() == 0) {
            abort(404);
        }

        //<<<-- * Redirect the user real name * -->>>
        $uri = request()->path();
        $uriCanonical = $user->username . '/post/' . $updates[0]->id;

        if ($uri != $uriCanonical) {
            return redirect($uriCanonical);
        }

        return view('users.post-detail', ['user' => $user,
            'updates' => $updates,
            'inPostDetail' => true,
            'users' => $users
        ]);
    }

//<--- End Method

    public function settings() {
        return view('users.settings');
    }

    public function updateSettings() {
        $input = $this->request->all();
        $id = auth()->user()->id;

        $validator = Validator::make($input, [
                    'profession' => 'required|min:6|max:100|string',
                    'countries_id' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                            ->withErrors($validator)
                            ->withInput();
        }

        $user = User::find($id);
        $user->profession = trim(strip_tags($input['profession']));
        $user->countries_id = trim($input['countries_id']);
        $user->email_new_subscriber = $input['email_new_subscriber'] ?? 'no';
        $user->save();

        \Session::flash('status', trans('auth.success_update'));

        return redirect('settings');
    }

    public function notifications() {
        // Notifications
        $notifications = DB::table('notifications')
                ->select(DB::raw('
        notifications.id id_noty,
        notifications.type,
        notifications.created_at,
        users.id userId,
        users.username,
        users.hide_name,
        users.name,
        users.avatar,
        updates.id,
        updates.description,
        U2.username usernameAuthor,
        messages.message
        '))
                ->leftjoin('users', 'users.id', '=', DB::raw('notifications.author'))
                ->leftjoin('updates', 'updates.id', '=', DB::raw('notifications.target'))
                ->leftjoin('messages', 'messages.id', '=', DB::raw('notifications.target'))
                ->leftjoin('users AS U2', 'U2.id', '=', DB::raw('updates.user_id'))
                ->leftjoin('comments', 'comments.updates_id', '=', DB::raw('notifications.target
        AND comments.user_id = users.id
        AND comments.updates_id = updates.id'))
                ->where('notifications.destination', '=', auth()->user()->id)
                ->where('users.status', '=', 'active')
                ->groupBy('notifications.id')
                ->orderBy('notifications.id', 'DESC')
                ->paginate(20);

        // Mark seen Notification
        $getNotifications = Notifications::where('destination', auth()->user()->id)->where('status', '0');
        $getNotifications->count() > 0 ? $getNotifications->update(['status' => '1']) : null;

        return view('users.notifications', ['notifications' => $notifications]);
    }

    public function settingsNotifications() {
        $user = User::find(auth()->user()->id);
        $user->notify_new_subscriber = $this->request->notify_new_subscriber ?? 'no';
        $user->notify_liked_post = $this->request->notify_liked_post ?? 'no';
        $user->notify_commented_post = $this->request->notify_commented_post ?? 'no';
        $user->notify_new_tip = $this->request->notify_new_tip ?? 'no';
        $user->email_new_subscriber = $this->request->email_new_subscriber ?? 'no';
        $user->save();

        return response()->json([
                    'success' => true,
        ]);
    }

    public function deleteNotifications() {
        auth()->user()->notifications()->delete();
        return back();
    }

    public function password() {
        return view('users.password');
    }

//<--- End Method

    public function updatePassword(Request $request) {

        $input = $request->all();
        $id = auth()->user()->id;
        $passwordRequired = auth()->user()->password != '' ? 'required|' : null;

        $validator = Validator::make($input, [
                    'old_password' => $passwordRequired . 'min:6',
                    'new_password' => 'required|min:6',
                    'confirm_new_password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                            ->withErrors($validator)
                            ->withInput();
        }

        if (auth()->user()->password != '' && !\Hash::check($input['old_password'], auth()->user()->password)) {
            return redirect('settings/password')->with(array('incorrect_pass' => trans('general.password_incorrect')));
        }
        
        if ($input["new_password"] != $input["confirm_new_password"]) {
            return redirect('settings/password')->with(array('incorrect_pass' => trans('general.confirm_password_error')));
        }
        

        $user = User::find($id);
        $user->password = \Hash::make($input["new_password"]);
        $user->save();

        \Session::flash('status', trans('auth.success_update_password'));

        return redirect('settings/password');
    }

//<--- End Method

    public function mySubscribers() {
        $subscriptions = auth()->user()->mySubscriptions()->orderBy('id', 'desc')->paginate(20);

        return view('users.my_subscribers')->withSubscriptions($subscriptions);
    }

    public function mySubscriptions() {
        $subscriptions = auth()->user()->userSubscriptions()->orderBy('id', 'desc')->paginate(20);
        return view('users.my_subscriptions')->withSubscriptions($subscriptions);
    }

    public function myPayments() {
        if (request()->is('my/payments')) {
            $transactions = auth()->user()->myPayments()->orderBy('id', 'desc')->paginate(20);
        } elseif (request()->is('my/payments/received')) {
            $transactions = auth()->user()->myPaymentsReceived()->orderBy('id', 'desc')->paginate(20);
        } else {
            abort(404);
        }

        return view('users.my_payments')->withTransactions($transactions);
    }

    public function payoutMethod() {
        return view('users.payout_method');
    }
    
    
    public function getSwift(Request $request) {
        
        $bic = DB::table('bankDetails')->where('id', $request->bank_id)->first();
        
        $branch = DB::table('bankBranches')->where('bank_id', $request->bank_id)->get();
        
         return response()->json(['message' => 'success', 'bic' => $bic->bic, 'branch' => $branch]);
    }

    public function payoutMethodConfigure() {

        if ($this->request->type != 'paypal' && $this->request->type != 'bank' && $this->request->type != 'FCIB' && $this->request->type != 'WisePayment' && $this->request->type != 'BankWire') {
            return redirect('settings/payout/method');
            exit;
        }

        // Validate Email Paypal
        if ($this->request->type == 'paypal') {
            $rules = array(
                'email_paypal' => 'required|email|confirmed',
            );

            $this->validate($this->request, $rules);

            $user = User::find(auth()->user()->id);
            $user->paypal_account = $this->request->email_paypal;
            
            if($this->request->make_default_paypal == 'yes'){
            $user->payment_gateway = 'PayPal'; 
            }
            
            $user->save();

            \Session::flash('status', trans('admin.success_update'));
            return redirect('settings/payout/method')->withInput();
        }// Validate Email Paypal
        elseif ($this->request->type == 'bank') {

            $rules = array(
             //   'bank_details' => 'required|min:20',
                'account_holder_name' => 'required',
            );

            $this->validate($this->request, $rules);

            $user = User::find(auth()->user()->id);
            $user->account_holder_name = $this->request->account_holder_name;
            $user->bank_name = $this->request->bank_name;
            $user->bic = $this->request->bic;
            $user->recipient_type = $this->request->recipient_type;
            $user->account_type = $this->request->account_type;
            $user->branch = $this->request->branch;
            $user->account_number = $this->request->account_number;
            $user->account = $this->request->account;
            $user->currency = $this->request->currency;
            $user->comment = $this->request->comment;
          //  $user->bank = strip_tags($this->request->bank_details);
            if($this->request->make_default_bank == 'yes'){
            $user->payment_gateway = 'Bank'; 
            }
            
            $user->save();

            \Session::flash('status', trans('admin.success_update'));
            return redirect('settings/payout/method');
        }
        
        elseif ($this->request->type == 'FCIB') {

            $rules = array(
                'email_fcib' => 'required|email',
                'mobile_fcib' => 'required',
            );

            $this->validate($this->request, $rules);

            $user = User::find(auth()->user()->id);
            $user->email_fcib = $this->request->email_fcib;
            $user->mobile_fcib = $this->request->mobile_fcib;
           //  $user->bank = strip_tags($this->request->bank_details);
            
            if($this->request->make_default_fcib == 'yes'){
            $user->payment_gateway = 'FCIB'; 
            }

            $user->save();

            \Session::flash('status', trans('admin.success_update'));
            return redirect('settings/payout/method');
        }
        
        elseif ($this->request->type == 'BankWire') {


             $rules = array(
                'bank_wire_account_holder_name' => 'required',
                'bank_wire_address1' => 'required',
                'bank_wire_city' => 'required',
                'bank_wire_zip' => 'required',
                'bank_wire_countries_id' => 'required',
                'bank_wire_account_number' => 'required',
                'bank_wire_iban' => 'required',
                'bank_wire_bic' => 'required',
                'bank_wire_currency' => 'required',
            );
             
            $this->validate($this->request, $rules);

            $user = User::find(auth()->user()->id);
            $user->bank_wire_account_holder_name = $this->request->bank_wire_account_holder_name;
            $user->bank_wire_address1 = $this->request->bank_wire_address1;
            $user->bank_wire_address2 = $this->request->bank_wire_address2;
            $user->bank_wire_city = $this->request->bank_wire_city;
            $user->bank_wire_zip = $this->request->bank_wire_zip;
            $user->bank_wire_countries_id = $this->request->bank_wire_countries_id;
            $user->bank_wire_account_number = $this->request->bank_wire_account_number;
            $user->bank_wire_iban = $this->request->bank_wire_iban;
            $user->bank_wire_bic = $this->request->bank_wire_bic;
            $user->bank_wire_currency = $this->request->bank_wire_currency;
            $user->bank_wire_comment = $this->request->bank_wire_comment;
            if($this->request->make_default_bank_wire == 'yes'){
            $user->payment_gateway = 'BankWire'; 
            }
            
            $user->save();

            \Session::flash('status', trans('admin.success_update'));
            return redirect('settings/payout/method');
        }
        
        elseif ($this->request->type == 'WisePayment') {


             $rules = array(
                'wise_account_holder_name' => 'required',
                'wise_address1' => 'required',
                'wise_city' => 'required',
                'wise_zip' => 'required',
                'wise_countries_id' => 'required',
                'wise_account_number' => 'required',
                'wise_iban' => 'required',
                'wise_bic' => 'required',
                'wise_ammount' => 'required',
                'wise_currency' => 'required',
            );
             
            $this->validate($this->request, $rules);

            $user = User::find(auth()->user()->id);
            $user->wise_account_holder_name = $this->request->wise_account_holder_name;
            $user->wise_address1 = $this->request->wise_address1;
            $user->wise_address2 = $this->request->wise_address2;
            $user->wise_city = $this->request->wise_city;
            $user->wise_zip = $this->request->wise_zip;
            $user->wise_countries_id = $this->request->wise_countries_id;
            $user->wise_account_number = $this->request->wise_account_number;
            $user->wise_iban = $this->request->wise_iban;
            $user->wise_bic = $this->request->wise_bic;
            $user->wise_ammount = $this->request->wise_ammount;
            $user->wise_currency = $this->request->wise_currency;
            $user->wise_comment = $this->request->wise_comment;
            if($this->request->make_default_wise == 'yes'){
            $user->payment_gateway = 'WisePayment'; 
            }
            
            $user->save();

            \Session::flash('status', trans('admin.success_update'));
            return redirect('settings/payout/method');
        }
        
    }

//<--- End Method

    public function uploadAvatar() {
        $validator = Validator::make($this->request->all(), [
                    'avatar' => 'required|mimes:jpg,gif,png,jpe,jpeg|dimensions:min_width=200,min_height=200|max:' . $this->settings->file_size_allowed . '',
        ]);

        if ($validator->fails()) {
            return response()->json([
                        'success' => false,
                        'errors' => $validator->getMessageBag()->toArray(),
            ]);
        }

        // PATHS
        $path = config('path.avatar');

        //<--- HASFILE PHOTO
        if ($this->request->hasFile('avatar')) {

            $photo = $this->request->file('avatar');
            $extension = $this->request->file('avatar')->getClientOriginalExtension();
            $avatar = strtolower(auth()->user()->username . '-' . auth()->user()->id . time() . str_random(10) . '.' . $extension);

            set_time_limit(0);
            ini_set('memory_limit', '512M');

            $imgAvatar = Image::make($photo)->orientate()->fit(200, 200, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })->encode($extension);

            // Copy folder
            Storage::put($path . $avatar, $imgAvatar, 'public');

            //<<<-- Delete old image -->>>/
            if (auth()->user()->avatar != $this->settings->avatar) {
                Storage::delete(config('path.avatar') . auth()->user()->avatar);
            }

            // Update Database
            auth()->user()->update(['avatar' => $avatar]);

            return response()->json([
                        'success' => true,
                        'avatar' => Helper::getFile($path . $avatar),
            ]);
        }//<--- HASFILE PHOTO
    }

//<--- End Method Avatar

    public function settingsPage() {
        $genders = explode(',', $this->settings->genders);
        return view('users.edit_my_page', ['genders' => $genders]);
    }

    public function updateSettingsPage() {

        $input = $this->request->all();
        $id = auth()->user()->id;
        $input['is_admin'] = $id;
        $input['is_creator'] = auth()->user()->verified_id == 'yes' ? 0 : 1;

        $messages = array(
            "letters" => trans('validation.letters'),
            "email.required_if" => trans('validation.required'),
            "birthdate.before" => trans('general.error_adult'),
            "story.required_if" => trans('validation.required'),
        );

        Validator::extend('ascii_only', function ($attribute, $value, $parameters) {
            return !preg_match('/[^x00-x7F\-]/i', $value);
        });

        // Validate if have one letter
        Validator::extend('letters', function ($attribute, $value, $parameters) {
            return preg_match('/[a-zA-Z0-9]/', $value);
        });

        $validator = Validator::make($input, [
                    'full_name' => 'required|string|max:100',
                    'username' => 'required|min:3|max:25|ascii_only|alpha_dash|letters|unique:pages,slug|unique:reserved,name|unique:users,username,' . $id,
                    'email' => 'required_if:is_admin,==,1|unique:users,email,' . $id,
                    'website' => 'url',
                    'facebook' => 'url',
                    'twitter' => 'url',
                    'instagram' => 'url',
                    'youtube' => 'url',
                    'pinterest' => 'url',
                    'github' => 'url',
                    'story' => 'required_if:is_creator,==,0|max:' . $this->settings->story_length . '',
                    'countries_id' => 'required',
                    'city' => 'max:100',
                    'address' => 'max:100',
                    'zip' => 'max:20',
                    'birthdate' => 'required|date|before:' . Carbon::now()->subYears(18),
                        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                        'success' => false,
                        'errors' => $validator->getMessageBag()->toArray(),
            ]);
        } //<-- Validator

        $user = User::find($id);
        $user->name = strip_tags($this->request->full_name);
        $user->username = trim($this->request->username);
        $user->email = $this->request->email ? trim($this->request->email) : auth()->user()->email;
        $user->website = trim($this->request->website) ?? '';
        $user->categories_id = $this->request->categories_id ?? '';
        $user->profession = $this->request->profession;
        $user->countries_id = $this->request->countries_id;
        $user->city = $this->request->city;
        $user->address = $this->request->address;
        $user->zip = $this->request->zip;
        $user->company = $this->request->company;
        $user->story = trim(Helper::checkTextDb($this->request->story));
        $user->facebook = trim($this->request->facebook) ?? '';
        $user->twitter = trim($this->request->twitter) ?? '';
        $user->instagram = trim($this->request->instagram) ?? '';
        $user->youtube = trim($this->request->youtube) ?? '';
        $user->pinterest = trim($this->request->pinterest) ?? '';
        $user->github = trim($this->request->github) ?? '';
        $user->plan = 'user_' . auth()->user()->id;
        $user->gender = $this->request->gender;
        $user->birthdate = $this->request->birthdate;
        $user->language = $this->request->language;
        $user->hide_name = $this->request->hide_name ?? 'no';
        $user->save();

        return response()->json([
                    'success' => true,
                    'url' => url(trim($this->request->username)),
                    'locale' => $this->request->language != '' && config('app.locale') != $this->request->language ? true : false,
        ]);
    }

//<--- End Method

    public function saveSubscription() {

        $input = $this->request->all();

        if (auth()->user()->verified_id == 'no' || auth()->user()->verified_id == 'reject') {
            return redirect()->back()
                            ->withErrors([
                                'errors' => trans('general.error'),
            ]);
        }

        $id = auth()->user()->id;
        $input['_verified_id'] = auth()->user()->verified_id;

        if ($this->settings->currency_position == 'right') {
            $currencyPosition = 2;
        } else {
            $currencyPosition = null;
        }

        if ($this->request->free_subscription) {
            $priceRequired = null;
        } else {
            $priceRequired = 'required_if:_verified_id,==,yes|';
        }

        $messages = array(
            'price.min' => trans('users.price_minimum_subscription' . $currencyPosition, ['symbol' => $this->settings->currency_symbol, 'code' => $this->settings->currency_code]),
            'price.max' => trans('users.price_maximum_subscription' . $currencyPosition, ['symbol' => $this->settings->currency_symbol, 'code' => $this->settings->currency_code]),
            "price.required_if" => trans('general.subscription_price_required'),
        );

        if (auth()->user()->verified_id == 'no' || auth()->user()->verified_id == 'reject') {
            $this->settings->min_subscription_amount = 0;
        } else {
            $this->settings->min_subscription_amount = $this->settings->min_subscription_amount;
        }

        $validator = Validator::make($input, [
                    'price' => $priceRequired . 'numeric|min:' . $this->settings->min_subscription_amount . '|max:' . $this->settings->max_subscription_amount . '',
                        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()
                            ->withErrors($validator)
                            ->withInput();
        }

        $user = User::find($id);
        $user->price = $this->request->price ?? auth()->user()->price;
        $user->free_subscription = $this->request->free_subscription ?? 'no';
        $user->plan = 'user_' . auth()->user()->id;
        $user->save();

        // Create Plan Stripe
        if (auth()->user()->verified_id == 'yes' && !$this->request->free_subscription) {
            $this->createPlanStripe();
        }

        // Create Plan Paystack
        if (auth()->user()->verified_id == 'yes' && !$this->request->free_subscription) {
            $this->createPlanPaystack();
        }

        \Session::flash('status', trans('admin.success_update'));
        return redirect('settings/subscription');
    }

//<--- End Method

    protected function createPlanStripe() {
        $payment = PaymentGateways::whereName('Stripe')->whereEnabled(1)->first();
        $plan = 'user_' . auth()->user()->id;

        if ($payment) {
            if ($this->request->price != auth()->user()->price) {
                $stripe = new \Stripe\StripeClient($payment->key_secret);

                try {
                    $planCurrent = $stripe->plans->retrieve($plan, []);

                    // Delete old plan
                    $stripe->plans->delete($plan, []);

                    // Delete Product
                    $stripe->products->delete($planCurrent->product, []);
                } catch (\Exception $exception) {
                    // not exists
                }

                // Create Plan
                $plan = $stripe->plans->create([
                    'currency' => $this->settings->currency_code,
                    'interval' => 'month',
                    "product" => [
                        "name" => trans('general.subscription_for') . ' @' . auth()->user()->username,
                    ],
                    'nickname' => $plan,
                    'id' => $plan,
                    'amount' => $this->settings->currency_code == 'JPY' ? $this->request->price : $this->request->price * 100,
                ]);
            }
        }
    }

    protected function createPlanPaystack() {
        $payment = PaymentGateways::whereName('Paystack')->whereEnabled(1)->first();

        if ($payment) {

            // initiate the Library's Paystack Object
            $paystack = new Paystack($payment->key_secret);

            //========== Create Plan if no exists
            if (!auth()->user()->paystack_plan) {

                $userPlan = $paystack->plan->create([
                    'name' => trans('general.subscription_for') . ' @' . auth()->user()->username,
                    'amount' => auth()->user()->price * 100,
                    'interval' => 'monthly',
                    'currency' => $this->settings->currency_code
                ]);

                $planCode = $userPlan->data->plan_code;

                // Insert Plan Code to User
                User::whereId(auth()->user()->id)->update([
                    'paystack_plan' => $planCode
                ]);
            } else {
                if ($this->request->price != auth()->user()->price) {

                    $userPlan = $paystack->plan->update([
                        'name' => trans('general.subscription_for') . ' @' . auth()->user()->username,
                        'amount' => $this->request->price * 100,
                            ], ['id' => auth()->user()->paystack_plan]);
                }
            }
        } // payment
    }

// end method

    public function uploadCover(Request $request) {
        $settings = AdminSettings::first();

        $validator = Validator::make($this->request->all(), [
                    'image' => 'required|mimes:jpg,gif,png,jpe,jpeg|dimensions:min_width=800,min_height=400|max:' . $settings->file_size_allowed . '',
        ]);

        if ($validator->fails()) {
            return response()->json([
                        'success' => false,
                        'errors' => $validator->getMessageBag()->toArray(),
            ]);
        }

        // PATHS
        $path = config('path.cover');

        //<--- HASFILE PHOTO
        if ($this->request->hasFile('image')) {

            $photo = $this->request->file('image');
            $widthHeight = getimagesize($photo);
            $extension = $photo->getClientOriginalExtension();
            $cover = strtolower(auth()->user()->username . '-' . auth()->user()->id . time() . str_random(10) . '.' . $extension);

            set_time_limit(0);
            ini_set('memory_limit', '512M');

            //=============== Image Large =================//
            $width = $widthHeight[0];
            $height = $widthHeight[1];
            $max_width = $width < $height ? 800 : 1500;

            if ($width > $max_width) {
                $coverScale = $max_width / $width;
            } else {
                $coverScale = 1;
            }

            $scale = $coverScale;
            $widthCover = ceil($width * $scale);

            $imgCover = Image::make($photo)->orientate()->resize($widthCover, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })->encode($extension);

            // Copy folder
            Storage::put($path . $cover, $imgCover, 'public');

            //<<<-- Delete old image -->>>/
            Storage::delete(config('path.cover') . auth()->user()->cover);

            // Update Database
            auth()->user()->update(['cover' => $cover]);

            return response()->json([
                        'success' => true,
                        'cover' => Helper::getFile($path . $cover),
            ]);
        }//<--- HASFILE PHOTO
    }

//<--- End Method Cover

    public function withdrawals() {
        $withdrawals = auth()->user()->withdrawals()->orderBy('id', 'desc')->paginate(20);

        return view('users.withdrawals')->withWithdrawals($withdrawals);
    }

    public function makeWithdrawals() {
        if (auth()->user()->balance >= $this->settings->amount_min_withdrawal && auth()->user()->payment_gateway != '' && Withdrawals::where('user_id', auth()->user()->id
                        )
                        ->where('status', 'pending')
                        ->count() == 0) {

            if (auth()->user()->payment_gateway == 'PayPal') {
                $_account = auth()->user()->paypal_account;
            } else {
                $_account = auth()->user()->bank;
            }

            $sql = new Withdrawals;
            $sql->user_id = auth()->user()->id;
            $sql->amount = auth()->user()->balance;
            $sql->gateway = auth()->user()->payment_gateway;
            $sql->account = $_account;
            $sql->save();

            // Remove Balance the User
            $userBalance = User::find(auth()->user()->id);
            $userBalance->balance = 0;
            $userBalance->save();
        }

        return redirect('settings/withdrawals');
    }

// End Method makeWithdrawals

    public function deleteWithdrawal() {
        $withdrawal = auth()->user()->withdrawals()
                ->whereId($this->request->id)
                ->whereStatus('pending')
                ->firstOrFail();

        // Add Balance the User again
        User::find(auth()->user()->id)->increment('balance', $withdrawal->amount);

        $withdrawal->delete();

        return redirect('settings/withdrawals');
    }

//<--- End Method

    public function deleteImageCover() {
        $path = 'public/cover/';
        $id = auth()->user()->id;

        // Image Cover
        $image = $path . auth()->user()->cover;

        if (\File::exists($image)) {
            \File::delete($image);
        }

        $user = User::find($id);
        $user->cover = '';
        $user->save();

        return response()->json([
                    'success' => true,
        ]);
    }

// End Method

    public function reportCreator(Request $request) {
        $data = Reports::firstOrNew(['user_id' => auth()->user()->id, 'report_id' => $request->id]);

        $validator = Validator::make($this->request->all(), [
                    'reason' => 'required|in:spoofing,copyright,privacy_issue,violent_sexual,spam,fraud,under_age',
        ]);

        if ($validator->fails()) {
            return response()->json([
                        'success' => false,
                        'text' => __('general.error'),
            ]);
        }

        if ($data->exists) {
            return response()->json([
                        'success' => false,
                        'text' => __('general.already_sent_report'),
            ]);
        } else {

            $data->type = 'user';
            $data->reason = $request->reason;
            $data->save();

            return response()->json([
                        'success' => true,
                        'text' => __('general.reported_success'),
            ]);
        }
    }

//<--- End Method

    public function like(Request $request) {

        $like = Like::firstOrNew(['user_id' => auth()->user()->id, 'updates_id' => $request->id]);

        $user = Updates::find($request->id);

        if ($like->exists) {

            $notifications = Notifications::where('destination', $user->user_id)
                    ->where('author', auth()->user()->id)
                    ->where('target', $request->id)
                    ->where('type', '2')
                    ->first();

            // IF ACTIVE DELETE FOLLOW
            if ($like->status == '1') {
                $like->status = '0';
                $like->update();

                // DELETE NOTIFICATION
                if (isset($notifications)) {
                    $notifications->status = '1';
                    $notifications->update();
                }

                // ELSE ACTIVE AGAIN
            } else {
                $like->status = '1';
                $like->update();

                // ACTIVE NOTIFICATION
                if (isset($notifications)) {
                    $notifications->status = '0';
                    $notifications->update();
                }
            }
        } else {

            // INSERT
            $like->save();

            // Send Notification //destination, author, type, target
            if ($user->user_id != auth()->user()->id && $user->user()->notify_liked_post == 'yes') {
                Notifications::send($user->user_id, auth()->user()->id, '2', $request->id);
            }
        }

        $totalLike = Helper::formatNumber($user->likes()->count());

        return $totalLike;
    }

//<---- End Method

    public function ajaxNotifications() {
        if (request()->ajax()) {

            // Logout user suspended
            if (auth()->user()->status == 'suspended') {
                auth()->logout();
            }

            // Notifications
            $notifications_count = auth()->user()->notifications()->where('status', '0')->count();
            // Messages
            $messages_count = auth()->user()->messagesInbox();

            return response()->json([
                        'messages' => $messages_count,
                        'notifications' => $notifications_count
            ]);
        } else {
            return response()->json(['error' => 1]);
        }
    }

//<---- * End Method

    public function verifyAccount() {
        return view('users.verify_account');
    }

//<---- * End Method

    public function verifyAccountSend() {
        $checkRequest = VerificationRequests::whereUserId(auth()->user()->id)->whereStatus('pending')->first();

        if ($checkRequest) {
            return redirect()->back()
                            ->withErrors([
                                'errors' => trans('admin.pending_request_verify'),
            ]);
        } elseif (auth()->user()->verified_id == 'reject') {
            return redirect()->back()
                            ->withErrors([
                                'errors' => trans('admin.rejected_request'),
            ]);
        }

        $input = $this->request->all();
        $input['isUSCitizen'] = auth()->user()->countries_id;

        $messages = [
            "form_w9.required_if" => trans('general.form_w9_required')
        ];

        $validator = Validator::make($input, [
                    'address' => 'required',
                    'city' => 'required',
                    'zip' => 'required',
                    'image' => 'required|mimes:jpg,gif,png,jpe,jpeg,zip|max:' . $this->settings->file_size_allowed_verify_account . '',
                    'form_w9' => 'required_if:isUSCitizen,==,1|mimes:pdf|max:' . $this->settings->file_size_allowed_verify_account . '',
                        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()
                            ->withErrors($validator)
                            ->withInput();
        }

        // PATHS
        $path = config('path.verification');

        if ($this->request->hasFile('image')) {

            $extension = $this->request->file('image')->getClientOriginalExtension();
            $fileImage = strtolower(auth()->user()->id . time() . Str::random(40) . '.' . $extension);

            $this->request->file('image')->storePubliclyAs($path, $fileImage);
        }//<====== End HasFile

        if ($this->request->hasFile('form_w9')) {

            $extension = $this->request->file('form_w9')->getClientOriginalExtension();
            $fileFormW9 = strtolower(auth()->user()->id . time() . Str::random(40) . '.' . $extension);

            $this->request->file('form_w9')->storePubliclyAs($path, $fileFormW9);
        }//<====== End HasFile

        $sql = new VerificationRequests;
        $sql->user_id = auth()->user()->id;
        $sql->address = $input['address'];
        $sql->city = $input['city'];
        $sql->zip = $input['zip'];
        $sql->image = $fileImage;
        $sql->form_w9 = $fileFormW9 ?? '';
        $sql->save();

        \Session::flash('status', trans('general.send_success_verification'));

        return redirect('settings/verify/account');
    }

    public function invoice($id) {
        $data = Transactions::whereId($id)->where('user_id', auth()->user()->id)->whereApproved('1')->firstOrFail();

        if (auth()->user()->address == '' || auth()->user()->city == '' || auth()->user()->zip == '' || auth()->user()->name == ''
        ) {
            return back()->withErrorMessage('Error');
        }

        return view('users.invoice')->withData($data);
    }

    public function formAddUpdatePaymentCard() {
        $payment = PaymentGateways::whereName('Stripe')->whereEnabled(1)->firstOrFail();
        \Stripe\Stripe::setApiKey($payment->key_secret);

        return view('users.add_payment_card', [
            'intent' => auth()->user()->createSetupIntent(),
            'key' => $payment->key
        ]);
    }

// End Method

    public function addUpdatePaymentCard() {
        $payment = PaymentGateways::whereName('Stripe')->whereEnabled(1)->firstOrFail();
        \Stripe\Stripe::setApiKey($payment->key_secret);

        if (!$this->request->payment_method) {
            return response()->json([
                        "success" => false
            ]);
        }

        if (!auth()->user()->hasPaymentMethod()) {
            auth()->user()->createOrGetStripeCustomer();
        }

        try {
            auth()->user()->deletePaymentMethods();
        } catch (\Exception $e) {
            // error
        }

        auth()->user()->updateDefaultPaymentMethod($this->request->payment_method);
        auth()->user()->save();

        return response()->json([
                    "success" => true
        ]);
    }

// End Method

    public function cancelSubscription($id) {
        $checkSubscription = auth()->user()->userSubscriptions()->whereStripeId($id)->firstOrFail();
        $creator = User::wherePlan($checkSubscription->stripe_plan)->first();
        $payment = PaymentGateways::whereName('Stripe')->whereEnabled(1)->firstOrFail();

        $stripe = new \Stripe\StripeClient($payment->key_secret);

        try {
            $response = $stripe->subscriptions->cancel($id, []);
        } catch (\Exception $e) {
            return back()->withErrorMessage($e->getMessage());
        }

        sleep(2);

        $checkSubscription->ends_at = date('Y-m-d H:i:s', $response->current_period_end);
        $checkSubscription->save();

        session()->put('subscription_cancel', trans('general.subscription_cancel'));
        return redirect($creator->username);
    }

// End Method
    // Delete Account
    public function deleteAccount() {
        if (!\Hash::check($this->request->password, auth()->user()->password)) {
            return back()->with(['incorrect_pass' => trans('general.password_incorrect')]);
        }
        if (auth()->user()->id == 1) {
            return redirect('settings/page');
        }

        $this->deleteUser(auth()->user()->id);

        return redirect('/');
    }

    // My Bookmarks
    public function myBookmarks() {
        $bookmarks = auth()->user()->bookmarks()->orderBy('bookmarks.id', 'desc')->paginate($this->settings->number_posts_show);

        $users = $this->userExplore();

        return view('users.bookmarks', ['updates' => $bookmarks, 'users' => $users]);
    }

    // Download File
    public function downloadFile($id) {
        $post = Updates::findOrFail($id);

        if (!auth()->user()->checkSubscription($post->user())) {
            abort(404);
        }

        $pathFile = config('path.files') . $post->file;
        $headers = [
            'Content-Type:' => ' application/x-zip-compressed',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ];

        return Storage::download($pathFile, $post->file_name . ' ' . __('general.by') . ' @' . $post->user()->username . '.zip', $headers);
    }

    public function myCards() {
        $payment = PaymentGateways::whereName('Stripe')->whereEnabled(1)->first();
        $paystackPayment = PaymentGateways::whereName('Paystack')->whereEnabled(1)->first();

        if (!$payment && !$paystackPayment) {
            abort(404);
        }

        if (auth()->user()->stripe_id != '' && auth()->user()->card_brand != '' && isset($payment->key_secret)) {
            $stripe = new \Stripe\StripeClient($payment->key_secret);

            $response = $stripe->paymentMethods->all([
                'customer' => auth()->user()->stripe_id,
                'type' => 'card',
            ]);

            $expiration = $response->data[0]->card->exp_month . '/' . $response->data[0]->card->exp_year;
        }

        $chargeAmountPaystack = ['NGN' => '50.00', 'GHS' => '0.10', 'ZAR' => '1', 'USD' => 0.20];

        if (array_key_exists($this->settings->currency_code, $chargeAmountPaystack)) {
            $chargeAmountPaystack = $chargeAmountPaystack[$this->settings->currency_code];
        } else {
            $chargeAmountPaystack = 0;
        }

        return view('users.my_cards', [
            'key_secret' => $payment->key_secret ?? null,
            'expiration' => $expiration ?? null,
            'paystackPayment' => $paystackPayment,
            'chargeAmountPaystack' => $chargeAmountPaystack
        ]);
    }

    // Privacy Security
    public function privacySecurity() {
        $sessions = \DB::table('sessions')
                ->where('user_id', auth()->user()->id)
                ->orderBy('id', 'DESC')
                ->first();

        return view('users.privacy_security')
                        ->with('sessions', $sessions)
                        ->with('current_session_id', \Session::getId());
        ;
    }

    public function savePrivacySecurity() {
        $user = User::find(auth()->user()->id);
        $user->hide_profile = $this->request->hide_profile ?? 'no';
        $user->hide_last_seen = $this->request->hide_last_seen ?? 'no';
        $user->hide_count_subscribers = $this->request->hide_count_subscribers ?? 'no';
        $user->hide_my_country = $this->request->hide_my_country ?? 'no';
        $user->show_my_birthdate = $this->request->show_my_birthdate ?? 'no';
        $user->save();

        return redirect('privacy/security')->withStatus(trans('admin.success_update'));
    }

    // Logout a session based on session id.
    public function logoutSession($id) {

        \DB::table('sessions')
                ->where('id', $id)->delete();

        return redirect('privacy/security');
    }

    public function deletePaymentCard() {
        $paymentMethod = auth()->user()->defaultPaymentMethod();

        $paymentMethod->delete();

        return redirect('my/cards')->withSuccessRemoved(__('general.successfully_removed'));
    }

    public function invoiceDeposits($id) {
        $data = Deposits::whereId($id)->whereUserId(auth()->user()->id)->whereStatus('active')->firstOrFail();

        if (auth()->user()->address == '' || auth()->user()->city == '' || auth()->user()->zip == '' || auth()->user()->name == ''
        ) {
            return back()->withErrorMessage('Error');
        }

        return view('users.invoice-deposits')->withData($data);
    }

    // My Purchases
    public function myPurchases() {
        $purchases = auth()->user()->payPerView()->orderBy('pay_per_views.id', 'desc')->paginate($this->settings->number_posts_show);

        $users = $this->userExplore();

        return view('users.my-purchases', [
            'updates' => $purchases,
            'users' => $users
        ]);
    }

    // My Purchases Ajax Pagination
    public function ajaxMyPurchases() {
        $skip = $this->request->input('skip');
        $total = $this->request->input('total');

        $data = auth()->user()->payPerView()->orderBy('pay_per_views.id', 'desc')->skip($skip)->take($this->settings->number_posts_show)->get();
        $counterPosts = ($total - $this->settings->number_posts_show - $skip);

        return view('includes.updates', ['updates' => $data,
                    'ajaxRequest' => true,
                    'counterPosts' => $counterPosts,
                    'total' => $total
                ])->render();
    }

//<--- End Method

    public function liveStreaming() {
        
        if(auth()->check() && Auth()->user()->username) {
       
        
        $settings = AdminSettings::first();
        
        $appID = $settings->agora_app_id;
        $appCertificate = $settings->agora_app_certificate;
//        $appID = "cf102d83e7754940bf7f28b71193925e";
//        $appCertificate = "ab728b98162143078e4c644ec379e677";
//        $appID = env('AGORA_APP_ID');
//        $appCertificate = env('AGORA_APP_CERTIFICATE');
        $channelName = Auth()->user()->username;
        $uid = 0;
        $uidStr = "0";
        $role = RtcTokenBuilder::RolePublisher;
        $expireTimeInSeconds = 3600;
        $currentTimestamp = (new DateTime("now", new DateTimeZone('UTC')))->getTimestamp();
        $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;

        $streamtoken = RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpiredTs);

        $user = Auth()->user()->name;
        $roles = RtmTokenBuilder::RoleRtmUser;

        $cohostusr = auth()->user()
                ->mySubscriptions()
                ->where('stripe_id', '=', '')
                ->whereDate('ends_at', '>=', Carbon::today())
                ->orWhere('stripe_status', 'active')
                ->where('stripe_id', '<>', '')
                ->whereStripePlan(auth()->user()->plan)
                ->orWhere('stripe_id', '=', '')
                ->where('stripe_plan', auth()->user()->plan)
                ->where('free', '=', 'yes')
                ->orderBy('id', 'desc')->get();

        $chattoken = RtmTokenBuilder::buildToken($appID, $appCertificate, $user, $roles, $privilegeExpiredTs);

        } else {
             abort(404);
        
        }
        return view('users.liveStreaming', ['appID' => $appID,
            'appCertificate' => $appCertificate,
            'channelName' => $channelName,
            'token' => $streamtoken,
            'username' => $user,
            'chattoken' => $chattoken,
            'cohostusr' => $cohostusr,
        ]);
        
    }

    public function startStreaming(Request $request) {

        $data = $request->all();

        User::whereId(auth()->user()->id)->update([
            'streaming_status' => $data['status'],
            'cnt_usr' => 0
        ]);

        $subscribers = auth()->user()
                ->mySubscriptions()
                ->where('stripe_id', '=', '')
                ->whereDate('ends_at', '>=', Carbon::today())
                ->orWhere('stripe_status', 'active')
                ->where('stripe_id', '<>', '')
                ->whereStripePlan(auth()->user()->plan)
                ->orWhere('stripe_id', '=', '')
                ->where('stripe_plan', auth()->user()->plan)
                ->where('free', '=', 'yes')
                ->orderBy('id', 'desc')->get();

        if ($data['status'] == '1') {


            foreach ($subscribers as $subscriber) {


                Notifications::send($subscriber->user()->id, auth()->user()->id, '8', '0');
            }
        } else if ($data['status'] == '0') {

            foreach ($subscribers as $subscriber) {


                $live_notification = Notifications::where('destination', $subscriber->user()->id)
                        ->where('author', auth()->user()->id)
                        ->where('type', 8)
                        ->delete();
            }

            DB::table('cohosts')->where('streamer_id', auth()->user()->id)->delete();
            
            DB::table('liveChat')->where('channel', auth()->user()->username)->delete();

            $leavecohost = DB::table('cohosts')->where('requestCoHostId', auth()->user()->id)->where('status', 1)->first();

            if ($leavecohost) {


//                DB::table('cohosts')->where('requestCoHostId', auth()->user()->id)->update([
//                    'status' => $data['status']
//                ]);
                DB::table('cohosts')->where('requestCoHostId', auth()->user()->id)->where('status', 1)->delete();
            }
        }



        return response()->json(['message' => 'success']);
    }

    public function leaveStreaming(Request $request) {

        $data = $request->all();

        User::whereId(auth()->user()->id)->update([
            'streaming_status' => $data['status']
        ]);

        $leavecohost = DB::table('cohosts')->where('requestCoHostId', auth()->user()->id)->where('status', 1)->first();

        if ($leavecohost) {


            DB::table('cohosts')->where('requestCoHostId', auth()->user()->id)->update([
                'status' => $data['status']
            ]);
        }



        $subscribers = auth()->user()
                ->mySubscriptions()
                ->where('stripe_id', '=', '')
                ->whereDate('ends_at', '>=', Carbon::today())
                ->orWhere('stripe_status', 'active')
                ->where('stripe_id', '<>', '')
                ->whereStripePlan(auth()->user()->plan)
                ->orWhere('stripe_id', '=', '')
                ->where('stripe_plan', auth()->user()->plan)
                ->where('free', '=', 'yes')
                ->orderBy('id', 'desc')->get();

        foreach ($subscribers as $subscriber) {


            $live_notification = Notifications::where('destination', $subscriber->user()->id)
                    ->where('author', auth()->user()->id)
                    ->where('type', 8)
                    ->delete();
        }


        return response()->json(['message' => 'success']);
    }

    public function leavePage(Request $request) {

        $data = $request->all();

        $usr = User::where('username', $data['streamerName'])->where('streaming_status', 1)->first();

        if ($usr) {
            $result = 'success';
        } else {
            $result = 'leave';
        }
        return response()->json(['result' => $result]);
    }

    public function showLiveStreaming($username = NULL) {
        
        $settings = AdminSettings::first();
       
         $appID = $settings->agora_app_id;
        $appCertificate = $settings->agora_app_certificate;

//        $appID = "cf102d83e7754940bf7f28b71193925e";
//        $appCertificate = "ab728b98162143078e4c644ec379e677";
        $channelName = $username;
        $uid = 0;
        $uidStr = "0";
        $role = RtcTokenBuilder::RoleSubscriber;
        $expireTimeInSeconds = 3600;
        $currentTimestamp = (new DateTime("now", new DateTimeZone('UTC')))->getTimestamp();
        $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;

        $token = RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpiredTs);
        $dynamicToken = $token;

        $user = Auth()->user()->name;
        $roles = RtmTokenBuilder::RoleRtmUser;

        $chattoken = RtmTokenBuilder::buildToken($appID, $appCertificate, $user, $roles, $privilegeExpiredTs);

        $usertip = User::where('username', '=', $username)->where('status', 'active')->firstOrFail();

        $checkjoinreq = DB::table('cohosts')->where('streamer_id', $usertip->id)->where('requestCoHostId', auth()->user()->id)->where('status', 0)->first();

        $notification = DB::table('notifications')->where('destination', auth()->user()->id)->where('type', 8)->first();
       
        $liveChat = DB::table('liveChat')->where('channel', $channelName)->get();

        return view('users.showLiveStreaming', ['appID' => $appID,
            'appCertificate' => $appCertificate,
            'channelName' => $channelName,
            'token' => $dynamicToken,
            'username' => $user,
            'chattoken' => $chattoken,
            'streamerName' => $username,
            'user' => $usertip,
            'checkjoinreq' => $checkjoinreq,
            'notification' => $notification,
            'liveChat' => $liveChat,
        ]);
    }

    public function requestCoHost(Request $request) {

        $data = $request->all();

        //   $check = DB::table('cohosts')->where('streamer_id', auth()->user()->id)->where('status', 0)->first();
        $chkanotherusr = DB::table('cohosts')->where('streamer_id', auth()->user()->id)->where('status', 1)->count();

//        if($check){
//            DB::table('cohosts')
//                    ->where('streamer_id', auth()->user()->id)
//                    ->where('status', 0)
//                    ->update([
//             'requestCoHostId' => $data['requestCoHostId'],
//             'token' => $data['token']
//         ]);
//             
//            $result = 'success';
//            $msg = 'Request Send Successfully';
//            
//        }else 
        if ($chkanotherusr == '2') {

            $result = 'error';
            $msg = 'At a time join only two co-host';
        } else {
            
            $sameusrreq= DB::table('cohosts')->where('streamer_id', auth()->user()->id)->where('requestCoHostId', $data['requestCoHostId'])->first();
            
            if($sameusrreq) {
                
               $result = 'success';
                
            }else {
                 DB::table('cohosts')->insert([
                'streamer_id' => auth()->user()->id,
                'requestCoHostId' => $data['requestCoHostId'],
                'status' => 0,
                'appid' => $data['appid'],
                'channel' => $data['channel'],
                'token' => $data['token']
            ]);

                 $result = 'success';
            }
           
            
            $msg = 'Request Send Successfully';
        }

        return response()->json(['result' => $result, 'message' => $msg]);
    }

    public function checkCoRequest(Request $request) {

        $data = $request->all();

        $checkjoinreq = DB::table('cohosts')->where('streamer_id', $data['streamerid'])->where('requestCoHostId', auth()->user()->id)->where('status', 0)->first();

        if ($checkjoinreq) {
            $chkanotherusr = DB::table('cohosts')->where('streamer_id',  $data['streamerid'])->where('status', 1)->count();

            if ($chkanotherusr == '2') {
                $result = 'notrefresh';
            } else {
                $result = 'refresh';
            }
        } else {
            $result = 'notrefresh';
        }
        return response()->json(['result' => $result, 'value' => $checkjoinreq]);
    }
    
    public function checkCohost(Request $request) {

        $data = $request->all();


            $chkanotherusr = DB::table('cohosts')->where('streamer_id', auth()->user()->id)->where('status', 1)->count();

            if ($chkanotherusr == '2') {
                $result = 'hide';
            } else {
                $result = 'show';
            }
       
        return response()->json(['result' => $result]);
    }

    public function acceptCoHost(Request $request) {

        $data = $request->all();

        $check = DB::table('cohosts')
                ->where('streamer_id', $data['streamer_id'])
                ->where('requestCoHostId', auth()->user()->id)
                ->where('status', 0)
                ->update(['status' => '1']);

        return response()->json(['message' => 'join as co-host Successfully']);
    }

    public function acceptreq(Request $request) {

        $data = $request->all();

          $checkjoinrequest = DB::table('cohosts')->where('streamer_id', $data['streamerid'])->where('status', 1);

    
          if($checkjoinrequest->count() > 0){

           $checkjoinreq = $checkjoinrequest->get();
                  
            foreach($checkjoinreq AS $key => $checkjoinreqs) {
            
            $usr = User::where('id', $checkjoinreqs->requestCoHostId)->first();

            $cohostname[] = $usr->name;
              $result = 'success';
            }

//            $usr = User::where('id', $checkjoinreq->requestCoHostId)->first();
//
//            $cohostname = $usr->name;
//            $result = 'success';
        } else {
            $cohostname[] = "";
            $result = 'notanycohost';
        }
        return response()->json(['result' => $result, 'cohostname' => $cohostname]);
    }
    
    
    public function CheckCohostRemove(Request $request) {

        $data = $request->all();

        $usr = DB::table('cohosts')->where('streamer_id', $data['streamerid'])->where('requestCoHostId', auth()->user()->id)->first();

        if ($usr) {
            $result = 'success';
        } else {
            $result = 'leave';
        }
        return response()->json(['result' => $result]);
    }

    public function checkNotification(Request $request) {


        $notifications = DB::table('notifications')
                ->select(DB::raw('
        notifications.id id_noty,
        notifications.destination,
        notifications.type,
        notifications.created_at,
        users.id userId,
        users.username,
        users.hide_name,
        users.name,
        users.avatar,
        updates.id,
        updates.description,
        U2.username usernameAuthor,
        messages.message
        '))
                ->leftjoin('users', 'users.id', '=', DB::raw('notifications.author'))
                ->leftjoin('updates', 'updates.id', '=', DB::raw('notifications.target'))
                ->leftjoin('messages', 'messages.id', '=', DB::raw('notifications.target'))
                ->leftjoin('users AS U2', 'U2.id', '=', DB::raw('updates.user_id'))
                ->leftjoin('comments', 'comments.updates_id', '=', DB::raw('notifications.target
        AND comments.user_id = users.id
        AND comments.updates_id = updates.id'))
                ->where('notifications.type', '=', 5)
                ->where('notifications.destination', '=', $request->streamerid)
                ->where('notifications.created_at', '=', Carbon::now()->subSeconds(3))
                ->where('users.status', '=', 'active')
                ->groupBy('notifications.id')
                ->orderBy('notifications.id', 'DESC')
                ->first();

        if ($notifications) {
            $result = 'success';

            $data = '<p>' . $notifications->name . ' sent a <a href="#">Gift</a></p>';
        } else {
            $result = 'not any user send a tip.';
            $data = '';
        }

        return response()->json(['result' => $result, 'notifications' => $notifications, 'data' => $data]);
    }

    public function searchCoHost() {

        $query = $this->request->get('user');
        $appId = $this->request->get('appId');
        $token = $this->request->get('token');
        $channelName = $this->request->get('channelName');
        $data = "";

        if ($query != '' && strlen($query) >= 2) {
            $sql = User::where('username', 'LIKE', '%' . $query . '%')
                    ->where('status', 'active')
                    ->whereVerifiedId('yes')
                    ->where('id', '<>', $this->settings->hide_admin_profile == 'on' ? 1 : 0)
                    // ->where('price', '<>', 0.00)
                    ->whereHideProfile('no')
                    ->orWhere('name', 'LIKE', '%' . $query . '%')
                    ->where('status', 'active')
                    ->whereVerifiedId('yes')
                    ->where('id', '<>', $this->settings->hide_admin_profile == 'on' ? 1 : 0)
                    // ->where('price', '<>', 0.00)
                    ->whereHideProfile('no')
                    ->orderBy('id', 'desc')
                    ->get();

            $subscribers = auth()->user()
                ->mySubscriptions()
                ->where('stripe_id', '=', '')
                ->whereDate('ends_at', '>=', Carbon::today())
                ->orWhere('stripe_status', 'active')
                ->where('stripe_id', '<>', '')
                ->whereStripePlan(auth()->user()->plan)
                ->orWhere('stripe_id', '=', '')
                ->where('stripe_plan', auth()->user()->plan)
                ->where('free', '=', 'yes')
                    ->orderBy('id', 'desc')->get();

            if ($sql) {
                foreach ($sql as $user) {

                    foreach ($subscribers as $subscriber) {

                        if ($user->id == $subscriber->user()->id) {

                            $name = $user->hide_name == 'yes' ? $user->username : $user->name;

                            $onclick = " '$user->id','$appId','$token','$channelName' ";

                            $data .= '<div class="card border-0">
  			<div class="list-group list-group-sm list-group-flush">
                 <a href="javascript:;" class="list-group-item list-group-item-action text-decoration-none py-2 px-3 bg-autocomplete" id="cohostJoin" data-id="' . $user->id . '" onclick="requestCoHost(' . $onclick . ');">
                   <div class="media">
                    <div class="media-left mr-3 position-relative">
                        <img class="media-object rounded-circle" src="' . Helper::getFile(config('path.avatar') . $user->avatar) . '" width="30" height="30">
                    </div>
                    <div class="media-body overflow-hidden">
                      <div class="d-flex justify-content-between align-items-center">
                       <h6 class="media-heading mb-0 text-truncate">
                            ' . $name . '
                        </h6>
                      </div>
  										<span class="text-truncate m-0 w-100 text-left">@' . $user->username . '</span>
                    </div>
                </div>
                <i class="fa fa-paper-plane" aria-hidden="true" id="reqSend"></i>
                
                  </a>
               </div>
  					 </div>';
                        }
                    }
                }
                return $data;
            }
        }
    }

    public function leaveCoHost(Request $request) {

        $data = $request->all();

//        if ($data['status'] == '0') {
//
//            $leavecohost = DB::table('cohosts')->where('streamer_id', $data['streamerid'])->where('requestCoHostId', auth()->user()->id)->where('status', 1)->first();
//
//            if ($leavecohost) {
//
//
//                DB::table('cohosts')->where('streamer_id', $data['streamerid'])->where('requestCoHostId', auth()->user()->id)->update([
//                    'status' => $data['status']
//                ]);
//            }
//        }
        
        DB::table('cohosts')->where('streamer_id', $data['streamerid'])->where('requestCoHostId', auth()->user()->id)->where('status', 1)->delete();


        return response()->json(['message' => 'success']);
    }

    public function hostleaveCohost(Request $request) {

        $data = $request->all();
        
        
        if ($data['status'] == '0') {

            $cohostDetails = User::where('name', $data['cohostName'])->first();
            $cohostId = $cohostDetails->id;

            $leavecohost = DB::table('cohosts')->where('streamer_id', auth()->user()->id)->where('requestCoHostId', $cohostId)->where('status', 1)->first();

            if ($leavecohost) {


                DB::table('cohosts')->where('streamer_id', auth()->user()->id)->where('requestCoHostId', $cohostId)->delete();
            }
        }


        return response()->json(['message' => 'success']);
    }
    
    public function LivestreamUserCount(Request $request) {

        $data = $request->all();

        User::whereId(auth()->user()->id)->update([
            'cnt_usr' => $data['totalUser']
        ]);


        return response()->json(['message' => 'Count']);
    }
    
    public function checkCountUser(Request $request) {

        $data = $request->all();
        
        
        $usr = User::where('id', $data['streamerid'])->first();

        
        return response()->json(['message' => 'success', 'totalCntUser' => $usr->cnt_usr]);
    }
    
    public function sendLiveMessage(Request $request) {

        $data = $request->all();
        
        
        $usr = DB::table('liveChat')->insert([
            'channel' => $data['channel'],
            'user_name' => $data['user_name'],
            'user_img' => $data['user_img'],
            'message' => $data['message']
        ]);
        
        return response()->json(['message' => 'Send message successfully.']);
    }

    public function liveStream() {

        $appID = env('AGORA_APP_ID');
        $appCertificate = env('AGORA_APP_CERTIFICATE');
        $channelName = Auth()->user()->username;
        $uid = 0;
        $uidStr = "0";
        $role = RtcTokenBuilder::RolePublisher;
        $expireTimeInSeconds = 3600;
        $currentTimestamp = (new DateTime("now", new DateTimeZone('UTC')))->getTimestamp();
        $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;

        $streamtoken = RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpiredTs);

        $user = Auth()->user()->name;
        $roles = RtmTokenBuilder::RoleRtmUser;

        $cohostusr = auth()->user()
                ->mySubscriptions()
                ->where('stripe_id', '=', '')
                ->whereDate('ends_at', '>=', Carbon::today())
                ->orWhere('stripe_status', 'active')
                ->where('stripe_id', '<>', '')
                ->whereStripePlan(auth()->user()->plan)
                ->orWhere('stripe_id', '=', '')
                ->where('stripe_plan', auth()->user()->plan)
                ->where('free', '=', 'yes')
                ->orderBy('id', 'desc')->get();

        $chattoken = RtmTokenBuilder::buildToken($appID, $appCertificate, $user, $roles, $privilegeExpiredTs);

        return view('users.livestream', ['appID' => $appID,
            'appCertificate' => $appCertificate,
            'channelName' => $channelName,
            'token' => $streamtoken,
            'username' => $user,
            'chattoken' => $chattoken,
            'cohostusr' => $cohostusr
        ]);
    }
    
    
    
    
     public function uploadEventImg(Request $request) {
        $settings = AdminSettings::first();

        $validator = Validator::make($this->request->all(), [
                    'event' => 'required|mimes:jpg,gif,png,jpe,jpeg|dimensions:min_width=800,min_height=400|max:' . $settings->file_size_allowed . '',
        ]);

        if ($validator->fails()) {
            return response()->json([
                        'success' => false,
                        'errors' => $validator->getMessageBag()->toArray(),
            ]);
            
            
        }

        // PATHS
        $path = config('path.event');

        //<--- HASFILE PHOTO
        if ($this->request->hasFile('event')) {

            $photo = $this->request->file('event');
            $widthHeight = getimagesize($photo);
            $extension = $photo->getClientOriginalExtension();
            $cover = strtolower(auth()->user()->username . '-' . auth()->user()->id . time() . str_random(10) . '.' . $extension);

            set_time_limit(0);
            ini_set('memory_limit', '512M');

            //=============== Image Large =================//
            $width = $widthHeight[0];
            $height = $widthHeight[1];
            $max_width = $width < $height ? 800 : 1500;

            if ($width > $max_width) {
                $coverScale = $max_width / $width;
            } else {
                $coverScale = 1;
            }

            $scale = $coverScale;
            $widthCover = ceil($width * $scale);

            $imgCover = Image::make($photo)->orientate()->resize($widthCover, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })->encode($extension);

            // Copy folder
            Storage::put($path . $cover, $imgCover, 'public');

            //<<<-- Delete old image -->>>/
            Storage::delete(config('path.event') . auth()->user()->event);

            // Update Database
            auth()->user()->update(['event' => $cover]);

            return response()->json([
                        'success' => true,
                        'cover' => Helper::getFile($path . $cover),
            ]);
        }//<--- HASFILE PHOTO
    }
    

    
    public function uploadEvent(Request $request) {
        
        $settings = AdminSettings::first();

        $validator = Validator::make($request->all(), [
                  //  'event' => 'mimes:jpg,gif,png,jpe,jpeg',
//                    'event' => 'mimes:jpg,gif,png,jpe,jpeg|dimensions:min_width=800,min_height=400|max:' . $settings->file_size_allowed . '',
                   'event_image' => 'mimes:jpg,gif,png,jpe,jpeg|dimensions:min_width=200,min_height=100,max_width=300,max_height=150',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                        'success' => false,
                        'errors' => $validator->getMessageBag()->toArray(),
                      //  'errors' => 'The event image must be a file of type: jpg, gif, png, jpe, jpeg.,The event may not be greater than 5120 kilobytes.',
            ]);
            
           // return Redirect()->route('events')->with('error', $validator->getMessageBag()->toArray());
        }
        
        
        $data = $request->all();
     
         // PATHS
        $path = config('path.event');
   
        $cover = "";
        //<--- HASFILE PHOTO
        if ($this->request->hasFile('event_image')) {

            $photo = $this->request->file('event_image');
            $widthHeight = getimagesize($photo);
            $extension = $photo->getClientOriginalExtension();
            $cover = strtolower(auth()->user()->username . '-' . auth()->user()->id . time() . str_random(10) . '.' . $extension);

            set_time_limit(0);
            ini_set('memory_limit', '512M');

            //=============== Image Large =================//
            $width = $widthHeight[0];
            $height = $widthHeight[1];
            $max_width = $width < $height ? 200 : 500;

            if ($width > $max_width) {
                $coverScale = $max_width / $width;
            } else {
                $coverScale = 1;
            }

            $scale = $coverScale;
            $widthCover = ceil($width * $scale);

            $imgCover = Image::make($photo)->orientate()->resize($widthCover, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })->encode($extension);

            // Copy folder
            Storage::put($path . $cover, $imgCover, 'public');

        }//<--- HASFILE PHOTO
        

        DB::table('events')->insert([
                'user_id' => auth()->user()->id,
                'event_img' => $cover,
                'event_name' => $data['event_name'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'event_place' => $data['event_place'],
                'event_type' => $data['event_type']
            ]);
        
        $success = "Event Created Successfully.";
        
         return Redirect()->route('events')->with('success', $success);
      //  return redirect()->back()->with(['success' => trans('general.send_success')]);
        
     //   return response()->json(['success'=> true, 'message' => $success]);
        
    }
    
    public function countAdd(Request $request) {
        $data = $request->all();
        
      $checkInterest = DB::table('event_interest')
               ->where('event_id', $data['event_id'])
               ->where('event_user_id', $data['user_id'])
               ->where('user_id', auth()->user()->id)
               ->first();
      
      if(isset($checkInterest)) {
          DB::table('event_interest')
               ->where('event_id', $data['event_id'])
               ->where('event_user_id', $data['user_id'])
               ->where('user_id', auth()->user()->id)
                  ->update(['interest' => $data['interest']]);
      }else{
               
         DB::table('event_interest')->insert([
                'event_id' => $data['event_id'],
                'event_user_id' => $data['user_id'],
                'user_id' => auth()->user()->id,
                'interest' => $data['interest'],
                ]);
      }
        
        return response()->json(['result' => 1, 'message' => 'Success send intrest']);
    }


    public function events() {
        
        $today = date("Y-m-d");
        
       $events = DB::table('events')
              // ->where('user_id', auth()->user()->id)
              // ->where('end_date', '>=', $today)
               ->where('start_date', '>=', $today)
               ->get();
       
       $eventsInterested = DB::table('event_interest')
              // ->where('user_id', auth()->user()->id)
               ->get();
       
        return view('users.events', ['events' => $events, 'eventsInterested' => $eventsInterested]);
    }
    
}
