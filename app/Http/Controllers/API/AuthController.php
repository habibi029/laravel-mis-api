<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\Admin;
use App\Models\Staff;
use App\Models\Client;
use Illuminate\Http\Request;
use Laravel\Passport\Passport;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\AdminResource;
use App\Http\Resources\StaffResource;
use App\Http\Responses\ErrorResponse;
use App\Http\Resources\ClientResource;
use App\Http\Responses\SuccessResponse;
use Illuminate\Support\Facades\Validator;
use App\Http\Responses\ValidationResponse;
use Illuminate\Contracts\Auth\Authenticatable;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return new ValidationResponse($validator->errors());
        }

        $credentials = request(['email', 'password','remember_me']);

        $staffs = Staff::where([
            ['email','=',$credentials['email']],
            ['is_active','=',1]
        ])->get();

        $auth_user = $this->findUserByPassword($staffs, $credentials['password']);

        if(!$auth_user) //check admin next
        {
            return $this->loginAdmin($request);
        }

        if (!Auth::guard('api-staff')->setUser($auth_user))
            return (new ErrorResponse)->unauthorize();

        $staff = Auth::guard('api-staff')->user();

        if((int)($credentials['remember_me']) == 1)  Passport::personalAccessTokensExpireIn(\Carbon\Carbon::now()->addDays(30));
        else Passport::personalAccessTokensExpireIn(Carbon::now()->addDays(1));

        $tokenResult = $staff->createToken('Staff Token',['staff_user']);
        $token = $tokenResult->token;

        $token->save();
        return new SuccessResponse(
            StaffResource::make($auth_user),
            [
                'access_token' => $tokenResult->accessToken,
                'token_type' => 'Bearer',
                'expires_at' => $tokenResult->token->expires_at->toDateTimeString(),
            ]
        );
    }

    public function loginAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $credentials = request(['email', 'password','remember_me']);

        $admins = Admin::where('email',$credentials['email'])->get();
        $auth_user = $this->findUserByPassword($admins, $credentials['password']);

        if(!$auth_user) return new ErrorResponse(message: 'Wrong Username/Password combination', code: Response::HTTP_UNAUTHORIZED);

        if (!Auth::guard('api-admin')->setUser($auth_user)) {
            return (new ErrorResponse())->unauthorize();
        }

        $admin = Auth::guard('api-admin')->user();

        if((int)($credentials['remember_me']) == 1)  Passport::personalAccessTokensExpireIn(\Carbon\Carbon::now()->addDays(30));
        else Passport::personalAccessTokensExpireIn(Carbon::now()->addDays(1));

        $tokenResult = $admin->createToken('Admin Token', ['admin_user']);
        $token = $tokenResult->token;

        $token->save();
        return new SuccessResponse(
            AdminResource::make($admin),
        [
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => $tokenResult->token->expires_at->toDateTimeString(),
        ]);
    }

    public function findUserByPassword(Collection $users, string $password): ?Authenticatable
    {
        foreach($users as $user){
            if(Hash::check($password ,$user->password)){
                return $user;
            }
        }
        return null;
    }

    public function auth(Request $request)
    {
        $user = auth()->user();
        if(!$user) return new ErrorResponse(message: 'Unauthenticated', code: Response::HTTP_UNAUTHORIZED);

        $user_type = $this->check_user_type($user);

        $resources = [
            'staff' => StaffResource::class,
            'admin' => AdminResource::class,
        ];

        if (!isset($resources[$user_type])) {
            return new ErrorResponse(message: 'Invalid user type', code: Response::HTTP_BAD_REQUEST);
        }

        return new SuccessResponse(
            $resources[$user_type]::make($user),
            []
        );
    }

    private function check_user_type($user)
    {
        if (Auth::check() && auth()->user()->tokenCan('staff_user')) {
            return 'staff';
        } else if (Auth::check() && auth()->user()->tokenCan('admin_user')) {
            return 'admin';
        }
        return null;
    }

    public function logout(Request $request)
    {
        try {
            $token = $request->user()->token();
            if($token) $token->revoke();
            $cookie = cookie()->forget('access_token'); //cookie in frontend
            session()->invalidate();
            return new SuccessResponse(message: 'Successfully logged out', cookie: $cookie);
        } catch (\Exception $e){
            return new ErrorResponse(message: 'Error Occured', code : Response::HTTP_BAD_REQUEST, e: $e);
        }
    }

    public function question_and_answer(Request $request){
        $validated = $request->validate([
            'email' => 'required|email',
            'answer_1' => 'required',
            'answer_2' => 'required',
            'answer_3' => 'required',
            'new_password' => 'required',
            'confirm_password' => 'required|same:new_password',
        ]);

        $admin = Admin::where('email', $validated['email'])->first();



        if (!$admin) {
            return response()->json(['error' => 'Email not found.'], 404);
        }

        // Get the security answers
        $securityAnswers = $admin->securityQuestionAnswers;

        if (
            $securityAnswers->answer_1 !== $validated['answer_1'] ||
            $securityAnswers->answer_2 !== $validated['answer_2'] ||
            $securityAnswers->answer_3 !== $validated['answer_3']
        ) {
            return response()->json(['error' => 'Incorrect answers.'], 400);
        }

        if ($validated['new_password'] !== $validated['confirm_password']) {
            return response()->json(['error' => 'Passwords do not match.'], 400);
        }

        $admin->update(['password' => bcrypt($validated['new_password'])]);

        return response()->json([
            'Message' => 'Password updated successfully.',
        ], 200);
    }


}
