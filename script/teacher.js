function profile() {
    document.getElementById('profile').style.display = 'block';
    document.getElementById('settings').style.display = 'none';
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

document.addEventListener('DOMContentLoaded', function() {
    profile();
})

function panel() {
    window.location.href = "panel.html";
}

function settings() {
    document.getElementById('settings').style.display = 'block';
    document.getElementById('profile').style.display = 'none';
}