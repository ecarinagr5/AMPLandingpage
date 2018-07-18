/* Inicializa eventos y variables */
var flag = 0;
var suma = 0;
requestAnimationFrame(enableForm, 0);
/* Inicializa eventos y variables */
function enableForm() {
    $('#logo-footer').animate({ bottom: "-100px", opacity: '0'}, 0);
    $('#logo-footer').animate({bottom: "0", opacity: '1'}, 1000);
 }
function movemouse(e) {
}
/* Formulario */
function formEnable(value) { 
	if (value === 'oferta') {document.getElementById("oferta").checked = true;document.getElementById("prueba").checked = false;} 
	else if (value === 'prueba') {document.getElementById("prueba").checked = true;document.getElementById("oferta").checked = false;} 
	if ( flag % 2 === 0) {    
          var topFooter = window.innerWidth > 801 ? '400px' : ( window.innerWidth<=850 && window.innerWidth >=668 ) ? '550px': (window.innerWidth<=320 && window.innerWidth >=568) ? '270px' : ( window.innerWidth < '640px' ? '500px': '500px' );
          $('#logo-footer').animate({bottom: topFooter , opacity: '1'}, "0s");
		  $('#footer-container').animate({
            height: topFooter
        });
		  } else {
          $('#logo-footer').animate({bottom: 0 , opacity: '1'}, 1000);
          $('#footer-container').animate({
            height: '0'
        },1000);
	  }
	  flag = flag + 1;
}

function handleSwitch () {
    var x = document.getElementById('checkSlide');
		if( x.checked === true ) {
            document.getElementById('exterior-contenido301').style.display = 'none';
            document.getElementById('interior-contenido301').style.display = 'block';
		} 
		if (x.checked === false) {
			document.getElementById('interior-contenido301').style.display = 'none';
			document.getElementById('exterior-contenido301').style.display = 'block';
        } 
        
}
function clickSwitch (val) {
    var bodyTotal = document.getElementsByTagName("BODY")[0];
       if(val === 'interior') {
           document.getElementById('exterior-contenido301').style.display = 'none';
           document.getElementById('interior-contenido301').style.display = 'block';
           bodyTotal.classList.add('background-black');
           document.getElementById('checkSlide').checked = true;
       } else {
           document.getElementById('interior-contenido301').style.display = 'none';
           document.getElementById('exterior-contenido301').style.display = 'block';
           document.getElementById('checkSlide').checked = false;
           bodyTotal.classList.remove('background-black');
       }
}

function changeCarA(values, val) {
    switch(val) {
        case 'gris':
            document.getElementById(values).src='content/img/208-gris-ch.png';
        break;
        case 'blanco':
        document.getElementById(values).src='content/img/208-blanco-ch.png';
        break;
        case 'negro':
        document.getElementById(values).src='content/img/208-negro-ch.png';
        break;
    }

}

function changeCarB(values, val) {
    switch(val) {
        case 'gris':
            document.getElementById(values).src='content/img/301grisartenseg.png';
        break;
        case 'grismoka':
        document.getElementById(values).src='content/img/301grismokag.png';
        break;
        case 'blanco':
        document.getElementById(values).src='content/img/301blancog.png';
        break;
        case 'negro':
        document.getElementById(values).src='content/img/301negroperlag.png';
        break;
    }

}
