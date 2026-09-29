<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use Illuminate\Http\Request;

/**
 * Branding for the mobile app, taken from the admin Settings page so the
 * logo can be changed without releasing a new app version.
 */
class AppSettingsController extends Controller
{
    public function settings(Request $request)
    {
        if (!hash_equals((string) env('PROJECT_KEY'), (string) $request->header('key'))) {
            $this->response_to_json(false, 'Unauthenticate Request !!!');
        }

        $setting = GeneralSetting::find(1);
        $fileUrl = fn (?string $file) => !empty($file) ? env('UPLOADS_URL') . $file : null;

        $this->response_to_json(true, 'Data Available !!!', [
            'site_name'   => $setting->site_name ?? '',
            'logo'        => $fileUrl($setting->site_logo ?? null),
            'footer_logo' => $fileUrl($setting->site_footer_logo ?? null),
            'favicon'     => $fileUrl($setting->site_favicon ?? null),
            // Lets the app notice a changed logo even if the file name is reused
            'updated_at'  => optional($setting?->updated_at)->toIso8601String(),
        ]);
    }
}
