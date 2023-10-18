<?php
/**
 * PPL Astra Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package PPL Astra
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define( 'CHILD_THEME_PPL_ASTRA_VERSION', '1.0.0' );

/**
 * Enqueue styles
 */
function child_enqueue_styles() {

    wp_enqueue_style( 'ppl-astra-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_PPL_ASTRA_VERSION, 'all' );

}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );

function your_prefix_load_google_fonts() {
    static $google_fonts = array();
    if ( empty( $google_fonts ) ) {
        $google_fonts_file = apply_filters( 'astra_google_fonts_json_file', ASTRA_THEME_DIR . 'assets/fonts/google-fonts.json' );
        
        if ( ! file_exists( ASTRA_THEME_DIR . 'assets/fonts/google-fonts.json' ) ) {
            return array();
        }
        // Use file_get_contents instead of WP_Filesystem->get_contents()
        $file_contants     = file_get_contents( $google_fonts_file );
        $google_fonts_json = json_decode( $file_contants, 1 );
        foreach ( $google_fonts_json as $key => $font ) {
            $name = key( $font );
            foreach ( $font[ $name ] as $font_key => $variant ) {
                if ( stristr( $variant, 'italic' ) ) {
                    unset( $font[ $name ][ $font_key ] );
                }
                if ( 'regular' == $variant ) {
                    $font[ $name ][ $font_key ] = '400';
                }
                $google_fonts[ $name ] = array_values( $font[ $name ] );
            }
        }
    }
    return $google_fonts;
}
add_filter( 'astra_google_fonts', 'your_prefix_load_google_fonts' );

add_action('wp_ajax_donation', 'donation_callback_wp');
add_action( 'wp_ajax_nopriv_donation', 'donation_callback_wp' );
function donation_callback_wp() {
    $paymentMessage="";
    global $wpdb; // this is how you get access to the database
    $params = array();
    parse_str($_POST['myData'], $_POST);
    
    if(!empty($_POST['stripeToken'])){
        // get token and user details
        $_POST['stripeToken'];
        $stripeToken  = $_POST['stripeToken'];
        $customerName = $_POST['custName'];
        $customerEmail = $_POST['custEmail'];
        
        $customerAddress = $_POST['customerAddress'];
        $customerCity = $_POST['customerCity'];
        $customerZipcode = $_POST['customerZipcode'];
        $customerState = $_POST['customerState'];
        $customerCountry = $_POST['customerCountry'];
        
        $cardNumber = $_POST['cardNumber'];
        $cardCVC = $_POST['cardCVC'];
        $cardExpMonth = $_POST['cardExpMonth'];
        $cardExpYear = $_POST['cardExpYear']; 
        
        // item details for which payment made
        $itemName = $_POST['item_details'];
        $itemNumber = $_POST['item_number'];
        $itemPrice = $_POST['price'];
        $totalAmount = $_POST['total_amount'];
        $currency = $_POST['currency_code'];
        $select_payment_type = $_POST['select_payment_type'];;
        
        //include Stripe PHP library
        require_once('stripe-php/init.php'); 
        //set stripe secret key and publishable key  
        $stripe = array(
          "secret_key"      => "sk_test_xxxx",
          "publishable_key" => "pk_test_yyyyy"
        );
        \Stripe\Stripe::setApiKey($stripe['secret_key']);    
        
        //add customer to stripe
        try {
            $customer = \Stripe\Customer::create(array(
            'name' => $customerName,
            'description' => 'Donation',
                'email' => $customerEmail,
                'source'  => $stripeToken,
            "address" => ["city" => $customerCity, "country" => $customerCountry, "line1" => $customerAddress, "line2" => "", "postal_code" => $customerZipcode, "state" => $customerState]
            ));  
        }catch (Exception $e) {
              $body = $e->getJsonBody();
              $err  = $body['error'];
              echo $err_code  =  $err['code'];
              $err_msg  = $err['message'];
              exit();
        } 
        
        if($select_payment_type == "monthly"){
            try { 
                // Create price with subscription info and interval 
                $price = \Stripe\Price::create([ 
                    'unit_amount' => $totalAmount, 
                    'currency' => 'USD', 
                    'recurring' => ['interval' => 'month'],
                    'product_data' => ['name' => 'Donation - wxyz'], 
                ]); 
            } catch (Exception $e) {  
                $api_error = $e->getMessage(); 
                $body = $e->getJsonBody();
                $err  = $body['error'];
                echo $err_code  =  $err['code'];
                $err_msg  = $err['message'];
                exit();
            } 
             
            // details for which monthly payment performed
            try { 
                $subscription = \Stripe\Subscription::create([ 
                    'customer' => $customer->id, 
                    'items' => [[ 
                        'price' => $price->id, 
                    ]], 
                    'payment_settings' => ['save_default_payment_method' => 'on_subscription'], 
                ]); 
            }catch(Exception $e) { 
                  $api_error = $e->getMessage(); 
                  $body = $e->getJsonBody();
                  $err  = $body['error'];
                  $err_code  =  $err['code'];
                  $err_msg  = $err['message'];
                  echo "<script type=\"text/javascript\">alert('Error creating subscription:  $err_msg ($err_code)'); window.location.href='/donation-new/';</script>";
                  exit();
            } 
            if(empty($api_error) && $subscription){ 
                $_POST['subscriptionId'] = $subscription->id;
                send_stripe_email($customerName, $customerEmail, $totalAmount);
                //echo json_encode($output); 
            }else{ 
                //echo json_encode(['error' => $api_error]); 
            } 
        }else{
            // details for which onetime payment performed
            try {
            $payDetails = \Stripe\Charge::create(array(
                'customer' => $customer->id,
                'amount'   => $totalAmount,
                'currency' => $currency,
                'description' => $itemName
            ));   
            $_POST['chargeId'] = $payDetails->id;
            if($payDetails->id!=""){
            send_stripe_email($customerName, $customerEmail, $totalAmount);}
            }catch (Exception $e) {
                  $body = $e->getJsonBody();
                  $err  = $body['error'];
                  $err_code  =  $err['code'];
                  $err_msg  = $err['message'];
                  echo "<script type=\"text/javascript\">alert('Card Error6:  $err_msg ($err_code)'); window.location.href='/donation-new/';</script>";
                  exit();
            } 
            // get payment details
            $paymenyResponse = $payDetails->jsonSerialize();
        }
        wp_create_donation_user($_POST);
        $paymentMessage = "The payment was successful.";
    } else{
        $paymentMessage = "failed";
    }
    echo $_SESSION["message"] = $paymentMessage;
    exit(); // this is required to return a proper result
    
}

// send email after stripe payment
function send_stripe_email($customerName, $customerEmail, $totalAmount){
    
    $totalAmount = $totalAmount/100;
    $to = $customerEmail;
    $subject = 'Your donations received';
    $message = '<html lang="en"><head>
                          <title>Donation</title>
                          <meta charset="utf-8">
                          <meta name="viewport" content="width=device-width, initial-scale=1">
                          </head>
                          <body style="padding: 0; margin: 0; background: #e5e5e5; font-family: Arial"><center>
                              <table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#e5e5e5" style="padding:20px;">
                                <tbody><tr>
                                  <td align="center" valign="top">
                                    <table width="650px" cellpadding="0" cellspacing="0" border="0" bgcolor="#ffffff" style="padding:40px 60px;">
                                      <tbody><tr>
                                        <td colspan="12">
                                          <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tbody><tr>
                                              <td colspan="12">
                                                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-bottom:1px solid #9f9f9f">
                                                  <tbody><tr>
                                                    <td align="center">
                                                    <a href="https://www.wxyz.org/"><img src="https://www.wxyz.org/wp-content/uploads/2023/08/adam-alani-logo-final-1024x369.png" width="128" style="margin-bottom:30px;" alt="IRO"></a>
                                                    </td>
                                                  </tr>
                                                </tbody></table>
                                              </td>
                                            </tr>
                                            <tr>
                                              <td colspan="12">
                                                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="padding-top:34px;">
                                                  <tbody><tr>
                                                    <td colspan="12">
                                                      <h3 style="font-weight:normal;margin: 0 0 30px;font-size: 22px;color: #000;">'.$customerName.'! Your donations matters</h3>
                                                      <p style="font-size: 15px;line-height: 23px;color: #000;margin-bottom: 27px;">We have received your donation of $'.$totalAmount.'</p>
                                                      <p style="font-size: 15px;line-height: 23px;color: #000;margin-bottom: 26px;">If you need futher assistance, please contact us at <a href="mailto:info@wxyz.org" style="color: #018fd7;">info@wxyz.org</a>.</p>
                                                    </td>
                                                  </tr>
                                                </tbody></table>
                                              </td>
                                            </tr>
                                            <tr>
                                              <td colspan="12">
                                                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="padding-bottom: 15px;padding-top: 0;">
                                                  <tbody><tr>
                                                    <td colspan="12">
                                                      <p style="margin:0;font-size: 12px;line-height: 23px;color: #000;">© 2023 International Rights Organization Copyright | All Right Reserved<br/><a style="margin:0;font-size: 12px;line-height: 23px;color: #000;" href="https://www.wxyz.org/" style="color: #018fd7;">www.wxyz.org</a></p>
                                                    </td>
                                                  </tr>
                                                </tbody></table>
                                              </td>
                                            </tr>
                                          </tbody></table>
                                        </td>
                                      </tr>
                                    </tbody></table>
                                  </td>
                                </tr>
                              </tbody></table>
                          </center></body></html>';
    
    
    $headers = array('Content-Type: text/html; charset=UTF-8');
    $headers[] = "From: IRO <info@wxyz.org>";
    wp_mail( $to, $subject, $message, $headers );
}

// Function added to create/update user on every donation
function wp_create_donation_user($post) {
    $_POST = $post;
    $password = rand(1111111111,9999999999);
    
    $customerName = $_POST['custName'];
    $username = $customerEmail = $_POST['custEmail'];
    
    $customerAddress = $_POST['customerAddress'];
    $customerCity = $_POST['customerCity'];
    $customerZipcode = $_POST['customerZipcode'];
    $customerState = $_POST['customerState'];
    $customerCountry = $_POST['customerCountry'];
    $name_arr = explode(" ",$customerName);
    $firstName = $name_arr[0];
    $lastName = $name_arr[1];
    $user = get_user_by( 'email', $customerEmail );
    if( ! $user ) {
        // Create the new user
        $userId = wp_create_user( $username, $password, $customerEmail );
        if( is_wp_error( $userId ) ) {
            // examine the error message
            echo( "Error: " . $userId->get_error_message() );
            exit;
        }
        
        wp_update_user([
            'ID' => $userId, // this is the ID of the user you want to update.
            'first_name' => $firstName,
            'last_name' => $lastName,
        ]);
        update_user_meta( $userId, 'customerName', "$customerName" );
        update_user_meta( $userId, 'customerAddress', "$customerAddress" );
        update_user_meta( $userId, 'customerCity', "$customerCity" );
        update_user_meta( $userId, 'customerZipcode', "$customerZipcode" );
        update_user_meta( $userId, 'customerState', "$customerState" );
        update_user_meta( $userId, 'customerCountry', "$customerCountry" );
    }else{
        $userId = $user->ID;
        wp_update_user([
            'ID' => $userId, // this is the ID of the user you want to update.
            'first_name' => $firstName,
            'last_name' => $lastName,
        ]);
        update_user_meta( $userId, 'customerName', "$customerName" );
        update_user_meta( $userId, 'customerAddress', "$customerAddress" );
        update_user_meta( $userId, 'customerCity', "$customerCity" );
        update_user_meta( $userId, 'customerZipcode', "$customerZipcode" );
        update_user_meta( $userId, 'customerState', "$customerState" );
        update_user_meta( $userId, 'customerCountry', "$customerCountry" );
    }
}

// ajax action for paypal user creation
add_action('wp_ajax_donation_paypal', 'donation_paypal_callback_wp');
add_action( 'wp_ajax_nopriv_donation_paypal', 'donation_paypal_callback_wp' );
function donation_paypal_callback_wp() {
    global $wpdb; // this is how you get access to the database
    $params = array();
    parse_str($_POST['myData'], $_POST);
    wp_create_donation_user($_POST);
    exit();
}

//function added to display custom information about user in admin
function wk_custom_user_profile_fields( $user ) {
    echo '<h3 class="heading">Donation Customer Details</h3>';
    ?>
    <table class="form-table">
        <tr>
            <th><label for="contact">Name</label></th>
            <td>
                <input type="text" name="customerName" id="customerName" value="<?php echo esc_attr( get_the_author_meta( 'customerName', $user->ID ) ); ?>" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="contact">Address</label></th>
            <td>
                <input type="text" name="customerAddress" id="customerAddress" value="<?php echo esc_attr( get_the_author_meta( 'customerAddress', $user->ID ) ); ?>" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="contact">City</label></th>
            <td>
                <input type="text" name="customerCity" id="customerCity" value="<?php echo esc_attr( get_the_author_meta( 'customerCity', $user->ID ) ); ?>" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="contact">Zipcode</label></th>
            <td>
                <input type="text" name="customerZipcode" id="customerZipcode" value="<?php echo esc_attr( get_the_author_meta( 'customerZipcode', $user->ID ) ); ?>" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="contact">State</label></th>
            <td>
                <input type="text" name="customerState" id="customerState" value="<?php echo esc_attr( get_the_author_meta( 'customerState', $user->ID ) ); ?>" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="contact">Country</label></th>
            <td>
                <input type="text" name="customerCountry" id="customerCountry" value="<?php echo esc_attr( get_the_author_meta( 'customerCountry', $user->ID ) ); ?>" class="regular-text" />
            </td>
        </tr>
    </table>
    <?php
}
add_action( 'show_user_profile', 'wk_custom_user_profile_fields' );
add_action( 'edit_user_profile', 'wk_custom_user_profile_fields' );
