let $select = $('.table-type1__select[name="occupation"]');
function toggleHideShow() {
    if($select.val() === '無職' || $select.val() === '専業主婦・主夫') {
        $('.is_hide').hide();
    } else {
        $('.is_hide').show();
    }
}

$(document).ready(function() {
    toggleHideShow();

    $select.on('change', function() {
        toggleHideShow();
    });
});


