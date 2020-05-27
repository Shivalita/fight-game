<?php

class CharactersManager
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

// --- Select all characters ---
    public function all()
    {
        $charactersStatement = $this->db->query(
            'SELECT * FROM characters'
        );
        $charactersRow = $charactersStatement->fetchAll();
        $characters = [];

        foreach ($charactersRow as $characterRow) {
            $characters += $characterRow;
        }

        return $characters;
    }

// --- CRUD AND OTHER METHODS---  

// --- CREATE --- 
public function create(Character $character)
{
    $addCharacterQuery = $this->db->prepare(
        'INSERT INTO characters(name, classType)
        VALUES(:name, :classType)'
    );

    $addCharacterQuery->bindValue(
        ':name', $character->getName()
    );
    $addCharacterQuery->bindValue(
        ':classType', $character->getClassType()
    );

    $addCharacterQuery->execute();

    $character->hydrate([
        'id' => $this->db->lastInsertId(),
        'damages' =>  0,
        'level' =>  1,
        'xp' =>  0,
        'strength' =>  1,
        'hitsCount' => 0,
        'lastHit' => '2020-05-26 00:00:00',
        'nextHit' => '2020-05-26 00:00:00'
    ]);
}
    
// --- UPDATE --- 
    public function update(Character $character)
    {
       $updateChatacterQuery = $this->db->prepare(
           'UPDATE characters 
            SET damages = ?, level = ?, xp = ?, strength = ?, hitsCount = ?, 
            lastHit = ?, nextHit = ? 
            WHERE id = ?'
        );
       $updateChatacterQuery->execute([
           $character->getDamages(),
           $character->getLevel(),
           $character->getXp(),
           $character->getStrength(),
           $character->getHitsCount(),
           $character->getLastHit(),
           $character->getNextHit(),
           $character->getId()
       ]); 
    }
    
// --- DELETE --- 
    public function delete(Character $character)
    {
        $deleteCharacterQuery = $this->db->prepare(
            'DELETE FROM characters WHERE id = ?'
        );
        $deleteCharacterQuery->execute([$character->getId()]);
    }

// --- GET --- 
    public function get($request)
    {
        if (is_int($request)) {
            $getCharacterData = $this->db->prepare(
                'SELECT * FROM characters WHERE id = ?'
            );
            $getCharacterData->execute([$request]);
            $characterData = $getCharacterData->fetch(PDO::FETCH_ASSOC);
            
        } else {
            $getCharacterData = $this->db->prepare(
                'SELECT * FROM characters WHERE name = ?'
            );
            $getCharacterData->execute([$request]);
            $characterData = $getCharacterData->fetch(PDO::FETCH_ASSOC);
            
        }

        switch ($characterData['classType']) {
            case 'warrior' : 
                return new Warrior($characterData);
                break;
            case 'mage' : 
                return new Mage($characterData);
                break;
            case 'archer' : 
                return new Archer($characterData);
                break;
            default : 
                return NULL;
        }
    }

 // --- COUNT --- 
    public function count()
    {
        $charactersCountQuery = $this->db->query(
            'SELECT * from characters'
        );
        $charactersCount = $charactersCountQuery->fetchAll(PDO::FETCH_ASSOC);

        return count($charactersCount);
    }

// --- GET LIST --- 
    public function getList($name)
    {
        $opponents = [];

        $opponentsQuery = $this->db->prepare(
            'SELECT * FROM characters WHERE name <> :name ORDER BY name'
        );
        $opponentsQuery->execute([':name' => $name]);

        while ($characterData = $opponentsQuery->fetch(PDO::FETCH_ASSOC)) {
            switch ($characterData['classType']) {
                case 'warrior' : 
                    $opponents[] = new Warrior($characterData);
                    break;
                case 'mage' : 
                    $opponents[] = new Mage($characterData);
                    break;  
                case 'archer' : 
                    $opponents[] = new Archer($characterData);
                    break;
            }
        }
        return $opponents;
    }

// --- CHARACTER EXISTS --- 
    public function characterExists($character)
    {
        if (is_int($character)) {
            return (bool) $this->db->query(
                'SELECT COUNT(*) FROM characters WHERE id = '
                .$character)->fetchColumn();
        }

        $verifyCharacterExists = $this->db->prepare(
            'SELECT COUNT(*) FROM characters WHERE name = :name'
        );
        $verifyCharacterExists->execute([':name' => $character]);
        
        return (bool) $verifyCharacterExists->fetchColumn();           
    }

}