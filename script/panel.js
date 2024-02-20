// globally declaring important variables
var selectedYear;
var classNames;
var amountOfClasses;
var studentId; // "studentId" = username of student

// Function in control of year selection
function showClasses(year) { 
    selectedYear = year;
    var yearsToRemove = document.getElementsByClassName("year-button");
    // Loop through each year button element and remove it
    for (var i = 0; i < yearsToRemove.length; i++) {
        yearsToRemove[i].parentNode.removeChild(yearsToRemove[i]);
    }
    var buttons = document.getElementById("buttonContainer");
    var newButtons = "";
    newButtons += `<button class="tutorClassButton" onclick="classOrTutor('class')">Class</button>`;
    newButtons += `<button class="tutorClassButton" onclick="classOrTutor('tutor')">Tutor</button>`;
    buttons.innerHTML = newButtons;
}
// Function in control of deciding between tutor/class
// XHR request in control of making AJAX request to retrieve class/tutor names
function classOrTutor(type) {
    selectedType = type;
    var xhr = new XMLHttpRequest(); // Create XMLHttpRequest object
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
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
                var classNames = JSON.parse(xhr.responseText);
                var amountOfClasses = classNames.length;
                // Loops through each class/tutor adding the name for that tutor/class to the new button
                for (var i = 0; i < amountOfClasses; i++) {
                    insertedData += `<button class="getStudentsButton" onclick="fetchStudents('`+classNames[i]+`')">`+classNames[i]+`</button>`;
                }
                classlist.innerHTML = insertedData;
            }
            else {
                console.error('AJAX request failed: ' + xhr.status + ' - ' + xhr.statusText);
            }
        }
    };
    // Prepare and send the AJAX request
    xhr.open('GET', 'panel.php?year=' + selectedYear + '&type=' + selectedType, true); // type meaning tutor/class
    xhr.send();
}

// Fetches students
function fetchStudents(className) {
    var xhr = new XMLHttpRequest(); // Creates XMLHttpRequest object
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                // Removes old buttons
                var removeClassButtons = document.getElementsByClassName("getStudentsButton");
                for (var i = 0; i < removeClassButtons.length; i++) {
                    removeClassButtons[i].parentNode.removeChild(removeClassButtons[i]);
                }
                // Receives response
                var studentsArray = JSON.parse(xhr.responseText);
                var cardPlacement = document.getElementById('card-container');
                cardPlacement.innerHTML = '';
                var cardContent = '<div class="card-container">';
                // Iterates over items in array, changing associative arrays into normal arrays
                for (var i = 0; i < studentsArray.length; i++) {
                    var surname = studentsArray[i][0];
                    var forename = studentsArray[i][1];
                    var username = studentsArray[i][2];
                    var stamps = studentsArray[i][3];
                    // Place DOM elements here with the above info
                    cardContent += `<div class="card"><div class="card-content">`;
                    cardContent += '<h3>Name: ' + forename + ' ' + surname + '</h3>';
                    cardContent += '<p>Username: ' + username + '</p>';
                    cardContent += '<p>Stamps ' + stamps + '</p>';
                    cardContent += '<button class="addStamps" onclick="showOverlay('+username+')">Add Stamps</button>';
                    // Screen overlay potentially when they click on student "add stamps" button to select how many
                    // Also could make it so that the teacher can add more than one person stamps at a time...
                    // For example, they could click on add multiple and get a dropdown or maybe they just input each student's name
                    // Or maybe they can just get an add all option
                    // Consider all these options before removing comments
                }
                cardContent += "</div>"; // closes card container after the final iteration
                cardPlacement.innerHTML = cardContent; // Creates DOM elements
            }
            else {
                console.error('AJAX request failed: ' + xhr.status + ' - ' + xhr.responseText);
            }
        }
    };
    // Prepare and send AJAX request
    xhr.open('GET', 'panel.php?class=' + className, true);
    xhr.send();
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
}