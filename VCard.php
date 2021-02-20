<?php
// Gestion du téléchargement
$download = filter_input(INPUT_GET, "telechargement");
if ($download && file_exists($download)) {
    header('Content-Type: text/html');
    header('Content-Disposition: attachment; filename="signature.html');
    readfile($download);
    exit();
}

//$firstname = $_POST["firstname"] ?? null;
// Plus sécurisée et plus flexible
$firstname = filter_input(INPUT_POST, "firstname");
$lastname = filter_input(INPUT_POST, "lastname");
$jobtitle = filter_input(INPUT_POST, "jobtitle");
$email = filter_input(INPUT_POST, "email", FILTER_VALIDATE_EMAIL);
$phone = filter_input(INPUT_POST, "phone");

$card = null;
// Si le formulaire est rempli
if ($firstname && $lastname && $email && $phone) {
    $card = [
        "firstname" => $firstname,
        "lastname" => $lastname,
        "email" => $email,
        "phone" => $phone,
        // Fichier dans lequel on stocke la carte
        "filename" => uniqid("vcard-") . ".html"
    ];
    // Code HTML de la carte
    $html = sprintf(
        '
    <div class="card">
        <header>
            <h1>%s %s</h1>
        </header>
        <footer>
            <div>
                <span class="phone">Téléphone : %s</span>
            </div>
            <hr>
            <div>
                <span class="email">Email : %s</span>
            </div>
        </footer>
    </div>
    ',
        // Données qui remplaceront les %s

        $card["firstname"],
        $card["lastname"],
        $card["phone"],
        $card["email"]
    );
    // Stockage dans le fichier
    file_put_contents($card["filename"], trim($html));
}

$previous = [];
foreach (new DirectoryIterator('.') as $fileInfo) {
    //var_dump($fileInfo->getFilename());
    if ($fileInfo->isDot()) continue;
    if (!preg_match('/vcard\-.*\.html/', $fileInfo->getFilename()))
        continue;

    // Il n'y a que des fichiers qui commencent par vcard-
    // et qui finissent par .html (donc des vCards)
    $previous[$fileInfo->getFilename()] = file_get_contents($fileInfo->getPathname());

    // Si 4 éléments, on quitte la boucle
    if (count($previous) > 3) break;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="vCard.scss">
    <title>vCard</title>
</head>


</div>
<main>
    <?php if (is_null($card)) : ?>
        <h1> Carte de visite virtuelle </h1>
        <div class="card">
            <form method="POST">
                <div>
                    <label for="firstname">Votre prénom :</label>
                    </br>
                    <input required type="text" name="firstname" id="firstname" placeholder="John" />
                </div>
                <div>
                    <label for="lastname">Votre nom :</label> </br>
                    <input required type="text" name="lastname" id="lastname" placeholder="Doe" />
                </div>
                <div>
                    <label for="email">Adresse mail</label>
                    </br>
                    <input required type="email" name="email" id="email" placeholder="j.doe@webdev.fr" />
                </div>
                <div>
                    <label for="phone">Téléphone</label>
                    </br>
                    <input required type="text" name="phone" id="phone" placeholder="0123456789" />
                </div>
        </div>
        <input class="cree_card" type="submit" value="Créer la carte" />
        </form>


    <?php else : ?>
        <?= file_get_contents($card["filename"]) ?>
        <a href="vCard.php?telechargement=<?= $card["filename"] ?>"> Télécharger </a>
    <?php endif; ?>
    </body>

</html>