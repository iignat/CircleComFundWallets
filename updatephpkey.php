<?php


    require("./config.php");

    $f = fopen("hostpgpkey.php", "w") or die("Unable to open file.\n");
    $ch = curl_init();

    //curl_opt($ch,CURLOPT_POST,TRUE);;

    curl_setopt($ch,CURLOPT_URL,$api_url."v1/encryption/public");
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array('accept: application/json','authorization: Bearer '.$key));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $res = curl_exec($ch);
    curl_close($ch);

    $text = "<?php \$host_pgp_key = '$res'; ?>";
    fwrite($f,$text);
    fclose($f);

?>
