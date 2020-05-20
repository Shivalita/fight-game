<?php

public function hitCharacter(Character $character) 
{
    if ($character->getId() === $this->id) {
        return self::HIT_MYSELF;
    }
    
    return $character->takeDamage();
}

public function takeDamage()
{
    $this->damages += 5;

    if ($this->damages >= 100) {
        return self::CHARACTER_KILLED;
    }

    return self::CHARACTER_HIT;
}