<!-- Required Meta Tags Always Come First -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <!-- Favicons -->
  <link href="<?=env('UPLOADS_URL').$generalSetting->site_favicon?>" rel="icon">
  <!-- Title -->
  <title><?=$title?></title>
  <!-- Favicon -->
  <link rel="shortcut icon" href="favicon.ico">
  <!-- Font -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&amp;display=swap" rel="stylesheet">
  <!-- CSS Implementing Plugins -->
  <link rel="stylesheet" href="<?=env('ADMIN_ASSETS_URL')?>assets/css/vendor.min.css">
  <!-- CSS Front Template -->
  <link rel="stylesheet" href="<?=env('ADMIN_ASSETS_URL')?>assets/css/theme.minc619.css?v=1.0">
  <link rel="preload" href="<?=env('ADMIN_ASSETS_URL')?>assets/css/theme.min.css" data-hs-appearance="default" as="style">
  <link rel="preload" href="<?=env('ADMIN_ASSETS_URL')?>assets/css/theme-dark.min.css" data-hs-appearance="dark" as="style">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="<?=env('ADMIN_ASSETS_URL')?>assets/css/custom.css">
  <style data-hs-appearance-onload-styles>
    *
    {
      transition: unset !important;
    }
    body
    {
      opacity: 0;
    }
  </style>
  <script>
            window.hs_config = {"autopath":"@@autopath","deleteLine":"hs-builder:delete","deleteLine:build":"hs-builder:build-delete","deleteLine:dist":"hs-builder:dist-delete","previewMode":false,"startPath":"/index.html","vars":{"themeFont":"https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap","version":"?v=1.0"},"layoutBuilder":{"extend":{"switcherSupport":true},"header":{"layoutMode":"default","containerMode":"container-fluid"},"sidebarLayout":"default"},"themeAppearance":{"layoutSkin":"default","sidebarSkin":"default","styles":{"colors":{"primary":"#377dff","transparent":"transparent","white":"#fff","dark":"132144","gray":{"100":"#f9fafc","900":"#1e2022"}},"font":"Inter"}},"languageDirection":{"lang":"en"},"skipFilesFromBundle":{"dist":["<?=env('ADMIN_ASSETS_URL')?>assets/js/hs.theme-appearance.js","<?=env('ADMIN_ASSETS_URL')?>assets/js/hs.theme-appearance-charts.js","<?=env('ADMIN_ASSETS_URL')?>assets/js/demo.js"],"build":["<?=env('ADMIN_ASSETS_URL')?>assets/css/theme.css","<?=env('ADMIN_ASSETS_URL')?>assets/vendor/hs-navbar-vertical-aside/dist/hs-navbar-vertical-aside-mini-cache.js","<?=env('ADMIN_ASSETS_URL')?>assets/js/demo.js","<?=env('ADMIN_ASSETS_URL')?>assets/css/theme-dark.html","<?=env('ADMIN_ASSETS_URL')?>assets/css/docs.css","<?=env('ADMIN_ASSETS_URL')?>assets/vendor/icon-set/style.html","<?=env('ADMIN_ASSETS_URL')?>assets/js/hs.theme-appearance.js","<?=env('ADMIN_ASSETS_URL')?>assets/js/hs.theme-appearance-charts.js","node_modules/chartjs-plugin-datalabels/dist/chartjs-plugin-datalabels.min.html","<?=env('ADMIN_ASSETS_URL')?>assets/js/demo.js"]},"minifyCSSFiles":["<?=env('ADMIN_ASSETS_URL')?>assets/css/theme.css","<?=env('ADMIN_ASSETS_URL')?>assets/css/theme-dark.css"],"copyDependencies":{"dist":{"*<?=env('ADMIN_ASSETS_URL')?>assets/js/theme-custom.js":""},"build":{"*<?=env('ADMIN_ASSETS_URL')?>assets/js/theme-custom.js":"","node_modules/bootstrap-icons/font/*fonts/**":"<?=env('ADMIN_ASSETS_URL')?>assets/css"}},"buildFolder":"","replacePathsToCDN":{},"directoryNames":{"src":"./src","dist":"./dist","build":"./build"},"fileNames":{"dist":{"js":"theme.min.js","css":"theme.min.css"},"build":{"css":"theme.min.css","js":"theme.min.js","vendorCSS":"vendor.min.css","vendorJS":"vendor.min.js"}},"fileTypes":"jpg|png|svg|mp4|webm|ogv|json"}
            window.hs_config.gulpRGBA = (p1) => {
  const options = p1.split(',')
  const hex = options[0].toString()
  const transparent = options[1].toString()
  var c;
  if(/^#([A-Fa-f0-9]{3}){1,2}$/.test(hex)){
    c= hex.substring(1).split('');
    if(c.length== 3){
      c= [c[0], c[0], c[1], c[1], c[2], c[2]];
    }
    c= '0x'+c.join('');
    return 'rgba('+[(c>>16)&255, (c>>8)&255, c&255].join(',')+',' + transparent + ')';
  }
  throw new Error('Bad Hex');
}
            window.hs_config.gulpDarken = (p1) => {
  const options = p1.split(',')
  let col = options[0].toString()
  let amt = -parseInt(options[1])
  var usePound = false
  if (col[0] == "#") {
    col = col.slice(1)
    usePound = true
  }
  var num = parseInt(col, 16)
  var r = (num >> 16) + amt
  if (r > 255) {
    r = 255
  } else if (r < 0) {
    r = 0
  }
  var b = ((num >> 8) & 0x00FF) + amt
  if (b > 255) {
    b = 255
  } else if (b < 0) {
    b = 0
  }
  var g = (num & 0x0000FF) + amt
  if (g > 255) {
    g = 255
  } else if (g < 0) {
    g = 0
  }
  return (usePound ? "#" : "") + (g | (b << 8) | (r << 16)).toString(16)
}
            window.hs_config.gulpLighten = (p1) => {
  const options = p1.split(',')
  let col = options[0].toString()
  let amt = parseInt(options[1])
  var usePound = false
  if (col[0] == "#") {
    col = col.slice(1)
    usePound = true
  }
  var num = parseInt(col, 16)
  var r = (num >> 16) + amt
  if (r > 255) {
    r = 255
  } else if (r < 0) {
    r = 0
  }
  var b = ((num >> 8) & 0x00FF) + amt
  if (b > 255) {
    b = 255
  } else if (b < 0) {
    b = 0
  }
  var g = (num & 0x0000FF) + amt
  if (g > 255) {
    g = 255
  } else if (g < 0) {
    g = 0
  }
  return (usePound ? "#" : "") + (g | (b << 8) | (r << 16)).toString(16)
}
</script>