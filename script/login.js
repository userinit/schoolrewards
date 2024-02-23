function submitForm() {
    var username = document.getElementById("username").value;
    var password = document.getElementById("password").value;

    fetch('http://localhost/digistamp/login.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify ({
            username: username,
            password: password
        })
    })
    .then(response => {
        console.log(response);
        if (!response.ok) {
            throw new error('Network response failed');
        }
        var isJson = response.headers.get('Content-Type').includes('application/json');
        if (response.redirected) {
            const location = response.url;
            if (location !== null && location !== '') {
                window.location.href = location;
            }
        }
        else if (isJson) {
            return response.json();
        }
    })
    .then(data => {
        if (typeof data === 'object' && data !== null) {
            console.log("Parsed JSON:", data);
            if (data && data.hasOwnProperty('invalid')) {
                var invalidpass = data.invalid;
                document.getElementById('ajaxContainer').innerHTML = `<p>${invalidpass}</p>`;
                var loginContainerElements = document.getElementsByClassName('login-container');
                for (var i = 0; i < loginContainerElements.length; i++) {
                    loginContainerElements[i].style.padding = "40px 40px 0px 40px";
                }
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}