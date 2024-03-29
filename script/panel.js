// globally declaring important variables
var selectedYear;
var classNames;
var amountOfClasses;
var classType; // tutor or class
var className; // tutor/class name
var maxStamps = 1500; // change as needed -- edit in HTML too
var stage = 0; // Needed for back button to know what stage to go to
var stampValidation = 0; // 0 = don't limit to 9, 1 = limit to 9 stamps

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
    // Change 'back to dashboard' to back on button
    document.getElementById('back').innerHTML = "Back";
    stage = 1;
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
            stage = 2;
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
            var container = document.getElementById("buttonContainer");
            container.style.padding = "0px";
            container.style.margin = "0px";
            studentMatrix = data;
            var cardPlacement = document.getElementById("cardContainer");
            cardPlacement.innerHTML = '';
            var cardContent = '';
            // Iterates over items in array, changing associative arrays into normal arrays
            for (var i = 0; i < studentMatrix.length; i++) {
                var surname = studentMatrix[i][0];
                var forename = studentMatrix[i][1];
                var username = studentMatrix[i][2];
                var stamps = studentMatrix[i][3];
                var fullname = forename + " " + surname;
                var backwardName = surname + ", " + forename;

                // Place DOM elements
                cardContent += `<div class="card"><div class="card-content">`;
                cardContent += `<h4>Name: ${backwardName}</h4>`;
                cardContent += `<p>Username: ${username}</p>`;
                cardContent += `<p user="${username}">Stamps: ${stamps}</p>`;
                cardContent += `<div id="addStampsWrapper"><button onclick="event.stopPropagation(); showOverlay('${fullname}', '${username}')">Add stamps</button></div>`;
                cardContent += '</div></div>';
            }
            cardPlacement.innerHTML = cardContent;
            stage = 3;
        }
    })
    .catch(error => {
        console.error("Error:", error);
    })
}

// Function that makes overlay disappear
function cancelOverlay() {
    var overlay = document.getElementById('overlay');
    overlay.style.display = 'none';
    // clears input field value
    stampsInput = document.getElementById('stampsInput');
    stampsInput.value = '';
}

// Function that shows the overlay
// First confirmation for stamps
function showOverlay(fullname, username) {
    var overlay = document.getElementById('overlay');
    overlay.style.display = 'flex';
    document.getElementById('stampsText').innerHTML = "Enter stamps for "+fullname+": ";
    var addStamps = document.getElementById("addStamps");
    // Adds onclick args for confirmStamps()
    addStamps.onclick = function (event) {
        event.stopPropagation();
        confirmStamps(fullname, username);
    };
    document.addEventListener('mousedown', function(event) {
        var overlayContent = document.getElementById("overlay-content");
        var targetElement = event.target;
        // Check if click happened outside the box
        if (targetElement != overlayContent && !overlayContent.contains(targetElement)) {
            cancelOverlay();
        }
    });
}

// Function that removes inputs dynamically if they are under/over range
function validateStamps() {
    if (stampValidation) {
        var stampsInput = document.getElementById("stampsInput");
        stampsInput.addEventListener("input", function() {
            let val = this.value.trim();
            // Checks with regex to see if it's an integer
            if (val !== "" && /^[0-9]+$/.test(val)) {
                var intValue = parseInt(val);
                if (intValue < 1 || intValue > maxStamps) {
                    this.value = val.slice(0, -1);
                }
            }
            else {
                this.value = '';
            }
        });
    }
}
document.addEventListener('DOMContentLoaded', function() {
    //validateStamps();
})

function closeModalBox() {
    modalBox = document.getElementById("modalBox");
    modalBox.style.display = "none";
}

// Second confirmation for stamps
function confirmStamps(fullname, username) {
    var stampIncrease = document.getElementById("stampsInput").value.trim();
    // Verify whether a valid stamp count has been sent
    var stampsInput = document.getElementById("stampsInput");
    if (stampsInput.checkValidity()) {
        var intValue = parseInt(stampIncrease);
        if (intValue > 0 && intValue <= maxStamps) {
            // singular and plural
            if (intValue === 1) {
                var text = "stamp";
            }
            else {
                var text = "stamps";
            }
            // Makes overlay disappear
            cancelOverlay();
            // makes modal box appear
            var modal = document.getElementById("modalBox");
            modal.style.display = 'flex';
            // adds text to modal box
            modalText = document.getElementById("modalResponse");
            modalText.innerHTML = `Do you want to add ${stampIncrease} ${text} for ${fullname}?`;
            // adds function with username argument to send stamps
            secondConfirm = document.getElementById("secondConfirm");
            secondConfirm.onclick = function() {
                sendStamps(username, stampIncrease);
                closeModalBox();
            }
            // Adds an event listener to disable the box if it is clicked outside of
            document.addEventListener('mousedown', function(event) {
                modalContent = document.getElementById("modalContent");
                var targetElement = event.target;
                // Check if click happened outside the box
                if (targetElement != modalContent && !modalContent.contains(targetElement)) {
                    closeModalBox();
                }
            });
        }
    }
}

function closeResponseBox() {
    responseBox = document.getElementById("responseBox");
    responseBox.style.display = 'none';
}

// Sends stamps
function sendStamps(username, stampIncrease) {
    fetch("http://localhost/digistamp/panel.php", {
        method: "POST",
        body: JSON.stringify({
            username: username,
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
            // Make response box visible
            responseBox = document.getElementById("responseBox");
            responseBox.style.display = 'flex';
            // Adds an event listener to disable the box if it is clicked outside of
            document.addEventListener('mousedown', function(event) {
                responseContent = document.getElementById("responseContent");
                var targetElement = event.target;
                // Check if click happened outside the box
                if (targetElement != responseContent && !responseContent.contains(targetElement)) {
                    closeResponseBox();
                }
            });
            // Edit response text in response box
            responseText = document.getElementById("responseBoxResponse");
            if ('success' in data) {
                responseText.innerHTML = data.success;
                // Dynamically edit the stamp count for the affected person
                var element = document.querySelector(`[user="${username}"]`);
                var content = element.innerHTML;
                var currentStamps = content.slice(8);
                var newStamps = parseInt(currentStamps) + parseInt(stampIncrease);
                element.innerHTML = `Stamps: ${newStamps}`;
            }
            else if ('failure' in data) {
                responseText.innerHTML = data.failure;
            }
            else {
                responseText.innerHTML = "Failed to load response ):";
            }
        }
    })
    .catch(error => {
        console.error("Error:", error);
    });
}

function back() {
    var container = document.getElementById("buttonContainer");
    container.innerHTML = ''; // clears previous items
    var content = '';
    switch (stage) {
        case 0:
            window.location.href = "http://localhost/digistamp/teacher.html";
            break;
        case 1:
            // Replace 'back' button text
            document.getElementById("back").innerHTML = 'Back to dashboard';
            for (var i = 10; i <= 13; i++) {
                content += `<button class="year-button" onclick="showClasses('${i}')">Year ${i}</button>`;
            }
            container.innerHTML = content;
            stage--;
            break;
        case 2:
            // Replace 'back to dashboard' with 'back'
            document.getElementById("back").innerHTML = 'Back';
            content += `<button class="tutorClassButton" onclick="classOrTutor('Classes')">Classes</button>`;
            content += `<button class="tutorClassButton" onclick="classOrTutor('Tutors')">Tutors</button>`;
            container.innerHTML = content;
            stage--;
            break;
        case 3:
            cards = document.getElementById("cardContainer");
            cards.innerHTML = '';
            for (var i = 0; i < classNames.length; i++) {
                content += `<button class="getStudentsButton" onclick="fetchStudents('`+classNames[i]+`')">`+classNames[i]+`</button>`;
            }
            container.innerHTML = content;
            // replace original padding and margin
            container = document.getElementById("buttonContainer");
            container.style.padding = "20px";
            container.style.margin = "50px auto";
            stage--;
            break;
    }
};