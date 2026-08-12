<?php

namespace App\Http\Controllers\Api\V1;

use Auth;
use Carbon\Carbon;
use App\Models\OwnerOfUser;
use App\Models\UserSetting;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\ResetPasswordEmail;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Models\{Categories, User, UserCategory};

class ApiV1AuthController extends Controller
{
	public function login(Request $request)
	{
		$success = false;
		$message = 'Something Wrong!';
		$data = array();
		$statuscode = 422;

		try {
			$msg = [];

			$rules['email'] = 'required|string|max:255';
			$rules['password'] = 'required|string|min:6';

			$validator = Validator::make($request->all(), $rules, $msg);
			if ($validator->fails()) {
				$message = $validator->errors()->first();
			} else {
				$identifier = $request->email;
				$user = User::query()
					->where(function ($q) use ($identifier) {
						$q->where('email', $identifier);
						if (is_numeric($identifier) && $identifier > 0) {
							$q->orWhere('contact', $identifier);
						}
					})
					->first();

				if ($user && !Hash::check($request->password, $user->password)) {
					$message = "Password mismatch";
					return apiResponce($statuscode, $success, $message, $data);
				}

				if ($user) {
					$token = $user->createToken('Laravel Password Grant Client')->accessToken;
					$data['user_details'] = $user->apiObject();
					$data['token'] = $token;
					$user->save();

					$statuscode = 200;
					$success = true;
					$message = 'Success';
				} else {
					$statuscode = 51;
					$message = 'User does not exist';
				}
			}
		} catch (\Exception $e) {
			$message = $e->getMessage();
		}
		return apiResponce($statuscode, $success, $message, $data);
	}

	public function register(Request $request)
	{
		$success = false;
		$message = 'Something Wrong!';
		$data = array();
		$statuscode = 422;

		$msgs = [];
		$rules = [
			'first_name' => 'required|max:191',
			'last_name' => 'required|max:191',
			'contact' => 'required|numeric|digits:10|unique:users,contact',
			'email' => 'required|email|unique:users,email|max:191',
			'dob' => 'nullable|date',
			'password' => 'required|min:6',
			'confirm_password' => 'required|min:6',
		];


		$validator = Validator::make($request->all(), $rules, $msgs);
		if ($validator->fails()) { // Validation fails
			$message = $validator->errors()->first();
		} elseif ($request->password != $request->confirm_password) {
			$message =  'Confirm password is not match';
		} else {
			try {
				$User = new User();
				$User->first_name = trim($request->first_name);
				$User->last_name = trim($request->last_name);
				$User->email = trim($request->email);
				$User->contact = $request->contact == 0 ? null : $request->contact;
				$User->dob = $request->dob;
				$User->role = 'User';
				$User->password = Hash::make($request->password);
				$User->save();

				$user = User::find($User->id);
				$token = $user->createToken('Laravel Password Grant Client')->accessToken;
				$data['user_details'] = $user->apiObject();
				$data['token'] = $token;

				$success = true;
				$message =  'User register SuccessFully';
				$statuscode = 200;
			} catch (\Exception $e) {
				$message = $e->getMessage();
			}
		}
		return apiResponce($statuscode, $success, $message, $data);
	}

	public function forgotPassword(Request $request)
	{
		$success = false;
		$message = 'Something Wrong!';
		$data = array();
		$statuscode = 422;

		$rules = [
			'email' => 'required|email',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) { // Validation fails
			$message = $validator->errors()->first();
		} else {
			try {
				$User = User::where('email', $request->email)->first();
				if ($User) {
					if ($request->email) {
						DB::table('password_reset_tokens')->where('email', $request->email)->delete();
					}
					$token = Str::random(64);
					DB::table('password_reset_tokens')->insert([
						'email' => $request->email,
						'token' => $token,
						'created_at' => Carbon::now()
					]);
					$maildata = [
						'username' => $User->username,
						'token' => $token,
						'url' => route('password.reset', [$token, $request->email])
					];

					Mail::to($request->email)->send(new ResetPasswordEmail($maildata));
					$success = true;
					$message =  'Successfully varification code send from you email address, please check your email';
					$statuscode = 200;
				} else {
					$message =  'Please enter registered email address';
				}
			} catch (\Exception $e) {
				$message = $e->getMessage();
			}
		}
		return apiResponce($statuscode, $success, $message, $data);
	}

	public function ResetPassword(Request $request)
	{
		$success = false;
		$message = 'Something Wrong!';
		$data = array();
		$statuscode = 422;

		$rules = [
			'email' => 'required|email|exists:users,email',
			'token' => 'required|string',
			'password' => 'required|min:6',
			'confirm_password' => 'required|min:6',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) { // Validation fails
			$message = $validator->errors()->first();
		} elseif ($request->password != $request->confirm_password) {
			$message =  'Confirm password is not match';
		} else {
			try {
				$reset = DB::table('password_reset_tokens')
					->where('email', $request->email)
					->where('token', $request->token)
					->first();

				if ($reset) {
					$createdAt = Carbon::parse($reset->created_at);
					if ($createdAt->addMinutes(60)->isPast()) {
						$message = 'Reset token has expired';
					} else {
						$User = User::where('email', $request->email)->first();
						$User->password = Hash::make($request->password);
						$User->save();

						DB::table('password_reset_tokens')->where('email', $request->email)->delete();

						$success = true;
						$message = 'Success';
						$statuscode = 200;
					}
				} else {
					$message = 'Invalid reset token';
				}
			} catch (\Exception $e) {
				$message = $e->getMessage();
			}
		}
		return apiResponce($statuscode, $success, $message, $data);
	}

	public function googleLogin(Request $request)
	{
		$success = false;
		$message = 'Something Wrong!';
		$data = array();
		$statuscode = 422;

		try {
			$rules = [
				'email' => 'required|email|max:255',
			];

			$validator = Validator::make($request->all(), $rules);
			if ($validator->fails()) {
				$message = $validator->errors()->first();
			} else {
				$email = trim($request->email);
				$user = User::where('email', $email)->first();

				if (!$user) {
					$firstName = trim($request->first_name ?? '');
					$lastName = trim($request->last_name ?? '');

					if (empty($firstName)) {
						$nameParts = explode('@', $email);
						$firstName = ucfirst($nameParts[0]);
					}

					$user = new User();
					$user->first_name = $firstName;
					$user->last_name = $lastName;
					$user->email = $email;
					$user->role = 'User';
					$user->password = Hash::make(Str::random(16));
					$user->save();
				}

				$token = $user->createToken('Laravel Password Grant Client')->accessToken;
				$data['user_details'] = $user->apiObject();
				$data['token'] = $token;

				$statuscode = 200;
				$success = true;
				$message = 'Google login successful';
			}
		} catch (\Exception $e) {
			$message = $e->getMessage();
		}
		return apiResponce($statuscode, $success, $message, $data);
	}

	public function logout(Request $request)
	{
		$success = false;
		$message = 'Something Wrong!';
		$data = array();
		$statuscode = 422;
		try {
			$user = $request->user();
			$user->tokens()->delete();
			$success = true;
			$message =  'Logouted';
			$statuscode = 200;
		} catch (\Exception $e) {
			$message = $e->getMessage();
		}
		return apiResponce($statuscode, $success, $message, $data);
	}
}
