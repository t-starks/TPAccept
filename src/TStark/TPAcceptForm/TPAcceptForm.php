<?php
namespace TStark\TPAcceptForm;

use pocketmine\form\Form;
use pocketmine\player\Player;

class TPAcceptForm implements Form {
    private string $sender;
    private string $location;
    private Main $plugin;

    public function __construct(Main $plugin, string $sender, string $location) {
        $this->plugin = $plugin;
        $this->sender = $sender;
        $this->location = $location;
    }

    public function jsonSerialize(): array {
        $config = $this->plugin->getConfig();
        return [
            "type" => "form",
            "title" => $config->get("form")["title"],
            "content" => str_replace(
                ["{player}", "{world}", "{x}", "{y}", "{z}"],
                [$this->sender, $this->location],
                $config->get("form")["content"]
            ),
            "buttons" => [
                ["text" => $config->get("form")["button_accept"]],
                ["text" => $config->get("form")["button_deny"]]
            ]
        ];
    }

    public function handleResponse(Player $player, $data): void {
        if ($data === 0) {
            $this->plugin->getTPAManager()->acceptRequest($player, $this->sender);
        } else {
            $this->plugin->getTPAManager()->denyRequest($player, $this->sender);
        }
    }
}