<?php

/**
 * Inspired by the Scoreboards Library
 * Developed by @SoyPlayek
 */

namespace Bossbar;

use pocketmine\entity\Entity;
use pocketmine\entity\EntityIds;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddActorPacket;

class Bossbar extends PluginBase implements Listener
{

    private static $instance;
    private $bossbars = [];

    public function onLoad():void
    {
        self::$instance = $this;
    }

    public function onEnable():void
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }
    
    public static function getInstance():Bossbar {
        return self::$instance;
    }

    public function new(Player $player):bool
    {
        if (isset($this->bossbars[$player->getLowerCaseName()])) {
            $this->remove($player);
        }
        $id = (int)Entity::$entityCount++;

        $pk = new AddActorPacket();
        $pk->entityRuntimeId = $id;
        $pk->type = EntityIds::SLIME;
        $pk->metadata = [Entity::DATA_FLAGS >> [Entity::DATA_TYPE_LONG, ((1 << Entity::DATA_FLAG_INVISIBLE) | (1 << Entity::DATA_FLAG_IMMOBILE))], Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, '']];
        $pk->position = new Vector3();

        $player->sendDataPacket($pk);
        $this->bossbars[$player->getLowerCaseName()] = $id;
        $this->sendBossPacket($player, '', BossEventPacket::TYPE_SHOW);
    }

    public function sendBossPacket(Player $player, String $tile, int $eventType = BossEventPacket::TYPE_TITLE):void {
        if(!isset($this->bossbars[$player->getLowerCaseName()])) return;
        $id = $this->getID($player->getLowerCaseName());

        $pk = new BossEventPacket();
        $pk->bossEid = $id;
        $pk->eventType = $eventType;
        $pk->title = $title;
        if($eventType === BossEventPacket::TYPE_SHOW){
            $pk->healthPercent = (float) 1.0;
            $pk->unknownShort = $pk->color = $pk->overlay = 0;
        }
        $player->sendDataPacket($pk);
    }

    public function remove(Player $player):void {
        if(!isset($this->bossbars[$player->getLowerCaseName()])) return;
        $id = $this->getID($player->getLowerCaseName());

        $this->sendBossPacket($player, '', BossEventPacket::TYPE_HIDE);

        $pk = new RemoveActorPacket();
        $pk->entityUniqueId = $id;
        $player->sendDataPacket($pk);
        unset($this->bossbars[$player->getLowerCaseName()]);
    }

    public function onQuit(PlayerQuitEvent $event):void {
        if(isset($this->bossbars[$event->getPlayer()->getLowerCaseName()])){
            $this->remove($event->getPlayer());
        }
    }

    public function getID(): ?int {
        return (isset($this->bossbars[$player->getLowerCaseName()])) ? $this->bossbars[$player->getLowerCaseName() : null;
    }
}
?>
