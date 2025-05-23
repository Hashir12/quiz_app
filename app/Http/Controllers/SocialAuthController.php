<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use function Laravel\Prompts\confirm;

class SocialAuthController extends Controller
{
    /**
     * Redirect user to Google or Microsoft authentication page.
     */
    public function redirectToProvider($provider)
    {

        try {
            return Socialite::driver($provider)->redirect();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle callback from Google or Microsoft.
     */
    public function handleProviderCallback($provider)
    {
        $socialUser = Socialite::driver($provider)->stateless()->user();

        $user = User::updateOrCreate([
            'email' => $socialUser->getEmail(),
        ], [
            'name' => $socialUser->getName(),
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
            'avatar' => $socialUser->getAvatar(),
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make(uniqid()),
        ]);

        $token = $user->createToken('auth-token');

        $frontendUrl = config('app.frontend_url') . '?token='.$token->plainTextToken;
        return redirect()->to($frontendUrl);
    }
}
