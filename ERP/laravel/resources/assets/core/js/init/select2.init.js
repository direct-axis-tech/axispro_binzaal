"use strict";

//
// Select2 Initialization
//

$.fn.select2.defaults.set("theme", "bootstrap5");
$.fn.select2.defaults.set("width", "100%");
$.fn.select2.defaults.set("selectionCssClass", ":all:");

$(document).on('select2:open', () => {
    setTimeout(() => {
        document.querySelector('.select2-container--open .select2-search__field').focus();
    }, 5)
});
