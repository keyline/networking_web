<?php
$successCount = $rows2 ? $rows2->count() : 0;
$failedCount = $rows1 ? $rows1->count() : 0;
$logoutCount = $rows3 ? $rows3->count() : 0;
$totalCount = $successCount + $failedCount + $logoutCount;
$tabs = [
    ['id' => 'success-logins', 'table' => 'simpletable', 'label' => 'Successful', 'icon' => 'fa-check', 'rows' => $rows2, 'class' => 'success', 'active' => true],
    ['id' => 'failed-logins', 'table' => 'simpletable2', 'label' => 'Failed', 'icon' => 'fa-times', 'rows' => $rows1, 'class' => 'failed', 'active' => false],
    ['id' => 'logout-events', 'table' => 'simpletable3', 'label' => 'Logouts', 'icon' => 'fa-sign-out-alt', 'rows' => $rows3, 'class' => 'logout', 'active' => false],
];
?>

<style>
  .login-audit { --la-blue: #397ef6; --la-border: #e5eaf2; --la-text: #263650; --la-muted: #728097; }
  .login-audit .page-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; margin-bottom: 18px; }
  .login-audit .breadcrumb { margin: 0 0 8px; font-size: 12px; }
  .login-audit .breadcrumb-item + .breadcrumb-item::before { color: #a8b2c1; }
  .login-audit h1 { margin: 0 0 4px; color: #17233c; font-size: 24px; font-weight: 750; letter-spacing: -.02em; }
  .login-audit .page-description { margin: 0; color: var(--la-muted); font-size: 13px; }
  .login-audit .metric-grid { display: grid; grid-template-columns: repeat(4, minmax(128px, 175px)); gap: 9px; }
  .login-audit .metric { display: flex; align-items: center; gap: 10px; min-height: 64px; padding: 11px 13px; background: #fff; border: 1px solid var(--la-border); border-radius: 10px; box-shadow: 0 4px 14px rgba(23, 35, 60, .04); }
  .login-audit .metric-icon { display: grid; place-items: center; width: 33px; height: 33px; flex: 0 0 33px; border-radius: 8px; background: #edf4ff; color: var(--la-blue); }
  .login-audit .metric.success .metric-icon { background: #e8f8f0; color: #198754; }
  .login-audit .metric.failed .metric-icon { background: #ffeded; color: #dc3545; }
  .login-audit .metric.logout .metric-icon { background: #f0edff; color: #7256d8; }
  .login-audit .metric-label { color: var(--la-muted); font-size: 10px; font-weight: 750; letter-spacing: .05em; text-transform: uppercase; }
  .login-audit .metric-value { margin-top: 2px; color: #17233c; font-size: 20px; font-weight: 750; line-height: 1; }
  .login-audit .audit-card { overflow: hidden; background: #fff; border: 1px solid var(--la-border); border-radius: 12px; box-shadow: 0 8px 24px rgba(23, 35, 60, .05); }
  .login-audit .audit-card-head { padding: 15px 18px 0; border-bottom: 1px solid var(--la-border); }
  .login-audit .audit-card-title { display: flex; align-items: center; justify-content: space-between; gap: 15px; margin-bottom: 13px; }
  .login-audit .audit-card-title h2 { margin: 0; color: var(--la-text); font-size: 15px; font-weight: 700; }
  .login-audit .audit-card-title span { color: var(--la-muted); font-size: 12px; }
  .login-audit .audit-tabs { gap: 7px; margin: 0; border: 0; }
  .login-audit .audit-tabs .nav-link { display: inline-flex; align-items: center; gap: 7px; margin: 0; padding: 9px 13px; color: #64748b; border: 0; border-bottom: 2px solid transparent; background: transparent; font-size: 12px; font-weight: 700; }
  .login-audit .audit-tabs .nav-link:hover { color: var(--la-blue); }
  .login-audit .audit-tabs .nav-link.active { color: var(--la-blue); border-bottom-color: var(--la-blue); }
  .login-audit .tab-count { min-width: 22px; padding: 2px 6px; border-radius: 10px; background: #eef2f7; color: #637188; font-size: 10px; text-align: center; }
  .login-audit .nav-link.active .tab-count { background: #e9f1ff; color: var(--la-blue); }
  .login-audit .table-zone { padding: 0 18px 14px; }
  .login-audit .dataTables_top { min-height: 58px; padding: 10px 0; border-bottom: 1px solid #edf1f6; }
  .login-audit .dt-buttons .dt-button { min-height: 34px; margin: 0 5px 0 0 !important; padding: 6px 12px !important; background: #fff !important; color: #4d5e78 !important; border: 1px solid #dce3ed !important; border-radius: 7px !important; box-shadow: none !important; font-size: 12px !important; font-weight: 650 !important; }
  .login-audit .dt-buttons .dt-button:hover { background: #f5f8fc !important; border-color: #bdc8d7 !important; }
  .login-audit .dt-length label, .login-audit .dt-search label { color: #68778e; font-size: 12px; font-weight: 600; }
  .login-audit .dt-length select { min-width: 66px; height: 34px; margin: 0 5px; border: 1px solid #dce3ed; border-radius: 7px; }
  .login-audit .dt-search input { width: 220px; height: 36px; margin-left: 8px; padding: 7px 11px; border: 1px solid #dce3ed; border-radius: 7px; outline: none; }
  .login-audit .dt-search input:focus { border-color: var(--la-blue); box-shadow: 0 0 0 3px rgba(57, 126, 246, .1); }
  .login-audit table.dataTable { width: 100% !important; margin: 0 !important; border: 0 !important; }
  .login-audit table.dataTable thead th { height: 41px; padding: 9px 11px !important; background: #f7f9fc; color: #65748b; border: 0 !important; border-bottom: 1px solid var(--la-border) !important; font-size: 10.5px; font-weight: 750; letter-spacing: .045em; text-transform: uppercase; white-space: nowrap; }
  .login-audit table.dataTable tbody td { height: 52px; padding: 8px 11px !important; color: #4c5e78; border: 0 !important; border-bottom: 1px solid #edf1f6 !important; background: #fff; font-size: 12.5px; vertical-align: middle; }
  .login-audit table.dataTable tbody tr:hover td { background: #f8fbff; }
  .login-audit .row-no { color: #9aa6b6; font-variant-numeric: tabular-nums; }
  .login-audit .identity strong { display: block; max-width: 220px; overflow: hidden; color: #2b3b55; font-size: 12.5px; font-weight: 700; text-overflow: ellipsis; white-space: nowrap; }
  .login-audit .identity span { display: block; max-width: 250px; margin-top: 2px; overflow: hidden; color: #8491a4; font-size: 11px; text-overflow: ellipsis; white-space: nowrap; }
  .login-audit .role-tag, .login-audit .platform-tag { display: inline-flex; padding: 4px 7px; border-radius: 5px; background: #f0f3f8; color: #596a82; font-size: 10px; font-weight: 750; letter-spacing: .03em; text-transform: uppercase; }
  .login-audit .network strong { display: block; color: #52637d; font-size: 12px; font-weight: 650; }
  .login-audit .network span { display: block; margin-top: 2px; color: #919cad; font-size: 10px; text-transform: uppercase; }
  .login-audit .activity-text { display: block; max-width: 260px; overflow: hidden; color: #53657e; text-overflow: ellipsis; white-space: nowrap; }
  .login-audit .date-cell { color: #718096; white-space: nowrap; font-variant-numeric: tabular-nums; }
  .login-audit .status-pill { display: inline-flex; align-items: center; gap: 5px; padding: 5px 8px; border-radius: 12px; font-size: 10px; font-weight: 750; letter-spacing: .03em; text-transform: uppercase; white-space: nowrap; }
  .login-audit .status-pill::before { width: 6px; height: 6px; border-radius: 50%; background: currentColor; content: ''; }
  .login-audit .status-success { background: #e8f8f0; color: #15824f; }
  .login-audit .status-failed { background: #ffeded; color: #c93843; }
  .login-audit .status-logout { background: #efedff; color: #684ec7; }
  .login-audit .dt-info { padding-top: 14px !important; color: #78869b; font-size: 12px; }
  .login-audit .dt-paging { padding-top: 10px; }
  .login-audit .dt-paging .dt-paging-button { min-width: 32px; height: 32px; margin-left: 3px !important; padding: 4px 8px !important; border: 1px solid #e0e6ef !important; border-radius: 6px !important; background: #fff !important; color: #5f6f87 !important; font-size: 12px; }
  .login-audit .dt-paging .dt-paging-button.current { background: var(--la-blue) !important; color: #fff !important; border-color: var(--la-blue) !important; }
  @media (max-width: 1199px) { .login-audit .page-head { align-items: stretch; flex-direction: column; } .login-audit .metric-grid { grid-template-columns: repeat(4, 1fr); } }
  @media (max-width: 700px) { .login-audit .metric-grid { grid-template-columns: repeat(2, 1fr); } .login-audit .dataTables_top { align-items: stretch !important; flex-direction: column; } .login-audit .dt-search { float: none; margin-left: 0; text-align: left; } .login-audit .dt-search input { width: calc(100% - 55px); } }
</style>

<div class="login-audit">
  <div class="page-head">
    <div>
      <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Dashboard</a></li><li class="breadcrumb-item active">Login Logs</li></ol></nav>
      <h1>Authentication Audit</h1>
      <p class="page-description">Monitor successful logins, failed attempts, and logout activity across the portal.</p>
    </div>
    <div class="metric-grid">
      <div class="metric"><div class="metric-icon"><i class="fa fa-list"></i></div><div><div class="metric-label">All events</div><div class="metric-value"><?= number_format($totalCount) ?></div></div></div>
      <div class="metric success"><div class="metric-icon"><i class="fa fa-check"></i></div><div><div class="metric-label">Successful</div><div class="metric-value"><?= number_format($successCount) ?></div></div></div>
      <div class="metric failed"><div class="metric-icon"><i class="fa fa-times"></i></div><div><div class="metric-label">Failed</div><div class="metric-value"><?= number_format($failedCount) ?></div></div></div>
      <div class="metric logout"><div class="metric-icon"><i class="fa fa-sign-out-alt"></i></div><div><div class="metric-label">Logouts</div><div class="metric-value"><?= number_format($logoutCount) ?></div></div></div>
    </div>
  </div>

  @if(session('success_message'))<div class="alert alert-success alert-dismissible fade show autohide">{{ session('success_message') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
  @if(session('error_message'))<div class="alert alert-danger alert-dismissible fade show autohide">{{ session('error_message') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

  <section class="audit-card">
    <div class="audit-card-head">
      <div class="audit-card-title"><div><h2>Access history</h2><span>Events are listed newest first</span></div></div>
      <ul class="nav nav-tabs audit-tabs" role="tablist">
        <?php foreach ($tabs as $tab) { ?>
          <li class="nav-item" role="presentation"><button class="nav-link<?= $tab['active'] ? ' active' : '' ?>" data-bs-toggle="tab" data-bs-target="#<?= $tab['id'] ?>" type="button"><i class="fa <?= $tab['icon'] ?>"></i><?= $tab['label'] ?><span class="tab-count"><?= number_format($tab['rows'] ? $tab['rows']->count() : 0) ?></span></button></li>
        <?php } ?>
      </ul>
    </div>

    <div class="tab-content">
      <?php foreach ($tabs as $tab) { ?>
        <div class="tab-pane fade<?= $tab['active'] ? ' show active' : '' ?>" id="<?= $tab['id'] ?>">
          <div class="table-zone"><div class="table-responsive">
            <table id="<?= $tab['table'] ?>" class="table nowrap">
              <thead><tr><th style="width:48px">#</th><th>Identity</th><th>Role</th><th>Network</th><th>Activity</th><th>Date &amp; time</th><th>Status</th></tr></thead>
              <tbody>
                <?php if ($tab['rows']) { $sl = 1; foreach ($tab['rows'] as $row) { ?>
                  <tr>
                    <td><span class="row-no"><?= $sl++ ?></span></td>
                    <td><div class="identity"><strong><?= e($row->user_name ?: 'Unknown user') ?></strong><span><?= e($row->user_email ?: 'Email not available') ?></span></div></td>
                    <td><span class="role-tag"><?= e($row->user_type ?: 'User') ?></span></td>
                    <td><div class="network"><strong><?= e($row->ip_address ?: '—') ?></strong><span><?= e($row->platform_type ?: 'Unknown') ?></span></div></td>
                    <td><span class="activity-text" title="<?= e($row->activity_details) ?>"><?= e($row->activity_details ?: 'Authentication event') ?></span></td>
                    <td data-order="<?= e($row->created_at) ?>"><span class="date-cell"><?= date_format(date_create($row->created_at), 'd M Y, h:i A') ?></span></td>
                    <td><span class="status-pill status-<?= $tab['class'] ?>"><?= $tab['label'] ?></span></td>
                  </tr>
                <?php } } ?>
              </tbody>
            </table>
          </div></div>
        </div>
      <?php } ?>
    </div>
  </section>
</div>
