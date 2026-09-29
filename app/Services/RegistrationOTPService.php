<?php

namespace App\Services;

use App\Models\UserRegistrationOtps;
use App\Notifications\SMSNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RegistrationOTPService
{
    protected $otp;
    protected $otpHash;
    public function generateOTP(int $userId): string
    {
        try {

            $user = UserRegistrationOtps::findOrFail($userId); //Throws exception if user not found.


            $digits = '123456789';
            $otpLength = 4;
            $this->otp = '';
            $this->otpHash = '';

            for ($i = 1; $i <= $otpLength; $i++) {
                $index = rand(0, strlen($digits) - 1);
                $this->otp .= $digits[$index];
            }


            $this->otpHash = password_hash($this->otp, PASSWORD_DEFAULT);

            return DB::transaction(function () use ($user) {

                $user->update([
                                'uro_otp' => $this->otp,
                                'uro_otp_secret'    => $this->otpHash,
                                'uro_expires_at' => Carbon::now()->addMinutes(5), // Use config for expiry time config('otp.expiry_minutes', 5)
                            ]);

                return $this->otp;

            });



        } catch (\Throwable $th) {
            throw $th;
        }


    }

    public function sendOTP(int $userId): void
    {
        try {

            $user = UserRegistrationOtps::findOrFail($userId); //Throws exception if user not found.


            if (strtolower($user->uro_user_type) === 'buyer') {
                // Send email using your preferred method (e.g., Laravel Mail)
                //Mail::to($user->email)->send(new OtpEmail($otp));
            } elseif (strtolower($user->uro_user_type) === 'seller') {
                // Send SMS using your preferred method (e.g., Twilio)
                // Replace this with your actual SMS sending logic.  Example below:
                $this->sendSms($user->uro_id);
            }


        } catch (\Throwable $th) {
            throw $th;
        }

    }

    public function verifyOTP(int $userId, string $enteredOTP): bool
    {
        try {

            $user = UserRegistrationOtps::findOrFail($userId); //Throws exception if user not found.

            if (!$user->uro_otp || !$user->uro_expires_at) {
                return false;
            }

            if (! password_verify($enteredOTP, $user->uro_otp_secret)) {
                return false;
            }

            if (Carbon::parse($user->uro_expires_at)->isPast()) {
                return false;
            }

            $user->update(['otp' => null, 'otp_expiry' => null]); // Clear OTP after verification
            return true;


        } catch (\Throwable $th) {
            throw $th;
        }

    }

    public function isOtpExpired(int $userId): bool
    {
        try {

            $user = UserRegistrationOtps::findOrFail($userId); //Throws exception if user not found.

            return $user->uro_expires_at && Carbon::parse($user->uro_expires_at)->isPast();


        } catch (\Throwable $th) {
            throw $th;
        }

    }

    public function resendOTP(int $userId): bool
    {
        try {

            $user = UserRegistrationOtps::findOrFail($userId); //Throws exception if user not found.

            if (!$this->isOtpExpired($user)) {
                return false; // OTP is not yet expired
            }

            $this->generateOTP($user);
            $this->sendOTP($user);
            return true;


        } catch (\Throwable $th) {
            throw $th;
        }

    }

    // Example SMS sending function - REPLACE WITH YOUR ACTUAL IMPLEMENTATION
    private function sendSms(int $userId): void
    {
        try {

            $user = UserRegistrationOtps::findOrFail($userId); //Throws exception if user not found.

            $user->notify(new SMSNotification());


        } catch (\Throwable $th) {
            throw $th;
        }

    }
}
