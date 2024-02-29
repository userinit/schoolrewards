document.addEventListener("DOMContentLoaded", function() {
    fetchStampLogs();
})

function fetchStampLogs() {
    document.getElementById('auditLogs').style.display = 'none';
    document.getElementById('stampLogs').style.display = 'table';
    fetch("http://localhost/digistamp/admin.php?stamps")
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
            const tbody = document.getElementsByTagName('tbody')[0];
            let content = '';
            const keysToCheck = ['id', 'date', 'time', 'teacher', 'student', 'stamps']; // change if you want to unify date and time
            // Extracts keys from first array
            let i = 0;
            for (let key in data) {
                if (data.hasOwnProperty(key)) {
                    // Extracts associative array
                    let associative = data[key];
                    // Extracts array from array
                    row = associative[`key${i}`];
                    i++;
                    // Checks if each key is there and if it is, then extract each item
                    if (keysToCheck.every(key => key in row)) {
                        let id = row.id;
                        let date = row.date;
                        let time = row.time;
                        let teacher = row.teacher;
                        let student = row.student;
                        let stamps = row.stamps;
                        content += `<tr>`;
                        content += `<td>${id}</td>`;
                        content += `<td>${date}</td>`;
                        content += `<td>${time}</td>`;
                        content += `<td>${teacher}</td>`;
                        content += `<td>${student}</td>`;
                        content += `<td>${stamps}</td>`;
                        content += `</tr>`;
                    }
                    tbody.innerHTML = content;
                }
            }
        }
    })
    .catch(error => {
        console.error("Error:", error);
    });
}

function fetchAuditLogs() {
    document.getElementById("stampLogs").style.display = 'none';
    document.getElementById("auditLogs").style.display = 'table';
    fetch("http://localhost/digistamp/admin.php?audit")
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
            const tbody = document.getElementsByTagName('tbody')[1];
            let content = '';
            const keysToCheck = ['id', 'date', 'time', 'fullname', 'username', 'action']; // change if you want to unify date and time
            // Extracts keys from first array
            let i = 0;
            for (let key in data) {
                if (data.hasOwnProperty(key)) {
                    // Extracts associative array
                    let associative = data[key];
                    // Extracts array from array
                    row = associative[`key${i}`];
                    i++;
                    // Checks if each key is there and if it is, then extract each item
                    if (keysToCheck.every(key => key in row)) {
                        let id = row.id;
                        let date = row.date;
                        let time = row.time;
                        let fullname = row.fullname;
                        let username = row.username;
                        let action = row.action;
                        content += `<tr>`;
                        content += `<td>${id}</td>`;
                        content += `<td>${date}</td>`;
                        content += `<td>${time}</td>`;
                        content += `<td>${fullname}</td>`;
                        content += `<td>${username}</td>`;
                        content += `<td>${action}</td>`;
                        content += `</tr>`;
                    }
                    tbody.innerHTML = content;
                    console.log(content);
                }
            }
        }
    })
    .catch(error => {
        console.error("Error:", error);
    })
}

function back() {
    window.location.href = "http://localhost/digistamp/admin.html";
}