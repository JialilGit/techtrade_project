<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(Request $request){
        $request->validate([
            'name'=>'required',
            'email'=>'required|email',
            'phone'=>'required',
            'address'=>'required',
            'password'=>'required|confirmed',
            'tc'=>'required',

        ]);
        if(User::where('email', $request->email)->first()){
            return response([
                'message' =>  'Email already exists',
                'status' => 'failed'
            ], 200);
        }

        $user = User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'phone'=>$request->phone,
            'address'=>$request->address,
            'password'=>Hash::make($request->password),
            'tc'=>json_decode($request->tc),
        ]);

        $token = $user->createToken($request->email)
        ->plainTextToken;
        return response([
            'token'=>$token,
            'message' =>  'Registration Success',
            'status' => 'success'
        ], 201);
    }

    public function login(Request $request){
        $request->validate([
            'email'=>'required|email',
            'password'=>'required',   
        ]);
        $user = User::where('email', $request->email)->first();
        if($user && Hash::check($request->password, 
        $user->password)){
            $token = $user->createToken($request->email)
            ->plainTextToken;
            return response([
                'token'=>$token,
                'message' =>  'Login Success',
                'status' => 'success'
            ], 200);
            
        }
        return response([
            'message' =>  'The Provided Credentials are incorrect',
            'status' => 'failed'
        ], 401);
        
    }

    public function logout(){
        auth()->user()->tokens()->delete();
        return response([
            'message' =>  'Logout Success',
            'status' => 'success'
        ], 200);

    }

    public function logged_user(){
        $loggeduser = auth()->user();
        return response([
            'user' => $loggeduser,
            'message' =>  'Logged User Data',
            'status' => 'success'
        ], 200);

    }

    public function change_password(Request $request){
        $request->validate([
            'password' => 'required|confirmed', 
        ]);
        $loggeduser = auth()->user();
        $loggeduser->password = Hash::make($request->password);
        $loggeduser->save();
        return response([
            'message' =>  'Password Changed Successfully',
            'status' => 'success'
        ], 200);


    }
}
