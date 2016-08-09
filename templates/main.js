var $died = $(".died");
var $survived = $(".survived");
var $intro = $(".intro");
var $coder = $(".coder");
var $buttons = $(".buttons");

var intro;
var example;
var inputs;
var outputs;

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
        $buttons.removeClass("hidden");
    }

    if (data.type == "spawn") {
        $intro.addClass("hidden");
        $coder.addClass("hidden");
        $buttons.addClass("hidden");
        editor.setValue("");
    }

    if (data.type == "died") {
        $died.removeClass("hidden");
    }

    if (data.type == "survived") {
        $survived.removeClass("hidden");
    }

    if (data.type == "intro") {
        $intro.text(data.text);
        $intro.removeClass("hidden");
    }

    if (data.type == "example") {
        example = data.text;
        editor.setValue(data.text, 1);
    }

    if (data.type == "inputs") {
        inputs = data.text;
    }

    if (data.type == "outputs") {
        outputs = data.text;
    }
});

var $body = $(document.body);

$body.on("click", ".reset", function() {
    editor.setValue(example, 1);
});

$body.on("click", ".try", function() {
    var value;
    var command;

    for (var i = 0; i < inputs.length; i++) {
        try {
            command = "(function(){ var fn = " + editor.getValue() + "; return fn(..." + JSON.stringify(inputs[i]) + "); }());";

            console.log(command, inputs[i], outputs[i]);
            result = eval(command);
        } catch (e) {
            socket.send("fail");
        }

        if (outputs[i] !== value) {
            socket.send("fail");
            return;
        }
    }

    socket.send("pass");
});
