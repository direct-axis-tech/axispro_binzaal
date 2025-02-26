<?php include_once $path_to_root . '/themes/daxis/kvcodes.inc'; ?>
<style>
  body { zoom: 90%; }
  table.tablestyle td { border: 1px solid #ccc !important; }
  .main-panel { width: 100% !important; }
  textarea[name='memo_'],
  textarea[name='Comments'] {
    height: 12em !important;
    width: 100em !important;
    max-width: 1000px !important;
  }
  .popover { min-width: 25em !important; }
  .popover-body,
  .popover-header {
    color: #000000 !important;
    font-size: 16px !important;
  }
  .popover-header {
    padding: 6px !important;
    color: white !important;
    background: #000000 !important
  }
  #_page_body { padding: 6px !important; }
  .kt-header__top {
    padding-left: 13px !important;
    padding-right: 13px !important;
  }
  #main-panel .content {
    padding-top: 0;
    padding-left: 0;
    padding-right: 0;
  }
  <?php if (!$newThemeMode): ?>
  .kt-header .kt-header__top { height: 88px !important; }
  <?php endif; ?>
  .kt-header__brand-logo-default { width: 175px !important; }
  .kt-header .kt-header__bottom { margin-top: 0 !important; }
  .kt-header-menu .kt-menu__nav > .kt-menu__item > .kt-menu__link > .kt-menu__link-text {
    font-family: sans-serif !important;
    font-weight: bold !important;
  }
  .inner-box-content { padding: 0 !important; }
  .kt-container { padding: 0 !important; }
  .select2-selection__arrow:before { content: \"\" !important; }
  .kt-header__brand-logo-default { width: 150px; }
  .kt-header-mobile__logo img { width: 75px; }
  .kt-portlet .kt-portlet__body {  padding: 0 !important; }
  .kt-iconbox .kt-iconbox__body .kt-iconbox__desc .kt-iconbox__content {
    font-size: 11px !important;
    color: black;
  }
  .kt-subheader-custom .kt-container { padding: 0 !important; }
  .kt-iconbox .kt-iconbox__body .kt-iconbox__desc .kt-iconbox__title { font-size: 18px !important; }
  .kt-menu__link-text { color: #fff !important; }
  #kt_header_menu_wrapper { background: #1a2226; }
  .kt-menu__link { background: none !important; }
  #kt_header_menu {
    margin: 0 auto;
    position: relative;
  }
  .kt-menu__item--here { background: #009688; }
  .kt-menu__item { border-right: 1px solid #fff !important; }
  .menu-icon { margin-right: 3px; }
  .kt-menu__item:first-child { border-left: 1px solid #fff !important; }
  <?php 
    $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    //Sales Menu
    if ( strpos($actual_link, '/view/') !== false || strpos($actual_link, 'popup=1') !== false ) {
        echo '  #kt_header { display: none !important; }';
    }
  ?>
  <?php if ($newThemeMode): ?>#ajaxmark {top: 50%; left: 50%}<?php endif; ?>
</style>

<?php include $path_to_root . '/themes/daxis/ExtraSettings.php'; ?>

<?php if ($_SESSION['wa_current_user']->prefs->user_language == 'AR'): ?>
  <script>
    document.getElementsByTagName("html")[0].setAttribute("dir", "rtl");
  </script>
<?php endif; ?>

  <script> 
    (function() {
      var link = document.querySelector("link[rel*='icon']") || document.createElement('link');
      link.type = 'image/x-icon';
      link.rel = 'shortcut icon';
      <?php if(kv_get_option('favicon') != 'false' && file_exists(dirname(__FILE__).'/images/'.kv_get_option('favicon'))): ?>
      link.href = "<?= $path_to_root.'/themes/'.user_theme().'/images/'.kv_get_option('favicon').'?'.rand(2,5) ?>";
      <?php else: ?>
      link.href = "<?= $path_to_root.'/themes/'.user_theme().'/images/favicon.ico?'.rand(2,5) ?>";
      <?php endif; ?>
      document.getElementsByTagName('head')[0].appendChild(link);
    })();
  </script>
  <meta name="csrf-token" content="<?= csrf_token() ?>">
  <link href="https://fonts.googleapis.com/css?family=Raleway:200,200i,300,400" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset(mix('plugins/global/plugins.bundle.css')) ?>">
  <link rel="stylesheet" href="<?= asset(mix('plugins/global/plugins-custom.bundle.css')) ?>">
  <link href="<?= $path_to_root ?>/../assets/css/style.bundle.css?id=v1.0.1" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset(mix('css/fa-overrides.css')) ?>">
  
  <script src="<?= asset(mix('plugins/global/plugins-minimal.bundle.js')) ?>"></script>
  <script src="<?= asset(mix('js/components/util.js')) ?>"></script>
  <script src="<?= asset(mix('js/fa-scripts.bundle.js')) ?>"></script>
  <script src="<?= $path_to_root ?>/../assets/plugins/general/select2/dist/js/select2.min.js" type="text/javascript"></script>
  <script src="<?= $path_to_root ?>/../assets/js/general.js?id=v1.0.5" type="text/javascript"></script>
  <script>
    var KTAppOptions = {
      "colors": {
        "state": {
          "brand": "#5d78ff",
          "light": "#ffffff",
          "dark": "#282a3c",
          "primary": "#5867dd",
          "success": "#34bfa3",
          "info": "#36a3f7",
          "warning": "#ffb822",
          "danger": "#fd3995"
        },
        "base": {
          "label": ["#c5cbe3", "#a1a8c3", "#3d4465", "#3e4466"],
          "shape": ["#f0f3ff", "#d9dffa", "#afb4d4", "#646c9a"]
        }
      }
    };

    var trans;
    $(function (e) { 
        if ($.fn.select2) {
          $("#code_id").length && $("#code_id").select2();
          $("select#govt_bank_account").length && $("select#govt_bank_account").select2();
          $("#stock_id").length && $("#stock_id").select2();
          $("#account").length && $("#account").select2();
        }

        $(".axispro-lang-btn").click(function (e) {
            var lang = $(this).data("lang");
            $.post(
                "<?= $path_to_root ?>/access/change_language.php",
                { lang: lang }
            ).done(function( data ) {
                window.location.reload();
            });
        });
    });
  </script>
  <?php if (!$newThemeMode): ?>
  <link rel="stylesheet" href="<?= $path_to_root ?>/themes/daxis/css/colorschemes/<?= kv_get_option('color_scheme') != 'false' ? kv_get_option('color_scheme') : 'default'; ?>.css">
  <?php endif; ?>