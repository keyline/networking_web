<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business\BusinessCategoryMaster;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\GeneralSetting;
use App\Models\Companies;
use App\Models\Client;
use App\Models\ClientCheckIn;
use App\Models\ClientOrder;
use App\Models\ClientType;
use App\Models\Companies\CategoryToCompany;
use App\Models\Companies\CompaniesDetail;
use App\Models\Companies\CompaniesMaster;
use App\Models\Companies\UserToCompanies;
use App\Models\Employees;
use App\Models\EmployeeType;
use App\Models\District;
use App\Models\Role;
use App\Models\User\UserDetails;
use App\Models\User\UserMaster;
use App\Models\User\UserTypeMaster;
use App\Models\UserType;
use App\Models\PublicRegistrationSetting;
use App\Models\Country;
use Auth;
use Exception;
use Session;
use Helper;
use Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\MemberDataDeletionService;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->data = array(
            'title'             => 'User : ',
            'controller'        => 'ClientController',
            'controller_route'  => 'clients',
            'primary_key'       => 'um_id',
        );
    }

    public function businessList()
    {
        $data['slug']                   = 'business';
        $data['rows'] = [];
        $data['module']                 = $this->data;
        $title                          = 'Business List';
        $page_name                      = 'client.businessList';
        $data['client_type']            = null;
        $list = CompaniesMaster::with(['details', 'owner', 'owner.userDtl'])
            ->whereHas('details')
            ->whereHas('owner')
            ->get();
        foreach ($list  as $row) {
            if (!is_null($row->owner)) {
                $tagList = [];
                $category = CategoryToCompany::select('ctc_bcm_id')->with('category:bcm_id,name')->where('ctc_cmp_id', $row->cmp_id)->get()->toArray();

                $userDetails = UserDetails::select('ud_first_name')->where('ud_um_id', $row->owner->ucm_um_id)->first();



                if (count($category)) {
                    foreach ($category as $tag) {
                        if (count($tag['category'])) {
                            $tagList[] = $tag['category']['name'];
                        }
                    }
                }





                $data['rows'][] = [
                    'cmp_id' => $row->cmp_id,
                    'name' => $row->details?->cmpd_name ?? '',
                    'public_slug' => $row->details?->ensurePublicSlug(),
                    'description' => $row->details?->cmpd_description ?? '',
                    'email' => $row->details?->cmpd_email ?? '',
                    'phone' => $row->details?->cmpd_phone ?? '',
                    'address1' => $row->details?->cmpd_address1 ?? '',
                    'district' => $row->details?->cmpd_district ?? '',
                    'pincode' => $row->details?->cmpd_pincode ?? '',
                    'status' => $row->details?->cmpd_status ?? 0,
                    'sponsored' => (bool) ($row->details?->cmpd_is_sponsored ?? false),
                    'owner_name' =>  $userDetails->ud_first_name ?? '',
                    'tagCount' => implode(', ', $tagList),
                ];
            }
        }

        return $this->admin_after_login_layout($title, $page_name, $data);
    }

    public function updateBusinessSponsored(Request $request, int $company)
    {
        $data = $request->validate([
            'sponsored' => ['required', 'boolean'],
        ]);

        $business = CompaniesDetail::where('cmpd_cmp_id', $company)->firstOrFail();
        $business->update(['cmpd_is_sponsored' => $data['sponsored']]);

        return back()->with('success_message', $data['sponsored']
            ? 'Business added to the Sponsored list.'
            : 'Business removed from the Sponsored list.');
    }

    public function registeredMembers(Request $request)
    {
        return $this->registeredUsers($request, 'members');
    }

    public function guestUsers(Request $request)
    {
        return $this->registeredUsers($request, 'guests');
    }

    public function registeredUsers(Request $request, string $audience = 'all')
    {
        $filters = $request->validate([
            'type' => ['nullable', Rule::in(['owner', 'visitor'])],
            'status' => ['nullable', Rule::in(['active', 'pending', 'inactive'])],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $query = UserMaster::query()
            ->with([
                'userDetail',
                'userType:utm_id,utm_name',
                'membership',
                'companiesMap.companie:cmpd_id,cmpd_cmp_id,cmpd_name,public_slug,cmpd_status',
            ])
            ->where('um_status', '!=', 3);

        if ($audience === 'members') {
            $query->whereHas('companies');
        } elseif ($audience === 'guests') {
            $query->whereDoesntHave('companies');
        }

        if (($filters['type'] ?? null) === 'owner') {
            $query->whereHas('companies');
        } elseif (($filters['type'] ?? null) === 'visitor') {
            $query->whereDoesntHave('companies');
        }

        if (($filters['status'] ?? null) === 'active') {
            $query->where('um_status', 2);
        } elseif (($filters['status'] ?? null) === 'pending') {
            $query->where('um_status', 1);
        } elseif (($filters['status'] ?? null) === 'inactive') {
            $query->where('um_status', 0);
        }

        if ($search = trim($filters['search'] ?? '')) {
            $query->where(function ($builder) use ($search) {
                $builder->where('um_user_name', 'like', "%{$search}%")
                    ->orWhere('um_email_id', 'like', "%{$search}%")
                    ->orWhere('um_mobile_no', 'like', "%{$search}%")
                    ->orWhereHas('userDetail', function ($details) use ($search) {
                        $details->where('ud_first_name', 'like', "%{$search}%")
                            ->orWhere('ud_last_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('companiesMap.companie', function ($company) use ($search) {
                        $company->where('cmpd_name', 'like', "%{$search}%");
                    });
            });
        }

        $baseUsers = UserMaster::query()->where('um_status', '!=', 3);
        $memberCount = (clone $baseUsers)->whereHas('companies')->count();
        $pageTitle = match ($audience) {
            'members' => 'Registered Members',
            'guests' => 'Guest Users',
            default => 'Registered Users',
        };
        $listRoute = match ($audience) {
            'members' => 'admin.clients.registered-members',
            'guests' => 'admin.clients.guest-users',
            default => 'admin.clients.registered-users',
        };

        $data = [
            'slug' => $audience === 'members' ? 'registered-members' : ($audience === 'guests' ? 'guest-users' : 'registered-users'),
            'module' => $this->data,
            'audience' => $audience,
            'pageTitle' => $pageTitle,
            'listRoute' => $listRoute,
            'filters' => $filters,
            'rows' => $query->orderByDesc('um_id')->paginate(20)->withQueryString(),
            'counts' => [
                'all' => (clone $baseUsers)->count(),
                'owners' => $memberCount,
                'visitors' => (clone $baseUsers)->whereDoesntHave('companies')->count(),
            ],
        ];

        return $this->admin_after_login_layout($pageTitle, 'client.registered-users', $data);
    }

    public function updateRegistrationSetting(Request $request)
    {
        $data = $request->validate([
            'public_registration_enabled' => ['required', 'boolean'],
            'public_registration_closed_message' => ['nullable', 'string', 'max:500'],
        ]);

        PublicRegistrationSetting::current()->update([
            'enabled' => $data['public_registration_enabled'],
            'closed_message' => $data['public_registration_closed_message'] ?? null,
        ]);

        return back()->with('success_message', $data['public_registration_enabled']
            ? 'The public Join link is now active.'
            : 'The public Join link is now closed.');
    }

    public function createRegisteredMember(Request $request)
    {
        $data = [
            'countries' => Country::where('status', 1)->orderBy('name')->get(['id', 'name']),
            'categories' => BusinessCategoryMaster::where('status', 1)->orderBy('name')->get(['bcm_id', 'name']),
            'defaultCountryId' => Country::where('name', 'India')->value('id'),
        ];

        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'first_name' => ['required', 'string', 'max:100'],
                'last_name' => ['nullable', 'string', 'max:100'],
                'email' => ['required', 'email', 'max:255', 'unique:user_master,um_email_id'],
                'mobile' => ['required', 'regex:/^[6-9][0-9]{9}$/', 'unique:user_master,um_mobile_no'],
                'business_name' => ['required', 'string', 'max:255'],
                'category_ids' => ['required', 'array', 'min:1', 'max:5'],
                'category_ids.*' => ['integer', 'distinct', 'exists:business_category_master,bcm_id'],
                'country' => ['required', 'integer', 'exists:countries,id'],
                'state' => ['required', 'integer', Rule::exists('states', 'id')->where(fn ($query) => $query->where('country_id', $request->input('country')))],
                'pincode' => ['required', 'regex:/^[1-9][0-9]{5}$/'],
                'address_line_1' => ['nullable', 'string', 'max:255'],
                'city' => ['nullable', 'string', 'max:100'],
            ]);

            DB::transaction(function () use ($validated) {
                $member = UserMaster::create([
                    'um_utm_id' => 2, 'um_email_id' => strtolower($validated['email']),
                    'um_mobile_no' => $validated['mobile'], 'um_password' => Hash::make(Str::random(40)),
                    'um_status' => 2, 'um_profile_type' => 'O',
                ]);
                $member->update(['um_user_name' => 'EN'.str_pad((string) $member->um_id, 6, '0', STR_PAD_LEFT)]);
                UserDetails::create([
                    'ud_um_id' => $member->um_id, 'ud_first_name' => $validated['first_name'],
                    'ud_last_name' => $validated['last_name'] ?? null, 'ud_whatsapp_no' => $validated['mobile'],
                    'ud_addr_1' => $validated['address_line_1'] ?? null, 'ud_addr_2' => $validated['city'] ?? null,
                    'ud_country_id' => $validated['country'], 'ud_state_id' => $validated['state'],
                    'ud_pincode' => $validated['pincode'],
                ]);
                $company = CompaniesMaster::create([]);
                CompaniesDetail::create([
                    'cmpd_cmp_id' => $company->cmp_id, 'cmpd_name' => $validated['business_name'],
                    'cmpd_description' => 'Business profile created by the super administrator.',
                    'cmpd_email' => strtolower($validated['email']), 'cmpd_phone' => $validated['mobile'],
                    'cmpd_address1' => $validated['address_line_1'] ?? null, 'cmpd_address3' => $validated['city'] ?? null,
                    'cmpd_country' => $validated['country'], 'cmpd_state' => $validated['state'],
                    'cmpd_pincode' => $validated['pincode'], 'cmpd_status' => 1, 'cmpd_is_document_valid' => '1',
                ]);
                $company->users()->attach($member->um_id);
                $company->categories()->attach($validated['category_ids']);
            });

            return redirect()->route('admin.clients.registered-members')->with('success_message', 'Business member created. They can now sign in with mobile OTP.');
        }

        echo $this->admin_after_login_layout('Add Business Member', 'client.create-registered-member', $data);
    }

    public function addMemberBusiness(Request $request, UserMaster $user)
    {
        abort_unless(
            (int) $user->um_status === 2 && $user->companies()->exists(),
            422,
            'Only an approved registered member can have another business added.'
        );

        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($data, $user) {
            $company = CompaniesMaster::create([]);
            CompaniesDetail::create([
                'cmpd_cmp_id' => $company->cmp_id,
                'cmpd_name' => trim($data['business_name']),
                'cmpd_description' => 'Business profile awaiting completion by the member.',
                'cmpd_email' => $user->um_email_id,
                'cmpd_phone' => $user->um_mobile_no,
                'cmpd_status' => 1,
                'cmpd_is_document_valid' => '1',
            ]);
            $company->users()->attach($user->um_id);
        });

        return back()->with('success_message', 'Business added. The member can now complete and publish its profile.');
    }

    public function destroyRegisteredUser(UserMaster $user, MemberDataDeletionService $deletionService)
    {
        $displayName = trim(($user->userDetail?->ud_first_name ?? '').' '.($user->userDetail?->ud_last_name ?? ''))
            ?: ($user->um_user_name ?: 'User #'.$user->um_id);

        try {
            $deletionService->delete($user);
        } catch (\Throwable $exception) {
            Log::error('Permanent member deletion failed.', ['user_id' => $user->um_id, 'exception' => $exception]);
            return back()->with('error_message', 'The user could not be deleted. No partial database deletion was saved.');
        }

        return redirect()->route('admin.clients.registered-users')
            ->with('success_message', $displayName.' and all associated business data were permanently deleted.');
    }

    public function startRegisteredUserPurge(Request $request)
    {
        $request->validate(['confirmation' => ['required', 'in:DELETE ALL']]);
        $token = (string) \Illuminate\Support\Str::uuid();
        $totalUsers = UserMaster::count();
        $totalBusinesses = CompaniesMaster::count();

        $request->session()->put('registered_user_purge', [
            'token' => $token,
            'deleted_users' => 0,
            'deleted_businesses' => 0,
            'started_at' => now()->toIso8601String(),
        ]);

        return response()->json([
            'token' => $token,
            'total_users' => $totalUsers,
            'total_businesses' => $totalBusinesses,
            'message' => 'Cleanup started. Keep this page open until it finishes.',
        ]);
    }

    public function runRegisteredUserPurge(Request $request, MemberDataDeletionService $deletionService)
    {
        $request->validate(['token' => ['required', 'uuid']]);
        $progress = $request->session()->get('registered_user_purge');
        abort_unless($progress && hash_equals($progress['token'], $request->input('token')), 403);

        try {
            $users = UserMaster::orderBy('um_id')->limit(2)->get();
            foreach ($users as $user) {
                $deletionService->delete($user);
                $progress['deleted_users']++;
            }

            if ($users->isEmpty()) {
                $businesses = CompaniesMaster::whereNotExists(function ($query) {
                    $query->selectRaw('1')->from('user_companies_map')
                        ->whereColumn('ucm_cmp_id', 'companies_master.cmp_id');
                })->orderBy('cmp_id')->limit(2)->get();
                foreach ($businesses as $business) {
                    $deletionService->deleteOrphanBusiness($business);
                    $progress['deleted_businesses']++;
                }
            }
        } catch (\Throwable $exception) {
            Log::error('Batched member cleanup failed.', ['progress' => $progress, 'exception' => $exception]);
            return response()->json(['message' => 'Cleanup paused because one record could not be deleted. Check the application log and try again.'], 422);
        }

        $remainingUsers = UserMaster::count();
        $remainingBusinesses = CompaniesMaster::count();
        $complete = $remainingUsers === 0 && $remainingBusinesses === 0;
        $request->session()->put('registered_user_purge', $progress);
        if ($complete) {
            $request->session()->forget('registered_user_purge');
        }

        return response()->json([
            'complete' => $complete,
            'deleted_users' => $progress['deleted_users'],
            'deleted_businesses' => $progress['deleted_businesses'],
            'remaining_users' => $remainingUsers,
            'remaining_businesses' => $remainingBusinesses,
        ]);
    }


    /* edit */
    public function businessEdit(Request $request, $slug = 'business', $id = 0, $uid = null)
    {

        $data['module']                 = $this->data;
        $data['slug']                   = $slug;
        $id                             = $id ? Helper::decoded($id) : $id;
        $title                          = $id ? 'Edit business' : 'Add business';

        $page_name                      = 'client.business-add-edit';
        $data['category']               = BusinessCategoryMaster::where('status', 1)->select('bcm_id', 'name')->orderBy('name')->get();
        $data['countries']              = Country::where('status', 1)->orderBy('name')->get(['id', 'name']);

        $data['row']                    = CompaniesDetail::where('cmpd_cmp_id', $id)->first();

        $data['selectedCategories'] = CategoryToCompany::where('ctc_cmp_id', $id)
            ->pluck('ctc_bcm_id')->map(fn ($categoryId) => (int) $categoryId)->all();
        if ($request->isMethod('post')) {
            $postData = $request->all();

            $validator = Validator::make($postData, [
                'id' => 'required|integer',
                'category_ids' => 'required|array|min:1',
                'category_ids.*' => 'required|integer|distinct|exists:business_category_master,bcm_id',
                'regn_no' => 'nullable|string|max:255',
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'email' => 'nullable|email|max:255',
                'alternate_email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|regex:/^\d{10}$/',
                'whatsapp_no' => 'nullable|string|regex:/^\d{10}$/',
                'address1' => 'nullable|string|max:255',
                'address2' => 'nullable|string|max:255',
                'address3' => 'nullable|string|max:255',
                'estd_year' => 'nullable|integer|digits:4|min:1900|max:' . date('Y'),
                // 'district' => 'nullable|string|max:255',
                'country' => ['required', 'integer', 'exists:countries,id'],
                'state' => [
                    'required',
                    'integer',
                    Rule::exists('states', 'id')->where(fn ($query) => $query->where('country_id', $request->input('country'))),
                ],
                'district' => [
                    'nullable',
                    'integer',
                    Rule::exists('districts', 'id')->where(fn ($query) => $query->where('state_id', $request->input('state'))),
                ],

                'pincode' => 'nullable|string|regex:/^\d{6}$/',
                'logo' => 'nullable|file|image|mimes:jpeg,png,jpg|max:2048',
                'status' => ['required', 'boolean'],
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            } else {

                $details_id = $request->input('id');

                /* profile image */
                $imageFile      = $request->file('logo');
                if ($imageFile != '') {
                    $imageName      = $imageFile->getClientOriginalName();
                    $uploadedFile   = $this->upload_single_file('logo', $imageName, 'company', 'image');
                    if ($uploadedFile['status']) {
                        $logo = $uploadedFile['newFilename'];
                    } else {
                        return redirect()->back()->with(['error_message' => $uploadedFile['message']]);
                    }
                } else {
                    $logo = $data['row']->cmpd_logo ?? null;
                }
                /* profile image */


                $fields =  [
                    'cmpd_company_regn_no' => $postData['regn_no'],
                    'cmpd_name' => $postData['name'],
                    'cmpd_description' => $postData['description'],
                    'cmpd_email' => $postData['email'],
                    'cmpd_alternate_email' => $postData['alternate_email'],
                    'cmpd_phone' => $postData['phone'],
                    'cmpd_whatsapp_no' => $postData['whatsapp_no'],
                    'cmpd_address1' => $postData['address1'],
                    'cmpd_address2' => $postData['address2'],
                    'cmpd_address3' => $postData['address3'],
                    'cmpd_estd_year' => $postData['estd_year'],
                    'cmpd_country' => $postData['country'],
                    'cmpd_state' => $postData['state'],
                    'cmpd_district' => $postData['district'] ?? null,
                    'cmpd_pincode' => $postData['pincode'],
                    'cmpd_logo' => $logo,
                    'cmpd_status' => (int) $postData['status'],
                    'cmpd_updated_at' => date('Y-m-d H:i:s')
                ];

                try {
                    $cmpId = 0;
                    DB::beginTransaction();

                    if ($details_id) {
                        $companie = CompaniesDetail::where('cmpd_id', $details_id)->update($fields);
                        $cmpId = $id;
                    } else {
                        $cmpId = CompaniesMaster::insertGetId(['cmp_created_at' => date('Y-m-d 00:00:00')]);

                        UserToCompanies::insert([
                            'ucm_cmp_id' => $cmpId,
                            'ucm_um_id' => $uid
                        ]);

                        $fields['cmpd_cmp_id'] = $cmpId;
                        $companie = CompaniesDetail::insertGetId($fields);
                    }

                    if ($companie) {
                        if ($details_id) {
                            CategoryToCompany::where('ctc_cmp_id', $cmpId)->delete();
                        }
                        CategoryToCompany::insert(array_map(fn ($categoryId) => [
                            'ctc_bcm_id' => $categoryId,
                            'ctc_cmp_id' => $cmpId,
                            'ctc_created_at' => now(),
                        ], $postData['category_ids']));

                        $ownerId = UserToCompanies::where('ucm_cmp_id', $cmpId)->value('ucm_um_id');
                        if ($ownerId) {
                            UserDetails::where('ud_um_id', $ownerId)->update([
                                'ud_business_category' => (string) $postData['category_ids'][0],
                                'ud_updated_at' => now(),
                            ]);
                        }
                        DB::commit();
                        // Successful update

                    } else {
                        // Update didn't affect any rows (e.g., invalid ID or no changes)
                        throw new Exception("Update failed: no rows affected.");
                    }
                } catch (Exception $e) {
                    DB::rollBack();
                    // Handle exceptions or errors
                    dd($e->getMessage());
                }



                return redirect("admin/" . $this->data['controller_route'] . "/" . $data['slug'] . "/list")->with('success_message', $this->data['title'] . "/" . $data['slug'] . 'data save successful.');
            }
        }
        echo $this->admin_after_login_layout($title, $page_name, $data);
    }
    /* edit */




    /* list */
    public function list($slug)
    {

        $data['slug']                   = $slug;
        $data['module']                 = $this->data;
        $title                          = ucfirst($data['slug']) . ' List';
        $page_name                      = 'client.list';
        // $sessionType                    = Session::get('type');
        $data['client_type']            = UserTypeMaster::select('utm_id', 'utm_name')->where('utm_status', 1)->where('utm_name', '=', $data['slug'])->orderBy('utm_id', 'ASC')->first();

        $UserMaster = UserMaster::with('userDetail');

        // Conditionally add `withCount` for `companiesMap` if `client_type->utm_id` is 2
        if ($data['client_type']->utm_id == 2) {
            $UserMaster->withCount('companiesMap');
            $UserMaster->with('companiesMap.companie:cmpd_id,cmpd_cmp_id,cmpd_name,public_slug,cmpd_status');
        }

        // Apply filters and retrieve results
        $data['rows'] = $UserMaster
            ->where('um_status', '!=', 3)
            ->where('um_utm_id', $data['client_type']->utm_id)
            ->orderByDesc('um_id')
            ->get()->toArray();


        echo $this->admin_after_login_layout($title, $page_name, $data);
    }
    /* list */
    /* add */
    public function add(Request $request, $slug)
    {
        $data['module']             = $this->data;
        $data['slug']               = $slug;
        $data['client_type']        = ClientType::where('status', '!=', 3)->where('slug', '=', $data['slug'])->orderBy('id', 'ASC')->first();
        $data['districts']          = District::select('id', 'name')->where('status', '=', 1)->orderBy('name', 'ASC')->get();

        if ($request->isMethod('post')) {
            $postData = $request->all();
            $rules = [
                'name'                  => 'required',
                'email'                 => 'required',
                'phone'                 => 'required',
                'whatsapp_no'           => 'required',
                'address'               => 'required',
                'district_id'           => 'required',
            ];
            if ($this->validate($request, $rules)) {
                $checkValue = Client::where('name', '=', $postData['name'])->count();
                if ($checkValue <= 0) {
                    $sessionData    = Auth::guard('admin')->user();
                    $prefix         = (($data['client_type']) ? $data['client_type']->prefix : '');
                    /* profile image */
                    $imageFile      = $request->file('profile_image');
                    if ($imageFile != '') {
                        $imageName      = $imageFile->getClientOriginalName();
                        $uploadedFile   = $this->upload_single_file('profile_image', $imageName, 'client', 'image');
                        if ($uploadedFile['status']) {
                            $profile_image = $uploadedFile['newFilename'];
                        } else {
                            return redirect()->back()->with(['error_message' => $uploadedFile['message']]);
                        }
                    } else {
                        $profile_image = '';
                    }
                    /* profile image */
                    /* generate client no  */
                    $getLastEnquiry = Client::orderBy('id', 'DESC')->first();
                    if ($getLastEnquiry) {
                        $sl_no                  = $getLastEnquiry->sl_no;
                        $next_sl_no             = $sl_no + 1;
                        $next_sl_no_string      = str_pad($next_sl_no, 5, 0, STR_PAD_LEFT);
                        $client_no            = $prefix . $next_sl_no_string;
                    } else {
                        $next_sl_no             = 1;
                        $next_sl_no_string      = str_pad($next_sl_no, 5, 0, STR_PAD_LEFT);
                        $client_no            = $prefix . $next_sl_no_string;
                    }
                    /* generate client no */
                    $fields = [
                        'company_id'                => session('company_id'),
                        'client_type_id'            => $data['client_type']->id,
                        'sl_no'                     => $next_sl_no,
                        'client_no'                 => $client_no,
                        'name'                      => $postData['name'],
                        'email'                     => $postData['email'],
                        'alt_email'                 => $postData['alt_email'],
                        'phone'                     => $postData['phone'],
                        'whatsapp_no'               => $postData['whatsapp_no'],
                        'short_bio'                 => $postData['short_bio'],
                        'district_id'               => $postData['district_id'],
                        'address'                   => $postData['address'],
                        'country'                   => $postData['country'],
                        'state'                     => $postData['state'],
                        'city'                      => $postData['city'],
                        'locality'                  => $postData['locality'],
                        'street_no'                 => $postData['street_no'],
                        'zipcode'                   => $postData['zipcode'],
                        'latitude'                  => $postData['latitude'],
                        'longitude'                 => $postData['longitude'],
                        'profile_image'             => $profile_image,
                        'created_by'                => $sessionData->id,
                    ];
                    // Helper::pr($fields);
                    Client::insert($fields);
                    return redirect("admin/" . $this->data['controller_route'] . "/" . $data['slug'] . "/list")->with('success_message', $this->data['title'] . ' Inserted Successfully !!!');
                } else {
                    return redirect()->back()->with('error_message', $this->data['title'] . ' Already Exists !!!');
                }
            } else {
                return redirect()->back()->with('error_message', 'All Fields Required !!!');
            }
        }
        $data['module']                 = $this->data;
        $title                          = ucfirst($data['slug']) . ' Add';
        $page_name                      = 'client.add-edit';
        $data['row']                    = [];
        echo $this->admin_after_login_layout($title, $page_name, $data);
    }
    /* add */
    /* edit */
    public function edit(Request $request, $slug, $id,)
    {
        $data['module']                 = $this->data;
        $data['slug']                   = $slug;
        $id                             = Helper::decoded($id);
        $title                          = ucfirst($data['slug']) . ' Update';
        $page_name                      = 'client.add-edit';
        $data['row']                    = Client::where($this->data['primary_key'], '=', $id)->first();
        $data['client_type']            = ClientType::where('status', '!=', 3)->where('slug', '=', $data['slug'])->orderBy('id', 'ASC')->first();
        $data['districts']              = District::select('id', 'name')->where('status', '=', 1)->orderBy('name', 'ASC')->get();

        if ($request->isMethod('post')) {
            $postData = $request->all();
            $rules = [
                'name'                  => 'required',
                'email'                 => 'required',
                'phone'                 => 'required',
                'whatsapp_no'           => 'required',
                'address'               => 'required',
                'district_id'           => 'required',
            ];
            if ($this->validate($request, $rules)) {
                $checkValue = Client::where('name', '=', $postData['name'])->where('id', '!=', $id)->count();
                if ($checkValue <= 0) {
                    $sessionData = Auth::guard('admin')->user();
                    /* profile image */
                    $imageFile      = $request->file('profile_image');
                    if ($imageFile != '') {
                        $imageName      = $imageFile->getClientOriginalName();
                        $uploadedFile   = $this->upload_single_file('profile_image', $imageName, 'client', 'image');
                        if ($uploadedFile['status']) {
                            $profile_image = $uploadedFile['newFilename'];
                        } else {
                            return redirect()->back()->with(['error_message' => $uploadedFile['message']]);
                        }
                    } else {
                        $profile_image = $data['row']->profile_image;
                    }
                    /* profile image */
                    $fields = [
                        'company_id'                => session('company_id'),
                        'client_type_id'            => $data['client_type']->id,
                        'name'                      => $postData['name'],
                        'email'                     => $postData['email'],
                        'alt_email'                 => $postData['alt_email'],
                        'phone'                     => $postData['phone'],
                        'whatsapp_no'               => $postData['whatsapp_no'],
                        'short_bio'                 => $postData['short_bio'],
                        'district_id'               => $postData['district_id'],
                        'address'                   => $postData['address'],
                        'country'                   => $postData['country'],
                        'state'                     => $postData['state'],
                        'city'                      => $postData['city'],
                        'locality'                  => $postData['locality'],
                        'street_no'                 => $postData['street_no'],
                        'zipcode'                   => $postData['zipcode'],
                        'latitude'                  => $postData['latitude'],
                        'longitude'                 => $postData['longitude'],
                        'profile_image'             => $profile_image,
                        'created_by'                => $sessionData->id,
                        'updated_by'                => $sessionData->id,
                        'updated_at'                => date('Y-m-d H:i:s')
                    ];
                    // Helper::pr($fields);
                    Client::where($this->data['primary_key'], '=', $id)->update($fields);
                    return redirect("admin/" . $this->data['controller_route'] . "/" . $data['slug'] . "/list")->with('success_message', $this->data['title'] . "/" . $data['slug'] . ' Updated Successfully !!!');
                } else {
                    return redirect()->back()->with('error_message', $this->data['title'] . ' Already Exists !!!');
                }
            } else {
                return redirect()->back()->with('error_message', 'All Fields Required !!!');
            }
        }
        echo $this->admin_after_login_layout($title, $page_name, $data);
    }
    /* edit */
    /* delete */
    public function delete(Request $request, $slug, $id)
    {
        $id                             = Helper::decoded($id);
        $fields = [
            'status'             => 3
        ];
        Client::where($this->data['primary_key'], '=', $id)->update($fields);
        return redirect("admin/" . $this->data['controller_route'] . "/" . $slug . "/list")->with('success_message', ucfirst($slug) . ' Deleted Successfully !!!');
    }
    /* delete */
    /* change status */
    public function change_status(Request $request, $slug, $id)
    {
        $id                             = Helper::decoded($id);

        $model                          = UserMaster::select('um_status')->find($id);

        // 2 = registered/active (the only status member login accepts),
        // 0 = deactivated. Activating also completes an unverified (1) signup.
        $status = ((int) $model->um_status === 2) ? 0 : 2;

        $msg = $status === 2 ? 'Activated' : 'Deactivated';

        UserMaster::where($this->data['primary_key'], $id)->update(['um_status' => $status]);

        return redirect("admin/" . $this->data['controller_route'] . "/" . $slug . "/list")->with('success_message', ucfirst($slug) . ' ' . $msg . ' Successfully !!!');
    }
    /* change status */

    public function approveMember(Request $request, UserMaster $user)
    {
        $companyIds = $user->companies()->pluck('companies_master.cmp_id');
        abort_if($companyIds->isEmpty(), 422, 'Only a member with a linked business can be approved here.');
        abort_if((int) $user->um_status === 2, 422, 'This member is already approved.');

        $user->update(['um_status' => 2, 'um_utm_id' => 2]);

        return back()->with('success_message', 'Member approved. You may now review and approve the linked business.');
    }

    public function approveBusiness(Request $request, UserMaster $user, CompaniesMaster $company)
    {
        abort_unless((int) $user->um_status === 2, 422, 'Approve the member before approving the business.');
        abort_unless($user->companies()->where('companies_master.cmp_id', $company->cmp_id)->exists(), 404);

        $business = CompaniesDetail::where('cmpd_cmp_id', $company->cmp_id)->firstOrFail();
        abort_if((int) $business->cmpd_status === 1, 422, 'This business is already approved.');
        $business->update(['cmpd_status' => 1, 'cmpd_is_document_valid' => '1']);

        return back()->with('success_message', 'Business approved and added to the public member directory.');
    }

    // view details
    public function viewDetails($slug, $id)
    {
        // \DB::enableQueryLog();
        // dd($id);
        $id                             = Helper::decoded($id);
        $data['module']                 = $this->data;
        $data['slug']                   = $slug;
        $page_name                      = 'client.view_details';
        // $data['row']                    = Client::where('status', '!=', 3)->where('id', '=', $id)->orderBy('id', 'DESC')->first();
        $data['row']                    =  UserMaster::select('um_id', 'um_utm_id', 'um_user_name', 'um_mobile_no', 'um_email_id', 'um_created_at')
            ->with(['userType:utm_id,utm_name', 'userDetail'])
            ->where('um_id', '=', $id)->first();

        $data['business']               = $data['row']->um_utm_id == 2 ? UserToCompanies::with(['details'])->where('ucm_um_id', $id)->get() : null;

        $firstName = $data['row']?->userDetail?->ud_first_name ?? '';
        $lastName = $data['row']?->userDetail?->ud_last_name ?? '';


        $title                          = $this->data['title'] . ' View Details : ' . $firstName . ' ' . $lastName;




        echo $this->admin_after_login_layout($title, $page_name, $data);
    }
    // view details
    public function viewOrderDetails($slug, $id)
    {
        // dd($id);
        $id                             = Helper::decoded($id);
        $data['module']                 = $this->data;
        $data['slug']                   = $slug;
        $page_name                      = 'client.view_order_details';
        $rows                           = DB::table('client_order_details')
            ->join('client_orders', 'client_orders.id', '=', 'client_order_details.order_id')
            ->join('products', 'products.id', '=', 'client_order_details.product_id')
            ->join('units', 'units.id', '=', 'client_order_details.case_unit')
            ->join('admins as created_by_admins', 'created_by_admins.id', '=', 'client_order_details.created_by')
            ->join('admins as updated_by_admins', 'updated_by_admins.id', '=', 'client_order_details.updated_by')
            ->select(
                'client_order_details.*',
                'client_orders.order_no',
                'products.name as product_name',
                'products.short_desc as product_short_desc',
                'sizes.name as size_name',
                'units.name as unit_name',
                'created_by_admins.name as created_by',
                'updated_by_admins.name as updated_by'
            )
            ->where('client_order_details.order_id', $id)
            ->get();

        $data['row']                    = $rows;
        $data['order_details']    = ClientOrder::where('status', '=', 1)->where('id', '=', $id)->first();
        $data['client_details']    = Client::where('status', '=', 1)->where('id', '=', $data['order_details']->client_id)->first();
        $data['employee_details']    = Employees::where('status', '=', 1)->where('id', '=', $data['order_details']->employee_id)->first();
        $data['employee_types']    = EmployeeType::where('status', '=', 1)->where('id', '=', $data['order_details']->employee_type_id)->first();
        // Helper::pr($data['order_details'])  ;
        $title                          = $this->data['title'] . ' View Order Details : ' . (($data['order_details']) ? $data['order_details']->order_no : '');
        echo $this->admin_after_login_layout($title, $page_name, $data);
    }
    public function clientwiseorderListRecords(Request $request)
    {
        // Retrieve query parameters
        $orderId = $request->query('orderId');
        $name = $request->query('name');

        // Ensure $orderId is present
        if (empty($orderId)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order ID is required.'
            ], 400);
        }

        // Use Laravel's query builder for safe and efficient SQL generation
        $rows = DB::table('client_order_details')
            ->join('client_orders', 'client_orders.id', '=', 'client_order_details.order_id')
            ->join('products', 'products.id', '=', 'client_order_details.product_id')
            ->join('sizes', 'sizes.id', '=', 'client_order_details.size_id')
            ->join('units', 'units.id', '=', 'client_order_details.unit_id')
            ->join('admins as created_by_admins', 'created_by_admins.id', '=', 'client_order_details.created_by')
            ->join('admins as updated_by_admins', 'updated_by_admins.id', '=', 'client_order_details.updated_by')
            ->select(
                'client_order_details.*',
                'client_orders.order_no',
                'products.name as product_name',
                'sizes.name as size_name',
                'units.name as unit_name',
                'created_by_admins.name as created_by',
                'updated_by_admins.name as updated_by'
            )
            ->where('client_order_details.order_id', $orderId)
            ->get();

        // Start building the HTML response
        $html = '<div class="modal-header" style="justify-content: center;">
                    <h6 class="modal-title">Orders Details for <b><u>' . htmlspecialchars($name) . '</u></b></h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container">
                        <div class="table-responsive table-card">
                            <table class="table general_table_style">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Order No</th>
                                        <th>Product Name</th>
                                        <th>Order Unit</th>
                                        <th>Order Qty</th>
                                        <th>Rate</th>
                                        <th>Subtotal</th>
                                        <th>Created Info</th>
                                        <th>Updated Info</th>
                                    </tr>
                                </thead>
                                <tbody>';

        // Add rows to the table
        if ($rows->isNotEmpty()) {
            $sl = 1;
            foreach ($rows as $record) {
                $html .= '<tr>
                            <td>' . $sl++ . '</td>
                            <td>' . htmlspecialchars($record->order_no) . '</td>
                            <td>' . htmlspecialchars($record->product_name) . '</td>
                            <td>' . htmlspecialchars($record->size_name) . ' ' . htmlspecialchars($record->unit_name) . '</td>
                            <td>' . htmlspecialchars($record->qty) . '</td>
                            <td>' . htmlspecialchars($record->rate) . '</td>
                            <td>' . htmlspecialchars($record->subtotal) . '</td>
                            <td>' . htmlspecialchars($record->created_by) . '<br>' . date('M d Y h:i A', strtotime($record->created_at)) . '</td>
                            <td>' . htmlspecialchars($record->updated_by) . '<br>' . date('M d Y h:i A', strtotime($record->updated_at)) . '</td>
                        </tr>';
            }
        } else {
            $html .= '<tr>
                        <td colspan="9" class="text-center">No records found for this order.</td>
                    </tr>';
        }

        $html .= '</tbody>
                        </table>
                    </div>
                </div>
            </div>';

        // Return the HTML response
        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    }
}
