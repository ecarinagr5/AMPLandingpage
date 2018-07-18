<?php

if(!empty($_POST)){
    /*$domain_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    header("Content-type: application/json");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Origin: ". str_replace('.', '-','http://previewsandbox.com') .".cdn.ampproject.org");
    header("AMP-Access-Control-Allow-Source-Origin: http://previewsandbox.com");
    header("Access-Control-Expose-Headers: AMP-Access-Control-Allow-Source-Origin");
    header("AMP-Redirect-To: http://previewsandbox.com/peugeot/experiencia-peugeot/thankyoupage");
    header("Access-Control-Expose-Headers: AMP-Redirect-To, AMP-Access-Control-Allow-Source-Origin"); 
    echo json_encode(array('successmsg'=>'data post'));
    exit;*/
    header("access-control-allow-credentials:true");
    header("AMP-Same-Origin: true");
    header("Access-Control-Allow-Credentials: true");
    header("access-control-allow-headers:Content-Type, Content-Length, Accept-Encoding, X-CSRF-Token");
    header("access-control-allow-methods:POST, GET, OPTIONS");
    header("access-control-allow-origin:".$_SERVER['HTTP_ORIGIN']);
    header("Access-Control-Max-Age: <delta-seconds>");
    header("Cache-Control: private, no-cache");
    header('AMP-Access-Control-Allow-Source-Origin: '.'http://'. $_SERVER['HTTP_HOST']);
    header("access-control-expose-headers:AMP-Access-Control-Allow-Source-Origin");
    // change to represent your site's protocol, either http or https
    header("amp-access-control-allow-source-origin:http://".$_SERVER['HTTP_HOST']);
    header("Content-Type: application/json");
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $output = ['email' => $email];
    header("Content-Type: application/json");
    echo json_encode($output);
    // $post_data['u'] and $post_data['id'] are required hidden field per:
    // http://kb.mailchimp.com/lists/signup-forms/host-your-own-signup-forms
    $post_data['u'] = 'uvalue';
    $post_data['id'] = 'idvalue';
    // $post_data['MERGE0'] represents the value of my email submission input tag's name attribute.
    // In my case the attribut of name="MERGE0", so $post_data['MERGE0'] is used as a variable.
    $post_data['MERGE0'] = urlencode($_POST['email']);
    foreach($post_data as $key => $value) {
        $post_items[] = $key.
        '='.$value;
    }
    $post_string = implode('&', $post_items);
    // Replace URL with your own. In the case of MailChimp, see:
    // http://kb.mailchimp.com/lists/signup-forms/host-your-own-signup-forms
    $curl_connection = curl_init('http://previewsandbox.com/peugeot/experiencia-peugeot/thankyoupage');
    curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $post_string);
    curl_exec($curl_connection);
    curl_close($curl_connection);
}
?>