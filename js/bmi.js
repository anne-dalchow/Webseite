
let button = document.getElementById('submit-button');
let heightInput = document.getElementById('groesse'); 
let weightInput = document.getElementById('gewicht'); 
let outputBmi = document.getElementById('output-bmi');
let outputCategory = document.getElementById('output-category');
let imageCategory = document.getElementsByClassName('img-category');
let image = document.getElementById('bmi-img');
let reloadButton = document.getElementById('reload-button');




button.addEventListener('click', function(event){

 event.preventDefault();

  // Überprüfe, ob die Eingabefelder leer sind
  if (heightInput.value.trim() === '' || weightInput.value.trim() === '') {   
      alert("Bitte fülle zuerst alle Felder aus");                            
      return; // Funktionsende, da keine Berechnung möglich
  }
  

  let height = parseFloat(heightInput.value);      // Hole die Werte aus den Eingabefeldern und konvertiere sie in Zahlen mit parseFloat
  let weight = parseFloat(weightInput.value);      

  let bmi = weight / ((height / 100) ** 2);

  let bmiCategory;
   switch(true) {                    
     case (bmi < 18.5):
     bmiCategory = 'Du hast Untergewicht. Es ist wichtig, ausgewogen zu essen und ausreichend Kalorien zu sich zu nehmen, um gesund zuzunehmen.';
       break;
     case (bmi >= 18.5 && bmi < 24.9):      
     bmiCategory = 'Du hast ein gesundes Normalgewicht. Behalte deine gesunde Ernährung und deinen Lebensstil bei.';
       break;
     case (bmi >= 25 && bmi < 29.9):
     bmiCategory = 'Du hast Übergewicht. Versuche, deine Ernährung anzupassen und regelmäßig Sport zu treiben, um abzunehmen und deine Gesundheit zu verbessern.';
       break;
     case (bmi >= 30 && bmi < 70):
     bmiCategory = 'Du hast Adipositas. Es ist wichtig, professionelle medizinische Beratung einzuholen und Änderungen im Lebensstil vorzunehmen, um deine Gesundheit zu verbessern.';
       break;
     default:
     bmiCategory = 'Wahrscheinlich hast du dich vertippt. Bitte überprüfe deine Eingaben und starte die Berechnung erneut';
   }

   
  outputBmi.innerText = `
   Dein Gewicht: ${weight}kg
   Deine Größe: ${height}cm
   Dein BMI ist: ${bmi.toFixed(1)} 
`;
//'toFixed(1)' runden auf eine Dezimalstelle

   outputCategory.innerText = `${bmiCategory}`;

   image.style.display = 'flex';
   imageCategory.innerText = image;
   

   heightInput.value = "";
   weightInput.value = "";

   
 // Funktion zum Aktualisieren der Seite

   reloadButton.addEventListener('click', function(){
      window.location.reload();
   });

   reloadButton.style.display = 'block';
   
});




