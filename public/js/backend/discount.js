/**
 * discount
 *
 * Contains the javascript necessary to administer the discount page
 *
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

$(document).ready(function (msg)
{
    $("select[name='discountaction']").change(function (e)
    {
        var discountaction = $(this).val();

        if (discountaction > 0)
        {
            $.post('', {discountaction: discountaction, task: 'show_discount_action'}, function (msg)
            {
                if (!msg.error)
                {
                    $("input[name='action_name']").val(msg.discount_action_info.name);
                    $("input[name='shipping_discount']").val(msg.discount_action_info.shippingdiscount);

                    if (msg.discount_action_info.shippingmethod != null)
                    {
                        $("select[name='shipping_method']").val(msg.discount_action_info.shippingmethod.id);
                    }
                    else
                    {
                        $("select[name='shipping_method']").val(0);
                    }

                    $("input[name='product_total_discount']").val(msg.discount_action_info.totaldiscount);
                    $("select[name='product_discount_type']").val(msg.discount_action_info.totaldiscounttype);
                    $("select[name='shipping_discount_type']").val(msg.discount_action_info.shipdiscounttype);
                }
                else
                {
                    alert(msg.message);
                }
            }, 'json');
        }
        else
        {
            $("input[name='action_name']").val("");
            $("input[name='shipping_discount']").val("");
            $("select[name='shipping_method']").val(0);
            $("input[name='product_total_discount']").val("");
        }
    });
});

// Add and remove actions
$(document).ready(function (msg)
{
    // Add
    $("input[name='add_action']").click(function (e)
    {
        e.preventDefault();

        var action_id = $("select[name='discount_action']").val();
        var action_name = $("select[name='discount_action']").find('option:selected').text();
        var actions = $("#discount_actions").find('div.discount_action');
        var action_ids = [];

        if (action_id == 0)
            return false;

        // Check if action is already in list
        for (var x = 0; x < actions.length; x++)
        {
            var action = actions[x];
            action_ids.push($(action).attr('data-id'));
        }

        if (action_ids.indexOf(action_id) > -1)
        {
            return false;
        }

        $("#discount_actions").append("<div data-id='"+action_id+"' class='discount_action'><a class='delete' href=''>[Close]</a> "+action_name+"</div>");

    });

    // Delete
    $('#discount_actions').delegate('a.delete', 'click', function (e)
    {
        e.preventDefault();

        $(this).parent().remove();
    });
});

$(document).ready(function (msg)
{
    $("a.delete-action").click(function (e)
    {
        e.preventDefault();

        if ($("select[name='discountaction']").val() > 0)
        {
            if (confirm("Are you sure you want to delete this discount action?"))
            {
                // Send request to delete to server
                var discountaction = $("select[name='discountaction']").val();

                $.post('', {discountaction: discountaction, task: 'delete_discount_action'}, function (msg)
                {
                    if (!msg.error)
                    {
                        $("select[name='discountaction']").val(0);
                        $("select[name='discount_action']").val(0);
                        $("input[name='action_name']").val("");
                        $("input[name='shipping_discount']").val("");
                        $("select[name='shipping_method']").val(0);
                        $("input[name='product_total_discount']").val("");
                        $("select[name='discountaction']").find("option[value='"+discountaction+"']").remove();
                        $("select[name='discount_action']").find("option[value='"+discountaction+"']").remove();
                    }
                    else
                    {
                        alert(msg.message);
                    }
                }, 'json');
            }
        }
    });
});

// Handle submission of discount
$(document).ready(function (msg)
{
    $("#create_discount").submit(function (e)
    {
        e.preventDefault();
        var discount_action_ids = [];
        var discount_id = $("select[name='discount']").val();

        $("#discount_actions").find('div.discount_action').each(function (index, value)
        {
            discount_action_ids.push($(this).attr('data-id'));
        });

        $("input[name='discount_action_info']").val(discount_action_ids);

        // Send form information to server
        $.post('', $(this).serialize(), function (msg)
        {
            if (!msg.error)
            {
                // Add discount to list if it is a new discount
                if (discount_id == 0)
                {
                    $("select[name='discount']").append("<option value='"+msg.discount.id+"'>"+msg.discount.name+"</option>")
                        .val(msg.discount.id);
                }
            }
            else
            {
                alert(msg.message);
            }
        }, 'json');

    });
});

// Handle display of discount
$(document).ready(function (e)
{
    $("select[name='discount']").change(function (e)
    {
        var discount_id = $(this).val();

        if (discount_id == 0)
        {
            $("input[name='discount_name']").val("");
            $("input[name='discount_code']").val("");
            $("input[name='dollar_hurdle']").val("");
            $("input[name='start_date']").val("");
            $("input[name='end_date']").val("");
            $("select[name='inactive']").val(0);
            $("#discount_actions").html("");

            return false;
        }

        // Send id to server
        $.post('', {discount_id: discount_id, task: 'show_discount'}, function (msg)
        {
            if (!msg.error)
            {
                $("input[name='discount_name']").val(msg.discount_info.name);
                $("input[name='discount_code']").val(msg.discount_info.code);
                $("input[name='dollar_hurdle']").val(msg.discount_info.dollarhurdle);
                $("input[name='start_date']").val(msg.discount_info.startdate);
                $("input[name='end_date']").val(msg.discount_info.enddate);
                $("select[name='inactive']").val(msg.discount_info.isinactive == true ? 1 : 0);

                // Add discount actions
                $.each(msg.discount_info.discount_actions, function (index, value)
                {
                    var discount_action_id = value.id;
                    var discount_action_name = value.name;

                    $("#discount_actions").append("<div data-id='"+discount_action_id+"' class='discount_action'><a class='delete' href=''>[Close]</a> "+discount_action_name+"</div>");
                });
            }
            else
            {
                alert(msg.message);
            }
        }, 'json');
    });
});

// Handle delete discount
$(document).ready(function (e)
{
   $('a.delete-discount').click(function (e)
   {
       e.preventDefault();
       var discount_id = $("select[name='discount']").val();

       if (discount_id == 0)
            return false;

       if (confirm("Are you sure you want to delete this discount?"))
       {
           // Send info to server
           $.post('', {discount_id: discount_id, task: 'delete_discount'}, function (msg)
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

   });
});
