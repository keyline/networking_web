<?php
use Illuminate\Support\Facades\Route;
$routeName = Route::current();
$pageName = explode('/', $routeName->uri());
$pageSegment = $pageName[1];
$pageFunction = count($pageName) > 2 ? $pageName[2] : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?= $head ?>
    <style type="text/css">
        /*#simpletable_filter{
      float: right;
    }
    .simpletable_length label {
      display: inline-flex;
      padding: 10px;
    }
    .dt-buttons button{
      padding: 2px 20px;
      background-color: <?= $generalSetting->theme_color ?>;
      color: <?= $generalSetting->font_color ?>;
      border-radius: 50px;
      border:2px solid <?= $generalSetting->theme_color ?>;
      transition: all .3s ease-in-out;
      box-shadow: 0 9px 20px -10px #a5a5a5;
    }
    .dt-buttons button:hover{
      background: transparent;
      color: <?= $generalSetting->theme_color ?>;
      border:2px solid <?= $generalSetting->theme_color ?>;
    }
    .dataTables_length label,
    /*.dataTables_filter label{
      display: inline-flex;
      align-items: center;
      margin-bottom: 10px;
    }*/
        /*.dataTables_length label select{
      margin: 0 10px;
    }*/
        /*.dataTables_filter label input{
      margin-left: 10px;
    }*/
        /*.pagination{
      justify-content: end;
    }
    .sidebar-nav .nav-content a:hover, .sidebar-nav .nav-content a.active{
      color: #dc3545
    }
    .passeye {
      position: absolute;
      right: 6px;
      top: 50%;
      transform: translate(0, -50%);
    }
      .dataTables_wrapper .dt-buttons {
          float: left;
      }
      .dataTables_length {
          float: left;
          margin-left: 10px;
      }
      .dataTables_filter {
          float: right;
          text-align: right;
          margin-bottom: 10px;
          margin-left: 10px;
      }
      .dataTables_wrapper::after {
          content: "";
          clear: both;
          display: table;
      }
      .dt-buttons button{
        padding: 2px 20px;
        background-color: <?= $generalSetting->theme_color ?>;
        color: <?= $generalSetting->font_color ?>;
        border-radius: 50px;
        border:2px solid <?= $generalSetting->theme_color ?>;
        transition: all .3s ease-in-out;
        box-shadow: 0 9px 20px -10px #a5a5a5;
      }
      .dt-buttons button:hover{
        background: transparent;
        color: <?= $generalSetting->theme_color ?>;
        border:2px solid <?= $generalSetting->theme_color ?>;
      }*/
    </style>
    <style>
        .dt-buttons button {
            padding: 2px 20px;
            background-color: <?=$generalSetting->theme_color ?>;
            color: <?=$generalSetting->font_color ?>;
            border-radius: 50px;
            border: 2px solid <?=$generalSetting->theme_color ?>;
            transition: all .3s ease-in-out;
            box-shadow: 0 9px 20px -10px #a5a5a5;
        }

        .dt-buttons button:hover {
            background: transparent;
            color: <?=$generalSetting->theme_color ?>;
            border: 2px solid <?=$generalSetting->theme_color ?>;
        }

        .dataTables_wrapper .dataTables_top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .dataTables_wrapper .dataTables_buttons {
            flex: 1;
        }

        .dataTables_wrapper .dataTables_length {
            flex: 1;
            margin-left: 10px;
            margin-bottom: 10px;
            margin-top: 10px;
        }

        .dataTables_wrapper .dataTables_filter {
            flex: 1;
            text-align: right;
        }

        .dt-search {
            float: right;
            margin-bottom: 10px;
            margin-left: 10px;
            margin-top: 10px;
        }

        .dt-length {
            margin-top: 10px;
        }

        .dt-paging {
            float: right !important;
        }

        .pagination {
            justify-content: end;
        }
    </style>

    <!-- Page CSS -->
    @yield('extra_css')
    @livewireStyles
</head>

<body class="has-navbar-vertical-aside navbar-vertical-aside-show-xl   footer-offset">
    <script src="<?= env('ADMIN_ASSETS_URL') ?>assets/js/hs.theme-appearance.js"></script>
    <script
        src="<?= env('ADMIN_ASSETS_URL') ?>assets/vendor/hs-navbar-vertical-aside/dist/hs-navbar-vertical-aside-mini-cache.js">
    </script>
    <!-- ========== HEADER ========== -->
    <header id="header"
        class="navbar navbar-expand-lg navbar-fixed navbar-height navbar-container navbar-bordered bg-white  ">
        <?= $header ?>
    </header>
    <!-- ========== END HEADER ========== -->
    <!-- ========== MAIN CONTENT ========== -->
    <!-- Navbar Vertical -->
    <aside
        class="js-navbar-vertical-aside navbar navbar-vertical-aside navbar-vertical navbar-vertical-fixed navbar-expand-xl navbar-bordered bg-white  ">
        <?= $sidebar ?>
    </aside>
    <!-- End Navbar Vertical -->
    <main id="content" role="main" class="main">
        <div class="content container-fluid">
            <?= $maincontent ?>
        </div>
        <!-- Footer -->
        <div class="footer">
            <?= $footer ?>
        </div>
        <!-- End Footer -->
    </main>
    <!-- ========== END MAIN CONTENT ========== -->
    <!-- ONLY DEV -->

    <!-- <script src="<?= env('ADMIN_ASSETS_URL') ?>assets/js/demo.js"></script> -->
    <!-- END ONLY DEV -->

    <!-- JS Implementing Plugins -->
    <script src="<?= env('ADMIN_ASSETS_URL') ?>assets/js/vendor.min.js"></script>
    <!-- <script
        src="<?= env('ADMIN_ASSETS_URL') ?>assets/vendor/chartjs-plugin-datalabels/dist/chartjs-plugin-datalabels.min.js">
    </script> -->
    <!-- JS Front -->
    <script src="<?= env('ADMIN_ASSETS_URL') ?>assets/js/theme.min.js"></script>
    <script src="<?= env('ADMIN_ASSETS_URL') ?>assets/js/hs.theme-appearance-charts.js"></script>

    <script src="<?= env('ADMIN_ASSETS_URL') ?>assets/js/main.js"></script>
    <script src="<?= env('ADMIN_ASSETS_URL') ?>assets/js/jquery.min.js"></script>
    <script src="<?= env('ADMIN_ASSETS_URL') ?>assets/js/pages/data-basic-custom.js"></script>

    <link href="https://cdn.datatables.net/v/dt/dt-2.0.3/datatables.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/v/dt/dt-2.0.3/datatables.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.3/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.1/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.1/js/buttons.dataTables.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.1/js/buttons.print.min.js"></script>

    <!-- JS Plugins Init. -->
    <!-- lightbox -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
    <script>
        $(function() {
            $('.autohide').delay(5000).fadeOut('slow');
        });
        // INITIALIZATION OF DATATABLES
        // =======================================================
        HSCore.components.HSDatatables.init($('#datatable'), {
            select: {
                style: 'multi',
                selector: 'td:first-child input[type="checkbox"]',
                classMap: {
                    checkAll: '#datatableCheckAll',
                    counter: '#datatableCounter',
                    counterInfo: '#datatableCounterInfo'
                }
            },
            language: {
                zeroRecords: `<div class="text-center p-4">
              <img class="mb-3" src="./<?= env('ADMIN_ASSETS_URL') ?>assets/svg/illustrations/oc-error.svg" alt="Image Description" style="width: 10rem;" data-hs-theme-appearance="default">
              <img class="mb-3" src="./<?= env('ADMIN_ASSETS_URL') ?>assets/svg/illustrations-light/oc-error.svg" alt="Image Description" style="width: 10rem;" data-hs-theme-appearance="dark">
            <p class="mb-0">No data to show</p>
            </div>`
            }
        });
        const datatable = HSCore.components.HSDatatables.getItem
        document.querySelectorAll('.js-datatable-filter').forEach(function(item) {
            item.addEventListener('change', function(e) {
                const elVal = e.target.value,
                    targetColumnIndex = e.target.getAttribute('data-target-column-index'),
                    targetTable = e.target.getAttribute('data-target-table');
                HSCore.components.HSDatatables.getItem(targetTable).column(targetColumnIndex).search(
                    elVal !== 'null' ? elVal : '').draw()
            })
        })
    </script>
    <!-- End Style Switcher JS -->
    <link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/43.1.0/ckeditor5.css" />
    <script type="importmap">
    {
        "imports": {
            "ckeditor5": "https://cdn.ckeditor.com/ckeditor5/43.1.0/ckeditor5.js",
            "ckeditor5/": "https://cdn.ckeditor.com/ckeditor5/43.1.0/"
        }
    }
  </script>
    <script type="module">
        import {
            ClassicEditor,
            Essentials,
            Bold,
            Italic,
            Strikethrough,
            Subscript,
            Superscript,
            CodeBlock,
            Font,
            Link,
            List,
            Paragraph,
            Image,
            ImageCaption,
            ImageResize,
            ImageStyle,
            ImageToolbar,
            LinkImage,
            PictureEditing,
            ImageUpload,
            CloudServices,
            CKBox,
            CKBoxImageEdit,
            SourceEditing,
            ImageInsert
        } from 'ckeditor5';

        for (let i = 0; i <= 15; i++) {
            ClassicEditor
                .create(document.querySelector('#ckeditor' + i), {
                    plugins: [Essentials, Bold, Italic, Strikethrough, Subscript, Superscript, CodeBlock, Font, Link,
                        List, Paragraph, Image, ImageToolbar, ImageCaption, ImageStyle, ImageResize, LinkImage,
                        PictureEditing, ImageUpload, CloudServices, CKBox, CKBoxImageEdit, SourceEditing,
                        ImageInsert
                    ],
                    toolbar: {
                        items: [
                            'undo', 'redo',
                            '|',
                            'heading',
                            '|',
                            'sourceEditing',
                            '|',
                            'fontfamily', 'fontsize', 'fontColor', 'fontBackgroundColor', 'formatPainter',
                            '|',
                            'bold', 'italic', 'strikethrough', 'subscript', 'superscript', 'code',
                            '|',
                            'link', 'uploadImage', 'blockQuote', 'codeBlock',
                            '|',
                            'bulletedList', 'numberedList', 'todoList', 'outdent', 'indent',
                            '|',
                            'ckbox', 'ckboxImageEdit', 'toggleImageCaption', 'imageTextAlternative',
                            'ckboxImageEdit',
                            '|',
                            'imageStyle:block',
                            'imageStyle:side',
                            '|',
                            'toggleImageCaption',
                            'imageTextAlternative',
                            '|',
                            'linkImage', 'insertImage', 'insertImageViaUrl'
                        ]
                    },
                    menuBar: {
                        isVisible: true
                    }
                })
                .then( /* ... */ )
                .catch( /* ... */ );
        }
    </script>


    {{-- page js --}}
    @yield('extra_js')
    @livewireScripts
</body>

</html>
