<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = User::paginate(10);
        return response()->json([
            'status'=>true,
            'message'=>'All users',
            'user'=>$user
        ]);
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        if ($user->id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to update this user.',
            ], 403);
        }
        
        $data = $request->validate([
            'name'     => ['sometimes', 'string', 'max:255'],
            'email'    => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['sometimes', 'string', 'min:8'],
        ]);

        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $user->update($data);

        return response()->json([
            'status'=>true,
            'message'=>'user updated successfully',
            'user'=>$user
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if ($user->id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to update this user.',
            ], 403);
        }
        
        $user->delete();

        return response()->json([
            'status'=>true,
            'message'=>'User Deleted Successfully'
        ]);
    }
}
