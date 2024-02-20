// globally declaring important variables
var selectedYear;
var classNames;
var amountOfClasses;
var classType; // tutor or class
var className; // tutor/class name
var username;

// Year buttons -> Tutor/class button
function showClasses(year) { 
    selectedYear = year;
    var yearsToRemove = document.getElementsByClassName("year-button");
    // Loop through each year button element and remove it
    for (var i = 0; i < yearsToRemove.length; i++) {
        yearsToRemove[i].parentNode.removeChild(yearsToRemove[i]);
    }
    var buttons = document.getElementById("buttonContainer");
    var newButtons = "";
    newButtons += `<button class="tutorClassButton" onclick="classOrTutor('Classes')">Classes</button>`;
    newButtons += `<button class="tutorClassButton" onclick="classOrTutor('Tutors')">Tutors</button>`;
    buttons.innerHTML = newButtons;
}

// Tutor/class button -> specific class button
function classOrTutor(type) {
    classType = type;
    fetch('http://localhost/digistamp/panel.php?year=' + selectedYear + '&type=' + type)
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok.');
        }
        const isJson = response.headers.get('Content-Type').includes('application/json');
        if (isJson) {
            return response.json();
        }
    })
    .then(data => {
        if (typeof data === 'object') {
            // Gets rid of old buttons
            var removeChoiceButtons = document.getElementsByClassName("tutorClassButton");
            for (var i = 0; i < removeChoiceButtons.length; i++) {
                removeChoiceButtons[i].parentNode.removeChild(removeChoiceButtons[i]);
            }
            // Prepares to add new buttons
            var classlist = document.getElementById('buttonContainer');
            classlist.innerHTML = '';
            var insertedData = '';
            // Response parsed
            classNames = data.classList;
            // Loops through each class/tutor adding the name for that tutor/class to the new button
            for (var i = 0; i < classNames.length; i++) {
                insertedData += `<button class="getStudentsButton" onclick="fetchStudents('`+classNames[i]+`')">`+classNames[i]+`</button>`;
            }
            classlist.innerHTML = insertedData;
        }
    })
    .catch(error => {
        console.error("Error:", error);
    });
}

// specific class button -> student cards
function fetchStudents(className) {
    fetch("http://localhost/digistamp/panel.php?class=" + className)
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok.');
        }
        const isJson = response.headers.get('Content-Type').includes('application/json');
        if (isJson) {
            return response.json();
        }
    })
    .then(data => {
        if (typeof data === 'object' && Array.isArray(data) && data !== null) {
            // Removes old buttons
            var removeClassButtons = document.getElementsByClassName("getStudentsButton");
            for (var i = 0; i < removeClassButtons.length; i++) {
                removeClassButtons[i].parentNode.removeChild(removeClassButtons[i]);
            }
            studentMatrix = data;
            var cardPlacement = document.getElementById("card-container");
            cardPlacement.innerHTML = '';
            var cardContent = '<div class="card-container">';
            // Iterates over items in array, changing associative arrays into normal arrays
            for (var i = 0; i < studentMatrix.length; i++) {
                var surname = studentMatrix[i][0];
                var forename = studentMatrix[i][1];
                username = studentMatrix[i][2];
                var stamps = studentMatrix[i][3];
                // Place DOM elements
                cardContent += '<div class="card"><div class="card-content">';
                cardContent += '<h4>Name: ' + surname + ", " + forename + '</h4>';
                cardContent += '<p>Username: ' + username + '</p>';
                cardContent += '<p>Stamps: ' + stamps + '</p>';
                cardContent += `<button class="addStamps" onclick="showOverlay('`+username+`')">Add stamps</button>`;
                cardContent += '</div></div>';
            }
            cardContent += "</div>" // closes card container after final iteration
            cardPlacement.innerHTML = cardContent;
        }
    })
    .catch(error => {
        console.error("Error:", error);
    })
}

// Function that shows the overlay
function showOverlay(userId) {
    var overlay = document.getElementById('overlay');
    overlay.style.display('flex');
    studentId = userId;
}

// Function that makes overlay disappear
function cancelOverlay() {
    var overlay = document.getElementById('overlay');
    overlay.style.display('none');
}
/*
// Function that sends stamps via AJAX
function sendStamps() {
    var stampCount = document.getElementById('stamps').value; // assigns the value of stamps
    xhr = new XMLHttpRequest;
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            cancelOverlay(); // removes original overlay
            if (xhr.status === 200) {
                var phpResponse = JSON.parse(xhr.responseText);
                // sets new overlay
                var overlay = document.getElementById('stampResponse');
                overlay.innerHTML = '';
                var responseText = "<p>"+phpResponse+"</p>";
                responseText += "<button id='finish'>Finish</button>";
            }
        }
    };
    xhr.open('GET', 'panel.php?username=' + userId + '&stamps=' + stampCount, true);
}*/