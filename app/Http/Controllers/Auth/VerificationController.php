<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Notifications\WelcomeNotification;
use App\Transformers\Json;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Validator;

class VerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */

    use VerifiesEmails;

    /**
     * Where to redirect users after verification.
     *
     * @var string
     */
    // protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
        // $this->middleware('signed')->only('verify');
        // $this->middleware('throttle:6,1')->only('verify', 'resend');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'email_token' => 'required|string|max:255',
        ]);
    }

    public function index(Request $request)
    {

        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            return response()->json(Json::response(422, ['error' => $validator->errors()], "Some fields are requried"), 422);
        }
        $email_token = $request->email_token;

        $account = User::where(['email_token' => $request->email_token])->first();

        if (!$account) {
            return response()->json(Json::response(401, [], "Invalid Verification code"), 401);
        } else {
            $account->update(['email_token' => null, 'email_verified_at' => Carbon::now()]);
            return response()->json(Json::response(200, [], "Account has been verified"), 200);
        }
    }

    public function resend_code(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(Json::response(422, ['error' => $validator->errors()], "Some fields are requried"), 422);
        }

        $email = $request->email;

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(Json::response(401, [], "Invalid details"), 401);
        } else {
            $token = generate_random_str(6);
            //Send Confirm Email Notification to User
            try {
                $user->update(['email_token' => $token]);
                $user->notify(new WelcomeNotification($user));
            } catch (Exception $e) { }

            return response()->json(Json::response(200, [], "Verification code has been sent to " . $request->email), 200);
        }
    }
}
