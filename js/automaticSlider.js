
const slides = document.getElementsByClassName("slide-gallery");     
slides[0].classList.add("aktiv");

let aktuellerIndex = 0;

function autoUmschalten(anzahl){        
 
   slides[aktuellerIndex].classList.remove("aktiv"); 

   var neuerIndex = aktuellerIndex + anzahl;       

   if(neuerIndex < 0){                        // überprüfen ob neuer Index kleiner Null ist, wenn ja 
    neuerIndex = slides.length -1;   //dann bekommt der neue Index die slides.lenght zugewiesen. Die lenght ist aber 3. Um das letzte Bild zu bekommen daher -1. Dann ist der Index 2 (bei drei Bildern z.B.)
   }
   if(neuerIndex > slides.length -1 ){          // wenn neuer Index größer als slides.lenght -1 dann bekommt der neueIndex den Wert 0 zugeordnet
    neuerIndex = 0;
   }

   slides[neuerIndex].classList.add("aktiv");
   aktuellerIndex = neuerIndex;

  }

  setInterval(autoUmschalten, 5000, 1); 
