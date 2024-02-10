var xhr = new XMLHttpRequest();
var method = "GET";
var url = "http://localhost/digistamp/login.php";

/*
xhr.open(method, url, true);
xhr.onreadystatechange = () => {
  if (xhr.readyState === XMLHttpRequest.DONE) {
    var status = xhr.status;
    if (status >= 200 && status < 400) {
        // The request has been completed successfully
        var response = JSON.parse(xhr.responseText);
        var message = response.invalid // "invalid" being the name part of the array in PHP
        var responseParagraph = document.getElementById('responseParagraph');
        responseParagraph.textContent = message;
        console.log(xhr.responseText);
    } else {
        console.error("AJAX Request Failed");
    }
  }
};
xhr.send();*/

xhr.open(method, url, true);
xhr.onreadystatechange = function() {
    if (xhr.readyState === XMLHttpRequest.DONE) {
        if (xhr.status >= 200 && xhr.status < 400) {
            var response = JSON.parse(xhr.responseText);
            var message = response.invalid
            var responseParagraph = document.getElementById('responseParagraph');
            responseParagraph.textContent = message;
            console.log(xhr.responseText);
        } 
        else {
            console.error('AJAX request failed');
        }
    }
}