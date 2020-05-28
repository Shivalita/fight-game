<?php

class Warrior extends Character 
{
    public function takeDamage($str, $classT)
    {
        if ($classT === 'archer') {
            $this->health -= ceil(((5 + $str) * 2));
        } else {
            $this->health -= ceil((5 + $str));
        }
        
        if ($this->health <= 0) {
            return self::CHARACTER_KILLED;
        }

        return self::CHARACTER_HIT;
    }

    public function increaseStrength()
    {
        $this->strength += 2;
    }
}