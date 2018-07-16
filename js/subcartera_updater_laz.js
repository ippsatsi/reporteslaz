      var myRequest = new XMLHttpRequest();

      function getCarteras(form_number) {
        var x = document.getElementById("cartera"+form_number);
        var num = x.value;
        myRequest.open('GET', 'subcartera.php?cartera='+num,true);
        myRequest.onreadystatechange = function () {
          if (myRequest.readyState === 4) {
            var selectSubCartera = document.getElementById("subcartera"+form_number);
            selectSubCartera.innerHTML = "<option value=\"0\">--seleccione--</option>";
            selectSubCartera.innerHTML += myRequest.responseText;
            console.log(myRequest.responseText);
          }
        };
        myRequest.send();
      }
