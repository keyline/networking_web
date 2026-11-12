<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Network | Networx</title>
    <style>
        :root{--ink:#17233c;--muted:#6f7c93;--line:#e4eaf2;--blue:#397ef6;--blue-soft:#edf4ff;--green:#159b72;--bg:#f4f7fb;--card:#fff;--shadow:0 10px 28px rgba(35,55,88,.07)}
        *{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--ink);font-family:Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;font-size:14px}.shell{min-height:100vh}.topbar{height:70px;background:#fff;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;padding:0 max(24px,calc((100vw - 1240px)/2));position:sticky;top:0;z-index:20}.brand{font-size:22px;font-weight:800;letter-spacing:-.6px;color:var(--ink);text-decoration:none}.brand i{font-style:normal;color:#f47a20}.top-actions{display:flex;align-items:center;gap:16px}.identity{text-align:right}.identity strong,.identity span{display:block}.identity span{font-size:12px;color:var(--muted);margin-top:2px}.avatar{width:38px;height:38px;border-radius:12px;background:var(--blue-soft);color:var(--blue);display:grid;place-items:center;font-weight:800}.logout{color:#5e6b82;text-decoration:none;font-weight:700}.container{max-width:1240px;margin:0 auto;padding:28px 24px 50px}.hero{background:linear-gradient(125deg,#172a52,#315caa);border-radius:16px;padding:18px 20px;color:#fff;display:grid;grid-template-columns:1fr auto;gap:18px;align-items:center;box-shadow:var(--shadow)}.hero h1{font-size:22px;margin:0;letter-spacing:-.4px}.hero-actions,.actions{display:flex;gap:8px;flex-wrap:wrap}.hero-actions .btn{height:34px;padding:0 12px;border-radius:8px;font-size:12px}.btn{border:0;border-radius:10px;height:42px;padding:0 16px;display:inline-flex;align-items:center;justify-content:center;gap:8px;font:inherit;font-weight:750;cursor:pointer;text-decoration:none}.btn-primary{background:var(--blue);color:#fff}.btn-light{background:#fff;color:#234678}.btn-outline{background:#fff;border:1px solid #d9e2ef;color:#40506b}.quick{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin:16px 0 22px}.quick button,.quick a{min-height:86px;background:#fff;border:1px solid var(--line);border-radius:14px;padding:16px;text-decoration:none;color:var(--ink);display:flex;gap:13px;align-items:center;text-align:left;cursor:pointer;font:inherit}.quick .icon{width:42px;height:42px;border-radius:11px;background:var(--blue-soft);color:var(--blue);display:grid;place-items:center;font-size:20px}.quick strong,.quick small{display:block}.quick small{color:var(--muted);margin-top:4px}.grid{display:grid;grid-template-columns:minmax(0,1.55fr) minmax(300px,.75fr);gap:18px}.card{background:var(--card);border:1px solid var(--line);border-radius:16px;box-shadow:var(--shadow);overflow:hidden}.card-head{padding:18px 20px;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;gap:16px}.card-head h2{font-size:17px;margin:0}.card-head p{color:var(--muted);margin:4px 0 0;font-size:12px}.card-body{padding:18px 20px}.search{display:flex;gap:9px}.search input{flex:1}.field,input,select,textarea{width:100%;border:1px solid #d9e2ef;border-radius:9px;background:#fff;padding:11px 12px;font:inherit;color:var(--ink);outline:none}input:focus,select:focus,textarea:focus{border-color:var(--blue);box-shadow:0 0 0 3px rgba(57,126,246,.11)}textarea{min-height:92px;resize:vertical}.business-list,.feed{display:grid;gap:10px}.business{border:1px solid var(--line);border-radius:12px;padding:14px;display:flex;align-items:center;gap:13px}.logo{width:46px;height:46px;flex:0 0 46px;border-radius:12px;background:#eef3fa;display:grid;place-items:center;color:#486078;font-weight:800}.business-info{min-width:0;flex:1}.business h3{font-size:14px;margin:0 0 4px}.business p{margin:0;color:var(--muted);font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.business .link{color:var(--blue);font-size:12px;font-weight:750;text-decoration:none}.feed-item{padding:13px 0;border-bottom:1px solid var(--line)}.feed-item:first-child{padding-top:0}.feed-item:last-child{border-bottom:0;padding-bottom:0}.feed-top{display:flex;align-items:center;justify-content:space-between;gap:10px}.feed-item h3{font-size:14px;margin:0}.feed-item p{color:var(--muted);font-size:12px;line-height:1.5;margin:6px 0 0}.pill{display:inline-flex;border-radius:99px;padding:5px 9px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;background:#eef3fa;color:#607088}.pill.public{background:#e9f8f2;color:#168160}.profile{display:grid;gap:12px}.profile-row{display:flex;justify-content:space-between;gap:12px;padding-bottom:11px;border-bottom:1px solid var(--line)}.profile-row span{color:var(--muted);font-size:12px}.profile-row strong{text-align:right;font-size:12px}.empty{text-align:center;padding:24px;color:var(--muted);font-size:13px}.results{margin-top:12px;display:grid;grid-template-columns:repeat(2,1fr);gap:10px}.notice{padding:12px 14px;border-radius:10px;margin-bottom:16px;background:#eaf8f1;color:#17734a}.notice.error{background:#fff0f0;color:#b42318}.notice ul{margin:0;padding-left:18px}dialog{border:0;border-radius:17px;padding:0;width:min(560px,calc(100% - 32px));box-shadow:0 28px 80px rgba(23,35,60,.3)}dialog::backdrop{background:rgba(16,27,48,.58)}.modal-head{padding:19px 22px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:center}.modal-head h2{font-size:18px;margin:0}.close{border:0;background:#eef2f7;width:34px;height:34px;border-radius:9px;cursor:pointer;font-size:20px}.modal-body{padding:20px 22px}.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}.form-group{display:grid;gap:6px}.form-group.full{grid-column:1/-1}.form-group label{font-size:12px;font-weight:750}.modal-foot{padding:15px 22px;border-top:1px solid var(--line);display:flex;justify-content:flex-end;gap:9px}.section-gap{margin-top:18px}@media(max-width:900px){.quick{grid-template-columns:repeat(2,1fr)}.grid{grid-template-columns:1fr}.hero{grid-template-columns:1fr}.results{grid-template-columns:1fr}}@media(max-width:560px){.container{padding:18px 14px 40px}.topbar{padding:0 14px}.identity{display:none}.hero{padding:16px}.hero h1{font-size:20px}.quick{grid-template-columns:1fr}.form-grid{grid-template-columns:1fr}.form-group.full{grid-column:auto}}
        [hidden]{display:none!important}.business-profile{min-width:0;flex:1;display:flex;align-items:center;gap:13px;color:inherit;text-decoration:none;border-radius:9px}.business-profile:hover h3{color:var(--blue)}.business-edit-card{color:inherit;text-decoration:none;transition:border-color .18s,box-shadow .18s,transform .18s}.business-edit-card:hover{border-color:#b9cff5;box-shadow:0 7px 18px rgba(57,126,246,.1);transform:translateY(-1px)}.business-edit-card .edit-hint{color:var(--blue);font-size:12px;font-weight:750}.request-contact{display:flex;flex-wrap:wrap;gap:6px 14px;margin-top:9px;padding-top:9px;border-top:1px dashed var(--line);font-size:11px}.request-contact strong{color:var(--ink)}.request-contact a,.request-contact span{color:#52627b;text-decoration:none}.request-contact a:hover{color:var(--blue)}
        .search-field{position:relative;min-width:0;flex:1}.search-field input{padding-right:38px}.search-clear{position:absolute;top:50%;right:9px;display:grid;width:22px;height:22px;place-items:center;transform:translateY(-50%);border-radius:50%;background:#edf1f7;color:#53627a;text-decoration:none;font-size:15px;font-weight:700;line-height:1}.search-clear:hover{background:#dfe7f2;color:#17233c}.search-clear:focus-visible{outline:2px solid var(--blue);outline-offset:2px}
        .business-actions{display:flex;flex:0 0 auto;gap:7px}.business-actions .btn{height:36px;padding:0 12px;font-size:11px}.business-actions .view-business{border:0;background:linear-gradient(135deg,#397ef6,#7557df);color:#fff;box-shadow:0 5px 13px rgba(68,94,210,.22)}.business-actions .view-business:hover{background:linear-gradient(135deg,#2868dc,#6546cd)}
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
        .qa{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin:16px 0 22px}
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

        /* readable interaction activity */
        .interactions-card .card-body{max-height:360px;padding:8px 20px 14px;overflow-y:auto}.interactions-card .feed-item{padding:14px 0}.interactions-card .feed-item h3{font-size:14px;line-height:1.35}.interactions-card .pill{padding:5px 9px;background:#edf4ff;color:#315f9d;font-size:9px;letter-spacing:.06em}.interactions-card .event-call,.interactions-card .event-whatsapp,.interactions-card .event-email{background:#e7f7f1;color:#167457}.interactions-card .event-share{background:#f1ecff;color:#6841b8}.interaction-meta{display:flex;flex-wrap:wrap;align-items:center;gap:4px 8px;margin-top:6px!important;font-size:11px!important;line-height:1.4!important}.interaction-business{color:#43536b;font-weight:700}.interaction-time,.interaction-source{color:#7b8799}.interaction-source{padding:2px 6px;border-radius:999px;background:#f1f4f8;font-size:9px;font-weight:800;letter-spacing:.04em}.interactions-card .request-contact{gap:7px;margin-top:9px;padding-top:9px;font-size:10px}.interactions-card .request-contact a{display:inline-flex;align-items:center;min-height:28px;padding:0 9px;border-radius:8px;background:#f4f7fb;color:#41516a;font-weight:700}.interactions-card .request-contact a:hover{background:#edf4ff;color:#2868d6}
        @media(max-width:560px){.interactions-card .card-body{max-height:420px;padding:6px 16px 12px}.interactions-card .feed-item{padding:13px 0}.interactions-card .feed-item h3{font-size:13px}.interaction-meta{font-size:11px!important}.interactions-card .request-contact{font-size:10px}}
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
@endphp
<div class="shell">
    <header class="topbar">
        <a class="brand" href="{{ url('/') }}">@include('Member.partials.site-logo')</a>
        <div class="top-actions">
            @if($adminAccess)<form class="admin-dashboard-form" method="post" action="{{route('member.admin-dashboard')}}" target="_blank">@csrf<button class="admin-dashboard-link" type="submit">Admin dashboard</button></form>@endif
            <div class="identity"><strong>{{ $displayName }}</strong><span>{{ $isBusinessOwner ? 'Registered Member' : 'Registered Guest' }}</span></div>
            <div class="avatar">{{ $initials ?: 'NW' }}</div>
            <a class="logout" href="{{ route('member.logout') }}">Log out</a>
        </div>
    </header>
    <main class="container">
        @if(session('success'))<div class="notice">{{ session('success') }}</div>@endif
        @if(isset($errors) && $errors->any())<div class="notice error"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <section class="hero">
            <h1>Welcome, {{ $displayName }}</h1>
            <div class="hero-actions"><button class="btn btn-light" data-open="reviewModal">Write a review</button><button class="btn btn-primary" data-open="referralModal">Share a reference</button></div>
        </section>

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
            <button class="qa-card qa-reference" type="button" data-open="referralModal">
                <span class="qa-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 16a3 3 0 0 0-2.4 1.2l-6.7-3.4a3 3 0 0 0 0-1.6l6.7-3.4A3 3 0 1 0 15 7a3 3 0 0 0 .1.8L8.4 11.2a3 3 0 1 0 0 3.6l6.7 3.4a3 3 0 1 0 2.9-2.2Z"/></svg></span>
                <strong>Share a reference</strong><small>Refer someone you trust</small>
            </button>
            <button class="qa-card qa-enquiry" type="button" data-open="enquiryModal">
                <span class="qa-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 2H4a2 2 0 0 0-2 2v18l4-4h14a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2Zm-7 12h-2v-2h2Zm1.6-5.4-.9.9A2.3 2.3 0 0 0 13 11h-2v-.5a3.4 3.4 0 0 1 1-2.4l1.2-1.3a1.4 1.4 0 0 0 .4-1A1.6 1.6 0 0 0 12 4.3a1.6 1.6 0 0 0-1.6 1.5H8.6a3.4 3.4 0 0 1 6.8 0 2.7 2.7 0 0 1-.8 2.8Z"/></svg></span>
                <strong>Post an enquiry</strong><small>Ask the whole network</small>
            </button>
        </nav>

        <section class="owner-analytics" aria-labelledby="pageAnalyticsTitle">
            <div class="owner-analytics-head"><h2 id="pageAnalyticsTitle">Your page analytics</h2><span>Last 30 days</span></div>
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
                <section class="card tcard th-blue" id="directory">
                    <div class="tc-band"><span class="th-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M10 2a8 8 0 1 0 4.9 14.3l5.4 5.4 1.4-1.4-5.4-5.4A8 8 0 0 0 10 2Zm0 2a6 6 0 1 1 0 12 6 6 0 0 1 0-12Z"/></svg></span><div class="tc-title"><h2>Find people & businesses</h2><p>Search the trusted Networx directory by name, service, email or keyword.</p></div></div>
                    <div class="card-body">
                        <form class="search" method="GET" action="{{ route('dashboard.index') }}"><div class="search-field"><input name="search" value="{{ $search }}" placeholder="Try ‘accountant’, ‘interior designer’ or a member name">@if($search !== '')<a class="search-clear" href="{{ route('dashboard.index') }}" aria-label="Clear search" title="Clear search">×</a>@endif</div><button class="btn btn-primary" type="submit">Search</button></form>
                        @if($search !== '')
                            <div class="results">
                                @forelse($directory as $business)
                                    @php $company = $business->details; @endphp
                                    <article class="business"><a class="business-profile" href="{{ route('business.show', $company->public_slug) }}" aria-label="Open {{ $company?->cmpd_name ?? 'business' }} profile"><div class="logo">{{ strtoupper(substr($company?->cmpd_name ?? 'B',0,2)) }}</div><div class="business-info"><h3>{{ $company?->cmpd_name ?? 'Business' }}</h3><p>{{ $company?->public_description ?: ($company?->cmpd_email ?? 'Networx business') }}</p></div></a><div class="business-actions"><a class="btn view-business" href="{{ route('business.show', $company->public_slug) }}">View business</a><button class="link btn btn-outline choose-business" type="button" data-id="{{ $business->cmp_id }}" data-name="{{ $company?->cmpd_name }}">Enquire</button></div></article>
                                @empty<div class="empty">No matching connections found. Try a broader search.</div>@endforelse
                            </div>
                        @endif
                    </div>
                </section>

                @if($hasBusinesses)
                <section class="card section-gap compact-card tcard th-orange">
                    <div class="tc-band"><span class="th-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 10v4a1 1 0 0 0 1 1h2l1 5h2.5l-1-5H9l7 4V5L9 9H4a1 1 0 0 0-1 1Zm15-1.5v7a3.5 3.5 0 0 0 0-7Z"/></svg></span><div class="tc-title"><h2>Member enquiries</h2><p>Requests you can help with.</p></div><button class="btn btn-outline" data-open="enquiryModal">Post <span aria-hidden="true">+</span></button></div>
                    <div class="card-body feed">
                        @forelse($communityEnquiries as $enquiry)
                            <article class="feed-item"><div class="feed-top"><h3>{{ $enquiry->enm_subject }}</h3><span class="pill public">Open request</span></div><p>{{ \Illuminate\Support\Str::limit($enquiry->enm_description,150) }} · {{ optional($enquiry->enm_created_at)->diffForHumans() }}</p><div class="request-contact"><strong>{{ $enquiry->enm_name ?: 'Name not provided' }}</strong>@if($enquiry->enm_phone)<a href="tel:{{ preg_replace('/[^0-9+]/', '', $enquiry->enm_phone) }}">☎ {{ $enquiry->enm_phone }}</a>@else<span>Phone not provided</span>@endif @if($enquiry->enm_email)<a href="mailto:{{ $enquiry->enm_email }}">✉ {{ $enquiry->enm_email }}</a>@else<span>Email not provided</span>@endif</div></article>
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

                @if($hasBusinesses)
                <section class="card section-gap interactions-card tcard th-teal">
                    <div class="tc-band"><span class="th-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 13h4l2.5-6 4 11 2.5-5H21v-2h-5.2L14 14.2 9.6 2.9 5.7 11H3Z"/></svg></span><div class="tc-title"><h2>Recent business interactions</h2><p>Registered users who viewed or contacted your businesses.</p></div></div>
                    <div class="card-body feed">
                        @forelse($recentInteractions as $interaction)
                            @php $visitorName = trim(($interaction->ud_first_name ?? '').' '.($interaction->ud_last_name ?? '')) ?: ($interaction->um_email_id ? 'Registered user' : 'Public visitor'); @endphp
                            <article class="feed-item"><div class="feed-top"><h3>{{$visitorName}}</h3><span class="pill event-{{$interaction->bae_event_type}}">{{ucfirst($interaction->bae_event_type)}}</span></div><p class="interaction-meta"><span class="interaction-business">{{$interaction->cmpd_name}}</span><span class="interaction-time">{{\Carbon\Carbon::parse($interaction->bae_created_at)->diffForHumans()}}</span><span class="interaction-source">{{$interaction->bae_source?:'WEB'}}</span></p>@if($interaction->um_email_id || $interaction->um_mobile_no)<div class="request-contact">@if($interaction->um_mobile_no)<a href="tel:{{$interaction->um_mobile_no}}">Call {{$interaction->um_mobile_no}}</a>@endif @if($interaction->um_email_id)<a href="mailto:{{$interaction->um_email_id}}">Email {{$interaction->um_email_id}}</a>@endif</div>@endif</article>
                        @empty<div class="empty">No business interactions have been recorded yet.</div>@endforelse
                    </div>
                </section>
                @endif
            </div>

            <aside class="dashboard-sidebar">
                @if($hasBusinesses)
                <section class="card business-priority tcard th-indigo" id="my-business">
                    <div class="tc-band"><span class="th-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M10 6V4a2 2 0 0 1 2-2 2 2 0 0 1 2 2v2h6a2 2 0 0 1 2 2v4H2V8a2 2 0 0 1 2-2Zm2-2v2V4ZM2 14h8v2h4v-2h8v4a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2Z"/></svg></span><div class="tc-title"><h2>{{ $member->companies->count() === 1 ? 'Edit my business' : 'Edit my businesses' }}</h2><p>Keep your public business page accurate and up to date.</p></div></div>
                    <div class="card-body business-list">@foreach($member->companies as $business) @php $company = $business->details; @endphp<a class="business business-edit-card" href="{{route('member.portfolio.edit',$business->portfolioRouteToken())}}" aria-label="Edit {{ $company?->cmpd_name ?? 'business profile' }}"><div class="logo">{{ strtoupper(substr($company?->cmpd_name ?? 'B',0,2)) }}</div><div class="business-info"><h3>{{ $company?->cmpd_name ?? 'Business profile' }}</h3><p>{{ $company?->cmpd_email ?? $member->um_email_id }}</p></div><span class="edit-hint">Edit →</span></a>@endforeach</div>
                </section>
                @endif

                <section class="card tcard th-purple">
                    <div class="tc-band"><span class="th-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-3.3 0-10 1.7-10 5v3h20v-3c0-3.3-6.7-5-10-5Z"/></svg></span><div class="tc-title"><h2>My profile</h2><p>Your identity across the network.</p></div></div>
                    <div class="card-body">
                        <div class="profile-summary"><div class="profile-photo">@if($profilePhotoUrl)<img src="{{$profilePhotoUrl}}" alt="{{$displayName}} profile photo">@else{{$initials ?: 'NW'}}@endif</div><div class="profile"><div class="profile-row"><span>Name</span><strong>{{ $displayName }}</strong></div><div class="profile-row"><span>Email</span><strong>{{ $member->um_email_id }}</strong></div><div class="profile-row"><span>Mobile</span><strong>{{ $member->um_mobile_no ?: 'Not added' }}</strong></div></div></div>
                    </div>
                </section>

                @if($hasBusinesses)
                <section class="card section-gap tcard th-green">
                    <div class="tc-band"><span class="th-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 2v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2V2h-2v2H9V2Zm-2 8h14v10H5Zm2 2v3h3v-3Z"/></svg></span><div class="tc-title"><h2>Meetings</h2><p>Your one-to-one meetings.</p></div><button class="btn btn-outline" type="button" data-open="meetingModal">Add <span aria-hidden="true">+</span></button></div>
                    <div class="card-body feed">
                        @forelse($recentMeetings as $meeting)
                            @php
                                $otherMember = (int) $meeting->reported_by_um_id === (int) $member->um_id ? $meeting->counterpart : $meeting->reporter;
                                $otherName = $otherMember?->um_name ?? 'Member';
                                $inviterName = (int) $meeting->invited_by_um_id === (int) $member->um_id ? 'You invited' : (($meeting->inviter?->um_name ?? $otherName).' invited');
                                $modeLabel = str($meeting->mode)->replace('_', ' ')->title();
                            @endphp
                            <article class="feed-item"><div class="feed-top"><h3>{{ $otherName }}</h3><span class="pill">{{ $modeLabel }}</span></div><div class="meeting-meta"><span>{{ $meeting->meeting_at->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}</span><span>{{ $inviterName }}</span>@if($meeting->location)<span>{{ $meeting->location }}</span>@endif</div><p class="meeting-details">{{ \Illuminate\Support\Str::limit($meeting->details, 110) }}</p>@if($meeting->outcome)<div class="request-contact"><strong>Next steps:</strong><span>{{ \Illuminate\Support\Str::limit($meeting->outcome, 100) }}</span></div>@endif</article>
                        @empty<div class="empty">No one-to-one member meetings registered yet.</div>@endforelse
                    </div>
                </section>
                @endif

                @if($hasBusinesses)
                <section class="card section-gap tcard th-rose">
                    <div class="tc-band"><span class="th-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 2H4a2 2 0 0 0-2 2v18l4-4h14a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2ZM6 9h12v2H6Zm8 5H6v-2h8Zm4-6H6V6h12Z"/></svg></span><div class="tc-title"><h2>My recent enquiries</h2><p>Requests and references you shared.</p></div></div>
                    <div class="card-body feed">
                        @forelse($myEnquiries as $enquiry)<article class="feed-item"><div class="feed-top"><h3>{{ $enquiry->enm_subject }}</h3><span class="pill">{{ $enquiry->enm_type == 2 ? 'Public' : 'Direct' }}</span></div><p>{{ \Illuminate\Support\Str::limit($enquiry->enm_description,85) }}</p><div class="request-contact"><strong>To:</strong><span>{{ $enquiry->recipient_label }}</span></div></article>@empty<div class="empty">You have not posted an enquiry yet.</div>@endforelse
                    </div>
                </section>
                @endif

                <section class="card section-gap tcard th-amber">
                    <div class="tc-band"><span class="th-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2Zm2.2 12.2L7 17l2.8-7.2L17 7Z"/></svg></span><div class="tc-title"><h2>Businesses to explore</h2><p>Recently added to the directory.</p></div></div>
                    <div class="card-body business-list">@forelse($recentBusinesses as $business) @php $company = $business->details; @endphp<article class="business"><div class="logo">{{ strtoupper(substr($company?->cmpd_name ?? 'B',0,2)) }}</div><div class="business-info"><h3>{{ $company?->cmpd_name ?? 'Business' }}</h3><p>{{ $company?->public_description ?? 'Networx member' }}</p></div>@if($company?->public_slug)<a class="link" href="{{route('business.show',$company->public_slug)}}" target="_blank">View profile</a>@endif</article>@empty<div class="empty">No businesses available.</div>@endforelse<a class="business-list-footer" href="{{ route('members.index') }}">Show all businesses →</a></div>
                </section>
            </aside>
        </div>
    </main>
</div>

<dialog class="tdialog th-orange" id="enquiryModal"><form method="POST" action="{{ route('member.enquiries.store') }}">@csrf<div class="modal-head"><span class="th-icon md-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 2H4a2 2 0 0 0-2 2v18l4-4h14a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2Zm-7 9v3h-2v-3H8V9h3V6h2v3h3v2Z"/></svg></span><div class="md-title"><h2>Post an enquiry</h2><div class="form-help">Tell members what you need.</div></div><button class="close" type="button" data-close aria-label="Close">×</button></div><div class="modal-body form-grid"><div class="form-group full"><label>Who should see this?</label><select name="visibility" id="visibility"><option value="public" @selected(old('visibility', 'public') === 'public')>All members — common enquiry</option><option value="private" @selected(old('visibility') === 'private')>One selected business — direct enquiry</option></select></div><div class="form-group full" id="companyField" hidden><label>Business</label><select name="company_id" id="enquiryCompany"><option value="">Choose a business</option>@foreach($businessOptions as $business)<option value="{{ $business->cmp_id }}" @selected((string) old('company_id') === (string) $business->cmp_id)>{{ $business->details?->cmpd_name ?? 'Business #'.$business->cmp_id }}</option>@endforeach</select></div><div class="form-group full"><label>What do you need?</label><input name="subject" value="{{ old('subject') }}" placeholder="Example: Looking for a GST consultant" required></div><div class="form-group full"><label>Details</label><textarea name="description" placeholder="Add useful context, location, timeline or budget." required>{{ old('description') }}</textarea></div></div><div class="modal-foot"><button class="btn btn-outline" type="button" data-close>Cancel</button><button class="btn btn-primary" type="submit">Share enquiry</button></div></form></dialog>

<dialog class="tdialog th-purple" id="referralModal"><form method="POST" action="{{ route('member.referrals.store') }}">@csrf<div class="modal-head"><span class="th-icon md-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M18 16a3 3 0 0 0-2.4 1.2l-6.7-3.4a3 3 0 0 0 0-1.6l6.7-3.4A3 3 0 1 0 15 7a3 3 0 0 0 .1.8L8.4 11.2a3 3 0 1 0 0 3.6l6.7 3.4a3 3 0 1 0 2.9-2.2Z"/></svg></span><div class="md-title"><h2>Share a reference</h2><div class="form-help">Refer someone you trust.</div></div><button class="close" type="button" data-close aria-label="Close">×</button></div><div class="modal-body form-grid"><div class="form-group full"><label>Who should receive this reference? *</label><select name="audience" id="referralAudience" required><option value="all_members" @selected(old('audience', 'all_members') === 'all_members')>All members</option><option value="individual" @selected(old('audience') === 'individual')>An individual business</option></select></div><div class="form-group full" id="referralCompanyField" hidden><label>Choose a member business *</label><select name="referral_company_id" id="referralCompany"><option value="">Choose a member business</option>@foreach($memberOptions as $recipient)<option value="{{ $recipient->company_id }}" @selected((string) old('referral_company_id') === (string) $recipient->company_id)>{{ $recipient->label }}</option>@endforeach</select><small>Member name - Business name (Category name)</small></div><div class="form-group"><label>Contact name</label><input name="name" value="{{ old('name') }}" required placeholder="Full name"></div><div class="form-group"><label>Phone</label><input name="phone" value="{{ old('phone') }}" required placeholder="Mobile number"></div><div class="form-group full"><label>Email <span style="color:var(--muted);font-weight:400">(optional)</span></label><input name="email" value="{{ old('email') }}" type="email" placeholder="name@example.com"></div><div class="form-group full"><label>Why is this a useful connection?</label><textarea name="note" required placeholder="Give members enough context to follow up professionally.">{{ old('note') }}</textarea></div></div><div class="modal-foot"><button class="btn btn-outline" type="button" data-close>Cancel</button><button class="btn btn-primary" type="submit">Share reference</button></div></form></dialog>

<dialog class="tdialog th-amber" id="reviewModal"><form method="POST" action="{{ route('member.reviews.store') }}">@csrf<div class="modal-head"><span class="th-icon md-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.8L12 17.8 5.8 21l1.2-6.8-5-4.9 6.9-1Z"/></svg></span><div class="md-title"><h2>Write a review</h2><div class="form-help">Share your experience with a member business.</div></div><button class="close" type="button" data-close aria-label="Close">×</button></div><div class="modal-body form-grid"><div class="form-group full"><label>Business *</label><select name="company_id" required><option value="">Choose a business</option>@foreach($reviewBusinessOptions as $business)<option value="{{ $business->cmp_id }}" @selected((string) old('company_id') === (string) $business->cmp_id)>{{ $business->details?->cmpd_name ?? 'Business #'.$business->cmp_id }}</option>@endforeach</select>@if($reviewBusinessOptions->isEmpty())<span class="form-help">There are no eligible businesses left to review.</span>@endif</div><div class="form-group full"><label>Rating *</label><select name="rating" required><option value="">Choose a rating</option>@for($rating = 5; $rating >= 1; $rating--)<option value="{{ $rating }}" @selected((string) old('rating') === (string) $rating)>{{ str_repeat('★', $rating) }} — {{ $rating }} out of 5</option>@endfor</select></div><div class="form-group full"><label>Your review *</label><textarea name="comment" minlength="3" maxlength="1000" required placeholder="Describe your experience with this business.">{{ old('comment') }}</textarea><span class="form-help">Reviews appear publicly on the business profile.</span></div></div><div class="modal-foot"><button class="btn btn-outline" type="button" data-close>Cancel</button><button class="btn btn-primary" type="submit" @disabled($reviewBusinessOptions->isEmpty())>Publish review</button></div></form></dialog>

@if($hasBusinesses)
<dialog class="meeting-dialog tdialog th-green" id="meetingModal"><form method="POST" action="{{ route('member.meetings.store') }}">@csrf<div class="modal-head"><span class="th-icon md-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 2v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2V2h-2v2H9V2Zm-2 8h14v10H5Zm2 2v3h3v-3Z"/></svg></span><div class="md-title"><h2>Register a meeting</h2><div class="form-help">Record a one-to-one meeting.</div></div><button class="close" type="button" data-close aria-label="Close">×</button></div><div class="modal-body form-grid"><div class="form-group full"><label>Meeting with *</label><select name="counterpart_member_id" id="meetingMember" required><option value="">Choose an active member</option>@foreach($meetingMemberOptions as $recipient)<option value="{{ $recipient->member_id }}" data-name="{{ $recipient->name }}" @selected((string) old('counterpart_member_id') === (string) $recipient->member_id)>{{ $recipient->label }}</option>@endforeach</select></div><div class="form-group full"><label>Who invited? *</label><div class="radio-row"><label class="radio-choice"><input type="radio" name="invited_by" value="me" @checked(old('invited_by', 'me') === 'me')><span>I invited</span></label><label class="radio-choice"><input type="radio" name="invited_by" value="other" @checked(old('invited_by') === 'other')><span id="otherInviterLabel">The other member invited</span></label></div></div><div class="form-group"><label>Meeting date and time *</label><input type="datetime-local" name="meeting_at" value="{{ old('meeting_at', now()->timezone('Asia/Kolkata')->format('Y-m-d\TH:i')) }}" required></div><div class="form-group"><label>Meeting mode *</label><select name="mode" id="meetingMode" required><option value="in_person" @selected(old('mode') === 'in_person')>In person</option><option value="online" @selected(old('mode') === 'online')>Online</option><option value="phone" @selected(old('mode') === 'phone')>Phone</option></select></div><div class="form-group full"><label>Location or meeting link</label><input name="location" id="meetingLocation" maxlength="250" value="{{ old('location') }}" placeholder="Venue, city or online meeting link"></div><div class="form-group full"><label>Discussion details *</label><textarea name="details" maxlength="2000" required placeholder="Topics discussed, opportunities identified, and how you can support each other.">{{ old('details') }}</textarea><span class="form-help">Keep sensitive or confidential information out of this note.</span></div><div class="form-group full"><label>Outcome / agreed next steps</label><textarea name="outcome" maxlength="2000" placeholder="Actions agreed by both members">{{ old('outcome') }}</textarea></div><div class="form-group"><label>Follow-up date</label><input type="date" name="follow_up_on" value="{{ old('follow_up_on') }}"></div></div><div class="modal-foot"><button class="btn btn-outline" type="button" data-close>Cancel</button><button class="btn btn-primary" type="submit">Register meeting</button></div></form></dialog>
@endif

<script>
document.querySelectorAll('[data-open]').forEach(button=>button.addEventListener('click',()=>document.getElementById(button.dataset.open).showModal()));
document.querySelectorAll('[data-close]').forEach(button=>button.addEventListener('click',()=>button.closest('dialog').close()));
const visibility=document.getElementById('visibility'),companyField=document.getElementById('companyField'),enquiryCompany=document.getElementById('enquiryCompany');
function syncVisibility(){const direct=visibility.value==='private';companyField.hidden=!direct;enquiryCompany.required=direct;if(!direct)enquiryCompany.value=''} visibility.addEventListener('change',syncVisibility);syncVisibility();
const referralAudience=document.getElementById('referralAudience'),referralCompanyField=document.getElementById('referralCompanyField'),referralCompany=document.getElementById('referralCompany');
function syncReferralAudience(){const individual=referralAudience.value==='individual';referralCompanyField.hidden=!individual;referralCompany.required=individual;if(!individual)referralCompany.value=''} referralAudience.addEventListener('change',syncReferralAudience);syncReferralAudience();
document.querySelectorAll('.choose-business').forEach(button=>button.addEventListener('click',()=>{visibility.value='private';syncVisibility();document.getElementById('enquiryCompany').value=button.dataset.id;document.getElementById('enquiryModal').showModal()}));
document.querySelectorAll('dialog').forEach(dialog=>dialog.addEventListener('click',event=>{if(event.target===dialog)dialog.close()}));
const meetingMember=document.getElementById('meetingMember'),otherInviterLabel=document.getElementById('otherInviterLabel'),meetingMode=document.getElementById('meetingMode'),meetingLocation=document.getElementById('meetingLocation');
function syncMeetingMember(){if(!meetingMember)return;const selected=meetingMember.options[meetingMember.selectedIndex];otherInviterLabel.textContent=selected?.dataset.name?`${selected.dataset.name} invited`:'The other member invited'}
function syncMeetingMode(){if(!meetingMode)return;const inPerson=meetingMode.value==='in_person';meetingLocation.required=inPerson;meetingLocation.placeholder=inPerson?'Enter venue or location':meetingMode.value==='online'?'Paste meeting link or platform':'Optional call details'}
meetingMember?.addEventListener('change',syncMeetingMember);meetingMode?.addEventListener('change',syncMeetingMode);syncMeetingMember();syncMeetingMode();
@if(session('open_dialog')) document.getElementById(@json(session('open_dialog')))?.showModal(); @endif
</script>
</body>
</html>
