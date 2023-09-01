 <?php

    require("./config.php");
    require("./lib.php");

    $pgp_key = json_decode($host_pgp_key,false);
    
    $expDate = explode('/',$_POST['expirationdate']);

    $card = new stdClass();
	$card->idempotencyKey = gen_uuid();
	$card->expMonth = intval($expDate[0]);
	$card->expYear = intval("20".$expDate[1]);
	$card->keyId = $pgp_key->data->keyId;
	$card->encryptedData = $_POST['encdata2'];
	$card->billingDetails = new stdClass();
	$card->billingDetails->name = $_POST['name'];
	$card->billingDetails->country =  $_POST['country'];
	$card->billingDetails->district = 'MA';
	$card->billingDetails->line1 = $_POST['address'];
	$card->billingDetails->line2 = "";
	$card->billingDetails->city = $_POST['city'];
	$card->billingDetails->postalCode = $_POST['postcode'];
	$card->metadata = new stdClass();
	$card->metadata->email = $_POST['email'];
	//$card->metadata->phoneNumber = "+1234567890";
	$card->metadata->sessionId = "xxx";
	$card->metadata->ipAddress = "172.33.222.1";

   $ch = curl_init();

    curl_setopt($ch,CURLOPT_URL,$api_url."v1/cards");
    curl_setopt($ch,CURLOPT_POST,TRUE);
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array('content-type: application/json','accept: application/json','authorization: Bearer '.$key));
    curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($card));
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	$res = curl_exec($ch);
	$http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
	echo "Code:".$http_code."<pre>".$res."<pre>";

	echo "<pre>";
	echo json_encode($card,JSON_PRETTY_PRINT);
	echo "</pre>";

	if($http_code==201){
	    $res = json_decode($res,false);

	    $pay = new stdClass();
		$pay->idempotencyKey = gen_uuid();
		$pay->autoCapture = true;
		$pay->amount = new stdClass();
		    $pay->amount->amount = "11";
		    $pay->amount->currency = "USD";
		$pay->source = new stdClass();
		    $pay->source->id = $res->data->id;
		    $pay->source->type = "card";
		$pay->verificationSuccessUrl = $success_url;
		$pay->verificationFailureUrl = $fail_url;
		$pay->metadata = $card->metadata;
		$pay->channel = "";
		$pay->verification = "cvv";
		$pay->keyId = $card->keyId;
		$pay->encryptedData = $_POST['encdata1'];
	    
	    curl_setopt($ch,CURLOPT_URL,$api_url."v1/payments");
	    curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($pay));
	    
	    $res = curl_exec($ch);
	    $http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);

    }
	
    curl_close($ch);
   
    echo "<pre>";
	echo json_encode($pay,JSON_PRETTY_PRINT);
	echo "</pre>";
    echo "<pre>";
     print_r($res);
    echo "</pre>";

?>
