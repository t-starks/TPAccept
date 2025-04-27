<?php
namespace TStark\TPAcceptForm;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class TPToggleCommand extends Command {
    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("tptoggle", "Toggle receiving TP requests", "/tptoggle");
        $this->plugin = $plugin;
        $this->setPermission("tpaform.command.tptoggle");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage("§cThis command can only be used in-game.");
            return false;
        }

        $tpaManager = $this->plugin->getTPAManager();
        $newStatus = !$tpaManager->canReceiveRequest($sender);
        $tpaManager->setToggle($sender, $newStatus);

        return true;
    }
}