<?php

use App\Http\Controllers\Api\V1\AppSettingsController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BusinessAnalyticsController;
use App\Http\Controllers\Api\V1\DeleteProfileController;
use App\Http\Controllers\Api\V1\MembersController;
use App\Http\Controllers\Api\V1\UtilityController;
use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
/*
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});*/

Route::prefix('v1')->group(function () {
    // App branding (logo) managed from the admin Settings page
    Route::get('app/settings', [AppSettingsController::class, 'settings']);
    // Authentication routes
    Route::post('auth/otp/resend', [AuthController::class, 'resendOtp']);
    Route::post('auth/otp/verify', [AuthController::class, 'validateOtp']);
    Route::post('auth/user/registration', [AuthController::class, 'userRegistration']);
    Route::post('auth/registration/complete', [AuthController::class, 'completeRegsitration']);
    Route::get('auth/test', [AuthController::class, 'test']);
    Route::get('utilities/countries', [UtilityController::class, 'countries']);
    Route::get('utilities/countries/{countryId}/states', [UtilityController::class, 'states']);
    Route::get('utilities/states/{stateId}/districts', [UtilityController::class, 'districts']);
    Route::post('auth/test/send/email', [AuthController::class, 'sendEmail']);
    Route::post('auth/login/sendotp', [AuthController::class, 'signinWithMobile']);
    Route::post('auth/login/validate', [AuthController::class, 'signinValidateMobile']);
    Route::post('auth/password/forget', [AuthController::class, 'forgotPassword']); //sendotp
    Route::post('auth/password/reset', [AuthController::class, 'resetPassword']);
    Route::post('auth/login/otp/validate', [AuthController::class, 'loginValidateOtp']);
    Route::post('auth/login/otp/resend', [AuthController::class, 'loginResendOtp']);
    Route::post('auth/user/login', [AuthController::class, 'signIn']);

    // test api by Shubha
    Route::get('auth/login/testOtp', [AuthController::class, 'smsTest']);
    // test api by Shubha

    Route::post('profile/remove/getOtp', [DeleteProfileController::class, 'getOtp']);
    Route::post('profile/remove/validate-otp', [DeleteProfileController::class, 'ValidateMobile']);
    Route::post('profile/remove/delete-account', [DeleteProfileController::class, 'deleteRequest']);

    // social media
    Route::post('auth/user/social-media-login', [ApiController::class, 'socialMediaLogin']);
    Route::post('auth/user/save-user-info', [ApiController::class, 'saveUserInfo']);
    // ============================  AFTER USER LOGIN ============================
    Route::get('auth/user/signout', [AuthController::class, 'signout']);
    Route::get('user/profile', [AuthController::class, 'getProfile']);
    Route::post('user/profile/change-password', [ApiController::class, 'changePassword']);
    Route::post('user/profile/edit', [ApiController::class, 'profileEdit']);
    Route::get('user/business-list', [AuthController::class, 'businessList']);
    Route::post('user/business/add', [ApiController::class, 'businessAdd']);
    Route::post('user/business/edit', [ApiController::class, 'businessEdit']);
    Route::get('user/account/delete', [AuthController::class, 'deleteAccount']);
    Route::post('user/policy-terms', [AuthController::class, 'policyAndTerms']);
    Route::get('user/get-category', [ApiController::class, 'getCategory']);
    Route::get('user/dashboard', [ApiController::class, 'dashboard']);
    Route::post('user/search/by/keywords', [ApiController::class, 'searchByKeywords']);

    Route::post('users/post/enquiries', [ApiController::class, 'postEnquiryToBusiness']);
    Route::post('users/post/business-enquiries', [ApiController::class, 'referralEnquiryToBusiness']);
    Route::post('users/post/public-enquiries', [ApiController::class, 'referralEnquiryToPublic']);
    Route::post('users/list/enquiries', [ApiController::class, 'listEnquiriesByUser']);
    Route::post('users/sellers/list/enquiries', [ApiController::class, 'listEnquiriesBySeller']);
    Route::post('users/enquiries/details', [ApiController::class, 'enquiriesDetails']);

    Route::post('business/post/reviews', [ApiController::class, 'saveBusinessReview']);
    Route::post('business/review/edit', [ApiController::class, 'updateBusinessReview']);
    Route::post('business/reviews/list', [ApiController::class, 'businessReviewList']);

    Route::post('companies/details', [ApiController::class, 'getBusinessDetailsById']);
    Route::post('companies/status/update', [ApiController::class, 'updateCompaniesStatus']);

    // Business owner analytics
    Route::post('business/analytics/track', [BusinessAnalyticsController::class, 'track']);
    Route::post('business/analytics/summary', [BusinessAnalyticsController::class, 'summary']);

    // Member directory
    Route::post('members/list', [MembersController::class, 'list']);

    Route::get('user/{id}/test-relation', [ApiController::class, 'testRelation']);
});
