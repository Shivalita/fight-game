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

        $manager = new CharactersManager($db);

        echo ('<h5 class="mt-4 ml-3">Fighters in-game : '.$manager->count().'</h5>');

        if (isset($_POST['create']) && !empty($_POST['name'])) {
            $character = new Character(['name' => $_POST['name']]);

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
        }

        if (isset($message)) {
        echo '<p class="my-4">'.$message.'</p>';
        }

        if (isset($character)) {
            ?>
            <div class="container">
                <div class="row justify-content-around">
                    <div class="card">
                        <legend class="mb-4">My fighter</legend>
                        <h4>Name : <?=$character->getName()?></h4></br>
                        <h4>Damages : <?=$character->getDamages()?></h4>
                    </div>
                </div>
            </div>

            <div class="container">
                <div class="row justify-content-around">
                    <div class="card">
                        <legend class="mb-4">Choose your target</legend>
                        <div>

                            <?php
                            $opponents = $manager->getList();

                            if (empty($opponents)) {
                                echo 'No opponent available.';
                            } else {
                                foreach ($opponents as $opponent) {
                                    echo ('<a href="hit '.$opponent->getId().'">'.$opponent->getName().'</a> (damages : '.$opponent->getDamages().')</br>');
                                }    
                            }
                            ?>

                        </div>
                    </div>
                </div>
            </div>
        <?php
        } else {
        ?>
            <div class="container">
                <div class="row justify-content-around text-center">
                    <div class="col-12 mb-5">
                        <h4>Select a character or create new fighter</h4>
                    </div>
                </div>

                <form action="" method="POST">
                    <div class="row justify-content-around text-center">      
                        <div class="col-12 mb-4">
                            <input type="text" name="name" minlength="2" maxlength="20" placeholder="Name">
                        </div> 
                    </div> 
                    <div class="row justify-content-center text-center">
                        <div class="col-8">
                            <input type="submit" name="create" class="btn btn-dark mx-3" value="Create"/>
                            <input type="submit" name="select" class="btn btn-dark mx-3" value="Select"/>
                        </div> 
                    </div>
                </form>
            </div>
        <?php
        }
        ?>
    </div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.min.js"></script>
</body>
</html>