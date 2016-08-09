<?php

use Aerys\Host;
use Aerys\Request;
use Aerys\Response;
use Aerys\Websocket;
use Amp\File;
use Theory\Builder\Client;
use function Aerys\root;
use function Aerys\router;
use function Aerys\websocket;

function spawn($builder) {
    $index = random_int(0, count($GLOBALS["blocks"]) - 1);
    $block = $GLOBALS["blocks"][$index];

    $builder->exec(sprintf("summon Blaze %s %s %s", $block[0], $block[1] + 2, $block[2]));
}

function get_blocks() {
    return [
        [922, 18, -1324],
        [922, 18, -1325],
        [922, 18, -1326],
        [923, 18, -1322],
        [923, 18, -1323],
        [923, 18, -1324],
        [923, 18, -1325],
        [923, 18, -1326],
        [923, 18, -1327],
        [923, 18, -1328],
        [926, 18, -1330],
        [927, 18, -1330],
        [928, 18, -1330],
        [924, 18, -1329],
        [925, 18, -1329],
        [926, 18, -1329],
        [927, 18, -1329],
        [928, 18, -1329],
        [929, 18, -1329],
        [930, 18, -1329],
        [932, 18, -1326],
        [932, 18, -1325],
        [932, 18, -1324],
        [931, 18, -1328],
        [931, 18, -1327],
        [931, 18, -1326],
        [931, 18, -1325],
        [931, 18, -1324],
        [931, 18, -1323],
        [931, 18, -1322],
        [928, 18, -1320],
        [927, 18, -1320],
        [926, 18, -1320],
        [930, 18, -1321],
        [929, 18, -1321],
        [928, 18, -1321],
        [927, 18, -1321],
        [926, 18, -1321],
        [925, 18, -1321],
        [924, 18, -1321],
        [924, 18, -1322],
        [924, 18, -1323],
        [924, 18, -1324],
        [924, 18, -1325],
        [924, 18, -1326],
        [924, 18, -1327],
        [924, 18, -1328],
        [925, 18, -1322],
        [925, 18, -1323],
        [925, 18, -1324],
        [925, 18, -1325],
        [925, 18, -1326],
        [925, 18, -1327],
        [925, 18, -1328],
        [926, 18, -1322],
        [926, 18, -1323],
        [926, 18, -1324],
        [926, 18, -1325],
        [926, 18, -1326],
        [926, 18, -1327],
        [926, 18, -1328],
        [927, 18, -1322],
        [927, 18, -1323],
        [927, 18, -1324],
        [927, 18, -1325],
        [927, 18, -1326],
        [927, 18, -1327],
        [927, 18, -1328],
        [928, 18, -1322],
        [928, 18, -1323],
        [928, 18, -1324],
        [928, 18, -1325],
        [928, 18, -1326],
        [928, 18, -1327],
        [928, 18, -1328],
        [929, 18, -1322],
        [929, 18, -1323],
        [929, 18, -1324],
        [929, 18, -1325],
        [929, 18, -1326],
        [929, 18, -1327],
        [929, 18, -1328],
        [930, 18, -1322],
        [930, 18, -1323],
        [930, 18, -1324],
        [930, 18, -1325],
        [930, 18, -1326],
        [930, 18, -1327],
        [930, 18, -1328],
    ];
}

function remove_block() {
    $builder = $GLOBALS["builder"];

    if (count($GLOBALS["blocks"]) == 0) {
        return;
    }

    $index = random_int(0, count($GLOBALS["blocks"]) - 1);
    $block = array_slice($GLOBALS["blocks"], $index, 1);

    $builder->exec(sprintf(
        "setblock %s %s %s air",
        $block[0][0], $block[0][1], $block[0][2]
    ));
}

function regenerate($blush = "cobblestone") {
    $builder = $GLOBALS["builder"];

    $blocks = [
        [922, 18, -1324, 922, 18, -1326],
        [923, 18, -1322, 923, 18, -1328],
        [926, 18, -1330, 928, 18, -1330],
        [924, 18, -1329, 930, 18, -1329],
        [932, 18, -1326, 932, 18, -1324],
        [931, 18, -1328, 931, 18, -1322],
        [928, 18, -1320, 926, 18, -1320],
        [930, 18, -1321, 924, 18, -1321],
        [924, 18, -1322, 930, 18, -1328],
    ];

    foreach ($blocks as $block) {
        $builder->exec(sprintf(
            "fill %s %s %s %s %s %s %s",
            $block[0], $block[1], $block[2],
            $block[3], $block[4], $block[5],
            $blush
        ));
    }
}

const AERYS_OPTIONS = [
    "keepAliveTimeout" => 60,
];

$GLOBALS["builder"] = new Client("127.0.0.1", 25575, "hello");

$wrapper = websocket(
    $GLOBALS["websocket"] = new class implements Aerys\Websocket
    {
        public function onStart(Websocket\Endpoint $endpoint)
        {
            $GLOBALS["endpoint"] = $endpoint;
        }

        public function onHandshake(Request $request, Response $response)
        {
            // TODO
        }

        public function onOpen(int $client_id, $handshake_data)
        {
            // TODO

            $GLOBALS["client_id"] = $client_id;
        }

        public function onData(int $client_id, Websocket\Message $message)
        {
            $GLOBALS["client_id"] = $client_id;

            $endpoint = $GLOBALS["endpoint"];
            $builder = $GLOBALS["builder"];
            $command = yield $message;

            if ($command == "script") {
                $endpoint->send($client_id, json_encode([
                    "type" => "script",
                    "text" => yield File\get(__DIR__ . "/templates/main.js"),
                ]));
            }

            if ($command == "fail") {
                $GLOBALS["seconds_left"] = $GLOBALS["seconds_default"];

                $builder->exec('title @a title {"text": "Test failed!", "color": "red"}');

                spawn($builder);
            }

            if ($command == "pass") {
                $builder->exec('title @a title {"text": "Test passed!", "color": "green"}');
                $builder->exec("kill @e[type=Blaze]");

                $GLOBALS["survived"] = true;

                Amp\once(function() use ($builder) {
                    $builder->exec("tp @a 922 5 -1328");
                }, 1000);
            }

            if ($command == "key") {
                $GLOBALS["keypress_count"]++;

                if ($GLOBALS["keypress_count"] >= $GLOBALS["keypress_threshold"]) {
                    remove_block();

                    $GLOBALS["keypress_count"] = $GLOBALS["multiplier"] * $GLOBALS["keypress_handicap"];
                }
            }
        }

        public function onClose(int $client_id, int $code, string $reason)
        {
            // TODO
        }

        public function onStop()
        {
            // TODO
        }
    }
);

$GLOBALS["place"] = "spawn";

$GLOBALS["multiplier"] = 0;
$GLOBALS["survived"] = false;
$GLOBALS["spawned"] = false;

$GLOBALS["seconds_left"] = 15;
$GLOBALS["seconds_default"] = 30;
$GLOBALS["seconds_handicap"] = 5;
$GLOBALS["seconds_min"] = 10;

$GLOBALS["keypress_threshold"] = 20;
$GLOBALS["keypress_count"] = 0;
$GLOBALS["keypress_handicap"] = 2;

$GLOBALS["builder"]->exec("/title @a time 20 100 20");

$GLOBALS["blocks"] = get_blocks();

// Amp\repeat(function() {
//     exec(sprintf("cd %s; git pull &", __DIR__));
//
//     $GLOBALS["challenges"] = json_decode(yield File\get(__DIR__ . "/questions.json"));
//
//     printf("%s question(s)\n", count($GLOBALS["challenges"]));
// }, 10000);

$GLOBALS["challenges"] = json_decode(file_get_contents(__DIR__ . "/questions.json"));

Amp\repeat(function() {


    $builder = $GLOBALS["builder"];

    if (isset($GLOBALS["endpoint"])) {
        $endpoint = $GLOBALS["endpoint"];
    }

    if (empty($endpoint)) {
        return;
    }

    if (isset($GLOBALS["client_id"])) {
        $client_id = $GLOBALS["client_id"];
    }

    if (empty($client_id)) {
        return;
    }

    $result = $builder->exec("/testfor @a[x=929,y=4,z=-1319,r=3]");

    if (stristr($result, "Found")) {
        if (!$GLOBALS["started"]) {
            $builder->exec("/tp @a[x=929,y=4,z=-1319,r=3] 928 19 -1322");

            $GLOBALS["seconds_left"] = max($GLOBALS["seconds_min"], $GLOBALS["seconds_default"] - ($GLOBALS["multiplier"] * $GLOBALS["seconds_handicap"]));

            $index = random_int(0, count($GLOBALS["challenges"]) - 1);
            $challenge = $GLOBALS["challenges"][$index];

            $endpoint->send($client_id, json_encode([
                "type" => "intro",
                "text" => $challenge->intro,
            ]));

            $endpoint->send($client_id, json_encode([
                "type" => "example",
                "text" => $challenge->example,
            ]));

            $endpoint->send($client_id, json_encode([
                "type" => "inputs",
                "text" => $challenge->inputs,
            ]));

            $endpoint->send($client_id, json_encode([
                "type" => "outputs",
                "text" => $challenge->outputs,
            ]));

            $endpoint->send($client_id, json_encode([
                "type" => "arena",
            ]));

            $GLOBALS["place"] = "arena";

            $GLOBALS["survived"] = false;
            $GLOBALS["spawned"] = false;
            $GLOBALS["started"] = true;
        }
    }

    $result = $builder->exec("/testfor @a[x=922,y=4,z=-1328,r=3]");

    if (stristr($result, "Found")) {

        if (!$GLOBALS["spawned"]) {
            $builder->exec("/kill @e[type=Blaze]");

            if ($GLOBALS["survived"]) {
                $endpoint->send($client_id, json_encode([
                    "type" => "survived",
                ]));

                $GLOBALS["multiplier"]++;
            } else {
                $endpoint->send($client_id, json_encode([
                    "type" => "died",
                ]));

                $GLOBALS["multiplier"] = 0;
            }

            $GLOBALS["blocks"] = get_blocks();
            regenerate();

            $GLOBALS["place"] = "spawn";

            $endpoint->send($client_id, json_encode([
                "type" => "spawn",
            ]));

            $GLOBALS["spawned"] = true;
            $GLOBALS["started"] = false;
        }
    }

    if ($GLOBALS["place"] == "arena") {
        $GLOBALS["seconds_left"]--;

        $builder->exec('title @a title {"text": ""}');
        $builder->exec('title @a subtitle {"text": "' . $GLOBALS["seconds_left"] . ' seconds..."}');

        if ($GLOBALS["seconds_left"] == 0) {
            spawn($builder);

            $GLOBALS["seconds_left"] = max($GLOBALS["seconds_min"], $GLOBALS["seconds_default"] - ($GLOBALS["multiplier"] * $GLOBALS["seconds_handicap"]));
        }
    }
}, 1000);

$router = router()
    ->get("/", function(Request $request, Response $response) {
        $response->end(yield File\get(__DIR__ . "/templates/index.html"));
    });

$root = root($path = __DIR__ . "/public");

$fallback = function(Request $request, Response $response) {
    $response->end(yield File\get(__DIR__ . "/templates/not-found.html"));
};

$router->get("/socket", $wrapper);

(new Host)
    ->expose("*", 8080)
    ->use($router)
    ->use($root)
    ->use($fallback);
