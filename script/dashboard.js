const minChars = 10 // Password requirements. Change as needed
const maxChars = 30

function removeCookie(name) {
    document.cookie = name + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
}

function showProfile() {
    document.getElementById('stamps').style.display = 'none';
    document.getElementById('settings').style.display = 'none';
    document.getElementById('profile').style.display = 'block';
    // Fetch data about profile
    fetch("http://localhost/digistamp/dashboard.php?item=profile")
    .then(response => {
        if (!response.ok) {
            throw new Error("Network response was not ok.");
        }
        var isJson = response.headers.get('Content-Type').includes('application/json');
        if (isJson) {
            return response.json();
        }
    })
    .then(data => {
        if (typeof data === 'object' && data !== null) {
            const keysToCheck = ['username', 'class', 'year', 'tutor', 'name'];
            var profileElement = document.getElementById('profileText');
            profileElement.innerHTML = '';
            if (keysToCheck.every(key => key in data)) {
                // All keys exist - extract keys
                var username = data.username;
                var studentClass = data.class;
                var year = data.year;
                var tutor = data.tutor;
                var fullname = data.name;
                // Place student info in second section
                var profileContent = '';
                profileContent += `<p><span class="label">Username:</span> <span class="data">${username}</span></p>`;
                profileContent += `<p id="nameElement"><span class="label">Full name:</span> <span class="data">${fullname}</span></p>`;
                profileContent += `<p><span class="label">Year:</span> <span class="data">${year}</span></p>`;
                profileContent += `<p><span class="label">Tutor:</span> <span class="data">${tutor}</span></p>`;
                profileContent += `<p><span class="label">Class:</span> <span class="data">${studentClass}</span></p>`;
                profileElement.innerHTML = profileContent;
            }
            else {
                profileElement.innerHTML = "Couldn't load profile info ):";
            }
        }
    })
    .catch(error => {
        console.error("Error:", error);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    showProfile();
});

function showStamps() {
    document.getElementById('profile').style.display = 'none';
    document.getElementById('settings').style.display = 'none';
    document.getElementById('stamps').style.display = 'block';
    fetch("http://localhost/digistamp/dashboard.php?item=stamps")
    .then(response => {
        var isJson = response.headers.get('Content-Type').includes('application/json');
        if (isJson) {
            return response.json();
        }
    })
    .then(data => {
        if (typeof data === 'object' && data !== null) {
            if ('stamps' in data) {
                var stamps = data.stamps;
                var stampsElement = document.getElementById("stampsText");
                stampsElement.innerHTML = `<p><span class='label'>Stamps: </span><span class='data'>${stamps}</span></p>`;
                var intStamps = parseInt(stamps);
                // Goals: Bronze (250), silver (500), gold (750), platinum (1000), diamond (1500), double diamond (3000)
                if (intStamps >= 0 && intStamps < 250) {
                    var goalDifference = 250;
                    var goal = 250;
                    var textGoal = "Bronze";
                    var color = "#D18A42"; // bronze
                }
                else if (intStamps >= 250 && intStamps < 500) {
                    var goalDifference = 250; // difference between the milestone just hit and the next one
                    var goal = 500;
                    var textGoal = "Silver";
                    var color = "#B0C4DE"; // silver
                    intStamps -= 250; // intStamps decreased because we want the score after milestone
                }
                else if (intStamps >= 500 && intStamps < 750) {
                    var goalDifference = 250;
                    var goal = 750;
                    var textGoal = "Gold";
                    var color = "#FFD700"; // gold
                    intStamps -= 500;
                }
                else if (intStamps >= 750 && intStamps < 1000) {
                    var goalDifference = 250;
                    var goal = 1000;
                    var textGoal = "Platinum";
                    var color = "#E5E5B7"; // light khaki
                    intStamps -= 750;
                }
                else if (intStamps >= 1000 && intStamps < 1500) {
                    var goalDifference = 500;
                    var goal = 1500;
                    var textGoal = "Diamond";
                    var color = "#66CCFF"; // blue
                    intStamps -= 1000;
                }
                else if (intStamps >= 1500 && intStamps < 3000) {
                    var goalDifference = 1500;
                    var goal = 3000;
                    var textGoal = "Double Diamond";
                    var color = "#00FFFF"; // cyan
                    intStamps -= 1500;
                }
                else {
                    // All goals finished
                    var textGoal = null;
                    var color = "#BC75FF"; // light purple
                    var roundedDown = 100;
                    var percentageCompletion = 100;
                }
                if (textGoal) {
                    // Calculates percentages for next goal
                    var percentageCompletion = (intStamps / goalDifference) * 100;
                    var roundedDown = Math.floor(percentageCompletion);

                    // displays next goal
                    var totalStamps = parseInt(stamps);
                    stampsElement.innerHTML += `<p><span class="label">Next goal:</span> ${textGoal} (${stamps}/${goal})</p>`
                    stampsElement.style.margin = "0 0 10px";
                }
                // Place percentage number into the bar text
                percentText = document.getElementById('percentage');
                percentText.innerHTML = `${roundedDown}%`;
                // Style the bar
                var filled = document.getElementById("filled");
                filled.style.backgroundColor = color;
                filled.style.width = percentageCompletion + "%";
            }
        }
    })
    .catch(error => {
        console.error("Error:", error);
    })
}

function showSettings() {
    document.getElementById('profile').style.display = 'none';
    document.getElementById('stamps').style.display = 'none';
    document.getElementById('settings').style.display = 'block';
}

function stampsOverlay() {
    fetch("http://localhost/digistamp/dashboard.php?item=stamps")
    .then(response => {
        if (!response.ok) {
            throw new Error("Network response was not ok.");
        }
        var isJson = response.headers.get('Content-Type').includes('application/json');
        if (isJson) {
            return response.json();
        }
    })
    .then(data => {
        if (typeof data === 'object' && data !== null) {
            if ('stamps' in data) {
                var stamps = data.stamps;
                // Makes overlay appear
                var overlay = document.getElementById("stampsOverlay");
                overlay.style.display = 'flex';
                // Makes text for stamps appear
                var stampsText = document.getElementById("stampsText");
                stampsText.innerHTML = `${stamps} stamps`;
            }
        }
    })
    .catch(error => {
        console.error("Error:", error);
    });
}

function cancelLogout() {
    document.getElementById('logoutModal').style.display = 'none';
}

function logout() {
    fetch("http://localhost/digistamp/dashboard.php", {
        method: "POST",
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify ({
            'logout': true
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error("Network response was not ok.");
        }
        console.log(response);
        var isJson = response.headers.get('Content-Type').includes('application/json');
        if (isJson) {
            return response.json();
        }
        else if (response.redirected) {
            // They have been redirected, clear session cookie first
            removeCookie("PHPSESSID");
            const location = response.url;
            if (location !== null && location !== '') {
                window.location.href = location;
            }
        }
    })
    .then(data => {
        if (typeof data === "object" && data !== null) {
            logoutModal = document.getElementById('logoutModal');
        }
    })
    .catch(error => {
        console.error("Error:", error);
    })
}

function logoutModal() {
    document.getElementById('logoutModal').style.display = 'flex';
    document.addEventListener('click', function(event) {
        var modalContent = document.getElementById("modalContent");
        var targetElement = event.target;
        // Check if click happened outside the box
        if (targetElement != modalContent && !modalContent.contains(targetElement)) {
            cancelLogout();
        }
    });
}

// Will put an overlay asking you if you want to exit 
function closeModalConf() {
    document.getElementById('confirmClose').style.display = 'flex';
}

// Will close the modal that confirms closing
function stayOnCurrentScreen() {
    document.getElementById('confirmClose').style.display = 'none';
}

function closePassModal() {
    // Remove values from all input fields
    document.getElementById('old').value = '';
    document.getElementById('new').value = '';
    document.getElementById('conf').value = '';
    // Remove display on modal
    document.getElementById('passwordModal').style.display = 'none';
    document.getElementById('passwordResponse').innerHTML = '';
    // Remove display on confirmation modal
    document.getElementById('confirmClose').style.display = 'none';
}

function showPasswordModal() {
    var passwordModal = document.getElementById('passwordModal');
    passwordModal.style.display = 'flex';
    passwordModal.addEventListener('click', function(event) {
        var modalContent = document.getElementById('passwordContainer');
        var targetElement = event.target;
        if (targetElement != modalContent && !modalContent.contains(targetElement)) {
            closeModalConf();
        }
    });
    const passwordForm = document.getElementById("new");
    const advice = document.getElementById("passwordStrength");
    const confirmInput = document.getElementById("conf");
    const confirmLabel = document.getElementById("confLabel");

    var response = '';
    response += "<span class='red'>&#x2715; Lowercase<br>"; // &#x2715; is cross
    response += "&#x2715; Uppercase<br>";
    response += "&#x2715; Number<br>";
    response += "&#x2715; Symbol<br>";
    response += `&#x2715; ${minChars}-${maxChars} Characters<br></span>`;
    advice.innerHTML = response;

    document.addEventListener('input', function() {
        // cross is &#x2715;, tick is &checkmark;
        response = '';
        if (passwordForm.value) {
            var password = passwordForm.value;
            // removes non-ASCII so no emojis in your password :)
            password.replace(/[a-zA-Z0-9!@£#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/, "");
            passwordForm.value = password;
        }
        else {
            var password = '';
        }
        // RegEx
        if (password.match(/[a-z]/, password)) {
            response += "<span class='green'>&checkmark; Lowercase</span><br>";
            var hasLower = true;
        }
        else {
            response += "<span class='red'>&#x2715; Lowercase</span><br>";
            var hasLower = false;
        }
        if (password.match(/[A-Z]/, password)) {
            response += "<span class='green'>&checkmark; Uppercase</span><br>";
            var hasUpper = true;
        }
        else {
            response += "<span class='red'>&#x2715; Uppercase</span><br>";
            var hasUpper = false;
        }
        if (password.match(/[0-9]/, password)) {
            response += "<span class='green'>&checkmark; Number</span><br>";
            var hasNum = true;
        }
        else {
            response += "<span class='red'>&#x2715; Number</span><br>";
            var hasNum = false;
        }
        if (password.match(/[!@£#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/, password)) {
            response += "<span class='green'>&checkmark; Symbol</span><br>";
            var hasSym = true;
        }
        else {
            response += "<span class='red'>&#x2715; Symbol</span><br>";
            var hasSym = false;
        }
        if (password.length >= minChars && password.length <= maxChars) {
            response += `<span class='green'>&checkmark; ${minChars}-${maxChars} Characters</span><br>`;
            var goodLength = true;
        }
        else {
            response += `<span class='red'>&#x2715; ${minChars}-${maxChars} Characters</span><br>`;
            var goodLength = false;
        }
        if (hasLower && hasUpper && hasNum && hasSym && goodLength) {
            confirmLabel.style.display = 'flex';
            confirmInput.style.display = 'flex';
            if (confirmInput.value && confirmInput.value === password) {
                response += "<span class='green'>&checkmark; Passwords Match</span>";
                document.getElementById('submit').style.display = 'block';
            }
            else {
                response += "<span class='red'>&#x2715; Passwords Match</span>";
                document.getElementById('submit').style.display = 'none';
            }
        }
        else {
            confirmLabel.style.display = 'none';
            confirmInput.style.display = 'none';
            confirmInput.value = '';
        }
        advice.innerHTML = response;
    })
}

function submitForm() {
    var oldField = document.getElementById("old");
    var newField = document.getElementById("new");
    var confField = document.getElementById("conf");
    var responseElement = document.getElementById("passwordResponse");
    // Check whether fields are truthy
    if (oldField.value && newField.value && confField.value) {
        var oldPass = oldField.value;
        var newPass = newField.value;
        var confPass = confField.value;
        if (newPass === confPass) {
            fetch("http://localhost/digistamp/dashboard.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    "old": oldPass,
                    "new": newPass,
                    "confirm": confPass
                })
            })
            .then (response => {
                if (!response.ok) {
                    document.getElementById("passwordResponse").innerHTML = "Failed to get a response";
                    console.error("Network response was not ok.")
                }
                isJson = response.headers.get('Content-Type').includes('application/json');
                if (isJson) {
                    return response.json();
                }
            })
            .then(data => {
                var fullOpacity = document.getElementsByClassName('fullOpacity')[0];
                fullOpacity.style.transition = 'none';
                responseElement.classList.remove('fadeout');
                if (typeof data === "object" && data !== null) {
                    if ('success' in data) {
                        responseElement.innerHTML = data.success;
                    }
                    else if ('failure' in data) {
                        responseElement.innerHTML = data.failure;
                    }
                    else {
                        responseElement.innerHTML = "Failed to get a response";
                    }
                    setTimeout(function() {
                        responseElement.style.transition = 'opacity 3s ease';
                        responseElement.classList.add('fadeout');
                        responseElement.addEventListener('transitionend', function(event) {
                        if (event.propertyName === 'opacity') {
                            responseElement.innerHTML = '';
                            if ('success' in data) {

                            }
                        }
                        });
                    }, 4000);
                }
            })
            .catch(error => {
                console.error("Error:", error);
            });
        }
    }
}