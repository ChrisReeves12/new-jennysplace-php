/**
 * banner.js
 *
 * Handles the banner code
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

$(document).ready(function ()
{
    $("#additional_images").sortable();

    // Handle adding banner slides
    $("input[name='image[]']").change(function (e)
    {
        e.preventDefault();

        var images = e.target.files;
        if (images.length > 0)
        {
            var post_data = new FormData();
            $.each(images, function (key, value)
            {
                post_data.append(key, value);
            });

            post_data.append('task', 'save_banner_slide_images');

            // Send information to server
            $.ajax({
                url:"",
                type: "POST",
                data: post_data,
                cache: false,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function (msg)
                {
                    if (!msg.error)
                    {
                        var images = msg.images;
                        $.each(images, function (key, value)
                        {
                            // Create banner images
                            var banner_slide = $("<div data-image-id='"+key+"' class='banner_slide'></div>");
                            banner_slide
                                .append("<div class='fa fa-close delete'></div>")
                                .append("<img class='banner-img' src='/img/banner_images/"+value+"'/>")
                                .append("<div class='banner-slide-info'></div>")
                                .find('div.banner-slide-info').append("<input type='text' class='banner-slide-link'/>");

                            $("#additional_images").append(banner_slide);
                        });
                    }
                    else
                    {
                        alert(msg.message);
                    }
                }
            });
        }
    });

    // Handle deleting of banner slide
    $("#additional_images").delegate("div.delete", "click", function(e)
    {
        e.preventDefault();
        $(this).parent().remove();
    });

    // Handle submitting
    $("#create_banner").submit(function (e)
    {
        e.preventDefault();

        // Move image info to form element
        var slide_data = [];
        $("#additional_images").find("div.banner_slide").each(function ()
        {
            var image_id = $(this).attr('data-image-id');
            var url = $(this).find('input.banner-slide-link').val();
            slide_data.push({url: url, image_id: image_id});
        });

        // Get information about banner
        var banner_info = {
            label: $(this).find("input[name='label']").val(),
            width: $(this).find("input[name='width']").val(),
            height: $(this).find("input[name='height']").val(),
            anim_type: $(this).find("select[name='anim_type']").val(),
            anim_speed: $(this).find("input[name='anim_speed']").val(),
            delay_time: $(this).find("input[name='delay_time']").val(),
            show_nav: $(this).find("select[name='show_nav']").val(),
            slide_direction: $(this).find("select[name='slide_direction']").val(),
            show_arrows: $(this).find("select[name='show_arrows']").val()
        };

        // Send to server
        $.post("", {task: 'save_banner', slide_data: slide_data, banner_info: banner_info}, function (msg)
        {
            if (!msg.error)
            {
                window.location = "?id=" + msg.banner_id;
            }
            else
            {
                alert(msg.message);
            }
        }, 'json');
    });
});