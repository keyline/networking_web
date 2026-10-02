<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User\UserMaster;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminAccessController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search'));

        $users = UserMaster::query()
            ->with('userDetail')
            ->where('um_status', 2)
            ->whereHas('companies')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('um_email_id', 'like', "%{$search}%")
                        ->orWhere('um_mobile_no', 'like', "%{$search}%")
                        ->orWhere('um_user_name', 'like', "%{$search}%")
                        ->orWhereHas('userDetail', function ($details) use ($search) {
                            $details->where('ud_first_name', 'like', "%{$search}%")
                                ->orWhere('ud_last_name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('um_id')
            ->paginate(20)
            ->withQueryString();

        $assigned = Admin::query()
            ->whereNotNull('user_master_id')
            ->whereIn('user_master_id', UserMaster::query()
                ->where('um_status', 2)
                ->whereHas('companies')
                ->select('um_id'))
            ->get()
            ->keyBy('user_master_id');

        $data = compact('users', 'assigned', 'search');
        return $this->admin_after_login_layout('Admin Access', 'admin-access', $data);
    }

    public function toggle(Request $request, UserMaster $user): RedirectResponse
    {
        $data = $request->validate(['enabled' => ['required', 'boolean']]);
        $existing = Admin::query()
            ->where('user_master_id', $user->um_id)
            ->orWhere('email', $user->um_email_id)
            ->first();

        if (!$data['enabled']) {
            if ($existing && $existing->type !== 'ma') {
                $existing->update([
                    'status' => 0,
                    'login_otp_hash' => null,
                    'login_otp_expires_at' => null,
                    'login_otp_attempts' => 0,
                ]);
            }

            return back()->with('success_message', 'Admin access revoked.');
        }

        abort_unless(
            (int) $user->um_status === 2 && $user->companies()->exists(),
            422,
            'Only an approved member with a linked business can receive admin access.'
        );

        $name = trim(($user->userDetail?->ud_first_name ?? '').' '.($user->userDetail?->ud_last_name ?? ''))
            ?: ($user->um_user_name ?: $user->um_email_id);

        $values = [
            'user_master_id' => $user->um_id,
            'login_id' => $user->um_user_name ?: $user->um_email_id,
            'company_id' => 0,
            'name' => $name,
            'mobile' => $user->um_mobile_no,
            'email' => $user->um_email_id,
            'type' => $existing?->type === 'ma' ? 'ma' : 's',
            'status' => 1,
        ];

        if ($existing) {
            $existing->update($values + ($user->um_password ? ['password' => $user->um_password] : []));
        } else {
            Admin::create($values + ['password' => $user->um_password ?: Hash::make(Str::random(40))]);
        }

        return back()->with('success_message', 'Admin access granted. The user can now sign in with their email and password.');
    }
}
