<?php

namespace App\Module\Authentication\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use App\Notifications\OtpNotification;
use Carbon\Carbon;
use Throwable;

class AuthService
{
 
 public function registerUser(array $data): User
 {
  try {
   DB::beginTransaction();

   if (User::where('email', $data['email'])->exists()) {
    throw ValidationException::withMessages([
     'email' => ['Email already exists.'],
    ]);
   }

   $user = User::create([
    'name' => $data['name'],
    'email' => $data['email'],
    'password' => Hash::make($data['password']),
   ]);

   $this->sendOtp($user);
   DB::commit();

   return $user;
  } catch (Throwable $e) {
   DB::rollBack();
   throw new \RuntimeException("Failed to register user: " . $e->getMessage(), 0, $e);
  }
 }

 public function login(array $data): array
 {
  $user = User::where('email', $data['email'])->first();

  if (!$user || !Hash::check($data['password'], $user->password)) {
   throw new AuthenticationException('Invalid email or password.');
  }

  if (!$user->email_verified_at) {
   throw new \RuntimeException('Email not verified. Please verify with OTP before logging in.');
  }

  $token = $user->createToken('auth-token')->plainTextToken;

  return [
   'user' => $user,
   'access_token' => $token,
   'token_type' => 'Bearer',
  ];
 }


 public function sendOtpToUser(string $email): void
 {
  $user = User::where('email', $email)->first();

  if (!$user) {
   throw ValidationException::withMessages(['email' => ['User not found.']]);
  }

  if ($user->email_verified_at) {
   throw new \RuntimeException('User email is already verified.');
  }

  if ($user->otp_expires_at && $user->otp_expires_at->gt(Carbon::now()->subMinutes(4))) {
   throw new \RuntimeException('OTP recently sent. Please wait before requesting again.');
  }

  $this->sendOtp($user);
 }


 public function verifyOtp(array $data): array
 {
  $user = User::where('email', $data['email'])->first();

  if (!$user) {
   throw ValidationException::withMessages(['email' => ['User not found.']]);
  }

  if ($user->email_verified_at) {
   throw new \RuntimeException('User already verified.');
  }

  if (!$user->otp || !$user->otp_expires_at) {
   throw new \RuntimeException('No OTP found. Please request a new one.');
  }

  if ($user->otp_expires_at->isPast()) {
   throw new \RuntimeException('OTP has expired. Please request a new one.');
  }

  if (!Hash::check($data['otp'], $user->otp)) {
   throw new \RuntimeException('Invalid OTP.');
  }

  try {
   DB::beginTransaction();

   $user->update([
    'otp' => null,
    'otp_expires_at' => null,
    'email_verified_at' => Carbon::now(),
   ]);

   $token = $user->createToken('auth-token')->plainTextToken;

   DB::commit();

   return [
    'user' => $user,
    'access_token' => $token,
    'token_type' => 'Bearer',
   ];
  } catch (Throwable $e) {
   DB::rollBack();
   throw new \RuntimeException('Failed to verify OTP: ' . $e->getMessage(), 0, $e);
  }
 }


 public function logout($user): void
 {
  if (!$user) {
   throw new AuthenticationException('User not authenticated.');
  }

  $user->currentAccessToken()?->delete();
 }
 private function sendOtp(User $user): void
 {
  try {
   $otp = random_int(100000, 999999);
   $user->otp = Hash::make($otp);
   $user->otp_expires_at = Carbon::now()->addMinutes(5);
   $user->raw_otp_for_test = $otp;
   $user->save();

   $user->notify(new OtpNotification((string) $otp));
  } catch (Throwable $e) {
   $user->otp = null;
   $user->otp_expires_at = null;
   $user->raw_otp_for_test = null;
   $user->save();

   throw new \RuntimeException('Failed to send OTP notification: ' . $e->getMessage(), 0, $e);
  }
 }
}
