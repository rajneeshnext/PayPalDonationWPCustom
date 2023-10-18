<?php 
/* Template Name: Donation Template  */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
get_header(); ?>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-creditcardvalidator/1.0.0/jquery.creditCardValidator.js"></script>
<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<script>

    //Stripe.setPublishableKey('pk_test_xxxxxxa');
    Stripe.setPublishableKey('pk_live_xxxxh');
    function stripePay(event) {
        $('.loader_gif').show();
        $('#makePayment').attr('disabled', 'disabled');
        event.preventDefault(); 
        if(validateForm() == true) {
         $('#payNow').attr('disabled', 'disabled');
         $('#payNow').val('Payment Processing....');
         Stripe.createToken({
          number:$('#cardNumber').val(),
          cvc:$('#cardCVC').val(),
          exp_month : $('#cardExpMonth').val(),
          exp_year : $('#cardExpYear').val()
         }, stripeResponseHandler);
         return false;
        }else{
            //alert("Form incomplete");
        }
    }

function stripeResponseHandler(status, response) {
     if(response.error) {
          $('.loader_gif').hide();
          $('#makePayment').removeAttr('disabled');
          alert(response.error.message);
     } else {
          var stripeToken = response['id'];
          $('#paymentForm').append("<input type='hidden' name='stripeToken' value='" + stripeToken + "' />");
          var formData = $('#paymentForm').serialize();
          $.ajax({
                type: "POST",
                cache: false,
                url: "<?php echo esc_url( home_url() ); ?>/wp-admin/admin-ajax.php",
                data: {myData: formData,action: 'donation'},
                success: function (msg) {
                    window.location.replace("/thank-you");//alert("Success!");
                }
           });
           //$('#paymentForm').submit();
     }
}

function validateForm() {
 var validCard = 0;
 var valid = false;
 var cardCVC = $('#cardCVC').val();
 var cardExpMonth = $('#cardExpMonth').val();
 var cardExpYear = $('#cardExpYear').val();
 var cardNumber = $('#cardNumber').val();
 var emailAddress = $('#emailAddress').val();
 var customerName = $('#customerName').val();
 var customerAddress = $('#customerAddress').val();
 var customerCity = $('#customerCity').val();
 var customerZipcode = $('#customerZipcode').val();
 var customerCountry = $('#customerCountry').val();
 var validateName = /^[a-z ,.'-]+$/i;
 var validateEmail = /^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/;
 var validateMonth = /^01|02|03|04|05|06|07|08|09|10|11|12$/;
 var validateYear = /^2017|2018|2019|2020|2021|2022|2023|2024|2025|2026|2027|2028|2029|2030|2031$/;
 var cvv_expression = /^[0-9]{3,3}$/;

 $('#cardNumber').validateCreditCard(function(result){
  if(result.valid) {
   $('#cardNumber').removeClass('require');
   $('#errorCardNumber').text('');
   validCard = 1;
  } else {
   $('#cardNumber').addClass('require');
   $('#errorCardNumber').text('Invalid Card Number');animate_top_card();
   $("#cardNumber").css("border","1px solid red");
   validCard = 0;
   return false;
  }
 });

 if(validCard == 1) {
  if(!validateMonth.test(cardExpMonth)){
   $('#cardExpMonth').addClass('require');
   $("#cardExpMonth").css("border","1px solid red");
   $('#errorCardExpMonth').text('Invalid Data');animate_top_card();
   valid = false;
   return valid;
  } else { 
   $('#cardExpMonth').removeClass('require');
   $('#errorCardExpMonth').text('');
   valid = true;
  }

  if(!validateYear.test(cardExpYear)){
   $("#cardExpYear").css("border","1px solid red");
   $('#cardExpYear').addClass('require');
   $('#errorCardExpYear').error('Invalid Data');animate_top_card();
   valid = false;return valid;
  } else {
   $('#cardExpYear').removeClass('require');
   $('#errorCardExpYear').error('');
   valid = true;
  }

  if(!cvv_expression.test(cardCVC)) {
   $("#cardCVC").css("border","1px solid red");
   $('#cardCVC').addClass('require');animate_top_card();
   $('#errorCardCvc').text('Invalid Data');
   valid = false;return valid;
  } else {
   $('#cardCVC').removeClass('require');
   $('#errorCardCvc').text('');
   valid = true;
  }
  
  if(!validateName.test(customerName)) {
   $("#customerName").css("border","1px solid red");animate_top();
   valid = false;return valid;
  } else {
   $("#customerName").css("border","1px solid #D4D4D4");
   valid = true;
  }

  if(!validateEmail.test(emailAddress)) {
   $("#emailAddress").css("border","1px solid red");animate_top();
   valid = false;return valid;
  } else {
   $("#emailAddress").css("border","1px solid #D4D4D4");
   valid = true;
  }

  if(customerAddress == '') {
   $("#customerAddress").css("border","1px solid red");animate_top();
   valid = false;return valid;
  } else {
   $("#customerAddress").css("border","1px solid #D4D4D4");
   valid = true;
  }

  if(customerCity == ''){
   $("#customerCity").css("border","1px solid red");animate_top();
   valid = false;return valid;
  } else {
   $("#customerCity").css("border","1px solid #D4D4D4");
   valid = true;
  }

  if(customerZipcode == ''){
   $("#customerZipcode").css("border","1px solid red");animate_top();
   valid = false;return valid;
  } else {
   $("#customerZipcode").css("border","1px solid #D4D4D4");
   valid = true;
  }

  if(customerCountry == '') {
   $("#customerCountry").css("border","1px solid red");animate_top();
   valid = false;return valid;
  } else {
   $("#customerCountry").css("border","1px solid #D4D4D4");
   valid = true;
  }  
 }
 return valid;
}

function validateNumber(event) {
 var charCode = (event.which) ? event.which : event.keyCode;
 if (charCode != 32 && charCode > 31 && (charCode < 48 || charCode > 57)){
  return false;
 }
 return true;
}

function animate_top_card(){
    setTimeout(function(){ 
       $('.loader_gif').hide();
       $('#makePayment').removeAttr('disabled');
    }, 3000);
	$('html, body').animate({
		scrollTop: $("#donate_by_stripe").offset().top
	}, 1000);
}
	
function animate_top(){
    setTimeout(function(){ 
       $('.loader_gif').hide();
       $('#makePayment').removeAttr('disabled');
    }, 3000);
	$('html, body').animate({
		scrollTop: $("#paymentForm").offset().top
	}, 1000);
}

$(document).ready(function() {
	
	// validate paypal 	
	$(".makePayment_paypal").click(function(e){
	     $('.loader_gif').show();
	     $('#makePayment').attr('disabled', 'disabled');
		 var valid = false;
		 var emailAddress = $('#emailAddress').val();
		 var customerName = $('#customerName').val();
		 var customerAddress = $('#customerAddress').val();
		 var customerCity = $('#customerCity').val();
		 var customerZipcode = $('#customerZipcode').val();
		 var customerCountry = $('#customerCountry').val();
		 var validateName = /^[a-z ,.'-]+$/i;
		 var validateEmail = /^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/;
		 var validateMonth = /^01|02|03|04|05|06|07|08|09|10|11|12$/;
		 var validateYear = /^2017|2018|2019|2020|2021|2022|2023|2024|2025|2026|2027|2028|2029|2030|2031$/;
		if(!validateName.test(customerName)) {
		   $("#customerName").css("border","1px solid red");
			animate_top();
		   valid = false;return valid;
		  } else {
		   $("#customerName").css("border","1px solid #D4D4D4");
		   valid = true;
		  }

		  if(!validateEmail.test(emailAddress)) {
		   $("#emailAddress").css("border","1px solid red");
		   animate_top();
		   valid = false;return valid;
		  } else {
		   $("#emailAddress").css("border","1px solid #D4D4D4");
		   valid = true;
		  }

		  if(customerAddress == '') {
		   $("#customerAddress").css("border","1px solid red");
			  animate_top();
		   valid = false;return valid;
		  } else {
		   $("#customerAddress").css("border","1px solid #D4D4D4");
		   valid = true;
		  }

		  if(customerCity == ''){
		   $("#customerCity").css("border","1px solid red");animate_top();
		   valid = false;return valid;
		  } else {
		   $("#customerCity").css("border","1px solid #D4D4D4");
		   valid = true;
		  }

		  if(customerZipcode == ''){
		   $("#customerZipcode").css("border","1px solid red");animate_top();
		   valid = false;return valid;
		  } else {
		   $("#customerZipcode").css("border","1px solid #D4D4D4");
		   valid = true;
		  }

		  if(customerCountry == '') {
		   $("#customerCountry").css("border","1px solid red");animate_top();
		   valid = false;return valid;
		  } else {
		   $("#customerCountry").css("border","1px solid #D4D4D4");
		   valid = true;
		  }  
		var formData = $('#paymentForm').serialize();
		$.ajax({
			type: "POST",
			cache: false,
			url: "<?php echo esc_url( home_url() ); ?>/wp-admin/admin-ajax.php",
			data: {myData: formData,action: 'donation_paypal'},
			success: function (msg) {	
			     $('.loader_gif').hide();
				 $(this).submit();return;
			}
		});	
    });
		
    $(".select_amount").click(function(){
        $("#other_amount").val('');
        $(".total_amount_text").text("$"+$(this).attr("amount")/100);
        $("#total_amount").val($(this).attr("amount"));
		$("#dntplgn_once_amount").val($(this).attr("amount")/100);
        $(".select_amount").removeClass("active");
        $(this).addClass("active");
    });
    $("#other_amount").on("keyup change", function(e) {
        $(".select_amount").removeClass("active");
        amount = parseFloat($(this).val())*100;
        if(isNaN(amount)) {
            $("#total_amount").val('');
            $(".total_amount_text").text("$"+'0');
			$("#dntplgn_once_amount").val(0);
        }else{
            $("#total_amount").val(parseFloat($(this).val())*100);
            $(".total_amount_text").text("$"+parseFloat($(this).val()));
			$("#dntplgn_once_amount").val(parseFloat($(this).val()));
        }    
    });
	$(".select_payment_mode").click(function(){
		$(".select_payment_mode").removeClass("active");
		$(this).addClass("active");
		if($(this).hasClass("stripe")){		   
		   $("#donate_by_stripe").show();
			$("#makePayment").show();
           $("#donate_by_paypal").hide();
		}else{
			$("#donate_by_stripe").hide();
            $("#donate_by_paypal").show();
			$("#makePayment").hide();
		}
	});
    $(".select_amount_type").click(function(){
        if($(this).attr("type") == "onetime"){
            $("#onetime").show();
			$("#onetime_form_paypal").show();
			$("#monthly_form_paypal").hide();
            $("#monthly").hide();
            amount = parseFloat($(".select_amount.active").attr("amount"));
            if(isNaN(amount)) {
                amount = parseFloat($("#other_amount").val())*100;
                $(".total_amount_text").text("$"+amount/100);
                $("#total_amount").val(amount);
            }else{
                $(".total_amount_text").text("$"+amount/100);
                $("#total_amount").val(amount);
            }
        }else{
            $("#onetime").hide();
            $("#monthly").show();
			$("#onetime_form_paypal").hide();
			$("#monthly_form_paypal").show();
            amount = parseFloat($(".select_amount_monthly.active").attr("amount"));
            if(isNaN(amount)) {
                amount = parseFloat($("#other_amount_monthly").val())*100;
                $(".total_amount_text").text("$"+amount/100);
                $("#total_amount").val(amount);
				$("#amount_paypal").val(amount/100);
            }else{
                $(".total_amount_text").text("$"+amount/100);
                $("#total_amount").val(amount);
				$("#amount_paypal").val(amount/100);
            }
        }
        $("#select_payment_type").val($(this).attr("type"));
        $( ".select_amount_type" ).removeClass( "active" );
        $( this ).addClass( "active" );
    });
    $(".select_amount_monthly").click(function(){
        $("#other_amount_monthly").val('');
        $(".total_amount_text").text("$"+$(this).attr("amount")/100);
        $("#total_amount").val($(this).attr("amount"));
		$("#amount_paypal").val($(this).attr("amount")/100);
        $(".select_amount_monthly").removeClass("active");
        $(this).addClass("active");
    });
    $("#other_amount_monthly").on("keyup change", function(e) {
        $(".select_amount_monthly").removeClass("active");
        amount = parseFloat($(this).val())*100;
        if(isNaN(amount)) {
            $("#total_amount").val('');
            $(".total_amount_text").text("$"+'0');
        }else{
            $("#total_amount").val(parseFloat($(this).val())*100);
			$("#amount_paypal").val(amount/100);
            $(".total_amount_text").text("$"+parseFloat($(this).val()));
        }    
    });
});

</script>

<?php if ( astra_page_layout() == 'left-sidebar' ) : ?>

	<?php get_sidebar(); ?>

<?php endif ?>

	<div id="primary" <?php astra_primary_class(); ?>>

        <div class="col-xs-12 col-md-12">
        	<div class="panel panel-default">
        		<div class="panel-body">       		    
        		    
        		    <?php astra_primary_content_top(); ?>
            		<?php astra_content_page_loop(); ?>            
            		<?php astra_primary_content_bottom(); ?>       		    
        		    
        			<span class="paymentErrors alert-danger"></span>
        			<style>
        			    .loader_gif{
        			        width: 30px;
                            margin: 0 auto;
        			    }
            			.panel-body{
            			    box-shadow: 0 1px 6px rgb(0 0 0 / 16%), 0 3px 6px rgb(0 0 0 / 23%);
            			}
            			.ast-container{
        			        background: #ffffff url(http://www.irousa.org/wp-content/uploads/2023/08/D00j-2HWwAAZStM.jpg) no-repeat center center fixed;
                            background-size: cover;
                            background-repeat: no-repeat;
            			}
						@media screen and (min-width: 1000px) {
						  #primary{    
        			        width: 1000px;
							margin-top: 100px !important;
						  }
						}
						#primary{    
                            float: none;
                            margin: 0 auto !important;
                            margin-bottom: 50px !important;
        			    }
        			    .panel.panel-default{max-width: 560px;}
        			    #customerCountry{
        			        width: 100%;
        			    }
                        .select_amount_type.active label, .select_amount.active label, .select_amount_monthly.active label{
                            background: var( --e-global-color-primary ) !important;
                            color: #fff !important;
                        }
                        #paymentForm .select_amount_type label, #paymentForm .select_amount label, #paymentForm .other_amount_class, #paymentForm .select_amount_monthly label{
                            cursor: pointer;
                            display: block;
                            width: 100%;
                            padding: 10px;
                            background: 0 0;
                            border-radius: 2px;
                            text-align: center;
                            border: 1px solid #999;
                            background: 0 0;
                            color: #6b6b6b;
                            line-height: 26px;
                        }
						#other_amount, #other_amount_monthly{
						    width: 100%;
							padding: 13px;
							border-radius: 2px;
						}
                        input {
                          text-align: center;
                        }                        
                        ::-webkit-input-placeholder {
                          text-align: center;
                        }                        
                        :-moz-placeholder {
                          text-align: center;
                        }
                        .form-group label{
                            color: #000;
                            font-size: 15px;
                            font-weight: normal;
                        }
                        label span{color: red;}
						.heading .total_amount_text{
						    font-size: 25px;color: var( --e-global-color-primary );
						}
						.heading{
						    font-size: 15px;
						}
						#makePayment, .makePayment_paypal{
    					    padding: 11px !important;
    					    font-size: 30px !important; 
						}
						#donate_by_paypal{display: none;}
						#monthly_form_paypal, #onetime_form_paypal{text-align: center;}
						.select_payment_mode{
							text-align: center;
							width: 100%;
							border-bottom: 3px solid grey;
							padding: 20px;
							cursor: pointer;
					   }
						.select_payment_mode.active{
							border-bottom: 3px solid red;
							padding: 20px;							
							margin-bottom: 20px;
						}
						.select_payment_mode.stripe{border-right: 3px solid grey; }
						.select_payment_mode img
						{
							height: 38px;
						}
        			</style>
        			<form action="" method="POST" id="paymentForm">	
        			    <div class="row">
            				<div class="col-xs-6">
            					<div class="form-group select_amount_type active" type="onetime">
            						<label>One-time</label>
            					</div>	
            				</div>	
            				<div class="col-xs-6">
            					<div class="form-group select_amount_type" type="monthly">
            							<label>Monthly</label>
            					</div>	
            				</div>	
        				</div>
        				<br/><br/>
        				<div id="onetime">
            			    <div class="row">
                				<div class="col-xs-4">
                					<div class="form-group select_amount" amount="15000">
                						<label>$150</label>
                					</div>	
                				</div>	
                				<div class="col-xs-4">
                					<div class="form-group select_amount active" amount="30000">
                							<label>$300</label>
                					</div>	
                				</div>	
                				<div class="col-xs-4">
                					<div class="form-group select_amount" amount="100000">
                						<label>$1000</label>
                					</div>	
                				</div>	
            				</div>
            				<div class="row">
                				<div class="col-xs-4">
                					<div class="form-group select_amount" amount="250000">
                						<label>$2500</label>
                					</div>	
                				</div>	
                				<div class="col-xs-4">
                					<div class="form-group select_amount" amount="500000">
                							<label>$5000</label>
                					</div>	
                				</div>	
                				<div class="col-xs-4">
                					<div class="form-group">
                						<input placeholder="Other" style="width: 100%;" type="text" id="other_amount" name="other_amount" value="">
                					</div>	
                				</div>	
            				</div>
        				</div>
        				<div id="monthly" style="display: none;">
        				    <div class="row">
                				<div class="col-xs-4">
                					<div class="form-group select_amount_monthly" amount="1900">
                						<label>$19</label>
                					</div>	
                				</div>	
                				<div class="col-xs-4">
                					<div class="form-group select_amount_monthly" amount="2900">
                							<label>$29</label>
                					</div>	
                				</div>	
                				<div class="col-xs-4">
                					<div class="form-group select_amount_monthly active" amount="5000">
                						<label>$50</label>
                					</div>	
                				</div>	
            				</div>
            				<div class="row">
                				<div class="col-xs-4">
                					<div class="form-group select_amount_monthly" amount="10000">
                						<label>$100</label>
                					</div>	
                				</div>	
                				<div class="col-xs-4">
                					<div class="form-group select_amount_monthly" amount="12400">
                							<label>$124</label>
                					</div>	
                				</div>	
                				<div class="col-xs-4">
                					<div class="form-group">
                						<input placeholder="Other" style="width: 100%;" type="text" id="other_amount_monthly" name="other_amount_monthly" value="">
                					</div>	
                				</div>	
            				</div>
        				</div>
        				<h2 class="section-header-container">Billing Information</h2>
        				<div class="form-group">
        					<label for="name">Name:<span>*</span></label>
        					<input type="text" id="customerName" name="custName" class="form-control">
        				</div>
        				<div class="form-group">
        					<label for="email">Email:<span>*</span></label>
        					<input type="email" id="emailAddress"  name="custEmail" class="form-control">
        				</div>
        				<div class="form-group">
        					<label for="text">Address:<span>*</span></label>
        					<input type="text" id="customerAddress"  name="customerAddress" class="form-control">
        				</div>
        				<div class="row">
            				<div class="col-xs-6">
            					<div class="form-group">
            						<label for="city">City:<span>*</span></label>
        					        <input type="text" id="customerCity"  name="customerCity" class="form-control">
            					</div>	
            				</div>
            				<div class="col-xs-6">
            					<div class="form-group">
            						<label for="email">State:<span></span></label>
        					        <input type="text" id="customerState" name="customerState" class="form-control">
            					</div>	
            				</div>
            			</div>	
            			<div class="row">
            				<div class="col-xs-6">
            					<div class="form-group">
            						<label for="email">Zipcode:<span>*</span></label>
        					        <input type="text" id="customerZipcode"  name="customerZipcode" class="form-control">
            					</div>	
            				</div>
            				<div class="col-xs-6">
            					<div class="form-group">
            						<label for="email">Country:<span>*</span></label>
        					        <select name="customerCountry" id="customerCountry" size="1">
                                        <option></option>
                                        <option selected="selected" value="United States">United States</option>
                                        <option value="Afghanistan">Afghanistan</option>
                                        <option value="Aland Islands">Aland Islands</option>
                                        <option value="Albania">Albania</option>
                                        <option value="Algeria">Algeria</option>
                                        <option value="American Samoa">American Samoa</option>
                                        <option value="Andorra">Andorra</option>
                                        <option value="Angola">Angola</option>
                                        <option value="Anguilla">Anguilla</option>
                                        <option value="Antarctica">Antarctica</option>
                                        <option value="Antigua and Barbuda">Antigua and Barbuda</option>
                                        <option value="Argentina">Argentina</option>
                                        <option value="Armenia">Armenia</option>
                                        <option value="Aruba">Aruba</option>
                                        <option value="Australia">Australia</option>
                                        <option value="Austria">Austria</option>
                                        <option value="Azerbaijan">Azerbaijan</option>
                                        <option value="Bahamas">Bahamas</option>
                                        <option value="Bahrain">Bahrain</option>
                                        <option value="Bangladesh">Bangladesh</option>
                                        <option value="Barbados">Barbados</option>
                                        <option value="Belarus">Belarus</option>
                                        <option value="Belgium">Belgium</option>
                                        <option value="Belize">Belize</option>
                                        <option value="Benin">Benin</option>
                                        <option value="Bermuda">Bermuda</option>
                                        <option value="Bhutan">Bhutan</option>
                                        <option value="Bolivarian Republic of Venezuela">Bolivarian Republic of Venezuela</option>
                                        <option value="Bonaire, Sint Eustatios and Saba">Bonaire, Sint Eustatios and Saba</option>
                                        <option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
                                        <option value="Botswana">Botswana</option>
                                        <option value="Bouvet Island">Bouvet Island</option>
                                        <option value="Brazil">Brazil</option>
                                        <option value="British Indian Ocean Territory">British Indian Ocean Territory</option>
                                        <option value="Brunei Darussalam">Brunei Darussalam</option>
                                        <option value="Bulgaria">Bulgaria</option>
                                        <option value="Burkina Faso">Burkina Faso</option>
                                        <option value="Burundi">Burundi</option>
                                        <option value="Cambodia">Cambodia</option>
                                        <option value="Cameroon">Cameroon</option>
                                        <option value="Canada">Canada</option>
                                        <option value="Cape Verde">Cape Verde</option>
                                        <option value="Cayman Islands">Cayman Islands</option>
                                        <option value="Central African Republic">Central African Republic</option>
                                        <option value="Chad">Chad</option>
                                        <option value="Chile">Chile</option>
                                        <option value="China">China</option>
                                        <option value="Christmas Island">Christmas Island</option>
                                        <option value="Cocos (Keeling) Islands">Cocos (Keeling) Islands</option>
                                        <option value="Colombia">Colombia</option>
                                        <option value="Comoros">Comoros</option>
                                        <option value="Congo">Congo</option>
                                        <option value="Cook Islands">Cook Islands</option>
                                        <option value="Costa Rica">Costa Rica</option>
                                        <option value="Cote D'Ivoire">Cote D'Ivoire</option>
                                        <option value="Croatia">Croatia</option>
                                        <option value="Cuba">Cuba</option>
                                        <option value="Curacao">Curacao</option>
                                        <option value="Cyprus">Cyprus</option>
                                        <option value="Czech Republic">Czech Republic</option>
                                        <option value="Democratic People's Republic of Korea">Democratic People's Republic of Korea</option>
                                        <option value="The Democratic Republic of the Congo">The Democratic Republic of the Congo</option>
                                        <option value="Denmark">Denmark</option>
                                        <option value="Djibouti">Djibouti</option>
                                        <option value="Dominica">Dominica</option>
                                        <option value="Dominican Republic">Dominican Republic</option>
                                        <option value="Ecuador">Ecuador</option>
                                        <option value="Egypt">Egypt</option>
                                        <option value="El Salvador">El Salvador</option>
                                        <option value="Equatorial Guinea">Equatorial Guinea</option>
                                        <option value="Eritrea">Eritrea</option>
                                        <option value="Estonia">Estonia</option>
                                        <option value="Ethiopia">Ethiopia</option>
                                        <option value="Falkland Islands (Malvinas)">Falkland Islands (Malvinas)</option>
                                        <option value="Faroe Islands">Faroe Islands</option>
                                        <option value="Federated States of Micronesia">Federated States of Micronesia</option>
                                        <option value="Fiji">Fiji</option>
                                        <option value="Finland">Finland</option>
                                        <option value="The Former Yugoslav Republic of Macedonia">The Former Yugoslav Republic of Macedonia</option>
                                        <option value="France">France</option>
                                        <option value="French Guiana">French Guiana</option>
                                        <option value="French Polynesia">French Polynesia</option>
                                        <option value="French Southern Territories">French Southern Territories</option>
                                        <option value="Gabon">Gabon</option>
                                        <option value="Gambia">Gambia</option>
                                        <option value="Georgia">Georgia</option>
                                        <option value="Germany">Germany</option>
                                        <option value="Ghana">Ghana</option>
                                        <option value="Gibraltar">Gibraltar</option>
                                        <option value="Greece">Greece</option>
                                        <option value="Greenland">Greenland</option>
                                        <option value="Grenada">Grenada</option>
                                        <option value="Guadeloupe">Guadeloupe</option>
                                        <option value="Guam">Guam</option>
                                        <option value="Guatemala">Guatemala</option>
                                        <option value="Guernsey">Guernsey</option>
                                        <option value="Guinea">Guinea</option>
                                        <option value="Guinea-Bissau">Guinea-Bissau</option>
                                        <option value="Guyana">Guyana</option>
                                        <option value="Haiti">Haiti</option>
                                        <option value="Heard Island and McDonald Islands">Heard Island and McDonald Islands</option>
                                        <option value="Holy See (Vatican City State)">Holy See (Vatican City State)</option>
                                        <option value="Honduras">Honduras</option>
                                        <option value="Hong Kong">Hong Kong</option>
                                        <option value="Hungary">Hungary</option>
                                        <option value="Iceland">Iceland</option>
                                        <option value="India">India</option>
                                        <option value="Indonesia">Indonesia</option>
                                        <option value="Iraq">Iraq</option>
                                        <option value="Ireland">Ireland</option>
                                        <option value="Islamic Republic of Iran">Islamic Republic of Iran</option>
                                        <option value="Isle of Man">Isle of Man</option>
                                        <option value="Israel">Israel</option>
                                        <option value="Italy">Italy</option>
                                        <option value="Jamaica">Jamaica</option>
                                        <option value="Japan">Japan</option>
                                        <option value="Jersey">Jersey</option>
                                        <option value="Jordan">Jordan</option>
                                        <option value="Kazakhstan">Kazakhstan</option>
                                        <option value="Kenya">Kenya</option>
                                        <option value="Kiribati">Kiribati</option>
                                        <option value="Kuwait">Kuwait</option>
                                        <option value="Kyrgyzstan">Kyrgyzstan</option>
                                        <option value="Laos People's Democratic Republic">Laos People's Democratic Republic</option>
                                        <option value="Latvia">Latvia</option>
                                        <option value="Lebanon">Lebanon</option>
                                        <option value="Lesotho">Lesotho</option>
                                        <option value="Liberia">Liberia</option>
                                        <option value="Libya">Libya</option>
                                        <option value="Liechtenstein">Liechtenstein</option>
                                        <option value="Lithuania">Lithuania</option>
                                        <option value="Luxembourg">Luxembourg</option>
                                        <option value="Macao">Macao</option>
                                        <option value="Madagascar">Madagascar</option>
                                        <option value="Malawi">Malawi</option>
                                        <option value="Malaysia">Malaysia</option>
                                        <option value="Maldives">Maldives</option>
                                        <option value="Mali">Mali</option>
                                        <option value="Malta">Malta</option>
                                        <option value="Marshall Islands">Marshall Islands</option>
                                        <option value="Martinique">Martinique</option>
                                        <option value="Mauritania">Mauritania</option>
                                        <option value="Mauritius">Mauritius</option>
                                        <option value="Mayotte">Mayotte</option>
                                        <option value="Mexico">Mexico</option>
                                        <option value="Monaco">Monaco</option>
                                        <option value="Mongolia">Mongolia</option>
                                        <option value="Montenegro">Montenegro</option>
                                        <option value="Montserrat">Montserrat</option>
                                        <option value="Morocco">Morocco</option>
                                        <option value="Mozambique">Mozambique</option>
                                        <option value="Myanmar">Myanmar</option>
                                        <option value="Namibia">Namibia</option>
                                        <option value="Nauru">Nauru</option>
                                        <option value="Nepal">Nepal</option>
                                        <option value="Netherlands">Netherlands</option>
                                        <option value="New Caledonia">New Caledonia</option>
                                        <option value="New Zealand">New Zealand</option>
                                        <option value="Nicaragua">Nicaragua</option>
                                        <option value="Niger">Niger</option>
                                        <option value="Nigeria">Nigeria</option>
                                        <option value="Niue">Niue</option>
                                        <option value="Norfolk Island">Norfolk Island</option>
                                        <option value="Northern Mariana Islands">Northern Mariana Islands</option>
                                        <option value="Norway">Norway</option>
                                        <option value="Oman">Oman</option>
                                        <option value="Pakistan">Pakistan</option>
                                        <option value="Palau">Palau</option>
                                        <option value="Palestinian Territory, Occupied">Palestinian Territory, Occupied</option>
                                        <option value="Panama">Panama</option>
                                        <option value="Papua New Guinea">Papua New Guinea</option>
                                        <option value="Paraguay">Paraguay</option>
                                        <option value="Peru">Peru</option>
                                        <option value="Philippines">Philippines</option>
                                        <option value="Pitcairn">Pitcairn</option>
                                        <option value="Plurinational State of Bolivia">Plurinational State of Bolivia</option>
                                        <option value="Poland">Poland</option>
                                        <option value="Portugal">Portugal</option>
                                        <option value="Puerto Rico">Puerto Rico</option>
                                        <option value="Qatar">Qatar</option>
                                        <option value="Republic of Korea">Republic of Korea</option>
                                        <option value="Republic of Moldova">Republic of Moldova</option>
                                        <option value="Reunion">Reunion</option>
                                        <option value="Romania">Romania</option>
                                        <option value="Russian Federation">Russian Federation</option>
                                        <option value="Rwanda">Rwanda</option>
                                        <option value="Saint Barthelemy">Saint Barthelemy</option>
                                        <option value="Saint Helena, Ascension and Tristan da Cunha">Saint Helena, Ascension and Tristan da Cunha</option>
                                        <option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
                                        <option value="Saint Lucia">Saint Lucia</option>
                                        <option value="Saint Martin (French)">Saint Martin (French)</option>
                                        <option value="Saint Pierre and Miquelon">Saint Pierre and Miquelon</option>
                                        <option value="Saint Vincent and the Grenadines">Saint Vincent and the Grenadines</option>
                                        <option value="Samoa">Samoa</option>
                                        <option value="San Marino">San Marino</option>
                                        <option value="Sao Tome and Principe">Sao Tome and Principe</option>
                                        <option value="Saudi Arabia">Saudi Arabia</option>
                                        <option value="Senegal">Senegal</option>
                                        <option value="Serbia">Serbia</option>
                                        <option value="Seychelles">Seychelles</option>
                                        <option value="S. Georgia &amp; S. Sandwich Isls.">S. Georgia &amp; S. Sandwich Isls.</option>
                                        <option value="Sierra Leone">Sierra Leone</option>
                                        <option value="Singapore">Singapore</option>
                                        <option value="Sint Maarten (Dutch)">Sint Maarten (Dutch)</option>
                                        <option value="Slovakia">Slovakia</option>
                                        <option value="Slovenia">Slovenia</option>
                                        <option value="Solomon Islands">Solomon Islands</option>
                                        <option value="Somalia">Somalia</option>
                                        <option value="South Africa">South Africa</option>
                                        <option value="South Sudan">South Sudan</option>
                                        <option value="Spain">Spain</option>
                                        <option value="Sri Lanka">Sri Lanka</option>
                                        <option value="Sudan">Sudan</option>
                                        <option value="Suriname">Suriname</option>
                                        <option value="Svalbard and Jan Mayen">Svalbard and Jan Mayen</option>
                                        <option value="Swaziland">Swaziland</option>
                                        <option value="Sweden">Sweden</option>
                                        <option value="Switzerland">Switzerland</option>
                                        <option value="Syrian Arab Republic">Syrian Arab Republic</option>
                                        <option value="Taiwan, Province of China">Taiwan, Province of China</option>
                                        <option value="Tajikistan">Tajikistan</option>
                                        <option value="Thailand">Thailand</option>
                                        <option value="Timor-Leste">Timor-Leste</option>
                                        <option value="Togo">Togo</option>
                                        <option value="Tokelau">Tokelau</option>
                                        <option value="Tonga">Tonga</option>
                                        <option value="Trinidad and Tobago">Trinidad and Tobago</option>
                                        <option value="Tunisia">Tunisia</option>
                                        <option value="Turkey">Turkey</option>
                                        <option value="Turkmenistan">Turkmenistan</option>
                                        <option value="Turks and Caicos Islands">Turks and Caicos Islands</option>
                                        <option value="Tuvalu">Tuvalu</option>
                                        <option value="Uganda">Uganda</option>
                                        <option value="Ukraine">Ukraine</option>
                                        <option value="United Arab Emirates">United Arab Emirates</option>
                                        <option value="United Kingdom">United Kingdom</option>
                                        <option value="United Republic of Tanzania">United Republic of Tanzania</option>
                                        <option value="Uruguay">Uruguay</option>
                                        <option value="USA Minor Outlying Islands">USA Minor Outlying Islands</option>
                                        <option value="Uzbekistan">Uzbekistan</option>
                                        <option value="Vanuatu">Vanuatu</option>
                                        <option value="Viet Nam">Viet Nam</option>
                                        <option value="Virgin Islands (British)">Virgin Islands (British)</option>
                                        <option value="Virgin Islands (USA)">Virgin Islands (USA)</option>
                                        <option value="Wallis and Futuna">Wallis and Futuna</option>
                                        <option value="West Bank - Gaza">West Bank - Gaza</option>
                                        <option value="Western Sahara">Western Sahara</option>
                                        <option value="Yemen">Yemen</option>
                                        <option value="Zambia">Zambia</option>
                                        <option value="Zimbabwe">Zimbabwe</option>
                                    </select>
            					</div>	
            				</div>
            			</div>	
            			<h2 class="section-header-container">Payment Information							
            			</h2> 
						<div class="row">
            				<div class="col-xs-6" style="padding:0;">
						<div class="select_payment_mode active stripe">
								<img class=" lazyloaded" alt="" loading="lazy" data-src="http://www.irousa.org/wp-content/uploads/2023/09/visa-319d545c6fd255c9aad5eeaad21fd6f7f7b4fdbdb1a35ce83b89cca12a187f00-1.svg" width="38" height="24" src="http://www.irousa.org/wp-content/uploads/2023/09/visa-319d545c6fd255c9aad5eeaad21fd6f7f7b4fdbdb1a35ce83b89cca12a187f00-1.svg">
								<img class=" lazyloaded" alt="" loading="lazy" data-src="http://www.irousa.org/wp-content/uploads/2023/09/master-173035bc8124581983d4efa50cf8626e8553c2b311353fbf67485f9c1a2b88d1.svg" width="38" height="24" src="http://www.irousa.org/wp-content/uploads/2023/09/master-173035bc8124581983d4efa50cf8626e8553c2b311353fbf67485f9c1a2b88d1.svg">
								<img class=" lazyloaded" alt="" loading="lazy" data-src="http://www.irousa.org/wp-content/uploads/2023/09/american_express-2264c9b8b57b23b0b0831827e90cd7bcda2836adc42a912ebedf545dead35b20.svg" width="38" height="24" src="http://www.irousa.org/wp-content/uploads/2023/09/american_express-2264c9b8b57b23b0b0831827e90cd7bcda2836adc42a912ebedf545dead35b20.svg">
						</div>
						</div>
						<div class="col-xs-6" style="padding:0;">
						<div class="select_payment_mode paypal">
								<img class=" lazyloaded" alt="" loading="lazy" data-src="http://www.irousa.org/wp-content/uploads/2023/09/MMFY20_PayPal-PNG-Transparent-Image_3x_LC.png" width="38" height="24" src="http://www.irousa.org/wp-content/uploads/2023/09/MMFY20_PayPal-PNG-Transparent-Image_3x_LC.png">
						</div>
						</div>
						</div>	
					<div id="donate_by_stripe">	
        				<div class="form-group">
        					<label>Card Number:<span>*</span></label>
        					<input type="text" name="cardNumber" size="20" autocomplete="off" id="cardNumber" class="form-control" />
        				</div>	
        				<div class="row">
            				<div class="col-xs-4">
            					<div class="form-group">
            						<label>Month:<span>*</span></label>
            						<input type="text" name="cardExpMonth" placeholder="MM" size="2" id="cardExpMonth" class="form-control" /> 
            					</div>	
            				</div>	
            				<div class="col-xs-4">
            					<div class="form-group">
            						<label>Year:<span>*</span></label>
            						<input type="text" name="cardExpYear" placeholder="YYYY" size="4" id="cardExpYear" class="form-control" />
            					</div>	
            				</div>	
            				<div class="col-xs-4">
            					<div class="form-group">
            						<label>CVC:<span>*</span></label>
            						<input type="password" name="cardCVC" size="4" autocomplete="off" id="cardCVC" class="form-control" />
            					</div>	
            				</div>
        				</div>					
					</div>	
        				<br>	
        				<div class="form-group">
        				    <div align="center">
            			        <div>Amount:</div>
            			        <div class="heading"><span class="total_amount_text">$300</span>USD</div>
    							<input type="hidden" id="total_amount" name="total_amount" value="30000">
    							<input type="hidden" id="select_payment_type" name="select_payment_type" value="onetime">
							</div>
        				    <div align="center">
    							<input type="hidden" name="currency_code" value="USD">
    							<input type="hidden" name="item_details" value="Donation">
    							<br/>
    							<input type="submit" id="makePayment" class="btn btn-success" onclick="stripePay(event)" value="DONATE NOW">
							</div>
        				</div>			
        			</form>	
					<div id="donate_by_paypal">						
						<form id="onetime_form_paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post">
							 <input id="dntplgn_once_amount" type="hidden" name="amount" value="300.00">
							<input type="hidden" name="cmd" value="_xclick">
							<input type="hidden" name="business" value="info@irousa.org">
							<input type="hidden" name="lc" value="US">
												<input type="hidden" name="item_name" value="Donation - irousa">
							<input type="hidden" name="currency_code" value="USD">
												<input type="hidden" name="return" value="https://www.irousa.org/thank-you">
												<input type="hidden" name="cancel_return" value="https://www.irousa.org/cancel">
							<input type="hidden" name="no_note" value="0">
							<input class="dntplgn_submit_button makePayment_paypal" type="submit" name="submit" value="DONATE NOW" alt="PayPal - The safer, easier way to pay online!">
							<img decoding="async" alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
						</form>					
						<form style="display:none;" id="monthly_form_paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post"> <!-- Identify your business so that you can collect the payments. --> <input type="hidden" name="business" value="info@irousa.org"> <!-- Specify a Subscribe button. --> <input type="hidden" name="cmd" value="_xclick-subscriptions"> <!-- Identify the subscription. --> <input type="hidden" name="item_name" value="Donation - irousa">  <!-- Set the terms of the regular subscription. --> <input type="hidden" name="currency_code" value="USD"> <input id="amount_paypal" type="hidden" name="a3" value="50.00"> <input type="hidden" name="p3" value="1"> <input type="hidden" name="t3" value="M"> <!-- Set recurring payments until canceled. --> <input type="hidden" name="src" value="1"> <!-- Display the payment button. --> 
							<input class="dntplgn_submit_button makePayment_paypal" type="submit" name="submit" value="DONATE NOW" alt="PayPal - The safer, easier way to pay online!"> <img alt="" width="1" height="1" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" > 
						</form>
						<br/><br/>
					</div>
					<div class='loader_gif' style="display: none;"><img src="/wp-content/themes/astra-child/image/loader.gif"/></div>
        		</div>
        	</div>
        </div>



	</div><!-- #primary -->

<?php get_footer(); ?>
