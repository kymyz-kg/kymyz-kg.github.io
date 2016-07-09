(function ($) {

    function restoreForm(){
        $("#myModal").modal('hide');
        setTimeout(function(){
            $("#success-alert").hide();
            $("#failure-alert").hide();
            $("#orderComment").hide();
            $("#orderForm").fadeTo(3000, 1);
        }, 500);
    }

    function getInfo(elem){
        var container = document.getElementById("infoSection");
        var x = elem.parentNode.textContent.trim();
        if (document.getElementById(x)) {
            container.innerHTML = "";
            container.appendChild(document.getElementById(x).cloneNode(true));
        } else {
            container.innerHTML = "<h4>Для получения дополнительной информации, пожалуйста, оставьте заявку.</h4>";
        }
    }

    $(document).ready(function(){
        $("#success-alert").hide();
        $("#failure-alert").hide();

        $('#myModal').on('shown.bs.modal', function() {
              $('#uname').focus();
        });
        $('#myModal').on('shown.bs.hide', function() {
              $('#uname').blur();
        });

        $(".orderBtn").click(function(){
            $("#infoModal").modal('hide');
            var x = this.parentNode.childNodes[1].textContent.trim();
            document.getElementById("servId").innerHTML = x;
            if(x.match(/^Другие/)) {
                $("#orderComment").fadeTo(3000, 1);   
            } else {
                $("#orderComment").hide();
            }

            setTimeout($("#myModal").modal(), 500);
        });

        $(".btn-skype").click(function(){ // using nodeValue depends on whitespace - (B)
            //document.getElementById("infoModalLabel").innerHTML = this.parentNode.textContent;
            //document.getElementById("infoModalLabel").innerHTML = "test1";
            //getInfo(this);
            $("#infoModal").modal();
        });

    });

    window.addEventListener("load", function () {
        function validateForm() {
            return true;
        }

        function sendData() {
            var createCORSRequest = function(method, url) {
                var xhr = new XMLHttpRequest();
                if ("withCredentials" in xhr) {
                    // Most browsers.
                    xhr.open(method, url, true);
                } else if (typeof XDomainRequest != "undefined") {
                    // IE8 & IE9
                    xhr = new XDomainRequest();
                    xhr.open(method, url);
                } else {
                    // CORS not supported.
                    xhr = null;
                }
                return xhr;
            };

            var XHR = createCORSRequest("POST", "http://up24.ddns.net:8000");

            // We bind the FormData object and the form element
            var FD  = new FormData(form);
            FD.append("Service", document.getElementById("servId").innerHTML)

                // We define what will happen if the data are successfully sent
                XHR.addEventListener("load", function(event) {
                    if(event.target.responseText == "Success") {
                        $("#orderForm").hide();
                        $("#success-alert").fadeTo(3000, 1);   
                        setTimeout(restoreForm, 4000);
                    } else {
                        $("#orderForm").hide();
                        $("#failure-alert").fadeTo(3000, 1);   
                        setTimeout(restoreForm, 4000);
                    }
                });

            // We define what will happen in case of error
            XHR.addEventListener("error", function(event) {
                $("#failure-alert").fadeTo(3000, 1);   
                setTimeout(restoreForm, 4000);
            });

            // The data sent are the one the user provide in the form
            XHR.send(FD);
        }

        // We need to access the form element
        var form = document.getElementById("orderForm");

        // to takeover its submit event.
        form.addEventListener("submit", function (event) {
            event.preventDefault();
            if(validateForm()){
                sendData();
            }
        });
    });

}(jQuery));

