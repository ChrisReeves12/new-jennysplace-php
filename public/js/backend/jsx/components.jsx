/**
 * components
 *
 * This file contains components used in the backend
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

var SearchListElement = React.createClass({

    render: function () {

        return (<a data-info={JSON.stringify(this.props.element)} className="com-search-list-element-link" href={this.props.element.href}>
            <li className="com-search-list-element">
                <div className="row">
                    <div className="col-xs-2">
                        <img width="64" height="64" src={this.props.element.img}/>
                    </div>
                    <div className="col-xs-10">
                        <h5>{this.props.element.label}</h5>
                        <p>{this.props.element.description}</p>
                    </div>
                </div>
        </li></a>);
    }
});

var SearchList = React.createClass({

    render: function () {

        var output;

        if (this.props.status == 'loading')
        {
            output = (<div className="com-search-list-container">Loading results...please wait</div>);
        }
        else if (typeof this.props.data && this.props.data.length > 0)
        {
            output = (<div className="com-search-list-container">
                <ul className="com-search-list">
                {this.props.data.map(function (element) {
                    return (<SearchListElement element={element}/>);
                })}
            </ul>
            </div>);
        }
        else
        {
            output = (<span> </span>);
        }

        return output;
    }
});


var ProductSearch = React.createClass({

    keyword: '',
    input_delay: 500,
    timer: null,

    getInitialState: function () {
        return ({
            data: [],
            status: 'show'
        });
    },

    startInputTimer: function (e) {
        this.keyword = e.target.value;
        var this_element = this;

        clearTimeout(this.timer);
        this.timer = setTimeout(function () {this_element.populateSearch()}, this.input_delay);
    },

    // Populates the search box from the server
    populateSearch: function () {

        var products = [];
        var element = this;

        // Make sure string is long enough
        if (this.keyword.length > 3)
        {
            // Show loading
            element.state.status = 'loading';
            element.setState(element.state);

            // Go to server
            $.ajax({
                url: '',
                method: 'post',
                data: {task: 'product_search', value: element.keyword},
                dataType: 'json',
                success: function (msg) {
                    if (!msg.error)
                    {
                        element.state.data = msg.products;
                        element.state.status = 'show';
                        element.setState(element.state);
                    }
                    else
                    {
                        alert(msg.message);
                    }
                }
            });
        }
    },

    render: function () {

        var output;

        output = <div className="form-group">
            <input onChange={this.startInputTimer} type="text" placeholder="Search Product" className="form-control"/>
            <SearchList status={this.state.status} data={this.state.data}/>
        </div>;

        return output;
    }
});

var BulkEditRow = React.createClass({

    render: function () {

        var element = this;
        var row_class;

        // Sorting is only allowed when a category is selected
        if (this.props.category == 0)
        {
            row_class = "";
        }
        else
        {
            row_class = "bulk-edit-row";
        }

        return (
            <tr data-row-id={this.props.idx} className={row_class}>
                {this.props.data.fields.map(function (field_content) {
                    return (
                        <td>
                            {(() => {
                                if (field_content.type == 'image')
                                    return (<a className="fancybox" href={field_content.value}><img src={field_content.value} width="96" height="96"/></a>);
                                else if(field_content.type == 'literal')
                                    return (<span>{field_content.value}</span>);
                                    else if(field_content.type == 'href')
                                        return (<a target="_blank" href={field_content.href}>{field_content.value}</a>)
                                else if(field_content.type == 'checkbox') {
                                    if (typeof field_content.status !== 'undefined' && field_content.status == true)
                                        return (<input type="checkbox" defaultChecked value={field_content.value} name={field_content.id}/>);
                                    else
                                        return (<input type="checkbox" value={field_content.value} name={field_content.id}/>);
                                }
                            })()}
                        </td>
                    );
                })}
            </tr>);
    }
});


// The main table that lists mass edit items
var BulkEditParentTable = React.createClass({

    getInitialState: function () {
        return ({
            data: {rows: this.props.data.rows},
            page: 0,
            loading: 'none',
            category: 0,
            filter: 'all'
        });
    },

    // Show more items in the list
    onViewMore: function (e) {
        e.preventDefault();
        var element = this;

        element.state.loading = 'view_more';
        element.setState(element.state);

        $.get('', {category: element.state.category, page: element.state.page, filter: element.state.filter, task: 'view_more'}, function (msg) {

            if (!msg.error) {

                // Add rows
                element.state.data.rows = element.state.data.rows.concat(msg.rows);
                element.state.page++;
                element.state.loading = 'none';
                element.setState(element.state);
            }
            else
            {
                alert(msg.message);
            }

        }, 'json');
    },

    refresh: function () {

        var element = this;

        $.get('', {category: element.state.category, filter: element.state.filter, task: 'refresh'}, function (msg) {

            if (!msg.error) {

                element.state.data.rows = msg.rows;
                element.state.page = 0;
                element.state.loading = 'none';
                element.setState(element.state);
                element.unselectAll();
            }
            else
            {
                alert(msg.message);
            }

        }, 'json');

    },

    change_categories: function (e) {

        e.preventDefault();
        this.state.category = e.target.value;
        this.setState(this.state);

        this.refresh();
    },

    delete_selected: function (e) {
        e.preventDefault();

        var element = this;
        var ids_to_delete = [];
        $('input[name="id"]:checked').each(function (object) {
            ids_to_delete.push(parseInt($(this).val()))
        });

        if (confirm("Are you sure you want to delete the following items?"))
        {
            $.post('', {task: 'delete', ids: ids_to_delete}, function (msg) {

                if (!msg.error) {
                    // Delete the rows from the state
                    for (var idx in element.state.data.rows)
                    {
                        if (element.state.data.rows.hasOwnProperty(idx))
                        {
                            var row = element.state.data.rows[idx];
                            var row_id = parseInt(row['fields'][0]['value']);

                            // Delete the row out of the state if there is a match
                            if (ids_to_delete.indexOf(row_id) >= 0) {
                                delete element.state.data.rows[idx];
                            }
                        }
                    }

                    element.setState(element.state);
                }
                else
                {
                    alert(msg.message);
                }

            }, 'json');
        }
    },

    update_important: function (e) {
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

        $.post('', {ids_to_make_important: ids_to_make_important, ids_to_make_normal: ids_to_make_normal, task: 'update_important'}, function (msg) {

            if (!msg.error)
            {
                alert("Products have been updated.");
            }
            else
            {
                alert(msg.message);
            }

        }, 'json');
    },

    activateDateAdd: function () {
        $('.datepicker').datepicker();
    },

    onStatusChange: function (e) {

        e.preventDefault();
        var status_override = $("select[name='status-override']").val();
        var element = this;
        var ids = [];

        $('input[name="id"]:checked').each(function (object) {
            ids.push(parseInt($(this).val()))
        });

        $.post('', {task: 'status_change', status: status_override, ids: ids}, function (msg) {

            if (!msg.error)
            {
                // Update the rows
                for (var i in element.state.data.rows) {
                    if (element.state.data.rows.hasOwnProperty(i))
                    {
                        var id = parseInt(element.state.data.rows[i]['fields'][0]['value']);
                        if (ids.indexOf(id) >= 0)
                            element.state.data.rows[i]['fields'][7]['value'] = msg.status_name;
                    }
                }

                element.setState(element.state);
            }
            else
            {
                alert(msg.message);
            }

        }, 'json');

    },

    onDateChange: function (e) {

        var date = $("input[name='date-added']").val();
        var element = this;
        var ids = [];

        $('input[name="id"]:checked').each(function (object) {
            ids.push(parseInt($(this).val()))
        });

        $.post('', {task: 'date_change', date: date, ids: ids}, function (msg) {

            if (!msg.error)
            {
                // Update the rows
                for (var i in element.state.data.rows) {
                    if (element.state.data.rows.hasOwnProperty(i))
                    {
                        var id = parseInt(element.state.data.rows[i]['fields'][0]['value']);
                        if (ids.indexOf(id) >= 0)
                            element.state.data.rows[i]['fields'][8]['value'] = date;
                    }
                }

                element.setState(element.state);
            }
            else
            {
                alert(msg.message);
            }

        }, 'json');

    },

    unselectAll: function (e) {
        $("input[name='id']:checked").prop("checked", false);
    },

    selectAll: function (e) {
        $("input[name='id']:not(:checked)").prop("checked", true);
    },

    categoryDialog: function (e) {
        $("#editCategoriesModal").modal('show');
    },

    populateSubDialogCategories: function (e) {

        // Clear sub category section
        $("div.sub-categories-checkbox-section").html("");

        var category_id = $(e.target).val();
        var data = this.props.data.sub_category_data;

        if (typeof data[category_id] !== "undefined" && data[category_id].length > 0) {

            // Create list
            var sub_cat_checkbox_list = $("<ul class='list-group'></ul>");
            sub_cat_checkbox_list.append("<li class='list-group-item'><input name='category-checkbox' value='"+category_id+"' type='checkbox' /> PARENT CATEGORY</li>");

            $.each(data[category_id], function (index, sub_category) {
                sub_cat_checkbox_list.append("<li class='list-group-item'> <input name='category-checkbox' data-name='"+sub_category[1]+"' value='"+sub_category[0]+"' type='checkbox'/> "+sub_category[1]+"</li>");
            });

            $("div.sub-categories-checkbox-section").html(sub_cat_checkbox_list);
        }
    },

    componentDidMount: function () {

        var element = this;

        // Bind delete function to category delete
        $("body").delegate('.cat_delete', 'click', function (e) {
            element.deleteCategoryFromDialog(e);
        });

        $('tbody').sortable({
            revert: false
        });
    },

    update_sort_ordering: function (e) {

        var element = this;
        if (element.state.category == 0)
        {
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
        $.post('', {task: 'update_sort_order', category: element.state.category, product_ids: product_ids},
            function (msg) {
                if (!msg.error) {
                    window.newjennysplace.utils.killWaitScreen();
                    alert("Product Order Saved Successfully!");
                }
                else
                {
                    window.newjennysplace.utils.killWaitScreen();
                    alert(msg.message);
                }
            }, 'json');

    },

    closeCategoriesDialog: function (e) {
        $("#editCategoriesModal").modal('hide');
    },

    saveCategoriesDialog: function (e) {

        var element = this;
        var save_method = $(e.target).parent().attr('data-method');

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

        if (selected_product_ids.length > 0)
        {
            if (category_ids.length > 0)
            {
                if (confirm("You sure you want to update the selected products' categories? This action cannot be undone.")) {
                    // Send information to server
                    $.post('', {
                        task: 'update_categories',
                        category_ids: category_ids,
                        product_ids: selected_product_ids,
                        save_method: save_method
                    }, function (msg) {
                        if (!msg.error) {
                            $("#editCategoriesModal").modal('hide');
                            element.refresh();
                        }
                        else {
                            alert(msg.message);
                        }
                    }, 'json');
                }
            }
            else
            {
                alert("Please add at least one category, no changes will apply.");
            }
        }
        else
        {
            alert("No products are selected for update, no changes will apply.");
        }
    },

    deleteCategoryFromDialog: function (e) {
        e.preventDefault();
        $(e.target).parents('div.cat_entry').remove();
    },

    addCategoriesFromDialog: function (e) {

        var main_category_id = $("select.main-category-select").val();
        var element = this;

        if (main_category_id > 0)
        {
            // If this category has no sub categories, just add the main category to the list
            if (element.props.data.sub_category_data[main_category_id].length == 0) {

                var main_category_name = element.props.data.main_category_dictionary[main_category_id];

                if ($("div.dialog-categories-box").find("div[data-cat='"+main_category_id+"']").length == 0)
                    $("div.dialog-categories-box").append("<div data-cat='"+main_category_id+"' class='cat_entry'><a class='cat_delete' data-cat='"+main_category_id+"' href=''><i class='fa fa-close'></i></a> "+main_category_name+"</div>");

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

                    if (typeof element.props.data.sub_category_dictionary[main_category_id][category_id] !== "undefined") {

                        var category_name = element.props.data.sub_category_dictionary[main_category_id][category_id];

                        // Check if category id is already in the box
                        if ($("div.dialog-categories-box").find("div[data-cat='"+category_id+"']").length == 0)
                            $("div.dialog-categories-box").append("<div data-cat='"+category_id+"' class='cat_entry'><a class='cat_delete' data-cat='"+category_id+"' href=''><i class='fa fa-close'></i></a> "+category_name+"</div>");

                    }
                    else
                    {
                        // Add main category as parent category
                        var category_name = element.props.data.main_category_dictionary[category_id];

                        // Check if category id is already in the box
                        if ($("div.dialog-categories-box").find("div[data-cat='"+category_id+"']").length == 0)
                            $("div.dialog-categories-box").append("<div data-cat='"+category_id+"' class='cat_entry'><a class='cat_delete' data-cat='"+category_id+"' href=''><i class='fa fa-close'></i></a> "+category_name+"</div>");
                    }
                });
            }
        }

    },

    // Change search filter
    changeFilter: function (e) {

        var filter = $(e.target).val();
        this.state.filter = filter;
        this.setState(this.state);

        // Refresh view
        this.refresh();
    },

    // Main render function
    render: function () {

        var output;
        var element = this;
        var rows_data = this.state.data.rows;

        // Only category display can be sortable
        var tbody_class;
        if (this.state.category == 0)
        {
            tbody_class = ""
        }
        else
        {
            tbody_class = "sortable";
        }

        output = (
            <div>
                <div id="editCategoriesModal" className="modal fade" role="dialog">
                    <div className="modal-dialog">
                        <div className="modal-content">
                            <div className="modal-header">
                                <h2>Category Dialog</h2>
                            </div>
                            <div className="modal-body">
                                <div className="row">
                                    <div className="col-xs-6">
                                        <div className="row">
                                            <div className="col-xs-12">
                                                <div className="form-group">
                                                    <label htmlFor="main-category-select">Select Parent Category</label>
                                                    <select onChange={this.populateSubDialogCategories} name="main-category-select" className="form-control main-category-select">
                                                        <option value="0">Select Category</option>
                                                        {this.props.data.main_categories.map(function (category) {
                                                            return (<option value={category[0]}>{category[1]}</option>);
                                                        })}
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="row">
                                            <div className="col-xs-12">
                                                <label>This Product's Categories</label>
                                                <div className="scrollbox dialog-categories-box">

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="col-xs-6 sub-categories-checkbox-section">

                                    </div>
                                </div>
                            </div>
                            <div className="modal-footer">
                                <div className="row">
                                    <div className="col-xs-4">
                                        <button data-method="change" onClick={this.saveCategoriesDialog} className="btn btn-success dialog-add-categories-save"><i className="fa fa-gavel"></i> Change Categories</button>
                                    </div>
                                    <div className="col-xs-4">
                                        <button data-method="add" onClick={this.saveCategoriesDialog} className="btn btn-success dialog-add-categories-save"><i className="fa fa-plus-square"></i> Add Categories</button>
                                    </div>
                                    <div className="col-xs-4">
                                        <button onClick={this.addCategoriesFromDialog} className="btn btn-default dialog-add-categories"><i className="fa fa-hand-pointer-o"></i> Select Categories</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <div className="row bulk-edit-box">
                <h3>Edit Menu</h3>
                <div className="form-group">
                    <button onClick={this.selectAll} className="btn btn-default"><i className="fa fa-mouse-pointer"></i> Select All</button>
                </div>
                <div className="form-group">
                    <button onClick={this.unselectAll} className="btn btn-default"><i className="fa fa-ban"></i> Unselect All</button>
                </div>
                <div className="form-group">
                    <button onClick={this.categoryDialog} className="btn btn-default"><i className="fa fa-edit"></i> Category Dialog</button>
                </div>
                <div className="form-group">
                    <label htmlFor="filter">Filter</label>
                    <select onChange={this.changeFilter} name="filter" className="form-control">
                        <option value="all">Show All</option>
                        <option value="in_stock">Show In Stock</option>
                        <option value="out_of_stock">Show Out Of Stock</option>
                        <option value="disabled">Show Disabled</option>
                    </select>
                </div>
                <div className="form-group">
                    <label>Category</label>
                    <select onChange={this.change_categories} className="form-control">
                        <option value="0">All Categories</option>
                        {this.props.data.categories.map(function (category) {
                            return (<option value={category.id}>{category.name}</option>);
                        })}
                    </select>
                </div>
                <div className="form-group">
                    <a onClick={this.delete_selected} className="btn btn-danger" href=""><i className="fa fa-remove"></i> Delete Selected</a>
                </div>
                <div className="form-group">
                    <a onClick={this.update_important} className="btn btn-default" href=""><i className="fa fa-save"></i> Save Important Status</a>
                </div>
                <div className="form-group">
                    <label>Status Override</label>
                    <select name="status-override" className="form-control">
                        {this.props.data.product_statuses.map(function (status) {
                            return (<option value={status.id}>{status.name}</option>);
                        })}
                    </select>
                </div>
                <div className="form-group">
                    <button onClick={this.onStatusChange} className="btn btn-default"><i className="fa fa-save"></i> Save Status Changes</button>
                </div>
                <div className="form-group">
                    <label>Date Added</label>
                    <input onFocus={this.activateDateAdd} name="date-added" type="text" className="form-control datepicker"/>
                </div>
                <div className="form-group">
                    <button onClick={this.onDateChange} className="btn btn-default"><i className="fa fa-calendar"></i> Save Date Added</button>
                </div>
                <div className="form-group">
                    <button onClick={element.update_sort_ordering} className="btn btn-default"><i className="fa fa-sort"></i> Save Sort Order</button>
                </div>
            </div>
            <table className="table">
                <tbody className={tbody_class}>
                    <tr>
                        {this.props.data.headers.map(function (header) { return (<th>{header}</th>) })}
                    </tr>
                    {rows_data.map(function (row_data, idx) {
                        return (<BulkEditRow category={element.state.category} idx={idx} data={row_data} />);
                    })}
                    {(() => {
                        if (element.state.loading == 'view_more') {
                            return (<tr>
                                <td><img src="/img/layout_images/loader.gif" width="45" height="45"/></td>
                            </tr>);
                        }
                        else
                        {
                            return (<tr>
                                <td><a onClick={element.onViewMore} className="btn btn-default" href=""><i className="fa fa-arrow-circle-down"></i> View More</a></td>
                            </tr>);
                        }
                    })()}
                </tbody>
            </table></div>);

        return output;
    }
});


var OrderStats = React.createClass({

    getInitialState: function () {

        return ({
            status: 'show',
            sub_total: window.newjennysplace.backend.page_data.sub_total,
            tax: window.newjennysplace.backend.page_data.tax,
            shipping_cost: window.newjennysplace.backend.page_data.shipping_cost,
            tracking_number: window.newjennysplace.backend.page_data.tracking_number,
            total_discount: window.newjennysplace.backend.page_data.total_discount,
            store_credit: window.newjennysplace.backend.page_data.store_credit,
            total: window.newjennysplace.backend.page_data.total,
            total_weight: window.newjennysplace.backend.page_data.total_weight,
            date_shipped: window.newjennysplace.backend.page_data.date_shipped,
        });
    },

    handleUpdateOrderStats: function (e) {
        // Set state with incoming data
        this.setState(e.detail);
    },

    showLoaders: function (e) {
        this.state.status = 'loading';
        this.setState(this.state);
    },

    componentDidMount: function () {
        window.addEventListener('update-order-stats', this.handleUpdateOrderStats);
        window.addEventListener('show-order-stat-loaders', this.showLoaders);
    },

    render: function () {

        if (this.state.status == 'loading')
        {
            return (<h4><i className="fa fa-hourglass-half"></i> Loading...please wait...</h4>);
        }

        return (
            <div id="order_info">
                <div className="inline">
                    <ul className="list-group">
                        <li className="list-group-item"><strong>Auth Code</strong>: <input className="auth_code" type="text" defaultValue={window.newjennysplace.backend.page_data.auth_code}/></li>
                        <li className="list-group-item"><strong>Sub-Total</strong>: ${this.state.sub_total}</li>
                        <li className="list-group-item"><strong>Tax</strong>: $<input className="tax" type="text" defaultValue={this.state.tax}/></li>
                        <li className="list-group-item"><strong>Shipping Cost</strong>: $<input className="shipping_cost" type="text" defaultValue={this.state.shipping_cost}/></li>
                        <li className="list-group-item"><strong>Tracking Number</strong>: <input style={{fontSize: 10}} placeholder="Tracking Number" className="tracking_number" type="text" defaultValue={this.state.tracking_number}/></li>
                        <li className="list-group-item"><strong>Total Discount</strong>: $<input className="total_discount" type="text" defaultValue={this.state.total_discount}/></li>
                        <li className="list-group-item"><strong>Store Credit</strong>: -${this.state.store_credit}</li>
                        <li className="list-group-item"><strong>Original Grand Total</strong>: ${window.newjennysplace.backend.page_data.original_total}</li>
                        <li className="list-group-item"><strong>GRAND TOTAL</strong>: ${this.state.total}</li>
                    </ul>
                </div>
                <div className="inline">
                    <ul className="list-group">
                        <li className="list-group-item">
                            <div class="form-group">
                            <strong>Status</strong>:
                            <select defaultValue={window.newjennysplace.backend.page_data.status} className="status form-control">
                                <option value="Pending">Pending</option>
                                <option value="Processing">Processing</option>
                                <option value="Shipped">Shipped</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                            </div>
                        </li>
                        <li className="list-group-item">
                            <strong>Shipping Method</strong>:
                            <select defaultValue={window.newjennysplace.backend.page_data.shipping_method_id} className="shipping_method form-control">
                                {window.newjennysplace.backend.page_data.shipping_methods.map(function (object) {
                                        return (<option value={object[0]} >{object[1]}</option>)
                                    })}
                            </select>
                        </li>
                        <li className="list-group-item"><strong>Payment Method</strong>: {window.newjennysplace.backend.page_data.payment_method}</li>
                        <li className="list-group-item"><strong>Total Weight</strong>: {this.state.total_weight} lbs.</li>
                        <li className="list-group-item"><strong>Order Notes</strong>:  {window.newjennysplace.backend.page_data.notes}</li>
                        <li className="list-group-item"><strong>Date Ordered</strong>: {window.newjennysplace.backend.page_data.date_created}</li>
                        <li className="list-group-item"><strong>Date Shipped</strong>: {this.state.date_shipped}</li>
                        <li className="list-group-item"><a href={window.newjennysplace.backend.page_data.fufillment_link}>Click For Fufillment</a></li>
                        <li className="list-group-item"><a href={window.newjennysplace.backend.page_data.invoice_link}>Click For Invoice</a></li>
                    </ul>
                </div>
            </div>
        );
    }
});

var OrderLineItemElement = React.createClass({

    render: function () {
        return (
            <tr className="line_item" data-line-id={this.props.data.id}>
                <td><input type="checkbox" data-line-id={this.props.data.id}/></td>
                <td>
                    <a className="fancybox" href={this.props.data.image}>
                        <img style={{width: 32}} src={this.props.data.image}/>
                    </a>
                </td>
                <td>{this.props.data.number}</td>
                <td><input className="item_qty" style={{width: 60}} type="text" defaultValue={this.props.data.quantity} /></td>
                <td>$<input className="item_price" style={{width: 80}} type="text" defaultValue={this.props.data.price} /></td>
                <td>$<input className="item_tax" style={{width: 80}} type="text" defaultValue={this.props.data.tax} /></td>
                <td><input className="item_name" type="text" defaultValue={this.props.data.name} /></td>
                <td><input className="item_weight" style={{width: 60}} type="text" defaultValue={this.props.data.weight} /> lbs.</td>
                <td>
                    <ul style={{fontSize: 10}}>
                        {this.props.data.attributes.map(function (attribute) {
                            return <li>{attribute[0]} - {attribute[1]}</li>;
                            })}
                    </ul>
                </td>
                <td>${this.props.data.total}</td>
            </tr>
        );
    }
});

var OrderLineItems = React.createClass({

    addLineItem: function (e) {
        this.state.line_items.push(e.detail);
        this.setState(this.state.line_items);
    },

    componentDidMount: function () {
        window.addEventListener('add-line-item', this.addLineItem);
    },

    getInitialState: function () {
        return ({
            line_items: window.newjennysplace.backend.page_data.line_items
        });
    },

    render: function () {
        return (
            <table className="table">
                <tbody>
                <tr>
                    <th> </th>
                    <th>Image</th>
                    <th>Sku Number</th>
                    <th>Qty.</th>
                    <th>Price</th>
                    <th>Tax</th>
                    <th>Name</th>
                    <th>Weight</th>
                    <th>Attributes</th>
                    <th>Total</th>
                </tr>
                {this.state.line_items.map(function (line_item) {
                    return <OrderLineItemElement data={line_item}/>;
                    })}
                </tbody>
            </table>
        );
    }

});

// Render elements
var elements = document.getElementsByClassName('component-product-search');
for (var x in elements) {
    if (elements.hasOwnProperty(x)) {
        var element = elements[x];
        ReactDOM.render(<ProductSearch/>, element);
    }
}

try { ReactDOM.render(<BulkEditParentTable data={window.newjennysplace.backend.page_data}/>, document.getElementById('bulk_edit_main')); } catch (err) {};
try { ReactDOM.render(<OrderStats/>, document.getElementById('order_info')); } catch (err) {};
try { ReactDOM.render(<OrderLineItems/>, document.getElementById('order_line_items')); } catch (err) {};

