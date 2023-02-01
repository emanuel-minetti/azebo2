<?php

namespace Message\Model;

use Laminas\Config\Factory;
use ReturnTypeWillChange;

class Message {
    public string $text;
    public string $variant;
    public function __construct() {
        $config = Factory::fromFile('./../server/config/message.config.php', true);
        $this->text = $config->get('text');
        $this->variant = $config->get('variant');
    }

    #[ReturnTypeWillChange] public function getArrayCopy(): array {
        return [
            'text' => $this->text,
            'variant' => $this->variant,
        ];
    }

    public function __toString()
    {
        return json_encode($this->getArrayCopy());
    }

}