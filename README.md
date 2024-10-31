# PayDollar/PesoPay/SiamPay Payment plugin for Woocommerce
Use PayDollar/PesoPay/SiamPay plugin for Woocommerce on Wordpress to offer ALL payments option.

## Download
Please download the latest plugin version. [Download](https://github.com/asiapay-lib/asiapay-Woocommerce/releases/latest)

## Integration
The plugin integrates Woocommerce on Wordpress with PayDollar/PesoPay/SiamPay payment gateway with All payment methods.

## Requirements
This plugin supports Woocommerce version 2.1.2 and higher, Wordpress version 3.8.1 and higher.

## Installation
1.	Upload the paydollar plugin to the wordpress plugins folder (/wordpress/wp-content/plugins/paydollar-payment-gateway-woocommerce/) using an FTP client.
2.	Login to WordPress Backend
3.	Go to the plugins section.
4.	Find the WooCommerce PayDollar Payment Gateway and click “Activate”
5.	Go to WooCommerce section > Settings.
6.	Go to Payment Gateways Tab.
7.	Go to PayDollar.
8.	Set the module configurations

## Setup the Datafeed URL on PayDollar/PesoPay/SiamPay
 1. Login to your PayDollar/PesoPay/SiamPay account.
 2. After login, Go to left sidebar on Profile > Profile Settings > Payment Options.
 3. Click the “Enable” radio button and set the datafeed URL on “Return Value Link” and click the “Update” button. The datafeed URL should be like this: http://www.yourdomain.com/index.php?wc-api=wc_paydollar
 4. On the confirmation page, review your changes then click the “Confirm button”.

## Woocommerce 8.3+
If you are using Woocommerce version 8.3 or above, you may find the checkout option is not displayed althought the plugin installation is completed.
You will be required to follow the steps to switch back to the Classic Checkout mode.
[Woocommerce Documentation: Cart and Checkout Blocks](https://woocommerce.com/document/cart-checkout-blocks-status/#compatible-extensions)

## Documentation
[Woocommerce Documentation](https://github.com/asiapay-lib/Woocommerce/raw/master/WordPress%2BWooCommerce%20Module%20Integration%20Guide.doc)

## Support
If you have a feature request, or spotted a bug or a technical problem, create a GitHub issue. For other questions, contact our [Customer Service](https://www.paydollar.com/en/contact-us.html).

## License
MIT license. For more information, see the LICENSE file.
