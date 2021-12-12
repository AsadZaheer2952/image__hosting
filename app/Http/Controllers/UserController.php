<?php

namespace App\Http\Controllers;

use App\Notifications\ForgotPassword;
use App\Notifications\UserRegister;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Image\Enums\ResponseMessage;
use Image\Repositories\User\UserRepositoryInterface;
use Image\Resources\UserResource;
use Image\Traits\ApiResponseTrait;
use Image\ValidationRequests\LoginRequests;
use Image\ValidationRequests\PasswordResetRequest;
use Image\ValidationRequests\UpdatePasswordRequest;
use Image\ValidationRequests\UserRegisterRequest;
use Image\ValidationRequests\UserUpdateRequest;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Auth;
class UserController extends Controller
{
    use ApiResponseTrait;
    protected $user;
    public function __construct()
    {
        $this->user = app(UserRepositoryInterface::class);
    }

    public function register(UserRegisterRequest $request){
        $user = $this->user->create($request->prepareRequest());
        if($user){
            $updateData = [
                'verificationCode' => mt_rand(10000000, 99999999),
                'verificationCodeSentAt' => now(),
            ];

            $this->user->update($updateData,$user->id);
            $user->notify(new UserRegister($updateData['verificationCode']));
            $data = [
                'email' => $user->email,
                'password' => $request['password'],
            ];

            if (!$token = $this->guard('api')->attempt($data)) {
                return $this->failureResponse( 'Unauthorized', 401);
            }
                $token =  $this->respondWithToken($token);
                $responseData = [
                    'message' => 'Successfully registered, App name sent you a verification code on this email',
                    'timeLimit' => 10,
                    'sendTime' => $updateData['verificationCodeSentAt'],
                    'accessToken' => $token['access_token'],
                    'tokenType' => $token['token_type'],
                    'tokenExpireIn' => $token['expires_in'],
                ];

                return $this->successResponse($responseData,ResponseMessage::CREATED , Response::HTTP_OK);
        }
    }

    public function login(LoginRequests $requests){
        $data = $requests->prepareRequest();
        if (!$token = $this->guard('api')->attempt($data)) {
            return $this->failureResponse( 'Unauthorized', 401);
        }
        $token =  $this->respondWithToken($token);
        $responseData = [
            'message' => 'Successfully login',
            'accessToken' => $token['access_token'],
            'tokenType' => $token['token_type'],
            'tokenExpireIn' => $token['expires_in'],
        ];
        return $this->successResponse($responseData,ResponseMessage::OK , Response::HTTP_OK);
    }


    protected function respondWithToken($token)
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard('api')->factory()->getTTL() * 120
        ];
    }

    public function guard($guard=null)
    {
        return Auth::guard($guard);
    }

    public function verifyCode(Request $request){
        $request->validate([
            'code' => 'required|int',
        ]);

        $user = auth()->user();
        if($user->verification_code === (int)$request->code){
            $user->is_verified = 1;
            $user->save();
            return $this->successResponse(new UserResource($user), ResponseMessage::OK , Response::HTTP_OK);
        }
        return $this->failureResponse(ResponseMessage::MESSAGE_500);

    }

    public function resendCode(){
        $code = mt_rand(10000000, 99999999);
        $user = auth()->user();
        $user->verification_code = $code;
        $user->verification_code_sent_at = now();
        $user->save();
        $user->notify(new UserRegister($code));
        return $this->successResponse(new UserResource($user),'Code resend successfully' , Response::HTTP_OK);
    }

    public function update(UserUpdateRequest $request){
        $userId = auth()->user()->id;
        $user = $this->user->update($request->prepareRequest(),$userId);
        if($user){
            return $this->successResponse(new UserResource($user),ResponseMessage::UPDATED , Response::HTTP_OK);
        }
        return $this->failureResponse(ResponseMessage::MESSAGE_500);
    }

    public function updatePassword(UpdatePasswordRequest $request){
        $user = auth()->user();
        $data = $request->prepareRequest();
        if (Hash::check($data['oldPassword'], $user->password)) {
            $user = $this->user->update($data,$user->id);
            if($user){
                return $this->successResponse(new UserResource($user),ResponseMessage::UPDATED , Response::HTTP_OK);
            }
            return $this->failureResponse(ResponseMessage::MESSAGE_500);
        }
        return $this->failureResponse('Old password not matched');
    }

    public function forgotPassword(Request $request){
        $request->validate([
            'email' => 'required',
        ]);
        $user = $this->user->getByEmail($request->email);
        if($user){
            $random = Str::random(12);
            $user->forgot_token = $random;
            $user->save();
            $user->notify(new ForgotPassword($random));
        }
        return $this->failureResponse("No record found against this email");
    }

    public function resetPassword(PasswordResetRequest $request){
        $reset = $this->user->resetPassword($request->prepareRequest());
        if($reset){
            return response()->json( [ 'message'=> 'Password Reset Successfully','email'=>$reset['email'] ], 200 );
        }else{
            return response()->json( [ 'message'=>'Invalid Token' ], 404 );

        }
    }

}
