// globally declaring important variables
var selectedYear;
var classNames;
var amountOfClasses;
var classType; // tutor or class
var className; // tutor/class name
var username;
var fullname; // Forename Surname

// Year buttons -> Tutor/class button
function showClasses(year) {
    selectedYear = year;
    // Remove old buttons
    document.querySelectorAll(".year-button").forEach(foo => foo.remove());
    // Add new buttons
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
            document.querySelectorAll(".getStudentsButton").forEach(foo => foo.remove());
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
    fetch("http://localhost/digistamp/panel.php?year=" + selectedYear + "&type=" + classType + "&class=" + className)
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
            document.querySelectorAll(".getStudentsButton").forEach(foo => foo.remove());
            // Removes padding on container element
            var declaration = document.styleSheets[0].cssRules[1].style; // Accesses .container in CSS
            declaration.removeProperty("padding");
            declaration.removeProperty("margin");
            studentMatrix = data;
            var cardPlacement = document.getElementById("card-container");
            cardPlacement.innerHTML = '';
            var cardContent = '';
            // Iterates over items in array, changing associative arrays into normal arrays
            for (var i = 0; i < studentMatrix.length; i++) {
                // items without var have already been globally declared
                var surname = studentMatrix[i][0];
                var forename = studentMatrix[i][1];
                username = studentMatrix[i][2];
                var stamps = studentMatrix[i][3];
                fullname = forename + " " + surname;
                var backwardName = surname + ", " + forename;

                // Place DOM elements
                cardContent += `<div class="card"><div class="card-content">`;
                cardContent += `<h4>Name: ${backwardName}</h4>`;
                cardContent += `<p>Username: ${username}</p>`;
                cardContent += `<p user="${username}">Stamps: ${stamps}</p>`;
                cardContent += `<button class="addStamps" onclick="showOverlay('${fullname}', '${username}')">Add stamps</button>`;
                cardContent += '</div></div>';
            }
            cardPlacement.innerHTML = cardContent;
        }
    })
    .catch(error => {
        console.error("Error:", error);
    })
}

// Function that shows the overlay
function showOverlay(name, userId) {
    var overlay = document.getElementById('overlay');
    overlay.style.display = 'flex';
    document.getElementById('stampsText').innerHTML = "Enter stamps for "+name+": ";
    var addStamps = document.getElementById("addStamps");
    // Adds onclick attribute to sendStamps() button with args full name and username
    addStamps.onclick = function() {
        sendStamps(userId);
    }
}

// Function that makes overlay disappear
function cancelOverlay() {
    var overlay = document.getElementById('overlay');
    overlay.style.display = 'none';
}

// Function that removes inputs dynamically if they are under/over range
function validateStamps() {
    var stampsInput = document.getElementById("stampsInput");
    stampsInput.addEventListener("input", function() {
        value = this.value.trim();
        if (value !== "") {
            var intValue = parseInt(value);
            if (isNaN(intValue) || intValue < 1 || intValue > 9) {
                this.value = value.slice(0, -1);
            }
        }
    });
}
document.addEventListener('DOMContentLoaded', function() {
    validateStamps();
})

// Function that sends stamps
function sendStamps(userId) {
    var stampsInput = document.getElementById("stampsInput");
    if (stampsInput.checkValidity()) {
        // If input is valid, continue by sending POST request to panel.php
        var stampIncrease = stampsInput.value;
        fetch("http://localhost/digistamp/panel.php", {
            method: "POST",
            body: JSON.stringify({
                username: userId,
                stamps: stampIncrease
            }),
            headers: {
                "Content-Type": "application/json"
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error("Network response was not ok.");
            }
            const isJson = response.headers.get('Content-Type').includes('application/json');
            if (isJson) {
                return response.json();
            }
        })
        .then(data => {
            if (typeof data === 'object' && data !== null) {
                // Remove overlay
                overlay = document.getElementById("overlay");
                overlay.style.display = 'none';
                // Display modal box
                modal = document.getElementById("modalBox");
                modal.style.display = 'flex';
                // Edit response text in modal box
                modalText = document.getElementById("modalResponse");
                if ('success' in data) {
                    modalText.innerHTML = data.success;
                    // Dynamically edit the stamp count for the affected person
                    var element = document.querySelector(`[user="${userId}"]`);
                    var content = element.innerHTML;
                    var currentStamps = content.slice(8);
                    console.log(currentStamps);
                    var newStamps = parseInt(currentStamps) + parseInt(stampIncrease);
                    element.innerHTML = `Stamps: ${newStamps}`;
                }
                else if ('failure' in data) {
                    modalText.innerHTML = data.failure;
                }
            }
        })
        .catch(error => {
            console.error("Error:", error);
        });
    }
}
function closeModalBox() {
    modalBox = document.getElementById("modalBox");
    modalBox.style.display = "none";
}