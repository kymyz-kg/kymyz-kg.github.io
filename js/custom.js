(function ($) {

    function getInfo(elem){
        var container = document.getElementById("infoSection");
        var x = elem.parentNode.textContent.trim();
        if (document.getElementById(x)) {
            container.innerHTML = "";
           container.appendChild(document.getElementById(x).cloneNode(true));
        } else {
            container.innerHTML = "<h4 class=\"text-center\" style=\"padding: 30px\">Толук маалымат үчүн биз менен байланышыңыз.</h4>";
        }
    }

    $(document).ready(function(){
        $(".orderBtn").click(function(){
            $("#infoModal").modal('hide');
            window.location.hash = '#contact';
        });

        $(".btn-skype").click(function(){ // using nodeValue depends on whitespace - (B)
            document.getElementById("infoModalLabel").innerHTML = this.parentNode.textContent;
            getInfo(this);
            $("#infoModal").modal();
        });
    });

}(jQuery));

