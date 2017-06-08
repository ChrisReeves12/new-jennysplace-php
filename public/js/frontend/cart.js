/**
 * cart.js
 *
 * Houses all the necessary functions for the shopping cart
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

// Set namespace
window.newjennysplace = window.newjennysplace || {};
window.newjennysplace.cart = window.newjennysplace.cart || {};
window.newjennysplace.cart.ajax_complete = window.newjennysplace.cart.ajax_complete || false;

// Define functions
window.newjennysplace.cart.show_shipping_methods = function (shipping_methods, current_method)
{
    $("div.shipping_methods_section").css('text-align', 'left').html("");

    $.each(shipping_methods, function(index, value)
    {
        var carrier = shipping_methods[index];

        $.each(carrier, function (index, value)
        {
            var method = carrier[index]

            window.newjennysplace.cart.ajax_complete = true;

            if (current_method.id == method.shipping_method_id && current_method.carrier == method.carrier)
            {
                var checked = "checked='checked'";
            }
            else
            {
                var checked = "";
            }

            // Create price display
            var price_display;
            if (method.price == 0)
            {
                price_display = "Free";
            }
            else
            {
                price_display = "$" + method.price;
            }


            $("div.shipping_methods_section").append("<input "+checked+" type='radio' data-shipping-price='"+method.price+"' data-carrier='"+method.carrier+"' name='shipping_method' value='"+method.shipping_method_id+"'/> "+price_display+" - "+method.name+"<br/>");
        });
    });
};

// Handle same as billing toggle checkbox
$(document).ready(function (e) {

    $("input[name='same_as_billing']").click(function (e) {

        // Copy billing information to shipping form
        $("input[name='shipping_address[first_name]").val($("input[name='billing_address[first_name]").val());
        $("input[name='shipping_address[last_name]").val($("input[name='billing_address[last_name]").val());
        $("input[name='shipping_address[email]").val($("input[name='billing_address[email]").val());
        $("input[name='shipping_address[line_1]").val($("input[name='billing_address[line_1]").val());
        $("input[name='shipping_address[line_2]").val($("input[name='billing_address[line_2]").val());
        $("input[name='shipping_address[city]").val($("input[name='billing_address[city]").val());
        $("input[name='shipping_address[company]").val($("input[name='billing_address[company]").val());
        $("input[name='shipping_address[zipcode]").val($("input[name='billing_address[zipcode]").val());
        $("input[name='shipping_address[phone]").val($("input[name='billing_address[phone]").val());
        $("select[name='shipping_address[state]").val($("select[name='billing_address[state]").val());

    });
});

// Handle removing item from cart
$(document).ready(function (e)
{
    $('.cart_item').find('a.remove').click(function (e)
    {
        e.preventDefault();

        var element_id = $(this).attr('data-element-id');
        window.newjennysplace.utils.showWaitScreen("Updating Cart...Please wait...");
        window.newjennysplace.cart.ajax_complete = false;

        // Post to the server
        $.post('/shopping-cart/remove', {element_id: element_id}, function (msg)
        {
            if (!msg.error)
            {
                // Refresh
                window.location.reload();
            }
            else
            {
                alert(msg.message);
                window.newjennysplace.utils.killWaitScreen();
                window.newjennysplace.cart.ajax_complete = true;
            }
        }, 'json');
    });
});

// Handle updating cart items
$(document).ready(function (e)
{
    $('.cart_item').find('a.update').click(function (e)
    {
        e.preventDefault();

        var element_id = $(this).attr('data-element-id');
        var quantity = $(this).parents('.cart_item').find('select.quantity').val();
        window.newjennysplace.utils.showWaitScreen("Updating Cart...Please wait...");
        window.newjennysplace.cart.ajax_complete = false;

        // Post to the server
        $.post('/shopping-cart/update', {element_id: element_id, quantity: quantity}, function (msg)
        {
            if (!msg.error)
            {
                // Refresh
                window.location.reload();
            }
            else
            {
                alert(msg.message);
                window.newjennysplace.utils.killWaitScreen();
                window.newjennysplace.cart.ajax_complete = true;
            }
        }, 'json');
    });
});

// Handle shipping method changing
$(document).ready(function (e)
{
    $(".shipping_methods_section").delegate("input[name='shipping_method']", 'change', function (e)
    {
        e.preventDefault();

        var shipping_price = $(this).attr('data-shipping-price');
        var shipping_method = $(this).val();
        var carrier = $(this).attr('data-carrier');

        // Update prices
        var show_order_total_error = document.createEvent('Event');
        show_order_total_error.initEvent('show-order-totals-loading', true, true);

        window.dispatchEvent(show_order_total_error);

        window.newjennysplace.cart.ajax_complete = false;

        // Send information to server
        $.ajax({
            url: '/shopping-cart/shippingmethod',
            timeout: 10000,
            dataType: 'json',
            method: 'POST',
            data: {shipping_price: shipping_price, shipping_method: shipping_method, carrier: carrier},
            success: function (msg)
            {
                if (!msg.error)
                {

                    // Update totals component
                    var price_info = {
                        grand_total: msg.total,
                        shipping_cost: msg.shipping_cost,
                        store_credit: msg.store_credit,
                        sub_total: window.newjennysplace.page.cart_total,
                        tax: window.newjennysplace.page.order_tax,
                        order_discount: window.newjennysplace.page.order_discount
                    };

                    // Create custom event
                    var price_change_event = document.createEvent('CustomEvent');
                    price_change_event.initCustomEvent('update-order-totals', true, true, price_info);

                    window.dispatchEvent(price_change_event);
                }
                else
                {
                    var show_order_total_error = document.createEvent('CustomEvent');
                    show_order_total_error.initCustomEvent('show-order-total-error', true, true, msg.message)

                    window.dispatchEvent(show_order_total_error);
                }
            },
            complete: function (jqXHR, textStatus)
            {
                window.newjennysplace.cart.ajax_complete = true;

                if (textStatus === "timeout")
                {
                    var show_order_total_error = document.createEvent('CustomEvent');
                    show_order_total_error.initCustomEvent('show-order-total-error', true, true, "Price loading timed out...try refreshing the browser.")

                    window.dispatchEvent(show_order_total_error);
                }
            }
        });
    });
});


// Handle discount code adding and deleting
$(document).ready(function (e)
{
    $("a.add_discount").click(function (e)
    {
        e.preventDefault();
        var discount_code = $("input[name='discount_code[discount_code]']").val();

        if (discount_code.length == 0)
            return;

        window.newjennysplace.utils.showWaitScreen("Applying Discount...Please wait...");
        window.newjennysplace.cart.ajax_complete = false;

        // Send discount code to server
        $.post('/shopping-cart/adddiscount', {discount_code: discount_code}, function (msg)
        {
            if (!msg.error)
            {
                // Refresh
                window.location.reload();
            }
            else
            {
                alert(msg.message);
                window.newjennysplace.utils.killWaitScreen();
                window.newjennysplace.cart.ajax_complete = true;
            }
        }, 'json');

    });

    $("a.delete_discount").click(function (e)
    {
        e.preventDefault();
        var discount_rel_id = $(this).attr('data-discount-rel-id');
        window.newjennysplace.utils.showWaitScreen("Updating Cart...Please wait...");
        window.newjennysplace.cart.ajax_complete = false;

        // Send discount id to server to remove
        $.post('/shopping-cart/removediscount', {discount_rel_id: discount_rel_id}, function (msg)
        {
            if (!msg.error)
            {
                // Refresh
                window.location.reload();
            }
            else
            {
                alert(msg.message);
                window.newjennysplace.utils.killWaitScreen();
                window.newjennysplace.cart.ajax_complete = true;
            }
        }, 'json');
    });
});

// Handle pay method selections
$(document).ready(function ()
{
    $('input.paypal_submit').click(function (e)
    {
        $("input[name='pay_info[pay_method]']").val('PayPal');
    });

    $('input.card_submit').click(function (e)
    {
        $("input[name='pay_info[pay_method]']").val('Credit/Debit');
    });
});

// Load shipping methods
$(document).ready(function ()
{
    $("div.shipping_methods_section").css('text-align', 'center').html("<img src='/img/layout_images/loader.gif'/><br/><br/>Loading Shipping Methods...Please Wait<br/><br/>");

    window.newjennysplace.cart.ajax_complete = false;

    $.ajax({
        url: "/shopping-cart/shippingmethods",
        method: "POST",
        dataType: "json",
        timeout: 20000,
        success: function (msg)
        {
            if (!msg.error)
            {
                var shipping_methods = msg.shipping_methods;
                window.newjennysplace.cart.show_shipping_methods(shipping_methods, msg.current_method);
            }
            else
            {
                $("div.shipping_methods_section").html("<p>An error occurred while loading shipping rates, please try again later.</p>");
                alert(msg.message);
            }
        },
        complete: function (jqXHR, textStatus)
        {
            window.newjennysplace.cart.ajax_complete = true;

            if (textStatus == "timeout")
            {
                $("div.shipping_methods_section").html("<p>Shipping methods could not be retrieved at this time. Please try again later.</p>");
            }
        }
    });
});

// Handle changing of addresses
$(document).ready(function ()
{
    $("a.save-addresses").click(function (e)
    {
        e.preventDefault();

        // Clear validation messages
        clear_validation_errors();
        $("div.shipping_methods_section").css('text-align', 'center').html("<img src='/img/layout_images/loader.gif'/><br/><br/>Loading Shipping Methods...Please Wait<br/><br/>");

        // Load prices
        var load_event = document.createEvent('Event');
        load_event.initEvent('show-order-totals-loading', true, true);

        window.dispatchEvent(load_event);

        window.newjennysplace.cart.ajax_complete = false;

        $.ajax({
            url: '/shopping-cart/updateaddresses',
            method: 'POST',
            dataType: 'json',
            timeout: 10000,
            data: $("#checkout_form").serialize(),
            success: function (msg)
            {
                if (!msg.error)
                {
                    // Check for validation errors
                    if (typeof msg.validation_errors !== 'undefined')
                    {
                        show_validation_errors(msg.validation_errors);
                    }
                    else
                    {
                        // Load new shipping methods
                        var shipping_methods = msg.shipping_methods;
                        window.newjennysplace.cart.show_shipping_methods(shipping_methods, msg.current_method);

                        // Update totals component
                        var price_info = {
                            grand_total: msg.total,
                            shipping_cost: msg.shipping_cost,
                            sub_total: window.newjennysplace.page.cart_total,
                            tax: window.newjennysplace.page.order_tax,
                            order_discount: window.newjennysplace.page.order_discount
                        };

                        var price_change_event = document.createEvent('CustomEvent');
                        price_change_event.initCustomEvent('update-order-totals', true, true, price_info);

                        window.dispatchEvent(price_change_event);
                    }
                }
                else
                {
                    var error_event = document.createEvent('CustomEvent');
                    error_event.initCustomEvent('show-order-total-error', true, true, msg.message);

                    window.dispatchEvent(error_event);
                }
            },
            complete: function (jqXHR, textStatus)
            {
                window.newjennysplace.cart.ajax_complete = true;

                var load_event = document.createEvent('Event');
                load_event.initEvent('stop-order-totals-loading', true, true);

                window.dispatchEvent(load_event);

                if (textStatus == 'timeout')
                {
                    var error_event = document.createEvent('CustomEvent');
                    error_event.initCustomEvent('show-order-total-error', true, true, "The operation timed out. Please try again later");

                    window.dispatchEvent(error_event);
                }
            }
        });
    });
});

// Handle submitting the order
$(document).ready(function ()
{
    $('#checkout_form').submit(function (e)
    {
        e.preventDefault();

        if (typeof window.newjennysplace.cart.ajax_complete === 'undefined' || window.newjennysplace.cart.ajax_complete === false)
            return false;

        window.newjennysplace.cart.ajax_complete = false;
        window.newjennysplace.utils.showWaitScreen("Please Wait...");

        // Send request to server
        $.ajax({
            url: '',
            method: 'POST',
            timeout: 30000,
            dataType: 'json',
            data: $(this).serialize() + '&task=' + 'place_order',
            success: function (msg)
            {
                if (!msg.error)
                {
                    // Check if this is a redirect to a payment gateway
                    if (typeof msg.redirect !== 'undefined' && msg.redirect === true)
                    {
                        window.location = msg.url;
                    }
                    else
                    {
                        // Redirect user to receipt page
                        window.location = "/shopping-cart/receipt/" + msg.order_number;
                    }
                }
                else
                {
                    alert(msg.message);
                    $("input.card_submit").val("Submit Order");
                }
            },
            complete: function (jqXHR, textStatus)
            {
                window.newjennysplace.cart.ajax_complete = true;
                window.newjennysplace.utils.killWaitScreen();

                if (textStatus == "timeout")
                {
                    alert("The operation timed out, please contact the site administrator.");
                    $("input.card_submit").val("Submit Order");
                }
            }
        });
    });
});