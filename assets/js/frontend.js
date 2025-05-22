
var rm_viewed_ajaxurl = wp_obj_ajax.rm_viewed_ajaxurl;
jQuery(document).ready(function() {
    var rm_recent_ajax = {
        action: "rm_viewed_recent_product_call",
        product_id: wp_obj_ajax.product_id
    };
    jQuery.post(rm_viewed_ajaxurl, rm_recent_ajax, function(e) {})

    var rm_most_ajax = {
        action: "rm_viewed_most_product_call",
        product_id: wp_obj_ajax.product_id
    };
    jQuery.post(rm_viewed_ajaxurl, rm_most_ajax, function(e) {})
})