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

const AERYS_OPTIONS = [
    "keepAliveTimeout" => 60,
];

$builder = $GLOBALS["builder"] = new Client("127.0.0.1", 25575, "hello");

$wrapper = websocket(
    $websocket = new class implements Aerys\Websocket
    {
        public $endpoint;
        public $clientId;

        public function onStart(Websocket\Endpoint $endpoint)
        {
            $this->endpoint = $endpoint;
        }

        public function onHandshake(Request $request, Response $response)
        {
            // TODO
        }

        public function onOpen(int $clientId, $handshakeData)
        {
            // TODO

            $this->clientId = $clientId;
        }

        public function onData(int $clientId, Websocket\Message $message)
        {
            $builder = $GLOBALS["builder"];
            $command = yield $message;

            if ($command == "fail") {
                $builder->exec("summon Blaze 928 25 -1330");
            }

            if ($command == "pass") {
                $builder->exec("kill @e[type=Blaze]");
            }

            if ($command == "key") {
                // TODO
            }
        }

        public function onClose(int $clientId, int $code, string $reason)
        {
            // TODO
        }

        public function onStop()
        {
            // TODO
        }
    }
);

Amp\repeat(function() use ($builder, $websocket) {
    $result = $builder->exec("/testfor @a[x=929,y=4,z=-1319,r=3]");

    if (stristr($result, "Found")) {
        $builder->exec("/tp @a[x=929,y=4,z=-1319,r=3] 928 19 -1322");

        $websocket->endpoint->send($websocket->clientId, "arena");
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
