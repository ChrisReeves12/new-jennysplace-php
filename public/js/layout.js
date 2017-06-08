/**
 * layout.js
 *
 * To be included on every page in the template
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

window.newjennysplace = window.newjennysplace ||  {};
window.newjennysplace.utils = window.newjennysplace.utils || {};
window.newjennysplace.init = window.newjennysplace.init || {};

// Shows a wait screen by darkening the screen
window.newjennysplace.utils.showWaitScreen = function (message)
{
    var overlay = $("<div class='wait-overlay' style='background-color: black; opacity: 0.45; position: fixed; z-index: 1000; width: 100%; height: 100%; top: 0; left: 0;'></div>");
    overlay.hide();
    $('body').append(overlay);

    overlay.fadeIn();

    // Show the text
    var text = $("<h1 class='wait-overlay' style='color: white; position: fixed; top: 50%; left: 0; z-index: 2000; text-align: center; width: 100%;'><i class='fa fa-hourglass-2'></i> "+message+"</h1>")
    $('body').append(text);
};

window.newjennysplace.utils.updateQueryStringParameter = function (uri, key, value) {
    var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
    var separator = uri.indexOf('?') !== -1 ? "&" : "?";
    if (uri.match(re)) {
        return uri.replace(re, '$1' + key + "=" + value + '$2');
    }
    else {
        return uri + separator + key + "=" + value;
    }
}

window.newjennysplace.utils.killWaitScreen = function ()
{
    $('.wait-overlay').fadeOut(300, function ()
    {
        $(this).remove();
    });
};

// Additional functionality for formating numbers as currency
Number.prototype.formatMoney = function(c, d, t){
    var j;
    var n = this,
        c = isNaN(c = Math.abs(c)) ? 2 : c,
        d = d == undefined ? "." : d,
        t = t == undefined ? "," : t,
        s = n < 0 ? "-" : "",
        i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "",
        j = (j = i.length) > 3 ? j % 3 : 0;
    return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
};

 // Fancy box
$('.fancybox').fancybox();
$(".fancybox-effects-c").fancybox({
    wrapCSS: 'fancybox-custom',
    closeClick: true,
    openEffect: 'none',
    helpers: {
        title: {
            type: 'inside'
        },
        overlay: {
            css: {
                'background': 'rgba(238,238,238,0.85)'
            }
        }
    }
});
$(".fancybox-effects-d").fancybox({
    padding: 0,
    openEffect: 'elastic',
    openSpeed: 150,
    closeEffect: 'elastic',
    closeSpeed: 150,
    closeClick: true,
    helpers: {
        overlay: {
            css: {
                'background': 'rgba(238,238,238,0.85)'
            }
        }
    }
});

/**
 * Highlight form fields that need to be corrected from validation errors
 * @param validation_errors
 */
function show_validation_errors(validation_errors)
{
    for (var c in validation_errors)
    {
        if (validation_errors.hasOwnProperty(c))
        {
            var input_name = c;

            // Get error messages
            var errors = validation_errors[c];
            var messages_element = $("<ul class='input-error'>");

            for (var e in errors)
            {
                if (errors.hasOwnProperty(e))
                {
                    var error_message = errors[e];
                    messages_element.append("<li>"+error_message+"</li>");
                }
            }

            // Place error messages under input
            $("input[name='"+input_name+"'], select[name='"+input_name+"'], textarea[name='"+input_name+"']").addClass('invalid-input').after(messages_element);
        }
    }
}

/**
 * Clear validation errors on the page
 */
function clear_validation_errors()
{
    $("input").removeClass('invalid-input');
    $(".input-error").remove();
}

/**
 * Initializes banners
 */
window.newjennysplace.init.initialize_banners = function ()
{
    $('.flexslider').each(function () {

        var anim_speed = $(this).attr('data-anim-speed');
        var delay_time = $(this).attr('data-delay');
        var anim_type = $(this).attr('data-anim-type');
        var slide_direction = $(this).attr('data-slide-direction');
        var show_nav = $(this).attr('data-show-nav');
        var show_arrows = $(this).attr('data-show-arrows');

        $(this).flexslider({
            animation: anim_type,
            animationSpeed: parseInt(anim_speed),
            slideshowSpeed: parseInt(delay_time),
            direction: slide_direction,
            controlNav: (show_nav == 1),
            directionNav: (show_arrows == 1)
        });
    });
};

/**
 * Initialize
 */
$(document).ready(function ()
{
    window.newjennysplace.init.initialize_banners();
});

