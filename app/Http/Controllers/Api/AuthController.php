<?php

namespace App\Http\Controllers\Api;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\Http\Resources\UserResource;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
class AuthController extends Controller
{
    //
    use ApiResponse;
 
     public function register(RegisterRequest $request){
        $user=User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>Hash::make($request->password)
        ]);
        $token=$user->createToken('auth-token')->plainTextToken;

        return $this->success([
            'token' => $token,
            'user' => new UserResource($user)
        ]);
     }

    public function login(LoginRequest $request){
        $user= User::where('email',$request->email)->first();
        if(!$user || !Hash::check($request->password,$user->password)){
           return $this->unauthorized('Invalid credentials');
        }
        $token=$user->createToken('auth-token')->plainTextToken;

        return $this->success([
            'token' => $token,
            'user' => new UserResource($user)
        ]);
    }

    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();
        return $this->success('Logged out successfully');
    }   
public function user(Request $request){
    return $this->success(new UserResource($request->user()));
}

    public function refresh(Request $request){
            $request->user()->currentAccessToken()->delete();
        $token=$request->user()->createToken('auth-token')->plainTextToken;
        return $this->success([
            'token' => $token,
            'user' => new UserResource($request->user())
        ]);
    }
    
}
