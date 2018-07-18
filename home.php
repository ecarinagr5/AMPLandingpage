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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="shortcut icon" href="content/img/favicon.ico" />
    <title>Peugeot</title>
    <div id="fb-root"></div>
</head>
<body>
<!-- HEADER  -->
<div id="header-container" class="header">
        <a href=""><img src="content/img/logon.png" /></a>
</div>
<!-- BODY  -->
<div id="body-content-index" class="row">
<!-- Cookies -->
<div class="cookiesms" id="cookie1">
        Peugeot México, S.A. de C.V. utiliza cookies con la finalidad de analizar el uso de la página web, mostrarle publicidad personalizada y obtener estadísticas. Las cookies son pequeños archivos de texto que pueden recabar datos personales y otra información, como su sistema operativo, su navegador, páginas que visita, tiempo que pasa en nuestro sitio, su dirección de Protocolo de Internet, sus preferencias. Las cookies pueden deshabilitarse siguiendo las instrucciones de su navegador. Para más información sobre el procedimiento de ejercicio de derechos, envíenos un correo a contacto@peugeot.com.
        <button onclick="controlcookies()">OK</button>
</div>
<!-- SLIDE  -->
  <a href="http://previewsandbox.com/peugeot/experiencia-peugeot/208"> 
        <div class="col-6 slidea">
            <div id="ladoa"  class="slidesA">
                <img id="slideuno" class="mySlides w3-animate-top"  src="content/img/208-2b.jpg" />
                <img id="slidedos" class="mySlides w3-animate-top"  src="content/img/208-3b.jpg" />
                <img id="slidetres" class="mySlides w3-animate-top"  src="content/img/208-4b.jpg" />
                <img id="slidecuatro" class="mySlides w3-animate-top"  src="content/img/208-5b.jpg" />
                <img id="slidecinco" class="mySlides w3-animate-top"  src="content/img/208-promo.jpg" />
             </div> 
            <a href="http://previewsandbox.com/peugeot/experiencia-peugeot/208-promocion">
             <div id="promo208" class="promo-content208">
               <p>PEUGEOT 208 
                 <span>BONO DE</span>
                 <span>$ 40,000</span>
               </p>
              <p>VER DETALLES</p>
            </div>    
            </a>
        </div>  
  </a>
  <a href="http://previewsandbox.com/peugeot/experiencia-peugeot/301">
        <div class="col-6 slideb"> 
                <div id="ladob" class="slidesB">
                    <img id="slidebuno" class="mySlides301 w3-animate-bottom"  src="content/img/301-3b.jpg" />
                    <img id="slidebdos" class="mySlides301 w3-animate-bottom"  src="content/img/301-4b.jpg" />
                    <img id="slidebtres" class="mySlides301 w3-animate-bottom"  src="content/img/301-5b.jpg" />
                    <img id="slidebcuatro" class="mySlides301 w3-animate-bottom"  src="content/img/301-1b.jpg" />
                    <img id="slidebcinco" class="mySlides301 w3-animate-bottom"  src="content/img/301-2b.jpg" />
                </div>
              <a href="http://previewsandbox.com/peugeot/experiencia-peugeot/301-promocion">
                <div id="promo208" class="promo-content301">
                  <p>PEUGEOT 301 
                    <span>BONO DE</span>
                    <span>$ 40,000</span>
                  </p>
                  <p>VER DETALLES</p>
                </div> 
              </a>
        </div>
    </a>
</div>
<!-- FORM -->
<div id="logo-footer" onClick="formEnable()">
                <img src="content/img/vive.png" alt="Vive la experiencia Peugeot" />
                <br />
                <img src="content/img/arrowdown.png" alt="arrow"/>
            </div>
            <form id="myForm" method="post">
      <div id="footer-container" ref="footer-container">
      <?php if( $msg != "" ): ?>
            <div id="wrongForm" class="<?php echo $class ?>"><?php echo $msg ?></div>
        <?php endif; ?>
        <div class="col-6 content-form sideleft">
        <div class="col-12">
            <p class="campolado col-6"><label>Nombre(s) </label><br/>
                <input ref="nombre" type="text" name="name" value="<?php echo $name ?>" placeholder="Nombre" onChange="" required/>
            </p>
            <p class="campolado col-6"><label>Apellido</label><br/>
                <input ref="apellido" type="text" name="lastname" value="<?php echo $lastname ?>" placeholder="Apellido" onChange="" required/>
            </p>
        </div>
        <div class="col-12">
            <p class="campo"><label>Teléfono</label><br/>
                <input type="tel" placeholder="(000) 000000-0000" name="phone" value="<?php echo $phone ?>"  required/>
            </p>
            <p class="campo"><label>Correo electrónico</label><br/>
            <input type="text" value="<?php echo $email ?>" placeholder="correo@mail.com" name="email" required />
            </p>
        </div>
        </div>
        <div class="col-6 content-form sideright">
        <div class="col-12">
            <p class="campoladob"><label>Modelo </label></p>
                <p class="radioBtn"><input type="radio" name="modelo" value="208" checked/><label>208</label></p>
                <p class="radioBtn"><input type="radio" name="modelo" value="301" /><label >301</label></p>
        </div><br>
        <div class="col-12">
            <p class="campoladob"><label for="solicitud">Tipo de Solicitud </label></p>
                <p class="radioBtn"><input type="radio" name="solicitud" value="Prueba" checked/><label>Prueba de Manejo</label></p>
                <p class="radioBtn"><input type="radio" name="solicitud" value="Cotizacion" /><label >Cotización</label></p>
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
        <input id="btn_submit" type="submit" name="enviar" value="¡QUIERO MI PRUEBA DE MANEJO!" />
        </div>
        <div id="btn-link" class="col-12">
                <a href="https://www.psafinancemexico.com.mx/administracion/cotizador/cotizador.php" target="_blank" className="btn-cotiza">Quiero una cotización</a>
        </div>
    </div>
   </form>
      </div>
</body>
<link rel="stylesheet" type="text/css" href="content/css/main.css"/>
<link rel="stylesheet" type="text/css" href="content/css/animaciones.css"/>
<script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.3.1.min.js"></script>
<script async src="content/js/home.js" type="text/javascript"></script>
<script async type="text/javascript">
    if (localStorage.controlcookie>0){ 
    document.getElementById('cookie1').style.bottom = '-50px';
    }
</script>
</body>
</html>