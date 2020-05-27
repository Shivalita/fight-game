<?php

class Warrior extends Character 
{
    public function takeDamage($str, $classT)
    {
        if ($classT === 'archer') {
            $this->damages += ((5 + $str) * 2);
        } else {
            $this->damages += (5 + $str);
        }
        
        if ($this->damages >= 100) {
            return self::CHARACTER_KILLED;
        }

        return self::CHARACTER_HIT;
    }
}