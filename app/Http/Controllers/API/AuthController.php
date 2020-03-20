<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Notifications\WelcomeNotification;
use App\Transformers\Json;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use JWTAuth;

class AuthController extends Controller
{
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request, JWTAuth $JWTAuth)
    {
        $credentials = $request->only('email', 'password');

        $rules = [
            'email' => 'required',
            'password' => 'required',
        ];

        //Validates data
        $validator = $this->validate($request, $rules);
        if (!$validator) {
            return response()->json(Json::response(422, ['error' => $validator->errors()], "Some fields are required"), 422);
        }

        if ($token = JWTAuth::attempt($credentials)) {
            // if (auth()->user()->email_token) {
            //     //logout the user
            //     // auth()->logout();
            //     return response()->json(Json::response(400, [], "Your Account has not been verified. Please follow the link in the mail that was sent to you"), 400);
            // }

            $tokenData = [
                'access_token' => $token,
                'expires_at' => Carbon::now()->addMinutes(auth()->factory()->getTTL() * 60),
            ];

            return response()->json(Json::response(200, User::authUser($tokenData), "Login successful"), 200);
        } else {
            return response()->json(Json::response(401, [], "Invalid login details"), 401);
        }
    }

    public function register(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|max:255',
        ];

        $validator = $this->validate($request, $rules);

        if (!$validator) {
            return response()->json(Json::response(422, ['error' => $validator->errors()], "Some fields are required"), 422);
        }

        DB::beginTransaction();
        try {

            //Create user
            $user = User::create([
                'uuid' => Str::orderedUuid(),
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
                'email_verified_at' => Carbon::now(),
            ]);

            $user->assignRole('user');

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return $e;
        }

        if ($user) {
            // $user->notify(new WelcomeNotification($user));

            $credentials = $request->only('email', 'password');

            if ($token = JWTAuth::attempt($credentials)) {
                $tokenData = [
                    'access_token' => $token,
                    'expires_at' => Carbon::now()->addMinutes(auth()->factory()->getTTL() * 60),
                ];
                return response()->json(Json::response(200, User::authUser($tokenData), "Registration Succcessful"), 200);
            } else {
                return response()->json(Json::response(401, [], "Invalid login details"), 401);
            }
        } else {
            return response()->json(Json::response(400, [], "Could not create user account"), 400);
        }
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $token = JWTAuth::getToken();
        $new_token = JWTAuth::refresh($token);
        if ($new_token) {
            return response()->json(Json::response(200, [
                "token" => $new_token,
            ], "Token refreshed successfully"), 200);
        }
    }
}
