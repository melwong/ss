var $=jQuery
var url = window.location.href;
if (url.indexOf('?page=cpmw-metamask-settings') > 0) {
    $('[href=\"admin.php?page=cpmw-metamask-settings\"]').parent('li').addClass('current');
}
var data = $('#adminmenu #toplevel_page_woocommerce ul li a[href=\"admin.php?page=cpmw-metamask-settings\"]')
data.each(function (e) {
    if ($(this).is(':empty')) {
        $(this).hide();
    }
});