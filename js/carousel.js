const dots = document.getElementsByClassName("dots");
dots[0].classList.add("aktiv");                             

const slides = document.getElementsByClassName("slide");     
slides[0].classList.add("aktiv");

let aktuellerIndex = 0;
let letzteAktualisierung = new Date();       // Zeitpunkt der letzten Aktualisierung


// Funktion um mit den Pfeiltasten umzuschalten:

function umschalten(anzahl){                      

   var neuerIndex = aktuellerIndex + anzahl;       
   
   if(neuerIndex < 0){                        
    neuerIndex = slides.length -1;   
   }
   if(neuerIndex > slides.length -1 ){          
    neuerIndex = 0;
   }

   springeZuEintrag(neuerIndex);   
  }

// Funktion um das Bild und den dazugehörigen Punkt zu aktualisieren:

function springeZuEintrag(neuerIndex){          
   
  dots[aktuellerIndex].classList.remove("aktiv");        
  slides[aktuellerIndex].classList.remove("aktiv");        

  dots[neuerIndex].classList.add("aktiv");
  slides[neuerIndex].classList.add("aktiv");

  aktuellerIndex = neuerIndex;                  
  letzteAktualisierung = new Date();       // letzteAktualisierung wird auf aktuelle Zeit gesetzt       
}     
  
//Funktion um automatisch zwischen den Bildern zu wechseln, wenn eine bestimmte Zeit vergangen ist (3,5 Sekunden):

function automatischWeiterschalten(){
  const vergangeneZeit = new Date() - letzteAktualisierung;

  if(vergangeneZeit >= 5000){         
   umschalten(1);
  }
}

// Intervall automatischWeiterschalten starten und Rückgabewert wird intervalID zugeordnet

let intervalID = setInterval(automatischWeiterschalten); 

let stopButton = document.getElementById("stop-btn");
let playButton = document.getElementById("play-btn");

stopButton.addEventListener('click', togglePlayPause);
playButton.addEventListener('click', togglePlayPause);

function togglePlayPause() {
    if (intervalID) {     //wenn intervalID einen Wert hat, dann:
        clearInterval(intervalID);           // Intervall stoppen
        intervalID = null; // Timer-Identifikator auf null setzen
        stopButton.style.display = 'none';
        playButton.style.display = 'block';

    } else {
        intervalID = setInterval(automatischWeiterschalten); // wenn intervalID keinen Wert hat, dann soll ein neues gestartet werden
        stopButton.style.display = 'block';
        playButton.style.display = 'none';
    }
}

  