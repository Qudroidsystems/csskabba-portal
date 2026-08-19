<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * The user has been authenticated.
     * Override to handle intended URL from 419 redirect
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        // Check if there's an intended URL from the 419 redirect
        if ($intendedUrl = session('intended')) {
            // Clear it so it doesn't persist
            session()->forget('intended');

            // Validate URL to prevent open redirect attacks
            if ($this->isValidIntendedUrl($intendedUrl, $request)) {
                return redirect($intendedUrl);
            }
        }

        // If no intended URL or invalid, use default redirect
        return redirect()->intended($this->redirectPath());
    }

    /**
     * Validate that the intended URL is safe to redirect to.
     *
     * @param  string  $url
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function isValidIntendedUrl($url, $request)
    {
        // Parse the URL
        $parsedUrl = parse_url($url);

        // If it's a relative URL (no host), it's safe
        if (!isset($parsedUrl['host'])) {
            return true;
        }

        // If it's an absolute URL, ensure it's our own domain
        return $parsedUrl['host'] === $request->getHost();
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'email';
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}