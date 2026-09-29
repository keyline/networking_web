<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Libraries\JWT;
use App\Models\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Member directory for the mobile app: active business owners with their
 * business name(s), category and phone.
 */
class MembersController extends Controller
{
    private const PER_PAGE = 20;
    private const GUEST_TYPE_ID = 3;

    public function list(Request $request)
    {
        $headerData = $request->header();
        if (($headerData['key'][0] ?? null) != env('PROJECT_KEY')) {
            $this->response_to_json(false, 'Unauthenticate Request !!!');
        }

        $auth = $this->tokenAuth($headerData['authorization'][0] ?? null);
        if (!$auth['status']) {
            $this->response_to_json(false, $auth['data']);
        }

        // Guests share one anonymous account, so they never see full numbers
        $isGuest = DB::table('user_master')
            ->where('um_id', (int) $auth['data'][1])
            ->value('um_utm_id') == self::GUEST_TYPE_ID;

        $page   = max(1, (int) $request->input('page_no', 1));
        $search = trim((string) $request->input('search', ''));

        $query = DB::table('user_master as um')
            ->join('user_details as ud', 'ud.ud_um_id', '=', 'um.um_id')
            ->join('user_companies_map as ucm', 'ucm.ucm_um_id', '=', 'um.um_id')
            ->join('companies_details as cd', function ($join) {
                $join->on('cd.cmpd_cmp_id', '=', 'ucm.ucm_cmp_id')
                    ->where('cd.cmpd_status', '=', 1);
            })
            ->leftJoin('categories_to_companies as ctc', 'ctc.ctc_cmp_id', '=', 'cd.cmpd_cmp_id')
            ->leftJoin('business_category_master as bcm', 'bcm.bcm_id', '=', 'ctc.ctc_bcm_id')
            ->where('um.um_utm_id', 2)     // sellers
            ->where('um.um_status', 2);    // active, verified accounts

        if ($search !== '') {
            $like = '%' . $search . '%';
            $query->where(function ($q) use ($like) {
                $q->where('ud.ud_first_name', 'like', $like)
                    ->orWhere('ud.ud_last_name', 'like', $like)
                    ->orWhere(DB::raw("CONCAT(ud.ud_first_name, ' ', ud.ud_last_name)"), 'like', $like)
                    ->orWhere('cd.cmpd_name', 'like', $like)
                    ->orWhere('bcm.name', 'like', $like)
                    ->orWhere('um.um_mobile_no', 'like', $like);
            });
        }

        $total = (clone $query)->distinct()->count('um.um_id');

        $rows = $query
            ->groupBy('um.um_id', 'ud.ud_first_name', 'ud.ud_last_name', 'ud.ud_profile_image', 'um.um_mobile_no')
            ->orderBy('ud.ud_first_name')
            ->orderBy('ud.ud_last_name')
            ->offset(($page - 1) * self::PER_PAGE)
            ->limit(self::PER_PAGE)
            ->selectRaw("
                um.um_id,
                ud.ud_first_name,
                ud.ud_last_name,
                ud.ud_profile_image,
                um.um_mobile_no,
                MIN(cd.cmpd_cmp_id) as business_id,
                GROUP_CONCAT(DISTINCT cd.cmpd_name ORDER BY cd.cmpd_name SEPARATOR '||') as business_names,
                GROUP_CONCAT(DISTINCT bcm.name ORDER BY bcm.name SEPARATOR '||') as categories
            ")
            ->get();

        $members = $rows->map(function ($row) use ($isGuest) {
            $businesses = array_values(array_filter(explode('||', (string) $row->business_names)));
            $categories = array_values(array_filter(explode('||', (string) $row->categories)));
            $phone      = (string) $row->um_mobile_no;

            return [
                'id'            => (int) $row->um_id,
                'name'          => trim(($row->ud_first_name ?? '') . ' ' . ($row->ud_last_name ?? '')) ?: 'Member',
                'profile_image' => !empty($row->ud_profile_image)
                    ? env('UPLOADS_URL') . 'user/' . $row->ud_profile_image
                    : null,
                'phone'         => $isGuest ? $this->maskPhone($phone) : $phone,
                'phone_hidden'  => $isGuest,
                'business_id'   => (int) $row->business_id,
                'business_name' => $businesses[0] ?? '',
                'more_businesses' => max(0, count($businesses) - 1),
                'category'      => $categories[0] ?? 'Unclassified',
            ];
        })->all();

        $this->response_to_json(true, 'Data Available !!!', [
            'members'  => $members,
            'total'    => $total,
            'page_no'  => $page,
            'has_more' => ($page * self::PER_PAGE) < $total,
        ]);
    }

    /** "9830012345" -> "98XXXXXX45" */
    private function maskPhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if (strlen($digits) < 5) {
            return str_repeat('X', strlen($digits));
        }
        return substr($digits, 0, 2) . str_repeat('X', strlen($digits) - 4) . substr($digits, -2);
    }

    /* Same token check the other API controllers use */
    private function tokenAuth($appAccessToken)
    {
        if (empty($appAccessToken)) {
            return ['status' => false, 'data' => 'Token Not Found In Request !!!'];
        }

        $userdata = $this->matchToken($appAccessToken);
        if (!$userdata['status']) {
            return ['status' => false, 'data' => 'Token Not Found !!!'];
        }

        $checkToken = UserDevice::where('user_id', '=', $userdata['data']->id)
            ->where('app_access_token', '=', $appAccessToken)
            ->first();
        if (empty($checkToken) || !$userdata['data']->exp || $userdata['data']->exp <= time()) {
            return ['status' => false, 'data' => 'Token Has Expired !!!'];
        }

        return ['status' => true, 'data' => [true, $userdata['data']->id]];
    }

    private static function matchToken($token)
    {
        try {
            $key = "1234567890qwertyuiopmnbvcxzasdfghjkl";
            $decoded = JWT::decode($token, $key, ['HS256']);
        } catch (\Exception $e) {
            return ['status' => false, 'data' => ''];
        }
        return ['status' => true, 'data' => $decoded];
    }
}
