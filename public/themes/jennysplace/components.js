"use strict";

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol ? "symbol" : typeof obj; };

/**
 * components
 *
 * This file contains components used in the backend
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

var SearchListElement = React.createClass({
    displayName: "SearchListElement",

    render: function render() {

        return React.createElement(
            "a",
            { "data-info": JSON.stringify(this.props.element), className: "com-search-list-element-link", href: this.props.element.href },
            React.createElement(
                "li",
                { className: "com-search-list-element" },
                React.createElement(
                    "div",
                    { className: "row" },
                    React.createElement(
                        "div",
                        { className: "col-xs-2" },
                        React.createElement("img", { width: "64", height: "64", src: this.props.element.img })
                    ),
                    React.createElement(
                        "div",
                        { className: "col-xs-10" },
                        React.createElement(
                            "h5",
                            null,
                            this.props.element.label
                        ),
                        React.createElement(
                            "p",
                            null,
                            this.props.element.description
                        )
                    )
                )
            )
        );
    }
});

var SearchList = React.createClass({
    displayName: "SearchList",

    render: function render() {

        var output;

        if (this.props.status == 'loading') {
            output = React.createElement(
                "div",
                { className: "com-search-list-container" },
                "Loading results...please wait"
            );
        } else if (_typeof(this.props.data) && this.props.data.length > 0) {
            output = React.createElement(
                "div",
                { className: "com-search-list-container" },
                React.createElement(
                    "ul",
                    { className: "com-search-list" },
                    this.props.data.map(function (element) {
                        return React.createElement(SearchListElement, { element: element });
                    })
                )
            );
        } else {
            output = React.createElement(
                "span",
                null,
                " "
            );
        }

        return output;
    }
});

var ProductSearch = React.createClass({
    displayName: "ProductSearch",

    keyword: '',
    input_delay: 500,
    timer: null,

    getInitialState: function getInitialState() {
        return {
            data: [],
            status: 'show'
        };
    },

    startInputTimer: function startInputTimer(e) {
        this.keyword = e.target.value;
        var this_element = this;

        clearTimeout(this.timer);
        this.timer = setTimeout(function () {
            this_element.populateSearch();
        }, this.input_delay);
    },

    // Populates the search box from the server
    populateSearch: function populateSearch() {

        var products = [];
        var element = this;

        // Make sure string is long enough
        if (this.keyword.length > 3) {
            // Show loading
            element.state.status = 'loading';
            element.setState(element.state);

            // Go to server
            $.ajax({
                url: '',
                method: 'post',
                data: { task: 'product_search', value: element.keyword },
                dataType: 'json',
                success: function success(msg) {
                    if (!msg.error) {
                        element.state.data = msg.products;
                        element.state.status = 'show';
                        element.setState(element.state);
                    } else {
                        alert(msg.message);
                    }
                }
            });
        }
    },

    render: function render() {

        var output;

        output = React.createElement(
            "div",
            { className: "form-group" },
            React.createElement("input", { onChange: this.startInputTimer, type: "text", placeholder: "Search Product", className: "form-control" }),
            React.createElement(SearchList, { status: this.state.status, data: this.state.data })
        );

        return output;
    }
});

var BulkEditRow = React.createClass({
    displayName: "BulkEditRow",

    render: function render() {

        var element = this;
        var row_class;

        // Sorting is only allowed when a category is selected
        if (this.props.category == 0) {
            row_class = "";
        } else {
            row_class = "bulk-edit-row";
        }

        return React.createElement(
            "tr",
            { className: row_class },
            this.props.data.fields.map(function (field_content) {
                return React.createElement(
                    "td",
                    null,
                    function () {
                        if (field_content.type == 'image') return React.createElement(
                            "a",
                            { className: "fancybox", href: field_content.value },
                            React.createElement("img", { src: field_content.value, width: "45", height: "45" })
                        );else if (field_content.type == 'literal') return React.createElement(
                            "span",
                            null,
                            field_content.value
                        );else if (field_content.type == 'checkbox') {
                            if (typeof field_content.status !== 'undefined' && field_content.status == true) return React.createElement("input", { type: "checkbox", defaultChecked: true, value: field_content.value, name: field_content.id });else return React.createElement("input", { type: "checkbox", value: field_content.value, name: field_content.id });
                        }
                    }()
                );
            })
        );
    }
});

// The main table that lists mass edit items
var BulkEditParentTable = React.createClass({
    displayName: "BulkEditParentTable",

    getInitialState: function getInitialState() {
        return {
            data: { rows: this.props.data.rows },
            page: 0,
            loading: 'none',
            category: 0,
            filter: 'all'
        };
    },

    // Show more items in the list
    onViewMore: function onViewMore(e) {
        e.preventDefault();
        var element = this;

        element.state.loading = 'view_more';
        element.setState(element.state);

        $.get('', { category: element.state.category, page: element.state.page, filter: element.state.filter, task: 'view_more' }, function (msg) {

            if (!msg.error) {

                // Add rows
                element.state.data.rows = element.state.data.rows.concat(msg.rows);
                element.state.page++;
                element.state.loading = 'none';
                element.setState(element.state);
            } else {
                alert(msg.message);
            }
        }, 'json');
    },

    refresh: function refresh() {

        var element = this;

        $.get('', { category: element.state.category, filter: element.state.filter, task: 'refresh' }, function (msg) {

            if (!msg.error) {

                element.state.data.rows = msg.rows;
                element.state.page = 0;
                element.state.loading = 'none';
                element.setState(element.state);
            } else {
                alert(msg.message);
            }
        }, 'json');
    },

    change_categories: function change_categories(e) {

        e.preventDefault();
        this.state.category = e.target.value;
        this.setState(this.state);

        this.refresh();
    },

    delete_selected: function delete_selected(e) {
        e.preventDefault();

        var element = this;
        var ids_to_delete = [];
        $('input[name="id"]:checked').each(function (object) {
            ids_to_delete.push(parseInt($(this).val()));
        });

        if (confirm("Are you sure you want to delete the following items?")) {
            $.post('', { task: 'delete', ids: ids_to_delete }, function (msg) {

                if (!msg.error) {
                    // Delete the rows from the state
                    for (var idx in element.state.data.rows) {
                        if (element.state.data.rows.hasOwnProperty(idx)) {
                            var row = element.state.data.rows[idx];
                            var row_id = parseInt(row['fields'][0]['value']);

                            // Delete the row out of the state if there is a match
                            if (ids_to_delete.indexOf(row_id) >= 0) {
                                delete element.state.data.rows[idx];
                            }
                        }
                    }

                    element.setState(element.state);
                } else {
                    alert(msg.message);
                }
            }, 'json');
        }
    },

    update_important: function update_important(e) {
        e.preventDefault();

        var element = this;
        var ids_to_make_important = [];
        var ids_to_make_normal = [];

        $('input[name="important"]:checked').each(function (object) {
            ids_to_make_important.push(parseInt($(this).val()));
        });

        $('input[name="important"]:not(:checked)').each(function (object) {
            ids_to_make_normal.push(parseInt($(this).val()));
        });

        $.post('', { ids_to_make_important: ids_to_make_important, ids_to_make_normal: ids_to_make_normal, task: 'update_important' }, function (msg) {

            if (!msg.error) {
                alert("Products have been updated.");
            } else {
                alert(msg.message);
            }
        }, 'json');
    },

    activateDateAdd: function activateDateAdd() {
        $('.datepicker').datepicker();
    },

    onStatusChange: function onStatusChange(e) {

        e.preventDefault();
        var status_override = $("select[name='status-override']").val();
        var element = this;
        var ids = [];

        $('input[name="id"]:checked').each(function (object) {
            ids.push(parseInt($(this).val()));
        });

        $.post('', { task: 'status_change', status: status_override, ids: ids }, function (msg) {

            if (!msg.error) {
                // Update the rows
                for (var i in element.state.data.rows) {
                    if (element.state.data.rows.hasOwnProperty(i)) {
                        var id = parseInt(element.state.data.rows[i]['fields'][0]['value']);
                        if (ids.indexOf(id) >= 0) element.state.data.rows[i]['fields'][7]['value'] = msg.status_name;
                    }
                }

                element.setState(element.state);
            } else {
                alert(msg.message);
            }
        }, 'json');
    },

    onDateChange: function onDateChange(e) {

        var date = $("input[name='date-added']").val();
        var element = this;
        var ids = [];

        $('input[name="id"]:checked').each(function (object) {
            ids.push(parseInt($(this).val()));
        });

        $.post('', { task: 'date_change', date: date, ids: ids }, function (msg) {

            if (!msg.error) {
                // Update the rows
                for (var i in element.state.data.rows) {
                    if (element.state.data.rows.hasOwnProperty(i)) {
                        var id = parseInt(element.state.data.rows[i]['fields'][0]['value']);
                        if (ids.indexOf(id) >= 0) element.state.data.rows[i]['fields'][8]['value'] = date;
                    }
                }

                element.setState(element.state);
            } else {
                alert(msg.message);
            }
        }, 'json');
    },

    unselectAll: function unselectAll(e) {
        $("input[name='id']:checked").prop("checked", false);
    },

    selectAll: function selectAll(e) {
        $("input[name='id']:not(:checked)").prop("checked", true);
    },

    categoryDialog: function categoryDialog(e) {
        $("#editCategoriesModal").modal('show');
    },

    populateSubDialogCategories: function populateSubDialogCategories(e) {

        // Clear sub category section
        $("div.sub-categories-checkbox-section").html("");

        var category_id = $(e.target).val();
        var data = this.props.data.sub_category_data;

        if (typeof data[category_id] !== "undefined" && data[category_id].length > 0) {

            // Create list
            var sub_cat_checkbox_list = $("<ul class='list-group'></ul>");
            sub_cat_checkbox_list.append("<li class='list-group-item'><input name='category-checkbox' value='" + category_id + "' type='checkbox' /> PARENT CATEGORY</li>");

            $.each(data[category_id], function (index, sub_category) {
                sub_cat_checkbox_list.append("<li class='list-group-item'> <input name='category-checkbox' data-name='" + sub_category[1] + "' value='" + sub_category[0] + "' type='checkbox'/> " + sub_category[1] + "</li>");
            });

            $("div.sub-categories-checkbox-section").html(sub_cat_checkbox_list);
        }
    },

    componentDidMount: function componentDidMount() {

        var element = this;

        // Bind delete function to category delete
        $("body").delegate('.cat_delete', 'click', function (e) {
            element.deleteCategoryFromDialog(e);
        });

        $('tbody').sortable({
            revert: false
        });
    },

    update_sort_ordering: function update_sort_ordering(e) {

        var element = this;
        if (element.state.category == 0) {
            alert("Please select a category you want to sort products in.");
            return false;
        }

        // Get products to update in order as they appear
        var product_ids = [];
        $("input[name='id']").each(function () {
            product_ids.push($(this).val());
        });

        window.newjennysplace.utils.showWaitScreen("Please Wait");

        // Send information to server
        $.post('', { task: 'update_sort_order', category: element.state.category, product_ids: product_ids }, function (msg) {
            if (!msg.error) {
                window.newjennysplace.utils.killWaitScreen();
                alert("Product Order Saved Successfully!");
            } else {
                window.newjennysplace.utils.killWaitScreen();
                alert(msg.message);
            }
        }, 'json');
    },

    closeCategoriesDialog: function closeCategoriesDialog(e) {
        $("#editCategoriesModal").modal('hide');
    },

    saveCategoriesDialog: function saveCategoriesDialog(e) {

        var element = this;

        // Get products to update
        var selected_product_ids = [];
        $("input[name='id']:checked").each(function () {
            selected_product_ids.push($(this).val());
        });

        // Get categories
        var category_ids = [];
        $("div.dialog-categories-box").find("div.cat_entry").each(function () {
            category_ids.push($(this).attr('data-cat'));
        });

        if (selected_product_ids.length > 0) {
            if (category_ids.length > 0) {
                if (confirm("You sure you want to update the selected products' categories? This action cannot be undone.")) {
                    // Send information to server
                    $.post('', {
                        task: 'update_categories',
                        category_ids: category_ids,
                        product_ids: selected_product_ids
                    }, function (msg) {
                        if (!msg.error) {
                            $("#editCategoriesModal").modal('hide');
                            element.refresh();
                        } else {
                            alert(msg.message);
                        }
                    }, 'json');
                }
            } else {
                alert("Please add at least one category, no changes will apply.");
            }
        } else {
            alert("No products are selected for update, no changes will apply.");
        }
    },

    deleteCategoryFromDialog: function deleteCategoryFromDialog(e) {
        e.preventDefault();
        $(e.target).parent().remove();
    },

    addCategoriesFromDialog: function addCategoriesFromDialog(e) {

        var main_category_id = $("select.main-category-select").val();
        var element = this;

        if (main_category_id > 0) {
            // If this category has no sub categories, just add the main category to the list
            if (element.props.data.sub_category_data[main_category_id].length == 0) {

                var main_category_name = element.props.data.main_category_dictionary[main_category_id];

                if ($("div.dialog-categories-box").find("div[data-cat='" + main_category_id + "']").length == 0) $("div.dialog-categories-box").append("<div data-cat='" + main_category_id + "' class='cat_entry'><a class='cat_delete' data-cat='" + main_category_id + "' href=''><i class='fa fa-close'></i></a> " + main_category_name + "</div>");

                // Copy contents of categories to box in modal
                var category_contents = $("#categories").html();
                $(".dialog-categories-box").html(category_contents);
            } else {
                // Build data to pass to category box
                var data = [];
                $("input[name='category-checkbox']:checked").each(function (index, category) {
                    data.push($(category).val());
                });

                // Find categories and place them in box
                $.each(data, function (index, category_id) {

                    if (typeof element.props.data.sub_category_dictionary[main_category_id][category_id] !== "undefined") {

                        var category_name = element.props.data.sub_category_dictionary[main_category_id][category_id];

                        // Check if category id is already in the box
                        if ($("div.dialog-categories-box").find("div[data-cat='" + category_id + "']").length == 0) $("div.dialog-categories-box").append("<div data-cat='" + category_id + "' class='cat_entry'><a class='cat_delete' data-cat='" + category_id + "' href=''><i class='fa fa-close'></i></a> " + category_name + "</div>");
                    }
                });
            }
        }
    },

    // Change search filter
    changeFilter: function changeFilter(e) {

        var filter = $(e.target).val();
        this.state.filter = filter;
        this.setState(this.state);

        // Refresh view
        this.refresh();
    },

    // Main render function
    render: function render() {

        var output;
        var element = this;
        var rows_data = this.state.data.rows;

        // Only category display can be sortable
        var tbody_class;
        if (this.state.category == 0) {
            tbody_class = "";
        } else {
            tbody_class = "sortable";
        }

        output = React.createElement(
            "div",
            null,
            React.createElement(
                "div",
                { id: "editCategoriesModal", className: "modal fade", role: "dialog" },
                React.createElement(
                    "div",
                    { className: "modal-dialog" },
                    React.createElement(
                        "div",
                        { className: "modal-content" },
                        React.createElement(
                            "div",
                            { className: "modal-header" },
                            React.createElement(
                                "h2",
                                null,
                                "Category Dialog"
                            )
                        ),
                        React.createElement(
                            "div",
                            { className: "modal-body" },
                            React.createElement(
                                "div",
                                { className: "row" },
                                React.createElement(
                                    "div",
                                    { className: "col-xs-6" },
                                    React.createElement(
                                        "div",
                                        { className: "row" },
                                        React.createElement(
                                            "div",
                                            { className: "col-xs-12" },
                                            React.createElement(
                                                "div",
                                                { className: "form-group" },
                                                React.createElement(
                                                    "label",
                                                    { htmlFor: "main-category-select" },
                                                    "Select Parent Category"
                                                ),
                                                React.createElement(
                                                    "select",
                                                    { onChange: this.populateSubDialogCategories, name: "main-category-select", className: "form-control main-category-select" },
                                                    React.createElement(
                                                        "option",
                                                        { value: "0" },
                                                        "Select Category"
                                                    ),
                                                    this.props.data.main_categories.map(function (category) {
                                                        return React.createElement(
                                                            "option",
                                                            { value: category[0] },
                                                            category[1]
                                                        );
                                                    })
                                                )
                                            )
                                        )
                                    ),
                                    React.createElement(
                                        "div",
                                        { className: "row" },
                                        React.createElement(
                                            "div",
                                            { className: "col-xs-12" },
                                            React.createElement(
                                                "label",
                                                null,
                                                "This Product's Categories"
                                            ),
                                            React.createElement("div", { className: "scrollbox dialog-categories-box" })
                                        )
                                    )
                                ),
                                React.createElement("div", { className: "col-xs-6 sub-categories-checkbox-section" })
                            )
                        ),
                        React.createElement(
                            "div",
                            { className: "modal-footer" },
                            React.createElement(
                                "div",
                                { className: "row" },
                                React.createElement(
                                    "div",
                                    { className: "col-xs-3" },
                                    React.createElement(
                                        "button",
                                        { onClick: this.saveCategoriesDialog, className: "btn btn-success dialog-add-categories-save" },
                                        React.createElement("i", { className: "fa fa-save" }),
                                        " Apply Changes"
                                    )
                                ),
                                React.createElement(
                                    "div",
                                    { className: "col-xs-3" },
                                    React.createElement(
                                        "button",
                                        { onClick: this.addCategoriesFromDialog, className: "btn btn-default dialog-add-categories" },
                                        React.createElement("i", { className: "fa fa-plus-circle" }),
                                        " Add Categories"
                                    )
                                ),
                                React.createElement(
                                    "div",
                                    { className: "col-xs-3" },
                                    React.createElement(
                                        "button",
                                        { onClick: this.closeCategoriesDialog, className: "btn btn-danger dialog-add-categories-close" },
                                        React.createElement("i", { className: "fa fa-close" }),
                                        " Close Dialog"
                                    )
                                )
                            )
                        )
                    )
                )
            ),
            React.createElement(
                "div",
                { className: "row bulk-edit-box" },
                React.createElement(
                    "h3",
                    null,
                    "Edit Menu"
                ),
                React.createElement(
                    "div",
                    { className: "form-group" },
                    React.createElement(
                        "button",
                        { onClick: this.selectAll, className: "btn btn-default" },
                        React.createElement("i", { className: "fa fa-mouse-pointer" }),
                        " Select All"
                    )
                ),
                React.createElement(
                    "div",
                    { className: "form-group" },
                    React.createElement(
                        "button",
                        { onClick: this.unselectAll, className: "btn btn-default" },
                        React.createElement("i", { className: "fa fa-ban" }),
                        " Unselect All"
                    )
                ),
                React.createElement(
                    "div",
                    { className: "form-group" },
                    React.createElement(
                        "button",
                        { onClick: this.categoryDialog, className: "btn btn-default" },
                        React.createElement("i", { className: "fa fa-edit" }),
                        " Category Dialog"
                    )
                ),
                React.createElement(
                    "div",
                    { className: "form-group" },
                    React.createElement(
                        "label",
                        { htmlFor: "filter" },
                        "Filter"
                    ),
                    React.createElement(
                        "select",
                        { onChange: this.changeFilter, name: "filter", className: "form-control" },
                        React.createElement(
                            "option",
                            { value: "all" },
                            "Show All"
                        ),
                        React.createElement(
                            "option",
                            { value: "in_stock" },
                            "Show In Stock"
                        ),
                        React.createElement(
                            "option",
                            { value: "out_of_stock" },
                            "Show Out Of Stock"
                        ),
                        React.createElement(
                            "option",
                            { value: "disabled" },
                            "Show Disabled"
                        )
                    )
                ),
                React.createElement(
                    "div",
                    { className: "form-group" },
                    React.createElement(
                        "label",
                        null,
                        "Category"
                    ),
                    React.createElement(
                        "select",
                        { onChange: this.change_categories, className: "form-control" },
                        this.props.data.categories.map(function (category) {
                            return React.createElement(
                                "option",
                                { value: category.id },
                                category.name
                            );
                        })
                    )
                ),
                React.createElement(
                    "div",
                    { className: "form-group" },
                    React.createElement(
                        "a",
                        { onClick: this.delete_selected, className: "btn btn-danger", href: "" },
                        React.createElement("i", { className: "fa fa-remove" }),
                        " Delete Selected"
                    )
                ),
                React.createElement(
                    "div",
                    { className: "form-group" },
                    React.createElement(
                        "a",
                        { onClick: this.update_important, className: "btn btn-default", href: "" },
                        React.createElement("i", { className: "fa fa-save" }),
                        " Save Important Status"
                    )
                ),
                React.createElement(
                    "div",
                    { className: "form-group" },
                    React.createElement(
                        "label",
                        null,
                        "Status Override"
                    ),
                    React.createElement(
                        "select",
                        { name: "status-override", className: "form-control" },
                        this.props.data.product_statuses.map(function (status) {
                            return React.createElement(
                                "option",
                                { value: status.id },
                                status.name
                            );
                        })
                    )
                ),
                React.createElement(
                    "div",
                    { className: "form-group" },
                    React.createElement(
                        "button",
                        { onClick: this.onStatusChange, className: "btn btn-default" },
                        React.createElement("i", { className: "fa fa-save" }),
                        " Save Status Changes"
                    )
                ),
                React.createElement(
                    "div",
                    { className: "form-group" },
                    React.createElement(
                        "label",
                        null,
                        "Date Added"
                    ),
                    React.createElement("input", { onFocus: this.activateDateAdd, name: "date-added", type: "text", className: "form-control datepicker" })
                ),
                React.createElement(
                    "div",
                    { className: "form-group" },
                    React.createElement(
                        "button",
                        { onClick: this.onDateChange, className: "btn btn-default" },
                        React.createElement("i", { className: "fa fa-calendar" }),
                        " Save Date Added"
                    )
                ),
                React.createElement(
                    "div",
                    { className: "form-group" },
                    React.createElement(
                        "button",
                        { onClick: element.update_sort_ordering, className: "btn btn-default" },
                        React.createElement("i", { className: "fa fa-sort" }),
                        " Save Sort Order"
                    )
                )
            ),
            React.createElement(
                "table",
                { className: "table" },
                React.createElement(
                    "tbody",
                    { className: tbody_class },
                    React.createElement(
                        "tr",
                        null,
                        this.props.data.headers.map(function (header) {
                            return React.createElement(
                                "th",
                                null,
                                header
                            );
                        })
                    ),
                    rows_data.map(function (row_data) {
                        return React.createElement(BulkEditRow, { category: element.state.category, data: row_data });
                    }),
                    function () {
                        if (element.state.loading == 'view_more') {
                            return React.createElement(
                                "tr",
                                null,
                                React.createElement(
                                    "td",
                                    null,
                                    React.createElement("img", { src: "/img/layout_images/loader.gif", width: "45", height: "45" })
                                )
                            );
                        } else {
                            return React.createElement(
                                "tr",
                                null,
                                React.createElement(
                                    "td",
                                    null,
                                    React.createElement(
                                        "a",
                                        { onClick: element.onViewMore, className: "btn btn-default", href: "" },
                                        React.createElement("i", { className: "fa fa-arrow-circle-down" }),
                                        " View More"
                                    )
                                )
                            );
                        }
                    }()
                )
            )
        );

        return output;
    }
});

var OrderStats = React.createClass({
    displayName: "OrderStats",

    getInitialState: function getInitialState() {

        return {
            status: 'show',
            sub_total: window.newjennysplace.backend.page_data.sub_total,
            tax: window.newjennysplace.backend.page_data.tax,
            shipping_cost: window.newjennysplace.backend.page_data.shipping_cost,
            tracking_number: window.newjennysplace.backend.page_data.tracking_number,
            total_discount: window.newjennysplace.backend.page_data.total_discount,
            store_credit: window.newjennysplace.backend.page_data.store_credit,
            total: window.newjennysplace.backend.page_data.total,
            total_weight: window.newjennysplace.backend.page_data.total_weight,
            date_shipped: window.newjennysplace.backend.page_data.date_shipped
        };
    },

    handleUpdateOrderStats: function handleUpdateOrderStats(e) {
        // Set state with incoming data
        this.setState(e.detail);
    },

    showLoaders: function showLoaders(e) {
        this.state.status = 'loading';
        this.setState(this.state);
    },

    componentDidMount: function componentDidMount() {
        window.addEventListener('update-order-stats', this.handleUpdateOrderStats);
        window.addEventListener('show-order-stat-loaders', this.showLoaders);
    },

    render: function render() {

        if (this.state.status == 'loading') {
            return React.createElement(
                "h4",
                null,
                React.createElement("i", { className: "fa fa-hourglass-half" }),
                " Loading...please wait..."
            );
        }

        return React.createElement(
            "div",
            { id: "order_info" },
            React.createElement(
                "div",
                { className: "inline" },
                React.createElement(
                    "ul",
                    { className: "list-group" },
                    React.createElement(
                        "li",
                        { className: "list-group-item" },
                        React.createElement(
                            "strong",
                            null,
                            "Auth Code"
                        ),
                        ": ",
                        React.createElement("input", { className: "auth_code", type: "text", defaultValue: window.newjennysplace.backend.page_data.auth_code })
                    ),
                    React.createElement(
                        "li",
                        { className: "list-group-item" },
                        React.createElement(
                            "strong",
                            null,
                            "Sub-Total"
                        ),
                        ": $",
                        this.state.sub_total
                    ),
                    React.createElement(
                        "li",
                        { className: "list-group-item" },
                        React.createElement(
                            "strong",
                            null,
                            "Tax"
                        ),
                        ": $",
                        React.createElement("input", { className: "tax", type: "text", defaultValue: this.state.tax })
                    ),
                    React.createElement(
                        "li",
                        { className: "list-group-item" },
                        React.createElement(
                            "strong",
                            null,
                            "Shipping Cost"
                        ),
                        ": $",
                        React.createElement("input", { className: "shipping_cost", type: "text", defaultValue: this.state.shipping_cost })
                    ),
                    React.createElement(
                        "li",
                        { className: "list-group-item" },
                        React.createElement(
                            "strong",
                            null,
                            "Tracking Number"
                        ),
                        ": ",
                        React.createElement("input", { style: { fontSize: 10 }, placeholder: "Tracking Number", className: "tracking_number", type: "text", defaultValue: this.state.tracking_number })
                    ),
                    React.createElement(
                        "li",
                        { className: "list-group-item" },
                        React.createElement(
                            "strong",
                            null,
                            "Total Discount"
                        ),
                        ": $",
                        React.createElement("input", { className: "total_discount", type: "text", defaultValue: this.state.total_discount })
                    ),
                    React.createElement(
                        "li",
                        { className: "list-group-item" },
                        React.createElement(
                            "strong",
                            null,
                            "Store Credit"
                        ),
                        ": -$",
                        this.state.store_credit
                    ),
                    React.createElement(
                        "li",
                        { className: "list-group-item" },
                        React.createElement(
                            "strong",
                            null,
                            "Original Grand Total"
                        ),
                        ": $",
                        window.newjennysplace.backend.page_data.original_total
                    ),
                    React.createElement(
                        "li",
                        { className: "list-group-item" },
                        React.createElement(
                            "strong",
                            null,
                            "GRAND TOTAL"
                        ),
                        ": $",
                        this.state.total
                    )
                )
            ),
            React.createElement(
                "div",
                { className: "inline" },
                React.createElement(
                    "ul",
                    { className: "list-group" },
                    React.createElement(
                        "li",
                        { className: "list-group-item" },
                        React.createElement(
                            "div",
                            { "class": "form-group" },
                            React.createElement(
                                "strong",
                                null,
                                "Status"
                            ),
                            ":",
                            React.createElement(
                                "select",
                                { defaultValue: window.newjennysplace.backend.page_data.status, className: "status form-control" },
                                React.createElement(
                                    "option",
                                    { value: "Pending" },
                                    "Pending"
                                ),
                                React.createElement(
                                    "option",
                                    { value: "Processing" },
                                    "Processing"
                                ),
                                React.createElement(
                                    "option",
                                    { value: "Shipped" },
                                    "Shipped"
                                ),
                                React.createElement(
                                    "option",
                                    { value: "Cancelled" },
                                    "Cancelled"
                                )
                            )
                        )
                    ),
                    React.createElement(
                        "li",
                        { className: "list-group-item" },
                        React.createElement(
                            "strong",
                            null,
                            "Shipping Method"
                        ),
                        ":",
                        React.createElement(
                            "select",
                            { defaultValue: window.newjennysplace.backend.page_data.shipping_method_id, className: "shipping_method form-control" },
                            window.newjennysplace.backend.page_data.shipping_methods.map(function (object) {
                                return React.createElement(
                                    "option",
                                    { value: object[0] },
                                    object[1]
                                );
                            })
                        )
                    ),
                    React.createElement(
                        "li",
                        { className: "list-group-item" },
                        React.createElement(
                            "strong",
                            null,
                            "Payment Method"
                        ),
                        ": ",
                        window.newjennysplace.backend.page_data.payment_method
                    ),
                    React.createElement(
                        "li",
                        { className: "list-group-item" },
                        React.createElement(
                            "strong",
                            null,
                            "Total Weight"
                        ),
                        ": ",
                        this.state.total_weight,
                        " lbs."
                    ),
                    React.createElement(
                        "li",
                        { className: "list-group-item" },
                        React.createElement(
                            "strong",
                            null,
                            "Order Notes"
                        ),
                        ":  ",
                        window.newjennysplace.backend.page_data.notes
                    ),
                    React.createElement(
                        "li",
                        { className: "list-group-item" },
                        React.createElement(
                            "strong",
                            null,
                            "Date Ordered"
                        ),
                        ": ",
                        window.newjennysplace.backend.page_data.date_created
                    ),
                    React.createElement(
                        "li",
                        { className: "list-group-item" },
                        React.createElement(
                            "strong",
                            null,
                            "Date Shipped"
                        ),
                        ": ",
                        this.state.date_shipped
                    ),
                    React.createElement(
                        "li",
                        { className: "list-group-item" },
                        React.createElement(
                            "a",
                            { href: window.newjennysplace.backend.page_data.fufillment_link },
                            "Click For Fufillment"
                        )
                    ),
                    React.createElement(
                        "li",
                        { className: "list-group-item" },
                        React.createElement(
                            "a",
                            { href: window.newjennysplace.backend.page_data.invoice_link },
                            "Click For Invoice"
                        )
                    )
                )
            )
        );
    }
});

var OrderLineItemElement = React.createClass({
    displayName: "OrderLineItemElement",

    render: function render() {
        return React.createElement(
            "tr",
            { className: "line_item", "data-line-id": this.props.data.id },
            React.createElement(
                "td",
                null,
                React.createElement("input", { type: "checkbox", "data-line-id": this.props.data.id })
            ),
            React.createElement(
                "td",
                null,
                React.createElement(
                    "a",
                    { className: "fancybox", href: this.props.data.image },
                    React.createElement("img", { style: { width: 32 }, src: this.props.data.image })
                )
            ),
            React.createElement(
                "td",
                null,
                this.props.data.number
            ),
            React.createElement(
                "td",
                null,
                React.createElement("input", { className: "item_qty", style: { width: 60 }, type: "text", defaultValue: this.props.data.quantity })
            ),
            React.createElement(
                "td",
                null,
                "$",
                React.createElement("input", { className: "item_price", style: { width: 80 }, type: "text", defaultValue: this.props.data.price })
            ),
            React.createElement(
                "td",
                null,
                "$",
                React.createElement("input", { className: "item_tax", style: { width: 80 }, type: "text", defaultValue: this.props.data.tax })
            ),
            React.createElement(
                "td",
                null,
                React.createElement("input", { className: "item_name", type: "text", defaultValue: this.props.data.name })
            ),
            React.createElement(
                "td",
                null,
                React.createElement("input", { className: "item_weight", style: { width: 60 }, type: "text", defaultValue: this.props.data.weight }),
                " lbs."
            ),
            React.createElement(
                "td",
                null,
                React.createElement(
                    "ul",
                    { style: { fontSize: 10 } },
                    this.props.data.attributes.map(function (attribute) {
                        return React.createElement(
                            "li",
                            null,
                            attribute[0],
                            " - ",
                            attribute[1]
                        );
                    })
                )
            ),
            React.createElement(
                "td",
                null,
                "$",
                this.props.data.total
            )
        );
    }
});

var OrderLineItems = React.createClass({
    displayName: "OrderLineItems",

    addLineItem: function addLineItem(e) {
        this.state.line_items.push(e.detail);
        this.setState(this.state.line_items);
    },

    componentDidMount: function componentDidMount() {
        window.addEventListener('add-line-item', this.addLineItem);
    },

    getInitialState: function getInitialState() {
        return {
            line_items: window.newjennysplace.backend.page_data.line_items
        };
    },

    render: function render() {
        return React.createElement(
            "table",
            { className: "table" },
            React.createElement(
                "tbody",
                null,
                React.createElement(
                    "tr",
                    null,
                    React.createElement(
                        "th",
                        null,
                        " "
                    ),
                    React.createElement(
                        "th",
                        null,
                        "Image"
                    ),
                    React.createElement(
                        "th",
                        null,
                        "Sku Number"
                    ),
                    React.createElement(
                        "th",
                        null,
                        "Qty."
                    ),
                    React.createElement(
                        "th",
                        null,
                        "Price"
                    ),
                    React.createElement(
                        "th",
                        null,
                        "Tax"
                    ),
                    React.createElement(
                        "th",
                        null,
                        "Name"
                    ),
                    React.createElement(
                        "th",
                        null,
                        "Weight"
                    ),
                    React.createElement(
                        "th",
                        null,
                        "Attributes"
                    ),
                    React.createElement(
                        "th",
                        null,
                        "Total"
                    )
                ),
                this.state.line_items.map(function (line_item) {
                    return React.createElement(OrderLineItemElement, { data: line_item });
                })
            )
        );
    }

});

// Render elements
var elements = document.getElementsByClassName('component-product-search');
for (var x in elements) {
    if (elements.hasOwnProperty(x)) {
        var element = elements[x];
        ReactDOM.render(React.createElement(ProductSearch, null), element);
    }
}

try {
    ReactDOM.render(React.createElement(BulkEditParentTable, { data: window.newjennysplace.backend.page_data }), document.getElementById('bulk_edit_main'));
} catch (err) {};
try {
    ReactDOM.render(React.createElement(OrderStats, null), document.getElementById('order_info'));
} catch (err) {};
try {
    ReactDOM.render(React.createElement(OrderLineItems, null), document.getElementById('order_line_items'));
} catch (err) {};