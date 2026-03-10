<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        if($validator->fails()){
            return response()->json([
                'status'=>false,
                'errors'=>$validator->errors()
            ],422);
        }

        $user = User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>Hash::make($request->password)
        ]);

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'status'=>true,
            'message'=>'User registered successfully',
            'token'=>$token,
            'user'=>$user
        ]);
    }


    public function login(Request $request)
    {
        if(!Auth::attempt($request->only('email','password'))){

            return response()->json([
                'status'=>false,
                'message'=>'Invalid credentials'
            ],401);
        }

        $user = Auth::user();

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'status'=>true,
            'message'=>'Login successful',
            'token'=>$token,
            'user'=>$user
        ]);
    }


    public function profile(Request $request)
    {
        return response()->json($request->user());
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'=>true,
            'message'=>'Logged out successfully'
        ]);
    }
}
