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
            if (xhr.status === 200) {
                // Handle successful response
                try {
                    var responseData = JSON.parse(xhr.responseText);
                    console.log("Parsed response data:", responseData);
                    // displays only if password is invalid
                    var invalidpass = responseData.invalid
                    document.getElementById('ajaxContainer').innerHTML = `<p>${invalidpass}</p>`;
                    var loginContainerElements = document.getElementsByClassName('login-container');
                    for (var i = 0; i < loginContainerElements.length; i++) {
                        loginContainerElements[i].style.padding = "40px 40px 0px 40px";
                    }
                }
                catch (error) {
                    console.error("Error parsing JSON: ", error);
                }
            } 
            else {
                // Handle error
                console.error("Error: ", xhr.status);
            }
        }
    };
    xhr.send(jsonData); // Send the form data to the server
}