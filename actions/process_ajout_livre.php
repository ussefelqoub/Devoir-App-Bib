<?php
session_start(); // Démarrer la session pour stocker les messages
require_once '../includes/db_connect.php'; // Remonter d'un niveau pour accéder à includes/

// Fonction simple pour rediriger avec un message
function redirect_with_message($message, $is_error = false) {
    $_SESSION['message'] = $message;
    header('Location: ../ajouter_livre.php'); // Rediriger vers la page du formulaire
    exit();
}

// Vérifier si le formulaire a été soumis en POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Récupérer les données du formulaire (avec une protection basique)
    $titre = trim($_POST['titre'] ?? '');
    $auteurs_str = trim($_POST['auteurs'] ?? ''); // Chaîne d'auteurs séparés par des virgules
    $resume = trim($_POST['resume'] ?? '');
    $id_genre = filter_input(INPUT_POST, 'id_genre', FILTER_VALIDATE_INT);
    $annee_publication = filter_input(INPUT_POST, 'annee_publication', FILTER_VALIDATE_INT);
    $nombre_pages = filter_input(INPUT_POST, 'nombre_pages', FILTER_VALIDATE_INT);
    $disponible = isset($_POST['disponible']) ? 1 : 0; // 1 si coché, 0 sinon

    // 2. Validation simple côté serveur (champs obligatoires)
    if (empty($titre) || empty($auteurs_str) || empty($resume) || $id_genre === false || $annee_publication === false || $nombre_pages === false) {
        redirect_with_message("Erreur : Tous les champs obligatoires (titre, auteurs, résumé, genre, année, pages) doivent être remplis.", true);
    }
    if ($annee_publication > date('Y') || $annee_publication < 1000) { // Année simple check
        redirect_with_message("Erreur : Année de publication invalide.", true);
    }
    if ($nombre_pages < 1) {
        redirect_with_message("Erreur : Le nombre de pages doit être au moins 1.", true);
    }


    // 3. Gestion de l'image de couverture (simplifiée)
    $nom_image_couverture = 'default_cover.jpg'; // Valeur par défaut
    if (isset($_FILES['image_couverture']) && $_FILES['image_couverture']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../images/'; // Dossier où stocker les images (remonter d'un niveau)
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['image_couverture']['type'];

        if (in_array($file_type, $allowed_types)) {
            // Générer un nom de fichier unique pour éviter les écrasements
            $extension = pathinfo($_FILES['image_couverture']['name'], PATHINFO_EXTENSION);
            $nom_image_couverture = uniqid('cover_', true) . '.' . $extension;
            $destination = $upload_dir . $nom_image_couverture;

            if (!move_uploaded_file($_FILES['image_couverture']['tmp_name'], $destination)) {
                // Si l'upload échoue, on utilise l'image par défaut mais on pourrait afficher une erreur
                $nom_image_couverture = 'default_cover.jpg';
                // Pour un débutant, on ne bloque pas tout pour une image optionnelle
                // redirect_with_message("Erreur lors du téléchargement de l'image.", true);
            }
        } else {
            // Type de fichier non autorisé, on utilise l'image par défaut
            $nom_image_couverture = 'default_cover.jpg';
            // redirect_with_message("Erreur : Type de fichier image non autorisé.", true);
        }
    }


    // Démarrer une transaction (bonne pratique pour les opérations multiples)
    $conn->begin_transaction();

    try {
        // 4. Insérer le livre dans la table `livres` (SANS les auteurs pour l'instant)
        // On utilise des requêtes préparées pour la sécurité
        $stmt_livre = $conn->prepare("INSERT INTO livres (titre, resume, annee_publication, nombre_pages, id_genre, image_couverture, disponible) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt_livre === false) {
            throw new Exception("Erreur de préparation de la requête livre : " . $conn->error);
        }
        // 'ssiiisi' : s pour string, i pour integer
        $stmt_livre->bind_param('ssiiisi', $titre, $resume, $annee_publication, $nombre_pages, $id_genre, $nom_image_couverture, $disponible);

        if (!$stmt_livre->execute()) {
            throw new Exception("Erreur lors de l'ajout du livre : " . $stmt_livre->error);
        }
        $id_nouveau_livre = $conn->insert_id; // Récupérer l'ID du livre qui vient d'être inséré
        $stmt_livre->close();


        // 5. Gestion des auteurs (simplifiée)
        $auteurs_noms_complets = explode(',', $auteurs_str); // Sépare la chaîne "Prénom1 Nom1, Prénom2 Nom2"
        $ids_auteurs_pour_ce_livre = [];

        foreach ($auteurs_noms_complets as $nom_complet_auteur) {
            $nom_complet_auteur = trim($nom_complet_auteur);
            if (empty($nom_complet_auteur)) continue; // Ignorer les entrées vides

            // On va séparer le prénom et le nom (très basique)
            // Suppose "Prénom Nom". Si juste un mot, on le met en Nom.
            $parties_nom = explode(' ', $nom_complet_auteur, 2);
            $prenom_auteur = (count($parties_nom) > 1) ? trim($parties_nom[0]) : '';
            $nom_auteur = (count($parties_nom) > 1) ? trim($parties_nom[1]) : trim($parties_nom[0]);

            // Vérifier si l'auteur existe déjà (basé sur nom et prénom)
            $stmt_check_auteur = $conn->prepare("SELECT id FROM auteurs WHERE nom = ? AND prenom = ?");
            if ($stmt_check_auteur === false) throw new Exception("Erreur prép. check auteur: " . $conn->error);
            $stmt_check_auteur->bind_param('ss', $nom_auteur, $prenom_auteur);
            $stmt_check_auteur->execute();
            $result_auteur = $stmt_check_auteur->get_result();
            $id_auteur_actuel = 0;

            if ($result_auteur->num_rows > 0) {
                // L'auteur existe, on récupère son ID
                $row_auteur = $result_auteur->fetch_assoc();
                $id_auteur_actuel = $row_auteur['id'];
            } else {
                // L'auteur n'existe pas, on l'ajoute
                $stmt_insert_auteur = $conn->prepare("INSERT INTO auteurs (nom, prenom) VALUES (?, ?)");
                if ($stmt_insert_auteur === false) throw new Exception("Erreur prép. insert auteur: " . $conn->error);
                $stmt_insert_auteur->bind_param('ss', $nom_auteur, $prenom_auteur);
                if (!$stmt_insert_auteur->execute()) {
                    throw new Exception("Erreur lors de l'ajout de l'auteur : " . $stmt_insert_auteur->error);
                }
                $id_auteur_actuel = $conn->insert_id;
                $stmt_insert_auteur->close();
            }
            $stmt_check_auteur->close();

            if ($id_auteur_actuel > 0) {
                $ids_auteurs_pour_ce_livre[] = $id_auteur_actuel;
            }
        }

        // 6. Lier le(s) auteur(s) au livre dans la table `livre_auteur`
        if (!empty($ids_auteurs_pour_ce_livre)) {
            $stmt_livre_auteur = $conn->prepare("INSERT INTO livre_auteur (id_livre, id_auteur) VALUES (?, ?)");
            if ($stmt_livre_auteur === false) throw new Exception("Erreur prép. livre_auteur: " . $conn->error);

            foreach ($ids_auteurs_pour_ce_livre as $id_auteur) {
                $stmt_livre_auteur->bind_param('ii', $id_nouveau_livre, $id_auteur);
                if (!$stmt_livre_auteur->execute()) {
                    // On pourrait gérer les doublons ici (PRIMARY KEY (id_livre, id_auteur))
                    // Pour un débutant, on ignore l'erreur si elle est due à un doublon.
                    // Si ce n'est pas une erreur de clé dupliquée, alors c'est un problème.
                    if ($conn->errno != 1062) { // 1062 = Erreur de clé dupliquée
                         throw new Exception("Erreur lors de la liaison livre-auteur : " . $stmt_livre_auteur->error);
                    }
                }
            }
            $stmt_livre_auteur->close();
        }

        // Si tout s'est bien passé, on valide la transaction
        $conn->commit();
        redirect_with_message("Livre ajouté avec succès !");

    } catch (Exception $e) {
        // En cas d'erreur, on annule la transaction
        $conn->rollback();
        // Afficher un message d'erreur plus technique pour le débogage
        // Dans un environnement de production, loguer cette erreur et afficher un message générique.
        redirect_with_message("Échec de l'ajout du livre : " . $e->getMessage(), true);
    }

} else {
    // Si la page est accédée directement sans soumission de formulaire
    redirect_with_message("Accès non autorisé.", true);
}

// Fermer la connexion (si elle n'est pas déjà fermée par la redirection)
if (isset($conn)) {
    $conn->close();
}
?>