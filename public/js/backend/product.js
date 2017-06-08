/**
 * product.js
 *
 * Handles all the actions needed on the product screen in the backend
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

// Setup namespace
window.newjennysplace = window.newjennysplace || {};
window.newjennysplace.skus = window.newjennysplace.skus || {};
window.newjennysplace.skus.delete_list = window.newjennysplace.skus.delete_list = [];

// Additional Images
$(document).ready(function()
{
    $("#image_area").sortable();

    var image_files;

    // Handle adding additional images
    $("input[name='image[]']").change(function (event)
    {
        image_files = event.target.files;
    });

    // Handle removing additional images
    $('#image_area').delegate('a.remove_pic', 'click', function (e)
    {
        e.preventDefault();
        var photo = $(this).parent();

        // Remove from database
        $.post("",
            {
                task: "remove_additional_photo",
                rel_id: $(this).parent().attr('data-rel-id')
            },
            function (msg)
            {
                if (!msg.error)
                {
                    // Remove the photo
                    photo.fadeOut(200, function ()
                    {
                        $(this).remove();
                    });
                }
            }, 'json');
    });

    // Add video
    $(document).ready(function ()
    {
        $('button.add-video').click(function (e)
        {
            e.preventDefault();

            var type = $('select.video_type').val();
            var url = $('input.video_url').val();

            $.post('',
                {
                    task: "add_additional_video",
                    type: type,
                    url: url
                },
                function (msg)
                {
                    if (!msg.error)
                    {
                        $("#video_area").append(msg.video_code);
                    }
                }, 'json');
            });
    });

    // Handle removing additional videos
    $('#video_area').delegate('a.remove_video', 'click', function (e)
    {
        e.preventDefault();
        var video = $(this).parent();

        // Remove from database
        $.post("",
            {
                task: "remove_additional_video",
                rel_id: $(this).parent().attr('data-rel-id')
            },
            function (msg)
            {
                if (!msg.error)
                {
                    // Remove the video
                    video.fadeOut(200, function ()
                    {
                        $(this).remove();
                    });
                }
            }, 'json');
    });

    $("#add_product_photos").submit(function (e)
    {
        e.preventDefault();
        if (!image_files)
        {
            alert('Please select some images to add.');
        }
        else
        {
            // Check if there is a product to add images to
            var product_id = $("input[name='product_id']").val();

            if (product_id.length < 1) {
                alert("You must create and save a product before adding additional images.");
            }
            else
            {
                // Gather information
                var action = $(this).attr('action');
                var info = new FormData();

                $.each(image_files, function (key, value)
                {
                    info.append(key, value);
                });

                // Append task and product id
                info.append('task', 'add_additional_photos');
                info.append('product_id', $("input[name='product_id']").val());

                // Post to server
                $.ajax({
                    url: action,
                    type: 'POST',
                    data: info,
                    cache: false,
                    dataType: 'json',
                    processData: false,
                    contentType: false,

                    success: function (msg)
                    {
                        // Upload successful
                        if (!msg.error)
                        {
                            // Place photos in scroll box
                            var images = msg.images;
                            $("#image_area").html("");

                            $.each(images, function (key, image)
                            {
                                $("#image_area").append("<div data-rel-id='" + image['rel_id'] + "' data-image='" + image['url'] + "' class='multi_img inline'><img src='" + image['url'] + "'/><a class='remove_pic' href=''>[Remove]</a></div>")
                            });
                        }
                        else
                        {
                            alert(msg.message);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown)
                    {
                        alert('ERRORS: ' + textStatus);
                    }
                });
            }
        }
    });
});

// Adding categories
$(document).ready(function () {
    $("button[name='add_category']").click(function () {
        var category_id = $("select[name='category']").val();
        var category_text = $("select[name='category'] option[value='" + category_id + "']").text();

        // Check to see if category exists in the box
        var res = $("#categories div.cat_entry[data-cat=" + category_id + "]");
        if (res.length == 0) {
            // Insert into scroll box
            var input = "<div data-cat='" + category_id + "' class='cat_entry'> <a class='cat_delete' data-cat='" + category_id + "' href=''><i class='fa fa-close'></i></a> " + category_text + "</div>"
            $("#categories").append(input);
        }
    });

    // Handle delete button of categories
    $('body').delegate('a.cat_delete', 'click', function (e) {
        e.preventDefault();
        var category_id = $(this).attr('data-cat');
        $('div.cat_entry[data-cat="'+category_id+'"]').remove();
    });

    // Modify categories
    $("button.edit-categories").click(function (e) {
        e.preventDefault();
        $('#editCategoriesModal').modal("show");

        // Copy contents of categories to box in modal
        var category_contents = $("#categories").html();
        $(".dialog-categories-box").html(category_contents);
    });

    // Select a category
    $("select.main-category-select").change(function () {

        // Clear sub category section
        $("div.sub-categories-checkbox-section").html("");

        var category_id = $(this).val();
        var data = window.newjennysplace.sub_category_data;

        if (typeof data[category_id] !== "undefined" && data[category_id].length > 0) {

            // List each check box
            var sub_cat_checkbox_list = $("<ul class='list-group'></ul>");
            sub_cat_checkbox_list.append("<li class='list-group-item list-group-item-check'><input name='category-checkbox' value='"+category_id+"' type='checkbox' /> PARENT CATEGORY</li>");

            $.each(data[category_id], function (index, sub_category) {
                sub_cat_checkbox_list.append("<li class='list-group-item'> <input name='category-checkbox' data-name='"+sub_category[1]+"' value='"+sub_category[0]+"' type='checkbox'/> "+sub_category[1]+"</li>");
            });

            $("div.sub-categories-checkbox-section").html(sub_cat_checkbox_list);
        }
    });

    // Add categories to categories box
    $("button.dialog-add-categories").click(function () {
        var main_category_id = $("select.main-category-select").val();

        if (main_category_id > 0)
        {
            // If this category has no sub categories, just add the main category to the list
            if (window.newjennysplace.sub_category_data[main_category_id].length == 0) {

                var main_category_name = window.newjennysplace.category_options[main_category_id];

                if ($("#categories").find("div[data-cat='"+main_category_id+"']").length == 0)
                    $("#categories").append("<div data-cat='"+main_category_id+"' class='cat_entry'><a class='cat_delete' data-cat='"+main_category_id+"' href=''><i class='fa fa-close'></i></a> "+main_category_name+"</div>");

                // Copy contents of categories to box in modal
                var category_contents = $("#categories").html();
                $(".dialog-categories-box").html(category_contents);
            }
            else
            {
                // Build data to pass to category box
                var data = [];
                $("input[name='category-checkbox']:checked").each(function (index, category) {
                    data.push($(category).val());
                });

                // Find categories and place them in box
                $.each(data, function (index, category_id) {
                    if (typeof window.newjennysplace.category_options[category_id] !== "undefined") {
                        var category_name = window.newjennysplace.category_options[category_id];

                        // Check if category id is already in the box
                        if ($("#categories").find("div[data-cat='"+category_id+"']").length == 0)
                            $("#categories").append("<div data-cat='"+category_id+"' class='cat_entry'><a class='cat_delete' data-cat='"+category_id+"' href=''><i class='fa fa-close'></i></a> "+category_name+"</div>");

                        // Copy contents of categories to box in modal
                        var category_contents = $("#categories").html();
                        $(".dialog-categories-box").html(category_contents);
                    }
                });
            }
        }
    });

    // Close dialog
    $("button.dialog-add-categories-close").click(function () {
        $('#editCategoriesModal').modal("hide");
    });
});



// Handle uploading of main product image
$(document).ready(function ()
{
    $("input[name='default_image']").change(function (e)
    {
        // Upload image to server
        var images = e.target.files;
        if (images.length > 0)
        {
            var post_data = new FormData();
            $.each(images, function (key, value)
            {
                post_data.append(key, value);
            });

            post_data.append('task', 'upload_images');

            $.ajax({
                url: "",
                type: 'POST',
                data: post_data,
                cache: false,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function (msg)
                {
                    if (!msg.error)
                    {
                        var file_info = msg.images;
                        var file_url, image_id;

                        $.each(file_info, function (index, value) {
                            file_url = value;
                            image_id = index;
                        });

                        // Add photo
                        if (file_url.length > 0)
                        {
                            var image_element = $("<div class='delete-image'><span class='fa fa-close'></span></div><a data-img-id='"+image_id+"' class='fancybox' href='/img/product_images/"+file_url+"'><img src='/img/product_images/"+file_url+"'/></a>");
                            image_element.css('display', 'none');
                            $("#main_photo").html(image_element);
                            image_element.fadeIn(200);
                        }
                    }
                    else
                    {
                        alert(msg.message);
                    }
                }
            });
        }
    });
});

// Submit basic product information
$(document).ready(function ()
{
    $('#create_product').submit(function ()
    {
        var cat_array = [];
        $("#categories").find("div.cat_entry").each(function ()
        {
            cat_array.push($(this).attr('data-cat'));
        });

        var theme_array = [];
        $("#themes").find('input:checked').each(function () {
            theme_array.push($(this).val());
        });

        $("input[name='category_list']").val(cat_array);
        $("input[name='theme_list']").val(theme_array);
        $("input[name='category_list_contents']").val($("#categories").html());

        // Add default image
        var default_image_id = $("#main_photo").find('a').attr('data-img-id');
        if (!default_image_id)
        {
            default_image_id = '';
        }
        $("input[name='default_image_id']").val(default_image_id);
    });
});

// Handle deleting options on sku page
$(document).ready(function ()
{
   $('div.options_container').delegate('a.delete', 'click', function(e)
   {
       // There should always be at least 2 options
       var options = $('div.options_container').find('div.product-option');
       if (options.length < 2)
       {
           alert("You must have at least 1 option for each sku.");
           return false;
       }

       var option_id = $(this).parent().parent().attr('data-id');

       e.preventDefault();
       $(this).parents('div.product-option').fadeOut(200, function()
       {
           $(this).remove();
       });

       // Delete any rows in the skus that need to be deleted
       $("div.sku-option-values > table").find("tr.sku-option[data-option-id="+option_id+"]").fadeOut(300, function ()
       {
           $(this).remove();
       });

   });
});

// Handle adding options on sku page
$(document).ready(function ()
{
    $("#skus").find('.add-option').click(function (e)
    {
        e.preventDefault();

        // Collect information on option
        var option_id = $('div.options-selector').find('select[name=options]').val();
        var option_name = $('div.options-selector').find('select[name=options] > option:selected').text();

        // Check for duplicates
        if ($("div.options_container").find("div.product-option[data-id="+option_id+"]").length > 0){
            return false;
        }

        // Fetch option values from server
        $.post("", {task: 'fetch_option_values', option_id: option_id}, function (msg)
        {
            if (!msg.error)
            {
                var code = "<div class=\"product-option\" data-id=\""+option_id+"\">";
                code += "<div class=\"inline\"><a class=\"delete\" href=\"\"><span class=\"fa fa-close\"> </span></a></div>";
                code += "<div class=\"inline\">"+option_name+"</div>";
                code += "<a class=\"edit\" target='_blank' href=\"/admin/option/single?id="+option_id+"\"><span class=\"fa fa-cog\"> </span></a></div>";

                // Attach to options field
                $("div.options_container").append(code);

                // Add row to skus
                code = "<tr class='sku-option' data-option-id=\""+option_id+"\">";
                code += "<td>"+option_name+"</td>";
                code += "<td>";
                code += "<select class='sku-option-value'>";

                $.each(msg.option_values, function (index, value)
                {
                    code += "<option value='"+index+"'>"+value+"</option>";
                });

                code += "</select>";
                code += "</td>";
                code += "<td><a class='add-new-value' href=''>[Add New Value]</a></td>";
                code += "</tr>";

                $("div.sku-option-values > table > tbody").append(code);
            }
            else
            {
                alert(msg.message);
            }
        }, 'json');

    });
});


// Handle deleting of sku
$(document).ready(function ()
{
    $("#skus").find('div.sku-display').delegate('div.sku div.delete', 'click', function (e)
    {
        $(this).parent().fadeOut(200, function ()
        {
            var sku_id = $(this).attr('data-id');
            if (sku_id != "")
            {
                window.newjennysplace.skus.delete_list.push(sku_id);
            }

            $(this).remove();
        });
    });
});

// Handle adding new values to options
$(document).ready(function ()
{
    $('#skus').delegate('a.add-new-value', 'click', function (e)
    {
        e.preventDefault();

        var option_id = $(this).parents('tr').attr('data-option-id');
        var value_select_element = $(this).parents('tr').find('select');

        // Bring up prompt
        var value_name;
        if (value_name = prompt("Option Value Name"))
        {
            // Send info to server
            $.post('', {option_id: option_id, value_name: value_name, task: 'add_new_option_value'}, function (msg)
            {
                if (!msg.error)
                {
                    var value_name = msg.option_value_name;
                    var value_id = msg.option_value_id;

                    // Check if this value should be added to the list
                    if (msg.is_new)
                    {
                        $('tr.sku-option[data-option-id="'+option_id+'"]').find('select.sku-option-value').append("<option value='"+value_id+"'>"+value_name+"</option>");
                        value_select_element.val(value_id);
                    }
                    else
                    {
                        value_select_element.val(value_id);
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

// Handle saving of skus
$(document).ready(function ()
{
    $("#add_skus").submit(function (e)
    {
        e.preventDefault();

        // Collect all data on skus
        var product_id = $("input[name='product_id']").val();
        var skus = $('div.sku-display').find('div.sku');

        // Give warning if a default sku will have to be created
        if (skus.length < 1)
        {
            if (!confirm("You must have at least two (2) skus in order to have a multi-sku item.\nThis will create a single sku product with no options. Would you like to continue?"))
            {
                return false;
            }
        }

        var info = {};
        info['task'] = 'update_skus';
        info['delete_list'] = window.newjennysplace.skus.delete_list;

        for (var x = 0; x < skus.length; x++)
        {
            var value = skus[x];
            var sku = $(value);
            var sku_id = sku.attr('data-id');
            if (sku_id.length == 0)
            {
                sku_id = "new-" + x;
            }

            var sku_image_id = sku.find('div.image > a').attr('data-img-id');
            if (!sku_image_id)
            {
                sku_image_id = '';
            }

            // Basic sku info
            info[sku_id] = {};
            info[sku_id]['id'] = sku_id;
            info[sku_id]['qty'] = sku.find('input.qty').val();
            info[sku_id]['sku_number'] = sku.find('input.sku_number').val();
            info[sku_id]['status'] = sku.find('select.status').val();
            info[sku_id]['image_id'] = sku_image_id;

            // Option values
            var option_values = sku.find("div.sku-option-values").find('tr.sku-option');
            info[sku_id]['option_values'] = {};

            for (var y = 0; y < option_values.length; y++)
            {
                var option_id = $(option_values[y]).attr('data-option-id');
                var value_id = $(option_values[y]).find('.sku-option-value').val();

                info[sku_id]['option_values'][option_id] = value_id;
            }
        }

        // Send information to server
        $.post("", info, function (msg)
        {
            if (!msg.error)
            {
                alert("Your skus have been saved successfully to the product.");
                if (msg.refresh)
                {
                    window.location.reload();
                }
            }
            else
            {
                alert(msg.message);
            }
        }, 'json');

    });
});

// Handle deleting of main image
$(document).ready(function ()
{
    $("#main_photo").delegate('div.delete-image', 'click', function (e)
    {
        $(this).siblings('a').fadeOut(200, function ()
        {
            $(this).remove();
        });
    });
});

// Handle deleting of sku images
$(document).ready(function ()
{
    $("div.sku-display").delegate('div.delete-image', 'click', function (e)
    {
        var sku_id = $(this).parent().parent().attr('data-id');
        var image_element = $(this).siblings('a.image-link');

        image_element.fadeOut(200, function ()
        {
            $(this).remove();
        });

    });
});

// Handle uploading photos for skus
$(document).ready(function ()
{
    $('div.sku-display').delegate("div.sku input[type='file']", "change", function (e)
    {
        // Upload image to server
        var sku_element = $(this).parents('div.sku');

        var images = e.target.files;

        if (images.length > 0)
        {
            var post_data = new FormData();
            $.each(images, function (key, value)
            {
                post_data.append(key, value);
            });

            post_data.append('task', 'upload_images');

            $.ajax({
                url: "",
                type: 'POST',
                data: post_data,
                cache: false,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function (msg)
                {
                    if (!msg.error)
                    {
                        var file_info = msg.images;
                        var file_url, image_id;

                        $.each(file_info, function (index, value) {
                            file_url = value;
                            image_id = index;
                        });

                        // Add photo
                        if (file_url.length > 0)
                        {
                            var image_element = $("<div class='delete-image'><span class='fa fa-close'> </span></div><a data-img-id='"+image_id+"' class='fancybox image-link' href='/img/product_images/"+file_url+"'><img src='/img/product_images/"+file_url+"'/></a>");
                            image_element.css('display', 'none');
                            sku_element.find('div.image').html(image_element);
                            image_element.fadeIn(200);
                        }
                    }
                    else
                    {
                        alert(msg.message);
                    }
                }
            });
        }
    });
});

// Handle adding of skus
$(document).ready(function ()
{
    $('.add-sku').click(function (e)
    {
        e.preventDefault();

        // Get options
        var options = [];
        var product_option_elements = $('div.options_container').find('div.product-option');

        if (product_option_elements.length < 1)
        {
            return false;
        }

        for (var x = 0; x < product_option_elements.length; x++)
        {
            var product_option_element = product_option_elements[x];
            options.push($(product_option_element).attr('data-id'));
        }

        // Get option and option value info from server
        $.post("", {task:'get_new_sku_info', options:options}, function (msg)
        {
            if (!msg.error)
            {
                var sku_element = $(msg.sku_dialog_html);
                sku_element.css('display', 'none');
                $('div.sku-display').append(sku_element);

                // Fade in the element
                sku_element.fadeIn(200);
            }
            else
            {
                alert(msg.message);
            }
        }, 'json');
    });

});