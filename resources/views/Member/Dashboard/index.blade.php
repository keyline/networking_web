<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.page_title') }}</title>
    <style>
        :root{--ink:#17233c;--muted:#6f7c93;--line:#e4eaf2;--blue:#397ef6;--blue-soft:#edf4ff;--green:#159b72;--bg:#f4f7fb;--card:#fff;--shadow:0 10px 28px rgba(35,55,88,.07)}
        *{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--ink);font-family:Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;font-size:14px}.shell{min-height:100vh}.topbar{height:70px;background:#fff;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;padding:0 max(24px,calc((100vw - 1240px)/2));position:sticky;top:0;z-index:20}.brand{font-size:22px;font-weight:800;letter-spacing:-.6px;color:var(--ink);text-decoration:none}.brand i{font-style:normal;color:#f47a20}.top-actions{display:flex;align-items:center;gap:16px}.identity{text-align:right}.identity strong,.identity span{display:block}.identity span{font-size:12px;color:var(--muted);margin-top:2px}.avatar{width:38px;height:38px;border-radius:12px;background:var(--blue-soft);color:var(--blue);display:grid;place-items:center;font-weight:800}.logout{color:#5e6b82;text-decoration:none;font-weight:700}.container{max-width:1240px;margin:0 auto;padding:28px 24px 50px}.hero{background:linear-gradient(125deg,#172a52,#315caa);border-radius:16px;padding:18px 20px;color:#fff;display:grid;grid-template-columns:1fr auto;gap:18px;align-items:center;box-shadow:var(--shadow)}.hero h1{font-size:22px;margin:0;letter-spacing:-.4px}.hero-actions,.actions{display:flex;gap:8px;flex-wrap:wrap}.hero-actions .btn{height:34px;padding:0 12px;border-radius:8px;font-size:12px}.btn{border:0;border-radius:10px;height:42px;padding:0 16px;display:inline-flex;align-items:center;justify-content:center;gap:8px;font:inherit;font-weight:750;cursor:pointer;text-decoration:none}.btn-primary{background:var(--blue);color:#fff}.btn-light{background:#fff;color:#234678}.btn-outline{background:#fff;border:1px solid #d9e2ef;color:#40506b}.quick{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin:16px 0 22px}.quick button,.quick a{min-height:86px;background:#fff;border:1px solid var(--line);border-radius:14px;padding:16px;text-decoration:none;color:var(--ink);display:flex;gap:13px;align-items:center;text-align:left;cursor:pointer;font:inherit}.quick .icon{width:42px;height:42px;border-radius:11px;background:var(--blue-soft);color:var(--blue);display:grid;place-items:center;font-size:20px}.quick strong,.quick small{display:block}.quick small{color:var(--muted);margin-top:4px}.grid{display:grid;grid-template-columns:minmax(0,1.55fr) minmax(300px,.75fr);gap:18px}.card{background:var(--card);border:1px solid var(--line);border-radius:16px;box-shadow:var(--shadow);overflow:hidden}.card-head{padding:18px 20px;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;gap:16px}.card-head h2{font-size:17px;margin:0}.card-head p{color:var(--muted);margin:4px 0 0;font-size:12px}.card-body{padding:18px 20px}.search{display:flex;gap:9px}.search input{flex:1}.field,input,select,textarea{width:100%;border:1px solid #d9e2ef;border-radius:9px;background:#fff;padding:11px 12px;font:inherit;color:var(--ink);outline:none}input:focus,select:focus,textarea:focus{border-color:var(--blue);box-shadow:0 0 0 3px rgba(57,126,246,.11)}textarea{min-height:92px;resize:vertical}.business-list,.feed{display:grid;gap:10px}.business{border:1px solid var(--line);border-radius:12px;padding:14px;display:flex;align-items:center;gap:13px}.logo{width:46px;height:46px;flex:0 0 46px;border-radius:12px;background:#eef3fa;display:grid;place-items:center;color:#486078;font-weight:800}.business-info{min-width:0;flex:1}.business h3{font-size:14px;margin:0 0 4px}.business p{margin:0;color:var(--muted);font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.business .link{color:var(--blue);font-size:12px;font-weight:750;text-decoration:none}.feed-item{padding:13px 0;border-bottom:1px solid var(--line)}.feed-item:first-child{padding-top:0}.feed-item:last-child{border-bottom:0;padding-bottom:0}.feed-top{display:flex;align-items:center;justify-content:space-between;gap:10px}.feed-item h3{font-size:14px;margin:0}.feed-item p{color:var(--muted);font-size:12px;line-height:1.5;margin:6px 0 0}.pill{display:inline-flex;border-radius:99px;padding:5px 9px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;background:#eef3fa;color:#607088}.pill.public{background:#e9f8f2;color:#168160}.profile{display:grid;gap:12px}.profile-row{display:flex;justify-content:space-between;gap:12px;padding-bottom:11px;border-bottom:1px solid var(--line)}.profile-row span{color:var(--muted);font-size:12px}.profile-row strong{text-align:right;font-size:12px}.empty{text-align:center;padding:24px;color:var(--muted);font-size:13px}.results{margin-top:12px;display:grid;grid-template-columns:repeat(2,1fr);gap:10px}.notice{padding:12px 14px;border-radius:10px;margin-bottom:16px;background:#eaf8f1;color:#17734a}.notice.error{background:#fff0f0;color:#b42318}.notice ul{margin:0;padding-left:18px}dialog{border:0;border-radius:17px;padding:0;width:min(560px,calc(100% - 32px));box-shadow:0 28px 80px rgba(23,35,60,.3)}dialog::backdrop{background:rgba(16,27,48,.58)}.modal-head{padding:19px 22px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:center}.modal-head h2{font-size:18px;margin:0}.close{border:0;background:#eef2f7;width:34px;height:34px;border-radius:9px;cursor:pointer;font-size:20px}.modal-body{padding:20px 22px}.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}.form-group{display:grid;gap:6px}.form-group.full{grid-column:1/-1}.form-group label{font-size:12px;font-weight:750}.modal-foot{padding:15px 22px;border-top:1px solid var(--line);display:flex;justify-content:flex-end;gap:9px}.section-gap{margin-top:18px}@media(max-width:900px){.quick{grid-template-columns:repeat(2,1fr)}.grid{grid-template-columns:1fr}.hero{grid-template-columns:1fr}.results{grid-template-columns:1fr}}@media(max-width:560px){.container{padding:18px 14px 40px}.topbar{padding:0 14px}.identity{display:none}.hero{padding:16px}.hero h1{font-size:20px}.quick{grid-template-columns:1fr}.form-grid{grid-template-columns:1fr}.form-group.full{grid-column:auto}}
        [hidden]{display:none!important}.business-profile{min-width:0;flex:1;display:flex;align-items:center;gap:13px;color:inherit;text-decoration:none;border-radius:9px}.business-profile:hover h3{color:var(--blue)}.business-edit-card{color:inherit;text-decoration:none;transition:border-color .18s,box-shadow .18s,transform .18s}.business-edit-card:hover{border-color:#b9cff5;box-shadow:0 7px 18px rgba(57,126,246,.1);transform:translateY(-1px)}.business-edit-card .edit-hint{color:var(--blue);font-size:12px;font-weight:750}.request-contact{display:flex;flex-wrap:wrap;gap:6px 14px;margin-top:9px;padding-top:9px;border-top:1px dashed var(--line);font-size:11px}.request-contact strong{color:var(--ink)}.request-contact a,.request-contact span{color:#52627b;text-decoration:none}.request-contact a:hover{color:var(--blue)}
        .search-field{position:relative;min-width:0;flex:1}.search-field input{padding-right:38px}.search-clear{position:absolute;top:50%;right:9px;display:grid;width:22px;height:22px;place-items:center;transform:translateY(-50%);border-radius:50%;background:#edf1f7;color:#53627a;text-decoration:none;font-size:15px;font-weight:700;line-height:1}.search-clear:hover{background:#dfe7f2;color:#17233c}.search-clear:focus-visible{outline:2px solid var(--blue);outline-offset:2px}
        .enquiry-actions{display:flex;flex-wrap:wrap;gap:7px;margin-top:10px}.enquiry-actions a{display:inline-flex;min-height:34px;align-items:center;justify-content:center;padding:0 11px;border-radius:8px;text-decoration:none;font-size:10px;font-weight:800}.enquiry-actions .whatsapp{background:#daf7e8;color:#08784a}.enquiry-actions .share-whatsapp{border:1px solid #bde8d2;background:#fff;color:#08784a}.enquiry-actions a:hover{filter:brightness(.96)}
        .business-actions{display:flex;flex:0 0 auto;gap:7px}.business-actions .btn{height:36px;padding:0 12px;font-size:11px}.business-actions .view-business{border:0;background:linear-gradient(135deg,#397ef6,#7557df);color:#fff;box-shadow:0 5px 13px rgba(68,94,210,.22)}.business-actions .view-business:hover{background:linear-gradient(135deg,#2868dc,#6546cd)}
        .account-menu{position:relative}.account-menu summary{display:flex;align-items:center;gap:9px;padding:4px 7px;border-radius:11px;list-style:none;cursor:pointer}.account-menu summary::-webkit-details-marker{display:none}.account-menu summary:hover,.account-menu[open] summary{background:#f3f7fc}.account-menu .avatar{overflow:hidden}.account-menu .avatar img{width:100%;height:100%;object-fit:cover}.account-chevron{color:#7c899d;font-size:11px;transition:transform .18s}.account-menu[open] .account-chevron{transform:rotate(180deg)}.account-dropdown{position:absolute;z-index:50;top:calc(100% + 8px);right:0;width:180px;padding:7px;border:1px solid var(--line);border-radius:12px;background:#fff;box-shadow:0 16px 40px rgba(25,43,72,.18)}.account-dropdown a{display:flex;align-items:center;gap:9px;min-height:40px;padding:0 11px;border-radius:8px;color:#35455d;text-decoration:none;font-size:12px;font-weight:750}.account-dropdown a:hover{background:#edf4ff;color:#2868dc}.account-dropdown .account-logout{color:#a83b34}.account-dropdown .account-logout:hover{background:#fff0ef;color:#a3261e}
        .brand{display:inline-flex;align-items:center}.brand img{display:block;max-width:180px;max-height:48px}.admin-dashboard-form{margin:0}.admin-dashboard-link{height:36px;padding:0 13px;border:1px solid #c8d8f3;border-radius:9px;background:#f3f7ff;color:#2769df;font:inherit;font-size:12px;font-weight:800;cursor:pointer;white-space:nowrap}@media(max-width:560px){.admin-dashboard-link{width:36px;padding:0;font-size:0}.admin-dashboard-link::before{content:'⚙';font-size:16px}}
        dialog.meeting-dialog{max-height:min(760px,calc(100vh - 28px));overflow:hidden}.meeting-dialog form{display:flex;max-height:inherit;flex-direction:column}.meeting-dialog .modal-body{overflow-y:auto}.radio-row{display:grid;grid-template-columns:1fr 1fr;gap:8px}.radio-choice{display:flex;align-items:center;gap:8px;padding:10px;border:1px solid var(--line);border-radius:9px;cursor:pointer}.radio-choice:has(input:checked){border-color:var(--blue);background:var(--blue-soft);color:#245fc4}.radio-choice input{width:auto;margin:0}.meeting-meta{display:flex;flex-wrap:wrap;gap:5px 10px;margin-top:6px;color:var(--muted);font-size:11px}.meeting-details{white-space:normal!important;line-height:1.45}.form-help{color:var(--muted);font-size:11px;line-height:1.45}@media(max-width:560px){dialog.meeting-dialog{width:calc(100% - 20px)}.meeting-dialog .modal-head,.meeting-dialog .modal-body,.meeting-dialog .modal-foot{padding-left:15px;padding-right:15px}.radio-row{grid-template-columns:1fr}}
        .dashboard-sidebar{display:grid;align-content:start;gap:10px}.dashboard-sidebar .section-gap{margin-top:0}.dashboard-sidebar .card{border-radius:12px;box-shadow:0 5px 16px rgba(35,55,88,.045)}.dashboard-sidebar .card-head{padding:11px 13px;gap:9px}.dashboard-sidebar .card-head h2{font-size:14px;line-height:1.25}.dashboard-sidebar .card-head p{margin-top:2px;font-size:10px;line-height:1.35}.dashboard-sidebar .card-head .btn{height:32px;padding:0 10px;border-radius:8px;font-size:11px}.dashboard-sidebar .card-body{padding:11px 13px}.dashboard-sidebar .profile{gap:6px}.dashboard-sidebar .profile-row{padding:0 0 6px}.dashboard-sidebar .profile-row:last-child{padding-bottom:0;border-bottom:0}.dashboard-sidebar .profile-row span,.dashboard-sidebar .profile-row strong{font-size:11px}.dashboard-sidebar .business-list,.dashboard-sidebar .feed{gap:6px}.dashboard-sidebar .business{gap:9px;padding:8px;border-radius:9px}.dashboard-sidebar .logo{width:34px;height:34px;flex-basis:34px;border-radius:8px;font-size:11px}.dashboard-sidebar .business h3{margin-bottom:2px;font-size:12px;line-height:1.25}.dashboard-sidebar .business p,.dashboard-sidebar .business .link,.dashboard-sidebar .business .edit-hint{font-size:10px}.dashboard-sidebar .feed-item{padding:8px 0}.dashboard-sidebar .feed-item h3{font-size:12px}.dashboard-sidebar .feed-item p{margin-top:3px;font-size:10px;line-height:1.35}.dashboard-sidebar .meeting-meta{gap:3px 8px;margin-top:4px;font-size:9px}.dashboard-sidebar .request-contact{gap:4px 8px;margin-top:5px;padding-top:5px;font-size:9px}.dashboard-sidebar .pill{padding:4px 7px;font-size:8px}.dashboard-sidebar .empty{padding:13px 8px;font-size:11px}.dashboard-sidebar .business-priority{border:2px solid #397ef6;box-shadow:0 10px 26px rgba(57,126,246,.16)}.dashboard-sidebar .business-priority .card-head{border-bottom-color:#cfe0ff;background:linear-gradient(110deg,#edf4ff,#f7faff)}.business-priority-label{display:block;margin-bottom:3px;color:#2769df;font-size:9px;font-weight:850;letter-spacing:.08em;text-transform:uppercase}.dashboard-sidebar .business-priority .business-edit-card{border-color:#bfd4fb;background:#f7faff}.dashboard-sidebar .business-priority .business-edit-card:hover{border-color:#397ef6;background:#edf4ff}.dashboard-sidebar .business-priority .edit-hint{padding:5px 8px;border-radius:7px;background:#397ef6;color:#fff;white-space:nowrap}@media(max-width:900px){.dashboard-sidebar{grid-template-columns:repeat(2,minmax(0,1fr));align-items:start}.dashboard-sidebar .card{min-width:0}.dashboard-sidebar .business-priority{grid-column:1/-1}}@media(max-width:620px){.dashboard-sidebar{grid-template-columns:1fr}.dashboard-sidebar .business-priority{grid-column:auto}}
        .hero + .grid{margin-top:18px}
        .interactions-card{border-radius:12px;box-shadow:0 5px 16px rgba(35,55,88,.045)}.interactions-card .card-head{padding:11px 14px}.interactions-card .card-head h2{font-size:14px;line-height:1.25}.interactions-card .card-head p{margin-top:2px;font-size:10px;line-height:1.35}.interactions-card .card-body{max-height:190px;overflow-y:auto;padding:5px 14px;scrollbar-width:thin}.interactions-card .feed-item{padding:8px 0}.interactions-card .feed-item h3{font-size:12px}.interactions-card .feed-item p{margin-top:3px;font-size:10px;line-height:1.35}.interactions-card .request-contact{gap:4px 10px;margin-top:5px;padding-top:5px;font-size:9px}.interactions-card .pill{padding:4px 7px;font-size:8px}.interactions-card .empty{padding:13px 8px;font-size:11px}
        .profile-summary{display:flex;align-items:center;gap:11px}.profile-photo{width:58px;height:58px;flex:0 0 58px;overflow:hidden;border:2px solid #dce8fb;border-radius:14px;background:var(--blue-soft);color:var(--blue);display:grid;place-items:center;font-size:16px;font-weight:800}.profile-photo img{width:100%;height:100%;display:block;object-fit:cover}.profile-summary .profile{min-width:0;flex:1}.profile-summary .profile-row{gap:8px}.profile-summary .profile-row strong{min-width:0;overflow-wrap:anywhere}
        /* mobile responsiveness fixes */
        html,body{max-width:100%;overflow-x:clip}.grid,.grid>*,.card,.card-head>*,.card-body,.results,.results>*,.search,.business,.business-profile{min-width:0}.card-head p,.hero h1,.feed-item,.request-contact{overflow-wrap:anywhere}.search input{min-width:0}.business .link{flex:0 0 auto}
        @media(max-width:767.98px){input,select,textarea{font-size:16px}.radio-choice input{font-size:inherit}.business{flex-wrap:wrap}.business-profile{flex:1 1 220px}.business-actions{flex:1 1 100%}.business-actions .btn{flex:1}}
        @media(max-width:560px){.topbar{gap:8px}.brand{min-width:0}.brand img{max-width:130px}.top-actions{gap:8px}.logout{font-size:12px}.admin-dashboard-link{width:44px;height:44px}.search{flex-wrap:wrap}.search-field{flex:1 1 100%}.search .btn{flex:1 1 100%;min-height:44px}.quick{grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.quick button,.quick a{min-height:72px;flex-direction:column;align-items:flex-start;gap:8px;padding:12px}.quick .icon{width:36px;height:36px;font-size:18px}}

        /* quick action cards */
        .qa{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin:16px 0 22px}
        .qa-card{position:relative;display:flex;flex-direction:column;align-items:flex-start;justify-content:flex-end;gap:4px;min-height:150px;padding:18px;border:1px solid var(--line);border-radius:18px;background:#fff;box-shadow:var(--shadow);color:var(--ink);font:inherit;text-align:left;text-decoration:none;cursor:pointer;overflow:hidden;transition:transform .15s ease,box-shadow .15s ease}
        .qa-card:hover,.qa-card:focus-visible{transform:translateY(-2px);box-shadow:0 14px 30px rgba(35,55,88,.13);outline:none}
        .qa-card:active{transform:scale(.98)}
        .qa-card:before{content:"";position:absolute;right:-34px;top:-34px;width:110px;height:110px;border-radius:50%;background:var(--qa-soft);opacity:.9}
        .qa-icon{position:absolute;top:16px;left:16px;display:grid;place-items:center;width:52px;height:52px;border-radius:15px;background:var(--qa-grad);box-shadow:0 8px 18px var(--qa-glow)}
        .qa-icon svg{width:27px;height:27px;fill:#fff}
        .qa-card strong{position:relative;font-size:15px;line-height:1.25}
        .qa-card small{position:relative;color:var(--muted);font-size:12px;line-height:1.35}
        .qa-members{--qa-grad:linear-gradient(135deg,#4f8dfd,#2f5fd0);--qa-soft:#eaf1ff;--qa-glow:rgba(57,126,246,.35)}
        .qa-business{--qa-grad:linear-gradient(135deg,#1fc29a,#118064);--qa-soft:#e5f8f2;--qa-glow:rgba(21,155,114,.33)}
        .qa-reference{--qa-grad:linear-gradient(135deg,#a77bff,#6b45d8);--qa-soft:#f1ebff;--qa-glow:rgba(122,82,230,.33)}
        .qa-enquiry{--qa-grad:linear-gradient(135deg,#ffaa45,#f0702c);--qa-soft:#fff2e5;--qa-glow:rgba(240,112,44,.33)}
        .qa-review{--qa-grad:linear-gradient(135deg,#ffcf55,#e99a16);--qa-soft:#fff7dc;--qa-glow:rgba(233,154,22,.3)}
        .qa-meeting{--qa-grad:linear-gradient(135deg,#43c990,#16875a);--qa-soft:#e6f8ef;--qa-glow:rgba(22,135,90,.3)}
        .visitor-qa{grid-template-columns:repeat(2,minmax(0,1fr));margin-top:12px}
        .owner-analytics{margin:-4px 0 22px}.owner-analytics-head{display:flex;align-items:end;justify-content:space-between;gap:12px;margin-bottom:10px}.owner-analytics-head h2{margin:0;font-size:17px}.owner-analytics-head span{color:var(--muted);font-size:12px}.analytics-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}.analytics-stat{padding:14px 16px;border:1px solid var(--line);border-radius:14px;background:#fff;box-shadow:var(--shadow)}.analytics-stat strong{display:block;font-size:24px;line-height:1.1;color:var(--blue)}.analytics-stat span{display:block;margin-top:5px;color:var(--muted);font-size:12px;font-weight:700}
        @media(max-width:991.98px){.qa{grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.qa-card{min-height:0;aspect-ratio:1/1}}
        @media(max-width:650px){.analytics-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @media(max-width:380px){.qa{gap:10px}.qa-card{padding:14px}.qa-icon{top:14px;left:14px;width:46px;height:46px;border-radius:13px}.qa-icon svg{width:24px;height:24px}.qa-card strong{font-size:14px}.qa-card small{font-size:12px}}

        /* colourful section headers: each card gets its own accent so sections are easy to tell apart */
        .th-blue{--th:#397ef6;--th2:#2a5fd0;--th-soft:#e8f0ff}
        .th-orange{--th:#f58a2c;--th2:#e0601f;--th-soft:#fff1e3}
        .th-teal{--th:#14b3a3;--th2:#0b8577;--th-soft:#e2f7f4}
        .th-indigo{--th:#5b5ce6;--th2:#3f3fc2;--th-soft:#ececff}
        .th-purple{--th:#9b6cf5;--th2:#6d45d6;--th-soft:#f2ecff}
        .th-green{--th:#22b26b;--th2:#138a50;--th-soft:#e4f7ec}
        .th-rose{--th:#ec4f7f;--th2:#c9305f;--th-soft:#ffe9f0}
        .th-amber{--th:#eab308;--th2:#c48a04;--th-soft:#fff7d9}
        .card-head.th{justify-content:flex-start;border-top:4px solid var(--th);border-bottom:1px solid color-mix(in srgb,var(--th) 22%,#fff);background:linear-gradient(110deg,var(--th-soft),#fff 85%)}
        .card-head.th>div:not(.th-icon){flex:1;min-width:0}
        .card-head.th h2{color:color-mix(in srgb,var(--th2) 70%,#17233c)}
        .th-icon{flex:0 0 auto;display:grid;place-items:center;width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,var(--th),var(--th2));box-shadow:0 6px 14px color-mix(in srgb,var(--th) 35%,transparent)}
        .th-icon svg{width:21px;height:21px;fill:#fff}
        .card-head.th .btn-outline{border-color:color-mix(in srgb,var(--th) 45%,#fff);color:var(--th2)}
        .card-head.th .business-priority-label{color:var(--th2)}
        @media(max-width:767.98px){.card-head.th{padding:12px 14px;gap:11px}.th-icon{width:34px;height:34px;border-radius:10px}.th-icon svg{width:18px;height:18px}.card-head.th p{font-size:12px}}

        /* compact enquiry card */
        .compact-card .card-head.th{padding:12px 16px}
        .compact-card .card-head h2{font-size:16px}
        .compact-card .card-head p{margin-top:2px;font-size:12px}
        .compact-card .card-head .btn{height:36px;padding:0 14px;font-size:13px}
        .compact-card .card-body{padding:10px 16px}
        .compact-card .empty{padding:10px 6px;font-size:13px}
        .business-list-footer{justify-self:center;margin-top:2px;padding:6px 10px;border:1px solid var(--line);border-radius:999px;background:#f7f9fc;color:#53637a;text-decoration:none;font-size:10px;font-weight:800}.business-list-footer:hover{border-color:var(--blue);color:var(--blue)}

        /* card design: coloured band + overlapping white sheet + pill action + CTA panel */
        .tcard{position:relative;background:linear-gradient(135deg,color-mix(in srgb,var(--th2) 70%,#0b1a3a),var(--th2));border:0;border-radius:24px;box-shadow:0 12px 30px color-mix(in srgb,var(--th2) 22%,transparent)}
        .tc-band{display:flex;align-items:center;gap:12px;padding:16px 22px 34px;color:#fff}
        .tc-band .th-icon{width:38px;height:38px;border-radius:12px;background:rgba(255,255,255,.16);box-shadow:none;border:1px solid rgba(255,255,255,.25)}
        .tc-band .th-icon svg{width:20px;height:20px}
        .tc-label{font-size:16px;font-weight:600;letter-spacing:.01em}
        .tcard .card-head.th{position:relative;margin-top:-20px;padding:20px 22px 16px;border:0;border-bottom:1px solid #eef1f6;border-radius:22px 22px 0 0;background:#fff;align-items:center}
        .tcard .card-head.th h2{color:var(--ink);font-size:19px;line-height:1.25}
        .tcard .card-head.th p{margin-top:5px;color:var(--muted);font-size:13px}
        .tcard .card-head.th .btn,.tcard .card-head.th .btn-outline{height:42px;padding:0 18px;border:0;border-radius:999px;background:var(--th-soft);color:var(--th2);font-size:15px;font-weight:700;gap:6px;white-space:nowrap}
        .tcard .card-head.th .btn:hover{filter:brightness(.97)}
        .tcard .card-body{background:#fff;padding:14px 22px 20px}
        .tcard.business-priority{border:0}
        .tcard .feed-item{padding:12px 0}
        .tcard .feed-item:last-child{border-bottom:0}
        .tcard .empty{margin-top:4px;padding:16px 18px;border-radius:16px;background:var(--th-soft);color:var(--ink);text-align:left;font-size:14px}
        .tc-cta{display:flex;align-items:center;gap:14px;width:100%;margin-top:4px;padding:16px 18px;border:0;border-radius:18px;background:var(--th-soft);color:var(--ink);font:inherit;text-align:left;cursor:pointer}
        .tc-cta:hover{filter:brightness(.98)}
        .tc-cta-icon{flex:0 0 40px;display:grid;place-items:center;width:40px;height:40px;border:1.5px solid var(--th2);border-radius:11px}
        .tc-cta-icon svg{width:22px;height:22px;fill:var(--th2)}
        .tc-cta-text{flex:1;min-width:0}.tc-cta-text strong{display:block;font-size:15px}.tc-cta-text small{display:block;margin-top:3px;color:#4c5a72;font-size:13px}
        .tc-cta-arrow{flex:0 0 auto;color:var(--th2);font-size:28px;line-height:1}
        .dashboard-sidebar{gap:18px}
        .dashboard-sidebar .tcard{border-radius:24px;box-shadow:0 12px 30px color-mix(in srgb,var(--th2) 22%,transparent)}
        @media(max-width:767.98px){.tcard,.dashboard-sidebar .tcard{border-radius:20px}.dashboard-sidebar{gap:16px}.tc-band{padding:14px 18px 30px}.tcard .card-head.th{padding:18px 18px 14px;border-radius:20px 20px 0 0}.tcard .card-head.th h2{font-size:17px}.tcard .card-head.th p{font-size:12px}.tcard .card-head.th .btn,.tcard .card-head.th .btn-outline{height:38px;padding:0 15px;font-size:14px}.tcard .card-body{padding:12px 18px 18px}}

        /* single header: title lives in the coloured band, content sits on the white sheet */
        .tc-band{align-items:center;padding:18px 22px 36px}
        .tc-title{flex:1;min-width:0}
        .tc-title h2{margin:0;color:#fff;font-size:19px;line-height:1.25}
        .tc-title p{margin:4px 0 0;color:rgba(255,255,255,.82);font-size:13px;line-height:1.4}
        .tc-band .btn,.tc-band .btn-outline{flex:0 0 auto;height:40px;padding:0 16px;border:1px solid rgba(255,255,255,.35);border-radius:999px;background:rgba(255,255,255,.16);color:#fff;font-size:14px;font-weight:700;gap:6px;white-space:nowrap}
        .tc-band .btn:hover{background:rgba(255,255,255,.26)}
        .tcard>.card-body{position:relative;margin-top:-20px;border-radius:22px 22px 0 0;background:#fff;padding:20px 22px 20px}
        @media(max-width:767.98px){.tc-band{padding:16px 18px 34px;gap:12px}.tc-title h2{font-size:17px}.tc-title p{font-size:12px}.tc-band .btn,.tc-band .btn-outline{height:36px;padding:0 14px;font-size:13px}.tcard>.card-body{border-radius:20px 20px 0 0;padding:18px 18px 18px}}

        /* colourful pop-ups: coloured band header + white sheet, matching the dashboard cards */
        dialog.tdialog{border-radius:24px;background:linear-gradient(135deg,color-mix(in srgb,var(--th2) 70%,#0b1a3a),var(--th2));box-shadow:0 28px 80px color-mix(in srgb,var(--th2) 35%,rgba(16,27,48,.4))}
        .tdialog .modal-head{gap:14px;justify-content:flex-start;padding:20px 22px 40px;border-bottom:0;color:#fff}
        .tdialog .md-icon{width:44px;height:44px;border-radius:13px;background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.25);box-shadow:none}
        .tdialog .md-icon svg{width:22px;height:22px}
        .tdialog .md-title{flex:1;min-width:0}
        .tdialog .modal-head h2{color:#fff;font-size:20px;line-height:1.25}
        .tdialog .modal-head .form-help{margin-top:4px;color:rgba(255,255,255,.82);font-size:13px}
        .tdialog .close{flex:0 0 auto;width:40px;height:40px;border:1px solid rgba(255,255,255,.3);border-radius:999px;background:rgba(255,255,255,.16);color:#fff;font-size:22px;line-height:1;cursor:pointer}
        .tdialog .close:hover{background:rgba(255,255,255,.28)}
        .tdialog .modal-body{position:relative;margin-top:-22px;border-radius:22px 22px 0 0;background:#fff;padding:24px 22px 18px}
        .tdialog .modal-foot{background:#fff;border-top:1px solid #eef1f6;padding:14px 22px 18px}
        .tdialog label{color:var(--ink)}
        .tdialog input:focus,.tdialog select:focus,.tdialog textarea:focus{border-color:var(--th);box-shadow:0 0 0 3px color-mix(in srgb,var(--th) 22%,transparent)}
        .tdialog .modal-foot .btn{height:46px;padding:0 22px;border-radius:999px}
        .tdialog .modal-foot .btn-outline{border:0;background:var(--th-soft);color:var(--th2)}
        .tdialog .modal-foot .btn-primary{background:linear-gradient(135deg,var(--th),var(--th2));color:#fff;box-shadow:0 8px 18px color-mix(in srgb,var(--th) 35%,transparent)}
        .tdialog .modal-foot .btn-primary:disabled{opacity:.5;box-shadow:none}
        .tdialog .close:focus{outline:none}
        .tdialog .close:focus-visible{outline:none;background:rgba(255,255,255,.3);border-color:rgba(255,255,255,.6)}
        .tdialog .radio-choice:has(input:checked){border-color:var(--th);background:var(--th-soft);color:var(--th2)}
        .tdialog input[type=radio]{accent-color:var(--th2)}
        @media(max-width:560px){dialog.tdialog{border-radius:20px}.tdialog .modal-head{padding:16px 16px 36px}.tdialog .modal-head h2{font-size:18px}.tdialog .md-icon{width:40px;height:40px}.tdialog .modal-body{padding:20px 16px 14px;border-radius:20px 20px 0 0}.tdialog .modal-foot{padding:12px 16px 16px}.tdialog .modal-foot .btn{flex:1;padding:0 12px}}
        .requirement-tabs{display:grid;grid-template-columns:1fr 1fr;gap:6px;padding:5px;background:#f1f4fa;border-radius:14px}.requirement-tab{min-height:44px;padding:8px 12px;border:0;border-radius:10px;background:transparent;color:var(--muted);font:inherit;font-weight:800;cursor:pointer}.requirement-tab.is-active{background:#fff;color:var(--th2);box-shadow:0 3px 12px rgba(23,35,60,.1)}.requirement-panel[hidden]{display:none}.requirement-panel .modal-body{margin-top:0;border-radius:0}.requirement-panel form{background:#fff}.requirement-tab-wrap{position:relative;margin-top:-22px;padding:18px 22px 0;border-radius:22px 22px 0 0;background:#fff}@media(max-width:560px){.requirement-tab-wrap{padding:16px 16px 0}.requirement-tab{font-size:12px;padding:7px}.requirement-tabs{gap:4px}}

        /* readable interaction activity */
        .interactions-card .card-body{max-height:360px;padding:8px 20px 14px;overflow-y:auto}.interactions-card .feed-item{padding:14px 0}.interactions-card .feed-item h3{font-size:14px;line-height:1.35}.interactions-card .pill{padding:5px 9px;background:#edf4ff;color:#315f9d;font-size:9px;letter-spacing:.06em}.interactions-card .event-call,.interactions-card .event-whatsapp,.interactions-card .event-email{background:#e7f7f1;color:#167457}.interactions-card .event-share{background:#f1ecff;color:#6841b8}.interaction-meta{display:flex;flex-wrap:wrap;align-items:center;gap:4px 8px;margin-top:6px!important;font-size:11px!important;line-height:1.4!important}.interaction-business{color:#43536b;font-weight:700}.interaction-time,.interaction-source{color:#7b8799}.interaction-source{padding:2px 6px;border-radius:999px;background:#f1f4f8;font-size:9px;font-weight:800;letter-spacing:.04em}.interactions-card .request-contact{gap:7px;margin-top:9px;padding-top:9px;font-size:10px}.interactions-card .request-contact a{display:inline-flex;align-items:center;min-height:28px;padding:0 9px;border-radius:8px;background:#f4f7fb;color:#41516a;font-weight:700}.interactions-card .request-contact a:hover{background:#edf4ff;color:#2868d6}
        @media(max-width:560px){.interactions-card .card-body{max-height:420px;padding:6px 16px 12px}.interactions-card .feed-item{padding:13px 0}.interactions-card .feed-item h3{font-size:13px}.interaction-meta{font-size:11px!important}.interactions-card .request-contact{font-size:10px}}

        /* analytics: six stat cards (references given + meetings done added) */
        .owner-analytics .analytics-grid{grid-template-columns:repeat(6,minmax(0,1fr))}
        .meeting-list-dialog .modal-body{max-height:min(520px,60vh);overflow-y:auto}.meeting-list-dialog .feed-item:first-child{padding-top:0}
        @media(max-width:1099.98px){.owner-analytics .analytics-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
        @media(max-width:767.98px){.owner-analytics .analytics-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}

        /* analytics period dropdown */
        .owner-analytics-head{align-items:center}
        .period-form{margin:0}
        .period-form select{min-height:38px;padding:6px 34px 6px 14px;border:1px solid #d6e0ee;border-radius:999px;background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%23397ef6' d='M7 10l5 5 5-5z'/%3E%3C/svg%3E") no-repeat right 10px center/18px;color:#2a5fd0;font:inherit;font-size:13px;font-weight:700;cursor:pointer;-webkit-appearance:none;appearance:none;box-shadow:0 4px 12px rgba(57,126,246,.1)}
        .period-form select:focus{outline:none;border-color:#397ef6;box-shadow:0 0 0 3px rgba(57,126,246,.2)}
        .visually-hidden{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);white-space:nowrap}
        @media(max-width:767.98px){.period-form select{font-size:16px;min-height:40px}}

        /* choice cards (replace two-option dropdowns) */
        .choice-native{position:absolute!important;width:1px!important;height:1px!important;opacity:0;pointer-events:none}
        .choice-group{display:grid;grid-template-columns:1fr 1fr;gap:10px}
        .choice{position:relative;display:flex;align-items:center;gap:10px;min-height:64px;padding:12px;border:1.5px solid #dfe6f0;border-radius:16px;background:#fff;color:var(--ink);font:inherit;text-align:left;cursor:pointer;transition:border-color .15s,background .15s,box-shadow .15s}
        .choice:hover{border-color:color-mix(in srgb,var(--th,#397ef6) 45%,#dfe6f0)}
        .choice:focus-visible{outline:none;box-shadow:0 0 0 3px color-mix(in srgb,var(--th,#397ef6) 25%,transparent)}
        .choice-icon{display:grid;place-items:center;flex:0 0 38px;width:38px;height:38px;border-radius:12px;background:#f1f4f9;transition:background .15s}
        .choice-icon svg{width:20px;height:20px;fill:#7a879b}
        .choice-text{flex:1;min-width:0}
        .choice-text strong{display:block;font-size:14px;line-height:1.25}
        .choice-text small{display:block;margin-top:2px;color:var(--muted);font-size:12px;line-height:1.35}
        .choice-check{position:absolute;top:8px;right:8px;width:18px;height:18px;border:1.5px solid #cfd8e5;border-radius:50%;background:#fff}
        .choice.is-on{border-color:var(--th,#397ef6);background:var(--th-soft,#edf4ff);box-shadow:0 6px 16px color-mix(in srgb,var(--th,#397ef6) 18%,transparent)}
        .choice.is-on .choice-icon{background:linear-gradient(135deg,var(--th,#397ef6),var(--th2,#2a5fd0))}
        .choice.is-on .choice-icon svg{fill:#fff}
        .choice.is-on .choice-check{border-color:var(--th2,#2a5fd0);background:var(--th2,#2a5fd0) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%23fff' d='M9 16.2 4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4Z'/%3E%3C/svg%3E") center/12px no-repeat}
        @media(max-width:560px){
          .choice{flex-direction:column;align-items:flex-start;gap:8px;min-height:0;padding:12px 12px 12px}
          .choice-icon{flex-basis:auto;width:34px;height:34px;border-radius:10px}
          .choice-text strong{font-size:14px}
          .choice-text small{font-size:12px}
        }
        /* polished closed state for the remaining pop-up dropdowns */
        .tdialog select:not(.choice-native){min-height:46px;padding:10px 42px 10px 14px;border:1.5px solid #dfe6f0;border-radius:14px;background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%2364748b' d='M7 10l5 5 5-5z'/%3E%3C/svg%3E") no-repeat right 12px center/20px;color:var(--ink);font-weight:600;cursor:pointer;-webkit-appearance:none;appearance:none;transition:border-color .15s}
        .tdialog select:not(.choice-native):hover{border-color:color-mix(in srgb,var(--th) 45%,#dfe6f0)}
        .tdialog select:not(.choice-native):focus{background-color:var(--th-soft)}

        /* references given: clickable stat + pop-up list */
        .ref-dialog .modal-body{max-height:min(560px,62vh);overflow-y:auto}
        .ref-item{padding:12px 0;border-bottom:1px dashed #e4e9f1}
        .ref-item:first-child{padding-top:0}
        .ref-top{display:flex;align-items:flex-start;justify-content:space-between;gap:10px}
        .ref-top h3{margin:0;font-size:15px;line-height:1.3;overflow-wrap:anywhere}
        .ref-top .pill{flex:0 0 auto}
        .ref-note{margin:5px 0 0;color:var(--muted);font-size:13px;line-height:1.45;overflow-wrap:anywhere}
        .ref-meta{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:4px 10px;margin-top:8px;font-size:12px;color:var(--muted)}
        .ref-to{min-width:0;color:#33435c;overflow-wrap:anywhere}.ref-to b{margin-right:4px;color:#6d45d6}
        .ref-contact{margin-top:4px;color:var(--muted);font-size:12px}
        .ref-more{display:block;width:100%;min-height:44px;margin-top:12px;border:0;border-radius:999px;background:#f2ecff;color:#6d45d6;font:inherit;font-size:14px;font-weight:700;cursor:pointer}
        .ref-more:disabled{opacity:.6}
        .ref-empty,.ref-end{padding:14px 0;color:var(--muted);text-align:center;font-size:13px}
        .ref-skel{height:64px;margin-bottom:10px;border-radius:12px;background:linear-gradient(90deg,#f1f4f9 25%,#f8fafc 50%,#f1f4f9 75%);background-size:200% 100%;animation:refshimmer 1.2s infinite}
        @keyframes refshimmer{from{background-position:200% 0}to{background-position:-200% 0}}

        /* reference card (redesign) */
        .ref-list{display:grid;gap:12px}
        .ref-card{padding:14px;border:1px solid #ebe5fb;border-radius:18px;background:#fff;box-shadow:0 4px 14px rgba(76,48,160,.06)}
        .ref-head{display:flex;align-items:flex-start;gap:11px}
        .ref-avatar{display:grid;place-items:center;flex:0 0 40px;width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,#8a5ce6,#6d45d6);color:#fff;font-size:14px;font-weight:800}
        .ref-avatar.is-public{background:linear-gradient(135deg,#22b26b,#138a50)}
        .ref-avatar svg{width:20px;height:20px;fill:#fff}
        .ref-to{flex:1;min-width:0}
        .ref-to small{display:block;color:#8a96a8;font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase}
        .ref-to strong{display:block;margin-top:1px;color:var(--ink);font-size:15px;line-height:1.3;overflow-wrap:anywhere}
        .ref-to span{display:block;margin-top:1px;color:var(--muted);font-size:12px;overflow-wrap:anywhere}
        .ref-side{display:flex;flex:0 0 auto;flex-direction:column;align-items:flex-end;gap:5px}
        .ref-pill{padding:3px 9px;border-radius:999px;background:#f2ecff;color:#6d45d6;font-size:11px;font-weight:800;letter-spacing:.04em;text-transform:uppercase}
        .ref-pill.public{background:#e4f7ec;color:#138a50}
        .ref-side time{color:#8a96a8;font-size:12px;white-space:nowrap}
        .ref-contact{margin-top:12px;padding:12px 14px;border-left:4px solid #6d45d6;border-radius:12px;background:linear-gradient(135deg,#f6f1ff,#fbf9ff)}
        .ref-contact small{display:block;color:#6d45d6;font-size:11px;font-weight:800;letter-spacing:.06em;text-transform:uppercase}
        .ref-person{margin-top:3px;color:#1d1640;font-size:18px;font-weight:800;line-height:1.25;overflow-wrap:anywhere}
        .ref-lines{display:flex;flex-wrap:wrap;gap:4px 14px;margin-top:5px}
        .ref-lines span{display:inline-flex;align-items:center;gap:6px;color:#33435c;font-size:15px;font-weight:600;overflow-wrap:anywhere}
        .ref-lines svg{flex:0 0 15px;width:15px;height:15px;fill:#6d45d6}
        .ref-actions{display:grid;grid-template-columns:repeat(auto-fit,minmax(0,1fr));gap:6px;margin-top:10px;max-width:330px}
        .ref-actions a{display:flex;align-items:center;justify-content:center;min-height:38px;border-radius:999px;font-size:13px;font-weight:700;text-decoration:none}
        .ref-actions .call{background:#6d45d6;color:#fff}
        .ref-actions .wa{background:#dff7ea;color:#128048}
        .ref-actions .mail{background:#fff;color:#6d45d6;border:1px solid #ddd2f8}
        .ref-subject{margin-top:10px;color:var(--ink);font-size:14px;font-weight:700}
        .ref-note{margin:8px 0 0;padding-left:10px;border-left:2px solid #e4e9f1;color:var(--muted);font-size:13px;line-height:1.5;overflow-wrap:anywhere}

        /* quick cards: activity count instead of icon */
        .qa-icon.qa-num{color:#fff;font-size:24px;font-weight:800;line-height:1;letter-spacing:-.02em;font-variant-numeric:tabular-nums;text-shadow:0 1px 2px rgba(0,0,0,.15)}
        @media(max-width:380px){.qa-icon.qa-num{font-size:21px}}

        /* eye buttons on quick cards */
        .qa-wrap{position:relative;min-width:0}
        .qa-wrap>.qa-card{width:100%;height:100%}
        .qa-eye{position:absolute;top:12px;right:12px;z-index:2;display:grid;place-items:center;width:38px;height:38px;border:1px solid color-mix(in srgb,var(--qa-glow) 60%,#fff);border-radius:12px;background:rgba(255,255,255,.92);cursor:pointer;box-shadow:0 4px 10px rgba(35,55,88,.08);transition:transform .15s,background .15s}
        .qa-eye svg{width:19px;height:19px;fill:color-mix(in srgb,var(--qa-glow) 100%,#000 35%)}
        .qa-eye:hover{transform:scale(1.06);background:#fff}
        .qa-eye:focus-visible{outline:none;box-shadow:0 0 0 3px var(--qa-glow)}
        .qa-reference-wrap{--qa-glow:rgba(122,82,230,.6)}.qa-enquiry-wrap{--qa-glow:rgba(240,112,44,.6)}.qa-review-wrap{--qa-glow:rgba(233,154,22,.6)}.qa-meeting-wrap{--qa-glow:rgba(22,135,90,.6)}
        @media(max-width:380px){.qa-eye{top:10px;right:10px;width:34px;height:34px}}
        /* my enquiries / reviews list */
        .list-dialog .modal-body{max-height:min(560px,62vh);overflow-y:auto}
        .list-card{padding:14px;border:1px solid var(--line);border-radius:16px;background:#fff;box-shadow:0 4px 12px rgba(35,55,88,.05)}
        .list-head{display:flex;align-items:flex-start;justify-content:space-between;gap:10px}
        .list-title{min-width:0}
        .list-title strong{display:block;color:var(--ink);font-size:15px;line-height:1.3;overflow-wrap:anywhere}
        .list-title span{display:block;margin-top:2px;color:var(--muted);font-size:13px;overflow-wrap:anywhere}
        .list-foot{display:flex;align-items:center;gap:6px;margin-top:8px;color:var(--muted);font-size:12px;font-weight:600}
        .status-dot{width:8px;height:8px;border-radius:50%;background:#c3ccd8}.status-dot.is-open{background:#22b26b}
        .stars i{color:#dde3ec;font-style:normal;font-size:15px;letter-spacing:1px}.stars i.on{color:#f5b301}
        .ref-pill.rv{background:#fff7d9;color:#b9840a}

        /* reference card v3: one flat card, divider sections, readable note */
        .ref-list{gap:14px}
        .ref-card{padding:16px 16px 14px;border:1px solid #e9ecf3;border-radius:16px;background:#fff;box-shadow:0 1px 2px rgba(23,35,60,.04),0 8px 24px rgba(23,35,60,.06)}
        .ref-avatar{border-radius:11px;box-shadow:none}
        .ref-contact{margin-top:14px;padding:14px 0 0;border:0;border-top:1px solid #eef1f6;border-radius:0;background:none}
        .ref-contact small,.ref-notebox small{display:block;color:#8a96a8;font-size:11px;font-weight:700;letter-spacing:.07em;text-transform:uppercase}
        .ref-person{margin-top:4px;color:var(--ink);font-size:18px;font-weight:800}
        .ref-lines{gap:6px 16px;margin-top:6px}
        .ref-lines span{color:#26344d;font-size:15px;font-weight:600}
        .ref-lines svg{fill:#8a5ce6}
        .ref-actions{margin-top:12px}
        .ref-actions a{min-height:40px}
        .ref-actions .call{background:#5b3fc4}
        .ref-actions .mail{border-color:#e2dbf7}
        .ref-notebox{margin-top:14px;padding-top:12px;border-top:1px solid #eef1f6}
        .ref-notebox p{margin:5px 0 0;color:#2f3d55;font-size:14px;line-height:1.6;white-space:pre-line;overflow-wrap:anywhere}

        /* visitor: apply for business membership */
        .apply-cta{display:flex;align-items:center;gap:14px;margin:16px 0 4px;padding:16px 18px;border:1px solid #dbe6fb;border-radius:20px;background:linear-gradient(135deg,#f3f7ff,#fbf9ff);box-shadow:0 8px 22px rgba(57,126,246,.08)}
        .apply-icon{display:grid;place-items:center;flex:0 0 48px;width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,#397ef6,#6b52dc);box-shadow:0 8px 16px rgba(57,126,246,.3)}
        .apply-icon svg{width:24px;height:24px;fill:#fff}
        .apply-text{flex:1;min-width:0}
        .apply-text strong{display:block;color:var(--ink);font-size:16px}
        .apply-text span{display:block;margin-top:2px;color:var(--muted);font-size:13px;line-height:1.45}
        .apply-btn{flex:0 0 auto;display:inline-flex;align-items:center;justify-content:center;min-height:44px;padding:0 18px;border-radius:999px;background:linear-gradient(135deg,#397ef6,#6b52dc);color:#fff;font-size:14px;font-weight:700;text-decoration:none;white-space:nowrap}
        .apply-btn.ghost{background:#fff;border:1px solid #d6e0ee;color:#2a5fd0}
        .apply-pending{border-color:#f6dfb5;background:linear-gradient(135deg,#fff8eb,#fffdf8)}
        .apply-pending .apply-icon{background:linear-gradient(135deg,#ffb547,#f08a1c);box-shadow:0 8px 16px rgba(240,138,28,.3)}
        .apply-active{border-color:#cdeedc;background:linear-gradient(135deg,#effaf4,#fbfefc)}
        .apply-active .apply-icon{background:linear-gradient(135deg,#43c990,#16875a);box-shadow:0 8px 16px rgba(22,135,90,.3)}
        @media(max-width:560px){.apply-cta{flex-wrap:wrap}.apply-btn{width:100%}}

        .request-contact .myself{color:#6b7890;font-weight:600}
</style>
</head>
<body>
@php
    $detail = $member->userDetail;
    $displayName = trim(($detail?->ud_first_name ?? '').' '.($detail?->ud_last_name ?? '')) ?: ($member->um_user_name ?: 'Member');
    $initials = collect(explode(' ', $displayName))->filter()->take(2)->map(fn($word) => strtoupper(substr($word,0,1)))->implode('');
    $profilePhotoUrl = $detail?->ud_profile_image ? env('UPLOADS_URL').'user/'.$detail->ud_profile_image : null;
    $hasBusinesses = $member->companies->isNotEmpty();
    $isBusinessOwner = $hasBusinesses;
    $isVisitor = (int) $member->um_utm_id === 1;
@endphp
<div class="shell">
    <header class="topbar">
        <a class="brand" href="{{ url('/') }}">@include('Member.partials.site-logo')</a>
        <div class="top-actions">
            @if($adminAccess)<form class="admin-dashboard-form" method="post" action="{{route('member.admin-dashboard')}}" target="_blank">@csrf<button class="admin-dashboard-link" type="submit">Admin dashboard</button></form>@endif
            <details class="account-menu"><summary><div class="identity"><strong>{{ $displayName }}</strong><span>{{ $isBusinessOwner ? 'Registered Member' : 'Registered Guest' }}</span></div><div class="avatar">@if($profilePhotoUrl)<img src="{{$profilePhotoUrl}}" alt="">@else{{ $initials ?: 'NW' }}@endif</div><span class="account-chevron" aria-hidden="true">▼</span></summary><div class="account-dropdown"><a href="{{route('member.profile.show')}}"><span aria-hidden="true">●</span> My profile</a><a href="{{route('member.members.index')}}"><span aria-hidden="true">♟</span> Members</a><a href="{{route('members.index')}}"><span aria-hidden="true">▦</span> Businesses</a>@if($hasBusinesses)<a href="{{route('member.businesses.index')}}"><span aria-hidden="true">◆</span> Edit my business</a><a href="{{route('member.business-activity.index')}}"><span aria-hidden="true">↗</span> Business activity</a>@endif<a class="account-logout" href="{{route('member.logout')}}"><span aria-hidden="true">↪</span> Log out</a></div></details>
        </div>
    </header>
    <main class="container">
        @if(session('success'))<div class="notice">{{ session('success') }}</div>@endif
        @if(isset($errors) && $errors->any())<div class="notice error"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <section class="hero">
            <h1>Welcome, {{ $displayName }}</h1>
            <div class="hero-actions"><button class="btn btn-light" data-open="reviewModal">Write a review</button><button class="btn btn-primary" data-requirement-tab="someone">Share a reference</button></div>
        </section>

        @unless($hasBusinesses)
            @php
                $mship = $member->membership;
                $mStatus = $canManageBusinesses ? 'active' : ($mship?->membership_status === 'pending' ? 'pending' : 'none');
            @endphp
            <section class="apply-cta apply-{{ $mStatus }}">
                <span class="apply-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M10 6V4a2 2 0 0 1 2-2 2 2 0 0 1 2 2v2h6a2 2 0 0 1 2 2v4H2V8a2 2 0 0 1 2-2Zm2-2v2V4ZM2 14h8v2h4v-2h8v4a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2Z"/></svg></span>
                <div class="apply-text">
                    @if($mStatus === 'active')
                        <strong>Your membership is approved</strong><span>Add your business to get your own page in the directory.</span>
                    @elseif($mStatus === 'pending')
                        <strong>Membership request under review</strong><span>An administrator is verifying your payment. Business tools unlock once it’s approved.</span>
                    @else
                        <strong>Become a business member</strong><span>List your business and receive enquiries and references from the network.</span>
                    @endif
                </div>
                @if($mStatus === 'active')
                    <a class="apply-btn" href="{{ route('member.businesses.create') }}">Add your business →</a>
                @elseif($mStatus === 'pending')
                    <a class="apply-btn ghost" href="{{ route('member.membership.create') }}">View request</a>
                @else
                    <a class="apply-btn" href="{{ route('member.membership.create') }}">Apply now →</a>
                @endif
            </section>
        @endunless

        @if($isVisitor)
        <nav class="qa visitor-qa" aria-label="Visitor directories">
            <a class="qa-card qa-business" href="{{ route('members.index') }}">
                <span class="qa-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 6V4a2 2 0 0 1 2-2h0a2 2 0 0 1 2 2v2h6a2 2 0 0 1 2 2v4H2V8a2 2 0 0 1 2-2Zm2-2v2h0V4ZM2 14h8v2h4v-2h8v4a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2Z"/></svg></span>
                <strong>Businesses</strong><small>Browse the directory</small>
            </a>
            <a class="qa-card qa-members" href="{{ route('member.members.index') }}">
                <span class="qa-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 11a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm-8 1a3 3 0 1 0-3-3 3 3 0 0 0 3 3Zm8 1c-2.7 0-8 1.3-8 4v2h16v-2c0-2.7-5.3-4-8-4Zm-8 0c-.3 0-.7 0-1.1.1A4.7 4.7 0 0 1 9 17v2H1v-2c0-2.2 4.4-4 7-4Z"/></svg></span>
                <strong>Members</strong><small>Find people in the network</small>
            </a>
        </nav>
        @endif

        @if($hasBusinesses)
        {{-- Quick action cards: 2x2 squares on phones/tablets, one row of four on desktop --}}
        <nav class="qa" aria-label="Quick actions">
            <a class="qa-card qa-members" href="{{ route('member.members.index') }}">
                <span class="qa-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 11a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm-8 1a3 3 0 1 0-3-3 3 3 0 0 0 3 3Zm8 1c-2.7 0-8 1.3-8 4v2h16v-2c0-2.7-5.3-4-8-4Zm-8 0c-.3 0-.7 0-1.1.1A4.7 4.7 0 0 1 9 17v2H1v-2c0-2.2 4.4-4 7-4Z"/></svg></span>
                <strong>View members</strong><small>Find people in the network</small>
            </a>
            <a class="qa-card qa-business" href="{{ route('members.index') }}">
                <span class="qa-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 6V4a2 2 0 0 1 2-2h0a2 2 0 0 1 2 2v2h6a2 2 0 0 1 2 2v4H2V8a2 2 0 0 1 2-2Zm2-2v2h0V4ZM2 14h8v2h4v-2h8v4a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2Z"/></svg></span>
                <strong>View businesses</strong><small>Browse the directory</small>
            </a>
            <div class="qa-wrap qa-reference-wrap"><button class="qa-card qa-reference" type="button" data-requirement-tab="someone">
                <span class="qa-icon qa-num" aria-label="{{ number_format($quickCounts['references'] ?? 0) }} references given">{{ number_format($quickCounts['references'] ?? 0) }}</span>
                <strong>Share a reference</strong><small>Refer someone you trust</small>
            </button><button type="button" class="qa-eye" data-open="referencesModal" aria-label="View references given" title="View references given"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5c-5.5 0-9.5 5.3-9.7 5.5a2.4 2.4 0 0 0 0 3C2.5 13.7 6.5 19 12 19s9.5-5.3 9.7-5.5a2.4 2.4 0 0 0 0-3C21.5 10.3 17.5 5 12 5Zm0 11a4 4 0 1 1 0-8 4 4 0 0 1 0 8Zm0-6a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z"/></svg></button></div>
            <div class="qa-wrap qa-enquiry-wrap"><button class="qa-card qa-enquiry" type="button" data-requirement-tab="own">
                <span class="qa-icon qa-num" aria-label="{{ number_format($quickCounts['enquiries'] ?? 0) }} enquiries posted">{{ number_format($quickCounts['enquiries'] ?? 0) }}</span>
                <strong>Post an enquiry</strong><small>Ask the whole network</small>
            </button><button type="button" class="qa-eye" data-list="enquiries" aria-label="View my enquiries" title="View my enquiries"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5c-5.5 0-9.5 5.3-9.7 5.5a2.4 2.4 0 0 0 0 3C2.5 13.7 6.5 19 12 19s9.5-5.3 9.7-5.5a2.4 2.4 0 0 0 0-3C21.5 10.3 17.5 5 12 5Zm0 11a4 4 0 1 1 0-8 4 4 0 0 1 0 8Zm0-6a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z"/></svg></button></div>
            <div class="qa-wrap qa-review-wrap"><button class="qa-card qa-review" type="button" data-open="reviewModal">
                <span class="qa-icon qa-num" aria-label="{{ number_format($quickCounts['reviews'] ?? 0) }} reviews written">{{ number_format($quickCounts['reviews'] ?? 0) }}</span>
                <strong>Write a review</strong><small>Recommend a member business</small>
            </button><button type="button" class="qa-eye" data-list="reviews" aria-label="View my reviews" title="View my reviews"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5c-5.5 0-9.5 5.3-9.7 5.5a2.4 2.4 0 0 0 0 3C2.5 13.7 6.5 19 12 19s9.5-5.3 9.7-5.5a2.4 2.4 0 0 0 0-3C21.5 10.3 17.5 5 12 5Zm0 11a4 4 0 1 1 0-8 4 4 0 0 1 0 8Zm0-6a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z"/></svg></button></div>
            <div class="qa-wrap qa-meeting-wrap"><button class="qa-card qa-meeting" type="button" data-open="meetingModal">
                <span class="qa-icon qa-num" aria-label="{{ number_format($quickCounts['meetings'] ?? 0) }} meetings done">{{ number_format($quickCounts['meetings'] ?? 0) }}</span>
                <strong>Log a meeting</strong><small>Record a one-to-one meeting</small>
            </button><button type="button" class="qa-eye" data-open="meetingListModal" aria-label="View my meetings" title="View my meetings"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5c-5.5 0-9.5 5.3-9.7 5.5a2.4 2.4 0 0 0 0 3C2.5 13.7 6.5 19 12 19s9.5-5.3 9.7-5.5a2.4 2.4 0 0 0 0-3C21.5 10.3 17.5 5 12 5Zm0 11a4 4 0 1 1 0-8 4 4 0 0 1 0 8Zm0-6a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z"/></svg></button></div>
        </nav>

        <section class="owner-analytics" aria-labelledby="pageAnalyticsTitle">
            <div class="owner-analytics-head"><h2 id="pageAnalyticsTitle">Your page analytics</h2><form class="period-form" method="GET" action="{{ route('dashboard.index') }}#pageAnalyticsTitle">@if($search !== '')<input type="hidden" name="search" value="{{ $search }}">@endif<label class="visually-hidden" for="analyticsPeriod">Reporting period</label><select id="analyticsPeriod" name="period" onchange="this.form.submit()">@foreach($analyticsPeriods as $periodKey => $periodLabel)<option value="{{ $periodKey }}" @selected($analyticsPeriod === $periodKey)>{{ $periodLabel }}</option>@endforeach</select><noscript><button type="submit">Show</button></noscript></form></div>
            <div class="analytics-grid">
                <div class="analytics-stat"><strong>{{ number_format($businessAnalytics['page_views']) }}</strong><span>Page views</span></div>
                <div class="analytics-stat"><strong>{{ number_format($businessAnalytics['unique_visitors']) }}</strong><span>Unique visitors</span></div>
                <div class="analytics-stat"><strong>{{ number_format($businessAnalytics['contact_clicks']) }}</strong><span>Contact clicks</span></div>
                <div class="analytics-stat"><strong>{{ number_format($businessAnalytics['enquiries']) }}</strong><span>Enquiries</span></div>
            </div>
        </section>
        @endif

        <div class="grid">
            <div>


                @if($hasBusinesses)
                <section class="card section-gap compact-card tcard th-orange">
                    <div class="tc-band"><span class="th-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 10v4a1 1 0 0 0 1 1h2l1 5h2.5l-1-5H9l7 4V5L9 9H4a1 1 0 0 0-1 1Zm15-1.5v7a3.5 3.5 0 0 0 0-7Z"/></svg></span><div class="tc-title"><h2>Member enquiries</h2></div><button class="btn btn-outline" data-open="enquiryModal">Post <span aria-hidden="true">+</span></button></div>
                    <div class="card-body feed">
                        @forelse($communityEnquiries as $enquiry)
                            @php
                                $requesterName = $enquiry->enm_name ?: 'Networx member';
                                $phoneDigits = preg_replace('/\D+/', '', (string) $enquiry->enm_phone);
                                $whatsappNumber = strlen($phoneDigits) === 10 ? '91'.$phoneDigits : ltrim($phoneDigits, '0');
                                $requirement = "Hi,\n\n{$requesterName} has posted a requirement in our Networx Business Platform group. Please connect with them through my reference.\n\nRequirement details:\n{$enquiry->enm_subject}\n".\Illuminate\Support\Str::limit((string) $enquiry->enm_description, 700).($enquiry->enm_phone ? "\n\nPhone: {$enquiry->enm_phone}" : '').($enquiry->enm_email ? "\nEmail: {$enquiry->enm_email}" : '');
                                $reply = "Hello {$requesterName}, I saw your requirement on Networx: {$enquiry->enm_subject}. I may be able to help. Can we discuss it?";
                            @endphp
                            <article class="feed-item"><div class="feed-top"><h3>{{ $enquiry->enm_subject }}</h3><span class="pill public">Open request</span></div><p>{{ \Illuminate\Support\Str::limit($enquiry->enm_description,150) }} · {{ optional($enquiry->enm_created_at)->diffForHumans() }}</p><div class="request-contact"><strong>{{ $enquiry->enm_name ?: 'Name not provided' }}@if(in_array((int) $enquiry->enm_id, $ownCommunityIds ?? [], true)) <span class="myself">(myself)</span>@endif</strong>@if($enquiry->enm_phone)<a href="tel:{{ preg_replace('/[^0-9+]/', '', $enquiry->enm_phone) }}">☎ {{ $enquiry->enm_phone }}</a>@else<span>Phone not provided</span>@endif @if($enquiry->enm_email)<a href="mailto:{{ $enquiry->enm_email }}">✉ {{ $enquiry->enm_email }}</a>@else<span>Email not provided</span>@endif</div><div class="enquiry-actions">@if($whatsappNumber)<a class="whatsapp" target="_blank" rel="noopener" href="https://wa.me/{{$whatsappNumber}}?text={{rawurlencode($reply)}}" aria-label="Reply to {{$requesterName}} on WhatsApp">WhatsApp</a>@endif<a class="share-whatsapp" target="_blank" rel="noopener" href="https://wa.me/?text={{rawurlencode($requirement)}}" aria-label="Share {{$enquiry->enm_subject}} on WhatsApp">Share on WhatsApp</a></div></article>
                        @empty<button type="button" class="tc-cta" data-open="enquiryModal"><span class="tc-cta-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 2H4a2 2 0 0 0-2 2v18l4-4h14a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2Zm-7 9v3h-2v-3H8V9h3V6h2v3h3v2Z"/></svg></span><span class="tc-cta-text"><strong>No requests yet</strong><small>Post one and members will reply</small></span><span class="tc-cta-arrow" aria-hidden="true">›</span></button>@endforelse
                    </div>
                </section>
                @else
                <section class="card section-gap compact-card tcard th-orange">
                    <div class="tc-band"><span class="th-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 2H4a2 2 0 0 0-2 2v18l4-4h14a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2ZM6 9h12v2H6Zm8 5H6v-2h8Zm4-6H6V6h12Z"/></svg></span><div class="tc-title"><h2>My enquiries</h2><p>What you asked for.</p></div><button class="btn btn-outline" data-open="enquiryModal">Post <span aria-hidden="true">+</span></button></div>
                    <div class="card-body feed">
                        @forelse($myEnquiries as $enquiry)<article class="feed-item"><div class="feed-top"><h3>{{ $enquiry->enm_subject }}</h3><span class="pill">{{ $enquiry->enm_type == 2 ? 'Public' : 'Direct' }}</span></div><p>{{ \Illuminate\Support\Str::limit($enquiry->enm_description,85) }}</p><div class="request-contact"><strong>To:</strong><span>{{ $enquiry->recipient_label }}</span></div></article>@empty<button type="button" class="tc-cta" data-open="enquiryModal"><span class="tc-cta-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 2H4a2 2 0 0 0-2 2v18l4-4h14a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2Zm-7 9v3h-2v-3H8V9h3V6h2v3h3v2Z"/></svg></span><span class="tc-cta-text"><strong>Need something?</strong><small>Post an enquiry and get replies</small></span><span class="tc-cta-arrow" aria-hidden="true">›</span></button>@endforelse
                    </div>
                </section>
                @endif

            </div>

            <aside class="dashboard-sidebar">


            </aside>
        </div>
    </main>
</div>



<dialog class="tdialog th-amber" id="reviewModal"><form method="POST" action="{{ route('member.reviews.store') }}">@csrf<div class="modal-head"><span class="th-icon md-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.8L12 17.8 5.8 21l1.2-6.8-5-4.9 6.9-1Z"/></svg></span><div class="md-title"><h2>Write a review</h2><div class="form-help">Share your experience with a member business.</div></div><button class="close" type="button" data-close aria-label="Close">×</button></div><div class="modal-body form-grid"><div class="form-group full"><label>Business *</label><select name="company_id" required><option value="">Choose a business</option>@foreach($reviewBusinessOptions as $business)<option value="{{ $business->cmp_id }}" @selected((string) old('company_id') === (string) $business->cmp_id)>{{ $business->details?->cmpd_name ?? 'Business #'.$business->cmp_id }}</option>@endforeach</select>@if($reviewBusinessOptions->isEmpty())<span class="form-help">There are no eligible businesses left to review.</span>@endif</div><div class="form-group full"><label>Rating *</label><select name="rating" required><option value="">Choose a rating</option>@for($rating = 5; $rating >= 1; $rating--)<option value="{{ $rating }}" @selected((string) old('rating') === (string) $rating)>{{ str_repeat('★', $rating) }} — {{ $rating }} out of 5</option>@endfor</select></div><div class="form-group full"><label>Your review *</label><textarea name="comment" minlength="3" maxlength="1000" required placeholder="Describe your experience with this business.">{{ old('comment') }}</textarea><span class="form-help">Reviews appear publicly on the business profile.</span></div></div><div class="modal-foot"><button class="btn btn-outline" type="button" data-close>Cancel</button><button class="btn btn-primary" type="submit" @disabled($reviewBusinessOptions->isEmpty())>Publish review</button></div></form></dialog>

<dialog class="tdialog th-purple" id="requirementModal">
    <div class="modal-head"><span class="th-icon md-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 2H4a2 2 0 0 0-2 2v18l4-4h14a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2Zm-7 9v3h-2v-3H8V9h3V6h2v3h3v2Z"/></svg></span><div class="md-title"><h2>Share a requirement</h2><div class="form-help">Choose who the requirement is for.</div></div><button class="close" type="button" data-close aria-label="Close">×</button></div>
    <div class="requirement-tab-wrap"><div class="requirement-tabs" role="tablist" aria-label="Requirement type"><button class="requirement-tab is-active" type="button" role="tab" aria-selected="true" data-requirement-panel="own">My own requirement</button><button class="requirement-tab" type="button" role="tab" aria-selected="false" data-requirement-panel="someone">I know someone who needs this</button></div></div>
    <section class="requirement-panel" data-requirement-form="own"><form method="POST" action="{{ route('member.enquiries.store') }}">@csrf
        <div class="modal-body form-grid"><div class="form-group full"><label>Who should see this?</label><div class="choice-group" role="radiogroup" data-choice-for="visibilityUnified"><button type="button" class="choice" role="radio" aria-checked="false" data-value="public"><span class="choice-text"><strong>All members</strong><small>Everyone can see it</small></span><span class="choice-check" aria-hidden="true"></span></button><button type="button" class="choice" role="radio" aria-checked="false" data-value="private"><span class="choice-text"><strong>One business</strong><small>Send it directly</small></span><span class="choice-check" aria-hidden="true"></span></button></div><select name="visibility" id="visibilityUnified" class="choice-native" tabindex="-1" aria-hidden="true"><option value="public" @selected(old('visibility', 'public') === 'public')>All members</option><option value="private" @selected(old('visibility') === 'private')>One business</option></select></div><div class="form-group full" id="companyFieldUnified" hidden><label>Business</label><select name="company_id" id="enquiryCompanyUnified"><option value="">Choose a business</option>@foreach($businessOptions as $business)<option value="{{ $business->cmp_id }}" @selected((string) old('company_id') === (string) $business->cmp_id)>{{ $business->details?->cmpd_name ?? 'Business #'.$business->cmp_id }}</option>@endforeach</select></div><div class="form-group full"><label>What do you need? *</label><textarea name="requirement" maxlength="2000" required placeholder="Describe the requirement, location, timeline or budget.">{{ old('requirement', old('description')) }}</textarea></div></div>
        <div class="modal-foot"><button class="btn btn-outline" type="button" data-close>Cancel</button><button class="btn btn-primary" type="submit">Share requirement</button></div>
    </form></section>
    <section class="requirement-panel" data-requirement-form="someone" hidden><form method="POST" action="{{ route('member.referrals.store') }}">@csrf
        <div class="modal-body form-grid"><div class="form-group full"><label>Who should see this?</label><div class="choice-group" role="radiogroup" data-choice-for="referralAudienceUnified"><button type="button" class="choice" role="radio" aria-checked="false" data-value="all_members"><span class="choice-text"><strong>All members</strong><small>Whole network</small></span><span class="choice-check" aria-hidden="true"></span></button><button type="button" class="choice" role="radio" aria-checked="false" data-value="individual"><span class="choice-text"><strong>One business</strong><small>Send it directly</small></span><span class="choice-check" aria-hidden="true"></span></button></div><select name="audience" id="referralAudienceUnified" class="choice-native" tabindex="-1" aria-hidden="true" required><option value="all_members" @selected(old('audience', 'all_members') === 'all_members')>All members</option><option value="individual" @selected(old('audience') === 'individual')>One business</option></select></div><div class="form-group full" id="referralCompanyFieldUnified" hidden><label>Business *</label><select name="referral_company_id" id="referralCompanyUnified"><option value="">Choose a member business</option>@foreach($memberOptions as $recipient)<option value="{{ $recipient->company_id }}" @selected((string) old('referral_company_id') === (string) $recipient->company_id)>{{ $recipient->label }}</option>@endforeach</select></div><div class="form-group"><label>Name *</label><input name="name" value="{{ old('name') }}" required placeholder="Full name"></div><div class="form-group"><label>Phone *</label><input name="phone" value="{{ old('phone') }}" required placeholder="Mobile number"></div><div class="form-group full"><label>Email <span style="color:var(--muted);font-weight:400">(optional)</span></label><input name="email" value="{{ old('email') }}" type="email" placeholder="name@example.com"></div><div class="form-group full"><label>What do they need? *</label><textarea name="requirement" maxlength="2000" required placeholder="Describe their requirement clearly.">{{ old('requirement', old('note')) }}</textarea></div></div>
        <div class="modal-foot"><button class="btn btn-outline" type="button" data-close>Cancel</button><button class="btn btn-primary" type="submit">Share requirement</button></div>
    </form></section>
</dialog>

@if($hasBusinesses)
<dialog class="tdialog th-purple ref-dialog" id="referencesModal"><div class="modal-head"><span class="th-icon md-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M18 16a3 3 0 0 0-2.4 1.2l-6.7-3.4a3 3 0 0 0 0-1.6l6.7-3.4A3 3 0 1 0 15 7a3 3 0 0 0 .1.8L8.4 11.2a3 3 0 1 0 0 3.6l6.7 3.4a3 3 0 1 0 2.9-2.2Z"/></svg></span><div class="md-title"><h2>References given</h2><div class="form-help">Leads you passed to other members.</div></div><button class="close" type="button" data-close aria-label="Close">×</button></div><div class="modal-body"><div class="ref-list" id="refList" aria-live="polite"></div><div class="ref-status" id="refStatus"></div><button type="button" class="ref-more" id="refMore" hidden>Load more</button></div></dialog>
<dialog class="tdialog th-orange list-dialog" id="myListModal"><div class="modal-head"><span class="th-icon md-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 6h16v2H4Zm0 5h16v2H4Zm0 5h10v2H4Z"/></svg></span><div class="md-title"><h2 id="myListTitle">My list</h2><div class="form-help" id="myListHelp"></div></div><button class="close" type="button" data-close aria-label="Close">×</button></div><div class="modal-body"><div class="ref-list" id="myListBody" aria-live="polite"></div><div class="ref-status" id="myListStatus"></div><button type="button" class="ref-more" id="myListMore" hidden>Load more</button></div></dialog>
<dialog class="meeting-list-dialog tdialog th-green" id="meetingListModal"><div class="modal-head"><span class="th-icon md-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 2v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2V2h-2v2H9V2Zm-2 8h14v10H5Zm2 2v3h3v-3Z"/></svg></span><div class="md-title"><h2>My meetings</h2><div class="form-help">Your one-to-one meeting history.</div></div><button class="close" type="button" data-close aria-label="Close">×</button></div><div class="modal-body feed">@forelse($recentMeetings as $meeting)@php
    $otherMember = (int) $meeting->reported_by_um_id === (int) $member->um_id ? $meeting->counterpart : $meeting->reporter;
    $otherName = $otherMember?->um_name ?? 'Member';
    $inviterName = (int) $meeting->invited_by_um_id === (int) $member->um_id ? 'You invited' : (($meeting->inviter?->um_name ?? $otherName).' invited');
    $modeLabel = str($meeting->mode)->replace('_', ' ')->title();
@endphp<article class="feed-item"><div class="feed-top"><h3>{{ $otherName }}</h3><span class="pill">{{ $modeLabel }}</span></div><div class="meeting-meta"><span>{{ $meeting->meeting_at->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}</span><span>{{ $inviterName }}</span>@if($meeting->location)<span>{{ $meeting->location }}</span>@endif</div><p class="meeting-details">{{ $meeting->details }}</p>@if($meeting->outcome)<div class="request-contact"><strong>Next steps:</strong><span>{{ $meeting->outcome }}</span></div>@endif</article>@empty<div class="empty">No meetings registered yet.</div>@endforelse</div><div class="modal-foot"><button class="btn btn-outline" type="button" data-close>Close</button></div></dialog>
<dialog class="meeting-dialog tdialog th-green" id="meetingModal"><form method="POST" action="{{ route('member.meetings.store') }}">@csrf<div class="modal-head"><span class="th-icon md-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 2v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2V2h-2v2H9V2Zm-2 8h14v10H5Zm2 2v3h3v-3Z"/></svg></span><div class="md-title"><h2>Register a meeting</h2><div class="form-help">Record a one-to-one meeting.</div></div><button class="close" type="button" data-close aria-label="Close">×</button></div><div class="modal-body form-grid"><div class="form-group full"><label>Meeting with *</label><select name="counterpart_member_id" id="meetingMember" required><option value="">Choose an active member</option>@foreach($meetingMemberOptions as $recipient)<option value="{{ $recipient->member_id }}" data-name="{{ $recipient->name }}" @selected((string) old('counterpart_member_id') === (string) $recipient->member_id)>{{ $recipient->label }}</option>@endforeach</select></div><div class="form-group full"><label>Who invited? *</label><div class="radio-row"><label class="radio-choice"><input type="radio" name="invited_by" value="me" @checked(old('invited_by', 'me') === 'me')><span>I invited</span></label><label class="radio-choice"><input type="radio" name="invited_by" value="other" @checked(old('invited_by') === 'other')><span id="otherInviterLabel">The other member invited</span></label></div></div><div class="form-group"><label>Meeting date and time *</label><input type="datetime-local" name="meeting_at" value="{{ old('meeting_at', now()->timezone('Asia/Kolkata')->format('Y-m-d\TH:i')) }}" required></div><div class="form-group"><label>Meeting mode *</label><select name="mode" id="meetingMode" required><option value="in_person" @selected(old('mode') === 'in_person')>In person</option><option value="online" @selected(old('mode') === 'online')>Online</option><option value="phone" @selected(old('mode') === 'phone')>Phone</option></select></div><div class="form-group full"><label>Location or meeting link</label><input name="location" id="meetingLocation" maxlength="250" value="{{ old('location') }}" placeholder="Venue, city or online meeting link"></div><div class="form-group full"><label>Discussion details *</label><textarea name="details" maxlength="2000" required placeholder="Topics discussed, opportunities identified, and how you can support each other.">{{ old('details') }}</textarea><span class="form-help">Keep sensitive or confidential information out of this note.</span></div><div class="form-group full"><label>Outcome / agreed next steps</label><textarea name="outcome" maxlength="2000" placeholder="Actions agreed by both members">{{ old('outcome') }}</textarea></div><div class="form-group"><label>Follow-up date</label><input type="date" name="follow_up_on" value="{{ old('follow_up_on') }}"></div></div><div class="modal-foot"><button class="btn btn-outline" type="button" data-close>Cancel</button><button class="btn btn-primary" type="submit">Register meeting</button></div></form></dialog>
@endif

<script>
const requirementDialog=document.getElementById('requirementModal');
function openRequirement(tab='own'){document.querySelectorAll('[data-requirement-panel]').forEach(button=>{const active=button.dataset.requirementPanel===tab;button.classList.toggle('is-active',active);button.setAttribute('aria-selected',active?'true':'false')});document.querySelectorAll('[data-requirement-form]').forEach(panel=>panel.hidden=panel.dataset.requirementForm!==tab);window.syncChoices&&window.syncChoices();if(!requirementDialog.open)requirementDialog.showModal()}
document.querySelectorAll('[data-requirement-tab]').forEach(button=>button.addEventListener('click',()=>openRequirement(button.dataset.requirementTab)));
document.querySelectorAll('[data-requirement-panel]').forEach(button=>button.addEventListener('click',()=>openRequirement(button.dataset.requirementPanel)));
document.querySelectorAll('[data-open]').forEach(button=>button.addEventListener('click',()=>button.dataset.open==='referralModal'?openRequirement('someone'):button.dataset.open==='enquiryModal'?openRequirement('own'):document.getElementById(button.dataset.open).showModal()));
document.querySelectorAll('[data-close]').forEach(button=>button.addEventListener('click',()=>button.closest('dialog').close()));
const visibilityUnified=document.getElementById('visibilityUnified'),companyFieldUnified=document.getElementById('companyFieldUnified'),enquiryCompanyUnified=document.getElementById('enquiryCompanyUnified');
function syncVisibilityUnified(){const direct=visibilityUnified.value==='private';companyFieldUnified.hidden=!direct;enquiryCompanyUnified.required=direct;if(!direct)enquiryCompanyUnified.value=''} visibilityUnified.addEventListener('change',syncVisibilityUnified);syncVisibilityUnified();
const referralAudienceUnified=document.getElementById('referralAudienceUnified'),referralCompanyFieldUnified=document.getElementById('referralCompanyFieldUnified'),referralCompanyUnified=document.getElementById('referralCompanyUnified');
function syncReferralAudienceUnified(){const individual=referralAudienceUnified.value==='individual';referralCompanyFieldUnified.hidden=!individual;referralCompanyUnified.required=individual;if(!individual)referralCompanyUnified.value=''} referralAudienceUnified.addEventListener('change',syncReferralAudienceUnified);syncReferralAudienceUnified();
document.querySelectorAll('.choose-business').forEach(button=>button.addEventListener('click',()=>{visibilityUnified.value='private';syncVisibilityUnified();enquiryCompanyUnified.value=button.dataset.id;openRequirement('own')}));
document.querySelectorAll('dialog').forEach(dialog=>dialog.addEventListener('click',event=>{if(event.target===dialog)dialog.close()}));
const meetingMember=document.getElementById('meetingMember'),otherInviterLabel=document.getElementById('otherInviterLabel'),meetingMode=document.getElementById('meetingMode'),meetingLocation=document.getElementById('meetingLocation');
function syncMeetingMember(){if(!meetingMember)return;const selected=meetingMember.options[meetingMember.selectedIndex];otherInviterLabel.textContent=selected?.dataset.name?`${selected.dataset.name} invited`:'The other member invited'}
function syncMeetingMode(){if(!meetingMode)return;const inPerson=meetingMode.value==='in_person';meetingLocation.required=inPerson;meetingLocation.placeholder=inPerson?'Enter venue or location':meetingMode.value==='online'?'Paste meeting link or platform':'Optional call details'}
meetingMember?.addEventListener('change',syncMeetingMember);meetingMode?.addEventListener('change',syncMeetingMode);syncMeetingMember();syncMeetingMode();
@if(session('open_dialog') === 'requirementModal') openRequirement(@json(session('requirement_tab', 'own'))); @elseif(session('open_dialog')) document.getElementById(@json(session('open_dialog')))?.showModal(); @endif
</script>

<script>
/* choice cards drive the hidden native selects (form + existing JS unchanged) */
(()=>{const groups=[...document.querySelectorAll('[data-choice-for]')];
const sync=()=>groups.forEach(g=>{const sel=document.getElementById(g.dataset.choiceFor);g.querySelectorAll('.choice').forEach(c=>{const on=c.dataset.value===sel.value;c.classList.toggle('is-on',on);c.setAttribute('aria-checked',on?'true':'false');c.tabIndex=on?0:-1})});
groups.forEach(g=>{const sel=document.getElementById(g.dataset.choiceFor);const choices=[...g.querySelectorAll('.choice')];
choices.forEach((c,i)=>{c.addEventListener('click',()=>{if(sel.value!==c.dataset.value){sel.value=c.dataset.value;sel.dispatchEvent(new Event('change',{bubbles:true}))}sync()});
c.addEventListener('keydown',e=>{if(!['ArrowRight','ArrowDown','ArrowLeft','ArrowUp'].includes(e.key))return;e.preventDefault();const n=choices[(i+(e.key==='ArrowRight'||e.key==='ArrowDown'?1:choices.length-1))%choices.length];n.click();n.focus()})});
sel.addEventListener('change',sync)});
document.querySelectorAll('[data-open]').forEach(b=>b.addEventListener('click',()=>setTimeout(sync,0)));
window.syncChoices=sync;sync()})();
</script>
<script>
/* References given pop-up: first 10 on open, then "Load more" */
(()=>{const modal=document.getElementById('referencesModal');if(!modal)return;const list=document.getElementById('refList'),status=document.getElementById('refStatus'),more=document.getElementById('refMore');let next=@json(route('member.references-given')),loaded=false,busy=false;
const skeleton='<div class="ref-skel"></div>'.repeat(3);
const load=async()=>{if(busy||!next)return;busy=true;more.disabled=true;more.textContent='Loading…';if(!list.children.length)status.innerHTML=skeleton;
try{const r=await fetch(next,{headers:{Accept:'application/json','X-Requested-With':'XMLHttpRequest'},credentials:'same-origin'});if(!r.ok)throw 0;const d=await r.json();status.innerHTML='';
if(d.empty){status.innerHTML='<div class="ref-empty">You haven’t given any references yet.</div>'}list.insertAdjacentHTML('beforeend',d.html);next=d.next;more.hidden=!next;if(!next&&list.children.length)status.innerHTML='<div class="ref-end">That’s everything.</div>'}
catch(e){status.innerHTML='<div class="ref-empty">Couldn’t load references. Try again.</div>';more.hidden=false}
finally{busy=false;more.disabled=false;more.textContent='Load more'}};
document.querySelectorAll('[data-open="referencesModal"]').forEach(b=>b.addEventListener('click',()=>{if(!loaded){loaded=true;load()}}));
more.addEventListener('click',load)})();
</script>
<script>
/* eye buttons: "My enquiries" / "My reviews" lists (10 at a time, Load more) */
(()=>{const modal=document.getElementById('myListModal');if(!modal)return;const body=document.getElementById('myListBody'),status=document.getElementById('myListStatus'),more=document.getElementById('myListMore'),title=document.getElementById('myListTitle'),help=document.getElementById('myListHelp');
const base=@json(route('member.my-list',['type'=>'__T__']));const meta={enquiries:['My enquiries','Requests you posted to the network.','th-orange','You haven’t posted any enquiries yet.'],reviews:['My reviews','Reviews you wrote for member businesses.','th-amber','You haven’t written any reviews yet.']};
let next=null,busy=false,current=null;
const load=async()=>{if(busy||!next)return;busy=true;more.disabled=true;more.textContent='Loading…';if(!body.children.length)status.innerHTML='<div class="ref-skel"></div>'.repeat(3);
try{const r=await fetch(next,{headers:{Accept:'application/json','X-Requested-With':'XMLHttpRequest'},credentials:'same-origin'});if(!r.ok)throw 0;const d=await r.json();status.innerHTML='';
if(d.empty)status.innerHTML='<div class="ref-empty">'+meta[current][3]+'</div>';body.insertAdjacentHTML('beforeend',d.html);next=d.next;more.hidden=!next;if(!next&&body.children.length)status.innerHTML='<div class="ref-end">That’s everything.</div>'}
catch(e){status.innerHTML='<div class="ref-empty">Couldn’t load the list. Try again.</div>';more.hidden=false}finally{busy=false;more.disabled=false;more.textContent='Load more'}};
document.querySelectorAll('[data-list]').forEach(btn=>btn.addEventListener('click',()=>{const t=btn.dataset.list;current=t;const m=meta[t];title.textContent=m[0];help.textContent=m[1];modal.classList.remove('th-orange','th-amber');modal.classList.add(m[2]);body.innerHTML='';status.innerHTML='';more.hidden=true;next=base.replace('__T__',t);modal.showModal();load()}));
more.addEventListener('click',load)})();
</script></body>
</html>
