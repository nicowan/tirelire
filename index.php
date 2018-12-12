<?php
/**
 * Gestion de tirelire
 * Nicolas Wanner
 * 5.12.2018
 * 
 */
include('tirelire.php');
$app = Tirelire::Open('myApp',false);
$app->controller();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Tirelire</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
  <form action="index.php" method="post">
    <div id="container">
      <h1>Ma tirelire - Total <?= $app->total() ?> Fr.</h1>
      <button id="vider" type="submit" name="action" value="vider">Tout retirer</button>
      <div id="contenu">
        <?php foreach ($app->coins as $index => $coin) { ?>
        <div class="columns">
          <fieldset >
              <legend>Pièce de <?= $coin->value ?> Fr</legend>
              <p><label>Nb de pièces : </label>
              <input type="text" name="<?= $index ?>" value="1"></p>
              <button type="submit" name="action" value="ajouter <?= $index ?>">Ajouter</button>
              <button type="submit" name="action" value="enlever <?= $index ?>">Enlever</button>
              <p>Sous-total = <?= $coin->total() ?> Fr.</p>
              <p class="red"><?= $coin->message . '&nbsp' ?></p>
          </fieldset>
          <fieldset class="pieces">
              <legend>Visualisation des pièces</legend>
              <?php for($i=0; $i<$coin->count; $i++) {
                echo "<img src=\"$coin->image\">";
              } ?>
          </fieldset>
        </div>
        <?php } ?>
      </div>
    </div>
  </form>
</body>
</html>