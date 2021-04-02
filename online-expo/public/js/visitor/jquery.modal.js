jQuery(function ($) {
    $('.modal .tab').click(function () {
        const group = $(this).parents('.group');
        group.find('.is-active').removeClass('is-active');
        $(this).addClass('is-active');
        group.find('.is-show').removeClass('is-show');
        const index = $(this).attr('id');
        group.find(".panel").eq(index).addClass('is-show');
    });
});


$(function () {
    $(".iframe").colorbox({
        iframe: true,
        width: "80%",
        height: "80%",
        opacity: 0.7
    });
});

$(function () {
    $(".single").colorbox({
        maxWidth: "90%",
        maxHeight: "90%",
        opacity: 0.7
    });
});

$(function () {
    var img_list = $(".campany_img .first_photo div").length;
    if (img_list > 3) {
        $(this).find('.campany_img .first_photo').css('justify-content', 'left');
    } else {
        $(this).find('.campany_img .first_photo').css('justify-content', 'center');
    }
});

$(function () {
    $("div.product_warp").each(function () {
        var img_list = $(this).find('.product_img .first_photo div').length;
        if (img_list > 3) {
            $(this).find('.product_img .first_photo').css('justify-content', 'left');
        } else {
            $(this).find('.product_img .first_photo').css('justify-content', 'center');
        }
    });
});



$(function () {
    $('a[href^="#"]').not('.nosc').not('#id a').click(function () {
        var speed = 400; // ミリ秒
        var href = $(this).attr("href");
        var target = $(href == "#" || href == "" ? 'html' : href);
        var position = target.offset().top - 15;
        $('body,html').animate({ scrollTop: position }, speed, 'swing');
        return false;
    });
});



$(function () {
    $(".chat_start_btn").click(function () {
        $(".chat_body").hide();
        $(".chat_window_body").fadeIn();
    });
});