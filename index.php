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
            $character = new Warrior(['name' => ucfirst($_POST['name']), 'classType' => $_POST['classType']]);
            break;
        case 'mage' : 
            $character = new Mage(['name' => ucfirst($_POST['name']), 'classType' => $_POST['classType']]);
            break;
        case 'archer' : 
            $character = new Archer(['name' => ucfirst($_POST['name']), 'classType' => $_POST['classType']]);
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
    if ($manager->characterExists(ucfirst($_POST['name']))) {
        $character = $manager->get(ucfirst($_POST['name']));
    } else {
        $message = 'This character doesn\'t exist.';
    }
} else if (isset($_POST['hit'])) {
    if (!isset($character)) {
        $message = 'Please create a character or login.';
    } else if (!$manager->characterExists((int) $_POST['hit'])) {
            $message = 'The character you want to hit doesn\'t exist !';
    } else if ($character->getHitsCount() >= 3) {
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


    if (isset($character)) {
        ?>
        <div class="container character">
            <div class="row justify-content-around">
                <div class="col-6 card cardDark mt-4">
                    <legend class="mb-4 text-center"><?=ucfirst($character->getName())?></legend>
                    <div class="row justify-content-around">
                        <div class="col-10 text-center">
                            <div class="progress">
                                <?php
                                if ($character->getHealth() < 25) {
                                    echo ('
                                        <div class="progress-bar bg-danger" role="progressbar" aria-valuenow="'.$character->getHealth().'" aria-valuemin="0" aria-valuemax="100" style="width:'.$character->getHealth().'%">
                                        HP '.$character->getHealth().'/100
                                        </div>
                                    ');
                                } else if ($character->getHealth() > 25 && $character->getHealth() < 50) {
                                    echo ('
                                        <div class="progress-bar bg-warning" role="progressbar" aria-valuenow="'.$character->getHealth().'" aria-valuemin="0" aria-valuemax="100" style="width:'.$character->getHealth().'%">
                                        HP '.$character->getHealth().'/100
                                        </div>
                                    ');
                                } else {
                                    echo ('
                                        <div class="progress-bar bg-success" role="progressbar" aria-valuenow="'.$character->getHealth().'" aria-valuemin="0" aria-valuemax="100" style="width:'.$character->getHealth().'%">
                                        HP '.$character->getHealth().'/100
                                        </div>
                                    '); 
                                }
                                ?>
                            </div>
                        </div>

                        <div class="col-5 text-center mt-1">
                            <div class="progress">
                                <div class="progress-bar bg-warning" role="progressbar" aria-valuenow="<?=$character->getXp()?>" aria-valuemin="0" aria-valuemax="100" style="width:<?=($character->getXp() *10)?>%">
                                    XP <?=$character->getXp()?>/10
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row justify-content-around mt-2">
                        <div class="col-12 text-center">
                            <h5 class="mt-1"><?=ucfirst($character->getClassType())?></h5>
                        </div>
                    </div>

                    <div class="row justify-content-around">
                        <div class="col-6 text-center">
                            <h6>Level : <?=$character->getLevel()?></h6>
                        </div>
                        <div class="col-6 text-center">
                            <h6>Strength : <?=$character->getStrength()?></h6>
                        </div>
                    </div>

                    <div class="row justify-content-around mt-3 mb-1">
                        <div class="col-12 text-center">
                        <?php
                            if ($character->getClassType() === 'archer') { 
                                echo ('<h6>Hits count : '.$character->getHitsCount().'/5</h6>');
                            } else {
                                echo ('<h6>Hits count : '.$character->getHitsCount().'/3</h6>');
                            }
                        ?>
                        </div>
                    </div>

                    <div class="row justify-content-around">
                        <div class="col-12 text-center">
                            <h6>Last hit : <?=$character->getLastHit()?></h6>
                        </div>
                    </div>

                    <div class="row justify-content-around mt-3">
                        <div class="col-12 text-center">
                            <button class="btn btn-sm"><a href="?logout=1">Logout</a></button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        

        <div class="container-fluid opponents">
            <div class="row justify-content-around mt-3">
                <?php
                $opponents = $manager->getList($character->getName());

                if (empty($opponents)) {
                    echo 'No opponent available.';
                } else {
                    echo('<legend class="text-center text-white mb-3">Choose your target</legend>');
                    foreach ($opponents as $opponent) {
                        switch ($opponent->getClassType()) {
                            case 'warrior' : 
                                echo ('<div class="col-2 card cardWarrior cardAnim mx-2">');
                                break;
                            case 'mage' : 
                                echo ('<div class="col-2 card cardMage cardAnim mx-2">');
                                break;
                            case 'archer' : 
                                echo ('<div class="col-2 card cardArcher cardAnim mx-2">');
                                break;
                                default : 
                                echo ('<div class="col-2 card cardDefault cardAnim mx-2">');
                                break;
                        }
                        ?>
                                <legend class="mb-4 text-center"><?=htmlspecialchars($opponent->getName())?></legend>
                                <div class="row justify-content-around">
                                    <div class="col-10 text-center">
                                        <div class="progress">
                                            <?php
                                            if ($opponent->getHealth() < 25) {
                                                echo ('
                                                    <div class="progress-bar bg-danger" role="progressbar" aria-valuenow="'.$opponent->getHealth().'" aria-valuemin="0" aria-valuemax="100" style="width:'.$opponent->getHealth().'%">
                                                    HP '.$opponent->getHealth().'/100
                                                    </div>
                                                ');
                                            } else if ($opponent->getHealth() > 25 && $opponent->getHealth() < 50) {
                                                echo ('
                                                    <div class="progress-bar bg-warning" role="progressbar" aria-valuenow="'.$opponent->getHealth().'" aria-valuemin="0" aria-valuemax="100" style="width:'.$opponent->getHealth().'%">
                                                    HP '.$opponent->getHealth().'/100
                                                    </div>
                                                ');
                                            } else {
                                                echo ('
                                                    <div class="progress-bar bg-success" role="progressbar" aria-valuenow="'.$opponent->getHealth().'" aria-valuemin="0" aria-valuemax="100" style="width:'.$opponent->getHealth().'%">
                                                    HP '.$opponent->getHealth().'/100
                                                    </div>
                                                '); 
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                    
                                <div class="row justify-content-around mt-2">
                                    <div class="col-12 text-center">
                                        <h5 class="mt-1"><?=ucfirst($opponent->getClassType())?></h5>
                                    </div>
                                </div>

                                <div class="row justify-content-around mr-2 mt-1 mb-2">
                                    <div class="col-3 text-center mr-4">
                                        <?php
                                        if ($character->defineStrong() === $opponent->getClassType()) {
                                            echo ('<p class="damages">Damages x 2</p>');
                                        } else {
                                            echo ('<p class="damages">Damages x 1</p>');
                                        }
                                        ?>
                                    </div>
                                </div>

                                <div class="row justify-content-around">
                                    <div class="col-6 text-center">
                                        <h6>Level : <?=$opponent->getLevel()?></h6>
                                    </div>
                                    <div class="col-6 text-center">
                                        <h6>Strength : <?=$opponent->getStrength()?></h6>
                                    </div>
                                </div>

                                <div class="row justify-content-around mt-3">
                                    <div class="col-12 text-center">
                                        <h6>Last hit : <?=$opponent->getLastHit()?></h6>
                                    </div>
                                </div>

                                <div class="row justify-content-around mt-3">
                                    <div class="col-12 text-center">
                                        <form action="" method="POST">
                                            <input type="hidden" name="hit" value="<?=$opponent->getId()?>">
                                            <button type="submit" class="btn btn-sm">Attack</button>
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

        echo('
        <div class="row justify-content-around text-center">
            <div class="col-6">
                <legend class="my-3 text-white">Fighters in-game : '.$manager->count().'</legend>
            </div>
        </div>
    ');
?>
        <div class="container">
            <div class="row justify-content-around text-center">

                <div class="col-12 my-5">
                    <h3 class="text-white letsFight">Let's fight !</h3>
                </div>

                <div class="col-4 card cardDark">
                    <div class="col-12 mb-4">
                        <h4>Create new fighter</h4>
                    </div>

                    <form action="" method="POST">
                        <div class="row justify-content-around text-center">      
                            <div class="col-12 mb-4">
                                <input type="text" name="name" class="mr-3" minlength="2" maxlength="20" placeholder="Name">
                                <select name="classType" id="classType" required>
                                    <option value="warrior">Warrior</option>
                                    <option value="mage">Mage</option>
                                    <option value="archer">Archer</option>
                                </select>
                            </div> 
                        </div> 
                        <div class="row justify-content-center text-center">
                            <div class="col-8">
                                <input type="submit" name="create" class="btn btn-sm mx-3" value="Create"/>
                            </div> 
                        </div>
                    </form>
                </div>


                <div class="col-4 card cardDark">
                    <div class="col-12 mb-4">
                        <h4>Select a character</h4>
                    </div>

                    <form action="" method="POST">
                        <div class="row justify-content-around text-center">      
                            <div class="col-12 mb-4">
                                <input type="text" name="name" minlength="2" maxlength="20" placeholder="ID or Name">
                            </div> 
                        </div> 
                        <div class="row justify-content-center text-center">
                            <div class="col-8">
                                <input type="submit" name="select" class="btn btn-sm mx-3" value="Select"/>
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