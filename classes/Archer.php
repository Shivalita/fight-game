<?php

class Archer extends Character 
{
    public function takeDamage($str, $classType)
    {
        if ($classType === 'mage') {
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

    public function hit(Character $opponent) 
    {
        if ($opponent->getId() === $this->id) {
            return self::HIT_MYSELF;
        } else {
            $this->increaseXp();
            $this->levelUp();
            $this->increaseHitsCount();
            $this->changeNextHit();
            $this->changeHitDate();
            return $opponent->takeDamage($this->strength, $this->classType);
        }
    }
}