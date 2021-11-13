<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return ResponseFormatter::error(NULL, 400, 'Invalid credentials');
            }
        } catch (JWTException $e) {
            return ResponseFormatter::error(NULL, 500, 'Cannot create token. Please call administrator.');
        }
        return ResponseFormatter::success(compact('token'));
    }

    public function me()
    {
        try {

            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return ResponseFormatter::error(NULL, 400, 'User not found');
            }

        } catch (TokenExpiredException $e) {
            return ResponseFormatter::error(NULL, 400, 'Token expired');
        } catch (TokenInvalidException $e) {
            return ResponseFormatter::error(NULL, 400, 'Token invalid');
        } catch (JWTException $e) {
            return ResponseFormatter::error(NULL, 500, 'Token error or not recognized. Please login again');
        }

        return ResponseFormatter::success(compact('user'));
    }

    public function logout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return ResponseFormatter::error(NULL, 419, $validator->messages());
        }

        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return ResponseFormatter::success(NULL);
        } catch (JWTException $exception) {
            return ResponseFormatter::error($exception, 500, 'Sorry, the user cannot be logged out');
        }
    }
}
