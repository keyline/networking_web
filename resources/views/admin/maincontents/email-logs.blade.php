<?php
use App\Helpers\Helper;

$totalLogs = $rows ? $rows->count() : 0;
$todayLogs = $rows ? $rows->filter(fn ($row) => \Carbon\Carbon::parse($row->created_at)->isToday())->count() : 0;
$monthLogs = $rows ? $rows->filter(fn ($row) => \Carbon\Carbon::parse($row->created_at)->isCurrentMonth())->count() : 0;
?>

<style>
  .email-log-page { --el-primary: #397ef6; --el-border: #e6ebf2; --el-muted: #718096; }
  .email-log-page .page-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; margin-bottom: 18px; }
  .email-log-page .page-head h1 { margin: 0 0 4px; color: #17233c; font-size: 24px; font-weight: 700; letter-spacing: -.02em; }
  .email-log-page .page-head p { margin: 0; color: var(--el-muted); font-size: 13px; }
  .email-log-page .breadcrumb { margin: 0 0 8px; font-size: 12px; }
  .email-log-page .breadcrumb-item + .breadcrumb-item::before { color: #a8b2c1; }
  .email-log-page .summary-grid { display: grid; grid-template-columns: repeat(3, minmax(150px, 220px)); gap: 10px; }
  .email-log-page .summary-card { display: flex; align-items: center; gap: 11px; min-height: 66px; padding: 12px 14px; background: #fff; border: 1px solid var(--el-border); border-radius: 10px; box-shadow: 0 4px 14px rgba(23, 35, 60, .04); }
  .email-log-page .summary-icon { display: grid; place-items: center; width: 34px; height: 34px; flex: 0 0 34px; border-radius: 8px; background: #edf4ff; color: var(--el-primary); font-size: 15px; }
  .email-log-page .summary-card:nth-child(2) .summary-icon { background: #eaf8f1; color: #1a8b58; }
  .email-log-page .summary-card:nth-child(3) .summary-icon { background: #fff4e8; color: #d77916; }
  .email-log-page .summary-label { color: var(--el-muted); font-size: 11px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
  .email-log-page .summary-value { margin-top: 1px; color: #17233c; font-size: 20px; font-weight: 750; line-height: 1; }
  .email-log-page .log-card { overflow: hidden; background: #fff; border: 1px solid var(--el-border); border-radius: 12px; box-shadow: 0 8px 24px rgba(23, 35, 60, .05); }
  .email-log-page .log-card-head { display: flex; align-items: center; justify-content: space-between; gap: 15px; padding: 15px 18px; border-bottom: 1px solid var(--el-border); }
  .email-log-page .log-card-head h2 { margin: 0; color: #26344f; font-size: 15px; font-weight: 700; }
  .email-log-page .log-card-head span { color: var(--el-muted); font-size: 12px; }
  .email-log-page .table-area { padding: 0 18px 14px; }
  .email-log-page .dataTables_top { min-height: 60px; padding: 10px 0; border-bottom: 1px solid #eef1f6; }
  .email-log-page .dt-buttons .dt-button { min-height: 34px; margin: 0 5px 0 0 !important; padding: 6px 12px !important; background: #fff !important; color: #4c5d78 !important; border: 1px solid #dce3ed !important; border-radius: 7px !important; box-shadow: none !important; font-size: 12px !important; font-weight: 650 !important; }
  .email-log-page .dt-buttons .dt-button:hover { background: #f5f8fc !important; border-color: #bfc9d8 !important; }
  .email-log-page .dt-length label, .email-log-page .dt-search label { color: #65738b; font-size: 12px; font-weight: 600; }
  .email-log-page .dt-length select { min-width: 66px; height: 34px; margin: 0 5px; border: 1px solid #dce3ed; border-radius: 7px; }
  .email-log-page .dt-search input { width: 220px; height: 36px; margin-left: 8px; padding: 7px 11px; border: 1px solid #dce3ed; border-radius: 7px; outline: none; }
  .email-log-page .dt-search input:focus { border-color: var(--el-primary); box-shadow: 0 0 0 3px rgba(57, 126, 246, .1); }
  .email-log-page table.dataTable { width: 100% !important; margin: 0 !important; border: 0 !important; }
  .email-log-page table.dataTable thead th { height: 42px; padding: 10px 12px !important; background: #f7f9fc; color: #64748b; border: 0 !important; border-bottom: 1px solid var(--el-border) !important; font-size: 11px; font-weight: 750; letter-spacing: .045em; text-transform: uppercase; white-space: nowrap; }
  .email-log-page table.dataTable tbody td { height: 52px; padding: 9px 12px !important; color: #4b5d78; border: 0 !important; border-bottom: 1px solid #edf1f6 !important; font-size: 13px; vertical-align: middle; }
  .email-log-page table.dataTable tbody tr:nth-child(even), .email-log-page table.dataTable tbody tr:nth-child(odd) { background: #fff !important; }
  .email-log-page table.dataTable tbody tr:hover { background: #f8fbff !important; }
  .email-log-page .row-number { color: #97a3b5; font-variant-numeric: tabular-nums; }
  .email-log-page .person-name { color: #273650; font-weight: 650; }
  .email-log-page .person-name.empty { color: #a6afbc; font-weight: 500; }
  .email-log-page .email-address { color: #52647f; }
  .email-log-page .subject-line { display: block; max-width: 430px; overflow: hidden; color: #344662; font-weight: 600; text-overflow: ellipsis; white-space: nowrap; }
  .email-log-page .date-cell { color: #718096; white-space: nowrap; font-variant-numeric: tabular-nums; }
  .email-log-page .view-btn { display: inline-flex; align-items: center; gap: 6px; padding: 6px 10px; color: var(--el-primary); border: 1px solid #bcd2fb; border-radius: 7px; background: #f8fbff; font-size: 12px; font-weight: 700; transition: .15s ease; }
  .email-log-page .view-btn:hover { color: #fff; background: var(--el-primary); border-color: var(--el-primary); }
  .email-log-page .dt-info { padding-top: 14px !important; color: #78869b; font-size: 12px; }
  .email-log-page .dt-paging { padding-top: 10px; }
  .email-log-page .dt-paging .dt-paging-button { min-width: 32px; height: 32px; margin-left: 3px !important; padding: 4px 8px !important; border: 1px solid #e0e6ef !important; border-radius: 6px !important; background: #fff !important; color: #5f6f87 !important; font-size: 12px; }
  .email-log-page .dt-paging .dt-paging-button.current { background: var(--el-primary) !important; color: #fff !important; border-color: var(--el-primary) !important; }
  @media (max-width: 991px) { .email-log-page .page-head { align-items: stretch; flex-direction: column; } .email-log-page .summary-grid { grid-template-columns: repeat(3, 1fr); } }
  @media (max-width: 640px) { .email-log-page .summary-grid { grid-template-columns: 1fr; } .email-log-page .dataTables_top { align-items: stretch !important; flex-direction: column; } .email-log-page .dt-search { float: none; margin-left: 0; text-align: left; } .email-log-page .dt-search input { width: calc(100% - 55px); } }
</style>

<div class="email-log-page">
  <div class="page-head">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active" aria-current="page">Email Logs</li>
        </ol>
      </nav>
      <h1>Email Delivery Log</h1>
      <p>Review all transactional and authentication emails generated by the system.</p>
    </div>

    <div class="summary-grid">
      <div class="summary-card">
        <div class="summary-icon"><i class="fa fa-envelope"></i></div>
        <div><div class="summary-label">Total records</div><div class="summary-value"><?= number_format($totalLogs) ?></div></div>
      </div>
      <div class="summary-card">
        <div class="summary-icon"><i class="fa fa-calendar-day"></i></div>
        <div><div class="summary-label">Sent today</div><div class="summary-value"><?= number_format($todayLogs) ?></div></div>
      </div>
      <div class="summary-card">
        <div class="summary-icon"><i class="fa fa-calendar-alt"></i></div>
        <div><div class="summary-label">This month</div><div class="summary-value"><?= number_format($monthLogs) ?></div></div>
      </div>
    </div>
  </div>

  @if(session('success_message'))
    <div class="alert alert-success alert-dismissible fade show autohide" role="alert">{{ session('success_message') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
  @endif
  @if(session('error_message'))
    <div class="alert alert-danger alert-dismissible fade show autohide" role="alert">{{ session('error_message') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
  @endif

  <section class="log-card">
    <div class="log-card-head">
      <div><h2>Outgoing email history</h2><span>Newest activity is shown first</span></div>
    </div>
    <div class="table-area">
      <div class="table-responsive">
        <table id="simpletable" class="table nowrap">
          <thead>
            <tr>
              <th style="width: 52px">#</th>
              <th>Name</th>
              <th>Email address</th>
              <th>Subject</th>
              <th>Date &amp; time</th>
              <th class="text-end" style="width: 90px">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($rows) { $sl = 1; foreach ($rows as $row) { ?>
              <tr>
                <td><span class="row-number"><?= $sl++ ?></span></td>
                <td><span class="person-name<?= empty($row->name) ? ' empty' : '' ?>"><?= e($row->name ?: 'Not provided') ?></span></td>
                <td><span class="email-address"><?= e($row->email) ?></span></td>
                <td><span class="subject-line" title="<?= e($row->subject) ?>"><?= e($row->subject) ?></span></td>
                <td data-order="<?= e($row->created_at) ?>"><span class="date-cell"><?= date_format(date_create($row->created_at), 'd M Y, h:i A') ?></span></td>
                <td class="text-end">
                  <a class="view-btn" href="<?= url('admin/email-logs/details/' . Helper::encoded($row->id)) ?>" aria-label="View email details">
                    <i class="fa fa-eye"></i><span>View</span>
                  </a>
                </td>
              </tr>
            <?php } } ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</div>
