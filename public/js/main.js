jQuery(document).ready(function ($) {

    // Header fixed and Back to top button
    $(window).scroll(function () {
        if ($(this).scrollTop() > 100) {
            $('.back-to-top').fadeIn('slow');
            $('#header').addClass('header-fixed');
        } else {
            $('.back-to-top').fadeOut('slow');
            $('#header').removeClass('header-fixed');
        }
    });
    $('.back-to-top').click(function () {
        $('html, body').animate({
            scrollTop: 0
        }, 1500, 'easeInOutExpo');
        return false;
    });

    // Initiate the wowjs animation library
    new WOW().init();

    // Initiate superfish on nav menu
    $('.nav-menu').superfish({
        animation: {
            opacity: 'show'
        },
        speed: 400
    });

    // Mobile Navigation
    if ($('#nav-menu-container').length) {
        var $mobile_nav = $('#nav-menu-container').clone().prop({
            id: 'mobile-nav'
        });
        $mobile_nav.find('> ul').attr({
            'class': '',
            'id': ''
        });
        $('body').append($mobile_nav);
        $('body').prepend('<button type="button" id="mobile-nav-toggle"><i class="fa fa-bars"></i></button>');
        $('body').append('<div id="mobile-body-overly"></div>');
        $('#mobile-nav').find('.menu-has-children').prepend('<i class="fa fa-chevron-down"></i>');

        $(document).on('click', '.menu-has-children i', function (e) {
            $(this).next().toggleClass('menu-item-active');
            $(this).nextAll('ul').eq(0).slideToggle();
            $(this).toggleClass("fa-chevron-up fa-chevron-down");
        });

        $(document).on('click', '#mobile-nav-toggle', function (e) {
            $('body').toggleClass('mobile-nav-active');
            $('#mobile-nav-toggle i').toggleClass('fa-times fa-bars');
            $('#mobile-body-overly').toggle();
        });

        $(document).click(function (e) {
            var container = $("#mobile-nav, #mobile-nav-toggle");
            if (!container.is(e.target) && container.has(e.target).length === 0) {
                if ($('body').hasClass('mobile-nav-active')) {
                    $('body').removeClass('mobile-nav-active');
                    $('#mobile-nav-toggle i').toggleClass('fa-times fa-bars');
                    $('#mobile-body-overly').fadeOut();
                }
            }
        });
    } else if ($("#mobile-nav, #mobile-nav-toggle").length) {
        $("#mobile-nav, #mobile-nav-toggle").hide();
    }

    // Smoth scroll on page hash links
    $('.nav-menu a, #mobile-nav a, .scrollto').on('click', function () {
        if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
            var target = $(this.hash);
            if (target.length) {
                var top_space = 0;

                if ($('#header').length) {
                    top_space = $('#header').outerHeight();

                    if (!$('#header').hasClass('header-fixed')) {
                        top_space = top_space - 20;
                    }
                }

                $('html, body').animate({
                    scrollTop: target.offset().top - top_space
                }, 1500, 'easeInOutExpo');

                if ($(this).parents('.nav-menu').length) {
                    $('.nav-menu .menu-active').removeClass('menu-active');
                    $(this).closest('li').addClass('menu-active');
                }

                if ($('body').hasClass('mobile-nav-active')) {
                    $('body').removeClass('mobile-nav-active');
                    $('#mobile-nav-toggle i').toggleClass('fa-times fa-bars');
                    $('#mobile-body-overly').fadeOut();
                }
                return false;
            }
        }
    });

    // Gallery - uses the magnific popup jQuery plugin
    $('.gallery-popup').magnificPopup({
        type: 'image',
        removalDelay: 300,
        mainClass: 'mfp-fade',
        gallery: {
            enabled: true
        },
        zoom: {
            enabled: true,
            duration: 300,
            easing: 'ease-in-out',
            opener: function (openerElement) {
                return openerElement.is('img') ? openerElement : openerElement.find('img');
            }
        }
    });

    // custom code

    //send message
    $("#sendMessageButton").click(function () {

        var mobileNumber = $("#mobileNumber").val();
        var email = $("#email").val();
        var message = $("#message").val();

        // بررسی داده های وارد شده
        if (validateEmail(email) === false) {

            swal({
                    title: "خطا",
                    text: "لطفا ایمیل را صحیح وارد نمایید",
                    type: "warning",
                    confirmButtonText: "بستن",
                    closeOnConfirm: false
                }
            )
        }
        else if (validateMobileNumber(mobileNumber) === false) {

            swal({
                    title: "خطا",
                    text: "لطفا شماره تلفن را صحیح وارد نمایید",
                    type: "warning",
                    confirmButtonText: "بستن",
                    closeOnConfirm: false
                }
            )
        }
        else if (message.length <1) {
            swal({
                    title: "خطا",
                    text: "لطفا پیام را وارد نمایید",
                    type: "warning",
                    confirmButtonText: "بستن",
                    closeOnConfirm: false
                }
            )
        }
        else {
            $.ajax({
                type: "post",
                data: {
                    name: $("#name").val(),
                    lastName: $("#lastName").val(),
                    mobileNumber: $("#mobileNumber").val(),
                    email: $("#email").val(),
                    title: $("#title").val(),
                    message: $("#message").val(),
                },
                url: "/app/api/v1/web/sendMessage",
                success: function (result) {

                    if (result.result == 1) {
                        swal({
                                title: "ثبت پیام",
                                text: "پیام شما با موفقیت ارسال شد",
                                type: "success",
                                confirmButtonText: "بستن",
                                closeOnConfirm: false
                            }
                        )
                    } else {
                        swal({
                                title: "خطا",
                                text: result.message,
                                type: "warning",
                                confirmButtonText: "بستن",
                                closeOnConfirm: false
                            }
                        )
                    }
                },
                error: function () {
                    swal({
                            title: "خطا",
                            text: "متاسفانه پیام شما ارسال نشد",
                            type: "warning",
                            confirmButtonText: "بستن",
                            closeOnConfirm: false
                        }
                    )
                },
                beforeSend: function () {
                    swal({
                            text: "لطفا صبر کنید",
                            showConfirmButton: false
                        }
                    )
                }
            });
        }
    });
});


function validateEmail(sEmail) {

    if ($.trim(sEmail).length === 0)
        return true;
    var filter = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;

    if (filter.test(sEmail)) {
        return true;
    }
    else {
        return false;
    }
}

function validateMobileNumber(mobileNumber) {

    if ($.trim(mobileNumber).length === 0)
        return true;
    else if ($.trim(mobileNumber).length !== 11)
        return false;

    var phoneRegEx = /^((\\+[1-9]{1,4}[ \\-]*)|(\\([0-9]{2,3}\\)[ \\-]*)|([0-9]{2,4})[ \\-]*)*?[0-9]{3,4}?[ \\-]*[0-9]{3,4}?$/;
    if (phoneRegEx.test(mobileNumber)) {
        return true;
    }
    else {
        return false;
    }
}