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

$datetime = date('Y-m-d H:i:s');

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
        $manager->update($character);
    }
} else if (isset($_POST['select'])) {
    if ($manager->characterExists(ucfirst($_POST['fighter']))) {
        $character = $manager->get(ucfirst($_POST['fighter']));
        $character->compareDates($datetime, $character->getNextHit());
    } else {
        $message = 'This character doesn\'t exist.';
    }
} else if (isset($_POST['hit'])) {
    if (!isset($character)) {
        $message = 'Please create a character or login.';
    } else if (!$manager->characterExists((int) $_POST['hit'])) {
            $message = 'The character you want to hit doesn\'t exist !';
    } else if (
        (($character->getClassType() === 'warrior') && ($character->getHitsCount() >= 3)) || 
        (($character->getClassType() === 'mage') && ($character->getHitsCount() >= 3)) || 
        (($character->getClassType() === 'archer') && ($character->getHitsCount() >= 5))
        ) {
        $character->changeHitDate();
        if ($character->compareDates($datetime, $character->getNextHit()) === 'dateNotOk') {
            $message = 
            'You are out of action points.</br>';
        } else {
            doHit();
        }
    } else {
        doHit();
    }
} else if (isset($_POST['heal'])) {
    if (!isset($character)) {
        $message = 'Please create a character or login.';
    } else if ($character->getHealth() === 100) {
        $message = 'You are already full-life.';
    } else if (
        (($character->getClassType() === 'warrior') && ($character->getHitsCount() >= 3)) || 
        (($character->getClassType() === 'mage') && ($character->getHitsCount() >= 3)) || 
        (($character->getClassType() === 'archer') && ($character->getHitsCount() >= 5))
        ) {
        $message = 
            'You are out of action points.</br>';
    } else {
        $character->heal();
        $manager->update($character);
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
?>
        <div class="row"<?php if (!isset($message)){echo 'style="visibility:hidden;"';}?>>
            <div class="col-12 pt-3">
                <h6 class="text-white">
   
    <?php
    if (isset($message)) {
        echo ($message);      
    } else {
        echo ('Enjoy the fight !');
    }
    
    echo ('
            </h6>
                </div>
            </div>
        ');


    if (isset($character)) {
        ?>
        <div class="pageContent">
        <div class="container character">
            <div class="row justify-content-around">
                <div class="col-10 col-md-6 card cardDark">
                    <div class="row card-body">
                        <div class="col-6 offset-3 col-md-4 offset-md-4 text-center">
                            <h5 class="text-center characterName"><?=ucfirst($character->getName())?></h5>
                        </div>
                        <div class="col-2 col-md-2 offset-1 offset-md-2 text-center ml-1">
                            <button class="btn btn-sm logoutBtn"><a href="?logout=1">Logout</a></button>
                        </div>
                    </div>
                    <div class="row justify-content-around characterLevel">
                        <div class="col-10 text-center">
                            <h6>Level <?=$character->getLevel()?></h6>
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

                        <div class="col-8 text-center mt-1">
                            <div class="progress">
                                <div class="progress-bar bg-info" role="progressbar" aria-valuenow="<?=$character->getXp()?>" aria-valuemin="0" aria-valuemax="100" style="width:<?=($character->getXp() *10)?>%">
                                    XP <?=$character->getXp()?>/10
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row justify-content-around mt-2 mb-3">
                        <div class="col-12 text-center">
                            <h5 class="mt-1 classType"><?=ucfirst($character->getClassType())?></h5>
                        </div>
                    </div>

                    <div class="row justify-content-around">
                        <div class="col-6 text-center">
                            <h6>Strength <?=$character->getStrength()?></h6>
                        </div>
                        <div class="col-6 text-center">
                            <h6>Magic <?=$character->getMagic()?></h6>
                        </div>
                    </div>

                    <div class="row justify-content-around mt-3">
                        <div class="col-12 text-center">
                        <?php
                        if (
                            (($character->getClassType() === 'warrior') && ($character->getHitsCount() >= 3)) || 
                            (($character->getClassType() === 'mage') && ($character->getHitsCount() >= 3)) || 
                            (($character->getClassType() === 'archer') && ($character->getHitsCount() >= 5))
                            ) {
                            $character->compareDates($datetime, $character->getNextHit());
                        }
                            if ($character->getClassType() === 'archer') { 
                                echo ('<h6>Action Points '.$character->getHitsCount().'/5</h6>');
                            } else {
                                echo ('<h6>Action Points '.$character->getHitsCount().'/3</h6>');
                            }
                        ?>
                        </div>
                    </div>

                    <div class="row justify-content-around">
                        <div class="col-12 text-center"<?php if ((($character->getClassType() === 'warrior') && ($character->getHitsCount() < 3)) || 
                                                                (($character->getClassType() === 'mage') && ($character->getHitsCount() < 3)) || 
                                                                (($character->getClassType() === 'archer') && ($character->getHitsCount() < 5)) || date('Y-m-d H:i:s') > $character->getNextHit() || $character->getNextHit() === NULL){ echo 'style="visibility:hidden;"';}?>>
                            <h6>Reset : <?=$character->getNextHit()?></h6>
                        </div>
                    </div>

                    <div id="healForm" class="row justify-content-around mb-1">
                        <div class="col-6 text-center">
                            <form action="" method="POST">
                                <input type="hidden" name="heal" value="heal">
                                <button id="healBtn" type="submit" class="btn btn-sm">Heal <?=$character->healPower()?> HP</button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        

        <div class="container-fluid opponents">
            <div class="row justify-content-around">
                <?php
                $opponents = $manager->getList($character->getName());

                if (empty($opponents)) {
                    echo 'No opponent available.';
                } else {
                    echo('
                        <div class="col-12 mb-3">
                            <h5 class="text-center text-white">Choose your target</h5>
                        </div>
                    ');
                    foreach ($opponents as $opponent) {
                        switch ($opponent->getClassType()) {
                            case 'warrior' : 
                                echo ('<div class="col-10 col-md-2 card cardWarrior cardAnim mx-2 mb-3">');
                                break;
                            case 'mage' : 
                                echo ('<div class="col-10 col-md-2 card cardMage cardAnim mx-2 mb-3">');
                                break;
                            case 'archer' : 
                                echo ('<div class="col-10 col-md-2 card cardArcher cardAnim mx-2 mb-3">');
                                break;
                                default : 
                                echo ('<div class="col-10 col-md-2 card cardDefault cardAnim mx-2 mb-3">');
                                break;
                        }
                        ?>
                                <div class="row justify-content-around card-body">
                                <h5 class="mb-3 text-center"><?=htmlspecialchars($opponent->getName())?></h5>
                                    <div class="col-11 text-center">
                                        <h6>Level <?=$opponent->getLevel()?></h6>
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
                                    
                                <div class="row justify-content-around opponentClass">
                                    <div class="col-12 text-center">
                                        <h5 class="mt-1 classType"><?=ucfirst($opponent->getClassType())?></h5>
                                    </div>
                                </div>

                                <div class="row justify-content-around mr-2 mt-1 mb-2">
                                    <div class="col-6 offset-2 text-center mr-4">
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
                                        <h6>Strength <?=$opponent->getStrength()?></h6>
                                    </div>
                                    <div class="col-6 text-center">
                                        <h6>Magic <?=$opponent->getMagic()?></h6>
                                    </div>
                                </div>

                                <div class="row justify-content-around mt-3">
                                    <div class="col-12 text-center">
                                        <h6>Last hit : <?=$opponent->getLastHit()?></h6>
                                    </div>
                                </div>

                                <div class="row justify-content-around mt-3">
                                    <div class="col-5 text-center">
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

?>
         <div class="row justify-content-start fightersCounter">
            <div class="col-12">
                <h5 class="text-white fightersNumber"><?=$manager->count()?> fighters in-game</h5>
            </div>
        </div>

        <div class="col-12 text-center mt-3 mb-5">
            <h4 class="text-white letsFight">Let's fight !</h4>
        </div>

        <div class="container">
            <div class="row justify-content-around text-center mb-4">
                <div class="col-10 col-md-4 card cardDark">
                    <div class="col-12 mb-3 card-body">
                        <h5>Create new fighter</h5>
                    </div>

                    <form action="" method="POST">
                        <div class="row justify-content-around text-center">      
                            <div class="col-12 mb-4">
                                <input type="text" name="name" class="col-6 mr-3 input-sm" minlength="2" maxlength="20" placeholder="Name">
                                <select name="classType" id="classType" class="col-4 mb-3" required>
                                    <option value="warrior" data-toggle="tooltip" title="Strength gain doubled and Damages halved">Warrior</option>
                                    <option value="mage" data-toggle="tooltip" title="XP gain and Magic gain doubled">Mage</option>
                                    <option value="archer" data-toggle="tooltip" title="XP gain doubled and 2 more Actions Points">Archer</option>
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
            </div>

            <div class="row justify-content-around text-center mb-2">
                <div class="col-6 or">
                    <h5><b class="text-white">OR</b></h5>
                </div>
            </div>

            <div class="row justify-content-around text-center">
                <div class="col-10 col-md-4 card cardDark">
                    <div class="col-12 mb-3 card-body">
                        <h5>Select a character</h5>
                    </div>

                    <form action="" method="POST">
                        <div class="row justify-content-around text-center"> 
                            <div class="col-12 mb-4">
                                <select name="fighter" id="fighter" class="col-6 mb-3" required>
                                <?php
                                    $allFighters = $manager->getAllFighters();
                                    foreach ($allFighters as $fighter) {
                                        echo ('<option value="'.$fighter['name'].'" data-toggle="tooltip" title="'.ucfirst($fighter['classType']).' level '.$fighter['level'].'">'.$fighter['name'].'</option>');
                                    }
                                    ?>
                                </select>
                            </div> 
                        </div>

                        <div class="row justify-content-center text-center">
                            <div class="col-8">
                                <input type="submit" name="select" class="btn btn-sm mx-3 input-sm" value="Select"/>
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
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.min.js"></script>
</body>
</html>