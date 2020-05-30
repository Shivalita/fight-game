<?php

class Mage extends Character 
{
    public function takeDamage($str, $classT)
    {
        if ($classT === 'warrior') {
            $this->health -= ceil(((5 + $str) * 2));
        } else {
            $this->health -= ceil((5 + $str));
        }
        
        if ($this->health <= 0) {
            return self::CHARACTER_KILLED;
        }

        return self::CHARACTER_HIT;
    } 

    public function increaseXp()
    {
        $this->xp += 2;
    }

    public function increaseMagic()
    {
        $this->magic += 2;
    }
}