/**
 * mailer.js
 *
 * Houses various functions for operation of the UI of the mailer API
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

$(document).ready(function () {
    // Remove email from campaign button
    $('div.scrollbox').delegate('a.email-on-campaign-remove', 'click', function (e) {
        e.preventDefault();

        var email_id = $(this).parent().attr('data-id');
        var element = $(this);

        // Send data to server
        $.post('', {email_id: email_id, task: 'async_remove_email_from_campaign'}, function (msg) {
            if (!msg.error)
            {
                element.parent().remove();
            }
            else
            {
                alert(msg.message);
            }
        }, 'json');
    });

    $('#launch_campaign').click(function (e) {

        var campaign_id = $(this).attr('data-id');
        var element = $(this);

        if (confirm('Are you sure you want to launch this email campaign?'))
        {
            // Send information to server to launch campaign
            $.post('', {task: 'launch_campaign', campaign_id: campaign_id}, function (msg) {
                if (!msg.error)
                {

                }
                else
                {
                    alert(msg.message);
                }
            }, 'json');
        }
    });

    $('#unlaunch_campaign').click(function (e) {

        var campaign_id = $(this).attr('data-id');

        // Send information to server
        $.post('', {task: 'unlaunch_campaign', campaign_id: campaign_id}, function (msg) {
            if (!msg.error)
            {

            }
            else
            {
                alert(msg.message);
            }

        }, 'json');
    });

    $('#test_email').click(function (e) {

        e.preventDefault();

        var email_id = $(this).attr('data-id');

        // Send information to server
        $.post('', {task: 'test_email', email_id: email_id}, function (msg) {
            if (!msg.error)
            {
                alert("Test email has been sent");
            }
            else
            {
                alert(msg.message);
            }
        });
    });
});
