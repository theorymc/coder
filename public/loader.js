var socket = new WebSocket("ws://127.0.0.1:8080/socket");

socket.addEventListener("message", function(e) {
    var data = JSON.parse(e.data);

    if (data.type === "script") {
        eval(data.text);
    }
});

socket.addEventListener("open", function(e) {
    socket.send("script");
});
