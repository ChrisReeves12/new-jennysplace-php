/**
 * list
 *
 * This file handles all of the frontend stuff on the list pages
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

// Handle deleting of individual items
$(document).ready(function ()
{
    // $('table tbody').sortable();

    $("#main").find('table').delegate('a.list_item_delete', 'click', function (e)
    {
        e.preventDefault();
        var clicked_link = $(this);
        var ids = $(this).attr('data-id');
        var element = $(this).attr('data-element');

        if (confirm("Are you sure you want to delete this item?"))
        {
            // Delete item from server
            $.post("", {ids: ids, task: 'delete', element: element}, function (msg)
            {
                if (!msg.error)
                {
                    clicked_link.parents('tr').fadeOut(200, function()
                    {
                        $(this).remove();
                    });
                }
                else
                {
                    alert(msg.message);
                }

            }, 'json');
        }
    });
});

// Handle deleting of multiple items
$(document).ready(function ()
{
    $("a.delete").click(function (e)
    {
        e.preventDefault();

        if (confirm("Are you sure you want to delete the selected items?"))
        {

            // Collect ids to delete
            var checkboxes = $("table").find(":checked");
            var element = $(this).attr('data-element');
            var ids = [];

            for (var x = 0; x < checkboxes.length; x++)
            {
                var value = checkboxes[x];

                ids.push($(value).attr('data-id'));
            }

            if (ids.length > 0)
            {
                $("nav.list_nav").delegate('a.delete', 'click', function (e)
                {
                    // Delete items from server
                    $.post("", {ids: ids, task: 'delete', element: element}, function (msg)
                    {
                        if (!msg.error)
                        {
                            location.reload();
                        }
                        else
                        {
                            alert(msg.message);
                        }
                    }, 'json');
                });
            }
        }
    });
});

// Handle paginator
$(document).ready(function ()
{
    $('select.page_select').change(function (e)
    {
        var page = $(this).val();
        window.location = window.newjennysplace.utils.updateQueryStringParameter(window.location.href, 'page', page);
    });
});
