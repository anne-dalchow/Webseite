
const todoForm = document.querySelector('form');
const todoInput = document.getElementById('todo-input');
const todoListUL = document.getElementById('todo-list');

// Array, das alle Todos speichert
let allTodosArray = [];

// Initialisiere die Todo-Liste, wenn die Seite geladen wird
updateTodoList();

// Event-Listener für das Formular, um ein neues Todo hinzuzufügen
todoForm.addEventListener('submit', function(e){
    e.preventDefault(); // Verhindert das Standardverhalten des Formulars (Seite neu laden)
    addTodo(); // Füge ein neues Todo hinzu
})

// Funktion zum Hinzufügen eines neuen Todos
function addTodo(){
    const todoText = todoInput.value.trim(); // Extrahiere den Text aus dem Eingabefeld 
    if(todoText.length > 0){ // Prüfe, ob der Text nicht leer ist
        const todoObject = { // Erstelle ein Todo-Objekt mit dem Text und dem Status "nicht abgeschlossen"
            text: todoText,
            completed: false
        }
        allTodosArray.push(todoObject); // Füge das Todo-Objekt zum Array aller Todos hinzu
        updateTodoList(); // Aktualisiere die Todo-Liste in der Benutzeroberfläche
        todoInput.value = ""; // Leere das Eingabefeld nach dem Hinzufügen eines Todos
    }  
}

// Funktion zum Aktualisieren der Todo-Liste in der Benutzeroberfläche
function updateTodoList(){
    todoListUL.innerText = ""; // Leere die Todo-Liste, um sie neu zu updaten
    allTodosArray.forEach((todoObject, todoIndex)=>{ // Iteriere über alle Todos und erstelle für jedes ein Listenelement
        const todoItem = createTodoItem(todoObject, todoIndex); // Erstelle ein Listenelement für das Todo
        todoListUL.append(todoItem); // Füge das Listenelement zur Todo-Liste hinzu
    })
}

// Funktion zum Erstellen eines Listenelements für ein Todo
function createTodoItem(todoObject, todoIndex){
    const todoId = "todo-"+todoIndex; // Erzeuge eine eindeutige ID für das Todo
    const todoLI = document.createElement("li"); // Erstelle ein neues Listenelement
    const todoText = todoObject.text; // Extrahiere den Text des Todos
    todoLI.className = "todo"; // Setze die Klasse des Listenelements auf "todo"
    // Setze den HTML-Inhalt des Listenelements mit dem Todo-Text, einer Checkbox und einem Lösch-Button
    todoLI.innerHTML = `
        <input type="checkbox" id="${todoId}">
        <label class="custom-checkbox" for="${todoId}">
            <svg fill="transparent" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24"><path d="M382-240 154-468l57-57 171 171 367-367 57 57-424 424Z"/></svg>
        </label>
        <label for="${todoId}" class="todo-text">
            ${todoText}
        </label>
        <button class="delete-button">
            <svg fill="var(--secondary-color)" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24"><path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z"/></svg>
        </button>
    `
    // Füge einen Event-Listener hinzu, um das Todo zu löschen, wenn der Lösch-Button geklickt wird
    const deleteButton = todoLI.querySelector(".delete-button");
    deleteButton.addEventListener("click", ()=>{
        deleteTodoItem(todoIndex);
    })
    // Füge einen Event-Listener hinzu, um den abgeschlossenen Status des Todos zu aktualisieren, wenn die Checkbox geklickt wird
    const checkbox = todoLI.querySelector("input");
    checkbox.addEventListener("change", ()=>{
        allTodosArray[todoIndex].completed = checkbox.checked;
    })
    // Setze den Zustand der Checkbox basierend auf dem abgeschlossenen Status des Todos
    checkbox.checked = todoObject.completed;
    // Rückgabe des erstellten Listenelements
    return todoLI;
}

function deleteTodoItem(todoIndex){
    allTodosArray = allTodosArray.filter((_, i)=> i !== todoIndex); // Filtere das Todo-Array, um das Todo mit dem angegebenen Index zu entfernen (i nicht gleich todoIndex, dann bleibt es erhalten)
    updateTodoList(); // Aktualisiere die Todo-Liste in der Benutzeroberfläche
}