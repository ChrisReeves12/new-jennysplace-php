/**
 * user.js
 *
 * The javascript used on the user page
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

// Handle deletion of orders
$(document).ready(function ()
{
    $('button.delete_orders').click(function (e)
    {
        var order_ids = [];

        $('#user_orders').find('tr.order_row').find("input[type='checkbox']:checked").each(function (index, value)
        {
            order_ids.push($(this).attr('data-id'));
        });

        if (order_ids.length > 0)
        {
            if (confirm("Are you sure you want to delete the selected orders?"))
            {
                // Send info to server
                $.post('', {task: 'delete_user_orders', ids: order_ids}, function (msg)
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
});

// Handle removing discounts on shopping carts
$(document).ready(function ()
{
   $('a.remove_discount').click(function (e)
   {
       e.preventDefault();

       if (confirm("Are you sure you want to remove this discount from the cart?"))
       {
           var discount_assoc_id = $(this).attr('data-id');

           $.post('', {task: 'remove_discount', discount_assoc_id: discount_assoc_id}, function (msg)
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