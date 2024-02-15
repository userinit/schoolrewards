function submitForm() {
    var formData = {
        username: document.getElementById("username").value,
        password: document.getElementById("password").value
    };
    var jsonData = JSON.stringify(formData);
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "login.php", true); // Specify the PHP script to handle the form data

    xhr.setRequestHeader("Content-Type", "application/json");
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            console.log('Checking status (initial):', xhr.status);
            if (xhr.status === 200) {
                console.log("Location: ", xhr.getResponseHeader('Location')); // checks for the location here
                try {
                    if (xhr.responseText != '') {
                        // checks for 'Content-Type: application/json' header
                        var contentType = xhr.getResponseHeader('Content-Type');
                        console.log("Location: ", xhr.getResponseHeader('Location')); // checks for the location here

                        if (contentType && contentType.indexOf('application/json') !== -1) {
                            var responseData = JSON.parse(xhr.responseText);
                            console.log("Parsed response data:", responseData);
                            // displays only if password is invalid
                            if (responseData.hasOwnProperty('invalid')) {
                                var invalidpass = responseData.invalid;
                                document.getElementById('ajaxContainer').innerHTML = `<p>${invalidpass}</p>`;
                                var loginContainerElements = document.getElementsByClassName('login-container');
                                for (var i = 0; i < loginContainerElements.length; i++) {
                                    loginContainerElements[i].style.padding = "40px 40px 0px 40px";
                                }
                            }
                        }
                        else if (xhr.getResponseHeader('Location')) {
                            var redirectUrl = xhr.getResponseHeader('Location');
                            console.log('Redirect URL:', redirectUrl);
                            //window.location.replace(redirectUrl);
                        }
                    }
                }
                catch (error) {
                    console.error("Error parsing JSON: ", error);
                }
            }
            else if (xhr.status >= 300 && xhr.status < 400) {
                console.log('Checking status:', xhr.status);
                var redirectUrl = xhr.getResponseHeader("Location");
                console.log('Redirect URL:', redirectUrl);
                window.location.href = redirectUrl;
            }
            else {
                // Handle error
                console.error("Error: ", xhr.status);
            }
        }
    };
    xhr.send(jsonData); // Send the form data to the server
}