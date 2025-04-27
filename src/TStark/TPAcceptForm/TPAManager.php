<?php
namespace TStark\TPAcceptForm;

use pocketmine\player\Player;
use pocketmine\world\Position;

class TPAManager {
    private Main $plugin;
    private array $pendingRequests = [];
    private array $toggleStatus = [];
    private array $cooldowns = [];

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function sendRequest(Player $sender, Player $target): bool {
        $now = time();
        
        // Check cooldown
        if (isset($this->cooldowns[$sender->getName()]) && 
            $this->cooldowns[$sender->getName()] > $now) {
            $remaining = $this->cooldowns[$sender->getName()] - $now;
            $sender->sendMessage(str_replace("{time}", $remaining, 
                "§c[Request] You must wait {time} seconds before sending another request."));
            return false;
        }

        $this->pendingRequests[$target->getName()][$sender->getName()] = [
            'expire' => $now + $this->plugin->getConfig()->get("settings")["request_timeout"],
            'position' => $sender->getPosition()
        ];
        
        // Set cooldown
        $this->cooldowns[$sender->getName()] = $now + $this->plugin->getConfig()->get("settings")["cooldown"];
        
        return true;
    }

    public function hasPendingRequest(Player $player): bool {
        $this->cleanExpiredRequests($player);
        return !empty($this->pendingRequests[$player->getName()]);
    }

    public function getPendingRequests(Player $player): array {
        $this->cleanExpiredRequests($player);
        return $this->pendingRequests[$player->getName()] ?? [];
    }

    public function cancelRequest(Player $player, string $targetName): void {
        unset($this->pendingRequests[$targetName][$player->getName()]);
        $player->sendMessage($this->plugin->getConfig()->get("messages")["tp_cancelled"]);
    }

    public function acceptRequest(Player $player, string $senderName): void {
        $requests = $this->getPendingRequests($player);
        
        if (!isset($requests[$senderName])) {
            $player->sendMessage($this->plugin->getConfig()->get("messages")["error_no_pending"]);
            return;
        }

        $sender = $this->plugin->getServer()->getPlayerExact($senderName);
        if ($sender === null) {
            $player->sendMessage("§c[Request] Player is no longer online.");
            return;
        }

        $position = $requests[$senderName]['position'];
        $player->teleport($position);
        
        $player->sendMessage(str_replace("{player}", $senderName, 
            $this->plugin->getConfig()->get("messages")["tp_accepted"]));
        
        $sender->sendMessage(str_replace("{player}", $player->getName(), 
            $this->plugin->getConfig()->get("messages")["tp_accepted"]));

        unset($this->pendingRequests[$player->getName()][$senderName]);
    }

    public function denyRequest(Player $player, string $senderName): void {
        $sender = $this->plugin->getServer()->getPlayerExact($senderName);
        if ($sender !== null) {
            $sender->sendMessage(str_replace("{player}", $player->getName(), 
                $this->plugin->getConfig()->get("messages")["tp_denied"]));
        }
        
        unset($this->pendingRequests[$player->getName()][$senderName]);
        $player->sendMessage(str_replace("{player}", $senderName, 
            $this->plugin->getConfig()->get("messages")["tp_denied"]));
    }

    public function setToggle(Player $player, bool $status): void {
        $this->toggleStatus[$player->getName()] = $status;
        $message = $status ? "toggle_on" : "toggle_off";
        $player->sendMessage($this->plugin->getConfig()->get("messages")[$message]);
    }

    public function canReceiveRequest(Player $player): bool {
        return $this->toggleStatus[$player->getName()] ?? true;
    }

    private function cleanExpiredRequests(Player $player): void {
        $now = time();
        foreach ($this->pendingRequests[$player->getName()] ?? [] as $senderName => $request) {
            if ($request['expire'] <= $now) {
                unset($this->pendingRequests[$player->getName()][$senderName]);
                $sender = $this->plugin->getServer()->getPlayerExact($senderName);
                if ($sender !== null) {
                    $sender->sendMessage($this->plugin->getConfig()->get("messages")["tp_expired"]);
                }
            }
        }
    }
}