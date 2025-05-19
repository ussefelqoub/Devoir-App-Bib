# Mini-Projet de Gestion de Produits (Bibliothèque en ligne)

Ce projet est une application web simple développée en PHP avec une base de données MySQL pour gérer un catalogue de livres.

## Fonctionnalités

*   Affichage d'une liste de livres sous forme de grille.
*   Informations affichées pour chaque livre : image de couverture, titre, auteur(s), résumé court, genre/          catégorie, disponibilité.
*   Affichage responsive (adapté aux différentes tailles d'écran).
*   Filtrage des livres par genre via un menu déroulant.
*   Tri des livres par titre (A-Z et Z-A) via un menu déroulant.
*   Le filtre et le tri fonctionnent en combinaison.
*   Page dédiée pour l'ajout de nouveaux livres avec un formulaire.
*   Champs du formulaire d'ajout : Titre, Auteur(s), Résumé, Genre (sélection parmi existants), Année de publication, Nombre de pages, Image de couverture (optionnelle), Disponibilité.
*   Validations des données côté client (HTML5) et côté serveur.
*   Gestion de l'upload d'image pour la couverture (avec image par défaut si non fournie).
*   Gestion des auteurs (création si nouvel auteur, liaison avec auteurs existants).

## Prérequis

*   Un serveur web local (Ex: WampServer) avec PHP et MySQL.
*   Un navigateur web.

## Installation

1.  **Cloner ou télécharger le projet** dans le dossier `www` de votre serveur local. Par exemple : `C:\wamp64\www\mini_projet_bibliotheque\`.
2.  **Démarrer les services Apache et MySQL** de votre serveur local.
3.  **Créer la base de données :**
    *   Ouvrez phpMyAdmin (généralement accessible via `http://localhost/phpmyadmin`).
    *   Créez une nouvelle base de données nommée `bibliotheque_db`.
4.  **Importer la structure et les données initiales :**
    *   Sélectionnez la base `bibliotheque_db` dans phpMyAdmin.
    *   Allez dans l'onglet "SQL".
    *   Copiez le contenu du fichier `BDD_script.sql` fourni avec le projet.
    *   Collez-le dans la zone de texte SQL et exécutez la requête.
5.  **Vérifier la configuration de la base de données (si nécessaire) :**
    *   Le fichier `includes/db_connect.php` contient les informations de connexion. Par défaut :
        *   Serveur : `localhost`
        *   Utilisateur : `root`
        *   Mot de passe : `(vide)`
        *   Nom de la base : `bibliotheque_db`
    *   Modifiez ces informations si votre configuration MySQL est différente.
6.  **Accéder à l'application :**
    *   Ouvrez votre navigateur et allez à `http://localhost/mini_projet_bibliotheque/` (adaptez le chemin si vous avez nommé le dossier différemment).

## Utilisation

*   **Page d'accueil (`index.php`) :** Affiche la liste des livres. Utilisez les menus déroulants pour filtrer par genre et trier par titre, puis cliquez sur "Appliquer".
*   **Page d'ajout (`ajouter_livre.php`) :** Accessible via le lien "Ajouter un livre" dans la navigation. Remplissez le formulaire et cliquez sur "Ajouter le Livre".

---
Auteur du projet : EZZIYARA Adam/ELQOUB Youssef