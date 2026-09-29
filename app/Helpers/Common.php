<?php

use App\Models\User\UserMaster;
use Illuminate\Support\Facades\DB;

if (!function_exists('format_date')) {
    function format_date(?string $date, string $format = 'M d, Y h:i a'): ?string
    {
        return $date ? date($format, strtotime($date)) : null;
    }
}

if (!function_exists('getBusinessRating')) {
    /**
     * Get the average rating and total reviews for a given business.
     *
     * @param  int  $businessId
     * @return object
     */
    function getBusinessRating($businessId)
    {
        $result = DB::table('reviews')
            ->select(
                'rev_cmp_id as business_id',
                DB::raw('ROUND(AVG(rev_rating), 2) as avg_rating'),
                DB::raw('COUNT(*) as total_reviews')
            )
            ->where('rev_cmp_id', $businessId)
            ->groupBy('rev_cmp_id')
            ->first();

        // Return default values if no record exists
        if (!$result) {
            return (object)[
                'business_id'   => $businessId,
                'avg_rating'    => 0,
                'total_reviews' => 0,
            ];
        }

        return $result;
    }
}



if (!function_exists('userType')) {
    function userType(?int $userId)
    {
        $user = UserMaster::find($userId);
        return $user ? $user->um_utm_id : 0;
    }
}
