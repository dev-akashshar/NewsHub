<?php
namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /** Return click count required to open login form */
    public function clickCount()
    {
        return response()->json([
            'count' => (int) Setting::get('logo_click_count', 7)
        ]);
    }

    /** Hidden login */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)
                    ->where('is_active', true)
                    ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Store in session
        session([
            'hidden_user_id'   => $user->id,
            'hidden_user_role' => $user->role,
            'hidden_login_at'  => now()->timestamp,
        ]);

        $user->update(['last_seen' => now()]);

        return response()->json([
            'success'  => true,
            'role'     => $user->role,
            'user'     => [
                'id'         => $user->id,
                'name'       => $user->name,
                'username'   => $user->username,
                'avatar_url' => $user->avatar_url,
            ],
            'redirect' => $user->isAdmin() ? route('admin.dashboard') : route('chat.index'),
        ]);
    }

    /** Logout from hidden mode */
    public function logout(Request $request)
    {
        session()->forget(['hidden_user_id', 'hidden_user_role', 'hidden_login_at']);
        session()->regenerate();

        return response()->json(['success' => true]);
    }

    /** Check if session is still alive (called on visibility change) */
    public function checkSession()
    {
        if (!session()->has('hidden_user_id')) {
            return response()->json(['authenticated' => false]);
        }

        $user = User::find(session('hidden_user_id'));
        if (!$user || !$user->is_active) {
            session()->forget('hidden_user_id');
            return response()->json(['authenticated' => false]);
        }

        $user->update(['last_seen' => now()]);

        return response()->json([
            'authenticated' => true,
            'role'          => $user->role,
        ]);
    }
}
