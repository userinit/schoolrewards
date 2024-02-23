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
})

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
                stampsElement = document.getElementById("stampsText");
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
                    totalStamps = parseInt(stamps);
                    stampsElement.innerHTML += `<p><span class="label">Next goal:</span> ${textGoal} (${stamps}/${goal})</p>`
                    stampsElement.style.margin = "0 0 10px";
                }
                // Place percentage number into the bar text
                percentText = document.getElementById('percentage');
                percentText.innerHTML = `${roundedDown}%`;
                // Style the bar
                filled = document.getElementById("filled");
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
                overlay = document.getElementById("stampsOverlay");
                overlay.style.display = 'flex';
                // Makes text for stamps appear
                stampsText = document.getElementById("stampsText");
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
        modalContent = document.getElementById("modalContent");
        var targetElement = event.target;
        // Check if click happened outside the box
        if (targetElement != modalContent && !modalContent.contains(targetElement)) {
            cancelLogout();
        }
    })
}
