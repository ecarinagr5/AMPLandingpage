<?php
/*$mysqli = new mysqli("localhost","sis_peugeotmx","fIciwe67","20180227_peugeotmexico");*/
$mysqli = new mysqli("localhost","sis_previews","PAnuDE42","20180223_previewsandbox");

function _telefono($phone,$mysqli){
  $resultado = $mysqli->query("SELECT telefono FROM tab_registros_multi where telefono = '".$phone."'");
  $numrows = $resultado->num_rows;
  $resultado->close();

  return $numrows;
}

function _getRandom($mysqli){
  $flag = 0;
  $random = 0;
  while ($flag == 0) {
    $random = rand(100000,999999);
    if ($resultado = $mysqli->query("select * from tab_registros_multi where random=$random")) {
      if($resultado->num_rows==0){
        $flag = 1;
        $resultado->close();
      }
    }
  }
  return $random;
}

function _rudeness($word=""){
  // 173
  $rudeness = array(
    "bronca","broncas","broncudo","broncudos","broncudas","bobo","boba","bobos","bobas","cabron","cabrón","cabrona","cabrones","caca","cagar","cagada","cagado","cagadas","cagados","cagando","carajo","carajos","chinga","chingo","chingar","chingaos","chingado","chingada","chingamos","chingaron","chingadera","chingaderas","chingon","chingón","chingona","chingones","chingoneria","chingale","cojer","cojido","cojida","cojidita","cojidito","cojemos","cojen","cojimos","coji","culera","culero","culerada","culeras","culeros","culo","culote","culon","culón","desmadrar","desmadre","desmadrarse","desmadrado","encabronar","encabronarse","encabronado","encabronados","encabronada","encabronadas","enc","huevon","huevonada","huevonear","webon","webos","mis huevos","guey","golfa","golfas","idiota","idiotas","imbecil","imbécil","imbeciles","imbéciles","imbesil","imbésiles","joterias","joteria","joder","jodete","madrear","madreado","madreados","madreada","madreadas","madriza","mamar","mamada","mamado","mamon","mamadas","mamados","mames","mamas","mamamos","mamaste","mamo","mentar","mentada","mientas","mentando","mento","marica","maricas","maricon","maricona","mariconeria","mariconerias","mierda","mierdas","OGTS","O.G.T","ojete","ojetes","pedo","pedote","pedos","pedotes","pedin","pendeja","pendejo","pendejas","pendejos","pendejada","pitito","pitote","pinche","pinches","pija","pijas","pudrete","puñetas","puta","puto","putazo","putiza","putazos","putisima","putita","putito","putisimo","putito","putote","verga","vergas","vergaso","vergazo","vergazos","vergasos","vergudo","verguda","vergudos","wey","wtf","zorrear","perra","perro","oto","Jundillo","desgraciado","culero","perrustico","putiar","perriar","culiar"
  );
  return str_ireplace($rudeness, "", $word);
}

function _ping($domain=""){
    $starttime = microtime(true);
    @$file     = fsockopen ($domain, 80, $errno, $errstr, 10);
    $stoptime  = microtime(true);
    $status    = 0;

    if (!$file){
      // Site is down
      $status = -1;
    }else{
        fclose($file);
        $status = ($stoptime - $starttime) * 1000;
        $status = floor($status);
    }
    return $status;
}

function _save($data,$mysqli){
  return $mysqli->query("insert into tab_registros_multi (random,nombre,correo,telefono,estado,modelo,id_modelo,tipo_ldp,aviso_privacidad,utm,utm_campaign,utm_source,utm_medium,utm_content,utm_term,utm_completa,cotizacion,prueba_manejo,fecha) values(".$data->id.",'".$data->nombre."','".$data->correo."','".$data->telefono."','".$data->estado."','".$data->modelo."','".$data->id_modelo."','".$data->tipo_ldp."','".$data->aviso_privacidad."','".$data->utm."','".$data->utm_campaign."','".$data->utm_source."','".$data->utm_medium."','".$data->utm_content."','".$data->utm_term."','".$data->utm_completa."','".$data->cotizacion."','".$data->prueba_manejo."',NOW());");
}

$models_arra = array(
  1 => "208",
  2 => "301",
  3 => "308",
  4 => "Suv 3008",
  5 => "Traveller",
  6 => "Partner Teppe",
  7 => "Partner",
  8 => "Expert",
  9 => "Manager",
  10 => "508",
  11 => "2008",
  12 => "5008",
  13 => "Partner VU"
);

$dealers_arr = array(
  "54"=>"PEUGEOT Pruebas",
  "1"=>"PEUGEOT AGUASCALIENTES",
  "3"=>"PEUGEOT CHIHUAHUA",
  "4"=>"PEUGEOT TORREÓN",
  "5"=>"PEUGEOT SALTILLO",
  "7"=>"PEUGEOT DF UNIVERSIDAD",
  "9"=>"PEUGEOT DF VALLEJO",
  "10"=>"PEUGEOT DF ANZURES",
  "11"=>"PEUGEOT DF LOMAS VERDES",
  "13"=>"PEUGEOT DF ARBOLEDAS",
  "15"=>"PEUGEOT PACHUCA",
  "16"=>"PEUGEOT GUADALAJARA ÁVILA CAMACHO",
  "17"=>"PEUGEOT GUADALAJARA LOPEZ MATEOS",
  "18"=>"PEUGEOT MORELIA",
  "19"=>"PEUGEOT MONTERREY GONZALITOS",
  "20"=>"PEUGEOT MONTERREY TEC",
  "21"=>"PEUGEOT OAXACA",
  "23"=>"PEUGEOT QUERÉTARO",
  "24"=>"PEUGEOT CANCÚN",
  "25"=>"PEUGEOT SAN LUIS POTOSÍ LOMAS",
  "26"=>"PEUGEOT SAN LUIS POTOSÍ GLORIETA",
  "27"=>"PEUGEOT HERMOSILLO",
  "31"=>"PEUGEOT MÉRIDA",
  "32"=>"PEUGEOT DF MIRAMONTES",
  "33"=>"PPEUGEOT BONAPARTE",
  "34"=>"PEUGEOT VILLAHERMOSA",
  "35"=>"PEUGEOT TUXTLA GUTIÉRREZ",
  "36"=>"PEUGEOT DURANGO",
  "37"=>"PEUGEOT CUERNAVACA",
  "38"=>"PEUGEOT COLIMA",
  "40"=>"PEUGEOT LEÓN",
  "41"=>"PEUGEOT CULIACÁN",
  "43"=>"PEUGEOT MONTERREY SAN NICOLÁS",
  "44"=>"PEUGEOT MEXICALI",
  "45"=>"PEUGEOT GUADALAJARA ACUEDUCTO",
  "46"=>"PEUGEOT DF INTERLOMAS",
  "47"=>"PEUGEOT CAMPECHE",
  "48"=>"PEUGEOT PUEBLA",
  "49"=>"PEUGEOT PUERTO VALLARTA",
  "50"=>"PEUGEOT TIJUANA",
  "52"=>"PEUGEOT TOLUCA",
  "53"=>"PEUGEOT XALAPA",
  "55"=>"PEUGEOT TAMPICO",
  "56"=>"PEUGEOT GUADALAJARA GALERÍAS",
  "57"=>"PEUGEOT PEDREGAL"
);
?>
