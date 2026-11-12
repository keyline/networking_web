<?php
use App\Helpers\Helper;
?>
<div class="pagetitle">
  <h1><?=$page_header?></h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?=url('admin/dashboard')?>">Home</a></li>
      <li class="breadcrumb-item"><a href="<?=url('admin/email-logs')?>">Email-logs List</a></li>
      <li class="breadcrumb-item active"><?=$page_header?></li>
    </ol>
  </nav>
</div><!-- End Page Title -->
<section class="section">
  <div class="row">
    <div class="col-xl-12">
      @if(session('success_message'))
        <div class="alert alert-success bg-success text-light border-0 alert-dismissible fade show autohide" role="alert">
          {{ session('success_message') }}
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif
      @if(session('error_message'))
        <div class="alert alert-danger bg-danger text-light border-0 alert-dismissible fade show autohide" role="alert">
          {{ session('error_message') }}
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif
    </div>
    <div class="col-lg-12">
      <div class="card">
        <div class="card-body" style="margin-top: 20px">
          <style>
            @media (max-width: 575.98px) {
              .email-log-kv tr, .email-log-kv th, .email-log-kv td { display: block; width: 100% !important; }
              .email-log-kv th { padding-bottom: 4px !important; }
            }
          </style>
          <!-- Table with stripped rows -->
          <table class="email-log-kv" style="width: 100%;  border-spacing: 2px;">
            <tbody>
                <tr>
                  <th style="background: #ccc; color: #000; width: 30%; padding: 10px; text-align: left; font-family: sans-serif; font-size: 14px;">Full Name</th>
                  <td class="text-break" style="padding: 10px; background: #ccc; text-align: left; color: #000;font-family: sans-serif;font-size: 15px;"><?=$logData->name?></td>
                </tr>
                <tr>
                  <th style="background: #cccccc42; color: #000; width: 30%; padding: 10px; text-align: left; font-family: sans-serif; font-size: 14px;">Email Address</th>
                  <td class="text-break" style="padding: 10px; background: #cccccc42; text-align: left; color: #000;font-family: sans-serif;font-size: 15px;"><?=$logData->email?></td>
                </tr>
                <tr>
                  <th style="background: #ccc; color: #000; width: 30%; padding: 10px; text-align: left; font-family: sans-serif; font-size: 14px;">Phone</th>
                  <td class="text-break" style="padding: 10px; background: #ccc; text-align: left; color: #000;font-family: sans-serif;font-size: 15px;"><?=$logData->phone?></td>
                </tr>
                <tr>
                  <th style="background: #cccccc42; color: #000; width: 30%; padding: 10px; text-align: left; font-family: sans-serif; font-size: 14px;">Subject</th>
                  <td class="text-break" style="padding: 10px; background: #cccccc42; text-align: left; color: #000;font-family: sans-serif;font-size: 15px;"><?=$logData->subject?></td>
                </tr>
                <tr>
                  <th style="background: #ccc; color: #000; width: 30%; padding: 10px; text-align: left; font-family: sans-serif; font-size: 14px;">Description</th>
                  <td class="text-break" style="padding: 10px; background: #ccc; text-align: left; color: #000;font-family: sans-serif;font-size: 15px;">
                    {{-- Rendered email HTML is isolated in a sandboxed iframe so its own styles cannot leak into the admin page or overflow the card. --}}
                    <iframe class="email-log-message" title="Email content" sandbox="allow-same-origin allow-popups allow-popups-to-escape-sandbox"
                      srcdoc="{{ '<base target="_blank">' . $logData->message }}"
                      style="display:block;width:100%;min-height:300px;border:0;background:#fff;"
                      onload="try{this.style.height=(this.contentWindow.document.documentElement.scrollHeight+16)+'px';}catch(e){this.style.height='500px';}"></iframe>
                  </td>
                </tr>
                <tr>
                  <th style="background: #cccccc42; color: #000; width: 30%; padding: 10px; text-align: left; font-family: sans-serif; font-size: 14px;">Added On</th>
                  <td class="text-break" style="padding: 10px; background: #cccccc42; text-align: left; color: #000;font-family: sans-serif;font-size: 15px;"><?=date_format(date_create($logData->created_at), "M d, Y h:i A")?></td>
                </tr>
              </tbody>
          </table>
          <!-- End Table with stripped rows -->
        </div>
      </div>
    </div>
  </div>
</section>