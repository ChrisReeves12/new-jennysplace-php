window.newjennysplace = window.newjennysplace || {};
window.newjennysplace.components = window.newjennysplace.components || {};

/**
 * Displays the total cart amount on the layout screen
 */
var CartDisplay = React.createClass({

    /**
     * Initializes the state of the component
     */
    getInitialState: function ()
    {
        return ({
            cart_total: window.newjennysplace.page.cart_total,
            cart_qty: window.newjennysplace.page.cart_qty,
            add_to_cart_product: null
        });
    },

    /**
     * Updates the price displayed on the cart item
     * @param object new_price
     */
    update: function (e)
    {
        var cart_display_element = this;

        this.setState({
            cart_total: e.detail.price,
            cart_qty: e.detail.qty,
            add_to_cart_product: e.detail.product
        });

        setTimeout(function () {
            cart_display_element.state.add_to_cart_product = null;
            cart_display_element.setState(cart_display_element.state);
        }, 4000);
    },

    componentDidMount: function () {
        window.addEventListener('update-cart-display', this.update)
    },

    /**
     * Renders the output
     */
    render: function ()
    {
        var url = 'https://' + window.location.hostname + '/shopping-cart';

        return (
            <div style={{position: 'relative'}}>
                Cart <span className="glyphicon glyphicon-shopping-cart"></span>

                {(() => {
                    if (this.state.cart_qty == 0) return (<span>0</span>);
                        else return (<span><span className="highlight">{this.state.cart_qty}</span> | <span className="highlight">${parseFloat(this.state.cart_total).formatMoney()}</span></span>);
                    })()}

                <br/><a href={url} className="btn btn-danger btn-xs"><i className="glyphicon glyphicon-shopping-cart"></i> Checkout</a>

                {(() => {
                    // Add to cart notification
                    if (this.state.add_to_cart_product != null) {
                        return (
                        <div className="custom-popup" style={{position: 'absolute', zIndex: 1000, fontSize: 13, left: 45, top: 43, padding: 4, borderRadius: 5, backgroundColor: 'white', width: 300, borderStyle: 'solid', borderColor: 'gray', borderWidth: 1}}>
                            <div className="row">
                                <div className="col-xs-2">
                                    <a href={this.state.add_to_cart_product.href}>
                                        <img src={this.state.add_to_cart_product.image} width='45' height='45'/>
                                    </a>
                                </div>
                                <div className="col-xs-10">
                                    <h5>Product Added To Cart!</h5>
                                    <p>
                                        <strong><a href={this.state.add_to_cart_product.href}>{this.state.add_to_cart_product.name}</a></strong><br/>
                                        Quantity: {this.state.add_to_cart_product.quantity}<br/><br/>
                                        <a href={url} className="btn btn-success"><i className="fa fa-shopping-cart"></i> Proceed To Checkout</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                            );
                        }
                    })()}
            </div>
        );
    }
});

/**
 * Displays the cart totals on the shopping cart page
 */
var CartTotals = React.createClass({

    /**
     * Initializes the state of the component
     */
    getInitialState: function ()
    {
        return ({
            cart_total: window.newjennysplace.page.cart_total,
            cart_grand_total: window.newjennysplace.page.cart_grand_total,
            order_discount: window.newjennysplace.page.order_discount,
            order_tax: window.newjennysplace.page.order_tax,
            shipping_cost: window.newjennysplace.page.shipping_cost,
            store_credit: window.newjennysplace.page.store_credit,
            add_to_cart_message: null,
            loading: false,
            error: false
        });
    },

    componentDidMount: function ()
    {
        window.addEventListener('show-order-totals-loading', this.show_loading);
        window.addEventListener('stop-order-totals-loading', this.stop_loading);
        window.addEventListener('update-order-totals', this.update);
        window.addEventListener('show-order-total-error', this.show_error)
    },

    /**
     * Shows loading message when prices are being calculated
     */
    show_loading: function ()
    {
        this.state.loading = true;
        this.setState(this.state);
    },

    /**
     * Stops loading message when prices are being calculated
     */
    stop_loading: function ()
    {
        this.state.loading = false;
        this.setState(this.state);
    },

    /**
     * Shows error message on timeout
     * @param string error_msg
     */
    show_error: function (e)
    {
        this.state.error = e.detail;
        this.setState(this.state);
    },

    /**
     * Updates the totals in the shopping cart
     * @param object data
     */
    update: function (e)
    {
        this.setState({
            cart_total: e.detail.sub_total,
            cart_grand_total: e.detail.grand_total,
            tax: e.detail.tax,
            shipping_cost: e.detail.shipping_cost,
            order_discount: e.detail.order_discount,
            store_credit: e.detail.store_credit,
            loading: false,
            error: false
        });
    },

    /**
     * Renders the output
     */
    render: function ()
    {
        var output;
        var discount_element;
        var tax_element;
        var store_credit_element;
        var shipping_price_display;

        if (this.state.shipping_cost == 0)
        {
            shipping_price_display = "Free";
        }
        else
        {
            shipping_price_display = '$' + parseFloat(this.state.shipping_cost).formatMoney();
        }

        if (this.state.error === false)
        {
            if (this.state.loading === false)
            {
                if (window.newjennysplace.page.cart_qty > 0)
                {
                    if (this.state.order_discount > 0)
                        discount_element = <p><strong>Discount Amount</strong>: -${parseFloat(this.state.order_discount).formatMoney()}</p>;

                    if (this.state.tax > 0)
                        tax_element = <p><strong>Sales Tax</strong>: ${parseFloat(this.state.order_tax).formatMoney()}</p>;

                    if (this.state.store_credit > 0)
                        store_credit_element = <p><strong>Store Credit</strong>: -${parseFloat(this.state.store_credit).formatMoney()}</p>;


                    output = (<div>
                        <p><strong>Order Sub-Total</strong>: ${parseFloat(this.state.cart_total).formatMoney()}</p>
                        {discount_element}
                        {tax_element}
                        <p><strong>Shipping Total</strong>: {shipping_price_display}</p>
                        {store_credit_element}
                        <p className='grand-total'>Order Grand Total: ${parseFloat(this.state.cart_grand_total).formatMoney()}</p>
                    </div>);
                }
            }
            else
            {
                output = (<div><p className="wait-message"><i className='fa fa-hourglass-2'></i> Updating prices...please wait</p></div>);
            }
        }
        else
        {
            // Error
            output = (<div><p className="error-message"><i className='fa fa-close'></i> {this.state.error}</p></div>);
        }

        return output;
    }
});

// Render components
try {
    ReactDOM.render(<CartDisplay/>, document.getElementById('cart'));
    ReactDOM.render(<CartTotals/>, document.getElementById('order_totals'));
} catch (err) { }