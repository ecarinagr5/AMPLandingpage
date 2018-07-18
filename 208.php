<?php
include "content/inc/utilities.inc";
$url_server = "http://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
$model = "";
$name = "";
$lastname = "";
$phone = "";
$email = "";
$dealers = "";
$politicas = "";
$msg = "";
$solicitud= "";
$flagcotizacion = "0";
$flagprueba = "0";
$class = "error";

if( isset($_POST['modelo']) && $_POST['modelo'] != '' ){
  $model = ( isset($_POST['modelo']) && $_POST['modelo'] != '' )?trim(_rudeness(strip_tags($_POST['modelo']))):'';
  $model_name = ( isset($models_arra[$model]) )?_rudeness($models_arra[$model]):'';
  $name = ( isset($_POST['name']) && $_POST['name'] != '' )?trim(_rudeness(strip_tags($_POST['name']))):'';
  $lastname = ( isset($_POST['lastname']) && $_POST['lastname'] != '' )?trim(_rudeness(strip_tags($_POST['lastname']))):'';
  $phone = ( isset($_POST['phone']) && $_POST['phone'] != '' )?trim(_rudeness(strip_tags($_POST['phone']))):'';
  $email = ( isset($_POST['email']) && $_POST['email'] != '' )?trim(_rudeness(strip_tags($_POST['email']))):'';
  $dealers = ( isset($_POST['dealers']) && $_POST['dealers'] != '' )?trim(_rudeness(strip_tags($_POST['dealers']))):'';
  $politicas = ( isset($_POST['politicas']) && $_POST['politicas'] != '' )?trim(_rudeness(strip_tags($_POST['politicas']))):'';
  $solicitud = ( isset($_POST['solicitud']) && $_POST['solicitud'] != '' )?trim(_rudeness(strip_tags($_POST['solicitud']))):'';

  if( $model == '' || $name == '' || $lastname == '' || $phone == '' || $email == '' || $dealers == '' || $solicitud === '' ){
    $msg = "Todos los campos son necesarios.";
  }else{
    if( $politicas == 1 ){
      if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        if(preg_match('/^[a-zA-Z ]+$/', $name) == 0){
          $msg = "El nombre solo debe contener letras.";
        }else {
          if(preg_match('/^[a-zA-Z ]+$/', $lastname) == 0){
            $msg = "El nombre solo debe contener letras.";
          }else {
            if( !is_numeric($phone) || strlen($phone)!=10 ){
              $msg = "El teléfono deberá ser de 10 digitos.";
            }else{
              // comprobamos el telefono en la db
              $rows_phone = _telefono($phone,$mysqli);
              if($rows_phone > 1){
                $msg = "El teléfono ya está registrado.";
              }else{
                $email_arr = explode("@",$email);
                $ping = _ping($email_arr[1]);
                if( $ping == -1 ){
                  $msg = "El correo ingresado no es válido.";
                }else{
                    $url = "http://dev.crmpeugeot.com.mx/service/landing";
                  //$url = "https://crmpeugeot.com.mx/service";
                   //$url = "http://dev.crmpeugeot.com.mx/service";


                  $random = _getRandom($mysqli);

                  /*
                  *
                  * Inicia - Código externo
                  *
                  */
                  $utm = (isset($_GET['utm']) && trim($_GET['utm']) != false)? $_GET['utm'] : "";
                  $utm_term = (isset($_GET['utm_term']) && trim($_GET['utm_term']) != false)? $_GET['utm_term'] : "";
                  $utm_campaign = (isset($_GET['utm_campaign']) && trim($_GET['utm_campaign']) != false)? $_GET['utm_campaign'] : "";
                  $utm_source = (isset($_GET['utm_source']) && trim($_GET['utm_source']) != false)? $_GET['utm_source'] : "";
                  $utm_medium = (isset($_GET['utm_medium']) && trim($_GET['utm_medium']) != false)? $_GET['utm_medium'] : "";
                  $utm_content = (isset($_GET['utm_content']) && trim($_GET['utm_content']) != false)? $_GET['utm_content'] : "";
                  $utm_completa = (isset($_GET['utm_completa']) && trim($_GET['utm_completa']) != false)? $_GET['utm_completa'] : "";

                  $fullname = $name." ".$lastname;
                  if ($solicitud === 'Prueba')
                    {
                        $flagprueba = '1';
                    } else {
                        $flagcotizacion = '1';
                    }
                  $fields = array(
                    "datos" => '{"id":"'.$random.'","nombre":"'.$fullname.'","correo":"'.$email.'","telefono":"'.$phone.'","estado":"'.$dealers.'","modelo":"'.$model_name.'","id_modelo":"'.$model.'","tipo_ldp": "","aviso_privacidad":"'.$politicas.'","utm": "'.$utm.'","utm_campaign":"'.$utm_campaign.'","utm_source":"'.$utm_source.'","utm_medium":"'.$utm_medium.'","utm_content":"'.$utm_content.'","utm_term":"'.$utm_term.'","utm_completa":"'.$utm_completa.'","cotizacion":"'.$flagcotizacion.'","prueba_manejo":"'.$flagprueba.'"}'
                  );
                  echo '<script>console.log('. json_encode( $fields ) .')</script>';
                  /*
                  *
                  * Termina - Código externo
                  *
                  */

                  $post = curl_init();
                  curl_setopt($post, CURLOPT_URL, $url);
                  curl_setopt($post, CURLOPT_POST, count($fields));
                  curl_setopt($post, CURLOPT_POSTFIELDS, $fields);
                  curl_setopt($post, CURLOPT_RETURNTRANSFER, 1);
            

                  $result = curl_exec($post);
                  curl_close($post);
                  $msg = $result;
                  $result = json_decode($result);
                  $data = json_decode($fields['datos']);

                  $data->nombre = $name;

                  if( $result->code2 === 202 || $result->code2 === 200){
                    // obtenemos el id del usuario
                    $lead = "";
                    $msg_explode = explode(" ", $result->msg);
                    if (isset($msg_explode[1])) {
                      $lead = trim($msg_explode[1]);
                    }
                    $data->lead = $lead;

                    $class = 'success';
                    $msg = "";
                    echo '<script>console.log("succes")</script>';
                    header("Location: http://previewsandbox.com/peugeot/experiencia-peugeot/thankyoupage");
                    _save($data,$mysqli);
                  }else {
                    header("Location: http://previewsandbox.com/peugeot/experiencia-peugeot/thankyoupage");
                    _save($data,$mysqli);
                    echo '<script>console.log("succes2")</script>';
                    $msg = $result->msg;
                  }
                }
              }
            }
          }
        }
      }else{
        $msg = "Ingresa un correo válido.";
      }
    }else{
      $msg = "Debes aceptar las políticas de privacidad.";
    }
  }
}else{
  /*
  *
  * Inicia - Código externo
  *
  */
  $utm = (isset($_GET['utm']) && trim($_GET['utm']) != false)? $_GET['utm'] : "";
  $utm_term = (isset($_GET['utm_term']) && trim($_GET['utm_term']) != false)? $_GET['utm_term'] : "";
  $utm_campaign = (isset($_GET['utm_campaign']) && trim($_GET['utm_campaign']) != false)? $_GET['utm_campaign'] : "";
  $utm_source = (isset($_GET['utm_source']) && trim($_GET['utm_source']) != false)? $_GET['utm_source'] : "";
  $utm_medium = (isset($_GET['utm_medium']) && trim($_GET['utm_medium']) != false)? $_GET['utm_medium'] : "";
  $utm_content = (isset($_GET['utm_content']) && trim($_GET['utm_content']) != false)? $_GET['utm_content'] : "";
  $utm_completa = (isset($_GET['utm_completa']) && trim($_GET['utm_completa']) != false)? $_GET['utm_completa'] : "";
  /*
  *
  * Termina - Código externo
  *
  */
}
?>
<!DOCTYPE html cross-domain-policy SYSTEM "http://www.macromedia.com/xml/dtds/cross-domain-policy.dtd"> 
<html ⚡="">

<head>
  <meta charset="utf-8">
  <title>Peugeot 208</title>
  <link rel="shortcut icon" href="content/img/favicon.ico" />
  <link rel="canonical" href="http://previewsandbox.com/peugeot/amp/"> 
  <meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">
  <script async="" src="https://cdn.ampproject.org/v0.js"></script>
  <style amp-boilerplate="">body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate="">body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>
<!-- Components AMP -->
<script custom-element="amp-bind" src="https://cdn.ampproject.org/v0/amp-bind-0.1.js" async=""></script>
<script custom-element="amp-sidebar" src="https://cdn.ampproject.org/v0/amp-sidebar-0.1.js" async=""></script>
<script async custom-element="amp-lightbox" src="https://cdn.ampproject.org/v0/amp-lightbox-0.1.js"></script>
<script async custom-element="amp-lightbox-gallery" src="https://cdn.ampproject.org/v0/amp-lightbox-gallery-0.1.js"></script>
<script async custom-element="amp-carousel" src="https://cdn.ampproject.org/v0/amp-carousel-0.1.js"></script>
<script async custom-element="amp-position-observer" src="https://cdn.ampproject.org/v0/amp-position-observer-0.1.js"></script>
<script async custom-element="amp-animation" src="https://cdn.ampproject.org/v0/amp-animation-0.1.js"></script>
<script async custom-element="amp-youtube" src="https://cdn.ampproject.org/v0/amp-youtube-0.1.js"></script>
<script async custom-element="amp-form" src="https://cdn.ampproject.org/v0/amp-form-0.1.js"></script>

<!-- Styles -->
<style amp-keyframes>
        @font-face {
            font-family: 'PeugeotFont';
            src: url('content/fonts/Peugeot_Normal_v2.otf');
            font-style: normal;
        }
        @font-face {
            font-family: 'PeugeotBold';
            src: url('content/fonts/Peugeot_Bold.otf');
            font-style: normal;
        }
        @font-face {
            font-family: 'PeugeotFontLight';
            src: url('content/fonts/Peugeot_Light_v2.otf');
            font-style: normal;
        }
        * {
            box-sizing: border-box;
            margin: 0;
        }
        html {
            font-family: 'PeugeotFontLight','Montserrat', sans-serif;
            line-height: 1.15;
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
            clear:both;
            overflow-x:hidden;
        }
        body {
            margin: 0;
            background:#000;
            overflow-x:hidden;
        }
        .center {
            margin:auto;
            text-align: center;
        }
        .left {
            text-align: left;
        }
        .right {
            table-layout: right;
        }
        [class*="col-"] {
            float: left;
        }
        /* For desktop: */
        .col-1 {width: 8.33%;}
        .col-2 {width: 16.66%;}
        .col-3 {width: 25%;}
        .col-4 {width: 33.33%;}
        .col-5 {width: 41.66%;}
        .col-6 {width: 50%;}
        .col-7 {width: 58.33%;}
        .col-8 {width: 66.66%;}
        .col-9 {width: 75%;}
        .col-10 {width: 83.33%;}
        .col-11 {width: 91.66%;}
        .col-12 {width: 100%;}
        
        @media only screen and (max-width: 767px) {
            /* For mobile phones: */
            [class*="col-"] {
            width: 100%;
            }
        }
        *:focus { outline: none; } 
/*Buton 208*/
div.button-container {
    display: block;
    width: 110px;
    height: 100px;
    position: fixed;
    z-index: 9;
    top:50%;
    right: 0;
    font-family: 'PeugeotFontLight', 'Montserrat', sans-serif;
    -webkit-animation: fadeInSlow 3s ease-in-out; /* Safari 4.0 - 8.0 */
    animation: fadeInSlow 3s ease-in-out;
  }
  
  #btnCar .button-container {
      display: block;
      width: 100px;
      height: 100px;
      position: absolute;
      z-index: 9;
      top:50%;
      left: 0;
    }
    #btn-promo {
      display: block;
      width: 100px;
      position: fixed;
      z-index: 9;
      top:6%;
      right: -5px;
      font-size: 12px;
      cursor:pointer;
    }
    #btn-promo .arrowRight {
        width: 90px;
        height: 20px;
        padding: 5px 0px;
        margin: 0 0 0 0;
        font-style: italic;
    }
    #sidebarPromo {
        width:30%;
        font-size: 16px;
        padding: 2%;
        background: black;
        text-align:justify;
    }
    #sidebarPromo .contenido-promocion p {
        color: white;
        font-size: 1.2vh;
    }
    #sidebarPromo .legales {
        font-size: 1.2vh;
    }
    .amp-close-imagepromo {
        top: -20px;
        left: 300px;
        cursor: pointer;
        color: black;
    }
    div.shadow-button {
      background: white;
      position: absolute;
      top:-45px;
      left:0;
      z-index: 8;
      opacity: .3;
      width: 80px; 
      height: 160px; 
      -moz-border-radius: 0 100px 100px 0;
      -webkit-border-radius: 0 100px 100px 0;
      border-radius: 0 100px 100px 0;
    }
    div.shadow-button:hover {
      width: 90px; 
    }
    .button-container p {
      font-family: 'PeugeotFontLight', 'Montserrat', sans-serif;
      font-size: 12px;
      color: white;
      padding: 5px 0px 3px 15px;
    }
    .button-container .parrafo-button {
      font-family: 'PeugeotFontLight', 'Montserrat', sans-serif;
      font-weight: 900;
      font-style: italic;
      padding: 0px 15px 0px 0px;
    }
    .button-container p:nth-child(2) {
    width: 90px;
    height: 20px;
    padding: 5px 0px;
    margin: 0 0 0 0;
    font-style: italic;
   background:url(content/img/flecha301.png) repeat center;
}
    .button-container p:nth-child(2):hover {
        margin:0 0 0 -12px;
    }
        /*menu 301*/
        #menu301 {
            font-family: 'PeugeotFontLight', 'Montserrat', sans-serif;
        }
        #menu301 ul {
            list-style-type: none;
            overflow: hidden;
        }
        #menu301 ul li {
            padding: 0;
            text-align: left;
        }
        #menu301 ul li p {
            border: .5px solid white;
            width: 60px;
            text-align: left;
            margin: 0 20% 0 -25%;
        }
        #menu301 ul li a {
            font-size: 12px;
            color:white;
            text-decoration: none;
            text-align: left;
        }
        #menu301 ul li a.active {
            padding-left: 0px;
            font-size: 13px;
            text-align: right;
        }
        #menu301 ul li a:hover {
        padding-left: 40px;
        }
        .container-menu301 {
            display: block;
            width: 200px;
            height: auto;
            position: fixed;
            z-index: 9;
            top:35%;
            left:-4%;
        }
        .section-wrapper {
            max-height: 900px;
            overflow: auto;
            position: relative;
        }
        .first-section {
            background: url(content/img/208-bg-1.jpg);
            background-attachment: fixed;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            height:800px;
            top:0;
        }
        .fichatecnica {
            margin: 5% auto;
            cursor: pointer;
        }
        .fichatecnica:hover {
            -webkit-box-shadow: -2px 2px 5px -2px rgba(255,255,255,1);
            -moz-box-shadow: -2px 2px 5px -2px rgba(255,255,255,1);
            box-shadow: -2px 2px 5px -2px rgba(255,255,255,1);
        }
        .header {
            position: fixed;
            z-index: 3;
        }
        .header amp-img {
            margin: 1% 22%;
        }
        .fixed {
            position: fixed;
        }
        .aligner {
          display: flex;
          align-items: center;
          justify-content: center;
          display: -webkit-box;  /* OLD - iOS 6-, Safari 3.1-6, BB7 */
          display: -ms-flexbox;  /* TWEENER - IE 10 */
          display: -webkit-flex; /* NEW - Safari 6.1+. iOS 7.1+, BB10 */
        }
        .wrapper-content {
            text-align: center;
            height: 635px;
            margin-left: auto;
            margin-right: auto;
            display: flex;
            justify-content: center;
            position: relative;
        }
        .center-align {
            display: inline-block;
        }
        .btn {
            margin: 10% 0 0 20%; 
        }
        #minititle {
            margin: 12% 0 0 0;
            display: block;
            margin-left: auto;
            margin-right: auto;
            left:0;
            right:0;
        }
        .title-initial {
            margin: 5% 0 0 0;
            display: block;
            margin-left: auto;
            margin-right: auto;
            left:0;
            right:0;
        }
        .car-home {
            margin: 12% 0 0 0;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        #dieselComplemento {
            position: fixed;
            top:63%;
            right: 26%;
        }
        #imgMotorLT {
            position: fixed;
            top:43%;
            left: 18%;
        }
        div#optionsIcons.container-buttons-options {
            position: fixed;
            z-index: 0;
            height: auto;
            bottom:14%;
            left: 1.0%;
            width: 200px;
          }
          .container-buttons-options .ico {
            float: left;
            padding: 5px;
          }
          .container-buttons-options .ico amp-img {
            width: 40px;
            cursor: pointer;
          }
          .container-buttons-options .ico amp-img:hover {
            -webkit-transform: translateY(-2px);
            transform: translateY(-2px);  
          }
          #optionsInfo {
            position: relative;
            float: left;
          }
          div.leyenda {
            position: fixed;
            font-size: 1.2vh;
            text-align: justify;
            width: 15%;
            bottom:8%;
            left: 1.5%;
            color: white;
          }
          .no-lopagas {
            text-align: right;
            position: fixed;
            z-index: 0;
            right: 34px;
            bottom: 8%;
          }
           /*Aviso de privacidad*/
           .privacidadClass {
              position: fixed;
              bottom: 2px;
              color: white;
              font-size: 12px;
              cursor: pointer;
              z-index: 12;
              bottom:1%;
              left: 1.5%;
          }
          .privacidadClass:hover {
              border-bottom: 2px solid white;
          }
        
          /*ICONS INFO*/
          div#iconsInfo {
            width: 50%;
            position: fixed;
            color:white;
            font-family: 'PeugeotFontLight', 'Montserrat', sans-serif;
            overflow: hidden;
            z-index: 0;
            bottom: 15%;
            left:20%;
            text-align: left;
          }
          div#info-uno {
            margin: 0 0 0 28%;
          }
          div#iconsInfo #info-uno p:nth-child(1){
            font-size: 2vh;
            font-weight: 900;
           }
          div#iconsInfo #info-uno p:nth-child(2){
           font-size: 4vh;
           font-weight: 900;
          }
          div#iconsInfo #info-uno p:nth-child(3){
            font-size: 2vh;
            font-weight: 900;
           }
        
           div#info-dos {
            font-size: 10px;
            overflow: hidden;
            margin: 0 0 0 28%;
          }
          div#iconsInfo .container-items {
            width: 30%;
            height: auto;
            float:left;
            padding: 0;
           }
          div#iconsInfo #info-dos img {
            float: left;
           }
           div#iconsInfo #info-dos p {
            float: left;
           }
           div#info-dos div h3 {
               margin: 0 0 0 30%;
           }
          div#info-tres301 {
            margin: 0 0 0 28%;
          }
        
          div#info-tres301 amp-img {
            cursor: pointer;
            float: left;
          }
          div#info-tres301 amp-img:hover {
            -webkit-transform: translateY(-5px);
            transform: translateY(-5px);   
          }
          .show {
              display: block;
            }
         .hide {
              display: none;
         }
         .content-popup {
             width: 320px;
             margin: 0;
             padding: 10%;
             text-align: justify;
             font-size: 12px;
             background: #fff;
         }
         .content-popup p {
             color:#000;
         }
         .amp-close-image {
            top: 3px;
            left: 280px;
            cursor: pointer;
        }
        .amp-close-imageb {
            top: 10px;
            left: 260px;
            cursor: pointer;
        }
        .amp-close-imagec {
            top: 80px;
            right: 80px;
            cursor: pointer;
            position: absolute;
            z-index: 12;
        }
        #seccion-cuatro301 {
            text-align: center;
            height: 700px;
            position: relative;
            background: #000;
        }
        #interior-contenido {
            width: 100%;
            height: 600px;
            background: url(contet/img/bgicockpit-208.png);
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            position: relative;
            display: none;
        }
        #exterior-contenido {
            width: 100%;
            height: 600px;
            background: url(content/img/bgchico-208.jpg);
            background-size: 100%;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            display: block;
        }
        .background-black {
            background: black;
        }
        #interior-contenido301 {
            height: 1100px;
            position: relative;
            text-align: left;
            display:none;
        }
        #interior-contenido301 .titleEx {
            margin: 5% 0 0 10%;
            font-size:36px;
            font-weight:900;
            line-height: 0.9;
        }
        #exterior-contenido301 {
            width: 100%;
            height: 1200px;
            display: block;
            text-align: left;
        }
        .backgroundexterior {
            background: url(content/img/208-bg-2.jpg);
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: scroll;
            background-size: cover;
        }
        #exterior-contenido301 .titleEx {
            margin: 5% 0 0 10%;
            font-size: 36px;
            font-weight: 900;
            line-height: 0.9;
        }
        #exterior-contenido301 div #carexterior {
            position: relative;
            margin: 4% 0 0 0;
        }
        .contenido-diseno {
             text-align: center;
             display: flex;
             justify-content: center;
         }
        .contenido-diseno-interno {
            width: 100%;
            height: 800px;
            background: url(content/img/208-interior.png);
            background-attachment: fixed;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
        }
        #carexterior {
          position: relative;
          margin: 10% 0 0 0;
        } 
        
        #interior-contenido h1 {
          margin: 10% 0;
          color: black;
        }
        
        #exterior-contenido h1 {
            margin: 10% 0;
            color: black;
          }
        
        #canceldiseno {
            margin: 2% 0 0 70%;
            cursor: pointer;
        }
        
        a.close {
            position: absolute;
            z-index: 10;
            top:2%;
            right: 25%;
            text-decoration: none;
            color: black;
            text-shadow: 2px 2px 4px #000000;
        }
        
        /*Switch*/
        .contenido-switch {
            color:white;
            font-size: 14px;
            position: absolute;
            z-index: 9;
            top: 10%;
            left: 70%;
        }
        
        .contenido-switch span:nth-child(1),
        .contenido-switch span:nth-child(3) {
            margin: 20px;
            cursor: pointer;
        }
        .switch {
            position: relative;
            display: inline-block;
            width: 80px;
            height: 2px;
          }
          
          .switch input {display:none;}
          
          .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            -webkit-transition: .4s;
            transition: .4s;
          }
          
          .slider:before {
            position: absolute;
            content: "";
            height: 15px;
            width: 15px;
            left: 0px;
            bottom: -5px;
            background-color: white;
            -webkit-transition: .4s;
            transition: .4s;
          }
          
          input:checked + .slider {
            background-color: gray;
          }
          
          input:focus + .slider {
            box-shadow: 0 0 1px gainsboro;
          }
          
          input:checked + .slider:before {
            -webkit-transform: translateX(65px);
            -ms-transform: translateX(65px);
            transform: translateX(65px);
            background: url(content/img/arrow-izq.png) no-repeat center;
            background-size: 90%;
          }
          
          /* Rounded sliders */
          .slider.round {
            border-radius: 4px;
          }
          
          .slider.round:before {
            border-radius: 50%;
            color: gray;
            text-align: center;
            align-items: center;
            background: url(content/img/arrow-der.png) no-repeat center;
            background-size: 90%;
          }
          /*carrusel*/
        #wrapper {
            width: 100%;
            height: 600px;
            overflow: hidden;
            position: absolute;
            left: 0%;
            top: 0%;
        }
        #carousel {
            width: 100%;
            height: auto;
            position: relative;
        }
        #carousel div {
            width: 100%;
            height: 700px;
        }
        #wrapper p {
            background: url(content/img/hotspot.png);
            background-size: 100%;
            border: 1px solid #fff;
            color: #fff;
            font-size: 20px;
            font-weight: bold;
            line-height: 30px;
            text-align: center;
            text-decoration: none;
            text-shadow: 0 0 2px rgba( 0, 0, 0, 0.4 );
            display: block;
            width: 30px;
            height: 30px;
            position: absolute;
            top: 40px;
            left: 48%;
            cursor: pointer;
        }
        #wrapper p.close {
            border: 1px solid #fff;
            color: black;
            font-size: 20px;
            font-weight: bold;
            line-height: 30px;
            text-align: center;
            text-decoration: none;
            text-shadow: 0 0 2px rgba( 0, 0, 0, 0.4 );
            display: block;
            width: 30px;
            height: 30px;
            position: absolute;
            top: 32%;
            left: 97%;
            z-index: 12;
            cursor: pointer;
        }
        #wrapper p#link-1 {
            width: 70px;
            height: 70px;
            left: 55%;
            top: 15%;
            border: 0;
        }
        #wrapper p#link-2 {
            width: 70px;
            height: 70px;
            left: 25%;
            top: 28%;
            border: 0;
        }
        #wrapper p#link-3 {
            width: 70px;
            height: 70px;
            left: 80%;
            top: 18%;
            border: 0;
        }
        #wrapper p#link-4 {
            width: 80px;
            height: 80px;
            left: 20%;
            top: 70%;
            border: 0;
        }
        #wrapper p#link-5 {
            width: 80px;
            height: 80px;
            left: 45%;
            top: 10%;
            border: 0;
        }
        #wrapper p#link-1:hover,
        #wrapper p#link-2:hover,
        #wrapper p#link-3:hover,
        #wrapper p#link-4:hover,
        #wrapper p#link-5:hover {
            transform: scale(1.1);
        }
        /*Diseno exterior*/
        #wrapper-exterior {
            width: 100%;
            height: 600px;
            overflow: hidden;
            position: absolute;
            left: 0%;
            top: 0%;
        }
        #carousel-exterior {
            width: 100%;
            height: auto;
        }
        #carousel-exterior div {
            width: 100%;
            height: 750px;
            position: relative;        
        }
        #wrapper p#link-5 {
            left:40%;
        }
        .content-modal {
            position: absolute;
            background: #000;
        }
        .content-modal h2 {
            color:black;
            position: absolute;
            top:48%;
            left: 53%;
            z-index: 12;
        }
        .content-modal p {
            color:black;
            position: absolute;
            top:55%;
            left: 53%;
            z-index: 12;
            text-align: left;
            padding: 0 3% 0 0;
        }
        .content-modali {
            position: absolute;
            z-index: 12; 
            background: #000;
        }
        .content-modali h2 {
            color:black;
            position: absolute;
            top:55%;
            left: 50%;
            z-index: 12;
        }
        .content-modali p {
            color:black;
            position: absolute;
            top:60%;
            left: 50%;
            z-index: 12;
            right: 6%;
            text-align: left;
        }
        #wrapper-exterior p {
            background: url(content/img/hotspot.png);
            background-size: 100%;
            border: 1px solid #fff;
            color: #fff;
            font-size: 20px;
            font-weight: bold;
            line-height: 30px;
            text-align: center;
            text-decoration: none;
            text-shadow: 0 0 2px rgba( 0, 0, 0, 0.4 );
            display: block;
            width: 30px;
            height: 30px;
            position: absolute;
            top: 0;
            left: 0;
            cursor:pointer;
        }
        #wrapper-exterior p.close {
            border: 1px solid #fff;
            color: black;
            font-size: 20px;
            font-weight: bold;
            line-height: 30px;
            text-align: center;
            text-decoration: none;
            text-shadow: 0 0 2px rgba( 0, 0, 0, 0.4 );
            display: block;
            width: 30px;
            height: 30px;
            position: absolute;
            top: 33%;
            z-index: 12;
            left: 95%;
        }
        
        #wrapper-exterior p#tool-1{
            width: 70px;
            height: 70px;
            left: 48%;
            top: 46%;
            border: 0;
        }
        #wrapper-exterior p#tool-2 {
            width: 70px;
            height: 70px;
            left: 35%;
            top: 53%;
            border: 0;
        }
        #wrapper-exterior p#tool-3 {
            width: 70px;
            height: 70px;
            left: 73%;
            top:42%;
            border: 0;
        }
        #wrapper-exterior p#tool-4 {
            width: 70px;
            height: 70px;
            left: 38%;
            top:42%;
            border: 0;
        }
        #wrapper-exterior p#tool-5 {
            width: 70px;
            height: 70px;
            left: 46%;
            top:34%;
            border: 0;
        }
        #wrapper-exterior p#tool-1:hover,
        #wrapper-exterior p#tool-3:hover,
        #wrapper-exterior p#tool-4:hover,
        #wrapper-exterior p#tool-5:hover,
        #wrapper-exterior p#tool-2:hover
         {
            transform: scale(1.1);
        }
        .ofertaboton {
            position: absolute;
            z-index: 0;
            bottom: 10%;
            left: 42%;
            cursor: pointer;
        }
        .ofertaboton:hover {
            -webkit-box-shadow: -2px 2px 5px -2px rgba(255,255,255,1);
            -moz-box-shadow: -2px 2px 5px -2px rgba(255,255,255,1);
            box-shadow: -2px 2px 5px -2px rgba(255,255,255,1);
        }
        .ofertabotoninterior {
            position: absolute;
            z-index: 0;
            bottom: 5%;
            left: 43%;
            cursor: pointer;
        }
        .ofertabotoninterior:hover {
            -webkit-box-shadow: -2px 2px 5px -2px rgba(255,255,255,1);
            -moz-box-shadow: -2px 2px 5px -2px rgba(255,255,255,1);
            box-shadow: -2px 2px 5px -2px rgba(255,255,255,1);
        }
        #seccion-cuatro301.main-content {
            text-align: center;
            height: 1000px;
            position: relative;
        }
        /*Motor*/
        .motobackground {
            background:url(content/img/bg-motor.png);
            /* Create the parallax scrolling effect */
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            overflow: hidden;
            height: 1100px;
            padding: 10%;
            position: relative;
        }
        .motor-engine {
            position: absolute;
            top: 15%;
            left: 70%;
        }
        .motor-gasolina {
            text-align: center;
            padding: 0 5%;
            margin-left: auto;
            margin-right: auto;
            position:relative;
        }
        .motor-gasolina h2 {
            color:white;
            margin: 16.5% 0 5% 0;
            font-family: 'PeugeotFontLight','Montserrat', sans-serif;
        }
        .motor-gasolina p:nth-child(1) {
            font-family: 'PeugeotFontLight','Montserrat', sans-serif;
            color: white;
            font-size: 20px;
            margin: 15% 0;
            text-align: center;
        }
        .motor-gasolina .des-motor {
            color: white;
            padding: 1% 0% 0 0%;
            font-size: 15px;
            line-height: 20px;
            text-align: justify;
            font-family: 'PeugeotFontLight','Montserrat', sans-serif;
        }
        
        .motor-gasolina p:nth-child(2){
            color: white;
            padding: 1% 5% 0 5%;
            font-size: 13px;
            text-align: center;
            font-family: 'PeugeotFontLight','Montserrat', sans-serif;
        }
        
        .motor-diesel {
            text-align: center;
            padding: 0 5% 0 5%;
            margin-left: auto;
            margin-right: auto;
        }
        .motor-diesel h2 {
            color:white;
            margin: 16.5% 0 5% 0;
            font-family: 'PeugeotFontLight','Montserrat', sans-serif;
        }
        div.icono-motor img {
            display: block;
            margin-left: auto;
            margin-right: auto; 
        }
        p.h2second {
            color:white;
            margin: 40% 0 5% 0;
            font-size: 18px;  
            font-family: 'PeugeotFontLight','Montserrat', sans-serif;
        }
        #imgmotor {
            display: block;
            margin-left: auto;
            margin-right: auto; 
        }
        #imgmotorb {
            transform: translateX(100%);
            display: block;
            margin-left: auto;
            margin-right: auto; 
        }
        .icono-motor-partea {
            transform: translateX(-100%);  
        }
        p.subtitlemotor {
            color:white;
            margin: 10% 0 10% 0;
            font-size: 18px;  
            font-family: 'PeugeotFontLight','Montserrat', sans-serif;
        }
        .motor-diesel p:nth-child(1) {
            font-family: 'PeugeotFontLight','Montserrat', sans-serif;
            color: white;
            font-size: 20px;
            margin: 15% 0;
            text-align: center;
        }
        .motor-diesel .des-motor {
            color: white;
            padding: 1% 0% 0 0%;
            font-size: 15px;
            line-height: 20px;
            text-align: justify;
            font-family: 'PeugeotFontLight','Montserrat', sans-serif;
        }
        
        .motor-diesel p:nth-child(2){
            color: white;
            padding: 1% 5% 0 5%;
            font-size: 15px;
            text-align: justify;
            font-family: 'PeugeotFontLight','Montserrat', sans-serif;
        }
        div.icono-motor {
            float:left;
            width: 25%;
            text-align: center;
            line-height: 2pt;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        div.icono-motor p:nth-child(1) {
            color: white;
            font-size: 13px;
            text-align: center;
            font-family: 'PeugeotFontLight','Montserrat', sans-serif;
        }
        div.icono-motor p:nth-child(2) {
            color: white;
            font-size: 13px;
            text-align: center;
            font-family: 'PeugeotFontLight','Montserrat', sans-serif;
            margin: 15% 0 0 0;
        }
        div.icono-motor p:nth-child(3) {
            color: white;
            font-size: 14px;
            text-align: center;
            font-family: 'PeugeotFont','Montserrat', sans-serif;
            margin: 15% 0 0 0;
        }
        .galeria-content {
            background:black;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            height: 900px;
            position: relative;
        }
        .grid-contenta {
            width: 38%;
            height: 99.6%;
            float:left;
            background: black;
            color: white;
            padding: 9% 0 0 10%;
            position: relative;
        }
        .grid-contentb {
            width: 60%;
            height: 100%;
            float: left;
            align-items: center;
            display: flex;
            justify-content: center;
            margin-left: -2%;
            position: relative;
            top: -10%;
        }
        .grid-contentb .columna,
        .grid-contentb .columnb,
        .grid-contentb .columnc {
            height: 100%;
        }
        .grid-contentb .columna p {
            height: 50%;
            width: 100%;
            background: black;
            border: 1px solid white;
            position: relative;
            overflow: hidden;
        }
        .grid-contentb .columna p img {
            height: auto;
            width: 100%;   
            -moz-transition: all 0.6s;
            -webkit-transition: all 0.6s;
            transition: all 0.6s;
        }
        .grid-contentb .columna p img:hover {
            -moz-transform: scale(1.1);
            -webkit-transform: scale(1.1);
            transform: scale(1.1);
        }
        
        .grid-contentb .columnb p {
            height: 50%;
            width: 100%;
            background: black;
            border: 1px solid white;
            position: relative;
            overflow: hidden;
        }
        .grid-contentb .columnb p img {
            height: 100%;
            width: 100%;   
            -moz-transition: all 0.6s;
            -webkit-transition: all 0.6s;
            transition: all 0.6s;
        }
        .grid-contentb .columnb p img:hover {
            -moz-transform: scale(1.1);
            -webkit-transform: scale(1.1);
            transform: scale(1.1);
        }
        div.columnc.col-4 {
            position: relative;
            overflow: hidden; 
        }
        div.columnc.col-4 p:nth-child(1) {
            height: 40%;
            width: 100%;
            background: black;
            border: 1px solid white;
            position: relative;
            overflow: hidden;    
        }
        div.columnc.col-4 p:nth-child(2) {
            height: 60%;
            width: 100%;
            background: black;
            border: 1px solid white;
            position: relative;
            overflow: hidden;
        }
        div.columnc.col-4 p img {
            height: auto;
            width: 100%;   
            -moz-transition: all 0.6s;
            -webkit-transition: all 0.6s;
            transition: all 0.6s;  
        }
        div.columnc.col-4 p img:hover {
            -moz-transform: scale(1.1);
            -webkit-transform: scale(1.1);
            transform: scale(1.1);  
        }
        .grid-contenta h2, p {
            color: white;
        }
        .grid-contentb .columnb img:hover {
            cursor: url(content/img/loupe.png), auto;	
        }
        .grid-contentb .columna img:hover {
            cursor: url(content/img/loupe.png), auto;	
        }
        div.columnc.col-4 p:nth-child(1):hover {
            cursor: url(content/img/loupe.png), auto;	
        }
        div.columnc.col-4 p:nth-child(2):hover {
            cursor: url(content/img/loupe.png), auto;	
        }
        h2.titleBig {
            color:white;
            font-size:28px;
        }
        p.titlegal {
            letter-spacing: 1px;
            font-size: 42px;
        }
        p.descripcion-galeria {
            margin: 2% 0;
            text-align: justify;
            width: 100%;
            line-height: 20px;
        }
        .content-grid {
            width: 600px;
            height: 280px;
            background: black;
            margin:0;
        }
        .container-menu301-mobile {
            display: none;
        }
        
        .arrow-precios-mobiler {
            display: none;
        }
        .arrow-precios-mobilel {
            display: none;
        }
        .clearfix { 
            height: 20px;
        }
        .pruebademanejoboton {
            clear: both;
            margin: 10% auto;
            top:10%;
            cursor: pointer;
        }
        .pruebademanejoboton:hover {
            -webkit-box-shadow: -2px 2px 5px -2px rgba(255,255,255,1);
            -moz-box-shadow: -2px 2px 5px -2px rgba(255,255,255,1);
            box-shadow: -2px 2px 5px -2px rgba(255,255,255,1);
        }
        /*Precios*/
        .precios-content {
            background:url(content/img/208-bg-1.png);
            background-position: center;
            background-attachment: scroll;
            background-repeat: no-repeat;
            background-size: cover;
            height: 1100px;
            padding: 0 15% 0 15%;  
            text-align: center;
            position: relative;
        }
        .preciobar {
            width: 33.333%;
            float:left;
            text-align: center;
        }
        .preciobar amp-img {
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .preciobar p {
            margin: 15% 0 0 0;
            color:white;
            line-height: 20px;
            text-align:left;
            padding:0 0 0 17%;
        }
        p.titlesedan {
            margin: 20% 0 10% 0;
            color: #ffffff;
            font-size: 48px;
            font-family: 'PeugeotFontLight','Montserrat', sans-serif;
        }
        div.colorbar {
            text-align: center;
            align-items: center;
            display: flex;
            justify-content: center;
        }
        div.colorbar div {
            cursor:pointer;
        }
        span.preciospan {
            color: white;
            font-size:20px;
            font-weight: 900;
        }
        .header amp-img:nth-child(2) {
            display: none;
        }
        amp-img.menu-ico-mobile {
            display: none;
        }
        #precioscarousel {
            display: none;
            margin: 10% 0 0 0;
        }
        div.colorwhite {
            width: 14px;
            height: 14px;
            float:left;
            margin:5px;
            -moz-border-radius: 50%;
            -webkit-border-radius: 50%;
            border-radius: 50%;
            background: white;
            border: 1px solid #666666;
        }
        div.colorgris {
            width: 14px;
            height: 14px;
            float:left;
            margin:5px;
            -moz-border-radius: 50%;
            -webkit-border-radius: 50%;
            border-radius: 50%;
            background: gray;
            border: 1px solid #666666;
        }
        div.colormoka {
            width: 14px;
            height: 14px;
            float:left;
            margin:5px;
            -moz-border-radius: 50%;
            -webkit-border-radius: 50%;
            border-radius: 50%;
            background: gray;
            border: 1px solid #666666;
        }
        div.colornegro {
            width: 14px;
            height: 14px;
            float:left;
            margin:5px;
            -moz-border-radius: 50%;
            -webkit-border-radius: 50%;
            border-radius: 50%;
            background: black;
            border: 1px solid #666666;
        }
        #galeria-mobile {
            display: none;
        }
        .iconosm {
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .lightbox {
            background: #000;
            width: 100%;
            height: 100%;
            display: table-cell;
            vertical-align: middle;
        }
        .lightbox h2 {
            color:black;
            width: 100%;
            
        }
        .lightboxvideo {
            background: #000;
            width: 100%;
            height: 100%; 
        }
        p#textodetailcinco,
        p#textodetailuno,
        p#textodetaildos,
        p#textodetailtres {
            color:black;
            width: 100%;
            border: 0;
            margin:25% 25%;
            padding: 0 30%;
            text-decoration: none;
            text-shadow: 0 0 0 rgba( 0, 0, 0, 0);
            font-size: 15px;
            text-align: justify;
            line-height: 20px;
        }
        p#textodetailcinco span,
        p#textodetailuno span,
        p#textodetaildos span,
        p#textodetailtres span {
            font-size: 25px;
            font-family: 'PeugeotBold';
        }
        p#textointerioruno,
        p#textointeriordos,
        p#textointeriortres {
            color:black;
            width: 52%;
            border: 0;
            margin:18% 5% 0 5%;
            text-decoration: none;
            text-shadow: 0 0 0 rgba( 0, 0, 0, 0);
            font-size: 15px;
            text-align: justify;
            line-height: 20px;
            padding-right:10%;
        }  
        p#textointerioruno span,
        p#textointeriordos span,
        p#textointeriortres span {
            font-size: 25px;
            font-family: 'PeugeotBold';
        }
 
        .imgdesktopdiseno {
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .w3-animate-zoom {animation:animatezoom 1.5s}
        @keyframes animatezoom
        {from{transform:scale(0)} 
        to{transform:scale(1)}}
        /* FOOTER */
       #logo-footer {
            height: 80px;
            width: 150px;
            border-radius: 50% / 100% 100% 0 0;
            background: white;
            position: fixed;
                left: 45%;
                z-index: 12;
                bottom:0;
            text-align: center;
            padding: 20px 25px;
            cursor: pointer;
            border: 0px;
            -webkit-box-shadow: 0px 2px 14px 0px rgba(0,0,0,1);
            -moz-box-shadow: 0px 2px 14px 0px rgba(0,0,0,1);
            box-shadow: 0px 2px 14px 0px rgba(0,0,0,1);
            font-weight: 900;
            font-style:italic;
        }
        #logo-footer span {
            color:#0a0a9a;
            font-size: 13px;
        }
        #footer-container {   
            position: fixed;
            background: #fff;
            height: 0;
            bottom: 0%;
            width: 100%;
            z-index: 12;
        }
        /*Formulario*/
        div.content-form {
            backface-visibility: blue;
            padding: 50px 10px;
            text-align: left;
            height: 400px;
        }
        div.content-form.sideleft {
            padding-left: 12%;
            height: auto;
        }
        div.content-form.sideright {
            padding-right: 12%;
            height: auto;
        }
        div.content-form p {
            color:rgb(140, 140, 140);
            font-size: 13px;
            padding: 0px 5px 0 0;
        }
        #btn-form {
            text-align:center;
            margin: -4% 0;
        }
        .campoladob {
            padding: 20px 0;
        }
        .campoladoc {
            margin: 20px 0;
        }
        .campolado {
            float:left;
            padding: 20px 0;
        }
        .campolado label, .campo label {
            color:rgb(140, 140, 140);
            font-size: 13px;
            padding: 20px 0;
        }
        .campo {
            width: 100%;
            margin: 10px 0;
        }
        
        input {
            border: 1px solid rgb(140, 140, 140);
            margin-top:5px;
        }
        
        select {
            width: 100%;
            height: 35px;
            color:  rgb(140, 140, 140);
            font-size: 15px;
            padding-left:10px;
            margin-top:5px;
            border: 1px solid rgb(140, 140, 140);
            background: white;
            font-weight: 900;
        }
        
        input[name=name] {
            height: 35px;
        
        }
        input[type=radio], input[type=checkbox] {
            width: 20px;
            float:left;
        }
        input, input[name=fapellido] {
            width: 100%;
            height: 35px;
            color:  rgb(0, 95, 198);
            font-size: 15px;
            padding-left:10px;
        }
        input:focus {
            border: 1px solid rgb(0, 95, 198);
            color:  rgb(0, 95, 198);
            padding: 0 0 5px 5px;
        }
        .radioBtn {
            float: left;
            width: 50%;
        }
        .radioBtn label {
            float:left;
            padding:17px 0 0 5px;
            font-weight: bold;
            font-size: 15px;
        }
        span.btn-pruebamanejo:hover {
            -webkit-box-shadow: -2px 0px 5px 0px rgba(0,0,0,0.75);
            -moz-box-shadow: -2px 0px 5px 0px rgba(0,0,0,0.75);
            box-shadow: -2px 0px 5px 0px rgba(0,0,0,0.75);
        }
        
        #btn-link {
            text-align: center;
            margin: 1% 0;
        }
        input[name=enviar] {
            width: 40%;
            text-align: center;
            cursor: pointer;
            opacity: 1;
            border-width: 0px;
            border-radius: 5px;
            border-color: rgb(53, 126, 189);
            border-style: solid;
            background-color: rgb(0, 98, 196);
            color:white;
            text-decoration: none;
            font-size: 13px;
        }
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
        -webkit-appearance: none; 
        margin: 0; 
        }
        .btn-cotiza {
            font-size: 13px;
            color: rgb(0, 98, 196);
            text-decoration: underline;
        }
        .btn-cotiza:hover {
            text-decoration: none;
        }
        .upButton {
            bottom:400px;
        }
        .downButton {
            bottom: 0;
        }
        .w3-animate-leftcar {
            position:relative;animation:animateleft 0.8s;
            animation-delay: 0s; opacity: 0; 
            animation-fill-mode: forwards; }
            @keyframes animateleft{from{left:-300px;opacity:0} to{left:0;opacity:1}}

        .pruebademanejopromo {
            margin:10% auto;
        }
        #moveprecios {
            margin-top:8%;
        }
        .texto-precios-min {
            font-size:11px;
            line-height: 1;
        }
        .title-prices-main {
            font-size:22px;
            font-weight:900;
        }
        .parrafo-listo {
            font-size:11px;
        }
        /*iPad Portrait*/
       @media only screen and (min-width : 768px) and (max-width : 1023px)  {
        #imgMotorLT {
            margin-top: -25%; 
        }
        #logo-footer {
            left:42%;
        }
        div#info-dos {
            margin:0 0 0 12%;
        }
        .title-initial {
            margin: 12% 0 0 14%;
        }
        #minititle {
            margin: 24% 0 0 17%;
        }
        .wrapper-content {
            display:block;
        }
        #optionsmenu li a {
            color: white;
        }
        .btn {
                margin: 10% 0 0 0; 
            }
        .header amp-img:nth-child(2) {
                width: 8%;
                display: block;
                position: fixed;
                top: 3%;
                right: -18%;
                z-index: 18;
            }
            #sidebarMenu {
            background: #000;
            width:50%;
            }
            .amp-close-imageb {
                top: 20px;
                left: 300px;
                cursor: pointer;
            }

            .grid-contenta {
                width: 100%;
                padding: 5% 18% 0 18%;
                height: 300px;
            }
            div#optionsIcons.container-buttons-options {
                bottom: 37%;
            }
            #dieselComplemento {
                right: 1%;
            }
            #sidebarPromo {
                width:100%;
            }
            .amp-close-imagepromo {
                top: 5%;
                left: 95%;
            }
            #carousel-exterior div {
                height:1100px;
            }
            #wrapper-exterior {
                height:1100px;
            }
            div.button-container {
                margin-top: -5%
            }
            #btn-promo {
                margin-top:63%;
            }
            #exterior-contenido301 .titleEx {
                margin: 27% 0 0 15%;
            }
            p.titlesedan {
                margin:40% 0 10% 0;
            }
            div.icono-motor p:nth-child(2) {
            font-size:10px;
            }
            div.icono-motor p:nth-child(3) {
                font-size:10px;
            }
            .contenido-switch {
                left: 50%;
                top: 20%;
            }
            .precios-content {
                height:1100px;
            }
            .galeria-content {
                height: 1200px;
            }
            .grid-contentb {
                left: 22%;
                height: 600px;
                top:40px;
            }
            .content-grid {
                margin: 10% 0 0 0;
            }
            p#textointerioruno, p#textointeriordos, p#textointeriortres {
                margin: 55% 0 0 5%;
                font-size: 12px;
                line-height: 16px;
            }
            #interior-contenido301 .titleEx {
                margin: 15% 0 0 10%;
            }
            #wrapper p.close {
                top: 45%;
            }
            #wrapper p#link-2 {
                left: 2%;
            }
            #wrapper p#link-1 {
                top:10%;
            }
            #wrapper p#link-3 {
                top:22%;
            }
            #carousel div, #wrapper {
                height:1100px;
            }
            p#textodetailcinco, p#textodetailuno, p#textodetaildos, p#textodetailtres {
                margin: 61% 23%;
                font-size:12px;
                line-height: 18px;
            }
            .precios-content {
                padding: 0 15% 0 11%;
            }
            #wrapper-exterior p.close {
                top: 43%;
            }
            .first-section {
                height:835px;
            }
            .container-menu301 {
                display:none;
            }
            .car-home {
                margin: 25% 0 0 0;
            }
            div.leyenda {
                width: 28%;
                bottom: 29%;
            }
            div#iconsInfo {
                bottom:42%;
                width:60%;
            }
            div#info-tres301 {
                margin: 0;
            }
            #logo-footer {
                left:42%;
            }
            #moveprecios {
                display:none;
            }
            #precioscarousel {
                display: block;
            }
            .ofertabotoninterior {
                left:38%;
                bottom:10%;
            }
        }
        @media only screen and (min-width: 1024px) { 
            .grid-contenta {
                width: 100%;
                padding: 5% 18% 0 18%;
                height: 300px;
            }
            .grid-contentb {
                left: 22%;
                height: 600px;
            }
            #dieselComplemento {
                right:13%;
            }
        }
        @media only screen  and (max-width: 667px) {
            .amp-close-imagec {
                top:10px;
                right:10px;
            }
            #btn-promo {
                margin-top:90%;
                right: -5px;
            }
            #sidebarPromo { 
                width:100%;
            }
            .amp-close-imagepromo {
                top:20%;
                left: 0;
            }
            #imgMotorLT {
                margin-top: -80px;
                width: 20%;
                position:absolute;
            }
            div#info-dos div h3 {
                margin:0;
            }
            div#iconsInfo #info-dos amp-img.imgesp {
                margin-top:15%;
            }
            div#iconsInfo .container-items {
            width: 33.333%; 
            }
            input[type=radio], input[type=checkbox] { 
                margin: -5px 0 0 0;
            }
            #wrapper a#link-4 {
                width: 50px;
                height: 50px;
                left: 10%;
                top: 78%;
            }
            #wrapper a#link-5 {
                width: 50px;
                height: 50px;
                left: 40%;
                top: 10%;
            }
            #footer-container {
                height: 0;
            }
            #btn-link {
                text-align: center;
                margin: 0% 0;
            }
            .error {
                position: absolute;
                left: 0%;
                top: 82%;
                width: 100%;
            }
            div.content-form.sideleft {
                margin: 0;
                padding: 10px;
            }
            div.content-form {
                padding: 0 10px;
            }
            div.content-form.sideright {
            margin: 0%;
            padding: 0 10px;
            }
            p.campoladoc {
                margin: 0px;
            }
            #btn-form {
                margin: 15px 0;
            }
            div#info-tres301 amp-img {
                display: none;
            }
            #carousel-exterior div {
                height: auto;
            }
            p#textodetailcinco,
            p#textodetailuno,
            p#textodetaildos,
            p#textodetailtres {
                width: 100%;
                margin:20% 0;
                padding: 0 5%;
            }
            p#textointerioruno,
            p#textointeriordos,
            p#textointeriortres {
                width: 100%;
                margin: 15% -48%;
                padding: 0 5%;
            } 
            #wrapper p.close {
                top:2%;
                left:90%;
            }
            #wrapper-exterior p.close {
                top:5%;
                left:90%;
            }
            #galeria-desktop {
                display: none;
            }
            #galeria-mobile {
                display: block;
            }
            .header amp-img:nth-child(1) {
            margin: -2% 2%;
            }
            img#dieselComplemento {
                display: block;
            }
            #slideGaleria {
                display: none;
            }
            .contenido-diseno-interno {
                background-size: cover;
                background-attachment: scroll;
            }
            .ico-close-img {
                position: absolute;
                top: 10%;
            }
            img#detailimgdos{
                display: none;
            }
            img#detailinteriorunomobile{
                display: block;
            }
            img#detailinteriortresmobile{
                display: block;
            }
            img#detailinteriordosmobile{
                display: block;
            }
            img#detailinterioruno {
                display: none;
            }
            img#detailinteriortres {
                display: none;
            }
            img#detailinteriordos {
                display: none;
            }
            img#detailimgunomobile{
                display: block;
            }
            img#detailimgdosmobile{
                display: block;
            }
            img#detailimgtresmobile{
                display: block;
            }
            img#detailimgcincomobile{
                display: block;
            }
            img.dieselmobile {
                display: block;
                position: absolute;
                bottom:27%;
                right:0%;
                width: 25%;
            }
            .dieselimg {
                display: none;
            }
            #carpreciouno.preciobar img {
                width: 70%;
                margin: -5% 0 0 0;
            }
            .pruebademanejoboton {
                margin: -10% auto;
            }
            .ofertaboton {
                bottom: 14%;
                left: 29%;
            }
            .ofertabotoninterior {
                margin-left:auto;
                margin-right:auto;
                left:0;
                right:0;
                bottom:13%;
            }
            .grid-contentb {
                display: block;
                background: #000;
            }
            .galeria-content {
                height: auto;
                background: #000;
            }
            p.titlegal {
                letter-spacing: 1px;
                font-size: 32px;
            }
            .grid-contentb .columnb p {
                height: 50%;
            }
            div.columnc.col-4 p:nth-child(1),
            div.columnc.col-4 p:nth-child(2) {
                height: 50%;
            }
            .container-notfound p:nth-child(1) {
                padding: 50% 0;
            }
            div.close-popup {
                top:0%;
                left: 90%;
                z-index: 12;
            }
            .arrow-precios-mobilel {
                position: absolute;
                z-index: 8;
                top:55%;
                left: 5%;
                display: block;
            }
            .arrow-precios-mobiler {
                position: absolute;
                z-index: 8;
                top:55%;
                right: 5%;
                display: block;
            }
            #seccion-dos.main-content { 
                background:url(content/img/bg-2-mobile.jpg);
            }
            #seccion-cuatro .disenointerior { 
                background:url(content/img/bgchico-208.jpg);
            }
            #exterior-contenido301 {
                background:url(content/img/208-bg-2-mobile.jpg);
                background-size: 100%;
                height: 700px;
            }
            #seccion-cuatro301.main-content {
                background: black;
                height: 700px;
            }
            .promo-content208 {
                padding: 3% 10%;
            }
            .promo-content301 {
                padding: 3% 10%;
            }
            .container-menu301-mobile {
                width: 100%;
                height: 120%;
                background: #000000;
                color:white;
                position: fixed;
                z-index: 999;
            }
          .container-menu301-mobile {
            font-family: 'PeugeotFontLight', 'Montserrat', sans-serif;
          }
          .container-menu301-mobile ul {
            list-style-type: none;
            overflow: hidden;
            padding: 20%;
          }
          .container-menu301-mobile ul li {
            padding: 10px 0px;
            text-align: left;
          }
          .container-menu301-mobile ul li p {
            border: .5px solid white;
            width: 60px; 
            margin-left: 0%;
            text-align: left;
          }
          .container-menu301-mobile ul li a {
            font-size: 18px;
            color:white;
            text-decoration: none;
            text-align: left;
          }
          .container-menu301-mobile ul li a.active {
            padding-left: 0px;
            font-size: 13px;
            text-align: right;
          }
          .container-menu301-mobile ul li a:hover {
           padding-right: 40px;
          }
            .header {
                width: 100%;
            }
            .header amp-img:nth-child(2) {
                width: 8%;
                display: block;
                position: fixed;
                top: 3%;
                right: -18%;
                z-index: 18;
            }
            img#closemenu {
                width: 8%;
                display: block;
                position: fixed;
                top: 3%;
                right: 5%;
                z-index: 18;
            }
            .grid-contentb { 
                width: 100%;
                padding: 0 3%;
                margin: 20% -18% 0;
            }
            .grid-contenta {
                width: 100%;
                height: 100%;
                padding: 25% 5% 5% 5%;
            }
            .motobackground {
                background:url(content/img/bg-motor-mobile.jpg);
                height: 1700px;
                padding: 0%;
            }
            .preciobar {
                width: 100%;
                float: left;
                text-align: center;
                margin: 50% 0 0 0;
            }
            p.titlesedan {
                font-size:25px;
            }
            div.leyenda {
                width: 35%;
                bottom: 16%;
            }
            .no-lopagas {
                bottom: 21%;
                right: 3px;
            }
            .no-lopagas img {
                width: 80%;
            }
            .slidea {
                height: 50%;
            }  
            .slideb {
                height: 50%;
            }  
            .privacidadClass {
                font-size: 10px;
                bottom: 12%;
                cursor: pointer;
                left: 1%;
            }
            .popUp {
                width:100%;
                padding: 6%;
            }
            input[name=enviar] { 
                width: 100%;
                margin: -20% 0;
            }
            .cookiesms {	
                width:100%;
                height:120px;
            }
            #minititle {
                width: 2%;
                margin: 45% 0 0 0;
                position:absolute;
                left:0;
                right:0;
                margin-left:auto;
                margin-right:auto;
            }
            .title-initial {
                margin: 33% auto;
                display: block;
                margin-left: auto;
                margin-right: auto;
                position:absolute;
                left:0;
                right:0;
            }
            div#optionsIcons.container-buttons-options {
                bottom:29%;
            }
            div.leyenda {
                width: 29%;
                bottom: 21%;
            }
            #menu301 {
                display: none;
            }
            div.button-container {
                display: none;
            }
            .car-home {
                width: 100%;
                position: absolute;
                top: 20%;
                left: 0%;
                z-index: 0;
            }
            div#logo-footer {
                left: 30%;
            }
            div#logo-footer {
                left: 30%;
            }
            div#iconsInfo {
                bottom: 40%;
                left:0;
                width:100%;
                position:absolute;
            }
            div#info-dos {
                margin: 0 20%;
            }
            div#iconsInfo .container-items span {
                font-size: 10px;
            }
            div#info-tres301 {
                bottom: 22%;
                left: 5%;
            }
           .imgcar {
                float: left;
            }
            div#info-tres301.icons-content p.car-uno {
                width: 36px;
                height: 36px;
                float:left;
                margin:5px;
                -moz-border-radius: 50%;
                -webkit-border-radius: 50%;
                border-radius: 50%;
                background: white;
                border: 1px solid white;
             }
             div#info-tres301.icons-content p.car-dos {
                width: 36px;
                height: 36px;
                float:left;
                margin:5px;
                -moz-border-radius: 50%;
                -webkit-border-radius: 50%;
                border-radius: 50%;
                background: rgb(69, 69, 69);
                border: 1px solid white;
             }
             div#info-tres301.icons-content p.car-tres {
                width: 36px;
                height: 36px;
                float:left;
                margin:5px;
                -moz-border-radius: 50%;
                -webkit-border-radius: 50%;
                border-radius: 50%;
                background:gray;
                border: 1px solid white;
             }
             div#info-tres301.icons-content p.car-cuatro {
                width: 36px;
                height: 36px;
                float:left;
                margin:5px;
                -moz-border-radius: 50%;
                -webkit-border-radius: 50%;
                border-radius: 50%;
                background: black ;
                border: 1px solid white;
             }
             /*formulario*/
             #footer-container {
                 height: 0;
             }
             #btn-link {
                text-align: center;
                margin: -29% 0;
            }
            .error {
                position: absolute;
                left: 0%;
                top: 82%;
                width: 100%;
            }
             div.content-form.sideleft {
                 margin: 0;
                 padding: 10px;
             }
             div.content-form {
                padding: 0 10px;
             }
             div.content-form.sideright {
               margin: -3% 0 0 0;
               padding: 0 10px;
             }
             p.campoladoc {
                margin: 0px;
             }
             #btn-form {
                margin: 20% 0;
             }
             .btn-pruebamanejo {
                padding: 10px;
             }
            #exterior-contenido301 .titleEx {
                margin: 25% 0 10% 4%;
            }
            #exterior-contenido301 div #carexterior {
                margin: 20% 0 0 0;
            } 
            #wrapper-exterior {
                top:20%;
                height: 800px;
            }
            #wrapper-exterior p#tool-3 {
                width: 50px;
                height: 50px;
                left: 70%;
                margin-top:48%;
            }
            #wrapper-exterior p#tool-4 {
                width: 50px;
                height: 50px;
                left: 30%;
                margin-top:50%;
                border: 0;
            }
            #wrapper-exterior p#tool-5 {
                width: 50px;
                height: 50px;
                left: 55%;
                margin-top:38%;
                border: 0;
            }
            div.icono-motor p:nth-child(2) {
                font-size: 9px;
            }
            div.icono-motor p:nth-child(3) {
                font-size: 12px;
            }
            #wrapper-exterior p#tool-2 {
                width: 50px;
                height: 50px;
                left:55%;
                margin-top:47%;
            }
            p.titlesedan {
                font-size:28px;
                margin: 35% 0 0 0;
            }
            .precios-content {
                background:url(content/img/208-bgprecios-mobile.jpg);
                background-size: cover;
                padding: 0 5% 0 5%;
                height: 900;
                position: relative;
            }
            #wrapper-exterior p#tool-1 {
                width: 50px;
                height: 50px;
                left: 18%;
                margin-top:45%;
            }
            #carousel-exterior img {
                top: 0%;
                height: auto;
            }
            #wrapper-exterior a.close {
                top: 2%;
                left: 90%; 
            }
            .contenido-switch {
                left: 11%;
                top: 28%;
                font-size: 14px;
            }
            #interior-contenido301 .titleEx {
                margin: 20% 0 15% 10%;
                width: 50%;
            }
            #wrapper p#link-1 {
                width: 50px;
                height: 50px;
                left: 60%;
                top: 43%;
                border: 0;
            }
            #wrapper p#link-2 {
                width: 50px;
                height: 50px;
                left: 8%;
                top: 35%;
                border: 0;
            }
            #wrapper p#link-3 {
                width: 50px;
                height: 50px;
                left: 60%;
                top: 30%;
                border: 0;
            }
            #wrapper p#link-4 {
                width: 50px;
                height: 50px;
                left: 20%;
                top: 78%;
                border: 0;
            }
            #wrapper p#link-5 {
                width: 50px;
                height: 50px;
                left: 45%;
                top: 18%;
                border: 0;
            }
            #carousel img {
                top:0%;
            }
            #wrapper a.close {
                top: 5%;
            }
            #seccion-uno301 {
                background: url(content/img/208-bg-1-mobile.jpg);
                background-size: cover;
                height: 700px;
            }
            /*home*/
            div#header-container.header{
               display: none;
            }
            .content-modal h2 {
                color:black;
                position: absolute;
                top:18%;
                left: 5%;
                z-index: 12;
                font-size: 34px;
            }
            .content-modal p {
                color: black;
                position: absolute;
                top: 30%;
                left: 5%;
                right: 2%;
                z-index: 12;
                font-size: 18px;
                text-align: justify;
            }
            .content-modali h2 {
                top:18%;
                left: 5%;
                font-size: 34px;
            }
            .content-modali p {
                top:30%;
                left: 5%;
                font-size: 17px;
            }
            .background-thanks {
                background-image: url(../img/bg-gracias301-mobile.jpg);
                background-repeat: no-repeat;
                background-size: 100% 100%;
                height: 100%;
              }
              .background-notfound {
                background-image: url(../img/bg-404-mobile.jpg);
                background-repeat: no-repeat;
                background-size: 100% 100%;
                height: 100%;
              }
              .container-thanks {
                  width: 80%;
                  left: 10%;
              }
              .content-grid {
                width: 350px;
                height: auto;
                margin-top: -20%;
                margin-left:22%;
              }
              div#info-uno {
                margin: 0 0 0 17%;
            }
            div#info-dos {
                padding: 0 0 2% 0;
            }
            #optionsmenu {
                width: 500px;
                list-style-type: none;
            }
            #optionsmenu a {
                color:#fff;
            }
            .btn {
                margin: 10% 0 0 0; 
            }
        #sidebarMenu {
            background: #000;
        }
        #moveprecios {
            display: none;
        }
        #precioscarousel {
            display: block;
        }
        .carousel-cars {
            margin-left: auto;
            margin-right: auto;
        }
        .carousel-cars amp-img {
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .fichatecnica {
            bottom:3%;
        }
    }
        /*iPhone 5*/
        @media only screen 
            and (min-width : 320px) 
            and (max-width : 330px) {
                .wrapper-content{
                    display:block;
                } 
                #minititle {
                    width: 2%;
                    margin: 45% 0 0 0;
                    position:absolute;
                    left:0;
                    right:0;
                    margin-left:auto;
                    margin-right:auto;
                }
                .title-initial {
                    margin: 33% auto;
                    display: block;
                    margin-left: auto;
                    margin-right: auto;
                    position:absolute;
                    left:0;
                    right:0;
                }  
                .contenido-switch {
                    left:1%;
                }
                #wrapper-exterior p#tool-5 {
                    top:32%;
                }
                #wrapper-exterior p#tool-2 {
                    top: 40%;
                }
                #wrapper-exterior p#tool-3 {
                    top: 40%; 
                }
                #wrapper-exterior p#tool-1 {
                    top: 40%; 
                }
                #exterior-contenido301 {
                    height: 600px;
                }
                .ofertabotoninterior {
                    bottom: 16%;
                    left: 22%;
                    margin-left:auto;
                    margin-right:auto;
                    left:0;
                    right:0;
                }
                div#optionsIcons.container-buttons-options {
                    bottom: 25%;
                }
                div.leyenda {
                    bottom: 17%;
                }
                div#iconsInfo {
                    bottom:47%;
                }
                .content-popup {
                    width:96%;
                }
                .amp-close-image {
                    left:90%
                }
                .grid-contenta {
                    height:150%;
                }
        }
        @media screen and (min-width: 1920px) and (max-width: 3000px) {  
            .first-section {
                height:900px;
            }
            .container-menu301 {
                left:-2%;
            }
            .amp-close-imagepromo {
                left:95%;
            }
            #wrapper-exterior p#tool-3 {
                left: 63%;
                top: 52%;
            }
            #wrapper-exterior p#tool-4 {
                left: 40%;
                top: 52%;
            }
            #wrapper-exterior p#tool-2 {
                top: 58%;
            }
            p#textointerioruno, p#textointeriordos, p#textointeriortres {
                color: black;
                width: 45%;
                border: 0;
                margin: 12% 0 0 -7.5%; 
            }
            #wrapper p.close {
                top:22%;
                left:75%;
            }
            .motobackground {
                height:1400px;
            }
            p.titlesedan {
            margin: 8% 0 10% 0; }
            #btn-form {
                margin: -2% 0 ;
            }
            }
    }
</style>
</head>
<body>
<!-- SIDE BAR NOTICE PRIVACITY -->
<amp-sidebar id="sidebarMenu"
  layout="nodisplay"
  side="right">
  <amp-img class="amp-close-imageb"
    src="content/img/cancela.png"
    width="20"
    height="20"
    alt="close sidebar"
    on="tap:sidebarMenu.close"
    role="button"
    tabindex="0"></amp-img>
    <ul id="optionsmenu">
            <li class="btn">
                <a id="menu-uno301" href=""> PEUGEOT 208</a><p></p>
            </li>
            <li class="btn">
                <a id="menu-dos301" href="#seccion-cuatro301"> DISEÑO </a><p></p>
            </li>
            <li class="btn">
                <a id="menu-tres301" href="#informacion-tecnica"> MOTOR </a><p></p>
            </li>
            <li class="btn">
                <a id="menu-cuatro301" href="#seccion-precios"> VERSIONES </a><p></p>
            </li>
            <li class="btn">
                <a id="menu-cinco301" href="#Galeria"> GALERÍA </a><p></p>
            </li>
        </ul>
</amp-sidebar>
<!-- SIDE BAR Promociones -->
<amp-sidebar id="sidebarPromo"
  layout="nodisplay"
  side="right">
  <amp-img class="amp-close-imagepromo"
    src="content/img/cancela.png"
    width="15"
    height="15"
    alt="close sidebar"
    on="tap:sidebarPromo.close"
    role="button"
    tabindex="0"
    ></amp-img>
    <amp-img 
    src="content/img/promocion208.jpg"
    width="350"
    height="630"
    layout="responsive"
    ></amp-img>
    <a href="http://previewsandbox.com/peugeot/experiencia-peugeot/208-promocion"><amp-img class="pruebademanejopromo" src="content/img/CTA-pruebademanejo.png" width="181" height="40" layout="responsive" sizes="(min-width: 181px) 181px, 100vw" alt="Peugeot 208"> </amp-img></a>
    <div class="contenido-promocion">
        <p>Bono de hasta $40,000 al financiamiento para Gama 301 AM 2017, AM2018 y AM2019 y Gama 208 AM2017, AM2018 (excepto GT) desde 10% de enganche a 60 meses.</p>
        <p class="legales">Plan de financiamiento presentado y formalizado en exclusiva con PSA Finance México aplicable para Gama 301 AM 2017, AM2018 y AM2019 y Gama 208 AM2017, AM2018 (excepto GT), con enganche mínimo desde el 10% a 60 meses, en los distribuidores que tengan habilitado el beneficio de bonos de marca advance y que tengan habilitado el plan de financiamiento.  CAT promedio del 19.3% sin IVA. "El porcentaje de CAT (Costo Anual Total) expresado, ha sido calculado tomando en cuenta un vehículo, plazo, tasa de interés promedio y un monto a financiar específico, razón por la cual se hace de su conocimiento exclusivamente para efectos INFORMATIVOS y de COMPARACIÓN, por lo cual dicho porcentaje puede variar al momento de firma de su contrato de crédito, le sugerimos verificarlo.
        Todos los planes de financiamiento están sujetos a la aprobación de crédito correspondiente. 
        Vigencia de la promoción del 1 de julio al 31 de julio de 2018</p>
    </div>  
</amp-sidebar>
<!-- SIDE BAR NOTICE PRIVACITY -->
<amp-sidebar id="sidebar"
    layout="nodisplay"
    side="left">
    <amp-img class="amp-close-image"
    src="content/img/close.png"
    width="20"
    height="20"
    alt="close sidebar"
    on="tap:sidebar.close"
    role="button"
    tabindex="0"></amp-img>
    <div id="contienetextoprivacidad" class="content-popup">
            <h2>Aviso de Privacidad</h2>
            <p><b>PEUGEOT MÉXICO, S.A. DE C.V.</b> (en adelante, “PEUGEOT”), con domicilio en Av. Insurgentes Sur N° 1898,2ndo piso, Colonia Florida, C.P. 01030, Delegación Álvaro Obregón, Ciudad de México, México,
            mismo que señala para todos los efectos legales derivados del manejo de la información proporcionada a través del sitio web www.peugeot.com.mx, de nuestra aplicación ‘My Peugeot App’ y demás sitios web 
            propiedad de PEUGEOT (en adelante, conjuntamente “el SITIO”,  según corresponda), en cumplimiento con la ‘Ley Federal de Protección 
            de Datos Personales en Posesión de los Particulares’ (en adelante ”LA LEY”) y demás normatividad vigente y aplicable, pone a su disposición el presente:</p><br />

            <h2>AVISO DE PRIVACIDAD</h2>

            <p>Este Aviso de Privacidad rige el tratamiento que PEUGEOT dará a los datos personales que nos proporcione a través del SITIO, o como consecuencia del uso que haga de los mismos. 
                Por esto, le pedimos lea este Aviso de Privacidad con detenimiento, y si no está de acuerdo con parte o la totalidad de este documento, le pedimos no utilice el SITIO y evite proporcionar cualquier dato personal. 
                El hacer uso del SITIO y el proporcionar datos personales significa que ha leído y está de acuerdo con el tratamiento que se dará a sus datos personales de conformidad con lo establecido en este Aviso de Privacidad.
                Para PEUGEOT la confidencialidad y seguridad en el resguardo y tratamiento de sus datos personales es una prioridad; razón por la cual usted puede tener la certeza de que los mismos serán manejados en forma 
                confidencial y de acuerdo con la legislación conducente en vigor.</p>

            <h2>Datos que se recaban</h2>

            <p>Se entiende que los datos personales que usted proporciona en este SITIO, a través de los formatos de registro que PEUGEOT pone a su disposición, a través de ‘My Peugeot App’, 
            así como cuando nos envía un correo electrónico con preguntas o comentarios o nos contacta vía telefónica o por cualquier otro medio, son comunicados por el Titular de los datos, usted, de forma libre 
            y voluntaria (en adelante, “usted” o “el Titular”). A través del SITIO o derivado del uso que haga de los mismos, PEUGEOT podrá recabar datos personales que pertenezcan a la categoría de identificación y contacto. 
            El Titular declara que los Datos Personales de contacto y/o de identificación proporcionados en el SITIO (nombre, edad, sexo, ocupación, domicilio, correo electrónico, número telefónico
            (del domicilio y/o portátil /celular)) son veraces y comprobables, y acepta que será responsable de informar a “PEUGEOT” de cualquier modificación trascendental en los mismos.</p>

            <h2>Finalidades o usos de los datos personales</h2>

            <p>El uso y destino de la información por Ud. proporcionada en el SITIO estará limitado al cumplimiento de las siguientes finalidades necesarias: (i) proveerle el bien y/o a suministrarle el servicio, 
            ambos referentes a los productos de la Marca PEUGEOT y/o directamente relacionados (en adelante, “LOS PRODUCTOS”) (tales como el programar una cita, programar una prueba de manejo); 
            (ii) responder a sus consultas y/o proporcionarle la información que solicite,(iii) dar seguimiento a nuestra relación comercial, (iv) hacer efectivas las garantías de los PRODUCTOS, 
            (v) enviarle comunicaciones relacionadas con los PRODUCTOS que haya adquirido (vi) cumplir con obligaciones legales y requerimientos de autoridad competente, (vi) proteger o defender 
            los derechos y propiedad de PEUGEOT y de otras empresas del grupo, así como la información confidencial de éstas, (vii) mantener la seguridad del SITIO, de PEUGEOT y de los usuarios y clientes de PEUGEOT, 
            (viii) prevenir y/o cooperar en la investigación de conductas delictivas e ilícitas (ix) enviarle comunicados para proporcionarle la información de identificación y contacto de 
            distribuidores autorizados de PEUGEOT dentro del territorio nacional; o bien, para informarle quiénes han dejado de ser distribuidores autorizados, (x) enviarle un correo o mensaje confirmando su registro,
            o proporcionándole la opción de reestablecer su contraseña con relación a ‘My Peugeot App’ o cualquier perfil que cree en el SITIO, (xi) gestionar su solicitud con algún miembro de la red comercial PEUGEOT 
            (concesionarios, distribuidores, agentes).</p>

            <p>Asimismo, siempre que usted no se oponga, o si se suscribe a ciertos servicios proporcionados a través del SITIO, sus datos personales podrán ser utilizados para dar cumplimiento a las siguientes finalidades voluntarias 
            que no son necesarias pero nos permiten y facilitan brindarle una mejor atención: (i) realizar encuestas sobre nuestros productos y servicios prestados por la Red de Distribuidores “PEUGEOT”; 
            (ii) comunicarle nuestras promociones y eventos, mercadotecnia y publicidad; (iii) medición de índices de satisfacción del cliente y de la calidad de LOS PRODUCTOS, (iv) enviarle Newsletters de PEUGEOT, 
            (v) participar en promociones.</p>

            <p>En caso de que no desee que sus datos personales sean tratados para las finalidades voluntarias, desde este momento nos puede comunicar lo anterior, mediante el envío de una solicitud 
            en los términos que se señalan en el apartado “Derechos del Titular” de este Aviso de Privacidad.</p>

            <h2>Uso de tecnologías de seguimiento</h2>

            <p>Nuestro SITIO de Internet obtiene automáticamente información acerca de su conducta cuando navega por el SITIO, a través de "cookies". Una cookie es un pequeño archivo de texto que algunos sitios web 
            escriben en el disco duro de su computadora cuando los visita. Un archivo cookie se utiliza para rastrear las páginas que usted ha visitado, el tiempo que pasa en el SITIO, optimizar y personalizar la navegación, 
            pero la única información personal que una cookie puede contener es información que usted mismo suministra. Una cookie no puede leer datos de su disco duro, ni leer los archivos cookie creados por otros sitios.
            PEUGEOT utiliza cookies sólo para rastrear los patrones de tráfico del usuario cuando navega por ciertas páginas del SITIO. Si usted ha ajustado su navegador para que le avise antes de aceptar cookies, 
            recibirá el mensaje de advertencia con cada cookie. Usted puede rehusar recibir cookies, desactivándolos en su navegador. Si usted rehúsa las cookies, existe la posibilidad de que no tenga acceso a 
            ciertos servicios brindados por el sitio. Puede eliminar o desactivar cookies a través de la configuración de su navegador. Para cada navegador debe seguirse un procedimiento diferente; sin embargo, 
            a manera de ejemplo, si utiliza <b>Google Chrome</b>, deberá seguir esta ruta: Configuración -> Mostrar opciones avanzadas -> Privacidad -> Configuración de contenido. Para más información o para saber cómo deshabilitar 
            las cookies en otros navegadores, consulte el sitio: <b>www.allaboutcookies.org/es/.</b></p>

            <p>Tome en cuenta que algunas de estas tecnologías pueden transmitir directamente su información a terceros ajenos que se rigen por otro Aviso de Privacidad. Por ejemplo, los botones que llevan a diferentes 
            redes sociales transmiten la información directamente a estas empresas, por lo que recomendamos lea el Aviso de Privacidad de estas empresas, ya que PEUGEOT no es responsable del tratamiento de datos 
            personales que dichas empresas lleven a cabo.</p>

            <h2>Enlaces a otros sitios web</h2>

            <p>El SITIO puede contener enlaces a otros sitios que pueden ser de su interés pero que no pertenecen a PEUGEOT; por tanto, PEUGEOT no se responsabiliza por los procedimientos, ni mecanismos de seguridad de 
            aquellos otros sitios web a los cuales se puede acceder, ya que son independientes y ajenos a PEUGEOT; por esta razón, recomendamos la lectura detenida del Aviso de Privacidad de cada uno de ellos.</p>

            <p>El SITIO puede igualmente contener enlaces a otros sitios propiedad de PEUGEOT; sin embargo, puede ser que estos sitios tengan su propio Aviso de Privacidad, como en el caso de PEUGEOT ADVISOR, 
            por lo que le alentamos a consultar el Aviso de Privacidad disponible en cada sitio que visite.</p>

            <h2>Comunicaciones de Datos Personales</h2>

            <p>La información que ha decidido compartir con nosotros podrá ser comunicada dentro y fuera del territorio de los Estados Unidos Mexicanos por “PEUGEOT” a:</p>
            
            <table>
                <tr>
                    <th>Destinatario de los Datos Personales</th>
                    <th>Finalidad</th>
                </tr>
                <tr>
                    <td>
                    Red de distribuidores autorizados de PEUGEOT dentro del territorio nacional.
                    </td>
                    <td>
                    <p>Para que los distribuidores autorizados puedan prestarle servicios relacionados con productos de la marca PEUGEOT o 
                    proporcionarle el bien o producto que desee, así como para enviarle información sobre los productos y servicios que ofrecen. 
                    Le pedimos que revise también detenidamente los avisos de privacidad de dichos distribuidores, ya que ellos son los 
                    Responsables del tratamiento que hagan de sus datos personales.</p>
                    <p>Usted tiene derecho a oponerse a esta transferencia de datos. Si desea hacerlo, por favor envíenos su solicitud de 
                    acuerdo al procedimiento que se describe en la siguiente sección, “Derechos del Titular”.</p>
                    </td>
            </tr>
            <tr>
                <td>Autoridades competentes</td>
                <td><p>Para cumplir requerimientos de autoridades competentes y otras obligaciones legales, para salvaguardar el interés 
                público o la procuración o administración de justicia; o bien, para el reconocimiento, ejercicio o defensa de un derecho 
                en un proceso judicial, así como en los casos específicamente permitidos por LA LEY.</p></td>
            </tr>
            <tr>
                <td>Prospectos compradores o adquirentes</td>
                <td><p>En caso de fusión o adquisición, para que el prospecto adquirente o comprador analice la situación de PEUGEOT.</p></td>
            </tr>
            <tr>
                <td>Fusionante/ Adquirente</td>
                <td><p>Para que utilice los datos en la misma forma en que PEUGEOT los utilizaba; es decir, para la relación laboral.</p></td>
            </tr>
            </table>
            <br/>
            <br/>
                            
        <table>
            <tr>
                <td><p>A otras empresas del grupo de PEUGEOT, 
                filiales o subsidiarias, dentro de México o internacionalmente.</p></td>
                <td><p>Para el análisis de PEUGEOT, la administración 
                de la base de datos de clientes a nivel mundial.</p></td>
            </tr>
            <tr>
                <td>Socios de negocios</td>
                <td><p>[Encuestas, promociones, publicidad, recordatorios de servicio, 
                llamados para realizar ajustes en modelo, 
                comunicados con novedades de la marca en México y programas de fidelización]</p></td>
            </tr>
            </table>
            <br/>

            <h2>Derechos del Titular</h2>

            <p>El Titular de los datos personales, debidamente acreditado, en forma personal y/o a través de su representante legal, 
            podrá ejercer los derechos de acceso, rectificación, cancelación y oposición con respecto a sus datos personales. Asimismo, podrá limitar el uso o divulgación 
            de sus datos cuando lo considere conveniente o revocar el consentimiento que haya otorgado para el tratamiento de sus datos. Su solicitud para el ejercicio 
            de estos derechos deberá ser presentada ante el Departamento de Datos Personales de PEUGEOT en el domicilio aquí señalado, o por correo electrónico a la 
            dirección siguiente: contacto@peugeot.com. Tratándose de los requisitos de la solicitud, así como su tramitación y los plazos de respuesta, “PEUGEOT” y el Titular, 
            se regirán por lo dispuesto en “LA LEY”. Asimismo, si usted, desea dejar de recibir mensajes promocionales de nuestros productos y/o servicios, 
            puede solicitarlo a los mecanismos de contacto señalados en el presente párrafo o al teléfono 01 800 (52) PEUGEOT (7384968).</p>

            <p>Para mayor referencia, su solicitud deberá contener, al menos, la siguiente información:</p>

            <p> *Su nombre completo, domicilio y correo electrónico para poder comunicarle la respuesta a su solicitud.<br />
                *El documento que acredite su identidad (copia de identificación oficial) y, en su caso, personalidad de su representante (con copia de la identificación oficial que corresponda).<br />
                *La descripción clara y precisa de los datos personales respecto de los cuales busca ejercer alguno de los derechos.<br />
                *Cualquier documento o información que facilite la localización de sus datos personales.<br />
                *En caso de solicitar una rectificación de datos personales deberá indicar también las modificaciones a realizarse y aportar la documentación que sustente su petición.</p>

            <p>Le responderemos sobre la procedencia de su solicitud en un plazo máximo de 20 días hábiles contados a partir de su recepción y, de ser procedente, tendrá 15 días hábiles 
            para hacer efectivo el derecho que solicite. Si su solicitud está incompleta, al recibirla le solicitaremos la complete en el término de cinco días hábiles.</p>

            <p>El derecho de acceso se dará por cumplido cuando se ponga a disposición del Titular, en el domicilio referido en el párrafo anterior, los datos personales, o bien, 
            mediante la expedición de copias simples, documentos electrónicos o cualquier otro medio que, en su momento, determine PEUGEOT.</p>

            <p>Para más información sobre el procedimiento de ejercicio de derechos, envíenos un correo a <b>contacto@peugeot.com.</b></p>

            <h2>Medidas de Seguridad</h2>

            <p>PEUGEOT utiliza tecnología de vanguardia y medidas de seguridad administrativas, técnicas y físicas razonables y suficientes 
            para proteger sus datos personales contra daño, pérdida, alteración, destrucción o el uso, acceso o tratamiento no autorizados.</p>

            <h2>Modificaciones al Aviso de Privacidad</h2>

            <p>En caso de modificaciones futuras al presente aviso, las mismas le serán comunicadas por “PEUGEOT” vía correo electrónico 
            a la dirección que ha proporcionado en los formatos de registro del SITIO” y/o a través de una publicación en su página 
            corporativa <b> www.peugeot.com.mx</b>; o bien, por cualquier otro medio de comunicación oral, impreso o electrónico que PEUGEOT determine 
            para tal efecto.</p>

            <h2>Uso de cookies</h2>

            <p>Peugeot México, S.A. de C.V. utiliza cookies con la finalidad de analizar el uso de la página web,
            mostrarle publicidad personalizada y obtener estadísticas. Las cookies son pequeños archivos de texto que pueden recabar datos personales
            y otra información, como su sistema operativo, su navegador, páginas que visita, tiempo que pasa en nuestro sitio, su dirección de
            Protocolo de Internet, sus preferencias. Las cookies pueden deshabilitarse siguiendo las instrucciones de su navegador. 
            Para más información sobre estas tecnologías.</p>

            <p>Fecha última modificación: 4 de octubre de 2017.</p>
        </div>
</amp-sidebar>
  <!-- HEADER -->
  <header>
        <div id="header-content" class="header">
            <a href="http://previewsandbox.com/peugeot/experiencia-peugeot/">
            <amp-img src="content/img/logo.png" width="80" height="80" layout="fixed" alt=""></amp-img>
            </a>
            <amp-img id="mobilemenu" role="button" tabindex="0" class="menu-ico-mobile" src="content/img/menu-ico.png" width="30" height="30" layout="fixed" on="tap:sidebarMenu.toggle"></amp-img>
        </div>
  </header>
<!-- BODY -->
  <main id="content" role="main" class="main">
<!-- JSON's-->
        <amp-state id="cars">
                <script type="application/json">
                  {
                    "currentCar": "blanco",
                    "blanco": {
                      "imageUrl": "content/img/208-blanco.png"
                    },
                    "gris": {
                      "imageUrl": "content/img/208-gris.png"
                    },
                    "negro": {
                        "imageUrl": "content/img/208-negro.png"
                    }
                  }
                </script>
        </amp-state>
<!-- PROMOCIONES  -->
    <div id="btn-promo">
    <amp-img src="content/img/bt-promocion.png" media="(min-width: 650px)"  width="95" height="97" layout="responsive" sizes="(min-width: 95px) 95px, 100vw" role="button" tabindex="0" on="tap:sidebarPromo.toggle"></amp-img>
    <amp-img src="content/img/bt-promocion.png" media="(max-width: 649px)"  width="95" height="97" layout="responsive" sizes="(min-width: 95px) 95px, 100vw" role="button" tabindex="0" on="tap:sidebarPromo.toggle"></amp-img>
    </div>

<!-- BOTON 208  -->
<a href="http://previewsandbox.com/peugeot/experiencia-peugeot/301" target="_self">
    <div id="btnCar" class="button-container">
        <p class="parrafo-buttona w3-animate-left">DESCUBRE</p>
        <p class="menu-parrafo arrowLeft"></p>
        <p class="parrafo-button w3-animate-left">PEUGEOT 301</p>
    </div>
</a>
<!-- MENU  -->
        <div id="menu301" class="container-menu301">
                <ul id="optionsmenu">
                    <li class="btn">
                        <a id="menu-uno301" href=""> PEUGEOT 208</a><p></p>
                    </li>
                    <li class="btn">
                        <a id="menu-dos301" href="#seccion-cuatro301"> DISEÑO </a><p></p>
                    </li>
                    <li class="btn">
                        <a id="menu-tres301" href="#informacion-tecnica"> MOTOR </a><p></p>
                    </li>
                    <li class="btn">
                        <a id="menu-cuatro301" href="#seccion-precios"> VERSIONES </a><p></p>
                    </li>
                    <li class="btn">
                        <a id="menu-cinco301" href="#Galeria"> GALERÍA </a><p></p>
                    </li>
                </ul>
        </div>
<!-- FIRST SECTION -->
<div id="window-that-crops-stuff" class="box">
    <section id="seccion-uno301" class="section-wrapper col-12 first-section">
        <amp-position-observer on="enter:slideTransitionFirst.start; exit:slideTransitionFirst.start,slideTransitionFirst.reverse" intersection-ratios="0.5" layout="nodisplay"></amp-position-observer>
        <amp-lightbox id="my-video" layout="nodisplay">
                <div class="lightboxvideo" on="tap:my-video.close" role="button" tabindex="0">
                <amp-img class="amp-close-imagec" src="content/img/cancela.png" width="20" height="20"  alt="close sidebar" on="tap:my-video.close" role="button" tabindex="0"></amp-img>
                        <amp-youtube width="480"
                            height="240"
                            layout="responsive"
                             data-videoid="NLfE_r7SsD4"
                            autoplay>
                      </amp-youtube>
                </div>
        </amp-lightbox>    
        <div class="wrapper-content col-12">
                <amp-img id="titleMain" src="content/img/title208.png" media="(min-width: 650px)" width="400" height="50" sizes="(min-width: 651px) 551px, 100vw" layout="responsive" class="title-initial fixed" alt="Peugeot 208"></amp-img>
                <amp-img id="titleMain" src="content/img/title208.png" media="(max-width: 649px)" width="200" height="20" sizes="(min-width: 300px) 300px, 100vw" layout="responsive" class="title-initial fixed" alt="Peugeot 208"></amp-img>
                <amp-img id="minititle" src="content/img/minititle208.png" media="(min-width: 650px)"  width="500" height="25" layout="responsive"  sizes="(min-width: 500px) 500px, 100vw"  class="title-min fixed" alt="Peugeot 208"></amp-img>
                <amp-img id="minititle" src="content/img/minititle208.png" media="(max-width: 649px)"  width="500" height="25" layout="responsive"  sizes="(min-width: 250px) 250px, 100vw"  class="title-min fixed" alt="Peugeot 208"></amp-img>
                <amp-img id="carBig" src="content/img/208-blanco.png" [src]="cars[cars.currentCar].imageUrl" width="400" height="220" layout="responsive" sizes="(min-width: 700px) 700px, 100vw" class="car-home fixed" alt="Peugeot 208"></amp-img>
                <amp-img id="dieselComplemento" src="content/img/208-rendimiento.png" width="131" height="31" layout="intrinsic" class="dieselimg fixed" alt="Peugeot 208"></amp-img>
                <amp-img id="imgMotorLT" src="content/img/motorturboLT.png" width="131" height="38" layout="intrinsic" class="img-motor-turbo fixed" alt="Peugeot 208"></amp-img>
            </div>
            <div class="no-lopagas">
                    <amp-img src="content/img/Nolopagas.png" width="124" height="27" layout="intrinsic" alt="No lo pagas Peugeot 301"></amp-img>
            </div>
            <div id="optionsIcons" class="col-6 container-buttons-options">
                    <div role="button" class="ico" on="tap:info-uno.toggleVisibility, info-dos.hide, info-tres301.hide" tabindex="0">
                        <amp-img src="content/img/208-ico-precio.gif" width="40" height="40" layout="responsive" sizes="(min-width: 40px) 40px, 100vw" alt="Peugeot 208"></amp-img>
                    </div>
                    <div role="button" class="ico" on="tap:info-dos.toggleVisibility, info-uno.hide, info-tres301.hide" tabindex="0">
                        <amp-img src="content/img/208-ico-personaliza.gif" width="40" height="40" layout="responsive" sizes="(min-width: 40px) 40px, 100vw" alt="Peugeot 208"></amp-img>
                    </div>
                    <div role="button" class="ico" on="tap:info-tres301.toggleVisibility, info-dos.hide, info-uno.hide" tabindex="0">
                        <amp-img src="content/img/208-ico-colores.gif" width="40" height="40" layout="responsive" sizes="(min-width: 40px) 40px, 100vw" alt="Peugeot 208"></amp-img>
                    </div>
                    <div role="button" class="ico" on="tap:my-video" tabindex="4">
                        <amp-img src="content/img/208-ico-play.gif" width="40" height="40" layout="responsive" sizes="(min-width: 40px) 40px, 100vw" alt="Peugeot 208"></amp-img>
                    </div>       
            </div>
            <div class="leyenda">
                    * Precio sujeto a cambio sin previo aviso. 
                    Imágenes de carácter ilustrativo, consulta disponibilidad, 
                    precios y aplicación con tu distribuidor autorizado.
            </div>
            <div role="button" tabindex="1" class="privacidadClass" on="tap:sidebar.toggle">AVISO DE PRIVACIDAD</div>
            <div id="iconsInfo">
                <div id="info-uno" class="icons-content w3-animate-leftcar" hidden>
                    <p>A partir de</p>
                    <p>$286 900</p>
                    <p>*Impuestos incluidos</p>
                </div>
                <div id="info-dos" class="icons-content" hidden>
                    <div class="container-items">
                            <h3>Rin Titan Negros</h3>
                            <amp-img src='content/img/rin-titanenegros.png' media="(min-width: 650px)" width="80" height="80" layout="responsive" sizes="(min-width: 80px) 80px, 100vw" alt="Peugeot 208"></amp-img>
                            <amp-img src='content/img/rin-titanenegros.png' media="(max-width: 649px)" width="80" height="80" layout="responsive" sizes="(min-width: 40px) 40px, 100vw" alt="Peugeot 208"></amp-img>
                    </div>
                    <div class="container-items">
                            <h3>Yellow Lime</h3>
                            <amp-img src='content/img/yellowlime-detail.png' media="(min-width: 650px)" width="80" height="80" layout="responsive" sizes="(min-width: 80px) 80px, 100vw" alt="Peugeot 208" class="imgesp"></amp-img>
                            <amp-img src='content/img/yellowlime-detail.png' media="(max-width: 649px)" width="80" height="80" layout="responsive" sizes="(min-width: 40px) 40px, 100vw" alt="Peugeot 208" class="imgesp"></amp-img>
                    </div>
                    <div class="container-items">
                            <h3>Menthol</h3>
                            <amp-img src='content/img/menthol-detail.png' media="(min-width: 650px)" width="80" height="80" layout="responsive" sizes="(min-width: 80px) 80px, 100vw" alt="Peugeot 208" class="imgesp"></amp-img>
                            <amp-img src='content/img/menthol-detail.png' media="(max-width: 649px)" width="80" height="80" layout="responsive" sizes="(min-width: 40px) 40px, 100vw" alt="Peugeot 208" class="imgesp"></amp-img>
                    </div>
                </div>
                <div id="info-tres301" class="icons-content w3-animate-leftcar"  hidden>
                        <amp-img role="button" tabindex="0" src="content/img/208-blanco-ch.png" on="tap:AMP.setState({cars: {currentCar: 'blanco'}})" width="144" height="80" layout="responsive" sizes="(min-width: 144px) 144px, 100vw"  alt="Peugeot 208" class="imgcar"></amp-img>
                        <amp-img role="button" tabindex="1" src="content/img/208-gris-ch.png" on="tap:AMP.setState({cars: {currentCar: 'gris'}})" width="144" height="80" layout="responsive" sizes="(min-width: 144px) 144px, 100vw" alt="Peugeot 208" class="imgcar"></amp-img>
                        <amp-img role="button" tabindex="2" src="content/img/208-negro-ch.png" on="tap:AMP.setState({cars: {currentCar: 'negro'}})"  width="144" height="80" layout="responsive"  sizes="(min-width: 144px) 144px, 100vw" alt="Peugeot 208" class="imgcar"></amp-img>
                        <p role="button" tabindex="0" class="car-uno" on="tap:AMP.setState({cars: {currentCar: 'blanco'}})"></p>
                        <p role="button" tabindex="1" class="car-tres" on="tap:AMP.setState({cars: {currentCar: 'gris'}})"></p>
                        <p role="button" tabindex="2" class="car-dos" on="tap:AMP.setState({cars: {currentCar: 'negro'}})"></p>
                </div>
<!-- ANIMATIONS -->
<amp-animation id="slideTransitionFirst" layout="nodisplay">
        <script type="application/json">
          {
            "duration": "600ms",
            "fill": "both",
            "easing": "ease-out",
            "iterations": "1",
            "animations": [
            {
              "selector": "#carBig",
              "keyframes": [
              {
                  "transform": "translateX(100%)"
                },
                {
                  "transform": "translateX(0)"
                }
              ]
            },
            {
                "selector": "#dieselComplemento",
                "keyframes": [{
                    "transform": "translateX(100%)"
                  },
                  {
                    "transform": "translateX(0)"
                  }
                ]
              },
              {
                "selector": "#minititle",
                "keyframes": [{
                    "transform": "translateX(-100%)"
                  },
                  {
                    "transform": "translateX(0%)"
                  }
                ]
              },
              {
                "selector": "#titleMain",
                "keyframes": [{
                    "transform": "translateY(-200%)"
                  },
                  {
                    "transform": "translateY(0%)"
                  }
                ]
              }
            ]
          }
        </script>
</amp-animation>
    </section>
<!-- SECOND SECTION -->
<section id="seccion-cuatro301" class="col-12 main-content">
    <div id="interior-contenido301" class="contenido-diseno-interno col-12">
        <p class="titleEx">DISEÑO <br> EMOCIONANTE</p>
        <div id="diseno-interior" class="col-12">
                <div id="wrapper">
                        <div id="carousel">
                            <div id="overview">
                                <p role="button" tabindex="0" on="tap:detailinterior-1" id="link-1" class="w3-animate-zoom"></p>
                                <p role="button" tabindex="1" on="tap:detailinterior-2" id="link-2" class="w3-animate-zoom"></p>
                                <p role="button" tabindex="2" on="tap:detailinterior-3" id="link-3" class="w3-animate-zoom"></p>
                                <p role="button" tabindex="2" on="tap:detailinterior-4" id="link-4" class="w3-animate-zoom"></p>
                            </div>
                            <amp-lightbox id="detailinterior-1" layout="nodisplay">
                                <div class="lightbox" on="tap:detailinterior-1.close" role="button" tabindex="0">
                                    <p class="close">X</p>
                                    <amp-img id="detailinterioruno" src="content/img/int-h3.png" media="(min-width: 650px)"  width="1480" height="580" layout="responsive" sizes="(min-width: 1480px) 1480px, 100vw" alt="i-Cockpit Peugeot 208" class="w3-animate-leftcar"></amp-img>
                                    <amp-img id="detailinteriorunomobile" src="content/img/int-h3m.jpg" media="(max-width: 649px)" width="640" height="1280" sizes="(min-width: 640px) 640px, 100vw" layout="responsive" alt="i-Cockpit Peugeot 208" class="w3-animate-leftcar"></amp-img>
                                    <p id="textointerioruno">
                                        <span>i-Cockpit</span><br>
                                        El PEUGEOT 208 presenta un innovador diseño interior: el Peugeot i-Cockpit®, el cual proporciona una experiencia de conducción intuitiva, ágil y segura. Sistema avanzado manos libres: Mediante tecnología Bluetooth:registered: con la que se puede realizar llamadas con alta calidad de sonido y accesando a los contactos de tu celular sin guardarlos en el auto.<br>
                                        Conexión de dispositivos de audio: Sus entradas USB (iPhone®/ iPad® ready), AUX y el streaming mediante Bluetooth:registered: permiten escuchar audio disponible en cualquier dispositivo digital.</p>
                                </div>
                            </amp-lightbox>
                            <amp-lightbox id="detailinterior-2" layout="nodisplay">
                                <div class="lightbox" on="tap:detailinterior-2.close" role="button" tabindex="1">
                                    <p class="close">X</p>
                                    <amp-img id="detailinteriordos" src="content/img/int-h2.png" media="(min-width: 650px)" width="1480" height="580" layout="responsive" sizes="(min-width: 1480px) 1480px, 100vw" alt="Volante Peugeot 208" class="w3-animate-leftcar"></amp-img>
                                    <amp-img id="detailinteriordosmobile" src="content/img/int-h2m.jpg" media="(max-width: 649px)" width="640" height="1280" sizes="(min-width: 640px) 640px, 100vw" layout="responsive" alt="Volante Peugeot 208" class="w3-animate-leftcar"></amp-img>
                                    <p id="textointeriordos">
                                        <span>Vibrante estabilidad</span><br>
                                        Se trata de un volante compacto ajustable que se encuentra más abajo que un volante tradicional para reducir la fatiga en el conductor, acompañado de una pantalla táctil multifunción de 7 pulgadas que pone a tu disposición todas las funcionalidades del Nuevo 208.</p>  
                                </div>
                            </amp-lightbox>
                            <amp-lightbox id="detailinterior-3" layout="nodisplay">
                                <div class="lightbox" on="tap:detailinterior-3.close" role="button" tabindex="2">
                                    <p class="close">X</p>
                                    <amp-img id="detailinteriortres" src="content/img/int-h1.png" media="(min-width: 650px)"  width="1280" height="580" layout="responsive" sizes="(min-width: 1480px) 1480px, 100vw" alt="Peugeot 208" class="w3-animate-leftcar"></amp-img>
                                    <amp-img id="detailinteriortresmobile" src="content/img/int-h1m.jpg" media="(max-width: 649px)" width="640" height="1280" sizes="(min-width: 640px) 6400px, 100vw" layout="responsive" alt="Peugeot 208" class="w3-animate-leftcar"></amp-img>
                                    <p id="textointeriortres">
                                        <span>Seducción que atrapa</span><br>
                                        El PEUGEOT 208 presenta un innovador diseño interior: el Peugeot i-Cockpit®, el cual proporciona una experiencia de conducción intuitiva, ágil y segura. Se trata de un volante compacto ajustable que se encuentra más abajo que un volante tradicional para reducir la fatiga en el conductor, acompañado de una precisa dirección con maniobrabilidad mejorada y una pantalla táctil multifunción de 7 pulgadas que pone a tu disposición todas las funcionalidades del Nuevo 208. Además, el panel elevado de instrumentos te permite acceder a toda la información sin apartar la vista del camino.</p>  
                                </div>
                            </amp-lightbox>
                            <amp-lightbox id="detailinterior-4" layout="nodisplay">
                                <div class="lightbox" on="tap:detailinterior-4.close" role="button" tabindex="2">
                                    <p class="close">X</p>
                                    <amp-img id="detailinteriortres" src="content/img/int-h5.png" media="(min-width: 650px)"  width="1280" height="580" layout="responsive" sizes="(min-width: 1480px) 1480px, 100vw" alt="Peugeot 208"></amp-img>
                                    <amp-img id="detailinteriortresmobile" src="content/img/int-h5m.jpg" media="(max-width: 649px)" width="640" height="1280" sizes="(min-width: 640px) 6400px, 100vw" layout="responsive" alt="Peugeot 208"></amp-img>
                                    <p id="textointeriortres">
                                        <span>Seguridad y confort</span><br>
                                        El asiento deportivo y el aire acondicionado automático son elementos de diseño que te brindan el confort necesario para tu viaje, además las bolsas de aire interiores, te protegen en el camino.</p>  
                                </div>
                            </amp-lightbox>
                        </div>
                    </div>
        </div>
    </div>
    <div id="exterior-contenido301" class="backgroundexterior col-12">
        <amp-position-observer on="enter:slideTransition.start; exit:slideTransition.start,slideTransition.reverse" intersection-ratios="0.5" layout="nodisplay"></amp-position-observer>
            <p id="titlediseno" class="titleEx">DISEÑO <br> EMOCIONANTE</p>
        <div class="contenido-diseno col-12">
            <amp-img id="carexterior" src="content/img/208-negro.png" width="850" height="470" layout="responsive" sizes="(min-width: 850px) 850px, 100vw" alt="Peugeot 208"></amp-img>
        <div id="wrapper-exterior">
            <div id="carousel-exterior">
                <div id="overview-exterior">
                    <p role="button" id="tool-1" tabindex="0" on="tap:detail-farostraseros" class="w3-animate-zoom"></p>
                    <p role="button" id="tool-2" tabindex="1" on="tap:detail-parrilla" class="w3-animate-zoom"></p>
                    <p role="button" id="tool-3" tabindex="2" on="tap:detail-carroceria" class="w3-animate-zoom"></p>
                    <p role="button" id="tool-4" tabindex="2" on="tap:detail-rendimiento" class="w3-animate-zoom"></p>
                </div>
                <amp-lightbox id="detail-farostraseros" layout="nodisplay">
                    <div class="lightbox" on="tap:detail-farostraseros.close" role="button" tabindex="0">
                    <amp-img id="detailimguno" src="content/img/ext-h1.png" media="(min-width: 650px)" width="1580" height="580" layout="responsive" sizes="(min-width: 1480px) 1480px, 100vw" alt="Faros Traseros Peugeot 301" class="w3-animate-leftcar"></amp-img>
                    <amp-img  src="content/img/ext-h1m.jpg" media="(max-width: 649px)" width="640" height="1280" sizes="(min-width: 640px) 640px, 100vw" layout="responsive" alt="Faros Traseros" class="w3-animate-leftcar"></amp-img>
                    <p class="close">X</p>
                    <p id="textodetailuno">
                        <span>Visibilidad y Estilo</span><br>
                        El nuevo PEUGEOT 208 ofrece una nueva gama de colores constituida por 3 nuevos colores que resaltan su diseño moderno y seductor, permitiendo adaptarse a todo tipo de gustos y personalidades. Su acabado vivo y brillante se expresa plenamente bajo la acción de la luz y realza tanto al vehículo como a su conductor.<br>
                        Cuenta con faros de LED, delanteros y traseros de alta tecnología, se vuelven la firma del Peugeot 208.</p>
                </div>
                </amp-lightbox>
                <amp-lightbox id="detail-parrilla" layout="nodisplay">
                    <div class="lightbox" on="tap:detail-parrilla.close" role="button" tabindex="0">
                    <amp-img id="detailimgdos" src="content/img/ext-h4.png" media="(min-width: 650px)" width="1580" height="580" layout="responsive" sizes="(min-width: 1480px) 1480px, 100vw" alt="Parrilla Peugeot 301" class="w3-animate-leftcar"></amp-img>
                    <amp-img  src="content/img/ext-h4m.jpg" media="(max-width: 649px)" width="640" height="1280" sizes="(min-width: 640px) 640px, 100vw" layout="responsive" alt="Parrilla Peugeot 301" class="w3-animate-leftcar"></amp-img>
                    <p class="close">X</p>
                    <p id="textodetaildos">
                        <span>Emoción y Diseño</span><br>
                        El Peugeot 208 nos presenta elementos de diseño visibles, como los detalles cromados en la carrocería que le imprimen un estilo único. En ella, se dibujan líneas precisas y delicadas que surgen desde su amplia parrilla, la cual se integra totalmente a sus faros bi-tono LED de gama alta. Todos estos elementos le proporcionan al nuevo 208 un inconfundible aire felino.</p>   
                    </div>
                </amp-lightbox>
                <amp-lightbox id="detail-carroceria" layout="nodisplay">
                    <div class="lightbox" on="tap:detail-carroceria.close" role="button" tabindex="0">
                    <amp-img id="detailimgtres" src="content/img/ext-h2.png" media="(min-width: 650px)"  width="1580" height="580" layout="responsive" sizes="(min-width: 1480px) 1480px, 100vw" alt="Carroceria Peugeot 208" class="w3-animate-leftcar"></amp-img>
                    <amp-img src="content/img/ext-h2m.jpg" media="(max-width: 649px)" width="640" height="1280" sizes="(min-width: 640px) 640px, 100vw" layout="responsive" alt="Carroceria Peugeot 208" class="w3-animate-leftcar"></amp-img>
                    <p class="close">X</p>
                    <p id="textodetailtres">
                        <span>Identidad Luminosa</span><br>
                        El nuevo diseño de las luces traseras 3D LED que sorprenden y cautivan a primera vista, proporcionando un acabado rojo opalino, homogéneo y un efecto 3D, que los hacen una joya tecnológica, las ópticas del nuevo PEUGEOT 208 incorporan el efecto de garras en 3D que forman parte del estilo Peugeot.</p>
                    </div>
                </amp-lightbox>
                <amp-lightbox id="detail-rendimiento" layout="nodisplay">
                    <div class="lightbox" on="tap:detail-rendimiento.close" role="button" tabindex="0">
                    <amp-img id="detailimgtres" src="content/img/int-h6.png" media="(min-width: 650px)"  width="1580" height="580" layout="responsive" sizes="(min-width: 1480px) 1480px, 100vw" alt="Carroceria Peugeot 208" class="w3-animate-leftcar"></amp-img>
                    <amp-img src="content/img/int-h6m.jpg" media="(max-width: 649px)" width="640" height="1280" sizes="(min-width: 640px) 640px, 100vw" layout="responsive" alt="Carroceria Peugeot 208" class="w3-animate-leftcar"></amp-img>
                    <p class="close">X</p>
                    <p id="textodetailtres">
                        <span>Rendimiento total</span><br>
                        El consumo de energía es una de las prestaciones que hacen del nuevo Peugeot 208 un auto que se adapta a todas las necesidades de conducción.<br>
                        Peugeot México ha introducido una nueva tecnología en motores denominada PureTech, que gracias a todas sus prestaciones ha sido galardonado como el mejor motor del año en 2015, 2016 y 2017 en su categoría.</p>
                    </div>
                </amp-lightbox>
            </div>
        </div>
    </div>
</div>
    <div id="contenidoSwitch" class="contenido-switch">
            <span role="button" tabindex="0" onclick="clickSwitch('exterior')">EXTERIOR</span>
        <label class="switch">
            <input id="checkSlide" type="checkbox" onclick="handleSwitch()">
            <span id="slideround" class="slider round"></span>
        </label>
            <span role="button" tabindex="1" onclick="clickSwitch('interior')">INTERIOR</span>
    </div> 
<!-- ANIMATIONS -->
<amp-animation id="slideTransition" layout="nodisplay">
        <script type="application/json">
          {
            "duration": "800ms",
            "fill": "both",
            "easing": "ease-out",
            "iterations": "1",
            "animations": [
            {
              "selector": "#carexterior",
              "keyframes": [{
                  "transform": "translateX(100%)"
                },
                {
                  "transform": "translateX(0)"
                }
              ]
            },
            {
                "selector": "#titlediseno",
                "keyframes": [{
                    "transform": "translateY(700%)"
                  },
                  {
                    "transform": "translateY(0%)"
                  }
                ]
              }
            ]
          }
        </script>
</amp-animation>
</section>
<!-- THIRD SECTION -->
<section id="informacion-tecnica" class="motobackground col-12">
    <amp-position-observer on="enter:slideMotor.start; exit:slideMotor.start,slideMotor.reverse" intersection-ratios="0.5" layout="nodisplay"></amp-position-observer>
    <div id="des" class="motor-gasolina col-6">
        <p id="titlemotor">NUEVO <strong>PureTech</strong> GASOLINA</p>
        <amp-img class="motor-engine" src="content/img/logoEOTY_2018.png" width="200" height="208" layout="responsive" sizes="(min-width: 80px) 80px, 100vw" alt="Motor PureTech Gasolina"></amp-img>
        <amp-img id="imgmotor" src="content/img/motor-16vti.png" width="209" height="286" layout="responsive" sizes="(min-width: 209px) 209px, 100vw" alt="Motor PureTech Gasolina"></amp-img>
        <p class="des-motor">Peugeot trae a México la nueva línea de motores PureTech que ha recibido el titulo del mejor motor del año consecutivamente desde 2015. Presenta una combinación de eficiencia y confort de conducción. El motor PureTech logra convertir la conducción en emoción.</p>
        <div class="clearfix"></div>
        <p class="subtitlemotor">CONSUMO</p>
        <div id="icomotor" class="icono-motor iconomove"> 
                <amp-img class="iconosm" src="content/img/ciudad-ico.png" width="37" height="28" layout="responsive" sizes="(min-width: 37px) 37px, 100vw" alt="motor 301"></amp-img>
                <p>CIUDAD</p>
                <p>17 KM/LT</p>
            </div>
            <div id="icomotordos" class="icono-motor iconomove"> 
                <amp-img class="iconosm" src="content/img/carretera-ico.png" width="37" height="28" layout="responsive" sizes="(min-width: 37px) 37px, 100vw"  alt="motor 301"></amp-img>
                <p>CARRETERA</p>
                <p>25.5 KM/LT</p>
            </div>
            <div id="icomotortres" class="icono-motor iconomove">
                <amp-img class="iconosm" src="content/img/combi-ico.png" width="37" height="28" layout="responsive" sizes="(min-width: 37px) 37px, 100vw" alt="motor 301"></amp-img>
                <p>COMBINADO</p>
                <p>21.7 KM/LT</p>
            </div>
            <div id="icomotorcuatro" class="icono-motor iconomove">
                <amp-img class="iconosm" src="content/img/CO2-ico.png" width="37" height="28" layout="responsive" sizes="(min-width: 37px) 37px, 100vw" alt="motor 301"></amp-img>
                <p>CO2</p>
                <p>B/104</p>
            </div>
    </div>
    <div id="desb" class="motor-diesel col-6">
        <p class="subtitlemotor">MEJORA PERFORMANCE</p>
        <p class="des-motor">Ya sea en la ciudad o a nivel del mar, su desempeño será siempre del más alto nivel, gracias a su potencia y a la tecnología HDI (High diesel injection).</p>
        <br><br>
        <p class="subtitlemotor">TORQUE BEST IN CLASS</p>
        <p class="des-motor">La sensación de manejo se vuelve incomparable gracias a los 205Nm ( 151Lb ft ) que este motor ofrece.</p>
        <br><br>
        <p class="subtitlemotor">LÍDER EN RENDIMIENTO</p>
        <p class="des-motor">El motor Puretech tiene el mejor rendimiento en combustible del mercado, gracias al consumo de hasta 25.6km/l.</p>
        <br>
        <p class="subtitlemotor">TRANSMISIÓN A ELEGIR</p>
        <p class="des-motor">Disponible con transmisión Manual para la versión Allure, y con transmisión Manual o Automática para la versión Allure Pack.</p>
        <a id="descarga" href="content/img/FT_208.pdf" download="FichaTécnica208" ><amp-img class="fichatecnica" width="181" height="40" layout="responsive" sizes="(min-width: 181px) 181px, 100vw" src="content/img/CTA-fichatecnica.png" alt="Peugeot 208"></amp-img></a>
    </div>

<!-- ANIMATION -->
        <amp-animation id="slideMotor" layout="nodisplay">
                <script type="application/json">
                  {
                      "duration": "800ms",
                      "fill": "both",
                      "easing": "ease-out",
                      "iterations": "1",
                      "animations": [
                        {
                            "selector": "#imgmotor",
                            "keyframes": [
                              { "transform": "translateX(-150%)" },
                              { "transform": "translateX(0)" }
                            ]
                          },
                          {
                            "selector": ".iconomove",
                            "keyframes": [
                              { "transform": "translateX(-360%)" },
                              { "transform": "translateX(0)" }
                            ]
                          },
                        {
                        "selector": "#des",
                        "keyframes": [
                            { "transform": "translateX(-100%)" },
                            { "transform": "translateX(0)" }
                        ]
                        },
                        {
                            "selector": "#desb",
                            "media": "(min-width: 650px)",
                            "keyframes": [
                                { "transform": "translateX(100%)" },
                                { "transform": "translateX(0)" }
                            ]
                        }
                      ]
                    }
                </script>
        </amp-animation>   
</section>
<!-- FOURTH SECTION -->
<!-- JSON's-->
<section id="seccion-precios" class="precios-content col-12 main-content">
        <amp-position-observer on="enter:slidePrecios.start; exit:slidePrecios.start,slidePrecios.reverse" intersection-ratios="0.5" layout="nodisplay"></amp-position-observer>
        <p id="sedantitle" class="titlesedan">NUEVO <strong>PEUGEOT 208 PureTech</strong></p>
        <amp-carousel id="precioscarousel" height="500"
            layout="fixed-height"
            type="slides">
            <div class="carousel-cars">
                <img id="imgpreciocarunom" src="content/img/208-blanco-ch.png"
                    width="240"
                    height="137"
                    alt="Peugeot 208">
                </img>
                <div id="color" class="colorbar col-12">
                        <div class="colorwhite" role="button" tabindex="0" onClick="changeCarA('imgpreciocarunom','blanco')"></div>
                        <div class="colorgris" role="button" tabindex="0" onClick="changeCarA('imgpreciocarunom','gris')"></div>
                </div>
                    <p id="describeprecios">
                    <span class="title-prices-main">Allure HDi</span><br><br>
                    <span class="bold-listo">INTERIOR<span><br>
                    <span class="parrafo-listo">Comandos de audio al volante</span><br><br>
                    <span class="bold-listo">EXTERIOR<span><br>
                    <span class="parrafo-listo">Espejos calefactores y abatibles</span><br><br>
                    <span class="bold-listo">SEGURIDAD<span><br>
                    <span class="parrafo-listo">Asistencia al Frenado <br>de Urgencia (AFU)</span><br><br>
                    <span class="bold-listo">MOTOR<span><br>
                    <span class="parrafo-listo">1.6 HDi 92Hp Turbo</span><br><br>
                    <span class="title-prices-main">Desde $286,900</span>
                    </p>
            </div>
            <div class="carousel-cars">
                <img id="imgpreciocardosm" src="content/img/208-gris-ch.png"
                    width="244"
                    height="137"
                    alt="Peugeot 208">
                </img>
                <div id="color" class="colorbar col-12">
                        <div class="colorwhite" role="button" tabindex="0" onClick="changeCarA('imgpreciocardosm','blanco')"></div>
                        <div class="colorgris"  role="button" tabindex="0" onClick="changeCarA('imgpreciocardosm','gris')"></div>
                    </div>
                    <p id="describeprecios">
                        <span class="title-prices-main">PureTech</span><br>
                        <span class="texto-precios-min">Equipamiento base
                        más Allure HDi</span><br><br>
                        <span class="bold-listo">INTERIOR<span><br>
                        <span class="parrafo-listo">Peugeot i-Cockpit R</span><br><br>
                        <span class="bold-listo">EXTERIOR<span><br>
                        <span class="parrafo-listo">Sensores de ayuda al estacionamiento</span><br><br>
                        <span class="bold-listo">SEGURIDAD<span><br>
                        <span class="parrafo-listo">Frenos de disco en las 4 ruedas</span><br><br>
                        <span class="bold-listo">MOTOR<span><br>
                        <span class="parrafo-listo">1.2l Puretech 110Hp Turbo</span><br><br>
                        <span class="title-prices-main">Desde $299,900</span>
                    </p>
            </div>
            <div class="carousel-cars">
                <img id="imgpreciocartresm" src="content/img/208-negro-ch.png"
                    width="244"
                    height="137"
                    alt="Peugeot 208">
                </img>
                <div id="color" class="colorbar col-12">
                        <div class="colormoka"  role="button" tabindex="0" onClick="changeCarA('imgpreciocartresm','gris')"></div>
                        <div class="colornegro" role="button" tabindex="0" onClick="changeCarA('imgpreciocartresm','negro')"></div>
                    </div>
                    <p id="describeprecios">
                    <span class="title-prices-main">Allure Pack</span><br>
                    <span class="texto-precios-min">Equipamiento base
                    más Puretech</span><br><br>
                    <span class="bold-listo">INTERIOR<span><br>
                    <span class="parrafo-listo">Cámara de reversa</span><br><br>
                    <span class="bold-listo">EXTERIOR<span><br>
                    <span class="parrafo-listo">Cristales y medallón sobretintado</span><br><br>
                    <span class="bold-listo">SEGURIDAD<span><br>
                    <span class="parrafo-listo">4 Bolsas de aire </span><br><br>
                    <span class="bold-listo">MOTOR<span><br>
                    <span class="parrafo-listo">1.6VTI 115Hp</span><br><br>
                    <span class="title-prices-main">Desde $314,900</span>
                    </p>
            </div>
        </amp-carousel>
        <div id="moveprecios" class="background-precio col-12">
            <div id="carpreciouno" class="preciobar">
                <img id="imgpreciocaruno" src="content/img/208-blanco-ch.png" width="244" height="137" alt="301 blanco"></img>
                <div id="color" class="colorbar col-12">
                    <div role="button" tabindex="0" class="colorwhite" onClick="changeCarA('imgpreciocaruno','blanco')"></div>
                    <div role="button" tabindex="1" class="colorgris" onClick="changeCarA('imgpreciocaruno','gris')"></div>
                </div>
                <p id="describeprecios">
                    <span class="title-prices-main">Allure HDi</span><br><br>
                    <span class="bold-listo">INTERIOR<span><br>
                    <span class="parrafo-listo">Comandos de audio al volante</span><br><br>
                    <span class="bold-listo">EXTERIOR<span><br>
                    <span class="parrafo-listo">Espejos calefactores y abatibles</span><br><br>
                    <span class="bold-listo">SEGURIDAD<span><br>
                    <span class="parrafo-listo">Asistencia al Frenado <br>de Urgencia (AFU)</span><br><br>
                    <span class="bold-listo">MOTOR<span><br>
                    <span class="parrafo-listo">1.6 HDi 92Hp Turbo</span><br><br>
                    <span class="title-prices-main">Desde $286,900</span>
                </p>
            </div>
            <div id="carpreciodos" class="preciobar">
                <img id="imgpreciocardos" src="content/img/208-gris-ch.png" width="244" height="137" alt="301 gris artense"></img>
                <div id="color" class="colorbar col-12">
                    <div role="button" tabindex="0" class="colorwhite" onClick="changeCarA('imgpreciocardos','blanco')"></div>
                    <div role="button" tabindex="1" class="colorgris" onClick="changeCarA('imgpreciocardos','gris')"></div>
                </div>
                <p id="describeprecios">
                        <span class="title-prices-main">PureTech</span><br>
                        <span class="texto-precios-min">Equipamiento base
                        más Allure HDi</span><br><br>
                        <span class="bold-listo">INTERIOR<span><br>
                        <span class="parrafo-listo">Peugeot i-Cockpit R</span><br><br>
                        <span class="bold-listo">EXTERIOR<span><br>
                        <span class="parrafo-listo">Sensores de ayuda al estacionamiento</span><br><br>
                        <span class="bold-listo">SEGURIDAD<span><br>
                        <span class="parrafo-listo">Frenos de disco en las 4 ruedas</span><br><br>
                        <span class="bold-listo">MOTOR<span><br>
                        <span class="parrafo-listo">1.2l Puretech 110Hp Turbo</span><br><br>
                        <span class="title-prices-main">Desde $299,900</span>
                </p>
            </div>
            <div id="carpreciotres" class="preciobar">
                <img id="imgpreciocartres" src="content/img/208-negro-ch.png" width="244" height="137" layout="responsive" alt="301 gris moka"></img>
                <div id="color" class="colorbar col-12">
                    <div role="button" tabindex="0" class="colormoka" onClick="changeCarA('imgpreciocartres','gris')"></div>
                    <div role="button" tabindex="1" class="colornegro" onClick="changeCarA('imgpreciocartres','negro')"></div>
                </div>
                <p id="describeprecios">
                    <span class="title-prices-main">Allure Pack</span><br>
                    <span class="texto-precios-min">Equipamiento base
                    más Puretech</span><br><br>
                    <span class="bold-listo">INTERIOR<span><br>
                    <span class="parrafo-listo">Cámara de reversa</span><br><br>
                    <span class="bold-listo">EXTERIOR<span><br>
                    <span class="parrafo-listo">Cristales y medallón sobretintado</span><br><br>
                    <span class="bold-listo">SEGURIDAD<span><br>
                    <span class="parrafo-listo">4 Bolsas de aire </span><br><br>
                    <span class="bold-listo">MOTOR<span><br>
                    <span class="parrafo-listo">1.6VTI 115Hp</span><br><br>
                    <span class="title-prices-main">Desde $314,900</span>
                </p>
            </div>
        </div>
        <amp-img onClick="formEnable('oferta')" class="ofertabotoninterior" src="content/img/CTA-ofertacomercial.png" width="181" height="40" layout="responsive" sizes="(min-width: 181px) 181px, 100vw" alt="Peugeot 208"></amp-img>
<!-- ANIMATIONS -->
<amp-animation id="slidePrecios" layout="nodisplay">
        <script type="application/json">
          {
            "duration": "600ms",
            "fill": "both",
            "easing": "ease-out",
            "iterations": "1",
            "animations": [
            {
              "selector": "#sedantitle",
              "keyframes": [
                {
                  "transform": "translateX(100%)"
                },
                {
                  "transform": "translateX(0)"
                }
              ]
            },
            {
                "selector": "#moveprecios",
                "keyframes": [
                {
                    "transform": "translateX(-150%)"
                  },
                  {
                    "transform": "translateX(0)"
                  }
                ]
              }
            ]
          }
        </script>
</amp-animation>
</section>
<!-- FIFTY SECTION -->
<section id="Galeria" class="galeria-content col-12">
        <amp-position-observer on="enter:slideTransitionGalery.start; exit:slideTransitionGalery.start,slideTransitionGalery.reverse" intersection-ratios="0.1" layout="nodisplay"></amp-position-observer>
        <div id="galeryuno" class="grid-contenta">
            <h2 class="titleBig">EMOCIÓN LIBERADA</h2>
            <p class="descripcion-galeria">Versatilidad para el camino<br><br>
                Atrévete a descubrir el nuevo Peugeot 208, te sorprenderán los detalles de diseño y tecnología que te harán querer liberar tus emociones. Presenta elementos de diseño distintivos, con precisas lineas que surgen desde su amplia parrilla. En la parte posterior, las ópticas traseras nos muestran un efecto de garras 3D.
            </p>
            <p class="descripcion-galeria">
            El nuevo Peugeot 208 cuenta con i-Cockpit®, el cual proporciona una experiencia de conducción intuitiva, ágil y segura. Se trata de un volante compacto ajustable que se encuentra más abajo que un volante tradicional para reducir la fatiga en el conductor, acompañado de una precisa dirección con maniobrabilidad mejorada y una pantalla táctil multifunción de 7 pulgadas que pone a tu disposición todas las funcionalidades del Nuevo 208. Además, el panel elevado de instrumentos te permite acceder a toda la información sin apartar la vista del camino.
            </p>
            <amp-img class="pruebademanejoboton"  onClick="formEnable('prueba')" media="(max-width: 649px)" src="content/img/CTA-pruebademanejo.png" width="181" height="40" layout="responsive" sizes="(min-width: 181px) 181px, 100vw" alt="Peugeot 208"> </amp-img>
        </div>
        <div id="galerydos" class="grid-contentb">
            <div id="galeria-desktop" class="content-grid">
                <div class="columna col-5">
                    <p><amp-img lightbox id="img1" src="content/img/208-galeria-1.jpg" class="imggrid w3-animate-bottom" media="(min-width: 650px)" width="250" height="140" layout="responsive" sizes="(min-width: 250px) 250px, 100vw" alt="Peugeot 208"></amp-img></p>
                    <p><amp-img lightbox id="img2" src="content/img/208-galeria-2.jpg" class="imggrid w3-animate-bottom" media="(min-width: 650px)" width="250" height="140" layout="responsive" sizes="(min-width: 250px) 250px, 100vw" alt="Peugeot 208"></amp-img></p>
                </div>
                <div class="columnb col-3">
                    <p><amp-img lightbox id="img4" src="content/img/208-galeria-4.jpg" class="imggrid w3-animate-bottom" media="(min-width: 650px)" width="150" height="94" layout="responsive" sizes="(min-width: 150px) 150px, 100vw" alt="Peugeot 208"></amp-img></p>
                    <p><amp-img lightbox id="img5" src="content/img/208-galeria-5.jpg" class="imggrid w3-animate-bottom" media="(min-width: 650px)" width="150" height="94" layout="responsive" sizes="(min-width: 150px) 150px, 100vw" alt="Peugeot 208"></amp-img></p>
                </div>
                <div class="columnc col-4">
                    <p><amp-img lightbox id="img6" src="content/img/208-galeria-6.jpg" class="imggrid w3-animate-bottom" media="(min-width: 650px)" width="199" height="112" layout="responsive" sizes="(min-width: 199px) 199px, 100vw" alt="Peugeot 208"></amp-img></p>
                    <p><amp-img lightbox id="img7" src="content/img/208-galeria-7.jpg" class="imggrid w3-animate-bottom" media="(min-width: 650px)" width="200" height="168" layout="responsive" sizes="(min-width: 200px) 200px, 100vw" alt="Peugeot 208"></amp-img></p>
                </div>
                <amp-img class="pruebademanejoboton"  onClick="formEnable('prueba')"   src="content/img/CTA-pruebademanejo.png" width="181" height="40" layout="responsive" sizes="(min-width: 181px) 181px, 100vw" alt="Peugeot 208"> </amp-img>
            </div>
            <div id="galeria-mobile" class="content-grid">
                    <div class="columna col-5">
                    <figure>
                        <amp-img lightbox id="img1" src="content/img/208-galeria-1.jpg" class="imggrid w3-animate-bottom" media="(max-width: 640px)" width="350" height="142" layout="responsive" sizes="(min-width: 350px) 350px, 100vw" alt="Peugeot 208"></amp-img>
                        <amp-img lightbox id="img2" src="content/img/208-galeria-2.jpg" class="imggrid w3-animate-bottom" media="(max-width: 640px)" width="350" height="142" layout="responsive" sizes="(min-width: 350px) 350px, 100vw" alt="Peugeot 208"></amp-img>
                        <amp-img lightbox id="img4" src="content/img/208-galeria-4.jpg" class="imggrid w3-animate-bottom" media="(max-width: 649px)" width="350" height="142" layout="responsive" sizes="(min-width: 350px) 350px, 100vw" alt="Peugeot 208"></amp-img>
                        <amp-img lightbox id="img5" src="content/img/208-galeria-5.jpg" class="imggrid w3-animate-bottom" media="(max-width: 649px)" width="350" height="142" layout="responsive" sizes="(min-width: 350px) 350px, 100vw" alt="Peugeot 208"></amp-img>
                        <amp-img lightbox id="img6" src="content/img/208-galeria-6.jpg" class="imggrid w3-animate-bottom" media="(max-width: 649px)" width="350" height="142" layout="responsive" sizes="(min-width: 350px) 350px, 100vw" alt="Peugeot 208"></amp-img>
                        <amp-img lightbox id="img7" src="content/img/208-galeria-7.jpg" class="imggrid w3-animate-bottom" media="(max-width: 649px)" width="350" height="142" layout="responsive" sizes="(min-width: 350px) 350px, 100vw" alt="Peugeot 208"></amp-img>
                    </figure>
                    </div>
                </div>
<!-- ANIMATIONS -->
<amp-animation id="slideTransitionGalery" layout="nodisplay">
        <script type="application/json">
          {
            "duration": "800ms",
            "fill": "both",
            "easing": "ease-out",
            "iterations": "1",
            "animations": [
            {
              "selector": "#galeryuno",
              "keyframes": [{
                  "transform": "translateX(-100%)"
                },
                {
                  "transform": "translateX(0)"
                }
              ]
            },
            {
                "selector": "#galerydos",
                "media": "(min-width: 650px)",
                "keyframes": [{
                    "transform": "translateX(100%)"
                  },
                  {
                    "transform": "translateX(0)"
                  }
                ]
              }
            ]
          }
        </script>
</amp-animation>
</section>
</div>
</main>
<!-- FORM -->
<div id="logo-footer" onClick="formEnable()">
          <img src="content/img/vive.png" alt="Vive la experiencia Peugeot" />
          <br />
          <img src="content/img/arrowdown.png" alt="arrow"/>
</div>
<form id="myForm" method="post" action-xhr="/thankyoupage">
      <div id="footer-container">
      <?php if( $msg != "" ): ?>
            <div id="wrongForm" class="<?php echo $class ?>"><?php echo $msg ?></div>
        <?php endif; ?>
        <div class="col-6 content-form sideleft">
        <div class="col-12">
            <p class="campolado col-6"><label>Nombre(s) </label><br/>
                <input type="text" name="name" value="<?php echo $name ?>" placeholder="Juan" required/>
            </p>
            <p class="campolado col-6"><label>Apellido</label><br/>
                <input id="fullname" type="text" name="lastname" value="<?php echo $lastname ?>" placeholder="López Pérez" required/>
            </p>
        </div>
        <div class="col-12">
            <p class="campo"><label>Teléfono</label><br/>
                <input id="phone" type="tel" placeholder="(55)5555-5555" name="phone" value="<?php echo $phone ?>" size="10" minlength="10" maxlength="10"  required/>
            </p>
            <p class="campo"><label>Correo electrónico</label><br/>
            <input type="text" value="<?php echo $email ?>" placeholder="juan.lopez@example.com" name="email" required />
            </p>
        </div>
        </div>
        <div class="col-6 content-form sideright">
        <div class="col-12">
            <p class="campoladob"><label>Modelo </label></p>
                <p class="radioBtn"><input type="radio" name="modelo" value="1" checked/><label>208</label></p>
                <p class="radioBtn"><input type="radio" name="modelo" value="2" /><label >301</label></p>
        </div><br>
        <div class="col-12">
            <p class="campoladob"><label for="solicitud">Tipo de Solicitud </label></p>
                <p class="radioBtn"><input id="prueba" type="radio" name="solicitud" value="Prueba" checked/><label>Prueba de Manejo</label></p>
                <p class="radioBtn"><input id="oferta" type="radio" name="solicitud" value="Cotizacion" /><label >Oferta Comercial</label></p>
        </div>
        <div class="col-12">        
            <p class="campoladoc">Concesionario<br/>
                <select id="consecionaria" name="dealers" required >
                <option value="">Selecciona una concesionaria</option>
                <?php
                foreach ($dealers_arr as $k => $v) {
                  $selected = ( $dealers == $k )?'selected':'';
                ?>
                <option value="<?php echo $k ?>" <?php echo $selected ?>><?php echo $v ?></option>
                <?php
                }
                ?>
                </select>
            </p>
        </div>
            <p class="privacidad col-12">
                <input type="checkbox" name="politicas" value="1" checked required />
                <label><br/>Acepto las politicas de privacidad</label></p>
            
        </div>
        <div id="btn-form" class="col-12">
        <input id="btn_submit" type="submit" name="enviar" value="¡ENVIAR!" />
        </div>
        <div id="btn-link" class="col-12">
                <a href="https://www.psafinancemexico.com.mx/administracion/cotizador/cotizador.php" target="_blank" class="btn-cotiza">Quiero una cotización</a>
        </div>
    </div>
   </form>
</div>
<!-- Scripts -->
<script src="content/js/jquery-3.3.1.min.js"></script>
<script async src="content/js/scripts.js" type="text/javascript"></script>
</body>
</html>

