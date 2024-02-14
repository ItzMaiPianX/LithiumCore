<?php

declare(strict_types=1);

namespace owonico\task;


use maipian\webhook\{Message, Webhook};
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class DiscordTask extends AsyncTask
{
    /** @var Webhook */
    protected $webhook;
    /** @var Message */
    protected $message;

    public function __construct(Webhook $webhook, Message $message)
    {
        $this->webhook = $webhook;
        $this->message = $message;
    }

    public function onRun(): void
    {
        $ch = curl_init($this->webhook->getURL());
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->message));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        $this->setResult([curl_exec($ch), curl_getinfo($ch, CURLINFO_RESPONSE_CODE)]);
        curl_close($ch);
    }

    public function onCompletion(): void
    {
        $response = $this->getResult();
        if (!in_array($response[1], [200, 204])) {
        }
    }
}
