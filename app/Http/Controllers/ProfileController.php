<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests\UserAccountRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Account;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        App::setLocale($request->user()->locale);

        return Redirect::route('profile.edit')->with('status', __('Your profile was successfully updated.'));
    }

    public function storeAccount(UserAccountRequest $request): RedirectResponse
    {
        Account::create(array_merge($request->validated(), [
            'user_id' => auth()->id(),
        ]));

        return Redirect::route('profile.edit')->with('status', __('External account successfully created.'));
    }

    public function updateAccount(UserAccountRequest $request, Account $account): RedirectResponse
    {
        $account->update($request->validated());

        return Redirect::route('profile.edit')->with('status', __('External account successfully updated.'));
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/')->with('status', __('Votre compte a bien été supprimé.'));
    }
}
