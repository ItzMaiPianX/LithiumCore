<?php

namespace owonico\rank;
class Rank{

    /** @var string */
    public $name;

    /** @var string */
    public $displayFormat;
    /** @var array */
    public $permissions;

    public $rankColor;
    public $chatFormat;

    public function __construct(string $name, string $displayFormat, array $permissions = [], string $rankColor = "§f", string $chatFormat = "{tier} {rank} {player}{tag} §f» {message}") {
        $this->name = $name;
        $this->displayFormat = $displayFormat;
        $this->permissions = $permissions;
        $this->rankColor = $rankColor;
        $this->chatFormat = $chatFormat;
    }

    public function getChatFormat(): string {
        return $this->chatFormat;
    }

    public function getRankColor(): string {
        return $this->rankColor;
    }

    /**
     * Returns raw rank format (For example: 'Guest' or 'Owner')
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * Returns permissions whose player has with this rank
     */
    public function getPermissions(): array {
        return $this->permissions;
    }

    /**
     * Returns displayed format (For example '' for Guest or '§l§6OWNER' for owner)
     */
    public function getDisplayFormat(): string {
        return $this->displayFormat;
    }

}
