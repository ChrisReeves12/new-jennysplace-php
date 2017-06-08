/**
 * verify.js
 *
 * Helps send the email to verify the user
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

// Send verify email
$(document).ready(function ()
{
    $('a.verify').click(function (e)
    {
        e.preventDefault();
        var url = $(this).attr('href');
        var id = $(this).attr('data-id');

        // Send info to server
        $.post(url, {id: id}, function (msg)
        {
            if (!msg.error)
            {

            }
            else
            {
                alert(msg.message);
            }
        }, 'json');
    });
});