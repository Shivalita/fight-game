<?php

abstract class Character
{
    protected $id;
    protected $name;
    protected $health;
    protected $level;
    protected $xp;
    protected $force;
    protected $hitsCount;
    protected $lastHit;
    protected $nextHit;
    protected $classType;
    protected $strong;
    public $token = false;

    const HIT_MYSELF = 1;
    const CHARACTER_KILLED = 2;
    const CHARACTER_HIT = 3;

    public function __construct(array $data)
    {
        $this->hydrate($data);
    }

// --- Hydration ---
    public function hydrate(array $data) 
    {
        foreach ($data as $key => $value) {
            $method = 'set' .ucfirst($key);

            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }


// --- Getters and Setters ---
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $id = (int) $id;

        if ($id > 0) {
            $this->id = $id;
        }
    }

    public function getName()
    {
        return ucfirst($this->name);
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getHealth()
    {
        return $this->health;
    }

    public function setHealth(int $health)
    {
        if ($health >= 0 && $health <= 100) {
            $this->health = $health;
        }
    }

    public function getLevel()
    {
        return $this->level;
    }
    
    public function setLevel($level) 
    {
        $this->level = $level;
    }

    public function levelUp()
    {
        if ($this->xp >= 10) {
            $this->level += 1;
            $this->xp = 0;
            $this->increaseStrength();
        }
    }

    public function getXp()
    {
        return $this->xp;
    }

    public function setXp($xp)
    {
        $this->xp = $xp;
    }

    public function increaseXp()
    {
        $this->xp += 1;
    }

    public function getStrength()
    {
        return $this->strength;
    }

    public function setStrength($strength)
    {
        $this->strength = $strength;
    }
    
    public function increaseStrength()
    {
        $this->strength += 1;
    }

    public function getHitsCount()
    {
        return $this->hitsCount;
    }

    public function setHitsCount($hitsCount)
    {
        $this->hitsCount = $hitsCount;
    }

    public function increaseHitsCount()
    {
        $this->hitsCount += 1;
    }

    public function getLastHit()
    {
        return $this->lastHit;
    }

    public function setLastHit($lastHit)
    {
        $this->lastHit = $lastHit;
    }

    public function getNextHit()
    {
        return $this->nextHit;
    }

    public function changeHitDate()
    {
        $this->lastHit = date('Y-m-d H:i:s');
    }

    public function changeNextHit()
    {
        $hitDate = new DateTime($this->getLastHit());
        // $hitDate->add(new DateInterval('PT2M'));
        $hitDate->add(new DateInterval('P1D'));
        $nextDate = $hitDate->format('Y-m-d H:i:s');
        $this->nextHit = $nextDate;
    }

    public function compareDates($lastHit, $nextDate)
    {
        if ($lastHit < $nextDate) {
            return 'dateNotOk';
        } else {
            $this->hitsCount = 0;
            return 'dateOk';
        }
    }

    public function switchToken()
    {
        if ($this->token === true) {
            $this->token = false;
        } else {
            $this->token = true;
        }
    }

    public function getClassType()
    {
        return $this->classType;
    }

    public function setClassType($classType)
    {
        $this->classType = $classType;
    }

    public function getStrong()
    {
        return $this->strong;
    }

    public function setStrong($strong)
    {
        $this->strong = $strong;
    }

    public function defineStrong()
    {
        switch ($this->classType) {
            case 'warrior' : 
                return 'mage';
                break;
            case 'mage' : 
                return 'archer';
                break;
            case 'archer' : 
                return 'warrior';
                break;
        }
    }


    public function hit(Character $opponent) 
    {
        if ($opponent->getId() === $this->id) {
            return self::HIT_MYSELF;
        } else {
            $this->increaseXp();
            $this->levelUp();
            $this->increaseHitsCount();
            $this->changeHitDate();
            if ($this->getHitsCount() >= 3) {
                if ($this->token === false) {
                    $this->changeNextHit();
                } 
                $this->switchToken();
            }
            return $opponent->takeDamage($this->strength, $this->classType);
        }
    }

    public function takeDamage($str, $classType)
    {
        $this->health -= (5 + $str);

        if ($this->health <= 0) {
            return self::CHARACTER_KILLED;
        }

        return self::CHARACTER_HIT;
    }
}