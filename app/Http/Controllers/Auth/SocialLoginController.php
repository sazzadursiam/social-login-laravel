<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Http\Request;
// use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class SocialLoginController extends Controller
{
    public function redirectToProvider(String $provider)
    {
        return Socialite::driver($provider)->redirect();
    }
    public function providerCallback(String $provider)
    {
        try {
            try {
                $social_user = Socialite::driver($provider)->user();
            } catch (InvalidStateException $e) {
                $social_user = Socialite::driver($provider)->stateless()->user();
            }
            dd($social_user);
            // First Find Social Account
            $account = SocialAccount::where([
                'provider_name' => $provider,
                'provider_id' => $social_user->getId()
            ])->first();

            // If Social Account Exist then Find User and Login
            if ($account) {
                auth()->login($account->user);
                return redirect()->route('home');
            }

            // Find User
            $user = User::where([
                'email' => $social_user->getEmail()
            ])->first();

            // If User not get then create new user
            if (!$user) {
                $user = User::create([
                    'email' => $social_user->getEmail(),
                    'name' => $social_user->getName()
                ]);
            }

            // Create Social Accounts
            $user->socialAccounts()->create([
                'provider_id' => $social_user->getId(),
                'provider_name' => $provider
            ]);

            // Login
            auth()->login($user);
            return redirect()->route('home');
        } catch (\Exception $e) {
            return redirect()->route('home');
        }
    }
}
