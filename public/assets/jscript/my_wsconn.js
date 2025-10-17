var conn;
var contador = 0;

jQuery(document).ready(function () {
  jQuery("#stat_server").removeClass("d-none");
  conectaWs();

  function conectaWs() {
    conn = new WebSocket("wss://delivery.taisho.com.br/ws");
    conn.onopen = function (e) {
      console.log("Conexão estabelecida com o Servidor");
      jQuery("#stat_server").addClass("text-success");
      jQuery("#stat_server").removeClass("text-danger");
      keepAlive();
    };
  }

  function keepAlive() {
    timerId = setInterval(function () {
      var data = JSON.stringify({ msg: "Ativo" });
      if (conn.readyState != WebSocket.OPEN) {
        // Fecha e reconecta
        conn.close();
      } else {
        contador++;
        // if (contador > 20) {
        //     location.reload();
        // }
        console.log("Ativo " + contador);
        console.log("Timer " + timerId);
        conn.send(data);
      }
    }, 20000);
  }

  conn.onmessage = function (e) {
    // console.log(e.data);
    clearTimeout(timerId);
    var data = JSON.parse(e.data);
    console.log("Recebi " + data.msg);
    if (data.msg != "Ativo" && data.msg != "Mobile Conectou") {
      contador = 0;
      if (data.msg == "Saida") {
        var currentUrl = window.location.href; // Obtém a URL completa
        var pageName = currentUrl.split("/").pop().split("?")[0]; // Obtém apenas o nome da página
        if (pageName === "EstSaida") {
          location.reload(true);
        }
      }
      if (data.msg == "Entrada") {
        var currentUrl = window.location.href; // Obtém a URL completa
        var pageName = currentUrl.split("/").pop().split("?")[0]; // Obtém apenas o nome da página
        if (pageName === "EstEntrada") {
          location.reload(true);
        }
      }
      if (data.msg == "Resumo") {
        var currentUrl = window.location.href; // Obtém a URL completa
        var pageName = currentUrl.split("/").pop().split("?")[0]; // Obtém apenas o nome da página
        if (pageName === "DashCompras") {
          carrega_dash_compras();
        }
      }
    }
    keepAlive();
  };

  conn.onclose = function (e) {
    jQuery("#stat_server").removeClass("text-success");
    jQuery("#stat_server").addClass("text-danger");
    jQuery("#stat_server").prop("title", "Servidor Desconectado");
    console.log("Fechou Conexão");
    clearTimeout(timerId);
    location.reload();
  };

  conn.onerror = function (err) {
    console.error("Socket encountered error: ", err.message, "Closing socket");
  };
});
