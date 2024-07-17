window.addEventListener("load", function () {                   //auf das gesamte Window ein EventListener, load alle Elemente sollen erst einmal geladen werden, weil auf die Positionierung der Bilder zugegriffen werden soll
      
 let imgEinfliegen = document.querySelectorAll(".webdesign-img-container> a> img");         
 imViewport();                                         // Bilder sind mit Transform ausgerichtet und sollen im Viewport auf 0 zurück fahren

 function imViewport() {
  for (let i = 0; i < imgEinfliegen.length; i++) {             
    if (imgEinfliegen[i].getBoundingClientRect().top >= 0){                     //getBoundingClientRect liefert die Position eines Elements im Browserfenster / Viewport
        if (imgEinfliegen[i].getBoundingClientRect().top <= window.innerHeight) {                
            imgEinfliegen[i].style.transform = "translateX(0)";                                
        }
    }
    
    if (imgEinfliegen[i].getBoundingClientRect().bottom >= 0) {
        if (imgEinfliegen[i].getBoundingClientRect().bottom <= window.innerHeight) {
            imgEinfliegen[i].style.transform = "translateX(0)"; 
        }
    }
  }
 }
 window.addEventListener("scroll", imViewport);                 //beim scrollen soll jedes mal überprüft werden, welche Elemente im Viewport sind und dann imViewoport ausführen
})