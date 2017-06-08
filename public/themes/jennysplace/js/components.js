'use strict';

window.newjennysplace = window.newjennysplace || {};
window.newjennysplace.components = window.newjennysplace.components || {};

/**
 * Displays the total cart amount on the layout screen
 */
var CartDisplay = React.createClass({
    displayName: 'CartDisplay',

    /**
     * Initializes the state of the component
     */
    getInitialState: function getInitialState() {
        return {
            cart_total: window.newjennysplace.page.cart_total,
            cart_qty: window.newjennysplace.page.cart_qty,
            add_to_cart_product: null
        };
    },

    /**
     * Updates the price displayed on the cart item
     * @param object new_price
     */
    update: function update(e) {
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

    componentDidMount: function componentDidMount() {
        window.addEventListener('update-cart-display', this.update);
    },

    /**
     * Renders the output
     */
    render: function render() {
        var _this = this;

        var url = 'https://' + window.location.hostname + '/shopping-cart';

        return React.createElement(
            'div',
            { style: { position: 'relative' } },
            'Cart ',
            React.createElement('span', { className: 'glyphicon glyphicon-shopping-cart' }),
            function () {
                if (_this.state.cart_qty == 0) return React.createElement(
                    'span',
                    null,
                    '0'
                );else return React.createElement(
                    'span',
                    null,
                    React.createElement(
                        'span',
                        { className: 'highlight' },
                        _this.state.cart_qty
                    ),
                    ' | ',
                    React.createElement(
                        'span',
                        { className: 'highlight' },
                        '$',
                        parseFloat(_this.state.cart_total).formatMoney()
                    )
                );
            }(),
            React.createElement('br', null),
            React.createElement(
                'a',
                { href: url, className: 'btn btn-danger btn-xs' },
                React.createElement('i', { className: 'glyphicon glyphicon-shopping-cart' }),
                ' Checkout'
            ),
            function () {
                // Add to cart notification
                if (_this.state.add_to_cart_product != null) {
                    return React.createElement(
                        'div',
                        { className: 'custom-popup', style: { position: 'absolute', zIndex: 1000, fontSize: 13, left: 45, top: 43, padding: 4, borderRadius: 5, backgroundColor: 'white', width: 300, borderStyle: 'solid', borderColor: 'gray', borderWidth: 1 } },
                        React.createElement(
                            'div',
                            { className: 'row' },
                            React.createElement(
                                'div',
                                { className: 'col-xs-2' },
                                React.createElement(
                                    'a',
                                    { href: _this.state.add_to_cart_product.href },
                                    React.createElement('img', { src: _this.state.add_to_cart_product.image, width: '45', height: '45' })
                                )
                            ),
                            React.createElement(
                                'div',
                                { className: 'col-xs-10' },
                                React.createElement(
                                    'h5',
                                    null,
                                    'Product Added To Cart!'
                                ),
                                React.createElement(
                                    'p',
                                    null,
                                    React.createElement(
                                        'strong',
                                        null,
                                        React.createElement(
                                            'a',
                                            { href: _this.state.add_to_cart_product.href },
                                            _this.state.add_to_cart_product.name
                                        )
                                    ),
                                    React.createElement('br', null),
                                    'Quantity: ',
                                    _this.state.add_to_cart_product.quantity,
                                    React.createElement('br', null),
                                    React.createElement('br', null),
                                    React.createElement(
                                        'a',
                                        { href: url, className: 'btn btn-success' },
                                        React.createElement('i', { className: 'fa fa-shopping-cart' }),
                                        ' Proceed To Checkout'
                                    )
                                )
                            )
                        )
                    );
                }
            }()
        );
    }
});

/**
 * Displays the cart totals on the shopping cart page
 */
var CartTotals = React.createClass({
    displayName: 'CartTotals',

    /**
     * Initializes the state of the component
     */
    getInitialState: function getInitialState() {
        return {
            cart_total: window.newjennysplace.page.cart_total,
            cart_grand_total: window.newjennysplace.page.cart_grand_total,
            order_discount: window.newjennysplace.page.order_discount,
            order_tax: window.newjennysplace.page.order_tax,
            shipping_cost: window.newjennysplace.page.shipping_cost,
            store_credit: window.newjennysplace.page.store_credit,
            add_to_cart_message: null,
            loading: false,
            error: false
        };
    },

    componentDidMount: function componentDidMount() {
        window.addEventListener('show-order-totals-loading', this.show_loading);
        window.addEventListener('stop-order-totals-loading', this.stop_loading);
        window.addEventListener('update-order-totals', this.update);
        window.addEventListener('show-order-total-error', this.show_error);
    },

    /**
     * Shows loading message when prices are being calculated
     */
    show_loading: function show_loading() {
        this.state.loading = true;
        this.setState(this.state);
    },

    /**
     * Stops loading message when prices are being calculated
     */
    stop_loading: function stop_loading() {
        this.state.loading = false;
        this.setState(this.state);
    },

    /**
     * Shows error message on timeout
     * @param string error_msg
     */
    show_error: function show_error(e) {
        this.state.error = e.detail;
        this.setState(this.state);
    },

    /**
     * Updates the totals in the shopping cart
     * @param object data
     */
    update: function update(e) {
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
    render: function render() {
        var output;
        var discount_element;
        var tax_element;
        var store_credit_element;
        var shipping_price_display;

        if (this.state.shipping_cost == 0) {
            shipping_price_display = "Free";
        } else {
            shipping_price_display = '$' + parseFloat(this.state.shipping_cost).formatMoney();
        }

        if (this.state.error === false) {
            if (this.state.loading === false) {
                if (window.newjennysplace.page.cart_qty > 0) {
                    if (this.state.order_discount > 0) discount_element = React.createElement(
                        'p',
                        null,
                        React.createElement(
                            'strong',
                            null,
                            'Discount Amount'
                        ),
                        ': -$',
                        parseFloat(this.state.order_discount).formatMoney()
                    );

                    if (this.state.tax > 0) tax_element = React.createElement(
                        'p',
                        null,
                        React.createElement(
                            'strong',
                            null,
                            'Sales Tax'
                        ),
                        ': $',
                        parseFloat(this.state.order_tax).formatMoney()
                    );

                    if (this.state.store_credit > 0) store_credit_element = React.createElement(
                        'p',
                        null,
                        React.createElement(
                            'strong',
                            null,
                            'Store Credit'
                        ),
                        ': -$',
                        parseFloat(this.state.store_credit).formatMoney()
                    );

                    output = React.createElement(
                        'div',
                        null,
                        React.createElement(
                            'p',
                            null,
                            React.createElement(
                                'strong',
                                null,
                                'Order Sub-Total'
                            ),
                            ': $',
                            parseFloat(this.state.cart_total).formatMoney()
                        ),
                        discount_element,
                        tax_element,
                        React.createElement(
                            'p',
                            null,
                            React.createElement(
                                'strong',
                                null,
                                'Shipping Total'
                            ),
                            ': ',
                            shipping_price_display
                        ),
                        store_credit_element,
                        React.createElement(
                            'p',
                            { className: 'grand-total' },
                            'Order Grand Total: $',
                            parseFloat(this.state.cart_grand_total).formatMoney()
                        )
                    );
                }
            } else {
                output = React.createElement(
                    'div',
                    null,
                    React.createElement(
                        'p',
                        { className: 'wait-message' },
                        React.createElement('i', { className: 'fa fa-hourglass-2' }),
                        ' Updating prices...please wait'
                    )
                );
            }
        } else {
            // Error
            output = React.createElement(
                'div',
                null,
                React.createElement(
                    'p',
                    { className: 'error-message' },
                    React.createElement('i', { className: 'fa fa-close' }),
                    ' ',
                    this.state.error
                )
            );
        }

        return output;
    }
});

// Render components
try {
    ReactDOM.render(React.createElement(CartDisplay, null), document.getElementById('cart'));
    ReactDOM.render(React.createElement(CartTotals, null), document.getElementById('order_totals'));
} catch (err) {}