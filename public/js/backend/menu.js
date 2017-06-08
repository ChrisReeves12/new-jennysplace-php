/**
 * menu.js
 *
 * Handles various methods for the menu creation form
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

// Handle adding and removing menu items
$(document).ready(function ()
{
    $('div.menu_items_box').sortable();

    $('button.add_menu_item').click(function (e)
    {
        e.preventDefault();

        // Get variables and values
        var menu_item_name = $('input.menu_item_label').val();
        var menu_item_url = $('input.menu_item_url').val();
        var menu_item_css = $('input.menu_item_css_class').val();

        // Do some validation
        if (menu_item_name.length < 1 || menu_item_url < 1)
        {
            alert("Ever menu item needs a name and a URL.");
            return false;
        }

        // Place value in box
        var element_html = $("<div class='menu_item'></div>");
        element_html.append("<a href='' class='delete fa fa-close'></a>");
        element_html.append("Menu Item Label<br/><input type='text' value='"+menu_item_name+"' class='menu_item_name'/><br/>");
        element_html.append("Menu Item URL<br/><input type='text' value='"+menu_item_url+"' class='menu_item_url'/><br/>");
        element_html.append("Menu Item CSS<br/><input type='text' value='"+menu_item_css+"' class='menu_item_css'/>");
        $("div.menu_items_box").append(element_html);
    });

    $('div.menu_items_box').delegate('a.delete', 'click', function (e)
    {
        e.preventDefault();
        $(this).parent().remove();
    });
});

// Handle form submission
$(document).ready(function ()
{
    $('#create_edit_menu').submit(function (e)
    {
        e.preventDefault();

        // Gather element information
        var menu_list_info = menu_list_info || {};
        menu_list_info['label'] = $('input[name="label"]').val();
        menu_list_info['css_class_name'] = $('input[name="css_class_name"]').val();
        menu_list_info['inactive'] =  ($('input[name="inactive"]:checked')).length == 0 ? 0 : 1;
        menu_list_info['menu_items'] = {};

        $('div.menu_items_box').find('div.menu_item').each(function (index, value)
        {
            menu_list_info['menu_items'][index] = {};
            menu_list_info['menu_items'][index] = {
                "item_label": $(this).find('input.menu_item_name').val(),
                "menu_item_url": $(this).find('input.menu_item_url').val(),
                "menu_item_css": $(this).find('input.menu_item_css').val()
            }
        });

        // Send info to server
        $.post('', {info: menu_list_info}, function (msg)
        {
            if (!msg.error)
            {
                window.location = '?id=' + msg.menu_id;
            }
            else
            {
                alert(msg.message);
            }
        }, 'json');
    });
});
