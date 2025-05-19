<?php
require_once 'includes/db_connect.php'; 
$sql_genres = "SELECT id, nom FROM genres ORDER BY nom ASC";
$result_genres = $conn->query($sql_genres);
$genres = [];
if ($result_genres && $result_genres->num_rows > 0) {
    while ($row = $result_genres->fetch_assoc()) {
        $genres[] = $row;
    }
}
session_start();
$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); 
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Livre - Catalogue de Bibliothèque</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box; 
        }
        .form-group input[type="file"] {
            padding: 3px;
        }
        .form-group textarea {
            resize: vertical; 
        }
        .btn-submit {
            background-color: #5cb85c;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-submit:hover {
            background-color: #4cae4c;
        }
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .message.success { background-color: #dff0d8; color: #3c763d; border: 1px solid #d6e9c6; }
        .message.error { background-color: #f2dede; color: #a94442; border: 1px solid #ebccd1; }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="index.php" style="color:white; text-decoration:none;">Catalogue des Livres</a></h1>
            <nav>
                <a href="index.php">Accueil</a>
                <a href="ajouter_livre.php">Ajouter un livre</a>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <h2>Ajouter un Nouveau Livre</h2>

            <?php if ($message): ?>
                <div class="message <?php echo strpos(strtolower($message), 'erreur') !== false || strpos(strtolower($message), 'échec') !== false ? 'error' : 'success'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form action="actions/process_ajout_livre.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="titre">Titre du livre (obligatoire) :</label>
                    <input type="text" id="titre" name="titre" required>
                </div>

                <div class="form-group">
                    <label for="auteurs">Auteur(s) (obligatoire, séparez les noms complets par une virgule) :</label>
                    <input type="text" id="auteurs" name="auteurs" required placeholder="Ex: Victor Hugo, J.K. Rowling">
                    <small>Si un auteur a un prénom et un nom, écrivez "Prénom Nom". Séparez plusieurs auteurs par une virgule.</small>
                </div>

                <div class="form-group">
                    <label for="resume">Résumé (obligatoire) :</label>
                    <textarea id="resume" name="resume" rows="5" required></textarea>
                </div>

                <div class="form-group">
                    <label for="id_genre">Genre (obligatoire) :</label>
                    <select id="id_genre" name="id_genre" required>
                        <option value="">-- Sélectionnez un genre --</option>
                        <?php foreach ($genres as $genre): ?>
                            <option value="<?php echo $genre['id']; ?>"><?php echo htmlspecialchars($genre['nom']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="annee_publication">Année de publication (obligatoire) :</label>
                    <input type="number" id="annee_publication" name="annee_publication" required min="0" max="<?php echo date('Y'); ?>">
                </div>

                <div class="form-group">
                    <label for="nombre_pages">Nombre de pages (obligatoire) :</label>
                    <input type="number" id="nombre_pages" name="nombre_pages" required min="1">
                </div>

                <div class="form-group">
                    <label for="image_couverture">Image de couverture (optionnel, .jpg, .jpeg, .png, .gif) :</label>
                    <input type="file" id="image_couverture" name="image_couverture" accept="image/jpeg,image/png,image/gif">
                    <small>Si aucune image n'est fournie, une image par défaut sera utilisée.</small>
                </div>

                <div class="form-group">
                    <label for="disponible">Disponible :</label>
                    <input type="checkbox" id="disponible" name="disponible" value="1" checked>
                </div>

                <button type="submit" class="btn-submit">Ajouter le Livre</button>
            </form>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>© <?php echo date('Y'); ?> Mini-Projet Bibliothèque</p>
        </div>
    </footer>

<?php
if (isset($conn)) {
    $conn->close();
}
?>
</body>
</html>