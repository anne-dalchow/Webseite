//PIXI JS Framework
/*
const app = new PIXI.Application({
    width: 1200, // gewünschte Breite
    height: 800 , // gewünschte Höhe

  }); 

// Füge die PIXI-Anwendung dem HTML-Dokument hinzu
document.body.appendChild(app.view);    

// Array für Ufos
const ufoArray = [];


// Hintergrundbild

const backgroundTexture = PIXI.Texture.from('assets/background-image.png');

const backgroundSprite = new PIXI.TilingSprite(
 backgroundTexture,
 app.screen.width,
 app.screen.height
);

app.stage.addChild(backgroundSprite);

// Funktion zur Aktualisierung des Hintergrund
function updateBackground(deltaY) {
 backgroundSprite.tilePosition.y += deltaY; 
}

app.ticker.add((delta) => {
 // Bewegt Hintergrund kontinuierlich nach unten
 updateBackground(delta * 1); 
});



// Lose Screen Container und Text
const loseScreen = new PIXI.Container();

const background = new PIXI.Graphics();
background.beginFill(0x000000, 0.5); 
background.drawRect(0, 0, app.screen.width, app.screen.height);
background.endFill();

const messageText = new PIXI.Text('You Lose!', { fontSize: 36, fill: 'white' });
messageText.position.set(app.screen.width / 2 - messageText.width / 2, app.screen.height / 2 - 30 - messageText.height / 2 - 30);
const tryAgainText = new PIXI.Text('Try Again', { fontSize: 24, fill: 'white' });
tryAgainText.position.set(app.screen.width / 2 - tryAgainText.width / 2, app.screen.height / 2 - tryAgainText.height / 2);


// Erstelle ReloadButton 

const reloadButtonTexture = PIXI.Texture.from('assets/reload-icon.svg'); 
const reloadButton = new PIXI.Sprite(reloadButtonTexture);
reloadButton.anchor.set(0.5); 
reloadButton.position.set(app.screen.width / 2, app.screen.height / 2 + 70); 
reloadButton.scale.x = 0.5;
reloadButton.scale.y = 0.5;

// Klick-Event für ReloadButton
reloadButton.interactive = true;
reloadButton.buttonMode = true;
reloadButton.on('click', () => {
   
    location.reload(); 
});

// Füge die Elemente zum LoseScreen-Container hinzu
loseScreen.addChild(background, messageText, tryAgainText, reloadButton);


// UFO Interval Funktion, Intervall auf 1000ms gesetzt & flyDown Funktion

//gameInterval Funktion aus gameloop.js
gameInterval(function(){         
 const ufo = PIXI.Sprite.from('assets/ufo' + random(1,2) + '.png');    // mit random (1,2) wird ufo1 oder ufo2 per Zufall ausgewählt
 ufo.x = random(0, 700);         // random Funktion aus gameloop.js; 0 bis 700 ( 700 max Breite)
 ufo.y = -25;
 ufo.scale.x = 0.1;
 ufo.scale.y = 0.1;
 app.stage.addChild(ufo);

 // neu erzeugtes Ufo wird dem Array hinzugefügt
 ufoArray.push(ufo);           
 
 //flyDown Funktion aus gameloop.js
 flyDown(ufo, 1);                
 
 //Collisions Funktion aus gameloop.js - mit stopGame() Spiel wird gestoppt
 waitForCollision(ufo, rocket).then(function(){
  app.stage.removeChild(rocket);
  app.stage.removeChild(backgroundSprite);
  stopGame();
  app.stage.addChild(loseScreen);
 });              

}, 1000);


// Rakete 
const rocket = PIXI.Sprite.from('assets/rocket.png');
rocket.x = 350;            //Ausrichtung an X und Y Achse
rocket.y = 520;
rocket.scale.x = 0.05;     
rocket.scale.y = 0.05;
app.stage.addChild(rocket);   // addChild wird in Grafikbibliotheken und -frameworks verwendet, um grafische Objekte zu einer Darstellungsebene oder Bühne hinzuzufügen

// Rakete Bewegung
function leftKeyPressed(){          //leftKeyPressed Funktion aus gameloop.js
 if (rocket.x <= 10){
  return;
 }
 rocket.x = rocket.x-5;
};

function rightKeyPressed(){     
 if (rocket.x >= 750){
  return;
 }     
 rocket.x = rocket.x + 5;
};

function upKeyPressed(){
 if (rocket.y <= 10){
  return;
 }              
 rocket.y = rocket.y - 5;
};

function downKeyPressed(){          
 rocket.y = rocket.y + 5;
};

//Bullets hinzufügen
function spaceKeyPressed(){
 const bullet = PIXI.Sprite.from('assets/bullet.png');    
 bullet.x = rocket.x +13;        
 bullet.y = rocket.y - 20;
 bullet.scale.x = 0.03;
 bullet.scale.y = 0.03 ;
 app.stage.addChild(bullet);

 flyUp(bullet);

 waitForCollision(bullet, ufoArray).then(function([bullet, ufo]){
  app.stage.removeChild(bullet);
  app.stage.removeChild(ufo);
 });  
 
}

*/

const app = new PIXI.Application({
    width: 1200,
    height: 600,
});

document.body.appendChild(app.view);

const ufoArray = [];
let ufoSpeed = 1; // Anfangsgeschwindigkeit der Ufos

const backgroundTexture = PIXI.Texture.from('assets/background-image.png');
const backgroundSprite = new PIXI.TilingSprite(
    backgroundTexture,
    app.screen.width,
    app.screen.height
);
app.stage.addChild(backgroundSprite);

function updateBackground(deltaY) {
    backgroundSprite.tilePosition.y += deltaY;
}

app.ticker.add((delta) => {
    updateBackground(delta * 1);
});

const loseScreen = new PIXI.Container();

const background = new PIXI.Graphics();
background.beginFill(0x000000, 0.5);
background.drawRect(0, 0, app.screen.width, app.screen.height);
background.endFill();

const messageText = new PIXI.Text('You Lose!', { fontSize: 36, fill: 'white' });
messageText.position.set(app.screen.width / 2 - messageText.width / 2, app.screen.height / 2 - 30 - messageText.height / 2 - 30);
const tryAgainText = new PIXI.Text('Try Again', { fontSize: 24, fill: 'white' });
tryAgainText.position.set(app.screen.width / 2 - tryAgainText.width / 2, app.screen.height / 2 - tryAgainText.height / 2);

const reloadButtonTexture = PIXI.Texture.from('assets/reload-icon.svg');
const reloadButton = new PIXI.Sprite(reloadButtonTexture);
reloadButton.anchor.set(0.5);
reloadButton.position.set(app.screen.width / 2, app.screen.height / 2 + 70);
reloadButton.scale.x = 0.5;
reloadButton.scale.y = 0.5;
reloadButton.interactive = true;
reloadButton.buttonMode = true;
reloadButton.on('click', () => {
    location.reload();
});

loseScreen.addChild(background, messageText, tryAgainText, reloadButton);

gameInterval(function () {
    const ufo = PIXI.Sprite.from('assets/ufo' + random(1, 3) + '.png');
    ufo.x = random(0, 1100);
    ufo.y = -25;
    ufo.scale.x = 0.1;
    ufo.scale.y = 0.1;
    app.stage.addChild(ufo);
    ufoArray.push(ufo);

    flyDown(ufo, ufoSpeed);

    waitForCollision(ufo, rocket).then(function () {
        app.stage.removeChild(rocket);
        app.stage.removeChild(backgroundSprite);
        stopGame();
        app.stage.addChild(loseScreen);
    });

}, 1000);

const rocket = PIXI.Sprite.from('assets/rocket.png');
rocket.x = 350;
rocket.y = 520;
rocket.scale.x = 0.05;
rocket.scale.y = 0.05;
app.stage.addChild(rocket);

function leftKeyPressed() {
    if (rocket.x <= 10) {
        return;
    }
    rocket.x -= 5;
}

function rightKeyPressed() {
    if (rocket.x >= 1125) {
        return;
    }
    rocket.x += 5;
}

function upKeyPressed() {
    if (rocket.y <= 10) {
        return;
    }
    rocket.y -= 5;
}

function downKeyPressed() {
    if (rocket.y >= 500) {
        return;
    }
    rocket.y += 5   ;
}

function spaceKeyPressed() {
    const bullet = PIXI.Sprite.from('assets/bullet.png');
    bullet.x = rocket.x + 13;
    bullet.y = rocket.y - 20;
    bullet.scale.x = 0.03;
    bullet.scale.y = 0.03;
    app.stage.addChild(bullet);

    flyUp(bullet);

    waitForCollision(bullet, ufoArray).then(function ([bullet, ufo]) {
        app.stage.removeChild(bullet);
        app.stage.removeChild(ufo);
    });
}

// Funktion zur Erhöhung der Spielgeschwindigkeit
function increaseDifficulty() {
    ufoSpeed += 0.1; // Erhöht die Ufo-Geschwindigkeit um 0.1 pro Aufruf
    // Hier können Sie weitere Anpassungen vornehmen, z.B. die Intervallzeit verkürzen
    // oder andere Spielparameter anpassen
}

// Regelmäßig increaseDifficulty aufrufen, z.B. aller 1,5 Sekunden
setInterval(increaseDifficulty, 1500); // alle 1,5 Sekunden

// Hier können Sie die increaseDifficulty Funktion weiter anpassen, z.B. abhängig vom Spielverlauf oder anderen Faktoren
