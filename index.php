<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css">
<link rel="stylesheet" href="css/style.css">
<title>Fight Game</title>
</head>
<body>
    <div class="container-fluid bg">

    <?php
    include('config/db.php');
    include('config/autoload.php');
    session_start();
    date_default_timezone_set('Europe/Paris');

    if (isset($_GET['logout']))
    {
        session_destroy();
        header('Location: .');
        exit();
    }

    $manager = new CharactersManager($db);

    if (isset($_SESSION['character'])) {
        $character = $_SESSION['character'];
    }

    if (isset($_POST['create']) && !empty($_POST['name'])) {
        switch ($_POST['classType']) {
            case 'warrior' : 
                $character = new Warrior(['name' => $_POST['name'], 'classType' => $_POST['classType']]);
                break;
            case 'mage' : 
                $character = new Mage(['name' => $_POST['name'], 'classType' => $_POST['classType']]);
                break;
            case 'archer' : 
                $character = new Archer(['name' => $_POST['name'], 'classType' => $_POST['classType']]);
                break;
             default : 
                $message = 'Character\'s type is not valid.';
                break;
        }

        if ($manager->characterExists($character->getName())) {
            $message = 'Name already taken.';
            unset($character);
        } else {
            $manager->create($character);
        }
    } else if (isset($_POST['select']) && !empty($_POST['name'])) {
        if ($manager->characterExists($_POST['name'])) {
            $character = $manager->get($_POST['name']);
        } else {
            $message = 'This character doesn\'t exist.';
        }
    } else if (isset($_POST['hit'])) {
        if (!isset($character)) {
            $message = 'Please create a character or login.';
        } else if (!$manager->characterExists((int) $_POST['hit'])) {
                $message = 'The character you want to hit doesn\'t exist !';
        } else if ($character->getHitsCount() >= 3) {
            // echo $character->getLastHit().'</br>';
            // echo $character->getNextHit().'</br>';
            // echo $character->compareDates($character->getLastHit(), $character->getNextHit());
            $character->changeHitDate();
            if ($character->compareDates($character->getLastHit(), $character->getNextHit()) === 'dateNotOk') {
                $message = 
                'You gave the maximum amount of hits today.</br>
                You\'ll be able to fight again starting '.$character->getNextHit();
            } else {
                doHit();
            }
        } else {
            doHit();
        }
    } 

    function doHit() {
        global $manager;
        global $character;
   
        $opponentToHit = $manager->get((int) $_POST['hit']);

        $feedback = $character->hit($opponentToHit);

        switch ($feedback) {
            case Character::HIT_MYSELF : 
                $message = 'But... why do you want to hit yourself ?';
                break;

            case Character::CHARACTER_HIT : 
                $message = 'The opponent did take a hit.';

                $manager->update($character);
                $manager->update($opponentToHit);

                break;

            case Character::CHARACTER_KILLED : 
                $message = 'You killed this fighter !';

                $manager->update($character);
                $manager->delete($opponentToHit);

                break;
        }
    }
        
        // if (isset($message)) {
        //     echo ('
        //         <div class="row text-center">
        //             <div class="col-12">
        //                 <h6 class="text-white">'.$message.'</h6>
        //             </div>
        //         </div>
        //     ');
                    
        // }

        echo ('
                <div class="row">
                    <div class="col-12 pt-3">
                        <h6 class="text-white">
        ');
        if (isset($message)) {
            echo ($message);      
        } else {
            echo (' ');
        }
        echo ('
                </h6>
                    </div>
                </div>
            ');

        echo('
            <div class="row justify-content-around text-center mt-5">
                <div class="col-6">
                    <legend class="my-4 ml-3 text-white">Fighters in-game : '.$manager->count().'</legend>
                </div>
            </div>
        ');

        if (isset($character)) {
            ?>
            <div class="container">
                <div class="row justify-content-around">
                    <div class="col-6 card cardFighter">
                        <legend class="mb-4 text-center"><?=ucfirst($character->getName())?></legend>
                        <div class="row justify-content-around">
                            <div class="col-6 text-center">
                                <h6>Class : <?=ucfirst($character->getClassType())?></h6></br>
                                <h6>Damages : <?=$character->getDamages()?></h6>
                                <h6>Strength : <?=$character->getStrength()?></h6>
                            </div>
                            <div class="col-6 text-center">
                                <h6>Level : <?=$character->getLevel()?></h6>
                                <h6>XP : <?=$character->getXp()?></h6></br>
                                <h6>Hits count today : <?=$character->getHitsCount()?></h6>
                            </div>
                            <div class="col-12 text-center mt-3">
                                <button class="btn btn-sm btn-light"><a href="?logout=1">Logout</a></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            

            <div class="container-fluid">
                <div class="row justify-content-around mt-4">
                    <?php
                    $opponents = $manager->getList($character->getName());

                    if (empty($opponents)) {
                        echo 'No opponent available.';
                    } else {
                        echo('<legend class="mt-4 text-center"><b class="text-white">Choose your target</b></legend>');
                        foreach ($opponents as $opponent) {
                            switch ($opponent->getClassType()) {
                                case 'warrior' : 
                                    echo ('<div class="col-3 card cardWarrior mx-4">');
                                    break;
                                case 'mage' : 
                                    echo ('<div class="col-3 card cardMage mx-4">');
                                    break;
                                case 'archer' : 
                                    echo ('<div class="col-3 card cardArcher mx-4">');
                                    break;
                                 default : 
                                 echo ('<div class="col-3 card cardDefault mx-4">');
                                    break;
                            }
                            ?>
                                    <legend class="mb-4 text-center"><?=htmlspecialchars($opponent->getName())?></legend>
                                    <div class="row justify-content-around">
                                        <div class="col-6 text-center">
                                            <h6>Class : <?=ucfirst($opponent->getClassType())?></h6></br>
                                            <h6>Damages : <?=$opponent->getDamages()?></h6>
                                        </div>
                                        <div class="col-6 text-center">
                                            <h6>Level : <?=$opponent->getLevel()?></h6></br>
                                            <h6>Strength : <?=$opponent->getStrength()?></h6>
                                        </div>
                                        <div class="col-12 text-center mt-3">
                                            <form action="" method="POST">
                                                <input type="hidden" name="hit" value="<?=$opponent->getId()?>">
                                                <button type="submit" class="btn btn-sm btn-light">Attack</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php
                        }
                    }
            echo ('
                </div>
            </div>
            ');

        } else {
?>
            <div class="container">
                <div class="row justify-content-around text-center">

                    <div class="col-12 my-5">
                        <h3>Let's fight !</h3>
                    </div>

                    <div class="col-4 card cardSelect">

                        <div class="col-12 mb-3">
                            <h4>Create new fighter</h4>
                        </div>

                        <form action="" method="POST">
                            <div class="row justify-content-around text-center">      
                                <div class="col-12 mb-4">
                                    <input type="text" name="name" minlength="2" maxlength="20" placeholder="Name">
                                </div> 
                                <div class="col-12 mb-4">
                                    <label for="classType">Choose your class</label></br>
                                    <select name="classType" id="classType" required>
                                        <option value="warrior">Warrior</option>
                                        <option value="mage">Mage</option>
                                        <option value="archer">Archer</option>
                                    </select>
                                </div> 
                            </div> 
                            <div class="row justify-content-center text-center">
                                <div class="col-8">
                                    <input type="submit" name="create" class="btn btn-dark mx-3" value="Create"/>
                                </div> 
                            </div>
                        </form>

                    </div>


                    <div class="col-4 card cardSelect">

                        <div class="col-12 mb-3">
                            <h4>Select a character</h4>
                        </div>

                        <form action="" method="POST">
                            <div class="row justify-content-around text-center">      
                                <div class="col-12 mb-4">
                                    <input type="text" name="name" minlength="2" maxlength="20" placeholder="Name">
                                </div> 
                            </div> 
                            <div class="row justify-content-center text-center">
                                <div class="col-8">
                                    <input type="submit" name="select" class="btn btn-dark mx-3" value="Select"/>
                                </div> 
                            </div>
                        </form>

                    </div>

                </div>
            </div>
        <?php
        }
        
        if (isset($character)) {
            $_SESSION['character'] = $character;
        }
        ?>
    </div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.min.js"></script>
</body>
</html>