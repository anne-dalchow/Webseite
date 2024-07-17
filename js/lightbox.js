
let currentImageIndex = 0;
let images = [];


function openLightbox(imageSrc) {

  let lightbox = document.getElementById("lightbox");
  let lightboxImg = document.getElementById("lightbox-image");
  lightbox.style.display = "block";

  // Quelle des Lightboxbildes auf angegebene imageSrc gesetzt:
  lightboxImg.src = imageSrc;

  // Funktion erstellt Array und speichert alle Bilder die in .column sind:
  images = Array.from(document.querySelectorAll('.column img'));

  // aktuelles Bild im erstellten Array mit findIndex() finden, mithilfe des entsprechenden imageSrc, wird currentImageIndex zugewiesen:
  currentImageIndex = images.findIndex(img => img.src === imageSrc);
}


function closeLightbox() {
  let lightbox = document.getElementById("lightbox");
  lightbox.style.display = "none";
}


function prevImage() {
  // Reduziert den Index um eins
  currentImageIndex = currentImageIndex - 1;
  
  // Überprüft, ob der Index kleiner als 0 ist
  if (currentImageIndex < 0) {
    // Setzt den Index auf den letzten Index im Array
    currentImageIndex = images.length - 1;
  }
  
  // Setzt die Quelle des Lightbox-Bildes auf das vorherige Bild mithilfe des Arrays images:
  document.getElementById("lightbox-image").src = images[currentImageIndex].src;
}

function nextImage() {
  currentImageIndex = currentImageIndex + 1;
  
  // Überprüft, ob der Index größer oder gleich der Anzahl der Bilder im Array ist
  if (currentImageIndex >= images.length) {
    // Setzt Index auf den 1. Index im Array
    currentImageIndex = 0;
  }
  
  // Setzt die Quelle des Lightbox-Bildes auf das nächste Bild
  document.getElementById("lightbox-image").src = images[currentImageIndex].src;
}