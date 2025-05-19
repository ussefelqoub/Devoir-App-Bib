<?php
// 1. Inclure le fichier de connexion à la base de données
require_once 'includes/db_connect.php';

// Récupérer les genres pour le filtre
$sql_all_genres = "SELECT id, nom FROM genres ORDER BY nom ASC";
$result_all_genres = $conn->query($sql_all_genres);
$all_genres = [];
if ($result_all_genres && $result_all_genres->num_rows > 0) {
    while ($row_genre = $result_all_genres->fetch_assoc()) {
        $all_genres[] = $row_genre;
    }
}

// 2. Gestion des filtres et tris
$genre_filter = isset($_GET['genre_filter']) && $_GET['genre_filter'] !== '' ? (int)$_GET['genre_filter'] : null;
$sort_order = isset($_GET['sort_order']) && in_array($_GET['sort_order'], ['ASC', 'DESC']) ? $_GET['sort_order'] : 'ASC'; // Défaut A-Z

// Construire la requête SQL de base
$sql_base = "SELECT
                l.id,
                l.titre,
                l.resume,
                l.image_couverture,
                l.disponible,
                l.annee_publication,
                l.nombre_pages,
                g.nom AS genre_nom
            FROM livres l
            JOIN genres g ON l.id_genre = g.id";

// Clauses WHERE et ORDER BY dynamiques
$where_clauses = [];
$params = []; // Pour les requêtes préparées si on les utilisait ici (plus complexe avec WHERE dynamique)
$types = "";  // Pour les types de bind_param

if ($genre_filter !== null) {
    $where_clauses[] = "l.id_genre = ?";
    $params[] = $genre_filter;
    $types .= "i";
}

$sql_final = $sql_base;
if (!empty($where_clauses)) {
    $sql_final .= " WHERE " . implode(" AND ", $where_clauses);
}

// Ajout du tri
if ($sort_order == 'ASC') {
    $sql_final .= " ORDER BY l.titre ASC";
} else {
    $sql_final .= " ORDER BY l.titre DESC";
}

// Exécution de la requête (on va utiliser une requête préparée pour la partie filtre)
if ($genre_filter !== null) {
    $stmt = $conn->prepare($sql_final);
    if ($stmt === false) {
        die("Erreur de préparation de la requête : " . $conn->error);
    }
    $stmt->bind_param($types, ...$params); // Splat operator pour passer les paramètres
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Si pas de filtre, requête simple (le tri est déjà dans $sql_final)
    $result = $conn->query($sql_final);
    if ($result === false) {
        die("Erreur d'exécution de la requête : " . $conn->error);
    }
}


// On va stocker les livres dans un tableau pour faciliter la gestion des auteurs plus tard
$livres = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Pour chaque livre, on doit récupérer ses auteurs (relation N-N)
        // Cette partie reste la même
        $sql_auteurs = "SELECT a.prenom, a.nom
                        FROM auteurs a
                        JOIN livre_auteur la ON a.id = la.id_auteur
                        WHERE la.id_livre = " . $row['id']; // Attention, pas de requête préparée ici pour simplifier
        $result_auteurs = $conn->query($sql_auteurs);
        $auteurs_list = [];
        if ($result_auteurs && $result_auteurs->num_rows > 0) {
            while ($auteur = $result_auteurs->fetch_assoc()) {
                $auteurs_list[] = htmlspecialchars(trim($auteur['prenom'] . ' ' . $auteur['nom']));
            }
        }
        $row['auteurs'] = implode(', ', $auteurs_list);
        $livres[] = $row;
    }
}
if (isset($stmt)) $stmt->close(); // Fermer le statement si utilisé
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalogue de Bibliothèque</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .filters-sort form {
            display: flex;
            gap: 20px; /* Espace entre les éléments du formulaire */
            align-items: center; /* Aligner verticalement les éléments */
            margin-bottom: 20px;
            flex-wrap: wrap; /* Permettre le retour à la ligne sur petits écrans */
        }
        .filters-sort label {
            font-weight: bold;
        }
        .filters-sort select, .filters-sort button {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .filters-sort button {
            background-color: #5cb85c;
            color: white;
            cursor: pointer;
        }
        .filters-sort button:hover {
            background-color: #4cae4c;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>Catalogue des Livres</h1>
            <nav>
                <a href="index.php">Accueil</a>
                <a href="ajouter_livre.php">Ajouter un livre</a>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <section class="filters-sort">
                <h2>Filtres et Tri</h2>
                <form method="GET" action="index.php">
                    <div>
                        <label for="genre_filter">Filtrer par genre :</label>
                        <select name="genre_filter" id="genre_filter">
                            <option value="">Tous les genres</option>
                            <?php foreach ($all_genres as $genre_item): ?>
                                <option value="<?php echo $genre_item['id']; ?>" <?php if ($genre_filter == $genre_item['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($genre_item['nom']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="sort_order">Trier par titre :</label>
                        <select name="sort_order" id="sort_order">
                            <option value="ASC" <?php if ($sort_order == 'ASC') echo 'selected'; ?>>A-Z</option>
                            <option value="DESC" <?php if ($sort_order == 'DESC') echo 'selected'; ?>>Z-A</option>
                        </select>
                    </div>

                    <button type="submit">Appliquer</button>
                </form>
            </section>

            <section class="book-grid">
                <?php if (!empty($livres)): ?>
                    <?php foreach ($livres as $livre): ?>
                        <article class="book-card">
                            <div class="book-cover">
                                <img src="images/<?php echo htmlspecialchars($livre['image_couverture'] ? $livre['image_couverture'] : 'default_cover.jpg'); ?>"
                                     alt="Couverture de <?php echo htmlspecialchars($livre['titre']); ?>">
                            </div>
                            <div class="book-info">
                                <h2><?php echo htmlspecialchars($livre['titre']); ?></h2>
                                <p><strong>Auteur(s):</strong> <?php echo $livre['auteurs'] ? $livre['auteurs'] : 'Non spécifié'; ?></p>
                                <p><strong>Genre:</strong> <?php echo htmlspecialchars($livre['genre_nom']); ?></p>
                                <p><strong>Année:</strong> <?php echo htmlspecialchars($livre['annee_publication']); ?></p>
                                <p><strong>Pages:</strong> <?php echo htmlspecialchars($livre['nombre_pages']); ?></p>
                                <p class="resume"><strong>Résumé:</strong> <?php echo nl2br(htmlspecialchars($livre['resume'])); ?></p>
                                <p><strong>Disponibilité:</strong>
                                    <span class="<?php echo $livre['disponible'] ? 'disponible' : 'emprunte'; ?>">
                                        <?php echo $livre['disponible'] ? 'Disponible' : 'Emprunté'; ?>
                                    </span>
                                </p>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Aucun livre trouvé correspondant à vos critères.</p>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>© <?php echo date('Y'); ?> Mini-Projet Bibliothèque</p>
        </div>
    </footer>

<?php
// Fermer la connexion à la base de données
if (isset($conn)) {
    $conn->close();
}
?>
</body>
</html>