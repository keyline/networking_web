<?php

namespace App\Services;

use App\Models\GeneralSetting;
use App\Models\User\UserMaster;
use App\Notifications\LoginSMSNotification;
use App\Notifications\SMSNotification;
use App\Notifications\UserOtpEmailNotify;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class LoginOTPService
{
    protected $otp;
    protected $otpHash;
    public function generateOTP(int $userId): string
    {
        try {

            $user = UserMaster::findOrFail($userId); //Throws exception if user not found.


            $otpLength = 4;
            $this->otp = '';
            $this->otpHash = '';

            for ($i = 1; $i <= $otpLength; $i++) {
                $this->otp .= (string) random_int(0, 9);
            }


            $this->otpHash = password_hash($this->otp, PASSWORD_DEFAULT);

            return DB::transaction(function () use ($user) {

                $user->update([
                                'um_otp' => $this->otp,
                                'um_otp_secret'    => $this->otpHash,
                                'um_otp_expires_at' => Carbon::now()->addMinutes(10), // Use config for expiry time config('otp.expiry_minutes', 5)
                            ]);

                return $this->otp;

            });



        } catch (\Throwable $th) {
            throw $th;
        }


    }

    public function sendEmailOTP(int $userId): void
    {
        $user = UserMaster::findOrFail($userId);

        if (!app()->environment('testing')) {
            $settings = GeneralSetting::find(1);

            if ($settings && $settings->smtp_host && $settings->smtp_username && $settings->smtp_password) {
                config([
                    'mail.default' => 'smtp',
                    'mail.mailers.smtp.host' => $settings->smtp_host,
                    'mail.mailers.smtp.port' => (int) $settings->smtp_port,
                    'mail.mailers.smtp.encryption' => (int) $settings->smtp_port === 465 ? 'ssl' : 'tls',
                    'mail.mailers.smtp.username' => $settings->smtp_username,
                    'mail.mailers.smtp.password' => $settings->smtp_password,
                    'mail.from.address' => $settings->from_email,
                    'mail.from.name' => $settings->from_name,
                ]);
                Mail::purge('smtp');
            }
        }

        // Authentication codes must be delivered during this request and must
        // never depend on a background queue worker being available.
        $user->notifyNow(new UserOtpEmailNotify());
    }

    public function sendOTP(int $userId): void
    {
        try {

            $user = UserMaster::findOrFail($userId); //Throws exception if user not found.


            if ($user->um_utm_id == '2') {

                $this->sendSms($user->um_id);

                //$this->sendEmail($user->um_id);

            } else {

                //$this->sendEmail($user->um_id);

            }


        } catch (\Throwable $th) {
            throw $th;
        }

    }

    public function verifyOTP(int $userId, string $enteredOTP): bool
    {
        try {

            $user = UserMaster::findOrFail($userId); //Throws exception if user not found.

            if (!$user->um_otp || !$user->um_otp_expires_at) {
                return false;
            }

            if (! password_verify($enteredOTP, $user->um_otp_secret)) {
                return false;
            }

            if (Carbon::parse($user->um_otp_expires_at)->isPast()) {
                return false;
            }

            $user->update(['um_otp' => null, 'um_otp_secret' => null, 'um_otp_expires_at' => null]); // Clear OTP after verification
            return true;


        } catch (\Throwable $th) {
            throw $th;
        }

    }

    public function isOtpExpired(int $userId): bool
    {
        try {

            $user = UserMaster::findOrFail($userId); //Throws exception if user not found.

            return $user->um_otp_expires_at && Carbon::parse($user->um_otp_expires_at)->isPast();


        } catch (\Throwable $th) {
            throw $th;
        }

    }

    public function resendOTP(int $userId): bool
    {
        try {

            $user = UserMaster::findOrFail($userId); //Throws exception if user not found.

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

            $user = UserMaster::findOrFail($userId); //Throws exception if user not found.

            $user->notify(new LoginSMSNotification());


        } catch (\Throwable $th) {
            throw $th;
        }

    }

    private function sendEmail(int $userId): void
    {
        try {

            $user = UserMaster::findOrFail($userId); //Throws exception if user not found.

            $user->notify(new UserOtpEmailNotify($user));

        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
