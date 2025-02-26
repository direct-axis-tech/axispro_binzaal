const requiredPlugins = require('./plugins.required');

//
// 3rd-Party Plugins JavaScript Includes
//

module.exports = [

    ...requiredPlugins,

    //////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////
    ///  Optional Plugins Includes(you can remove or add)  ///////////////
    //////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////

    // Select2 - Select2 is a jQuery based replacement for select boxes: https://select2.org/
    'node_modules/select2/dist/js/select2.full.js',
    'resources/assets/core/js/init/select2.init.js',

    // Bootstrap Maxlength - This plugin integrates by default with Twitter bootstrap using badges to display the maximum length of the field where the user is inserting text: https://github.com/mimo84/bootstrap-maxlength
    'node_modules/bootstrap-maxlength/src/bootstrap-maxlength.js',
    'resources/assets/core/plugins/bootstrap-multiselectsplitter/bootstrap-multiselectsplitter.min.js',

    // Date Range Picker - A JavaScript component for choosing date ranges, dates and times: https://www.daterangepicker.com/
    'node_modules/bootstrap-daterangepicker/daterangepicker.js',

    // Datepicker - Bootstrap-datepicker provides a flexible datepicker widget in the Bootstrap style.
    'node_modules/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js',

    // Inputmask - is a javascript library which creates an input mask: https://github.com/RobinHerbots/Inputmask
    'node_modules/inputmask/dist/inputmask.js',
    'node_modules/inputmask/dist/bindings/inputmask.binding.js',

    // noUiSlider - is a lightweight range slider with multi-touch support and a ton of features. It supports non-linear ranges, requires no external dependencies: https://refreshless.com/nouislider/
    'node_modules/nouislider/dist/nouislider.min.js',

    // The autosize - function accepts a single textarea element, or an array or array-like object (such as a NodeList or jQuery collection) of textarea elements: https://www.jacklmoore.com/autosize/
    'node_modules/autosize/dist/autosize.min.js',

    // Clipboard - Copy text to the clipboard shouldn't be hard. It shouldn't require dozens of steps to configure or hundreds of KBs to load: https://clipboardjs.com/
    'node_modules/clipboard/dist/clipboard.min.js',

    // DropzoneJS -  is an open source library that provides drag'n'drop file uploads with image previews: https://www.dropzonejs.com/
    'node_modules/dropzone/dist/min/dropzone.min.js',
    'resources/assets/core/js/init/dropzone.init.js',

    // Quill - is a free, open source WYSIWYG editor built for the modern web. Completely customize it for any need with its modular architecture and expressive API: https://quilljs.com/
    'node_modules/quill/dist/quill.js',

    // Tagify - Transforms an input field or a textarea into a Tags component, in an easy, customizable way, with great performance and small code footprint, exploded with features: https://github.com/yairEO/tagify
    'node_modules/@yaireo/tagify/dist/tagify.polyfills.min.js',
    'node_modules/@yaireo/tagify/dist/tagify.min.js',

    // Apexcharts - modern charting library that helps developers to create beautiful and interactive visualizations for web pages: https://apexcharts.com/
    'node_modules/apexcharts/dist/apexcharts.min.js',

    // Bootstrap Session Timeout - Session timeout and keep-alive control with a nice Bootstrap warning dialog: https://github.com/orangehill/bootstrap-session-timeout
    'resources/assets/core/plugins/bootstrap-session-timeout/dist/bootstrap-session-timeout.min.js',

    // JQuery Idletimer - provides you a way to monitor user activity with a page: https://github.com/thorst/jquery-idletimer
    'resources/assets/core/plugins/jquery-idletimer/idle-timer.min.js',

    // ES6 Promise Polyfill - This is a polyfill of the ES6 Promise: https://github.com/lahmatiy/es6-promise-polyfill
    'node_modules/es6-promise-polyfill/promise.min.js',

    // Sweetalert2 - a beautiful, responsive, customizable and accessible (WAI-ARIA) replacement for JavaScript's popup boxes: https://sweetalert2.github.io/
    'node_modules/sweetalert2/dist/sweetalert2.min.js',
    'resources/assets/core/js/init/sweetalert2.init.js',

    // CountUp.js - is a dependency-free, lightweight JavaScript class that can be used to quickly create animations that display numerical data in a more interesting way.
    'node_modules/countup.js/dist/countUp.umd.js',

    // Chart.js - Simple yet flexible JavaScript charting for designers & developers
    'node_modules/chart.js/dist/chart.js',

    // Tiny slider - for all purposes, inspired by Owl Carousel.
    'node_modules/tiny-slider/dist/min/tiny-slider.js',

    // A lightweight script to animate scrolling to anchor links
    'node_modules/smooth-scroll/dist/smooth-scroll.js',

    // ParsleyJs - A simple, popular and feature rich form validation library
    'node_modules/parsleyjs/dist/parsley.min.js',
    'node_modules/parsleyjs/src/extra/validator/date.js',
    'node_modules/parsleyjs/src/extra/validator/words.js',
    'public/vendor/parsleyjs/extra/validators/comparison.js',
    'resources/assets/core/js/init/parsley.init.js',

    // DataTables - DataTables is a plug-in for the jQuery Javascript library
    'node_modules/datatables.net/js/jquery.dataTables.js',
    'node_modules/datatables.net-bs5/js/dataTables.bootstrap5.min.js',
    'resources/assets/core/js/init/datatables.init.js',
    'node_modules/datatables.net-buttons/js/dataTables.buttons.min.js',
    'node_modules/datatables.net-buttons-bs5/js/buttons.bootstrap5.min.js',
    'node_modules/datatables.net-dateTime/dist/dataTables.dateTime.min.js',
    'node_modules/jszip/dist/jszip.min.js',
    'node_modules/pdfmake/build/pdfmake.min.js',
    'node_modules/pdfmake/build/vfs_fonts.js',
    'node_modules/datatables.net-buttons/js/buttons.html5.min.js',
    'node_modules/datatables.net-buttons/js/buttons.print.min.js',
    'node_modules/datatables.net-buttons/js/buttons.colVis.min.js',
    'node_modules/datatables.net-fixedheader/js/dataTables.fixedHeader.min.js',
    'node_modules/datatables.net-scroller/js/dataTables.scroller.js',
    'node_modules/datatables.net-responsive/js/dataTables.responsive.js',
    'node_modules/datatables.net-responsive-bs5/js/responsive.bootstrap5.js',
    'node_modules/datatables.net-rowgroup/js/dataTables.rowGroup.js',
    'node_modules/datatables.net-rowgroup-bs5/js/rowGroup.bootstrap5.js',
    'node_modules/datatables.net-colreorder/js/dataTables.colReorder.min.js',
    'node_modules/datatables.net-colreorder-bs5/js/colReorder.bootstrap5.min.js',
    'node_modules/datatables.net-fixedcolumns/js/dataTables.fixedColumns.min.js',
    'node_modules/datatables.net-fixedcolumns-bs5/js/fixedColumns.bootstrap5.min.js',
    'node_modules/datatables.net-searchbuilder/js/dataTables.searchBuilder.min.js',
    'node_modules/datatables.net-searchbuilder-bs5/js/searchBuilder.bootstrap5.min.js',
    'node_modules/datatables.net-searchpanes/js/dataTables.searchPanes.min.js',
    'node_modules/datatables.net-searchpanes-bs5/js/searchPanes.bootstrap5.min.js',
    'node_modules/datatables.net-select/js/dataTables.select.min.js',
    'node_modules/datatables.net-select-bs5/js/select.bootstrap5.min.js',

    // jsTree - jsTree is jquery plugin, that provides interactive trees
    'node_modules/jstree/dist/jstree.min.js',

    // bootstrap-select - The jQuery plugin that brings select elements into the 21st century
    'node_modules/bootstrap-select/dist/js/bootstrap-select.min.js',

    // morris - good-looking charts shouldn't be difficult
    'node_modules/raphael/raphael.min.js',
    'node_modules/morris.js/morris.min.js',

    // block-ui - The jQuery BlockUI Plugin lets you simulate synchronous behavior when using AJAX, without locking the browser
    'node_modules/block-ui/jquery.blockUI.js',

    // perfect=scrollbar - Minimalistic but perfect custom scrollbar plugin
    'node_modules/perfect-scrollbar/dist/perfect-scrollbar.min.js'
];