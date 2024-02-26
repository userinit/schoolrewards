// Load profile as soon as DOM loads
document.addEventListener("DOMContentLoaded", function() {
    showProfile();
})

function showProfile() {
    document.getElementById('profile').style.display = 'block';
    document.getElementById('settings').style.display = 'none';
    document.getElementById('management').style.display = 'none';
    fetch("http://localhost/digistamp/dashboard.php?item=profile")
    .then(response => {
        if (!response.ok) {
            throw new Error("Network response was not ok.");
        }
        if (response.headers.get('Content-Type').includes('application/json')) {
            return response.json();
        }
    })
    .then(data => {
        if (typeof data === "object" && data !== null) {
            if ("fullname" in data && "username" in data) {
                // Extract and display
                var fullname = data.fullname;
                var username = data.username;
                profileText = document.getElementById("profileText");
                profileText.innerHTML = '';
                var profileContent = '';
                profileContent += `<p><span class="label">Username:</span> ${username}</p>`;
                profileContent += `<p><span class="label">Name:</span> ${fullname}</p>`;
                profileText.innerHTML = profileContent;
            }
        }
    })
    .catch(error => {
        console.error("Error:", error);
    });
}
function showSettings() {
    document.getElementById('settings').style.display = 'block';
    document.getElementById('profile').style.display = 'none';
    document.getElementById('management').style.display = 'none';
}

function showManagement() {
    document.getElementById('management').style.display = 'block';
    document.getElementById('settings').style.display = 'none';
    document.getElementById('profile').style.display = 'none';
}

// First confirmation for danger zone action
function confirmOne(action) {
    document.getElementById('dz').style.display = 'flex';
    let proceed = document.getElementById('proceed');
    document.getElementById('dangerText').innerHTML = "Are you sure you want to proceed?";
    proceed.onclick = function(event) {
        confirmTwo(action);
        event.stopPropagation();
    }
}

// Second confirmation for danger zone section
function confirmTwo(action) {
    if (action === "yearUp") {
        let textContent = "Are you sure you want to move all years up?<br>This deletes year 11 and 13.";
        document.getElementById('dangerText').innerHTML = textContent;
    }
    else {
        let textContent = "Once again: are you sure you want to proceed?"
        document.getElementById('dangerText').innerHTML = textContent
    }
    let proceed = document.getElementById('proceed');
    proceed.onclick = function(event) {
        dangerzone(action);
        event.stopPropagation();
    }
}

function dangerzone(action) {
    document.getElementById('dz').style.display = 'none';
    document.getElementById('dzModal').style.display = 'flex';
    let dzContainer = document.getElementById("dzContainer");
    dzContainer.innerHTML = '';
    let content = '';
    if (action === "yearUp") {
        content += `<h2>WARNING: Moving years up deletes year 11 and year 13!</h2>`;
        content += `<button id="moveUpBtn" class="yes" onclick="moveYearsUp()">Move Years Up</button>`;
        content += `<button class="no" onclick='cancelAction()'>Cancel</button>`;
        dzContainer.innerHTML = content;
    }
    else if (action === "delUser") {
        content += `<h2>Students or staff?</h2>`;
        content += `<button id='rmStudents' onclick="delUser('students')">Students</button>`;
        content += `<button id='rmStaff' onclick="delUser('staff')">Staff</button>`;
        dzContainer.innerHTML = content;
    }
    else if (action === "editClass") {
        content += `<div class="formContainer">`;
        content += `<h2>Change Class</h2>`;
        content += `<form method='post' enctype='multipart/form-data' id="editClassForm">`;
        content += `<div class="form-group">`;
        content += `<label for="file">Import CSV:</label>`;
        content += `<input type="file" id="file" name="file" accept=".csv" required>`;
        content += `</div><button onclick="editClass()">Upload</button></form></div>`;
        dzContainer.innerHTML = content;
    }
}

// Function in control of removing the display of danger zone modals
function cancelAction() {
    document.getElementById('dz').style.display = 'none';
    document.getElementById('dzModal').style.display = 'none';
    // Changes button options back to default
}

// Function in control of moving years up
function moveYearsUp() {
    fetch("http://localhost/digistamp/dashboard.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            moveYearsUp: true,
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error("Network response was not ok.");
        }
        if (response.headers.get("Content-Type").includes("application/json")) {
            return response.json();
        }
    })
    .then(data => {
        if (typeof data === "object" && data !== "null") {
            // display response
        }
    })
    .catch(error => {
        console.error("Error:", error);
    });
}

// Function in control of fetching items from database
function fetchItems() {

}

function delUser(type) {

}

function editClass() {
    // Gets form data when form is uploaded
    let fileInput = document.getElementById("file");
    let formData = new FormData();
    formData.append('file', fileInput.files[0])
    fetch("http://localhost/digistamp/dashboard.php", {
        method: "POST",
        body: formData
    })
    .then(response => {
        if (response.headers.get("Content-Type").includes("application/json")) {
            return response.json();
        }
    })
    .then(data => {
        if (typeof data === "object" && data !== null) {
            let dzContainer = document.getElementById('dzContainer');
            // Removes all child elements from DOM (which is the update class section)
            while (dzContainer.firstChild) {
                dzContainer.removeChild(dzContainer.firstChild);
            }
            let content = '';
            let keysToCheck = ['success', 'username', 'fullname', 'startClass', 'endClass', 'errors'];
            content += "<table><tr><th>Success<th><th>Username</th><th>Full Name</th><th>Old Class</th><th>New Class</th><th>Errors</th></tr>";
            // Iterate over keys extracting objects
            Object.keys(data).forEach(key => {
                if (keysToCheck.every(key => key in data)) {
                    let success = data.success;
                    let username = data.username;
                    let fullname = data.fullname;
                    let startClass = data.startClass;
                    let endClass = data.endClass;
                    let errors = data.errors;
                    content += `<tr><td>${success}</td><td>${username}</td><td>${fullname}</td><td>${startClass}</td><td>${endClass}</td><td>${errors}</tr>`;
                }
            });
            dzContainer.innerHTML = content;
        }
    })
    .catch(error => {
        console.error("Error:", error);
    });
}