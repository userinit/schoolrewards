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
                let fullname = data.fullname;
                let username = data.username;
                profileText = document.getElementById("profileText");
                profileText.innerHTML = '';
                let profileContent = '';
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
    let dz = document.getElementById('dz');
    let dzCont = document.getElementById('dzContent');
    dz.style.display = 'flex';
    let proceed = document.getElementById('proceed');
    document.getElementById('dangerText').innerHTML = "Are you sure you want to proceed?";
    proceed.onclick = function(event) {
        confirmTwo(action);
        event.stopPropagation();
    }
    dz.addEventListener("click", function clickHandlerOne(event) {
        let targetElement = event.target;
        if (targetElement !== dzCont && !dzCont.contains(targetElement)) {
            dz.style.display = 'none';
        }
    });
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
        content += `<button class="selection" id='rmStudents' onclick="event.stopPropagation(); delUser('students')">Students</button>`;
        content += `<button class="selection" id='rmStaff' onclick="event.stopPropagation(); delUser('staff')">Staff</button>`;
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
    let modal = document.getElementById("dzModal");
    modal.addEventListener("click", function contHandler(event) {
        let targetElement = event.target;
        if (targetElement != dzContainer && !dzContainer.contains(targetElement)) {
            modal.style.display = 'none';
        }
    });
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

function delUser(type) {
    function delRequest(username, role) {
        fetch("http://localhost/digistamp/dashboard.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                delUser: true,
                username: username,
                role: role
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
            if (typeof data === "object" && data !== null) {
                if (document.getElementById("delUserCont")) {
                    let container = document.getElementById("delUserCont");
                    if (data.success) {
                        container.innerHTML = `<p style="font-size: 22px;">${data.success}</p>`;
                    }
                    else if (data.failure) {
                        container.innerHTML = `<p style="font-size: 22px;">${data.failure}</p>`;
                    }
                    let delModal = document.getElementById("delUserModal");
                    // Event listener to be able to be able to click off of the modal
                    delModal.addEventListener("click", function clickHandler(event) {
                        event.stopPropagation();
                        let targetElement = event.target;
                        if (targetElement != container && !container.contains(targetElement)) {
                            delModal.remove();
                        }
                    });
                }
            }
        })
        .catch(error => {
            console.error("Error:", error);
        });
    }
    let dzContainer = document.getElementById("dzContainer");
    dzContainer.innerHTML = '';
    if (type === "staff") {
        fetch("http://localhost/digistamp/dashboard.php?staff")
        .then(response => {
            if (!response.ok) {
                throw new Error("Network response was not ok.");
            }
            if (response.headers.get("Content-Type").includes("application/json")) {
                return response.json();
            }
        })
        .then(data => {
            if (typeof data === "object" && data !== null) {
                let content = '<table id="staffTable"><thead><tr><th>Full Name</th><th>Username</th><th>Role</th></thead></tr><tbody>';
                let keysToCheck = ["fullname", "username", "role"];
                Object.values(data).forEach(item => {
                    if (keysToCheck.every(key => key in item)) {
                        let username = item.username;
                        let fullname = item.fullname;
                        let role = item.role;
                        content += `<tr><td>${fullname}</td><td>${username}</td><td>${role}</td></tr>`;
                    }
                });
                content += '</tbody></table>';
                dzContainer.innerHTML = content;

                // Event listener for if they click the button
                document.querySelectorAll('#staffTable tbody tr').forEach(row => {
                    row.addEventListener('click', function(event) {
                        let clickedRow = event.target.parentElement;
                        let fullname = clickedRow.getElementsByTagName('td')[0].innerText;
                        let username = clickedRow.getElementsByTagName('td')[1].innerText;
                        mainContent = document.getElementById("main-content");
                        content = '';
                        content += `<div class="modal" id="delUserModal"><div id="delUserCont">`;
                        content += `<h3 style="display: flex;">Delete user ${fullname}?</h3>`;
                        content += `<button id="delUsr" class="yes">Yes</button>`;
                        content += `<button class="no" id="rmModal">No</button></div></div>`;
                        mainContent.insertAdjacentHTML('beforeend', content); // stops event listeners from disabling unlike innerHTML
                        document.getElementById('delUserModal').style.display = 'flex';
                        let delUsr = document.getElementById('delUsr');
                        let rmModal = document.getElementById('rmModal');
                        delUsr.onclick = function() {
                            delRequest(username, 'staff');
                        }
                        rmModal.onclick = function() {
                            document.getElementById("delUserModal").remove();
                        }
                    });
                });
            }
        })
        .catch(error => {
            console.error("Error:", error);
        });
    }
    else if (type === "students") {
        fetch("http://localhost/digistamp/dashboard.php?students")
        .then(response => {
            if (!response.ok) {
                throw new Error("Network response was not ok");
            }
            if (response.headers.get("Content-Type").includes("application/json")) {
                return response.json();
            }
        })
        .then(data => { 
            if (typeof data === "object" && data !== null) {
                let keysToCheck = ['surname', 'forename', 'username', 'class', 'tutor', 'year'];
                let content = '<table id="studentTable"><thead><tr><th>Full Name</th><th>Username</th><th>Year</th><th>Tutor</th><th>Class</th></tr></thead>';
                content += `<tbody>`;
                Object.values(data).forEach(item => {
                    if (keysToCheck.every(key => key in item)) {
                        let username = item.username;
                        let schoolClass = item.class;
                        let tutor = item.tutor;
                        let year = item.year;
                        let forename = item.forename;
                        let surname = item.surname;
                        let fullname = surname + ', ' + forename;
                        content += `<tr><td>${fullname}</td><td>${username}</td><td>${year}</td><td>${tutor}</td><td>${schoolClass}</td></tr>`; 
                    }
                });
                content += `</tbody></table>`;
                dzContainer.innerHTML = content;
                // Event listener to delete individual rows from tables
                document.querySelectorAll('#studentTable tbody tr').forEach(row => {
                    row.addEventListener('click', function(event) {
                        let clickedRow = event.target.parentElement;
                        let fullname = clickedRow.getElementsByTagName('td')[0].innerText;
                        let parts = fullname.split(/,\s*/);
                        let forwardName = parts[1] + " " + parts[0];
                        let username = clickedRow.getElementsByTagName('td')[1].innerText;
                        mainContent = document.getElementById("main-content");
                        content = '';
                        content += `<div class="modal" id="delUserModal"><div id="delUserCont">`;
                        content += `<h3 style="display: flex;">Delete user ${forwardName}?</h3>`;
                        content += `<button id="delUsr" class="yes">Yes</button>`;
                        content += `<button class="no" id="rmModal">No</button>`;
                        mainContent.insertAdjacentHTML("beforeend", content); // stops event listeners from disabling unlike innerHTML
                        document.getElementById('delUserModal').style.display = 'flex';
                        document.getElementById('delUsr').onclick = function() {
                            delRequest(username, 'students');
                        }
                        document.getElementById('rmModal').onclick = function() {
                            document.getElementById('delUserModal').remove();
                        }
                    });
                });
            }
        })
        .catch(error => {
            console.error("Error:", error);
        });
    }
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
                    content += `<tr><td>${success}</td><td>${username}</td><td>${fullname}</td><td>${startClass}</td>`;
                    content += `<td>${endClass}</td><td>${errors}</tr>`;
                }
            });
            content += `</table>`;
            dzContainer.innerHTML = content;
        }
    })
    .catch(error => {
        console.error("Error:", error);
    });
}