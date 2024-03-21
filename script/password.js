const minChars = 10 // Password requirements. Change as needed
const maxChars = 30

function removeCookie(name) {
    document.cookie = name + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
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
    document.addEventListener('mousedown', function(event) {
        var modalContent = document.getElementById("modalContent");
        var targetElement = event.target;
        // Check if click happened outside the box
        if (targetElement != modalContent && !modalContent.contains(targetElement)) {
            cancelLogout();
        }
    });
}

function closePassModal() {
    // Remove values from all input fields
    document.getElementById('old').value = '';
    document.getElementById('new').value = '';
    document.getElementById('conf').value = '';
    // Remove display on modal
    document.getElementById('passwordModal').style.display = 'none';
    document.getElementById('passwordResponse').innerHTML = '';
}

function showPasswordModal() {
    var passwordModal = document.getElementById('passwordModal');
    passwordModal.style.display = 'flex';
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