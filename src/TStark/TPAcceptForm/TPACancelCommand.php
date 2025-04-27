<?php
namespace TStark\TPAcceptForm;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class TPACancelCommand extends Command {
    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("tpacancel", "Cancel a teleport request", "/tpacancel <player>");
        $this->plugin = $plugin;
        $this->setPermission("tpaform.command.tpacancel");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage("§cThis command can only be used in-game.");
            return false;
        }

        if (count($args) < 1) {
            $sender->sendMessage("§cUsage: /tpacancel <player>");
            return false;
        }

        $targetName = $args[0];
        $tpaManager = $this->plugin->getTPAManager();
        $requests = $tpaManager->getPendingRequests($sender);

        if (!isset($requests[$targetName])) {
            $sender->sendMessage($this->plugin->getConfig()->get("messages")["error_no_pending"]);
            return false;
        }

        $tpaManager->cancelRequest($sender, $targetName);
        return true;
    }
}