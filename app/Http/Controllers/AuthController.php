<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\Streak;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        $email = strtolower($request->email);

        $user = User::where('email', $email)->first();

        $otp = rand(100000, 999999);
        $otpExpiry = Carbon::now()->addMinutes(10);

        if ($user) {
            // If user exists, verify the password, sending the otp again
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'Incorrect password. Please enter the correct password to receive a new OTP.'
                ], 401);
            }

            $user->otp = $otp;
            $user->otp_expires_at = $otpExpiry;
            $user->save();

        } else {
            $user = User::create([
                'name' => $request->name,
                'email' => $email,
                'password' => Hash::make($request->password),
                'otp' => $otp,
                'otp_expires_at' => $otpExpiry,
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;
            $user->token = $token;

            Streak::create([
                'user_id' => $user->id,
                'streak_count' => 0,
                'last_login' => now(),
            ]);
        }

        Mail::raw("Your OTP code is: $otp", function ($message) use ($user) {
            $message->to($user->email)->subject('Email Verification OTP');
        });

        return response()->json([
            'message' => 'OTP has been sent to your email.',
            'user' => new UserResource($user),
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.'
            ],403);
        }

        // If user registered via Social Login and tries to log in manually
        if ($user->provider) {
            return response()->json(['message' => "This email is linked to a social account. Please log in using $user->provider."], 403);
        }

        $token = $user->createToken('auth-token');

        Streak::updateStreak($user);

        return response()->json(['token' => $token->plainTextToken], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    public function getUserStreak($id)
    {
        $streak = Streak::where('user_id',$id)->value('streak_count');
        if (!$streak) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json(['streak_score' => $streak], 200);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|digits:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || $user->otp !== $request->otp) {
            return response()->json(['message' => 'Invalid OTP'], 400);
        }

        if (Carbon::now()->greaterThan($user->otp_expires_at)) {
            return response()->json(['message' => 'OTP has expired'], 400);
        }

        // Mark email as verified
        $user->email_verified_at = now();
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();

        return response()->json(['message' => 'Email verified successfully']);
    }

    public function resetPasswordEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);

        $email = strtolower($request->email);

        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'No user found with this email'],404);
        }

        $otp = rand(100000, 999999);
        $otpExpiry = Carbon::now()->addMinutes(10);

        $user->otp = $otp;
        $user->otp_expires_at = $otpExpiry;
        $user->save();

        Mail::raw("Your OTP code is: $otp", function ($message) use ($user) {
            $message->to($user->email)->subject('Password reset OTP');
        });
        return response()->json([
            'message' => 'OTP has been sent to your email.',
        ], 200);
    }

    public function resetPasswordOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $user = User::where('otp', $request->otp)->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid OTP'], 400);
        }

        if (Carbon::now()->greaterThan($user->otp_expires_at)) {
            return response()->json(['message' => 'OTP has expired'], 400);
        }

        return response()->json([
            'user_email' => $user->email
        ],200);
    }

    public function resetPassword(Request $request)
    {

        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string|min:8',
        ]);


        if ($request->password != $request->confirm_password) {
            return response()->json([
                'message' => 'Password does not match',
            ], 404);
        }

        $email = strtolower($request->email);
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'No account found with this email.',
            ], 404);
        }

        // Reset password
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Send password change confirmation email
        Mail::raw("Your password has been successfully reset.", function ($message) use ($user) {
            $message->to($user->email)->subject('Password Reset Confirmation');
        });

        return response()->json([
            'message' => 'Password reset successfully.',
            'user' => new UserResource($user),
        ], 200);
    }
}
