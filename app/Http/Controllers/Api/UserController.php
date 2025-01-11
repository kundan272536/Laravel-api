<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
class UserController extends Controller
{
    //--------------------------------------Register Controller Code------------------------------------// 
    public function register(Request $request){
        $validator=Validator::make($request->all(),[
            'name'=>['required','string'],
            'email'=>['required','email','unique:users,email'],
            'password'=>['required','string'],
            'profile_image'=>['nullable','file','mimes:png,jpg,jpeg,webp'],
        ]);
        if($validator->fails()){
            return response()->json(
                [
                    'status'=>'error',
                    'errors'=>$validator->errors()
                ]
            ,422);
        }
        $validatedData=$validator->validated();
     try {
        if($request->hasFile('profile_image')){
            $file=$request->file('profile_image');
            $filename=time().'.'.$file->getClientOriginalExtension();
            $filePath=$file->storeAs('profile_image',$filename,'public');
            $validatedData['profile_image']=$filePath;
        }
        if($request->password){
            $password=Hash::make($validatedData['password']);
            $validatedData['password']=$password;
        }
        $userDetail=new User();
        $userDetail->fill($validatedData);
        if($userDetail->save()){
            $token= $userDetail->createToken('token')->accessToken;
            if ($userDetail->profile_image) {
                $userDetail->profile_image = asset('storage/' . $userDetail->profile_image);
            }
            return response()->json(['status'=>'success','message'=>'User register successfully!','token'=>$token,'data'=>$userDetail]);
        }else{
            return response()->json(['status'=>'error','message'=>'User failed to register!']);
        }
        } 
        catch (\Exception $e) {
            return response()->json(['status'=>'errors',  'message' => 'An error occurred: ' . $e->getMessage(),],500);
        }
    }
    // ------------------------------------------------Login Controller Code----------------------------//
    public function login(Request $request){
        $validator=Validator::make($request->all(),[
            'email'=>['required','email'],
            'password'=>['required','string'],
        ]);
        if($validator->fails()){
            return response()->json([
                'status'=>'error',
                'errors'=>$validator->errors(),
            ],422);
        }
        $validatedData=$validator->validated();
        try {
            $username=$validatedData['email'] ?? '';
            $password=$validatedData['password'] ?? '';
            $userDetail=User::where('email',$username)->first();
            if(!$userDetail){
                return response()->json(['status'=>'success','message'=>'User not found!','data'=>[]],404);
            }
            if(Hash::check($password,$userDetail->password)){
                $token=$userDetail->createToken('access')->accessToken;
                if ($userDetail->profile_image) {
                    $userDetail->profile_image = asset('storage/' . $userDetail->profile_image);
                }
                return response()->json(['status'=>'success','message'=>'User login successfully!','token'=>$token,'data'=>$userDetail]);
            }
            else{
                return response()->json(['status'=>'error','message'=>'Ivalid crdentials'],401);
            }
        } catch (\Exception $e) {
            return response()->json(['status'=>'error','message'=>'An error occured'.$e->getMessage()],500);
        }
    }
}
