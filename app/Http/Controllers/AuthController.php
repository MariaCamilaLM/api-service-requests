<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use App\Models\Engineer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users|max:255',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:client,engineer',
        ]);


        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
            'profile' => $validatedData['role'],
        ]);


        if ($validatedData['role'] === 'client') {
            Client::create(['user_id' => $user->id]);
        } else {
            Engineer::create(['user_id' => $user->id]);
        }

        return response()->json(['message' => 'User registered successfully'], 201);
    }

    /**
     * Update the authenticated user's profile.
     */
    public function updateProfile(Request $request)
    {

        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|string|in:client,engineer',
        ]);


        $user = User::find($validatedData['user_id']);


        Log::info('Current user role:', ['isClient' => $user->isClient(), 'isEngineer' => $user->isEngineer()]);

        $user->profile = $validatedData['role'];
        $user->save();

        if ($validatedData['role'] === 'client') {
            if ($user->isEngineer()) {
                Engineer::where('user_id', $user->id)->delete();
            }

            if (!$user->isClient()) {
                Client::create(['user_id' => $user->id]);
            }
        } elseif ($validatedData['role'] === 'engineer') {
            if ($user->isClient()) {
                Client::where('user_id', $user->id)->delete();
            }

            if (!$user->isEngineer()) {
                Engineer::create(['user_id' => $user->id]);
            }

        } else {
            return response()->json(['message' => 'User not moved to the correct table'], 210);
        }

        return response()->json(['message' => 'Profile updated successfully'], 200);
    }

    public function login(Request $request)
    {
        // Validate the request
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Find the user by email
        $user = User::where('email', $request->email)->first();

        // Check if the user exists and if the password matches
        if ($user && Hash::check($request->password, $user->password)) {
            // Generate the access token
            $token = $user->createToken('YourAppName')->accessToken;

            // Prepare the response with the role and token
            return response()->json([
                'token' => $token,
                'role' => $user->profile // Assuming 'role' is a field in your users table
            ]);
        }

        // If authentication fails, return unauthorized error
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function role(Request $request)
    {
        $user = Auth::user();

        return response()->json(['role' => $user->profile], 200);
    }

}
