<!-- begin::Global Config(global config for global JS sciprts) -->
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
</script>

<script src="<?= asset(mix('plugins/global/plugins.bundle.js')) ?>"></script>
<script src="assets/js/scripts.bundle.js?id=v1.0.3" type="text/javascript"></script>
<script src="<?= asset(mix('/js/fa-scripts.bundle.js')) ?>"></script>
<script src="assets/js/pages/dashboard.js?id=v1.1.2" type="text/javascript"></script>
<script src="assets/js/axispro.js?id=v1.0.3" type="text/javascript"></script>
<script src="assets/js/general.js?id=v1.0.5" type="text/javascript"></script>
<script>
    $( document ).ajaxComplete(function( event, xhr, settings ) {
        try {
            var responseText = xhr.responseText;
            var responseJson = $.parseJSON(responseText);
            if(responseJson.status === 'LOGIN_TIME_OUT') {
                swal.fire(
                    'Login TimeOut !',
                    responseJson.msg,
                    'error'
                ).then(function () {
                    window.location.reload();
                });
            }
        } catch (e) {
            return;
        }
    });
</script>

<?php 
    if (isset($GLOBALS['__FOOT__']) && is_array($GLOBALS['__FOOT__'])) {
        foreach ($GLOBALS['__FOOT__'] as $data) {
            echo $data;
        }
    }
?>

<!--end::Page Scripts