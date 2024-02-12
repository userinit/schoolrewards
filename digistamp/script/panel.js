// globally declaring
var selectedYear;
var classNames;
var amountOfClasses;

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
    var selectedType = type;
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
                var classlist = document.getElementById('class-list');
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
    xhr.open('GET', 'get_classes.php?year=' + selectedYear + '&type=' + selectedType, true); // type meaning tutor/class
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
                
            }
            else {
                console.error('AJAX request failed: ' + xhr.status + ' - ' + xhr.responseText);
            }
        }
    }
}