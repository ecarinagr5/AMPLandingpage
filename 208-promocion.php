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
<!doctype html>
<html ⚡="" lang="en">

<head>
  <meta charset="utf-8">
  <title>Promoción 208</title>
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
            -webkit-text-size-adjust: 100%
        }
        body {
            margin: 0;
            background:#000;
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
         /*Formulario*/
         div.content-form {
            backface-visibility: blue;
            padding: 20px 10px;
            text-align: left;
            height: 400px;
        }
        div.content-form.sideleft {
            padding-left: 12%;
            height: auto;
            background:white;
        }
        div.content-form.sideright {
            padding-right: 12%;
            height: auto;
            background:white;
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
            margin; -3% 0 0 0;
        }
        .campoladoc {
            margin: 5px 0;
        }
        .campolado {
            float:left;
            padding: 20px 0;
            margin: 15px 0 0 0;
        }
        .privacidad {
            margin:12px 0 0 0;
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
        }
        .legales {
            font-size:1.7vh;
            background: #F4F4F4;
            padding:3% 2% 2% 2%;
            text-align:justify;
        }
        .legales span {
            font-family: 'PeugeotBold'
        }
        input[name=enviar] {
            width: 100%;
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
            margin:37px 0 0 0;
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
            margin: 0 0 0 -70%;
        }
        .btn-cotiza:hover {
            text-decoration: none;
        }
         /* FOOTER */

        #footer-container {   
            background: #fff;
            height: 230px;
            bottom: 0%;
            width: 100%;
        }
        #principal {
            height:400px;
        }
        .slidegrande {
            height:100%;
            text-align:center;
            position:relative;
            background: url(content/img/208promo-desktop.jpg);
            background-position: center;
            background-repeat: no-repeat;
            background-size: 100% 100%;
            top:0;
        }
        .slidegrande amp-img {
            position:absolute;
            top: 53%;
            left: 30%;
        }
        .slidemini {
            height:400px;
            background:purple;
        }
        #modelo-section {
            display:none;
        }
        .texto-slide {
            color:white;
            font-size:12px;
            position:absolute; 
            bottom:10%;
            left:5%;
            font-family: 'PeugeotBold';
            cursor:pointer;
            display:none;
        }
        .amp-arrow {
            position: absolute;
            bottom:10%;
            left:50%;
            cursor:pointer;
        }
        .header {
            position: fixed;
            z-index: 3;
        }
        .header amp-img {
            margin: 1% 22%;
        }
        @media only screen  and (max-width: 667px) {
                #principal {
                    height:600px;
                }
                #footer-container {
                    height: 0;
                }
                #btn-link {
                    text-align: center;
                        margin: -2% 0 2% 0;
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
                margin: -5% 0 0 0;
                padding: 0 10px;
                }
                p.campoladoc {
                    margin: 0px;
                }
                #btn-form {
                    margin: 15px 0;
                }
                input[name=enviar] {
                    margin:0;
                }
                .btn-cotiza {
                    margin:0;
                }
                .slidegrande {
                    background: url(content/img/208promo-mobile.jpg);
                    height:100%;
                    text-align:center;
                    position:relative;
                    background-position: center;
                    background-repeat: no-repeat;
                    background-size: 100% auto;
                }
                .legalesm {
                    font-size:1.7vh;
                    margin-top:30%;
                    background: black;
                    color: white;
                    padding: 0 5%;
                    text-align:justify;
                }
                #sidebarLegales {
                    width:100%;
                    background:black;
                }
                .amp-close-imageb {
                    margin: 3% 0 0 85%;
                }
                .texto-slide {
                    display:block;
                    top:55%;
                }
                .legales {
                    display:none;
                }
        }
        @media screen and (min-width: 1920px) and (max-width: 3000px) { 
            .slidegrande {
                height: 600px; 
            }
         }
            /*iPad Portrait*/
       @media only screen and (min-width : 768px) and (max-width : 1023px)  { 
        div.content-form.sideleft {
            height:281px;
        }
        .legales {
            display:none;
        }
        .slidegrande {
            background: url(content/img/208promo-mobile.jpg);
            height:900px;
            text-align:center;
            position:relative;
            background-position: center;
            background-repeat: no-repeat;
            background-size: 100% 100%;
        }
        .slidemini {
            height:350px;
            width:100%;
        }
        .texto-slide {
            display:block;
            top:45%
        }
        #sidebarLegales {
            width:100%;
            background:black;
        }
        .legalesm {
            font-size:1.7vh;
            margin-top:30%;
            background: black;
            color: white;
            padding: 0 5%;
            text-align:justify;
        }
        .amp-close-imageb {
            margin: 3% 0 0 85%;
        }

       }
</style>
</head>
<body>
<!-- SIDEBAR LEGALES -->
<amp-sidebar id="sidebarLegales"
  layout="nodisplay"
  side="right">
  <amp-img class="amp-close-imageb"
    src="content/img/cancela.png"
    width="15"
    height="15"
    alt="close sidebar"
    on="tap:sidebarLegales.close"
    role="button"
    tabindex="0"></amp-img>
    <div class="legalesm">
        <span>Bono de hasta $40,000 al financiamiento para Gama 301 AM 2017, AM2018 y AM2019 y Gama 208 AM2017, AM2018 (excepto GT) desde 10% de enganche a 60 meses.</span>
        Plan de financiamiento presentado y formalizado en exclusiva con PSA Finance México aplicable para Gama 301 AM 2017, AM2018 y AM2019 y Gama 208 AM2017, AM2018 (excepto GT), con enganche mínimo desde el 10% a 60 meses, en los distribuidores que tengan habilitado el beneficio de bonos de marca advance y que tengan habilitado el plan de financiamiento.  CAT promedio del 19.3% sin IVA. "El porcentaje de CAT (Costo Anual Total) expresado, ha sido calculado tomando en cuenta un vehículo, plazo, tasa de interés promedio y un monto a financiar específico, razón por la cual se hace de su conocimiento exclusivamente para efectos INFORMATIVOS y de COMPARACIÓN, por lo cual dicho porcentaje puede variar al momento de firma de su contrato de crédito, le sugerimos verificarlo.
        Todos los planes de financiamiento están sujetos a la aprobación de crédito correspondiente. 
        Vigencia de la promoción del 1 de julio al 31 de julio de 2018.
    </div>
</amp-sidebar>
<section id="principal">
    <a href="http://previewsandbox.com/peugeot/experiencia-peugeot/208" target="_self">
        <div class="slidegrande col-12">
            <p class="texto-slide"  on="tap:sidebarLegales.toggle" role="button" tabindex="0">AVISO LEGAL  </p>
            <amp-img src="content/img/open-modal.png" media="(max-width: 649px)" width="20" height="20" layout="responsive" sizes="(min-width: 20px) 20px, 100vw" on="tap:sidebarLegales.toggle" role="button" tabindex="0"></amp-img>
        </div>
    </a>
</section>
</main>
<!-- FORM -->
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
        <div id="modelo-section" class="col-12">
            <p class="campoladob"><label>Modelo </label></p>
                <p class="radioBtn"><input type="radio" name="modelo" value="2" checked/><label >301</label></p>
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
        <div class="col-12"> 
            <p class="privacidad col-6">
                <input type="checkbox" name="politicas" value="1" checked required />
                <label><br/>Acepto las politicas de privacidad</label> 
            </p>
            <div id="btn-form" class="col-6">
                <input id="btn_submit" type="submit" name="enviar" value="¡ENVIAR!" />
            </div>
        </div> 
        <div id="btn-link" class="col-12">
                <a href="https://www.psafinancemexico.com.mx/administracion/cotizador/cotizador.php" target="_blank" class="btn-cotiza">Quiero una cotización</a>
        </div> 
        </div>
    </div>
   </form>
<div class="legales">
        <span>Bono de hasta $40,000 al financiamiento para Gama 301 AM 2017, AM2018 y AM2019 y Gama 208 AM2017, AM2018 (excepto GT) desde 10% de enganche a 60 meses.</span>
        Plan de financiamiento presentado y formalizado en exclusiva con PSA Finance México aplicable para Gama 301 AM 2017, AM2018 y AM2019 y Gama 208 AM2017, AM2018 (excepto GT), con enganche mínimo desde el 10% a 60 meses, en los distribuidores que tengan habilitado el beneficio de bonos de marca advance y que tengan habilitado el plan de financiamiento.  CAT promedio del 19.3% sin IVA. "El porcentaje de CAT (Costo Anual Total) expresado, ha sido calculado tomando en cuenta un vehículo, plazo, tasa de interés promedio y un monto a financiar específico, razón por la cual se hace de su conocimiento exclusivamente para efectos INFORMATIVOS y de COMPARACIÓN, por lo cual dicho porcentaje puede variar al momento de firma de su contrato de crédito, le sugerimos verificarlo.
        Todos los planes de financiamiento están sujetos a la aprobación de crédito correspondiente. 
        Vigencia de la promoción del 1 de julio al 31 de julio de 2018.
</div>
</div>

</body>
</html>

