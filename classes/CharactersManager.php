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
        $charactersStatement = $this->db->query('SELECT * FROM characters');
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
    $addCharacterQuery = $this->db->prepare('INSERT INTO characters(name)
    VALUES(:name)');
    $addCharacterQuery->bindValue(':name', $character->getName());
    $addCharacterQuery->execute();

    $character->hydrate([
        'id' => $this->db->lastInsertId(),
        'damages' =>  0
    ]);
}
    
// --- UPDATE --- 
    public function update(Character $character)
    {
       $updateChatacterQuery = $this->db->prepare('UPDATE characters SET damages = ? WHERE id = ?');
       $updateChatacterQuery->execute([
           $character->getDamages(),
           $character->getId()
       ]); 
    }
    
// --- DELETE --- 
    public function delete(Character $character)
    {
        $deleteCharacterQuery = $this->db->prepare('DELETE FROM characters WHERE id = ?');
        $deleteCharacterQuery->execute([$character->getId()]);
    }

// --- GET --- 
    public function get($request)
    {
        if (is_int($request)) {
            $getCharacterData = $this->db->prepare('SELECT * FROM characters WHERE id = ?');
            $getCharacterData->execute([$request]);
            $characterData = $getCharacterData->fetch(PDO::FETCH_ASSOC);

            return $characterData;
        } else {
            $getCharacterData = $this->db->prepare('SELECT * FROM characters WHERE name = ?');
            $getCharacterData->execute([$request]);
            $characterData = $getCharacterData->fetch(PDO::FETCH_ASSOC);

            return $characterData;
        }
    }

 // --- COUNT --- 
    public function count()
    {
        $charactersCountQuery = $this->db->query('SELECT * from characters');
        $charactersCount = $charactersCountQuery->fetchAll(PDO::FETCH_ASSOC);

        return count($charactersCount);
    }

// --- GET LIST --- 
    public function getList()
    {
        $charactersList = [];

        $charactersListQuery = $this->db->query('SELECT * FROM characters');
        $names = $charactersListQuery->fetchAll(PDO::FETCH_ASSOC);

        foreach ($names as $name) {
            array_push($charactersList, $name);
        }

        return $charactersList;
    }

// --- GET LIST --- 
    public function characterExists($character)
    {
        $verifyCharacterExists = $this->db->prepare('SELECT * FROM characters WHERE name = ?');
        $verifyCharacterExists->execute([$character]);
        $characterExists = $verifyCharacterExists->fetch(PDO::FETCH_ASSOC);

        if ($characterExists) {
            return $characterExists['name'];
        } else {
            
        }
    }

}