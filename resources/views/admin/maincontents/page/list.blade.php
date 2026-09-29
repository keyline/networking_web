<?php
use App\Helpers\Helper;
use App\Models\Companies;
use App\Models\Admin;

$controllerRoute = $module['controller_route'];
$totalPages = count($rows);
$activePages = $rows->where('status', 1)->count();
$inactivePages = $totalPages - $activePages;
$adminIds = $rows->pluck('created_by')->merge($rows->pluck('updated_by'))->filter()->unique();
$pageAdmins = Admin::whereIn('id', $adminIds)->get()->keyBy('id');
$companyIds = $rows->pluck('company_id')->filter()->unique();
$pageCompanies = $companyIds->isNotEmpty() ? Companies::whereIn('id', $companyIds)->get()->keyBy('id') : collect();
?>

<style>
  .page-manager { --pm-primary:#397ef6; --pm-text:#263650; --pm-muted:#728097; --pm-border:#e5eaf2; }
  .page-manager .page-head { display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:12px; }
  .page-manager .breadcrumb { margin:0 0 4px; font-size:11px; }
  .page-manager h1 { margin:0; color:#17233c; font-size:21px; font-weight:750; letter-spacing:-.02em; }
  .page-manager .page-description { margin:3px 0 0; color:var(--pm-muted); font-size:11.5px; }
  .page-manager .page-head-actions { display:flex; align-items:center; gap:10px; }
  .page-manager .add-page { display:inline-flex; align-items:center; gap:6px; min-height:34px; padding:7px 12px; color:#fff; border:1px solid var(--pm-primary); border-radius:6px; background:var(--pm-primary); font-size:11.5px; font-weight:750; box-shadow:0 4px 10px rgba(57,126,246,.16); }
  .page-manager .add-page:hover { color:#fff; background:#2e6fdd; }
  .page-manager .summary { display:flex; overflow:hidden; border:1px solid var(--pm-border); border-radius:6px; background:#fff; }
  .page-manager .summary-item { display:flex; align-items:center; gap:6px; min-width:auto; padding:7px 10px; border-right:1px solid var(--pm-border); }
  .page-manager .summary-item:last-child { border-right:0; }
  .page-manager .summary-item span { color:#8793a5; font-size:8.5px; font-weight:750; letter-spacing:.04em; text-transform:uppercase; }
  .page-manager .summary-item strong { color:#2e3e58; font-size:13px; line-height:1; }
  .page-manager .content-card { overflow:hidden; border:1px solid var(--pm-border); border-radius:8px; background:#fff; box-shadow:0 4px 14px rgba(23,35,60,.04); }
  .page-manager .card-head { padding:10px 13px; border-bottom:1px solid #edf1f6; }
  .page-manager .card-head h2 { margin:0; color:var(--pm-text); font-size:13px; font-weight:750; }
  .page-manager .card-head p { margin:2px 0 0; color:#8995a6; font-size:10px; }
  .page-manager .table-area { padding:0 13px 9px; }
  .page-manager .dataTables_top { min-height:48px; padding:7px 0; border-bottom:1px solid #edf1f6; }
  .page-manager .dt-buttons .dt-button { min-height:34px; margin:0 5px 0 0 !important; padding:6px 12px !important; color:#4d5e78 !important; border:1px solid #dce3ed !important; border-radius:7px !important; background:#fff !important; box-shadow:none !important; font-size:12px !important; font-weight:650 !important; }
  .page-manager .dt-buttons .dt-button:hover { border-color:#bdc8d7 !important; background:#f5f8fc !important; }
  .page-manager .dt-length label, .page-manager .dt-search label, .page-manager .dataTables_length label, .page-manager .dataTables_filter label { color:#68778e; font-size:12px; font-weight:600; }
  .page-manager .dt-length select, .page-manager .dataTables_length select { min-width:66px; height:34px; margin:0 5px; border:1px solid #dce3ed; border-radius:7px; }
  .page-manager .dt-search input, .page-manager .dataTables_filter input { width:220px; height:36px; margin-left:8px; padding:7px 11px; border:1px solid #dce3ed; border-radius:7px; outline:none; }
  .page-manager .dt-search input:focus, .page-manager .dataTables_filter input:focus { border-color:var(--pm-primary); box-shadow:0 0 0 3px rgba(57,126,246,.1); }
  .page-manager table.dataTable { width:100% !important; margin:0 !important; border:0 !important; }
  .page-manager table.dataTable thead th { height:36px; padding:7px 9px !important; color:#65748b; border:0 !important; border-bottom:1px solid var(--pm-border) !important; background:#f7f9fc; font-size:9.5px; font-weight:750; letter-spacing:.045em; text-transform:uppercase; white-space:nowrap; }
  .page-manager table.dataTable tbody td { height:48px; padding:7px 9px !important; color:#52637d; border:0 !important; border-bottom:1px solid #edf1f6 !important; background:#fff; font-size:11.5px; vertical-align:middle; }
  .page-manager table.dataTable tbody tr:hover td { background:#f8fbff; }
  .page-manager .row-no { color:#9aa6b6; font-variant-numeric:tabular-nums; }
  .page-manager .page-identity { display:flex; align-items:center; gap:10px; }
  .page-manager .page-icon { display:grid; place-items:center; width:27px; height:27px; flex:0 0 27px; color:#5c7eb5; border-radius:6px; background:#edf3fc; }
  .page-manager .page-identity strong { display:block; color:#2c3c56; font-size:12.5px; font-weight:700; }
  .page-manager .page-identity small { display:block; margin-top:2px; color:#929dad; font-size:9.5px; }
  .page-manager .audit-info strong { display:block; overflow:hidden; max-width:145px; color:#52627a; font-size:11.5px; font-weight:650; text-overflow:ellipsis; white-space:nowrap; }
  .page-manager .audit-info time { display:block; margin-top:2px; color:#909bac; font-size:10px; white-space:nowrap; }
  .page-manager .status { display:inline-flex; align-items:center; gap:5px; padding:5px 8px; border-radius:12px; font-size:9.5px; font-weight:800; text-transform:uppercase; }
  .page-manager .status::before { width:6px; height:6px; border-radius:50%; background:currentColor; content:''; }
  .page-manager .status.active { color:#178454; background:#e8f8f0; }
  .page-manager .status.inactive { color:#b36a17; background:#fff3e5; }
  .page-manager .actions { display:flex; align-items:center; gap:5px; white-space:nowrap; }
  .page-manager .action-btn { display:grid; place-items:center; width:28px; height:28px; color:#66768d; border:1px solid #dce3ed; border-radius:5px; background:#fff; font-size:10px; }
  .page-manager .action-btn.edit:hover { color:#fff; border-color:var(--pm-primary); background:var(--pm-primary); }
  .page-manager .action-btn.delete:hover { color:#fff; border-color:#dc5360; background:#dc5360; }
  .page-manager .action-btn.status-action:hover { color:#fff; border-color:#21a572; background:#21a572; }
  .page-manager .empty { padding:35px !important; color:#8995a6 !important; text-align:center; }
  .page-manager .dt-info, .page-manager .dataTables_info { padding-top:14px !important; color:#78869b; font-size:12px; }
  .page-manager .dt-paging, .page-manager .dataTables_paginate { padding-top:10px; }
  .page-manager .dt-paging .dt-paging-button, .page-manager .dataTables_paginate .paginate_button { min-width:32px; height:32px; margin-left:3px !important; padding:4px 8px !important; border:1px solid #e0e6ef !important; border-radius:6px !important; background:#fff !important; color:#5f6f87 !important; font-size:12px; }
  .page-manager .dt-paging .dt-paging-button.current, .page-manager .dataTables_paginate .paginate_button.current { color:#fff !important; border-color:var(--pm-primary) !important; background:var(--pm-primary) !important; }
  @media(max-width:991px){.page-manager .page-head{align-items:stretch;flex-direction:column}.page-manager .page-head-actions{justify-content:space-between}}
  @media(max-width:767px){.page-manager .page-head-actions{align-items:stretch;flex-direction:column}.page-manager .summary{width:100%}.page-manager .summary-item{flex:1;justify-content:center}.page-manager .add-page{justify-content:center}.page-manager .dataTables_top{align-items:stretch !important;flex-direction:column}.page-manager .dt-search,.page-manager .dataTables_filter{float:none;margin-left:0;text-align:left}.page-manager .dt-search input,.page-manager .dataTables_filter input{width:calc(100% - 55px)}}
</style>

<div class="page-manager">
  <div class="page-head">
    <div>
      <nav><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Dashboard</a></li><li class="breadcrumb-item active">Pages</li></ol></nav>
      <h1>Content Pages</h1>
      <p class="page-description">Create and maintain the informational pages displayed across the portal and mobile app.</p>
    </div>
    <div class="page-head-actions">
      <div class="summary"><div class="summary-item"><span>Total pages</span><strong><?= number_format($totalPages) ?></strong></div><div class="summary-item"><span>Published</span><strong><?= number_format($activePages) ?></strong></div><div class="summary-item"><span>Draft</span><strong><?= number_format($inactivePages) ?></strong></div></div>
      <a href="<?= url('admin/'.$controllerRoute.'/add/') ?>" class="add-page"><i class="fa fa-plus"></i>Add page</a>
    </div>
  </div>

  @if(session('success_message'))<div class="alert alert-success alert-dismissible fade show autohide">{{ session('success_message') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
  @if(session('error_message'))<div class="alert alert-danger alert-dismissible fade show autohide">{{ session('error_message') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

  <section class="content-card">
    <div class="card-head">
      <div><h2>Page directory</h2><p>Manage page content, publishing status, and revision activity.</p></div>
    </div>
    <div class="table-area"><div class="table-responsive">
      <table id="simpletable" class="table nowrap">
        <thead><tr><th style="width:46px">#</th><th>Page</th><?php if($admin->company_id == 0){ ?><th>Company</th><?php } ?><th>Navigation</th><th>Last updated</th><th>Status</th><th style="width:145px">Actions</th></tr></thead>
        <tbody>
          <?php if($totalPages > 0){ $sl=1; foreach($rows as $row){
            $creator = $pageAdmins->get($row->created_by);
            $updater = $pageAdmins->get($row->updated_by);
            $company = $pageCompanies->get($row->company_id);
          ?>
            <tr>
              <td><span class="row-no"><?= $sl++ ?></span></td>
              <td><div class="page-identity"><span class="page-icon"><i class="fa fa-file-alt"></i></span><div><strong><?= $row->parent_id ? '↳ ' : '' ?><?= e($row->page_name) ?></strong><small>/page/<?= e($row->page_slug ?: 'page') ?></small></div></div></td>
              <?php if($admin->company_id == 0){ ?><td><?= e($company->name ?? 'Global') ?></td><?php } ?>
              <td><div class="audit-info"><strong><?= e(ucwords(str_replace(['both','none'], ['Header & footer','Not displayed'], $row->nav_location ?? 'none'))) ?></strong><time><?= $row->parent ? 'Under '.$row->parent->page_name : 'Top level' ?> · Order <?= (int)($row->nav_order ?? 0) ?></time></div></td>
              <td data-order="<?= e($row->updated_at) ?>"><div class="audit-info"><strong><?= e($updater->name ?? $creator->name ?? 'System') ?></strong><time><?= $row->updated_at ? date('d M Y, h:i A', strtotime($row->updated_at)) : '—' ?></time></div></td>
              <td><span class="status <?= $row->status ? 'active' : 'inactive' ?>"><?= $row->status ? 'Published' : 'Draft' ?></span></td>
              <td><div class="actions">
                <?php if($row->status){ ?><a href="<?= url('page/'.$row->page_slug) ?>" target="_blank" class="action-btn" title="View live page" aria-label="View live page"><i class="fa fa-external-link-alt"></i></a><?php } ?>
                <a href="<?= url('admin/'.$controllerRoute.'/edit/'.Helper::encoded($row->id)) ?>" class="action-btn edit" title="Edit page" aria-label="Edit page"><i class="fa fa-edit"></i></a>
                <a href="<?= url('admin/'.$controllerRoute.'/change-status/'.Helper::encoded($row->id)) ?>" class="action-btn status-action" title="<?= $row->status ? 'Move to draft' : 'Publish page' ?>" aria-label="Change page status"><i class="fa <?= $row->status ? 'fa-eye-slash' : 'fa-check' ?>"></i></a>
                <a href="<?= url('admin/'.$controllerRoute.'/delete/'.Helper::encoded($row->id)) ?>" class="action-btn delete" title="Delete page" aria-label="Delete page" onclick="return confirm('Delete this page? This action cannot be undone.');"><i class="fa fa-trash"></i></a>
              </div></td>
            </tr>
          <?php } } else { ?>
            <tr><td colspan="<?= $admin->company_id == 0 ? 7 : 6 ?>" class="empty">No pages have been created yet.</td></tr>
          <?php } ?>
        </tbody>
      </table>
    </div></div>
  </section>
</div>
