<?php
/*
 Plugin Name: WooCommerce PayDollar Payment Gateway
 Plugin URI: http://www.paydollar.com
 Description: PayDollar Payment gateway for woocommerce
 Version: 1.2
 Author: APPH
 Author URI: http://www.asiapay.com.ph
 */
add_action('plugins_loaded', 'woocommerce_paydollar_init', 0);
function woocommerce_paydollar_init(){    
    if(!class_exists('WC_Payment_Gateway')) return;
    
    class WC_PayDollar extends WC_Payment_Gateway{

        // Declare all properties used in the class
        public $id;
        public $method_title;
        public $has_fields;
        public $title;
        public $description;
        public $payment_url;
        public $merchant_id;
        public $pay_method;
        public $pay_type;
        public $curr_code;
        public $language;
        public $secure_hash_secret;
        public $prefix;
        public $msg;
        public $form_fields;

        public function __construct(){
            
            $this->id = 'paydollar';
            $this->method_title = 'PayDollar';
            $this->has_fields = false;

            $this->init_form_fields();
            $this->init_settings();

            $this->title =                    $this->settings['title'];
            $this->description =              $this->settings['description'];
            $this->payment_url =              $this->settings['payment_url'];
            $this->merchant_id =              $this->settings['merchant_id'];
            $this->pay_method =               $this->settings['pay_method'];
            $this->pay_type =                 $this->settings['pay_type'];
            $this->curr_code =                $this->getCurrCode(get_woocommerce_currency());
            $this->language =                 $this->settings['language'];
            $this->secure_hash_secret =       $this->settings['secure_hash_secret'];
            $this->prefix =                   $this->settings['prefix'];

            $this->msg['message'] = "";
            $this->msg['class'] = "";

            if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) ) {
                add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            } else {
                add_action( 'woocommerce_update_options_payment_gateways', array( $this, 'process_admin_options' ) );
            }
            add_action('woocommerce_receipt_paydollar', array($this, 'receipt_page'));
            
            /* for callback/datafeed */            
            add_action( 'woocommerce_api_wc_paydollar', array( $this, 'gateway_response' ) );
        }
        
        function generatePaymentSecureHash($merchantId, $merchantReferenceNumber, $currencyCode, $amount, $paymentType, $secureHashSecret) {
            $buffer = $merchantId . '|' . $merchantReferenceNumber . '|' . $currencyCode . '|' . $amount . '|' . $paymentType . '|' . $secureHashSecret;
            return sha1($buffer);
        }
        
        function verifyPaymentDatafeed($src, $prc, $successCode, $merchantReferenceNumber, $paydollarReferenceNumber, $currencyCode, $amount, $payerAuthenticationStatus, $secureHashSecret, $secureHash) {
            $buffer = $src . '|' . $prc . '|' . $successCode . '|' . $merchantReferenceNumber . '|' . $paydollarReferenceNumber . '|' . $currencyCode . '|' . $amount . '|' . $payerAuthenticationStatus . '|' . $secureHashSecret;
            $verifyData = sha1($buffer);
            return $secureHash == $verifyData;
        }
        
        function getCurrCode($woocommerce_currency){
            
            $cur = '';
            
            switch($woocommerce_currency){
                case 'HKD':
                    $cur = '344';
                    break;                        
                case 'USD':
                    $cur = '840';
                    break;    
                case 'SGD':
                    $cur = '702';
                    break;
                case 'CNY':
                    $cur = '156';
                    break;
                case 'JPY':
                    $cur = '392';
                    break;    
                case 'TWD':
                    $cur = '901';
                    break;
                case 'AUD':
                    $cur = '036';
                    break;
                case 'EUR':
                    $cur = '978';
                    break;
                case 'GBP':
                    $cur = '826';
                    break;
                case 'CAD':
                    $cur = '124';
                    break;
                case 'MOP':
                    $cur = '446';
                    break;
                case 'PHP':
                    $cur = '608';
                    break;
                case 'THB':
                    $cur = '764';
                    break;        
                case 'MYR':
                    $cur = '458';
                    break;
                case 'IDR':
                    $cur = '360';
                    break;
                case 'KRW':
                    $cur = '410';
                    break;
                case 'SAR':
                    $cur = '682';
                    break;
                case 'NZD':
                    $cur = '554';
                    break;
                case 'AED':
                    $cur = '784';
                    break;
                case 'BND':
                    $cur = '096';
                    break;
                case 'VND':
                    $cur = '704';
                    break;
                case 'INR':
                    $cur = '356';
                    break;
                default:
                    $cur = '344';
            }

            return $cur;
        }
        
        function init_form_fields(){

            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('Enable/Disable'),
                    'type' => 'checkbox',
                    'label' => __('Enable PayDollar Payment Module.'),
                    'default' => 'no'),
                'title' => array(
                    'title' => __('Title:'),
                    'type'=> 'text',
                    'description' => __('This controls the title which the user sees during checkout.'),
                    'default' => __('PayDollar/PesoPay/SiamPay')),
                'description' => array(
                    'title' => __('Description:'),
                    'type' => 'textarea',
                    'description' => __('This controls the description which the user sees during checkout.'),
                    'default' => __('Pay securely through PayDollar/PesoPay/SiamPay Secure Servers.')),
                'payment_url' => array(
                    'title' => __('Payment URL'),
                    'type' => 'text',
                    'description' => __('
                        This is the payment URL of PayDollar/PesoPay/SiamPay (via client post through browser).')),
                'merchant_id' => array(
                    'title' => __('Merchant ID'),
                    'type' => 'text',
                    'description' => __('This is your PayDollar/PesoPay/SiamPay merchant account ID.')),
                'pay_method' => array(
                    'title' => __('Payment Method'),
                    'type' => 'text',
                    'description' => __('"ALL" for all supported payment methods of the merchant account, "CC" for credit cards only. Etc..'),
                    'default' => __('ALL')),
                'pay_type' => array(
                    'title' => __('Payment Type'),
                    'type' => 'text',
                    'description' => __('"N" for Sale (Normal), "H" for Authorize (Hold).'),
                    'default' => __('N')),
                'language' => array(
                    'title' => __('Language'),
                    'type' => 'text',
                    'description' => __('"E" for English, "C" for Traditional Chinese, "T" for Thai, Etc..'),
                    'default' => __('E')),
                'secure_hash_secret' => array(
                    'title' => __('Secure Hash Secret'),
                    'type' => 'text',
                    'description' => __('Optional. The secret key from PayDollar/PesoPay/SiamPay for the "Secure Hash" function.'),
                    'default' => __('')),
                'prefix' => array(
                    'title' => __('Prefix'),
                    'type' => 'text',
                    'description' => __('Optional. Prefix for Order Reference No. (Warning: Do not use a dash "-" because the system uses it as a separator between the prefix and the order reference no.)'),
                    'default' => __(''))
            );
        }

        public function admin_options(){
            echo '<h3>'.__('PayDollar Payment Gateway').'</h3>';
            echo '<p>'.__('
                PayDollar/PesoPay/SiamPay PayGate is a powerful secure online payment services platform. It is used by many renowned companies and organizations.
                <br/><br/>
                <strong>PayDollar Payment URL:</strong> <br/>
                - Live: https://www.paydollar.com/b2c2/eng/payment/payForm.jsp <br/>
                - Test: https://test.paydollar.com/b2cDemo/eng/payment/payForm.jsp <br/>
                <strong>PesoPay Payment URL:</strong> <br/>
                - Live: https://www.pesopay.com/b2c2/eng/payment/payForm.jsp <br/>
                - Test: https://test.pesopay.com/b2cDemo/eng/payment/payForm.jsp <br/>
                <strong>SiamPay Payment URL:</strong> <br/>
                - Live: https://www.siampay.com/b2c2/eng/payment/payForm.jsp <br/>
                - Test: https://test.siampay.com/b2cDemo/eng/payment/payForm.jsp <br/>
                <br/>
                <strong>Test Credit Cards:</strong> <br/>
                - VISA: 4918914107195005 <br/>
                - Master: 5422882800700007 <br/>
                - Expiry Date: July 2030 <br/>
                - Security Code: 123 <br/>
                - Card Holder Name: John Doe <br/>
                <br/>
            ').'</p>';
            echo '<hr/>';
            echo '<table class="form-table">';
            // Generate the HTML For the settings form.
            $this->generate_settings_html();
            echo '</table>';

        }

        function payment_fields(){
            if($this->description) echo wpautop(wptexturize($this->description));
        }

        /**
         * Receipt Page
         **/
        function receipt_page($order){
            echo '<p>'.__('Thank you for your order. We are now redirecting you to the Payment Gateway to proceed with the payment.').'</p>';
            echo $this->generate_paydollar_form($order);
        }
        
        /**
         * Generate PayDollar button link
         **/
        public function generate_paydollar_form($order_id){

            global $woocommerce;

            $order = new WC_Order($order_id);
            
            
            if($this->prefix == ''){
                $orderRef = $order_id;
            }else{
                $orderRef = $this->prefix . '-' . $order_id;
            }
                        
            $success_url = esc_url( add_query_arg( 'utm_nooverride', '1', $this->get_return_url( $order ) ) );
            $fail_url = esc_url( $order->get_cancel_order_url() );
            $cancel_url = esc_url( $order->get_cancel_order_url() );
            
            
            $secureHash = '';
            if($this->secure_hash_secret != ''){
                $secureHash = $this->generatePaymentSecureHash($this->merchant_id, $orderRef, $this->curr_code, $order->order_total, $this->pay_type, $this->secure_hash_secret);
            }
                    
            $remarks = '';
            
            $paydollar_args = array(
                'orderRef' =>       $orderRef,
                'amount' =>         $order->order_total,            
                'merchantId' =>     $this->merchant_id,                 
                'payMethod' =>      $this->pay_method, 
                'payType'    =>     $this->pay_type,
                'currCode' =>       $this->curr_code,
                'lang' =>           $this->language,            
                'successUrl' =>     $success_url,
                'failUrl' =>        $fail_url,
                'cancelUrl' =>      $cancel_url,                        
                'secureHash' =>     $secureHash,
                'remark' =>         $remarks
              );

            $paydollar_args_array = array();
            foreach($paydollar_args as $key => $value){
                $paydollar_args_array[] = "<input type='hidden' name='$key' value='$value'/>";
            }
            
              return '<form action="' . $this->payment_url . '" method="post" id="paydollar_payment_form">
                ' . implode('', $paydollar_args_array) . '
                    </form>
                    <script type="text/javascript">
                        jQuery(function(){                        
                            setTimeout("paydollar_payment_form();", 5000);
                        });
                        function paydollar_payment_form(){
                            jQuery("#paydollar_payment_form").submit();
                        }
                    </script>
            ';

        }
        
        /**
         * Process the payment and return the result
         **/
        function process_payment($order_id){
            $order = new WC_Order($order_id);
            
            return array(
                'result'    => 'success',
                'redirect'  => $order->get_checkout_payment_url( true )
            );
            
        }

        /**
         * Check for valid paydollar server datafeed
         **/
        function gateway_response(){
            
            global $woocommerce;
            
            $src = $_POST['src'];
            $prc = $_POST['prc'];
            $ord = $_POST['Ord'];
            $holder = $_POST['Holder'];
            $successCode = $_POST['successcode'];
            $ref = $_POST['Ref'];
            $payRef = $_POST['PayRef'];
            $amt = $_POST['Amt'];
            $cur = $_POST['Cur'];
            $remark = $_POST['remark'];
            $authId = $_POST['AuthId'];
            $eci = $_POST['eci'];
            $payerAuth = $_POST['payerAuth'];
            $sourceIp = $_POST['sourceIp'];
            $ipCountry = $_POST['ipCountry'];
            $secureHash = $_POST['secureHash'] ?? "";
            
            echo "OK!";
            
            if( isset($_POST['Ref']) && isset($_POST['successcode']) && isset($_POST['src']) && isset($_POST['src']) ){
                
                $order_id = $ref;

                //prefix handler
                $hasPrefix = preg_match("/-/", $order_id);
                if($hasPrefix == 1){
                    $exploded_order_id = explode("-", $order_id);
                    $order_id = $exploded_order_id[1];
                }
                
                if($order_id != ''){                    
                    $order = new WC_Order($order_id);                    
                    if($order->status != 'completed'){
                        
                        $secureHashArr = explode ( ',', $secureHash );
                        foreach ($secureHashArr as $key => $value) {
                            $checkSecureHash = $this->verifyPaymentDatafeed($src, $prc, $successCode, $ref, $payRef, $cur, $amt, $payerAuth, $this->secure_hash_secret, $value);
                            if($checkSecureHash){
                                break;
                            }
                        }
                        
                        if( $this->secure_hash_secret == '' || $checkSecureHash ){

                            if($successCode == "0"){                                
                                if($order->status == 'processing'){
                                    //do nothing
                                }else{                                    
                                    $this->msg['message'] = 'Thank you for shopping with us. Your account has been charged and your transaction is successful. We will be shipping your order to you soon. Payment reference no: ' . $payRef;
                                    $this->msg['class'] = 'woocommerce_message';
                                    
                                    $order->payment_complete();
                                    $order->add_order_note('Your payment was successful! Payment reference no: '.$payRef);
                                    $woocommerce->cart->empty_cart();    
                                    echo ' - Payment Success!';
                                }
                            }else{    
                                if($order->status == 'processing'){
                                    //do nothing
                                }else{                            
                                    $this->msg['message'] = 'Thank you for shopping with us. However, the transaction has been declined. Payment reference no: '. $payRef;
                                    $this->msg['class'] = 'woocommerce_error';
                                    
                                    $order->update_status('failed');
                                    $order->add_order_note('Sorry! your payment was unsuccessful! Payment reference no: '.$payRef);                                
                                    echo ' - Payment Failed!';
                                }
                            }
                        }else{
                            $this->msg['message'] = 'Security Error. Illegal access detected. Payment reference no: '.$payRef;
                            $this->msg['class'] = 'error';
                            
                            $order->update_status('failed');
                            $order->add_order_note('Secure Hash checking failed! Payment reference no: '.$payRef);
                            echo ' - Secure Hash Failed!';
                        }
                        
                        add_action('the_content', array($this, 'showMessage'));
                    
                    }

                }

            }
            
            exit();
            
        }

        function showMessage($content){
            return '<div class="box '.$this->msg['class'].'-box">'.$this->msg['message'].'</div>'.$content;
        }
        
        // get all pages
        function get_pages($title = false, $indent = true) {
            $wp_pages = get_pages('sort_column=menu_order');
            $page_list = array();
            if ($title) $page_list[] = $title;
            foreach ($wp_pages as $page) {
                $prefix = '';
                if ($indent) {
                    $has_parent = $page->post_parent;
                    while($has_parent) {
                        $prefix .=  ' - ';
                        $next_page = get_page($has_parent);
                        $has_parent = $next_page->post_parent;
                    }
                }
                // add to page list array array
                $page_list[$page->ID] = $prefix . $page->post_title;
            }
            return $page_list;
        }
    }

    
    /**
     * Add the Gateway to WooCommerce
     **/
    function woocommerce_add_paydollar_gateway($methods) {
        $methods[] = 'WC_PayDollar';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'woocommerce_add_paydollar_gateway' );
    
}