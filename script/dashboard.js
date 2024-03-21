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
                var intStamps = parseInt(stamps);
                // Goals: Bronze (200), silver (450), gold (750), platinum (1000), diamond (1500), double diamond (3000)
                if (intStamps >= 0 && intStamps < 200) {
                    var goalDifference = 200;
                    var goal = 200;
                    var textGoal = "Bronze";
                    var color = "#D18A42"; // bronze
                    var nextReward = "Certificate";
                }
                else if (intStamps >= 200 && intStamps < 450) {
                    var goalDifference = 250; // difference between the milestone just hit and the next one
                    var goal = 450;
                    var textGoal = "Silver";
                    var color = "#B0C4DE"; // silver
                    intStamps -= 200; // intStamps decreased because we want the score after milestone
                    var nextReward = "Certificate &amp; grab bag";
                }
                else if (intStamps >= 450 && intStamps < 750) {
                    var goalDifference = 300;
                    var goal = 750;
                    var textGoal = "Gold";
                    var color = "#FFD700"; // gold
                    intStamps -= 450;
                    var nextReward = "Certificate &amp; £5 canteen spend";
                }
                else if (intStamps >= 750 && intStamps < 1000) {
                    var goalDifference = 250;
                    var goal = 1000;
                    var textGoal = "Platinum";
                    var color = "#E5E5B7"; // light khaki
                    intStamps -= 750;
                    var nextReward = "Certificate &amp; £10 voucher";
                }
                else if (intStamps >= 1000 && intStamps < 1500) {
                    var goalDifference = 500;
                    var goal = 1500;
                    var textGoal = "Diamond";
                    var color = "#66CCFF"; // blue
                    intStamps -= 1000;
                    var nextReward = "Certificate &amp; £15 voucher";
                }
                else if (intStamps >= 1500 && intStamps < 3000) {
                    var goalDifference = 1500;
                    var goal = 3000;
                    var textGoal = "Double Diamond";
                    var color = "#00FFFF"; // cyan
                    intStamps -= 1500;
                    var nextReward = "Certificate, £30 voucher &amp; free ticket to prom";
                }
                else {
                    // All goals finished
                    var textGoal = null;
                    var color = "#BC75FF"; // light purple
                    var roundedDown = 100;
                    var percentageCompletion = 100;
                    var nextReward = "None";
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
                let percentText = document.getElementById('percentage');
                percentText.innerHTML = `${roundedDown}%`;
                // Style the bar
                let filled = document.getElementById("filled");
                filled.style.backgroundColor = color;
                filled.style.width = percentageCompletion + "%";
                let content = '';
                content += `<p><span class='label'>Stamps: </span><span class='data'>${stamps}</span></p>`
                content += `<p><span class='label'>Next Reward: </span><span class='data'>${nextReward}</span></p>`;
                stampsElement.innerHTML = content;
            }
        }
    })
    .catch(error => {
        console.error("Error:", error);
    })
}

// Removes display on leaderboard element
function cancelLeaderboard() {
    document.getElementById('lbOverlay').style.display = 'none';
}

function leaderboardTypes() {
    document.getElementById('lbOverlay').style.display = 'flex';
    document.getElementById('lbSelection').style.display = 'grid';
    const lbOverlay = document.getElementById("lbOverlay");
    const lbCont = document.getElementById("lbContainer");
    const table = document.getElementById("lbTable");
    lbOverlay.addEventListener("click", function(event) {
        let targetElement = event.target;
        if (targetElement !== lbCont && !lbCont.contains(targetElement)) {
            lbOverlay.style.display = 'none';
            table.innerHTML = '';
        }
    });
}

function showLeaderboard(type) {
    document.getElementById('lbSelection').style.display = 'none';
    document.getElementById('lbTable').innerHTML = '';
    let possibleTypes = ["school", "year", "tutor"];
    if (possibleTypes.includes(type)) {
        fetch("http://localhost/digistamp/leaderboard.php?leaderboard="+type)
        .then(response => {
            if (!response.ok) {
                throw new Error("Network response was not ok.");
            }
            let isJson = response.headers.get("Content-Type").includes("application/json");
            if (isJson) {
                return response.json();
            }
        })
        .then(data => {
            if (typeof data === "object" && data !== null) {
                const table = document.getElementById("lbTable");
                if (type === "school") {
                    var content = '<table><thead><tr><th>Rank</th><th>Name</th><th>Stamps</th><th class="tutorLb">Tutor</th><th class="yearLb">Year</th></tr></thead><tbody>';
                }
                else if (type === "year") {
                    var content = '<table><thead><tr><th>Rank</th><th>Name</th><th>Stamps</th><th class="tutorLb">Tutor</th></tr></thead><tbody>';
                }
                else if (type === "tutor") {
                    var content = '<table><thead><tr><th>Rank</th><th>Name</th><th>Stamps</th></tr></thead><tbody>';
                }
                else {
                    var content = "Leaderboard type not found ):";
                }
                if (data.success) {
                    let assoc = data.success;
                    if (type === "school") {
                        let keysToCheck = ["rank", "name", "stamps", "tutor", "year", "selected"];
                        for (let i = 0; i < assoc.length; i++) {
                            if (keysToCheck.every(key => key in assoc[i])) {
                                let rank = assoc[i].rank;
                                let name = assoc[i].name;
                                let stamps = assoc[i].stamps;
                                let tutor = assoc[i].tutor;
                                let year = assoc[i].year;
                                let selected = assoc[i].selected;
                                let rankNoHash = rank.slice(1);
                                if (selected && rankNoHash > 3) {
                                    content += `<tr id="sel"><td>${rank}</td><td>${name}</td><td>${stamps}</td><td class="tutorLb">${tutor}</td><td class="yearLb">${year}</td></tr>`;
                                }
                                else if (rankNoHash == 1) {
                                    content += `<tr id="gold"><td>${rank}</td><td>${name}</td><td>${stamps}</td><td class="tutorLb">${tutor}</td><td class="yearLb">${year}</td></tr>`;
                                }
                                else if (rankNoHash == 2) {
                                    content += `<tr id="silver"><td>${rank}</td><td>${name}</td><td>${stamps}</td><td class="tutorLb">${tutor}</td><td class="yearLb">${year}</td></tr>`;
                                }
                                else if (rankNoHash == 3) {
                                    content += `<tr id="bronze"><td>${rank}</td><td>${name}</td><td>${stamps}</td><td class="tutorLb">${tutor}</td><td class="yearLb">${year}</td></tr>`;
                                }
                                else {
                                    content += `<tr><td>${rank}</td><td>${name}</td><td>${stamps}</td><td class="tutorLb">${tutor}</td><td class="yearLb">${year}</td></tr>`;
                                }
                            }
                        }
                    }
                    else if (type === "year") {
                        let keysToCheck = ["rank", "name", "stamps", "tutor", "selected"];
                        for (let i = 0; i < assoc.length; i++) {
                            if (keysToCheck.every(key => key in assoc[i])) {
                                let rank = assoc[i].rank;
                                let name = assoc[i].name;
                                let stamps = assoc[i].stamps;
                                let tutor = assoc[i].tutor;
                                let selected = assoc[i].selected;
                                let rankNoHash = rank.slice(1);
                                if (selected) {
                                    content += `<tr id="sel"><td>${rank}</td><td>${name}</td><td>${stamps}</td><td class="tutorLb">${tutor}</td></tr>`;
                                }
                                else if (rankNoHash == 1) {
                                    content += `<tr id="gold"><td>${rank}</td><td>${name}</td><td>${stamps}</td><td class="tutorLb">${tutor}</td></tr>`;
                                }
                                else if (rankNoHash == 2) {
                                    content += `<tr id="silver"><td>${rank}</td><td>${name}</td><td>${stamps}</td><td class="tutorLb">${tutor}</td></tr>`;
                                }
                                else if (rankNoHash == 3) {
                                    content += `<tr id="bronze"><td>${rank}</td><td>${name}</td><td>${stamps}</td><td class="tutorLb">${tutor}</td></tr>`;
                                }
                                else {
                                    content += `<tr><td>${rank}</td><td>${name}</td><td>${stamps}</td><td class="tutorLb">${tutor}</td></tr>`;
                                }
                            }
                        }
                    }
                    else if (type === "tutor") {
                        let keysToCheck = ["rank", "name", "stamps", "selected"];
                        for (let i = 0; i < assoc.length; i++) {
                            if (keysToCheck.every(key => key in assoc[i])) {
                                let rank = assoc[i].rank;
                                let name = assoc[i].name;
                                let stamps = assoc[i].stamps;
                                let selected = assoc[i].year;
                                let rankNoHash = rank.slice(1);
                                if (selected) {
                                    content += `<tr id="sel"><td>${rank}</td><td>${name}</td><td>${stamps}</td></tr>`;
                                }
                                else if (rankNoHash == 1) {
                                    content += `<tr id="gold"><td>${rank}</td><td>${name}</td><td>${stamps}</td></tr>`;
                                }
                                else if (rankNoHash == 2) {
                                    content += `<tr id="silver"><td>${rank}</td><td>${name}</td><td>${stamps}</td></tr>`;
                                }
                                else if (rankNoHash == 3) {
                                    content += `<tr id="bronze"><td>${rank}</td><td>${name}</td><td>${stamps}</td></tr>`;
                                }
                                else {
                                    content += `<tr><td>${rank}</td><td>${name}</td><td>${stamps}</td></tr>`;
                                }
                            }
                        }
                    }
                    if (content) {
                        content += `</tbody></table>`
                    }
                    table.innerHTML = content;
                }
                else if (data.failure) {
                    // handle failure
                }
            }
        })
        .catch(error => {
            console.error("Error:", error);
        });
    }
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
