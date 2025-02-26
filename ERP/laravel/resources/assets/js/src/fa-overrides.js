// when select2 is open focus the input
$(document).on('select2:open', () => {
    setTimeout(() => {
        document.querySelector('.select2-container--open .select2-search__field').focus();
    }, 5)
});