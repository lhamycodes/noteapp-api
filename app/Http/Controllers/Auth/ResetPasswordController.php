<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Notifications\SendResetLinkEmail;
use App\PasswordReset;
use App\Transformers\Json;
use App\User;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    public function sendEmail(Request $request)
    {
        $messages = [
            'email.exists' => "User with email not found",
        ];

        $this->validate($request, [
            'email' => 'required|exists:users'
        ], $messages);

        $user = User::where('email', $request->email)->first();

        $token = Str::random(60);

        PasswordReset::updateOrCreate(
            [
                'user_id' => $user->id,
                'email' => $user->email,
            ],
            [
                'token' => $token
            ]
        );

        $user->notify(new SendResetLinkEmail($user, $token));
        return response()->json(Json::response(200, [], "Password reset requested successfully"), 200);
    }

    public function resetPassword(Request $request)
    {
        $messages = [
            'token.exists' => "Token provided by user is invalid",
        ];

        $this->validate($request, [
            'token' => 'required|exists:password_resets',
            'password' => 'required'
        ], $messages);

        $tokenedUser = PasswordReset::where('token', $request->token)->first();

        $user = User::find($tokenedUser->user_id);

        $user->update([
            'password' => $request->password,
        ]);

        $tokenedUser->delete();

        return response()->json(Json::response(200, [], "Password reset successfully"), 200);
    }
}
