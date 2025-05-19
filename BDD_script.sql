SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS livre_auteur;
DROP TABLE IF EXISTS livres;
DROP TABLE IF EXISTS auteurs;
DROP TABLE IF EXISTS genres;

-- Réactiver la vérification des clés étrangères
SET FOREIGN_KEY_CHECKS=1;

-- --------------------------------------------------------
-- Table `genres`
-- Stocke les différents genres de livres.
-- --------------------------------------------------------
CREATE TABLE genres (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nom VARCHAR(50) NOT NULL UNIQUE, -- Le nom du genre doit être unique
  description TEXT -- Une description optionnelle du genre
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table `auteurs`
-- Stocke les informations sur les auteurs.
-- --------------------------------------------------------
CREATE TABLE auteurs (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nom VARCHAR(100) NOT NULL,
  prenom VARCHAR(100), -- Le prénom peut être optionnel pour certains auteurs ou si non connu
  biographie TEXT, -- Biographie optionnelle
  date_naissance DATE -- Date de naissance optionnelle
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table `livres`
-- Stocke les informations sur les livres.
-- Chaque livre est associé à un genre via id_genre.
-- --------------------------------------------------------
CREATE TABLE livres (
  id INT PRIMARY KEY AUTO_INCREMENT,
  titre VARCHAR(200) NOT NULL,
  resume TEXT NOT NULL,
  annee_publication INT NOT NULL,
  nombre_pages INT NOT NULL,
  id_genre INT NOT NULL, -- Clé étrangère vers la table `genres`
  image_couverture VARCHAR(255) DEFAULT 'default_cover.jpg', -- Nom du fichier image, avec une valeur par défaut
  disponible BOOLEAN DEFAULT TRUE, -- TRUE si disponible, FALSE si emprunté
  date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Date et heure d'ajout du livre, par défaut à la date actuelle
  FOREIGN KEY (id_genre) REFERENCES genres(id)
    ON DELETE RESTRICT -- Empêche de supprimer un genre s'il est utilisé par des livres
    ON UPDATE CASCADE -- Si l'ID d'un genre change, met à jour ici aussi
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table de jonction `livre_auteur`
-- Gère la relation Plusieurs-à-Plusieurs entre les livres et les auteurs.
-- Un livre peut avoir plusieurs auteurs, et un auteur peut avoir écrit plusieurs livres.
-- --------------------------------------------------------
CREATE TABLE livre_auteur (
  id_livre INT, -- Clé étrangère vers la table `livres`
  id_auteur INT, -- Clé étrangère vers la table `auteurs`
  PRIMARY KEY (id_livre, id_auteur), -- La combinaison id_livre et id_auteur doit être unique
  FOREIGN KEY (id_livre) REFERENCES livres(id)
    ON DELETE CASCADE -- Si un livre est supprimé, les associations correspondantes sont supprimées
    ON UPDATE CASCADE,
  FOREIGN KEY (id_auteur) REFERENCES auteurs(id)
    ON DELETE CASCADE -- Si un auteur est supprimé, les associations correspondantes sont supprimées
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------
-- Insertion de données initiales pour le test
-- --------------------------------------------------------

-- Insertion dans la table `genres`
INSERT INTO genres (nom, description) VALUES
('Science-Fiction', 'Récits se déroulant dans un futur imaginaire, souvent basés sur des avancées scientifiques ou technologiques.'),
('Fantasy', 'Récits merveilleux se déroulant dans des mondes imaginaires peuplés de créatures magiques et de sorcellerie.'),
('Roman Policier', 'Récits centrés sur la résolution d''une énigme, souvent un crime, par un détective ou un enquêteur.'),
('Classique', 'Œuvres littéraires reconnues pour leur valeur universelle et intemporelle.'),
('Aventure', 'Récits d''actions et de péripéties, souvent dans des lieux exotiques ou inconnus.');

-- Insertion dans la table `auteurs`
-- Les IDs seront auto-incrémentés (1, 2, 3, 4, 5...)
INSERT INTO auteurs (nom, prenom, biographie, date_naissance) VALUES
('Herbert', 'Frank', 'Auteur américain de science-fiction, célèbre pour son roman Dune.', '1920-10-08'),
('Tolkien', 'J.R.R.', 'Philologue et écrivain britannique, auteur du Seigneur des Anneaux et du Hobbit.', '1892-01-03'),
('Conan Doyle', 'Arthur', 'Écrivain et médecin britannique, créateur du célèbre détective Sherlock Holmes.', '1859-05-22'),
('Orwell', 'George', 'Écrivain et journaliste britannique, connu pour 1984 et La Ferme des animaux.', '1903-06-25'),
('Hugo', 'Victor', 'Poète, dramaturge, écrivain, romancier et dessinateur romantique français.', '1802-02-26');

-- Insertion dans la table `livres`
-- On utilise les IDs des genres insérés précédemment (SF=1, Fantasy=2, Policier=3, Classique=4, Aventure=5)
INSERT INTO livres (titre, resume, annee_publication, nombre_pages, id_genre, image_couverture, disponible) VALUES
('Dune', 'Sur la planète désertique Arrakis, Paul Atréides et sa famille luttent pour le contrôle de l''Épice...', 1965, 600, 1, 'dune.jpg', TRUE),
('Le Seigneur des Anneaux', 'Frodon Sacquet hérite d''un anneau puissant qu''il doit détruire pour sauver la Terre du Milieu.', 1954, 1200, 2, 'lotr.jpg', FALSE),
('Les Aventures de Sherlock Holmes', 'Recueil de douze nouvelles mettant en scène le détective Sherlock Holmes et son acolyte le Dr. Watson.', 1892, 320, 3, 'sherlock.jpg', TRUE),
('1984', 'Dans un régime totalitaire, Winston Smith tente de résister à la surveillance omniprésente de Big Brother.', 1949, 350, 1, '1984.jpg', TRUE),
('Les Misérables', 'Une fresque sociale de la France du XIXe siècle, suivant le destin de plusieurs personnages dont Jean Valjean.', 1862, 1500, 4, 'miserables.jpg', TRUE),
('Le Tour du Monde en quatre-vingts jours', 'Phileas Fogg, gentleman anglais, parie qu''il peut faire le tour du monde en 80 jours.', 1872, 250, 5, 'tourdumonde.jpg', TRUE);


-- Insertion dans la table `livre_auteur`
-- On lie les livres à leurs auteurs en utilisant leurs IDs respectifs.
-- (Suppose que les IDs des livres sont 1, 2, 3, 4, 5, 6 et ceux des auteurs 1, 2, 3, 4, 5)

-- Dune (ID livre 1) par Frank Herbert (ID auteur 1)
INSERT INTO livre_auteur (id_livre, id_auteur) VALUES (1, 1);
-- Le Seigneur des Anneaux (ID livre 2) par J.R.R. Tolkien (ID auteur 2)
INSERT INTO livre_auteur (id_livre, id_auteur) VALUES (2, 2);
-- Les Aventures de Sherlock Holmes (ID livre 3) par Arthur Conan Doyle (ID auteur 3)
INSERT INTO livre_auteur (id_livre, id_auteur) VALUES (3, 3);
-- 1984 (ID livre 4) par George Orwell (ID auteur 4)
INSERT INTO livre_auteur (id_livre, id_auteur) VALUES (4, 4);
-- Les Misérables (ID livre 5) par Victor Hugo (ID auteur 5)
INSERT INTO livre_auteur (id_livre, id_auteur) VALUES (5, 5);

-- Pour 'Le Tour du Monde en quatre-vingts jours' (ID livre 6), supposons qu'on doive ajouter Jules Verne
INSERT INTO auteurs (nom, prenom, biographie, date_naissance) VALUES
('Verne', 'Jules', 'Écrivain français dont l''œuvre est, pour la plus grande partie, constituée de romans d''aventures.', '1828-02-08');
-- Supposons que Jules Verne ait obtenu l'ID 6
INSERT INTO livre_auteur (id_livre, id_auteur) VALUES (6, 6); -- Associe livre 6 à auteur 6 (Jules Verne)