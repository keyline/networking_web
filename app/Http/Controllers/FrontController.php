<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\OpenAiAuth;
use Illuminate\Http\Request;
use PHPExperts\RESTSpeaker\RESTSpeaker;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use App\Models\Country;
use App\Models\State;
use App\Models\District;
use App\Models\Center;
use App\Models\CenterTimeSlot;
use App\Models\Student;
use App\Models\GeneralSetting;
use App\Models\EmailLog;
use App\Models\Page;
use App\Models\Testimonial;
use App\Models\Banner;
use App\Models\Events\Event;
use App\Models\HomepageSection;
use App\Models\Teacher;
use App\Models\GalleryCategory;
use App\Models\Gallery;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\Enquiry;
use App\Models\UserActivity;
use App\Models\Source;
use App\Models\Notification;
use App\Models\NotificationTemplate;
use App\Models\UserDevice;
use App\Models\User\UserMaster;
use App\Models\Companies\CompaniesDetail;

use Auth;
use Session;
use Helper;
use Hash;
use stripe;

class FrontController extends Controller
{
    /* home */
    public function home()
    {
        $data['generalSetting'] = GeneralSetting::find(1);
        $data['title'] = $data['generalSetting']->site_name ?? 'Net-Works';
        $data['headerNavigation'] = $this->navigationFor('header');
        $data['footerNavigation'] = $this->navigationFor('footer');
        $data['banners'] = Banner::where('status', 1)->orderBy('id')->get()->filter(function ($banner) {
            return $banner->banner_image && is_file(public_path('uploads/banners/'.$banner->banner_image));
        })->values();
        $data['featuredEvents'] = Event::where('status', 'published')->where('ends_at', '>=', now())->orderBy('starts_at')->limit(12)->get();
        $now = now();
        $openEvents = Event::query()
            ->where('status', 'published')
            ->where('starts_at', '>', $now)
            ->where(function ($query) use ($now) {
                $query->whereNull('registration_opens_at')->orWhere('registration_opens_at', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('registration_closes_at')->orWhere('registration_closes_at', '>=', $now);
            })
            ->whereHas('tickets', function ($query) use ($now) {
                $query->where('is_active', true)
                    ->where(function ($sales) use ($now) {
                        $sales->whereNull('sales_start_at')->orWhere('sales_start_at', '<=', $now);
                    })
                    ->where(function ($sales) use ($now) {
                        $sales->whereNull('sales_end_at')->orWhere('sales_end_at', '>=', $now);
                    });
            })
            ->orderBy('starts_at');
        $data['openEventCount'] = (clone $openEvents)->count();
        $data['openEvents'] = $openEvents->limit(3)->get();
        $data['homeSections'] = Schema::hasTable('homepage_sections')
            ? HomepageSection::with(['items' => fn ($query) => $query->where('is_active', true)])
                ->where('is_active', true)->orderBy('sort_order')->orderBy('id')->get()
            : collect();
        $data['homeStats'] = [
            'members' => UserMaster::activeMembers()->count(),
            'businesses' => DB::table('companies_master')->count(),
            'events' => $data['openEventCount'],
        ];
        $data['sponsoredBusinesses'] = CompaniesDetail::with(['companies.categories', 'district'])
            ->where('cmpd_status', 1)
            ->where('cmpd_is_sponsored', 1)
            ->whereNotNull('public_slug')
            ->orderBy('cmpd_name')
            ->limit(12)
            ->get();

        return view('front.cms-home', $data);
    }
    /* home */
    /* page */
    public function page($slug)
    {

        $ac_delete = 'profile-delete';
        $data['generalSetting']             = GeneralSetting::find('1');
        if ($slug != $ac_delete) {
            $data['page']                       = Page::where('page_slug', $slug)
                ->where('status', 1)
                ->where(function ($query) {
                    $query->whereNull('published_at')->orWhere('published_at', '<=', now());
                })->firstOrFail();
            $data['title']                      = $data['page']->meta_title ?: $data['page']->page_name;
            $data['headerNavigation']           = $this->navigationFor('header');
            $data['footerNavigation']           = $this->navigationFor('footer');
            $page_name                          = 'page-content';
            return view('front.page-content', $data);
        } else {

            $page_name                          = 'front.delete_account';
            return view($page_name, $data);
        }
    }
    /* page */

    private function navigationFor(string $location)
    {
        return Page::with(['children' => function ($query) use ($location) {
                $query->where('status', 1)
                    ->whereIn('nav_location', [$location, 'both'])
                    ->where(function ($publish) {
                        $publish->whereNull('published_at')->orWhere('published_at', '<=', now());
                    });
            }])
            ->whereNull('parent_id')
            ->where('status', 1)
            ->whereIn('nav_location', [$location, 'both'])
            ->where(function ($query) {
                $query->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->orderBy('nav_order')
            ->orderBy('page_name')
            ->get();
    }
    public function cron_for_attendance_notification()
    {
        /* throw notification */
        $getTemplate = $this->getNotificationTemplates('ATTENDANCE');
        if ($getTemplate) {
            $getUserFCMTokens   = DB::table('user_devices')
                ->select('fcm_token', DB::raw('MIN(user_id) as user_id'))
                ->where('fcm_token', '!=', '')
                ->groupBy('fcm_token')
                ->get();
            $tokens             = [];
            $type               = 'attendance';
            if ($getUserFCMTokens) {
                foreach ($getUserFCMTokens as $getUserFCMToken) {
                    $employee_id        = $getUserFCMToken->user_id;
                    $response           = $this->sendCommonPushNotification($getUserFCMToken->fcm_token, $getTemplate['title'], $getTemplate['description'], $type);
                    $users[]            = $employee_id;
                    $notificationFields = [
                        'title'             => $getTemplate['title'],
                        'description'       => $getTemplate['description'],
                        'to_users'          => $employee_id,
                        'users'             => json_encode($users),
                        'is_send'           => 1,
                        'send_timestamp'    => date('Y-m-d H:i:s'),
                    ];
                    Notification::insert($notificationFields);
                }
            }
        }
        echo "Attendance notification";
        /* throw notification */
    }
    public function getNotificationTemplates($notificationType)
    {
        $returnArray                    = [];
        $getRandomNotificationTemplate  = NotificationTemplate::select('title', 'description')->where('status', '=', 1)->where('type', '=', $notificationType)->inRandomOrder()->first();
        if ($getRandomNotificationTemplate) {
            $returnArray                = [
                'title'         => $getRandomNotificationTemplate->title,
                'description'   => $getRandomNotificationTemplate->description,
            ];
        }
        return $returnArray;
    }
}
