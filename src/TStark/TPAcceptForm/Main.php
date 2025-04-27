<?php
namespace TStark\TPAcceptForm;

use pocketmine\plugin\PluginBase;
use TStark\TPAcceptForm\TPAManager;

class Main extends PluginBase {
    private TPAManager $tpaManager;

    public function onEnable(): void {
        $this->saveDefaultConfig();
        $this->tpaManager = new TPAManager($this);
        $this->getServer()->getCommandMap()->registerAll("tpaform", [
            new TPACommand($this),
            new TPACancelCommand($this),
            new TPToggleCommand($this)
        ]);
    }

    public function getTPAManager(): TPAManager {
        return $this->tpaManager;
    }
}