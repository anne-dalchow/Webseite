let columns = document.querySelectorAll(".column");
let lastColumn = columns[columns.length-1];
lastColumn.style.display = 'none';

// Erstellen Load more button und onclick Event hinzufügen:
function showLoadMoreButton() {
    let loadMore = document.createElement('button');
    loadMore.setAttribute("style", "display:inline-block; padding:5px 10px; margin-top:20px");
    loadMore.innerText = "Load more";
    document.querySelector('#work').appendChild(loadMore);
    loadMore.onclick = function () {
        lastColumn.style.display = 'flex';
        loadMore.style.display = 'none';
        showCloseButton();
    };
}

// Erstellen Close Button mit onclick Event:
function showCloseButton() {
    let close = document.createElement('button');
    close.setAttribute("style", "display:inline-block; padding:5px 10px; margin-top:20px");
    close.innerText = "Close";
    document.querySelector('#work').appendChild(close);
    close.onclick = function () {
        lastColumn.style.display = 'none';
        close.style.display = 'none';
        showLoadMoreButton();
    };
}

// Bedingungen zum Anzeigen des jeweiligen Buttons:
for (let i = 0; i < columns.length; i++) {
    if (columns[i] === lastColumn) {
        if (columns[i].style.display === 'none') {
            showLoadMoreButton();
        } else {
            showCloseButton();
        }
        break;
    }
}




