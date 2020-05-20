<?php

class Character
{
    private $id;
    private $name;
    private $damages;

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
        // $this->setName($characterRow['name']);
        // $this->setDamages($characterRow['damages']);
    }

// --- Getters and Setters ---
    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return ucfirst($this->name);
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getDamages()
    {
        return $this->damages;
    }

    public function setDamages(int $damages)
    {
        if ($damages >= 0 && $damages <= 100) {
            $this->damages = $damages;
        }
    }
}