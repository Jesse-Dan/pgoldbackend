<?php

namespace App\Module\Authentication\Controllers;

use App\Http\Controllers\Controller;
use App\Module\Authentication\Services\AuthService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Throwable;

class AuthController extends Controller
{
 public function __construct(private AuthService $authService)
 {
 }

 public function register(Request $request)
 {
  try {
   $validated = $request->validate([
    'name' => 'required|string|max:255',
    'email' => 'required|string|email|max:255|unique:users',
    'password' => 'required|string|min:8',
   ]);

   $user = $this->authService->registerUser($validated);

   return ResponseHelper::created(
    ['user_id' => $user->id],
    'User registered successfully. An OTP has been sent to your email.'
   );
  } catch (ValidationException $e) {
   return ResponseHelper::unprocessableEntity(
    'Validation failed',
    [],
    $e->errors()
   );
  } catch (Throwable $e) {
   return ResponseHelper::internalServerError($e->getMessage());
  }
 }

 public function login(Request $request)
 {
  try {
   $validated = $request->validate([
    'email' => 'required|email',
    'password' => 'required|string|min:8',
   ]);

   $response = $this->authService->login($validated);

   return ResponseHelper::success(
    $response,
    'Login successful.'
   );
  } catch (AuthenticationException $e) {
   return ResponseHelper::unauthorized($e->getMessage());
  } catch (Throwable $e) {
   return ResponseHelper::internalServerError($e->getMessage());
  }
 }

 public function sendOtp(Request $request)
 {
  try {
   $validated = $request->validate(['email' => 'required|email']);
   $this->authService->sendOtpToUser($validated['email']);

   return ResponseHelper::success([], 'New OTP sent to your email.');
  } catch (ValidationException $e) {
   return ResponseHelper::unprocessableEntity('Validation failed', [], $e->errors());
  } catch (Throwable $e) {
   return ResponseHelper::error($e->getMessage(), 400);
  }
 }

 public function verifyOtp(Request $request)
 {
  try {
   $validated = $request->validate([
    'email' => 'required|email',
    'otp' => 'required|string|min:6|max:6',
   ]);

   $result = $this->authService->verifyOtp($validated);

   return ResponseHelper::success($result, 'Email verified and logged in successfully.');
  } catch (ValidationException $e) {
   return ResponseHelper::unprocessableEntity('Validation failed', [], $e->errors());
  } catch (Throwable $e) {
   return ResponseHelper::error($e->getMessage(), 400);
  }
 }

 public function logout(Request $request)
 {
  try {
   $user = $request->user();
   $this->authService->logout($user);

   return ResponseHelper::success([], 'Successfully logged out.');
  } catch (AuthenticationException $e) {
   return ResponseHelper::unauthenticated($e->getMessage());
  } catch (Throwable $e) {
   return ResponseHelper::internalServerError($e->getMessage());
  }
 }
}
