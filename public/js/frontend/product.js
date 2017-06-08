/**
 * product.js
 *
 * This file houses frontend functionality for the product screen
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

// Handle add to cart
$(document).ready(function (e)
{
    $('a.add-to-cart').click(function (e)
    {
        e.preventDefault();

        // Collect variables to send to server
        var sku_id = $(this).attr('data-sku-id');
        var product_id = $(this).attr('data-product-id');
        var qty = $('select.qty').val();
        var options = {};

        // Get options
        $("input[type='radio']:checked").each(function ()
        {
            var option_id = $(this).attr('name');
            var option_value_id = $(this).val();

            options[option_id] = option_value_id;
        });

        // Show wait screen
        window.newjennysplace.utils.showWaitScreen("Please Wait...");

        $.post('/shopping-cart/add',
            {
                sku_id: sku_id,
                product_id: product_id,
                qty: qty,
                options: options
            },
            function (msg)
            {
                if (!msg.error)
                {
                    // Update total display
                    var cart_update_event = document.createEvent('CustomEvent');
                    cart_update_event.initCustomEvent('update-cart-display', true, true, {price: msg.total, qty: msg.count, product: msg.product});

                    window.dispatchEvent(cart_update_event);

                    window.newjennysplace.utils.killWaitScreen();
                }
                else
                {
                    alert(msg.message);
                    window.newjennysplace.utils.killWaitScreen();
                }
            }, 'json');
    });
});


// Handle changing of sku choice
$(document).ready(function () {

    $("div.product_option").find("input[type='radio']").change(function (e) {

        // Check if all options are filled in
        var selected_options = $("div.product_option > input[type='radio']:checked");
        var num_of_options = $("div.product_option").length;
        var num_of_selected_options = selected_options.length;
        var selected_info = {};

        if (num_of_options == num_of_selected_options)
        {
            for (var i in selected_options)
            {
                if (selected_options.hasOwnProperty(i))
                {
                    if (isNaN(i))
                        continue;

                    var option = selected_options[i];
                    selected_info[parseInt($(option).attr('name'))] = parseInt($(option).val());
                }
            }

            $("div.main-photo").html("<img src='" + window.newjennysplace.page.image_path + "/layout_images/loader.gif'/>");

            $.get('', {task: 'change_sku', product_id: window.newjennysplace.product.id, selected_info: selected_info}, function (msg) {

                if (!msg.error) {
                    // Change main photo
                    if (msg.sku_info) {
                        if (msg.sku_info.image_url) {
                            var photo_html = "<a title='"+msg.sku_info.image_title+"' class='fancybox-effects-d' data-fancybox-group='gallery' href='"+msg.sku_info.image_url+"'>";
                            photo_html += "<img alt='"+msg.sku_info.image_alt+"' class='img-responsive' src='"+msg.sku_info.image_url+"'/>";
                            photo_html += "</a>";

                            $("div.main-photo").html(photo_html);
                        }

                        // Update stock status
                        $(".product-status").html(msg.sku_info.status);
                    }
                    else
                    {
                        // Update stock status
                        $(".product-status").html("Out Of Stock");
                        photo_html = "<img alt='No photo available' title='No photo available' class='img-responsive' src='"+window.newjennysplace.page.image_path+"/layout_images/no_photo.jpg'/>";
                        $("div.main-photo").html(photo_html);
                    }
                }
                else
                {
                    alert(msg.message);
                }

            }, 'json');
        }
    });
});


