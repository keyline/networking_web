<?php
use App\Models\District;
use App\Models\Employees;
use App\Models\EmployeeType;
use App\Helpers\Helper;
?>
<style>
  .tree ul {
      list-style: none;
      margin: 0;
      padding-left: 20px;
      position: relative;
      display: none; /* Initially hide all child nodes */
  }

  .tree li {
      margin: 0;
      padding: 0 0 10px 20px;
      line-height: 1.5em;
      position: relative;
  }

  .tree li::before, .tree li::after {
      content: '';
      position: absolute;
      left: -10px;
  }

  .tree li::before {
      border-left: 2px solid #ccc;
      top: 0;
      bottom: 50%;
      height: 100%;
      width: 10px;
  }

  .tree li::after {
      border-top: 2px solid #ccc;
      top: 1.5em;
      width: 10px;
      height: 0;
  }

  .tree li:last-child::before {
      height: 50%;
  }

  .node {
      display: inline-block;
      border: 1px solid #ccc;
      border-radius: 4px;
      padding: 5px 10px;
      background: #f9f9f9;
      cursor: pointer;
  }

  .node:hover {
      background: #e0e0e0;
  }

  .join-control {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 24px;
      margin-bottom: 24px;
      padding: 20px 22px;
      border: 1px solid #e3e9f2;
      border-radius: 14px;
      background: #fff;
      box-shadow: 0 8px 24px rgba(33, 55, 90, .05);
  }
  .join-control-copy h2 { margin: 0 0 5px; color: #17233c; font-size: 18px; }
  .join-control-copy p { margin: 0; color: #71809a; }
  .join-control-copy a { font-weight: 700; }
  .join-control-form { display: flex; align-items: center; gap: 12px; }
  .join-control-form input[type="text"] { width: min(420px, 32vw); height: 42px; border: 1px solid #d8e0ec; border-radius: 9px; padding: 0 13px; }
  .join-switch { display: inline-flex; align-items: center; gap: 10px; margin: 0; cursor: pointer; white-space: nowrap; color: #53637d; font-weight: 700; }
  .join-switch input { position: absolute; opacity: 0; pointer-events: none; }
  .join-switch-track { position: relative; width: 50px; height: 28px; border-radius: 999px; background: #c8d0dc; transition: background .2s; }
  .join-switch-track::after { content: ''; position: absolute; top: 4px; left: 4px; width: 20px; height: 20px; border-radius: 50%; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,.18); transition: transform .2s; }
  .join-switch input:checked + .join-switch-track { background: #397cf6; }
  .join-switch input:checked + .join-switch-track::after { transform: translateX(22px); }
  .join-control-form button { height: 42px; border: 0; border-radius: 9px; padding: 0 18px; background: #397cf6; color: #fff; font-weight: 750; }
  .pending-card{margin:0 0 26px;border:1px solid #e3e9f2;border-radius:14px;background:#fff;box-shadow:0 8px 24px rgba(33,55,90,.05);overflow:hidden}.pending-head{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:17px 20px;border-bottom:1px solid #e8edf4}.pending-head h2{margin:0;color:#17233c;font-size:18px}.pending-head p{margin:4px 0 0;color:#71809a;font-size:12px}.pending-count{padding:6px 10px;border-radius:999px;background:#fff5df;color:#a86700;font-size:11px;font-weight:800}.pending-table-wrap{overflow-x:auto}.pending-table{width:100%;border-collapse:collapse}.pending-table th{padding:11px 14px;background:#f7f9fc;color:#738099;text-align:left;text-transform:uppercase;letter-spacing:.05em;font-size:10px}.pending-table td{padding:13px 14px;border-top:1px solid #edf1f5;color:#34415a;vertical-align:middle;font-size:12px}.pending-name{color:#17233c;font-weight:800}.pending-meta{margin-top:3px;color:#7b8798;font-size:11px}.pending-business{font-weight:700}.pending-badge{display:inline-flex;margin-left:5px;padding:4px 7px;border-radius:999px;background:#fff5df;color:#a86700;font-size:9px;font-weight:800;text-transform:uppercase}.pending-actions{display:flex;align-items:center;gap:6px;flex-wrap:wrap}.pending-actions form{margin:0}.pending-button{display:inline-flex;align-items:center;gap:6px;min-height:34px;padding:0 11px;border:1px solid #b8e2cf;border-radius:8px;background:#e9f8f1;color:#13845b;font:inherit;font-size:11px;font-weight:800;cursor:pointer;white-space:nowrap}.pending-button.business{border-color:#cbdaf5;background:#f3f7ff;color:#2769df}.pending-button:disabled{border-color:#e1e5eb;background:#f3f4f6;color:#98a2b3;cursor:not-allowed}.pending-empty{padding:32px;text-align:center;color:#71809a}
  .pending-action-grid{display:grid;gap:8px;min-width:310px}.pending-action-set{display:grid;grid-template-columns:58px repeat(2,minmax(102px,1fr));align-items:center;gap:6px}.pending-action-label{color:#71809a;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.06em}.pending-action-set form{margin:0}.pending-action-set .pending-button{width:100%;justify-content:center}.pending-button.review{justify-content:center;border-color:#cbdaf5;background:#fff;color:#2769df}.pending-button.approve-profile{border-color:#b8e2cf;background:#e9f8f1;color:#13845b}.review-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}.review-card{padding:15px;border:1px solid #e3e9f2;border-radius:10px;background:#f9fbfd}.review-card.full{grid-column:1/-1}.review-card h3{margin:0 0 12px;color:#17233c;font-size:15px}.review-detail{display:grid;grid-template-columns:125px minmax(0,1fr);gap:7px;padding:7px 0;border-top:1px solid #e8edf4;font-size:12px}.review-detail:first-of-type{border-top:0}.review-detail span{color:#71809a}.review-detail strong{color:#34415a;overflow-wrap:anywhere}.review-description{white-space:pre-line;line-height:1.55}.review-approval{display:flex;align-items:center;justify-content:flex-end;gap:8px;flex-wrap:wrap;padding-top:14px;margin-top:14px;border-top:1px solid #e3e9f2}.review-sequence{margin-right:auto;color:#71809a;font-size:11px}.review-business+.review-business{margin-top:14px;padding-top:14px;border-top:1px solid #dfe6ef}@media(max-width:900px){.pending-action-grid{min-width:285px}}@media(max-width:700px){.review-grid{grid-template-columns:1fr}.review-card.full{grid-column:auto}.review-detail{grid-template-columns:1fr;gap:2px}.pending-action-set{grid-template-columns:52px 1fr 1fr}}
  @media(max-width: 900px) { .join-control { align-items: stretch; flex-direction: column; } .join-control-form { align-items: stretch; flex-wrap: wrap; } .join-control-form input[type="text"] { width: 100%; flex-basis: 100%; } }
</style>
<!-- Content -->
    <!-- Page Header -->
    <div class="page-header">
      <div class="row align-items-center">
        <div class="col">
          <h1 class="page-header-title"><?=$page_header?></h1>
        </div>
        <!-- End Col -->
        <!-- <div class="col-auto">
          <a class="btn btn-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#inviteUserModal">
            <i class="bi-person-plus-fill me-1"></i> Invite users
          </a>
        </div> -->
        <!-- End Col -->
      </div>
      <!-- End Row -->
    </div>
    <!-- End Page Header -->
    @php $joinOpen = (bool) $registrationSettings->enabled; @endphp
    @if(session('success_message'))<div class="alert alert-success">{{ session('success_message') }}</div>@endif
    <section class="join-control" aria-labelledby="join-control-title">
      <div class="join-control-copy">
        <h2 id="join-control-title">Public Join registration</h2>
        <p><a href="{{ route('join.create') }}" target="_blank" rel="noopener">Open Join page</a> · Currently <strong>{{ $joinOpen ? 'accepting registrations' : 'closed' }}</strong></p>
      </div>
      <form class="join-control-form" method="post" action="{{ route('admin.dashboard.registration-setting') }}">
        @csrf
        <input type="text" name="public_registration_closed_message" value="{{ $registrationSettings->closed_message }}" placeholder="Message shown when registration is closed" aria-label="Closed registration message">
        <input type="hidden" name="public_registration_enabled" value="0">
        <label class="join-switch">
          <input type="checkbox" name="public_registration_enabled" value="1" @checked($joinOpen) onchange="this.form.submit()">
          <span class="join-switch-track" aria-hidden="true"></span>
          <span>{{ $joinOpen ? 'On' : 'Off' }}</span>
        </label>
        <button type="submit">Save setting</button>
      </form>
    </section>
    <!-- Stats -->
    {{-- <div class="dashboad_top">
        <h4>Today's Report</h4>
      <div class="row">
        <div class="col-sm-6 col-lg-4 mb-2 mb-lg-1">
          <!-- Card -->
          <a class="card card-hover-shadow h-100" href="javascript:void(0);" onclick="openPucnchpop()">
            <div class="card-body">
              <h6 class="card-subtitle">Employee Punched In</h6>
              <div class="row align-items-center gx-2 mb-1">
                <div class="col-12">
                  <h2 class="card-title text-inherit"><?=$totalattandence?></h2>
                </div>
              </div>
              <!-- End Row -->
            </div>
          </a>
          <!-- End Card -->
        </div>
        <div class="col-sm-6 col-lg-4 mb-2 mb-lg-1">
          <!-- Card -->
          <a class="card card-hover-shadow h-100" href="javascript:void(0);" onclick="openOrderpop()">
            <div class="card-body">
              <h6 class="card-subtitle">Total Order</h6>
              <div class="row align-items-center gx-2 mb-1">
                <div class="col-12">
                  <h2 class="card-title text-inherit"><?=$totalorder?></h2>
                </div>
              </div>
              <!-- End Row -->
              <!-- <span class="badge bg-soft-success text-success">
                <i class="bi-graph-up"></i> 1.7%
              </span>
              <span class="text-body fs-6 ms-1">from 29.1%</span> -->
            </div>
          </a>
          <!-- End Card -->
        </div>
        <div class="col-sm-6 col-lg-4 mb-2 mb-lg-1">
          <!-- Card -->
          <a class="card card-hover-shadow h-100" href="javascript:void(0);" onclick="openOrderpop()">
            <div class="card-body">
              <h6 class="card-subtitle">Order Value</h6>
              <div class="row align-items-center gx-2 mb-1">
                <div class="col-12">
                  <h2 class="card-title text-inherit"><i class="fa-solid fa-indian-rupee-sign"></i><?=number_format($totalordervalue,2);?></h2>
                </div>
              </div>
              <!-- End Row -->
              <!-- <span class="badge bg-soft-danger text-danger">
                <i class="bi-graph-down"></i> 4.4%
              </span>
              <span class="text-body fs-6 ms-1">from 61.2%</span> -->
            </div>
          </a>
          <!-- End Card -->
        </div>
      </div>
      <div class="vist_repot_dash">
          <div class="col-md-12"><h3>Visit Report</h3></div>
          <div class="row">
              <div class="col-sm-6 col-lg-3 mb-2 mb-lg-1">
              <!-- Card -->
                <a class="card card-hover-shadow h-100" href="javascript:void(0);" onclick="openCheckingpop(<?=$dealer?>)">
                  <div class="card-body">
                    <h6 class="card-subtitle">Distributor</h6>
                    <div class="row align-items-center gx-2 mb-1">
                      <div class="col-12">
                        <h2 class="card-title text-inherit"><?=$todaydistributor?></h2>
                      </div>
                    </div>
                    <!-- End Row -->
                  </div>
                </a>
                <!-- End Card -->
              </div>
              <div class="col-sm-6 col-lg-3 mb-2 mb-lg-1">
              <!-- Card -->
                <a class="card card-hover-shadow h-100" href="javascript:void(0);" onclick="openCheckingpop(<?=$distributor?>)">
                  <div class="card-body">
                    <h6 class="card-subtitle">Dealer</h6>
                    <div class="row align-items-center gx-2 mb-1">
                      <div class="col-12">
                        <h2 class="card-title text-inherit"><?=$todaydealer?></h2>
                      </div>
                    </div>
                    <!-- End Row -->
                  </div>
                </a>
                <!-- End Card -->
              </div>
              <div class="col-sm-6 col-lg-3 mb-2 mb-lg-1">
              <!-- Card -->
                <a class="card card-hover-shadow h-100" href="javascript:void(0);" onclick="openCheckingpop(<?=$retailer?>)">
                  <div class="card-body">
                    <h6 class="card-subtitle">Retailer</h6>
                    <div class="row align-items-center gx-2 mb-1">
                      <div class="col-12">
                        <h2 class="card-title text-inherit"><?=$todayretailer?></h2>
                      </div>
                    </div>
                    <!-- End Row -->
                  </div>
                </a>
                <!-- End Card -->
              </div>
              <div class="col-sm-6 col-lg-3 mb-2 mb-lg-1">
              <!-- Card -->
                <a class="card card-hover-shadow h-100" href="javascript:void(0);" onclick="openCheckingpop(<?=$farmer?>)">
                  <div class="card-body">
                    <h6 class="card-subtitle">Farmar</h6>
                    <div class="row align-items-center gx-2 mb-1">
                      <div class="col-12">
                        <h2 class="card-title text-inherit"><?=$todayfarmer?></h2>
                      </div>
                    </div>
                    <!-- End Row -->
                  </div>
                </a>
                <!-- End Card -->
              </div>
          </div>
      </div>
    </div> --}}
    <div class="dasbbaord_total">
        {{-- <h3>Total Client</h3> --}}
        <div class="row">
          <div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
            <!-- Card -->
            <a class="card card-hover-shadow h-100" target="_blank" href="<?=url('admin/clients/business/list')?>">
              <div class="card-body">
                <h6 class="card-subtitle">Business</h6>
                <div class="row align-items-center gx-2 mb-1">
                  <div class="col-12">
                    <h2 class="card-title text-inherit"><?=$totalBusiness?></h2>
                  </div>
                </div>
                <!-- End Row -->
              </div>
            </a>
            <!-- End Card -->
          </div>

          <div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
            <!-- Card -->
            <a class="card card-hover-shadow h-100" target="_blank" href="<?=url('admin/clients/buyer/list')?>">
              <div class="card-body">
                <h6 class="card-subtitle">Guests</h6>
                <div class="row align-items-center gx-2 mb-1">
                  <div class="col-12">
                    <h2 class="card-title text-inherit"><?=$totalGuests?></h2>
                  </div>
                </div>
                <!-- End Row -->
              </div>
            </a>
            <!-- End Card -->
          </div>
          <div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
            <!-- Card -->
            <a class="card card-hover-shadow h-100" target="_blank" href="<?=url('admin/clients/seller/list')?>">
              <div class="card-body">
                <h6 class="card-subtitle">Members</h6>
                <div class="row align-items-center gx-2 mb-1">
                  <div class="col-12">
                    <h2 class="card-title text-inherit" data-stat="active-members"><?=$totalMembers?></h2>
                  </div>
                </div>
                <!-- End Row -->
              </div>
            </a>
            <!-- End Card -->
          </div>
          <div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
            <!-- Card -->
            <a class="card card-hover-shadow h-100" target="_blank" href="#">
              <div class="card-body">
                <h6 class="card-subtitle">Business Types</h6>
                <div class="row align-items-center gx-2 mb-1">
                  <div class="col-12">
                    <h2 class="card-title text-inherit"><?=$totalTypes?></h2>
                  </div>
                </div>
                <!-- End Row -->
              </div>
            </a>
            <!-- End Card -->
          </div>
        </div>
    </div>
    <!-- End Stats -->
    <section class="pending-card" aria-labelledby="pending-registration-title">
      <div class="pending-head"><div><h2 id="pending-registration-title">Pending member requests</h2><p>Approve the member first, then approve each linked business.</p></div><span class="pending-count">{{ $pendingRegistrations->count() }} pending</span></div>
      @if($pendingRegistrations->isEmpty())
        <div class="pending-empty"><i class="fa fa-check-circle me-1"></i> No member or business approvals are pending.</div>
      @else
        <div class="pending-table-wrap"><table class="pending-table"><thead><tr><th>Member</th><th>Business</th><th>Member status</th><th>Actions</th></tr></thead><tbody>
        @foreach($pendingRegistrations as $pendingMember)
          @php
            $pendingDetail = $pendingMember->userDetail;
            $pendingName = trim(($pendingDetail?->ud_salutation ? $pendingDetail->ud_salutation.' ' : '').($pendingDetail?->ud_first_name ?? '').' '.($pendingDetail?->ud_last_name ?? '')) ?: ($pendingMember->um_user_name ?: 'Member #'.$pendingMember->um_id);
            $memberApproved = (int) $pendingMember->um_status === 2;
            $pendingBusinesses = $pendingMember->companies->filter(fn($company) => $company->details && (int) $company->details->cmpd_status !== 1);
          @endphp
          <tr>
            <td><div class="pending-name">{{ $pendingName }}</div><div class="pending-meta">{{ $pendingMember->um_email_id }} · {{ $pendingMember->um_mobile_no }}</div></td>
            <td>@foreach($pendingMember->companies as $company)@if($company->details)<div class="pending-business">{{ $company->details->cmpd_name }} <span class="pending-badge">{{ (int) $company->details->cmpd_status === 1 ? 'Approved' : 'Pending' }}</span></div>@endif @endforeach</td>
            <td><span class="pending-badge">{{ $memberApproved ? 'Approved' : 'Pending' }}</span></td>
            <td>
              <div class="pending-action-grid">
                <div class="pending-action-set">
                  <span class="pending-action-label">Profile</span>
                  <button class="pending-button review" type="button" data-bs-toggle="modal" data-bs-target="#pending-profile-{{ $pendingMember->um_id }}"><i class="fa fa-eye"></i> View</button>
                  @if(!$memberApproved)
                    <form method="post" action="{{ route('admin.registrations.approve-member', $pendingMember) }}" onsubmit="return confirm('Approve this member profile? Business approval will remain separate.');">@csrf<button class="pending-button approve-profile" type="submit"><i class="fa fa-user-check"></i> Approve</button></form>
                  @else
                    <button class="pending-button" type="button" disabled><i class="fa fa-check"></i> Approved</button>
                  @endif
                </div>
                @foreach($pendingMember->companies as $company)
                  @php $business = $company->details; $businessApproved = $business && (int) $business->cmpd_status === 1; @endphp
                  @if($business)
                    <div class="pending-action-set">
                      <span class="pending-action-label">Business</span>
                      <button class="pending-button review business" type="button" data-bs-toggle="modal" data-bs-target="#pending-business-{{ $pendingMember->um_id }}-{{ $company->cmp_id }}"><i class="fa fa-eye"></i> View</button>
                      @if($businessApproved)
                        <button class="pending-button business" type="button" disabled><i class="fa fa-check"></i> Approved</button>
                      @elseif($memberApproved)
                        <form method="post" action="{{ route('admin.registrations.approve-business', [$pendingMember, $company]) }}" onsubmit="return confirm('Approve {{ addslashes($business->cmpd_name) }} for the public directory?');">@csrf<button class="pending-button business" type="submit"><i class="fa fa-building-circle-check"></i> Approve</button></form>
                      @else
                        <button class="pending-button business" type="button" disabled title="Approve the profile first"><i class="fa fa-lock"></i> Approve</button>
                      @endif
                    </div>
                  @endif
                @endforeach
              </div>
            </td>
          </tr>
        @endforeach
        </tbody></table></div>
        @foreach($pendingRegistrations as $pendingMember)
          @php
            $pendingDetail = $pendingMember->userDetail;
            $pendingName = trim(($pendingDetail?->ud_salutation ? $pendingDetail->ud_salutation.' ' : '').($pendingDetail?->ud_first_name ?? '').' '.($pendingDetail?->ud_last_name ?? '')) ?: ($pendingMember->um_user_name ?: 'Member #'.$pendingMember->um_id);
            $memberApproved = (int) $pendingMember->um_status === 2;
          @endphp
          <div class="modal fade" id="pending-profile-{{ $pendingMember->um_id }}" tabindex="-1" aria-labelledby="pending-profile-title-{{ $pendingMember->um_id }}" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content"><div class="modal-header"><div><h2 class="modal-title fs-5" id="pending-profile-title-{{ $pendingMember->um_id }}">View member profile</h2><div class="pending-meta">Registration {{ $pendingMember->um_user_name ?: '#'.$pendingMember->um_id }}</div></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><div class="review-grid">
            <section class="review-card"><h3>Profile details</h3><div class="review-detail"><span>Name</span><strong>{{ $pendingName }}</strong></div><div class="review-detail"><span>Email</span><strong>{{ $pendingMember->um_email_id ?: '—' }}</strong></div><div class="review-detail"><span>Mobile</span><strong>{{ $pendingMember->um_mobile_no ?: '—' }}</strong></div><div class="review-detail"><span>WhatsApp</span><strong>{{ $pendingDetail?->ud_whatsapp_no ?: '—' }}</strong></div><div class="review-detail"><span>Joined</span><strong>{{ $pendingMember->um_created_at?->format('d M Y, h:i A') ?: '—' }}</strong></div></section>
            <section class="review-card"><h3>Profile status</h3><div class="review-detail"><span>Member ID</span><strong>{{ $pendingMember->um_user_name ?: '#'.$pendingMember->um_id }}</strong></div><div class="review-detail"><span>Status</span><strong>{{ $memberApproved ? 'Approved' : 'Pending approval' }}</strong></div><div class="review-detail"><span>Linked businesses</span><strong>{{ $pendingMember->companies->count() }}</strong></div><div class="review-detail"><span>Address</span><strong>{{ collect([$pendingDetail?->ud_addr_1, $pendingDetail?->ud_addr_2, $pendingDetail?->ud_pincode])->filter()->implode(', ') ?: '—' }}</strong></div></section>
          </div></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>@if(!$memberApproved)<form method="post" action="{{ route('admin.registrations.approve-member', $pendingMember) }}" onsubmit="return confirm('Approve this member profile? Business approval will remain separate.');">@csrf<button class="pending-button approve-profile" type="submit"><i class="fa fa-user-check"></i> Approve profile</button></form>@else<button class="pending-button" type="button" disabled><i class="fa fa-check"></i> Profile approved</button>@endif</div></div></div></div>

          @foreach($pendingMember->companies as $company)
            @php
              $business = $company->details;
              $businessApproved = $business && (int) $business->cmpd_status === 1;
              $address = collect([$business?->cmpd_address1, $business?->cmpd_address2, $business?->cmpd_address3, $business?->state?->name, $business?->country?->name, $business?->cmpd_pincode])->filter()->implode(', ');
            @endphp
            @if($business)
              <div class="modal fade" id="pending-business-{{ $pendingMember->um_id }}-{{ $company->cmp_id }}" tabindex="-1" aria-labelledby="pending-business-title-{{ $pendingMember->um_id }}-{{ $company->cmp_id }}" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content"><div class="modal-header"><div><h2 class="modal-title fs-5" id="pending-business-title-{{ $pendingMember->um_id }}-{{ $company->cmp_id }}">View business</h2><div class="pending-meta">{{ $business->cmpd_name ?: 'Unnamed business' }}</div></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><section class="review-card"><h3>Business details</h3><div class="review-detail"><span>Business name</span><strong>{{ $business->cmpd_name ?: 'Unnamed business' }}</strong></div><div class="review-detail"><span>Owner</span><strong>{{ $pendingName }}</strong></div><div class="review-detail"><span>Categories</span><strong>{{ $company->categories->pluck('name')->implode(', ') ?: '—' }}</strong></div><div class="review-detail"><span>Email</span><strong>{{ $business->cmpd_email ?: '—' }}</strong></div><div class="review-detail"><span>Phone</span><strong>{{ $business->cmpd_phone ?: '—' }}</strong></div><div class="review-detail"><span>WhatsApp</span><strong>{{ $business->cmpd_whatsapp_no ?: '—' }}</strong></div><div class="review-detail"><span>Address</span><strong>{{ $address ?: '—' }}</strong></div><div class="review-detail"><span>Registration no.</span><strong>{{ $business->cmpd_company_regn_no ?: '—' }}</strong></div><div class="review-detail"><span>GST / PAN</span><strong>{{ collect([$business->cmpd_gst_no, $business->cmpd_pan_no])->filter()->implode(' / ') ?: '—' }}</strong></div><div class="review-detail"><span>Description</span><strong class="review-description">{{ $business->cmpd_description ?: '—' }}</strong></div><div class="review-detail"><span>Status</span><strong>{{ $businessApproved ? 'Approved' : 'Pending approval' }}</strong></div></section></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>@if($businessApproved)<button class="pending-button business" type="button" disabled><i class="fa fa-check"></i> Business approved</button>@elseif($memberApproved)<form method="post" action="{{ route('admin.registrations.approve-business', [$pendingMember, $company]) }}" onsubmit="return confirm('Approve {{ addslashes($business->cmpd_name) }} for the public directory?');">@csrf<button class="pending-button business" type="submit"><i class="fa fa-building-circle-check"></i> Approve business</button></form>@else<button class="pending-button business" type="button" disabled title="Approve the profile first"><i class="fa fa-lock"></i> Approve profile first</button>@endif</div></div></div></div>
            @endif
          @endforeach
        @endforeach
      @endif
    </section>
    {{-- <div class="row">
      <div class="col-sm-12 col-lg-12 mb-3 mb-lg-5">
        <div class="card">
          <div class="card-header"><h5>Employee Tree</h5></div>
          <div class="card-body">
            <?php
            $districtIds = [];
            $emps = Employees::select('assign_district')->where('status', '!=', 3)->get();
            if($emps){
              foreach($emps as $emp){
                $assign_districts = json_decode($emp->assign_district);
                if(!empty($assign_districts)){
                  for($d=0;$d<count($assign_districts);$d++){
                    if(!in_array($assign_districts[$d], $districtIds)){
                      $districtIds[] = $assign_districts[$d];
                    }
                  }
                }
              }
            }
            ?>
            <div id="tree-view">
              <ul class="tree">
                  <li>
                      <span class="node">WEST BENGAL</span>
                      <ul>
                        <?php
                        ini_set('memory_limit', '512M'); // Or '512M', depending on your needs
                        $level1_emp_type = EmployeeType::select('prefix')->where('status', '=', 1)->where('level', '=', 1)->first();
                        $level2_emp_type = EmployeeType::select('prefix')->where('status', '=', 1)->where('level', '=', 2)->first();
                        $level3_emp_type = EmployeeType::select('prefix')->where('status', '=', 1)->where('level', '=', 3)->first();
                        $level4_emp_type = EmployeeType::select('prefix')->where('status', '=', 1)->where('level', '=', 4)->first();
                        $level5_emp_type = EmployeeType::select('prefix')->where('status', '=', 1)->where('level', '=', 5)->first();
                        $level6_emp_type = EmployeeType::select('prefix')->where('status', '=', 1)->where('level', '=', 6)->first();
                        $level7_emp_type = EmployeeType::select('prefix')->where('status', '=', 1)->where('level', '=', 7)->first();
                        if(!empty($districtIds)){ for($d=0;$d<count($districtIds);$d++){
                          $getDistrict = District::select('id', 'name')->where('id', '=', $districtIds[$d])->first();
                        ?>
                          <li>
                              <span class="node"><?=(($getDistrict)?$getDistrict->name:'')?></span>
                              <ul>
                                <?php
                                $getEmps1 = Employees::select('name')->where('employee_type_id', '=', 1)->where('status', '=', 1)->whereJsonContains('assign_district', $districtIds[$d])->get();
                                if($getEmps1){ foreach($getEmps1 as $getEmp1){
                                ?>
                                  <li>
                                    <span class="node" style="<?=(($getEmp1)?(($getEmp1->name != 'VACANT')?'':'color:red;'):'color:red;')?>"><?=(($getEmp1)?$getEmp1->name:'-VACANT-')?> (<?=(($level1_emp_type)?$level1_emp_type->prefix:'')?>)</span>
                                    <ul>
                                      <?php
                                      $getEmps2 = Employees::select('name')->where('employee_type_id', '=', 2)->where('status', '=', 1)->whereJsonContains('assign_district', $districtIds[$d])->get();
                                      if($getEmps2){ foreach($getEmps2 as $getEmp2){
                                      ?>
                                        <li>
                                            <span class="node" style="<?=(($getEmp2)?(($getEmp2->name != 'VACANT')?'':'color:red;'):'color:red;')?>"><?=(($getEmp2)?$getEmp2->name:'-VACANT-')?> (<?=(($level2_emp_type)?$level2_emp_type->prefix:'')?>)</span>
                                            <ul>
                                              <?php
                                              $getEmps3 = Employees::select('name')->where('employee_type_id', '=', 3)->where('status', '=', 1)->whereJsonContains('assign_district', $districtIds[$d])->get();
                                              if($getEmps3){ foreach($getEmps3 as $getEmp3){
                                              ?>
                                                <li>
                                                    <span class="node" style="<?=(($getEmp3)?(($getEmp3->name != 'VACANT')?'':'color:red;'):'color:red;')?>"><?=(($getEmp3)?$getEmp3->name:'-VACANT-')?> (<?=(($level3_emp_type)?$level3_emp_type->prefix:'')?>)</span>
                                                    <ul>
                                                      <?php
                                                      $getEmps4 = Employees::select('name')->where('employee_type_id', '=', 4)->where('status', '=', 1)->whereJsonContains('assign_district', $districtIds[$d])->get();
                                                      if($getEmps4){ foreach($getEmps4 as $getEmp4){
                                                      ?>
                                                        <li>
                                                            <span class="node" style="<?=(($getEmp4)?(($getEmp4->name != 'VACANT')?'':'color:red;'):'color:red;')?>"><?=(($getEmp4)?$getEmp4->name:'-VACANT-')?> (<?=(($level4_emp_type)?$level4_emp_type->prefix:'')?>)</span>
                                                            <ul>
                                                              <?php
                                                              $getEmps5 = Employees::select('name')->where('employee_type_id', '=', 5)->where('status', '=', 1)->whereJsonContains('assign_district', $districtIds[$d])->get();
                                                              if($getEmps5){ foreach($getEmps5 as $getEmp5){
                                                              ?>
                                                                <li>
                                                                    <span class="node" style="<?=(($getEmp5)?(($getEmp5->name != 'VACANT')?'':'color:red;'):'color:red;')?>"><?=(($getEmp5)?$getEmp5->name:'-VACANT-')?> (<?=(($level5_emp_type)?$level5_emp_type->prefix:'')?>)</span>
                                                                    <ul>
                                                                      <?php
                                                                      $getEmps6 = Employees::select('name')->where('employee_type_id', '=', 6)->where('status', '=', 1)->whereJsonContains('assign_district', $districtIds[$d])->get();
                                                                      if($getEmps6){ foreach($getEmps6 as $getEmp6){
                                                                      ?>
                                                                        <li>
                                                                            <span class="node" style="<?=(($getEmp6)?(($getEmp6->name != 'VACANT')?'':'color:red;'):'color:red;')?>"><?=(($getEmp6)?$getEmp6->name:'-VACANT-')?> (<?=(($level6_emp_type)?$level6_emp_type->prefix:'')?>)</span>
                                                                            <ul>
                                                                              <?php
                                                                              $getEmps7 = Employees::select('name')->where('employee_type_id', '=', 7)->where('status', '=', 1)->whereJsonContains('assign_district', $districtIds[$d])->get();
                                                                              if($getEmps7){ foreach($getEmps7 as $getEmp7){
                                                                              ?>
                                                                                <li>
                                                                                    <span class="node" style="<?=(($getEmp7)?(($getEmp7->name != 'VACANT')?'':'color:red;'):'color:red;')?>"><?=(($getEmp7)?$getEmp7->name:'-VACANT-')?> (<?=(($level7_emp_type)?$level7_emp_type->prefix:'')?>)</span>
                                                                                </li>
                                                                              <?php } }?>
                                                                            </ul>
                                                                        </li>
                                                                      <?php } }?>
                                                                    </ul>
                                                                </li>
                                                              <?php } }?>
                                                            </ul>
                                                        </li>
                                                      <?php } }?>
                                                    </ul>
                                                </li>
                                              <?php } }?>
                                            </ul>
                                        </li>
                                      <?php } }?>
                                    </ul>
                                  </li>
                                <?php } }?>
                              </ul>
                          </li>
                        <?php } }?>
                      </ul>
                  </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div> --}}
<!-- End Content -->

<!-- Modal -->
<div class="modal fade" id="pucnchpop" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">

</div>

<div class="modal fade" id="orderpop" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">

</div>

<div class="modal fade" id="checkingpop" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">

</div>

<script>
  document.querySelectorAll('.node').forEach(node => {
      node.addEventListener('click', function (e) {
          e.stopPropagation(); // Prevent event from bubbling up

          const parentLi = this.parentElement;
          const childUl = parentLi.querySelector('ul');

          if (childUl) {
              // Toggle visibility
              if (childUl.style.display === 'none' || childUl.style.display === '') {
                  childUl.style.display = 'block'; // Expand
              } else {
                  childUl.style.display = 'none'; // Collapse
              }
          }
      });
  });
</script>

<script>
  function openPucnchpop(){
    // $('#pucnchpop').modal('show');
    $.ajax({
        url: '<?php echo url('admin/today-attandence-details'); ?>',
        type: 'POST',
        data: {
            "_token": "{{ csrf_token() }}",
        },
        dataType: 'html',
        success: function(response) {
          $('#pucnchpop').html(response);
          $('#pucnchpop').modal('show');
        }
    });
  }

  function openOrderpop(){
    $.ajax({
        url: '<?php echo url('admin/today-order-details'); ?>',
        type: 'POST',
        data: {
            "_token": "{{ csrf_token() }}",
        },
        dataType: 'html',
        success: function(response) {
          $('#orderpop').html(response);
          $('#orderpop').modal('show');
        }
    });
  }

  function openCheckingpop(clienttype){
    $.ajax({
        url: '<?php echo url('admin/today-client-details'); ?>',
        type: 'POST',
        data: {
            "_token": "{{ csrf_token() }}",
            clienttype: clienttype,
        },
        dataType: 'html',
        success: function(response) {
          $('#orderpop').html(response);
          $('#orderpop').modal('show');
        }
    });
  }

</script>
