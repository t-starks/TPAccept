<?php
namespace TStark\TPAcceptForm;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class TPACommand extends Command {
    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("tpa", "Send a teleport request to a player", "/tpa <player>");
        $this->plugin = $plugin;
        $this->setPermission("tpaform.command.tpa");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage("§cThis command can only be used in-game.");
            return false;
        }

        if (count($args) < 1) {
            $sender->sendMessage("§cUsage: /tpa <player>");
            return false;
        }

        $target = $this->plugin->getServer()->getPlayerExact($args[0]);
        if ($target === null) {
            $sender->sendMessage("§cPlayer not found.");
            return false;
        }

        if ($sender === $target) {
            $sender->sendMessage("§cYou cannot send a request to yourself.");
            return false;
        }

        $tpaManager = $this->plugin->getTPAManager();
        
        if (!$tpaManager->canReceiveRequest($target)) {
            $sender->sendMessage(str_replace("{player}", $target->getName(), 
                $this->plugin->getConfig()->get("messages")["error_toggle_blocked"]));
            return false;
        }

        if ($tpaManager->sendRequest($sender, $target)) {
            $sender->sendMessage(str_replace("{player}", $target->getName(), 
                $this->plugin->getConfig()->get("messages")["request_sent"]));
            
            $target->sendMessage(str_replace("{player}", $sender->getName(), 
                $this->plugin->getConfig()->get("messages")["request_received"]));
                
            $target->sendForm(new TPAcceptForm(
                $this->plugin,
                $sender->getName(),
                $sender->getPosition()->getWorld()->getDisplayName() . 
                " (X: " . round($sender->getPosition()->getX(), 1) . 
                ", Y: " . round($sender->getPosition()->getY(), 1) . 
                ", Z: " . round($sender->getPosition()->getZ(), 1) . ")"
            ));
        }

        return true;
    }
}