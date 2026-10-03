<?php
use App\Models\ClientOrder;
use Illuminate\Support\Facades\Route;
$routeName = Route::current();
$pageName = explode('/', $routeName->uri());
$pageSegment = $pageName[1];
$pageFunction = count($pageName) > 2 ? $pageName[2] : '';

$parameters = $routeName->parameters();
$memberDelegatedAdmin = session('admin_login_source') === 'member_dashboard';
// dd($parameters);
// if(!empty($parameters)){
//   if (array_key_exists("id1",$parameters)){
//     $pId1 = Helper::decoded($parameters['id1']);
//   } else {
//     $pId1 = Helper::decoded($parameters['id1']);
//   }
//   if(count($parameters) > 1){
//     $pId2 = Helper::decoded($parameters['id2']);
//   }
// }

$slug = '';
if ($pageSegment == 'clients' && !empty($routeName->parameters())) {
    $slug = $routeName->parameters()['slug'];
} else {
    $slug = 'business';
}

if ($pageSegment == 'employee-details') {
    $slug = $routeName->parameters()['slug'];
}
if ($pageSegment == 'orders') {
    $slug = $routeName->parameters()['id'];
}
// echo $slug;die;
?>
<div class="navbar-vertical-container">
    <div class="navbar-vertical-footer-offset">
        <!-- Logo -->
        <a class="navbar-brand" href="<?= url('/') ?>" aria-label="Home">
            <img class="navbar-brand-logo" src="<?= env('UPLOADS_URL') . $generalSetting->site_logo ?>"
                alt="<?= $generalSetting->site_name ?>" data-hs-theme-appearance="default" style="margin: 0 auto;">
            <img class="navbar-brand-logo" src="<?= env('UPLOADS_URL') . $generalSetting->site_logo ?>"
                alt="<?= $generalSetting->site_name ?>" data-hs-theme-appearance="dark" style="margin: 0 auto;">
            <img class="navbar-brand-logo-mini" src="<?= env('UPLOADS_URL') . $generalSetting->site_logo ?>"
                alt="<?= $generalSetting->site_name ?>" data-hs-theme-appearance="default" style="margin: 0 auto;">
            <img class="navbar-brand-logo-mini" src="<?= env('UPLOADS_URL') . $generalSetting->site_logo ?>"
                alt="<?= $generalSetting->site_name ?>" data-hs-theme-appearance="dark" style="margin: 0 auto;">
        </a>
        <!-- End Logo -->
        <!-- Navbar Vertical Toggle -->
        <button type="button" class="js-navbar-vertical-aside-toggle-invoker navbar-aside-toggler">
            <i class="bi-arrow-bar-left navbar-toggler-short-align"
                data-bs-template='<div class="tooltip d-none d-md-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
                data-bs-toggle="tooltip" data-bs-placement="right" title="Collapse"></i>
            <i class="bi-arrow-bar-right navbar-toggler-full-align"
                data-bs-template='<div class="tooltip d-none d-md-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
                data-bs-toggle="tooltip" data-bs-placement="right" title="Expand"></i>
        </button>
        <!-- End Navbar Vertical Toggle -->
        <!-- Content -->
        <div class="navbar-vertical-content">
            <div id="navbarVerticalMenu" class="nav nav-pills nav-vertical card-navbar-nav">
                <!-- dashboard -->
                <div class="nav-item">
                    <a class="nav-link <?= $pageSegment == 'dashboard' ? 'active' : '' ?>"
                        href="<?= url('admin/dashboard') ?>" data-placement="left">
                        <i class="fa fa-home nav-icon"></i>
                        <span class="nav-link-title">Dashboard</span>
                    </a>
                </div>
                <!-- End dashboard -->
                <!-- Access & Permission -->
                <!-- <div class="nav-item">
            <a class="nav-link dropdown-toggle active <?= $pageSegment == 'modules' || $pageSegment == 'role' || $pageSegment == 'sub-user' || $pageSegment == 'companies' ? '' : 'collapsed' ?>" href="#navbarVerticalMenuAccess" role="button" data-bs-toggle="collapse" data-bs-target="#navbarVerticalMenuAccess" aria-expanded="<?= $pageSegment == 'modules' || $pageSegment == 'role' || $pageSegment == 'sub-user' || $pageSegment == 'companies' ? 'true' : 'false' ?>" aria-controls="navbarVerticalMenuAccess">
              <i class="fa fa-database nav-icon"></i>
              <span class="nav-link-title">Access & Permission</span>
            </a>
            <div id="navbarVerticalMenuAccess" class="nav-collapse collapse <?= $pageSegment == 'modules' || $pageSegment == 'role' || $pageSegment == 'sub-user' || $pageSegment == 'companies' ? 'show' : '' ?>" data-bs-parent="#navbarVerticalMenu">
              <a class="nav-link <?= $pageSegment == 'modules' ? 'active' : '' ?>" href="<?= url('admin/modules/list') ?>">Modules</a>
              <a class="nav-link <?= $pageSegment == 'role' ? 'active' : '' ?>" href="<?= url('admin/role/list') ?>">Roles</a>
              <a class="nav-link <?= $pageSegment == 'sub-user' ? 'active' : '' ?>" href="<?= url('admin/sub-user/list') ?>">Sub Users</a>
              <?php if($admin->company_id == 0){ ?>
              <a class="nav-link <?= $pageSegment == 'companies' ? 'active' : '' ?>" href="<?= url('admin/companies/list') ?>">Companies</a>
              <?php } ?>
            </div>
          </div> -->
                <!-- End Access & Permission -->
                <!-- masters -->
                {{-- <div class="nav-item">
                    <a class="nav-link dropdown-toggle active <?= $pageSegment == 'product-categories' || $pageSegment == 'product' || $pageSegment == 'client-type' || $pageSegment == 'employee-type' || $pageSegment == 'zones' || $pageSegment == 'region' || $pageSegment == 'state' || $pageSegment == 'size' || $pageSegment == 'unit' || $pageSegment == 'banners' || $pageSegment == 'quotes' || $pageSegment == 'notification-templates' ? '' : 'collapsed' ?>"
                        href="#navbarVerticalMenuMasters" role="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarVerticalMenuMasters"
                        aria-expanded="<?= $pageSegment == 'product-categories' || $pageSegment == 'product' || $pageSegment == 'client-type' || $pageSegment == 'employee-type' || $pageSegment == 'zones' || $pageSegment == 'region' || $pageSegment == 'state' || $pageSegment == 'size' || $pageSegment == 'unit' || $pageSegment == 'banners' || $pageSegment == 'quotes' || $pageSegment == 'notification-templates' ? 'true' : 'false' ?>"
                        aria-controls="navbarVerticalMenuAccess">
                        <i class="fa fa-database nav-icon"></i>
                        <span class="nav-link-title">Masters</span>
                    </a>
                    <div id="navbarVerticalMenuMasters"
                        class="nav-collapse collapse <?= $pageSegment == 'product-categories' || $pageSegment == 'product' || $pageSegment == 'client-type' || $pageSegment == 'employee-type' || $pageSegment == 'zones' || $pageSegment == 'region' || $pageSegment == 'state' || $pageSegment == 'size' || $pageSegment == 'unit' || $pageSegment == 'banners' || $pageSegment == 'quotes' || $pageSegment == 'notification-templates' ? 'show' : '' ?>"
                        data-bs-parent="#navbarVerticalMenu">
                        <a class="nav-link <?= $pageSegment == 'product-categories' ? 'active' : '' ?>"
                            href="<?= url('admin/product-categories/list') ?>">Product Categories</a>
                        <a class="nav-link <?= $pageSegment == 'product' ? 'active' : '' ?>"
                            href="<?= url('admin/product/list') ?>">Products</a>
                        <a class="nav-link <?= $pageSegment == 'client-type' ? 'active' : '' ?>"
                            href="<?= url('admin/client-type/list') ?>">Client Types</a>
                        <a class="nav-link <?= $pageSegment == 'employee-type' ? 'active' : '' ?>"
                            href="<?= url('admin/employee-type/list') ?>">Employee Types</a>
                        <a class="nav-link <?= $pageSegment == 'zones' ? 'active' : '' ?>"
                            href="<?= url('admin/zones/list') ?>">Zones</a>
                        <a class="nav-link <?= $pageSegment == 'region' ? 'active' : '' ?>"
                            href="<?= url('admin/region/list') ?>">Regions</a>
                        <a class="nav-link <?= $pageSegment == 'state' ? 'active' : '' ?>"
                            href="<?= url('admin/state/list') ?>">States</a>
                        <a class="nav-link <?= $pageSegment == 'unit' ? 'active' : '' ?>"
                            href="<?= url('admin/unit/list') ?>">Units</a>
                        <a class="nav-link <?= $pageSegment == 'size' ? 'active' : '' ?>"
                            href="<?= url('admin/size/list') ?>">Sizes</a>
                        <a class="nav-link <?= $pageSegment == 'banners' ? 'active' : '' ?>"
                            href="<?= url('admin/banners/list') ?>">Banners</a>
                        <a class="nav-link <?= $pageSegment == 'quotes' ? 'active' : '' ?>"
                            href="<?= url('admin/quotes/list') ?>">Quotes</a>
                        <a class="nav-link <?= $pageSegment == 'notification-templates' ? 'active' : '' ?>"
                            href="<?= url('admin/notification-templates/list') ?>">Notification Templates</a>
                        <a class="nav-link <?= $pageSegment == 'notification-templates' ? 'active' : '' ?>"
                            href="{{ route('industry-master.index') }}">Industry</a>
                        <a class="nav-link <?= $pageSegment == 'notification-templates' ? 'active' : '' ?>"
                            href="{{ route('business-master.index') }}">Business Type</a>
                    </div>
                </div> --}}
                <!-- End masters -->
                <!-- employee -->
                {{-- <div class="nav-item">
                    <a class="nav-link dropdown-toggle active <?= $pageSegment == 'employee-details' ? '' : 'collapsed' ?>"
                        href="#navbarVerticalMenuemployee" role="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarVerticalMenuemployee"
                        aria-expanded="<?= $pageSegment == 'employee-details' ? 'true' : 'false' ?>"
                        aria-controls="navbarVerticalMenuAccess">
                        <i class="fa fa-users nav-icon"></i>
                        <span class="nav-link-title">Employees</span>
                    </a>
                    <div id="navbarVerticalMenuemployee"
                        class="nav-collapse collapse <?= $pageSegment == 'employee-details' ? 'show' : '' ?>"
                        data-bs-parent="#navbarVerticalMenu">
                        <a class="nav-link <?= $pageSegment == 'employee-details' && $slug == 'all' ? 'active' : '' ?>"
                            href="<?= url('admin/employee-details/all/list') ?>">ALL</a>
                        <?php if($employee_type){ foreach($employee_type as $employee_types) { ?>
                        <a class="nav-link <?= $pageSegment == 'employee-details' && $slug == $employee_types->slug ? 'active' : '' ?>"
                            href="<?= url('admin/employee-details/' . $employee_types->slug . '/list') ?>"><?= $employee_types->name ?></a>
                        <?php } } ?>
                    </div>
                </div> --}}
                <!-- End employee -->
                <!-- clients -->
                <div class="nav-item">
                    <a class="nav-link dropdown-toggle active <?= $pageSegment == 'clients' ? '' : 'collapsed' ?>"
                        href="#navbarVerticalMenuClient" role="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarVerticalMenuClient"
                        aria-expanded="<?= $pageSegment == 'clients' ? 'true' : 'false' ?>"
                        aria-controls="navbarVerticalMenuAccess">
                        <i class="fa fa-users nav-icon"></i>
                        <span class="nav-link-title">Users</span>
                    </a>
                    <div id="navbarVerticalMenuClient"
                        class="nav-collapse collapse <?= $pageSegment == 'clients' ? 'show' : '' ?>"
                        data-bs-parent="#navbarVerticalMenu">

                        <a class="nav-link <?= $pageSegment == 'clients' && $slug == 'business' ? 'active' : '' ?>"
                            href="<?= url('admin/clients/business/list') ?>">
                            Business </a>

                        <a class="nav-link <?= $pageSegment == 'clients' && ($slug ?? '') == 'registered-members' ? 'active' : '' ?>"
                            href="<?= route('admin.clients.registered-members') ?>">Registered Members</a>

                        <a class="nav-link <?= $pageSegment == 'clients' && ($slug ?? '') == 'guest-users' ? 'active' : '' ?>"
                            href="<?= route('admin.clients.guest-users') ?>">Guest Users</a>
                    </div>
                </div>
                <!-- End clients -->
                <!-- orders -->
                {{-- <div class="nav-item">
                    <a class="nav-link dropdown-toggle active <?= $pageSegment == 'orders' ? '' : 'collapsed' ?>"
                        href="#navbarVerticalMenuorders" role="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarVerticalMenuorders"
                        aria-expanded="<?= $pageSegment == 'orders' ? 'true' : 'false' ?>"
                        aria-controls="navbarVerticalMenuAccess">
                        <i class="fa fa-database nav-icon"></i>
                        <span class="nav-link-title">Orders</span>
                    </a>
                    <?php
                    $submittedOrdersCount = ClientOrder::where('status', '=', 1)->count();
                    $approvedOrdersCount = ClientOrder::where('status', '=', 2)->count();
                    $dispatchOrdersCount = ClientOrder::where('status', '=', 3)->count();
                    $billingOrdersCount = ClientOrder::where('status', '=', 4)->count();
                    $completeOrdersCount = ClientOrder::where('status', '=', 5)->count();
                    ?>
                    <div id="navbarVerticalMenuorders"
                        class="nav-collapse collapse <?= $pageSegment == 'orders' ? 'show' : '' ?>"
                        data-bs-parent="#navbarVerticalMenu">
                        <a class="nav-link <?= $pageSegment == 'orders' && $slug == 'submitted' ? 'active' : '' ?>"
                            href="<?= url('admin/orders/submitted') ?>">Submitted Orders
                            (<?= $submittedOrdersCount ?>)</a>
                        <a class="nav-link <?= $pageSegment == 'orders' && $slug == 'approved' ? 'active' : '' ?>"
                            href="<?= url('admin/orders/approved') ?>">Approved Orders
                            (<?= $approvedOrdersCount ?>)</a>
                        <a class="nav-link <?= $pageSegment == 'orders' && $slug == 'dispatch' ? 'active' : '' ?>"
                            href="<?= url('admin/orders/dispatch') ?>">Dispatch Orders
                            (<?= $dispatchOrdersCount ?>)</a>
                        <a class="nav-link <?= $pageSegment == 'orders' && $slug == 'billing' ? 'active' : '' ?>"
                            href="<?= url('admin/orders/billing') ?>">Billing Orders (<?= $billingOrdersCount ?>)</a>
                        <a class="nav-link <?= $pageSegment == 'orders' && $slug == 'completed' ? 'active' : '' ?>"
                            href="<?= url('admin/orders/completed') ?>">Complete Orders
                            (<?= $completeOrdersCount ?>)</a>
                    </div>
                </div> --}}
                <!-- End orders -->
                <!-- Report -->
                {{-- <div class="nav-item">
                    <a class="nav-link dropdown-toggle active <?= $pageSegment == 'report' ? '' : 'collapsed' ?>"
                        href="#navbarVerticalMenureport" role="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarVerticalMenureport"
                        aria-expanded="<?= $pageSegment == 'report' ? 'true' : 'false' ?>"
                        aria-controls="navbarVerticalMenuAccess">
                        <i class="fa fa-database nav-icon"></i>
                        <span class="nav-link-title">Report</span>
                    </a>
                    <div id="navbarVerticalMenureport"
                        class="nav-collapse collapse <?= $pageSegment == 'report' ? 'show' : '' ?>"
                        data-bs-parent="#navbarVerticalMenu">
                        <a class="nav-link <?= $pageSegment == 'report' && $slug == 'attendance-report' ? 'active' : '' ?>"
                            href="<?= url('admin/report/attendance-report') ?>">Attandence</a>
                        <!-- <a class="nav-link ?=(($pageSegment == 'report' && $slug == 'odometer-report')?'active':'')?>" href="?=url('admin/report/odometer-report')?>">Odometer</a> -->
                        <a class="nav-link <?= $pageSegment == 'report' && $slug == 'odometer-details-report' ? 'active' : '' ?>"
                            href="<?= url('admin/report/odometer-details-report') ?>">Odometer Details</a>
                    </div>
                </div> --}}
                <!-- End Report -->
                <!-- contact enquires -->
                {{-- <div class="nav-item">
                    <a class="nav-link <?= $pageSegment == 'enquiry' ? 'active' : '' ?>"
                        href="<?= url('admin/enquiry/list') ?>" data-placement="left">
                        <i class="fa fa-envelope nav-icon"></i>
                        <span class="nav-link-title">Contact Enquires</span>
                    </a>
                </div> --}}
                <!-- End contact enquires -->
                <!-- page -->
                <div class="nav-item">
                    <a class="nav-link <?= $pageSegment == 'homepage' ? 'active' : '' ?>"
                        href="<?= url('admin/homepage') ?>" data-placement="left">
                        <i class="fa fa-house nav-icon"></i>
                        <span class="nav-link-title">Homepage CMS</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link <?= $pageSegment == 'page' ? 'active' : '' ?>"
                        href="<?= url('admin/page/list') ?>" data-placement="left">
                        <i class="fa fa-file nav-icon"></i>
                        <span class="nav-link-title">Pages</span>
                    </a>
                </div>
                <!-- End page -->

                @unless($memberDelegatedAdmin)
                <div class="nav-item">
                    <a class="nav-link <?= $pageSegment == 'bulk-imports' ? 'active' : '' ?>"
                        href="<?= url('admin/bulk-imports') ?>" data-placement="left">
                        <i class="fa fa-file-import nav-icon"></i>
                        <span class="nav-link-title">Bulk Import</span>
                    </a>
                </div>

                <!-- memberships -->
                <div class="nav-item">
                    <a class="nav-link <?= $pageSegment == 'memberships' ? 'active' : '' ?>"
                        href="<?= url('admin/memberships') ?>" data-placement="left">
                        <i class="fa fa-id-card nav-icon"></i>
                        <span class="nav-link-title">Memberships</span>
                    </a>
                </div>
                <!-- End memberships -->
                <div class="nav-item">
                    <a class="nav-link <?= $pageSegment == 'chapters' ? 'active' : '' ?>"
                        href="<?= url('admin/chapters') ?>" data-placement="left">
                        <i class="fa fa-people-group nav-icon"></i>
                        <span class="nav-link-title">Chapters</span>
                    </a>
                </div>
                @endunless
                <div class="nav-item">
                    <a class="nav-link <?= $pageSegment == 'events' ? 'active' : '' ?>"
                        href="<?= url('admin/events') ?>" data-placement="left">
                        <i class="fa fa-calendar nav-icon"></i>
                        <span class="nav-link-title">Events</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link <?= $pageSegment == 'membership-accounts' ? 'active' : '' ?>"
                        href="<?= url('admin/membership-accounts') ?>" data-placement="left">
                        <i class="fa fa-calculator nav-icon"></i>
                        <span class="nav-link-title">Membership Accounts</span>
                    </a>
                </div>

                @unless($memberDelegatedAdmin)
                <!-- business analytics -->
                <div class="nav-item">
                    <a class="nav-link <?= $pageSegment == 'analytics' ? 'active' : '' ?>"
                        href="<?= url('admin/analytics') ?>" data-placement="left">
                        <i class="fa fa-chart-line nav-icon"></i>
                        <span class="nav-link-title">Analytics</span>
                    </a>
                </div>
                <!-- End business analytics -->

                <div class="nav-item">
                    <a class="nav-link <?= $pageSegment == 'business-categories' ? 'active' : '' ?>"
                        href="<?= url('admin/business-categories') ?>" data-placement="left">
                        <i class="fa fa-tags nav-icon"></i>
                        <span class="nav-link-title">Business Categories</span>
                    </a>
                </div>

                <!-- email logs -->
                <div class="nav-item">
                    <a class="nav-link <?= $pageSegment == 'email-logs' ? 'active' : '' ?>"
                        href="<?= url('admin/email-logs') ?>" data-placement="left">
                        <i class="fa fa-history nav-icon"></i>
                        <span class="nav-link-title">Email Logs</span>
                    </a>
                </div>
                <!-- End email logs -->
                <!-- login logs -->
                <div class="nav-item">
                    <a class="nav-link <?= $pageSegment == 'login-logs' ? 'active' : '' ?>"
                        href="<?= url('admin/login-logs') ?>" data-placement="left">
                        <i class="fa fa-sign-in nav-icon"></i>
                        <span class="nav-link-title">Login Logs</span>
                    </a>
                </div>
                <!-- End login logs -->
                @endunless
                <?php if (($admin->type ?? null) === 'ma') { ?>
                <div class="nav-item">
                    <a class="nav-link <?= $pageSegment == 'admin-access' ? 'active' : '' ?>"
                        href="<?= url('admin/admin-access') ?>" data-placement="left">
                        <i class="fa fa-user-shield nav-icon"></i>
                        <span class="nav-link-title">Admin Access</span>
                    </a>
                </div>
                <?php } ?>
                @unless($memberDelegatedAdmin)
                <!-- settings -->
                <div class="nav-item">
                    <a class="nav-link <?= $pageSegment == 'settings' ? 'active' : '' ?>"
                        href="<?= url('admin/settings') ?>" data-placement="left">
                        <i class="fa fa-cog nav-icon"></i>
                        <span class="nav-link-title">Settings</span>
                    </a>
                </div>
                <!-- End settings -->
                @endunless
            </div>
        </div>
        <!-- End Content -->
        <!-- Footer -->
        <div class="navbar-vertical-footer">
            <ul class="navbar-vertical-footer-list">
                <li class="navbar-vertical-footer-list-item">
                    <!-- Style Switcher -->
                    <div class="dropdown dropup">
                        <!-- <button type="button" class="btn btn-ghost-secondary btn-icon rounded-circle" id="selectThemeDropdown" data-bs-toggle="dropdown" aria-expanded="false" data-bs-dropdown-animation>
            </button> -->
                        <div class="dropdown-menu navbar-dropdown-menu navbar-dropdown-menu-borderless"
                            aria-labelledby="selectThemeDropdown">
                            <a class="dropdown-item" href="#" data-icon="bi-moon-stars" data-value="auto">
                                <i class="bi-moon-stars me-2"></i>
                                <span class="text-truncate" title="Auto (system default)">Auto (system default)</span>
                            </a>
                            <a class="dropdown-item" href="#" data-icon="bi-brightness-high"
                                data-value="default">
                                <i class="bi-brightness-high me-2"></i>
                                <span class="text-truncate" title="Default (light mode)">Default (light mode)</span>
                            </a>
                            <a class="dropdown-item active" href="#" data-icon="bi-moon" data-value="dark">
                                <i class="bi-moon me-2"></i>
                                <span class="text-truncate" title="Dark">Dark</span>
                            </a>
                        </div>
                    </div>
                    <!-- End Style Switcher -->
                </li>
            </ul>
        </div>
        <!-- End Footer -->
    </div>
</div>
