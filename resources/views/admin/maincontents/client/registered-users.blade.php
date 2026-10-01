@php
    use App\Helpers\Helper;
    $selectedType = $filters['type'] ?? '';
    $selectedStatus = $filters['status'] ?? '';
    $search = $filters['search'] ?? '';
@endphp

<style>
.ru-page{--ink:#17233c;--muted:#71809a;--line:#e3e9f2;--blue:#397cf6}.ru-head{display:flex;justify-content:space-between;gap:20px;align-items:end;margin-bottom:22px}.ru-head h1{font-size:28px;color:var(--ink);margin:0 0 5px}.ru-sub{color:var(--muted);margin:0}.ru-stats{display:grid;grid-template-columns:repeat(3,minmax(130px,1fr));gap:12px;margin-bottom:18px}.ru-stat{background:#fff;border:1px solid var(--line);border-radius:14px;padding:16px 18px}.ru-stat span{display:block;color:var(--muted);font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.06em}.ru-stat strong{display:block;color:var(--ink);font-size:25px;margin-top:4px}.ru-card{background:#fff;border:1px solid var(--line);border-radius:16px;box-shadow:0 8px 24px rgba(33,55,90,.05);overflow:hidden}.ru-filter{display:grid;grid-template-columns:minmax(240px,1fr) 190px 170px auto;gap:10px;padding:18px;border-bottom:1px solid var(--line)}.ru-control{height:44px;border:1px solid #d8e0ec;border-radius:9px;padding:0 13px;color:var(--ink);background:#fff;width:100%}.ru-btn{height:44px;border:0;border-radius:9px;padding:0 19px;background:var(--blue);color:#fff;font-weight:700}.ru-clear{display:inline-flex;align-items:center;justify-content:center;height:44px;padding:0 12px;color:var(--muted)}.ru-table{width:100%;border-collapse:collapse}.ru-table th{padding:13px 16px;background:#f7f9fc;color:#738099;text-transform:uppercase;font-size:11px;letter-spacing:.05em;text-align:left}.ru-table td{padding:15px 16px;border-top:1px solid var(--line);vertical-align:middle;color:#34415a}.ru-person{display:flex;align-items:center;gap:11px}.ru-avatar{width:38px;height:38px;border-radius:11px;background:#eaf1ff;color:#397cf6;display:grid;place-items:center;font-weight:800}.ru-name{font-weight:750;color:var(--ink)}.ru-meta{font-size:12px;color:var(--muted);margin-top:2px}.ru-pill{display:inline-flex;border-radius:999px;padding:5px 9px;font-size:11px;font-weight:800;letter-spacing:.04em;background:#edf3ff;color:#2769df}.ru-pill.visitor{background:#f0f1f5;color:#667085}.ru-pill.active{background:#e9f8f1;color:#13845b}.ru-pill.pending{background:#fff5df;color:#a86700}.ru-pill.inactive{background:#fff0f0;color:#c43b3b}.ru-business{font-size:13px}.ru-business+ .ru-business{margin-top:4px}.ru-actions{white-space:nowrap}.ru-icon{display:inline-grid;place-items:center;width:34px;height:34px;border:1px solid #dce4ef;border-radius:8px;color:#397cf6;margin-right:4px}.ru-empty{text-align:center;padding:55px 20px!important;color:var(--muted)!important}.ru-foot{padding:14px 18px;border-top:1px solid var(--line)}@media(max-width:900px){.ru-filter{grid-template-columns:1fr 1fr}.ru-filter .search{grid-column:1/-1}.ru-table-wrap{overflow:auto}.ru-table{min-width:850px}}@media(max-width:560px){.ru-head{display:block}.ru-stats{grid-template-columns:1fr}.ru-filter{grid-template-columns:1fr}.ru-filter .search{grid-column:auto}}
.ru-icon{background:#fff}.ru-icon.danger{color:#d63745;border-color:#f1cbd0}.ru-action-form{display:inline}.ru-approve{display:inline-flex;align-items:center;gap:6px;height:34px;margin-right:4px;padding:0 11px;border:1px solid #b8e2cf;border-radius:8px;background:#e9f8f1;color:#13845b;font-size:12px;font-weight:750;cursor:pointer}.ru-alert{padding:13px 16px;border-radius:10px;margin-bottom:16px}.ru-alert.success{background:#eaf8f1;color:#14734f;border:1px solid #c8ead9}.ru-alert.danger{background:#fff0f1;color:#a82d38;border:1px solid #f2c7cb}
.ru-head-actions{display:flex;align-items:center;gap:8px}.ru-purge{border:1px solid #dc3545;background:#fff;color:#c92f3d;border-radius:9px;padding:10px 14px;font-weight:750}.ru-purge:disabled{opacity:.55;cursor:not-allowed}.ru-progress{display:none;margin-bottom:16px;padding:16px;border:1px solid #f0c9cd;border-radius:12px;background:#fff7f8;color:#713039}.ru-progress.show{display:block}.ru-progress-bar{height:7px;margin-top:10px;overflow:hidden;border-radius:999px;background:#f1dfe1}.ru-progress-bar span{display:block;width:0;height:100%;background:#dc3545;transition:width .25s}.ru-progress small{display:block;margin-top:8px;color:#8e5960}
.ru-registration{display:flex;align-items:center;gap:10px;margin-bottom:16px;padding:14px 16px;border:1px solid var(--line);border-radius:12px;background:#fff}.ru-registration form{display:flex;align-items:center;gap:8px;margin-left:auto}.ru-registration input[type=text]{width:310px;height:38px;border:1px solid #d8e0ec;border-radius:8px;padding:0 11px}.ru-registration button{height:38px;border:0;border-radius:8px;padding:0 14px;background:var(--blue);color:#fff;font-weight:750}
</style>

<div class="ru-page">
    @if(session('success_message'))<div class="ru-alert success">{{session('success_message')}}</div>@endif
    @if(session('error_message'))<div class="ru-alert danger">{{session('error_message')}}</div>@endif
    <div class="ru-head">
        <div><h1>{{$pageTitle}}</h1><p class="ru-sub">{{$audience === 'members' ? 'Members granted Business Add access by an administrator.' : ($audience === 'guests' ? 'Viewer accounts registered from web or mobile without Business Add access.' : 'View and manage every registered account.')}}</p></div>
        <div class="ru-head-actions"><button type="button" class="ru-purge" id="purge-all"><i class="fa fa-trash me-1"></i> Delete all test data</button><a class="ru-clear" href="{{ route($listRoute) }}"><i class="fa fa-refresh me-2"></i>Refresh</a></div>
    </div>

    @php
        $joinOpen = (bool) $registrationSettings->enabled;
    @endphp
    <div class="ru-registration"><div><strong>Public Join link</strong><div class="ru-meta"><a href="{{route('join.create')}}" target="_blank">{{route('join.create')}}</a> · {{$joinOpen ? 'Accepting registrations' : 'Closed'}}</div></div><form method="post" action="{{route('admin.clients.registered-users.registration-setting')}}">@csrf<input type="hidden" name="public_registration_enabled" value="{{$joinOpen ? 0 : 1}}"><input type="text" name="public_registration_closed_message" value="{{$registrationSettings->closed_message}}" placeholder="Message shown while registration is closed"><button type="submit">{{$joinOpen ? 'Deactivate Join link' : 'Activate Join link'}}</button></form></div>

    <div class="ru-progress" id="purge-progress" role="status" aria-live="polite"><strong id="purge-title">Preparing cleanup…</strong><div class="ru-progress-bar"><span id="purge-bar"></span></div><small id="purge-detail">Do not close this page.</small></div>

    <div class="ru-stats">
        <div class="ru-stat"><span>All users</span><strong>{{ number_format($counts['all']) }}</strong></div>
        <div class="ru-stat"><span>Registered members</span><strong>{{ number_format($counts['owners']) }}</strong></div>
        <div class="ru-stat"><span>Registered guests</span><strong>{{ number_format($counts['visitors']) }}</strong></div>
    </div>

    <section class="ru-card">
        <form class="ru-filter" method="get" action="{{ route($listRoute) }}">
            <input class="ru-control search" name="search" value="{{ $search }}" placeholder="Search name, email, phone or business">
            @if($audience === 'all')<select class="ru-control" name="type">
                <option value="">All user types</option>
                <option value="owner" @selected($selectedType === 'owner')>Business owners</option>
                <option value="visitor" @selected($selectedType === 'visitor')>Visitors</option>
            </select>@endif
            <select class="ru-control" name="status">
                <option value="">All statuses</option>
                <option value="active" @selected($selectedStatus === 'active')>Active</option>
                <option value="pending" @selected($selectedStatus === 'pending')>Pending approval</option>
                <option value="inactive" @selected($selectedStatus === 'inactive')>Inactive</option>
            </select>
            <div><button class="ru-btn" type="submit"><i class="fa fa-search me-1"></i> Filter</button>@if($selectedType || $selectedStatus || $search)<a class="ru-clear" href="{{ route($listRoute) }}">Clear</a>@endif</div>
        </form>

        <div class="ru-table-wrap">
            <table class="ru-table">
                <thead><tr><th>User</th><th>Contact</th><th>Type</th><th>Business</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
                <tbody>
                @forelse($rows as $row)
                    @php
                        $name = trim(($row->userDetail?->ud_first_name ?? '').' '.($row->userDetail?->ud_last_name ?? '')) ?: ($row->um_user_name ?: 'Unnamed user');
                        $initials = collect(explode(' ', $name))->filter()->take(2)->map(fn($part) => strtoupper(substr($part, 0, 1)))->implode('');
                        $membership = $row->membership;
                        $isOwner = $membership && $membership->membership_status === 'active'
                            && $membership->payment_date && (float) $membership->payment_amount > 0
                            && (!$membership->renewal_date || $membership->renewal_date->isToday() || $membership->renewal_date->isFuture());
                        $status = match((int) $row->um_status) { 2 => 'active', 1 => 'pending', default => 'inactive' };
                    @endphp
                    <tr>
                        <td><div class="ru-person"><span class="ru-avatar">{{ $initials ?: 'U' }}</span><div><div class="ru-name">{{ $name }}</div><div class="ru-meta">User #{{ $row->um_id }}</div></div></div></td>
                        <td><div>{{ $row->um_email_id ?: '—' }}</div><div class="ru-meta">{{ $row->um_mobile_no ?: 'No mobile number' }}</div></td>
                        <td><span class="ru-pill {{ $isOwner ? '' : 'visitor' }}">{{ $isOwner ? 'Registered Member' : 'Registered Guest' }}</span></td>
                        <td>
                            @forelse($row->companiesMap as $map)
                                @if($map->companie)<div class="ru-business">{{ $map->companie->cmpd_name }}</div>@endif
                            @empty<span class="ru-meta">No business</span>@endforelse
                        </td>
                        <td><span class="ru-pill {{ $status }}">{{ $status === 'pending' ? 'Pending approval' : ucfirst($status) }}</span></td>
                        <td>{{ $row->um_created_at ? $row->um_created_at->format('d M Y') : '—' }}</td>
                        <td class="ru-actions">
                            <a class="ru-icon" href="{{ url('admin/clients/'.strtolower($row->userType?->utm_name ?: ($isOwner ? 'seller' : 'guest')).'/view_details/'.Helper::encoded($row->um_id)) }}" target="_blank" title="View user"><i class="fa fa-eye"></i></a>
                            @if($status === 'pending')
                                <form class="ru-action-form" method="post" action="{{ route('admin.registrations.approve', $row) }}" onsubmit="return confirm('Approve this member? They will be able to sign in with normal member access.');">
                                    @csrf
                                    <button class="ru-approve" type="submit" title="Approve this member account"><i class="fa fa-check-circle"></i> Approve member</button>
                                </form>
                            @endif
                            <form class="ru-action-form" method="post" action="{{route('admin.clients.registered-users.destroy',$row)}}" onsubmit="return confirm('PERMANENTLY DELETE this user?\n\nThis removes the user, linked businesses, enquiries, memberships, portfolio content and uploaded images. This cannot be undone.');">
                                @csrf @method('delete')
                                <button class="ru-icon danger" type="submit" title="Permanently delete user and all data" aria-label="Permanently delete {{$name}}"><i class="fa fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td class="ru-empty" colspan="7"><i class="fa fa-users fa-2x mb-3 d-block"></i>No registered users match these filters.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($rows->hasPages())<div class="ru-foot">{{ $rows->links() }}</div>@endif
    </section>
</div>

<script>
(() => {
    const button = document.getElementById('purge-all');
    const panel = document.getElementById('purge-progress');
    const title = document.getElementById('purge-title');
    const detail = document.getElementById('purge-detail');
    const bar = document.getElementById('purge-bar');
    if (!button) return;
    const csrf = '{{csrf_token()}}';
    const post = async (url, body) => {
        const response = await fetch(url, {method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(body)});
        const data = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(data.message || 'The cleanup request failed.');
        return data;
    };
    const wait = milliseconds => new Promise(resolve => setTimeout(resolve, milliseconds));
    button.addEventListener('click', async () => {
        const confirmation = window.prompt('This permanently removes ALL users, businesses, enquiries and uploaded business assets.\n\nThe admin account and master settings will remain.\n\nType DELETE ALL to continue:');
        if (confirmation !== 'DELETE ALL') return;
        button.disabled = true;
        panel.classList.add('show');
        try {
            const start = await post('{{route('admin.clients.registered-users.purge.start')}}', {confirmation});
            const originalTotal = start.total_users + start.total_businesses;
            let result = {complete:false,deleted_users:0,deleted_businesses:0,remaining_users:start.total_users,remaining_businesses:start.total_businesses};
            while (!result.complete) {
                result = await post('{{route('admin.clients.registered-users.purge.run')}}', {token:start.token});
                const remaining = result.remaining_users + result.remaining_businesses;
                const percentage = originalTotal ? Math.min(100, Math.round(((originalTotal - remaining) / originalTotal) * 100)) : 100;
                bar.style.width = percentage + '%';
                title.textContent = `Cleaning test data… ${percentage}%`;
                detail.textContent = `${result.deleted_users} users processed · ${result.deleted_businesses} orphan businesses processed · ${remaining} records remaining. Keep this page open.`;
                if (!result.complete) await wait(600);
            }
            bar.style.width = '100%';
            title.textContent = 'Cleanup complete';
            detail.textContent = 'All users, businesses and associated assets were removed. Reloading…';
            await wait(900);
            window.location.href = '{{route($listRoute)}}';
        } catch (error) {
            title.textContent = 'Cleanup paused';
            detail.textContent = error.message;
            button.disabled = false;
        }
    });
})();
</script>
