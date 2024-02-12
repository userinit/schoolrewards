var xhr = new XMLHttpRequest();
var url = 'your_api_endpoint_here';

xhr.open('GET', url, true);
xhr.setRequestHeader('Content-Type', 'application/json');
xhr.onreadystatechange = function () {
    if (xhr.readyState === XMLHttpRequest.DONE) {
        if (xhr.status === 200) {
            var data = JSON.parse(xhr.responseText);
            var studentInfoDiv = document.getElementById('studentInfo');
            studentInfoDiv.innerHTML = '';
            var studentInfoHTML = '';

            // Constructs "stamps" HTML element
            document.getElementById('rewardPoints').innerHTML = '<p><span class="label">Stamps:</span> ' + data.stamps + '</p>';

            // Constructs "personal info" HTML element line by line
            studentInfoHTML += '<div class="student-info">';
            studentInfoHTML += '<p><span class="label">Username:</span> ' + data.username + '</p>';
            studentInfoHTML += '<p><span class="label">Name:</span> ' + data.name + '</p>';
            studentInfoHTML += '<p><span class="label">Year:</span> ' + data.year + '</p>';
            studentInfoHTML += '<p><span class="label">Tutor:</span> ' + data.tutor + '</p>';
            studentInfoHTML += '</div>';


            // Appends HTML to div
            studentInfoDiv.innerHTML = studentInfoHTML;

            // Handle the JSON response here
            console.log(jsonResponse);
        } else {
            // Handle errors here
            console.error('Error:', xhr.status);
        }
    }
};

xhr.send();