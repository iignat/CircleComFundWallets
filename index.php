<?php

    session_start();
    require("./config.php");
    require("./countries.php");
    $_SESSION['amount'] = 111.0;
    $_SESSION['curr'] = "EUR";
?>

<!DOCTYPE html>
<html lang="en" >
<head>
  <meta charset="UTF-8">
  <title>Payment Form</title>
  <link rel='stylesheet' href='//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css'><link rel="stylesheet" href="./style.css">

</head>
<body>
<!-- partial:index.partial.html -->
<link href='https://fonts.googleapis.com/css?family=Open+Sans:400,300' rel='stylesheet' type='text/css'>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">

<div class="container">
  <div id="Checkout" class="inline">
      <h1>Pay Invoice</h1>
      <div class="card-row">
          <span class="visa"></span>
          <span class="mastercard"></span>
          <span class="amex"></span>
          <span class="discover"></span>
      </div>
      <form id="pay_form" method="post" action="./process.php">
          <div class="form-group">
              <label for="PaymentAmount">Payment amount</label>
              <div class="amount-placeholder">
                  <span><?php  echo $_SESSION['amount']; ?></span>
		  <span><?php echo $_SESSION['curr']; ?></span>
              </div>
          </div>
          <div class="form-group">
              <label for="name">Name on card</label>
              <input id="name" name="name" class="form-control" type="text" maxlength="255"></input>
          </div>
          <div class="form-group">
              <label for="email">E-mail</label>
              <input id="email" name="email" class="form-control" type="text" maxlength="255"></input>
          </div>
          <div class="form-group">
              <label for="cardnumber">Card number</label>
              <input id="cardnumber" class="null card-image form-control" type="text" maxlength="20" pattern="[0-9 ]*" inputmode="numeric"></input>
          </div>
          <div class="expiry-date-group form-group">
              <label for="expirationdate">Expiry date</label>
              <input id="expirationdate" name="expirationdate" class="form-control" type="text" placeholder="MM / YY" maxlength="7" pattern="[0-9/]*" inputmode="numeric"></input>
          </div>
          <div class="security-code-group form-group">
              <label for="securitycode">Security code</label>
              <div class="input-container" >
                  <input id="securitycode" class="form-control" type="password" maxlength="3" pattern="[0-9]*" inputmode="numeric"></input>
                  <i id="cvc" class="fa fa-question-circle"></i>
              </div>
              <div class="cvc-preview-container two-card hide">
                  <div class="amex-cvc-preview"></div>
                  <div class="visa-mc-dis-cvc-preview"></div>
              </div>
          </div>
          <input id="encdata1" name="encdata1" type="hidden" value="">
    	  <input id="encdata2" name="encdata2" type="hidden" value="">
          <div class="zip-code-group form-group">
              <label for="postcode">ZIP/Postal code</label>
              <div class="input-container">
                  <input id="postcode" name="postcode" class="form-control" type="text" maxlength="10"></input>
                  <a tabindex="0" role="button" data-toggle="popover" data-trigger="focus" data-placement="left" data-content="Enter the ZIP/Postal code for your credit card billing address."><i class="fa fa-question-circle"></i></a>
              </div>
         </div>
         
	<div class="district-code-group form-group">
	<label for="district">Region/District</label>
              <div class="input-container">
                  <input id="district" name="district" class="form-control" type="text" maxlength="64"></input>
                  <a tabindex="0" role="button" data-toggle="popover" data-trigger="focus" data-placement="left" data-content="State / County / Province / Region portion of the address. If the country is US or Canada, then district is required and should use the two-letter code for the subdivision."><i class="fa fa-question-circle"></i></a>
              </div>
        </div>
	<div class="form-group">
	<label for="city">City</label>
              <div class="input-container">
                  <input id="city" name="city" class="form-control" type="text" maxlength="10"></input>
                  <a tabindex="0" role="button" data-toggle="popover" data-trigger="focus" data-placement="left" data-content="Enter the City for your credit card billing address."><i class="fa fa-question-circle"></i></a>
              </div>
        </div>
        <div class="form-group">
	<label for="address">Address</label>
 	<div class="input-container">
                  <input id="address" name="address" class="form-control" type="text" maxlength="10"></input>
                  <a tabindex="0" role="button" data-toggle="popover" data-trigger="focus" data-placement="left" data-content="Enter credit card billing address."><i class="fa fa-question-circle"></i></a>
              </div>
        </div>
        <div class="form-group">
	<label for="country">Country</label>
 	<div class="input-container">
                  <Select id="country" name="country" class="form-control">
                      <?php echo countries_select(); ?>
                  </Select>
                  <a tabindex="0" role="button" data-toggle="popover" data-trigger="focus" data-placement="left" data-content="Select the Country for your credit card billing address."><i class="fa fa-question-circle"></i></a>
          	</div>
          </div>
          <button id="PayButton" class="btn btn-block btn-success submit-button" type="submit">
              <span class="submit-button-lock"></span>
              <span class="align-middle">Pay <?php echo $_SESSION['amount']." ".$_SESSION['curr']; ?></span>
          </button>
      </form>
  </div>
</div>
<!-- partial -->
  <script src='//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
<script src='//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js'></script><script  src="./script.js"></script>
<script src="./openpgp.min.js"></script>
<script>
    
     const host_pgp_key = JSON.parse( '<?php echo $host_pgp_key; ?> ');
     const form = document.getElementById('pay_form');
     form.addEventListener('submit', senddatafn);
     
    async function senddatafn(event){
	event.preventDefault();
	const fd = new FormData(event.target);
	const formdata = {};
	
	formdata['cvv'] = document.getElementById('securitycode').value;
	const opgp = window.openpgp;
	const decodedPublicKey = await opgp.readKey({ armoredKey: atob(host_pgp_key.data.publicKey) });
	message = await opgp.createMessage({text:JSON.stringify(formdata)});
	document.getElementById('encdata1').value = btoa(await opgp.encrypt({message,encryptionKeys:decodedPublicKey,}));
	formdata['number'] = document.getElementById('cardnumber').value.replace(/\s+/g,'');
	message = await opgp.createMessage({text:JSON.stringify(formdata)});
	document.getElementById('encdata2').value = btoa(await opgp.encrypt({message,encryptionKeys:decodedPublicKey,}));
	
	form.submit();
    }
    
</script>

</body>
</html>
