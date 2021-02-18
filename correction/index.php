<?php
// Gestion du téléchargement
$download = filter_input(INPUT_GET, "telechargement");
if($download && file_exists($download)) {
    header('Content-Type: text/html');
    header('Content-Disposition: attachment; filename="signature.html');
    readfile($download);
    exit();
}

// CSS utilisé à la fois dans la carte et dans la page
$cardStyle = '
.card {
    margin: 1rem;
    border: 1px grey solid;
    box-shadow: 0 3px 6px #999;
    padding: 1rem;
    display: grid;
    grid-template-rows: 1fr auto;
    background: #FCFCFC;
}
.card header {
    padding-bottom: 2rem;
}
.card h1 {
    font-size: 1.2rem;
}
.card h2 {
    font-size: 1rem;
    font-weight: normal;
}
.card footer {
    display: grid;
    grid-template-columns: 1fr 1fr;
    font-style: italic;
}
.card .email {
    text-align: right;
}

.card .phone {
    text-align: left;
}
.card h1, h2 {
    margin: 0;
}';
//$firstname = $_POST["firstname"] ?? null;
// Plus sécurisée et plus flexible
$firstname = filter_input(INPUT_POST, "firstname");
$lastname = filter_input(INPUT_POST, "lastname");
$jobtitle = filter_input(INPUT_POST, "jobtitle");
$email = filter_input(INPUT_POST, "email", FILTER_VALIDATE_EMAIL);
$phone = filter_input(INPUT_POST, "phone");

$card = null;
// Si le formulaire est rempli
if($firstname && $lastname && $jobtitle && $email && $phone) {
    $card = [
        "firstname" => $firstname,
        "lastname" => $lastname,
        "jobtitle" => $jobtitle,
        "email" => $email,
        "phone" => $phone,
        // Fichier dans lequel on stocke la carte
        "filename" => uniqid("vcard-") . ".html"
    ];
    // Code HTML de la carte
    $html = sprintf('
    <div class="card">
        <style>
            %s
        </style>
        <header>
            <h1>%s %s</h1>
            <h2>%s</h2>
        </header>
        <footer>
            <span class="phone">%s</span>
            <span class="email">%s</span>
        </footer>
    </div>
    ', 
    // Données qui remplaceront les %s
        $cardStyle, $card["firstname"], $card["lastname"], 
        $card["jobtitle"], $card["phone"], $card["email"]
    );
    // Stockage dans le fichier
    file_put_contents($card["filename"], trim($html));
}

$previous = [];
foreach (new DirectoryIterator('.') as $fileInfo) {
    //var_dump($fileInfo->getFilename());
    if($fileInfo->isDot()) continue;
    if(!preg_match('/vcard\-.*\.html/', $fileInfo->getFilename()))
        continue;
    
    // Il n'y a que des fichiers qui commencent par vcard-
    // et qui finissent par .html (donc des vCards)
    $previous[$fileInfo->getFilename()] = file_get_contents($fileInfo->getPathname());
    
    // Si 4 éléments, on quitte la boucle
    if(count($previous) > 3) break;
}
?><!doctype html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>vCard</title>
        <style>
html, body { margin: 0; padding: 0; }
body { 
    display: grid;
    grid-template-columns: auto 1fr;
}
.card-list {
    padding: 1rem;
    margin-right: 1rem;
    background: #EDEDED;
}
.card-list a {
    display: block;
    text-align: center;
}
main > h1 { text-align: center; }
form {
    display: grid;
    grid-template-columns: 1fr 1fr;
    grid-auto-rows: min-content;
    grid-row-gap: 1rem;
    grid-column-gap: 1rem;
}
form input[type='submit'] {
    grid-column: 1 / span 2;
}
<?= $cardStyle ?>
        </style>
    </head>
    <body>
        <div class="card-list">
            <?php foreach($previous as $name => $prev): ?>
            <?= $prev ?>
            <a href="index.php?telechargement=<?= $name ?>">Télécharger</a>
            <hr/>
            <?php endforeach; ?>
        </div>
        <main>
        <?php if(is_null($card)): ?>
            <h1>Créer votre vCard</h1>
            <form method="POST">
                <label for="firstname">Prénom</label>
                <input required type="text" name="firstname" id="firstname" value="John" />
                
                <label for="lastname">Nom</label>
                <input required type="text" name="lastname" id="lastname" value="Doe" />
                
                <label for="jobtitle">Poste</label>
                <input required type="text" name="jobtitle" id="jobtitle" value="WebDev" />
                
                <label for="email">Adresse mail</label>
                <input required type="email" name="email" id="email" value="j.doe@webdev.fr" />
                
                <label for="phone">Téléphone</label>
                <input required type="text" name="phone" id="phone" value="0123456789" />
                
                <input type="submit" value="Créer la carte" />
            </form>
            <?php else: ?>
                <?= file_get_contents($card["filename"]) ?>
                <a href="index.php?telechargement=<?= $card["filename"] ?>">Télécharger</a>
            <?php endif; ?>
        </main>
    </body>
</html>