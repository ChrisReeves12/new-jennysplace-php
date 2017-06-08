/**
 * order.js
 *
 * Handles javascript for the single order page
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

$(document).ready(function (e)
{
    $('button.save_order').click(function (e)
    {
        // Gather information on order
        var order_info = {};

        // Billing address
        var billing_addres = {};
        var billing_address_div = $("div.billing_address");
        billing_addres.first_name = billing_address_div.find('input.first-name').val();
        billing_addres.last_name = billing_address_div.find('input.last-name').val();
        billing_addres.line_1 = billing_address_div.find('input.line1').val();
        billing_addres.line_2 = billing_address_div.find('input.line2').val();
        billing_addres.city = billing_address_div.find('input.city').val();
        billing_addres.state = billing_address_div.find('input.state').val();
        billing_addres.zipcode = billing_address_div.find('input.zipcode').val();
        billing_addres.company = billing_address_div.find('input.company').val();
        billing_addres.email = billing_address_div.find('input.email').val();
        order_info.billing_address = billing_addres;

        // Shipping address
        var shipping_addres = {};
        var shipping_address_div = $("div.shipping_address");
        shipping_addres.first_name = shipping_address_div.find('input.first-name').val();
        shipping_addres.last_name = shipping_address_div.find('input.last-name').val();
        shipping_addres.line_1 = shipping_address_div.find('input.line1').val();
        shipping_addres.line_2 = shipping_address_div.find('input.line2').val();
        shipping_addres.city = shipping_address_div.find('input.city').val();
        shipping_addres.state = shipping_address_div.find('input.state').val();
        shipping_addres.zipcode = shipping_address_div.find('input.zipcode').val();
        shipping_addres.company = shipping_address_div.find('input.company').val();
        shipping_addres.email = shipping_address_div.find('input.email').val();
        order_info.shipping_address = shipping_addres;

        // Sales order info
        var sale_info = {};
        var sale_info_div = $("#order_info");
        sale_info.auth_code = sale_info_div.find('input.auth_code').val();
        sale_info.tax = sale_info_div.find('input.tax').val();
        sale_info.tracking_number = sale_info_div.find('input.tracking_number').val();
        sale_info.shipping_cost = sale_info_div.find('input.shipping_cost').val();
        sale_info.total_discount = sale_info_div.find('input.total_discount').val();
        sale_info.status = sale_info_div.find('select.status').val();
        sale_info.shipping_method = sale_info_div.find('select.shipping_method').val();
        order_info.sale_info = sale_info;

        // Order line items
        var line_items = {};
        var line_items_table = $("#order_line_items").find('table');
        line_items_table.find('tr.line_item').each(function (index, value)
        {
            var line_id = $(this).attr('data-line-id');
            line_items[line_id] = {
                quantity: $(this).find('input.item_qty').val(),
                price: $(this).find('input.item_price').val(),
                tax: $(this).find('input.item_tax').val(),
                name: $(this).find('input.item_name').val(),
                weight: $(this).find('input.item_weight').val()
            };
        });
        order_info.line_items = line_items;

        // Send order information to server
        $.post('', {task: 'save_order', order_info: order_info}, function (msg)
        {
            if (!msg.error)
            {
                window.location.reload();
            }
            else
            {
                alert(msg.message);
            }
        }, 'json');
    });
});

// Handle deleting and adding of line items
$(document).ready(function(e)
{
    // Delete
    $('button.delete_line_items').click(function (e)
    {
        var line_item_ids = [];

        $("#order_line_items").find("tr.line_item input[type='checkbox']:checked").each(function (index, value)
        {
            line_item_ids.push($(this).attr('data-line-id'));
        });

        if (confirm("Are you sure you want to delete the following line items?"))
        {
            if (line_item_ids.length > 0)
            {
                $.post('', {task: 'delete_order_lines', order_line_ids: line_item_ids}, function (msg)
                {
                    if (!msg.error)
                    {
                        window.location.reload();
                    }
                    else
                    {
                        alert(msg.message);
                    }
                }, 'json');
            }
        }
    });

    // Handle adding product in search
    $('#addProductModal').delegate('a.com-search-list-element-link', 'click', function (e)
    {
        e.preventDefault();
        $('#addProductModal').modal("hide");

        var data = JSON.parse($(this).attr('data-info'));

        // Set up the product modal
        var add_product_modal = $('#addSingleProductModal');
        add_product_modal.find('.modal-header')
            .html('<h2>'+data.label+'</h2>');

        add_product_modal.find('.modal-header').append("<ul style='list-style: none; padding: 0; margin: 0;'></ul>")
            .append("<li>Product Code: "+data.product_code+"</li>")
            .append("<li>Base Price: $"+data.base_price+"</li>")
            .append("<li>Discount Price: $"+data.discount_price+"</li>");

        add_product_modal.find('.modal-footer').append("<p>"+data.product_desc+"</p>");

        add_product_modal
            .find('div.product-photo').html("<a class='fancybox-image' href='"+data.img+"'><img src='"+data.img+"' width='150' height='150'/></a>");

        var info_section = add_product_modal.find('div.product-options');
        info_section.html("");

        // Create options
        var option_map = data.option_map;
        $.each(option_map, function(id, option_info) {

            // Print the option title
            var option_id = id;
            info_section.append("<h4 style='margin-bottom: 5px;'>"+option_info.name+"</h4>");

            // Print the option attributes
            var option_values = $("<ul style='padding: 0; margin: 0; list-style: none;'></ul>");
            $.each(option_info.values, function (vid, value) {
                option_values.append("<li><input name='"+option_id+"' type='radio' value='"+vid+"' /> "+value+"</li>");
            });

            info_section.append(option_values);
        });

        info_section.append("<button data-info='"+JSON.stringify(data)+"' style='margin-top: 20px;' class='btn btn-success add-product'><i class='fa fa-plus-square'></i> Add Product</button>");

        // Create modal to show product
        $('#addSingleProductModal').modal("show");

    });

    $('#addSingleProductModal').delegate('button.add-product', 'click', function (e)
    {
        e.preventDefault();
        var data = JSON.parse($(this).attr('data-info'));
        var option_value_info = {};

        // Get options that are selected
        var selected_values = $('#addSingleProductModal .product-options').find(':checked');
        $.each(selected_values, function (id, selected_value) {
            option_value_info[$(selected_value).attr('name')] = $(selected_value).attr('value');
        });

        data.option_values = option_value_info;
        data.product_qty = $('#addSingleProductModal').find('select[name="product-qty"]').val();

        $('#addSingleProductModal').modal('hide');

        // Show loading to indicate processing of new order data
        window.dispatchEvent(new Event('show-order-stat-loaders'));

        // Send to server
        $.post('', {data: data, task: 'add_product'}, function (msg)
        {
            if (!msg.error)
            {
                // Add line item
                event = new CustomEvent('add-line-item', {detail: msg.product_data});
                window.dispatchEvent(event);

                // Update order stats
                var event = new CustomEvent('update-order-stats', {detail: msg.order_data});
                window.dispatchEvent(event);
            }
            else
            {
                alert(msg.message);
            }
        }, 'json');
    });
});
