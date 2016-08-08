var $died = $(".died");
var $survived = $(".survived");
var $coder = $(".coder");

var intro;
var example;
var solution;

var editor = ace.edit("editor");
editor.setTheme("ace/theme/tomorrow_night_eighties");
editor.getSession().setMode("ace/mode/javascript");

var textarea = editor.textInput.getElement();
var $textarea = $(textarea);

$textarea.on("keypress", function() {
    socket.send("key");
});

socket.addEventListener("message", function(e) {
    var data = JSON.parse(e.data);

    if (data.type == "arena") {
        $died.addClass("hidden");
        $survived.addClass("hidden");
        $coder.removeClass("hidden");
    }

    if (data.type == "spawn") {
        $coder.addClass("hidden");
        editor.setValue("");
    }

    if (data.type == "died") {
        $died.removeClass("hidden");
    }

    if (data.type == "survived") {
        $survived.removeClass("hidden");
    }
});

var $body = $(document.body);

$body.on("click", ".test-fail", function() {
    socket.send("fail");
});

$body.on("click", ".test-pass", function() {
    socket.send("pass");
});

$body.on("click", ".try", function() {
    if (editor.getValue() === solution) {
        socket.send("pass");
    } else {
        socket.send("fail");
    }
});
