
var flag = 0;
var myIndex = 0;
var myIndex301 = 0;
carousel208();
carousel301();
requestAnimationFrame(enableForm, 0);
document.addEventListener("mouseover", movemouse);

function enableForm() {
    $('#logo-footer').animate({ bottom: "-100px", opacity: '0'}, 0);
    $('#logo-footer').animate({bottom: "0", opacity: '1'}, 1000);
 }

/* Inicia Control de Cookies */
function controlcookies() {
// si variable no existe se crea (al clicar en Aceptar)
localStorage.controlcookie = (localStorage.controlcookie || 0);
localStorage.controlcookie++; // incrementamos cuenta de la cookie
cookie1.style.display='none'; // Esconde la política de cookies
}

function cookies() {
document.getElementById('cookies').style.visibility='hidden';
}

/*Reproduccion automatica Slide */
function carousel208() {
    var x = document.getElementsByClassName("mySlides");
    for (var i = 0; i < x.length; i++) {
        x[i].style.display = "none";  
    }
    myIndex++;
    if (myIndex > x.length) {myIndex = 1}    
    x[myIndex-1].style.display = "block"; 
    x[myIndex-1].classList.add('w3-animate-fadingslide'); 
    setTimeout(carousel208, 4500);
}

function carousel301() {
    var y = document.getElementsByClassName("mySlides301");
    for (var i = 0; i < y.length; i++) {
        y[i].style.display = "none";    
    }
    myIndex301++;
    if (myIndex301 > y.length) {myIndex301 = 1}    
    y[myIndex301-1].style.display = "block"; 
    y[myIndex301-1].classList.add('w3-animate-fadingslide');
    setTimeout(carousel301, 4500); // Change image every 2 seconds
}
function movemouse(e) {

}

var sidea = document.getElementById('ladoa'); 
sidea.onmousemove = function(e) {
    document.getElementById('ladoa').style.cursor ='url(content/img/208.cur),auto';
}

var sideb = document.getElementById('ladob'); 
sideb.onmousemove = function(e) { 
    var x = e.pageX - this.offsetLeft; 
    var y = e.pageY - this.offsetTop; 
    document.getElementById('ladob').style.cursor ='url(content/img/301.cur),auto';
}
/* Formulario */
function formEnable() { 
	if ( flag % 2 === 0) {    
        var topFooter = window.innerWidth > 801 ? '400px' : ( window.innerWidth<=850 && window.innerWidth >=668 ) ? '270px': (window.innerWidth<=320 && window.innerWidth >=568) ? '270px' : ( window.innerWidth < '640px' ? '500px': '500px' );
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

