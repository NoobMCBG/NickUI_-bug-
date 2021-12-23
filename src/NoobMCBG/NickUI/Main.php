<?php

namespace NoobMCBG\NickUI;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\command\{Command, CommandSende, ConsoleCommandSender};
use pocketmine\event\Listener as L;
use pocketmine\plugin\PluginBase as PB;
use pocketmine\event\player\{PlayerJoinEvent, PlayerQuitEvent};
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\utils\Config;
use jojoe77777\FormAPI\{SimpleForm, CustomForm};

class Main extends PB implements L {

	public function onEnable(){
		@mkdir($this->getDataFolder());
		$this->nick = new Config($this->getDataFolder() . "nick.yml", Config::YAML);
		$this->autonick = new Config($this->getDataFolder() . "autonick.yml", Config::YAML);
		$this->saveDefaultConfig();
		$this->getLogger()->info("\n\n\nNickUI v1.0.0 by NoobMCBg\n   For PlowingForVictory\n\n\n");
	}

	public function onDisable(){
		$this->autonick->save();
		$this->nick->save();
	}

	public function onJoin(PlayerJoinEvent $ev){
		$player = $ev->getPlayer();
		if(!$player->hasPermission("nick.command")){
		    if($this->autonick->get(strtolower($player->getName())) == true){
			    $name = $this->nick->get(strtolower($player->getName()));
			    $player->setDisplayName($name);
                $player->setNameTag($name);
            }
		}
	}

	public function onQuit(PlayerQuitEvent $ev){
		$this->autonick->save();
		$this->nick->save();
	}


	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool {
		switch($cmd->getName()){
			case "nick":
			    if(!$sender instanceof Player){
			    	$sender->sendMessage("§l§c●§e Hãy Sử Dụng Lệnh Trong Trò Chơi !");
			    	return true;
			    }
			    if(!$sender->hasPermission("nick.command")){
			    	$sender->sendMessage("§l§c●§e Bạn Không Có Quyền Sử Dụng§b /nick");
			    }else{
			    	$this->MenuNick($sender);
			    }
			break;
		}
		return true;
	}

	public function MenuNick($player){
		$form = new SimpleForm(function(Player $player, $data){
			if($data == null){
				return true;
			}
			switch($data){
				case 0:
				break;
				case 1:
				    $this->ChangerNick($player);
				break;
				case 2:
				    $this->ResetNick($player);
				break;
				case 3:
				    $this->SettingNick($player);
				break;
			}
		});
		$form->setTitle("§l§c♦§2 Nick §6♦");
		$form->addButton("§l§3●§4 Thoát Menu §3●");
		$form->addButton("§l§3●§2 Đổi Tên §3●");
		$form->addButton("§l§3●§2 Reset Tên §3●");
		$form->addButton("§l§3●§2 Cài Đặt §3●");
		$form->sendToPlayer($player);
	}

	public function ChangerNick($player){
		$form = new CustomForm(function(Player $player, $data){
			if($data === null){
				return true;
			}
			$name = $data[1]
			$this->nick->set(strtolower($player->getName()), $name);
			$this->nick->save();
			$player->setDisplayName($name);
			$player->setNameTag($name);
		    $packet = new PlaySoundPacket();
		    $packet->soundName = "random.levelup";
		    $packet->x = $player->getX();
		    $packet->y = $player->getY();
		    $packet->z = $player->getZ();
		    $packet->volume = 1;
		    $packet->pitch = 1;
		    $player->sendDataPacket($packet);
		});
		$form->setTitle("§l§c♦§b Nick §6♦");
		$form->addInput("§l§c●§e Nhập Tên Muốn Đổi: ", "NoobMCBG");
		$form->sendToPlayer($player);
	}

	public function ResetNick($player){
		$all = $this->nick->getAll();
        unset($all[strtolower($player->getName())]);
        $this->nick->save();
		$name = $player->getName();
		$player->setNameTag($name);
		$player->setDisplayName($name);
		$packet = new PlaySoundPacket();
		$packet->soundName = "enderdragon.growl";
		$packet->x = $player->getX();
		$packet->y = $player->getY();
		$packet->z = $player->getZ();
		$packet->volume = 1;
		$packet->pitch = 1;
		$player->sendDataPacket($packet);
	}

	public function SettingNick($player){
		$form = new CustomForm(function(Player $player, $data){
			if($data === null){
				return true;
			}
			switch($data[0]){
				case 0:
				    $this->autonick->set(strtolower($player->getName()), true);
				    $this->autonick->save();
				    $player->sendMessage("§l§c●§e Chế Độ Tự Động Nick Khi Tham Gia:§a Bật");
				    $player->sendPopup("§l§c●§e Chế Độ Tự Động Nick Khi Tham Gia:§a Bật");
				break;
				case 1:
				    $this->autonick->set(strtolower($player->getName()), false);
				    $this->autonick->save();
				    $player->sendMessage("§l§c●§e Chế Độ Tự Động Nick Khi Tham Gia:§c Tắt");
				    $player->sendPopup("§l§c●§e Chế Độ Tự Động Nick Khi Tham Gia:§c Tắt");
				break;
			}
			$this->nick->set(strtolower($player->getName()), $data[2]);
			$this->nick->save();
		});
        $form->setTitle("§l§c♦§b Nick §6♦");
        $form->addLabel("§l§c●§e Gạt Sang Phải Để§a Bật§e, Sang Trái Để§c Tắt");
        $form->addToggle("§l§c●§e Tự Động Nick Khi Tham Gia");
        $form->addInput("§l§c●§e Tên Muốn Đổi Auto:§a ", "NoobMCBG");
        $form->sendToPlayer($player);
	}
}