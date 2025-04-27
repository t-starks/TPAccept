<?php
namespace TStark\TPAcceptForm;

use pocketmine\player\Player;
use pocketmine\utils\Config;

class TPAManager {
    private Main $plugin;
    private array $pendingRequests = [];
    private array $toggleStatus = [];

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function sendRequest(Player $sender, Player $target): void {
        $this->pendingRequests[$target->getName()][$sender->getName()] = time() + $this->plugin->getConfig()->get("settings")["request_timeout"];
    }

    public function hasPendingRequest(Player $player): bool {
        return isset($this->pendingRequests[$player->getName()]);
    }

    public function getPendingRequests(Player $player): array {
        return $this->pendingRequests[$player->getName()] ?? [];
    }

    public function setToggle(Player $player, bool $status): void {
        $this->toggleStatus[$player->getName()] = $status;
    }

    public function canReceiveRequest(Player $player): bool {
        return $this->toggleStatus[$player->getName()] ?? true;
    }
}