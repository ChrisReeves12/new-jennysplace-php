/**
 * option
 *
 * This file handles all of the javascript on the option single page
 *
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/


// Handle adding, creating and deleting option values from scroll box and from database
$(document).ready(function (e)
{
    // Removes option value from scroll box
    $('div.scrollbox').delegate('div.option_value_entry > a', 'click', function (e)
    {
        e.preventDefault();
        $(this).parent().remove();
    });

    // Adds option value to scroll box
    $('button.add_value').click(function (e)
    {
        e.preventDefault();

        var option_value_id = $("#values").val();
        var option_value_name = $("#values").find("option:selected").text();

        if (option_value_id == 0)
            return false;

        // Check if value already exists in the box
        if ($("div.scrollbox").find('div.option_value_entry[data-value-id="'+option_value_id+'"]').length > 0)
            return false;

        $('div.scrollbox').append("<div class='option_value_entry' data-value-id='"+option_value_id+"'><a data-value-id='"+option_value_id+"' href=''>[Close]</a> "+option_value_name+"</div>");

    });

    // Creates a new option value
    $('button.create_value').click(function (e)
    {
        e.preventDefault();

        var option_value_name;

        if (option_value_name = prompt("Please enter a name for the new option value."))
        {
            if (option_value_name != "") {
                // Send info to server
                $.post("", {option_value_name: option_value_name, task: 'add_option_value'}, function (msg) {
                    if (!msg.error) {
                        $('#values').append("<option value='" + msg.option_value_id + "'>" + option_value_name + "</option>");
                    }
                    else {
                        alert(msg.message);
                    }
                }, 'json');
            }
        }
    });

    // Deletes option value
    $('a.delete_value').click(function (e)
    {
        e.preventDefault();

        var value_id = $('#values').val();

        if (value_id == 0)
            return false;

        $.post("", {task:'delete_option_value', value_id:value_id}, function (msg)
        {
            if (!msg.error)
            {
                $("#values").val(0);
                $("#values").find("option[value='"+value_id+"']").remove();
            }
            else
            {
                alert(msg.message);
            }
        }, 'json');

    });

    // Updates option value
    $('a.update_value').click(function (e)
    {
        e.preventDefault();

        var value_id = $('#values').val();
        var value_name = $('#values').find('option:selected').text();
        var new_name;

        if (value_id == 0)
            return false;

        // Get new name
        if (new_name = prompt("Option Value Name", value_name))
        {
            if (new_name !== "")
            {
                $.post("", {task: 'update_option_value', value_id: value_id, name: new_name}, function (msg)
                {
                    if (!msg.error)
                    {
                        $('#values').find('option:selected').html(new_name);
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

// Handle the submitting
$(document).ready(function (e)
{
    $('#create_option').submit(function (e)
    {
        // Place the contents of the values scroll box to an object
        var option_values = [];

        $('div.scrollbox').find('div.option_value_entry').each(function (e)
        {
            var option_id = $(this).attr('data-value-id');
            option_values.push(option_id);
        });

        // Submit values to server
        if (option_values.length > 0)
        {
            $("input[name='value_data']").val(option_values);
        }
    });
});