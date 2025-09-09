-- phpMyAdmin SQL Dump
-- version 5.2.1deb1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : mar. 09 sep. 2025 à 13:43
-- Version du serveur : 10.11.6-MariaDB-0+deb12u1
-- Version de PHP : 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `fep-frma`
--

-- --------------------------------------------------------

--
-- Structure de la table `commentaire`
--

CREATE TABLE `commentaire` (
  `id` int(11) NOT NULL,
  `id_recette` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `texte` text NOT NULL,
  `date_creation` datetime NOT NULL DEFAULT current_timestamp(),
  `date_update` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `commentaire`
--

INSERT INTO `commentaire` (`id`, `id_recette`, `id_utilisateur`, `texte`, `date_creation`, `date_update`) VALUES
(2, 3, 1, 'tres bon plat exelent mais rectte tres dure ', '2025-08-27 16:17:05', '2025-08-27 20:11:26'),
(3, 1, 4, 'boff bof', '2025-08-27 20:04:24', '2025-08-27 20:40:22'),
(4, 4, 4, 'bof', '2025-08-27 21:00:08', '2025-08-27 21:00:22'),
(5, 4, 5, 'efe', '2025-09-02 19:47:02', '2025-09-02 19:47:02'),
(6, 6, 7, 'bof tres bof et nulle', '2025-09-08 18:15:48', '2025-09-08 18:16:01');

-- --------------------------------------------------------

--
-- Structure de la table `ingredient`
--

CREATE TABLE `ingredient` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `quantite` varchar(255) NOT NULL,
  `id_recette` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `ingredient`
--

INSERT INTO `ingredient` (`id`, `nom`, `quantite`, `id_recette`) VALUES
(4, 'Farine de quinoa royal', '30g', 1),
(5, 'tomate', '3', 1),
(6, 'carotte', '5', 1),
(13, 'Farine de maïs ensoleillée', '1000g', 3),
(14, 'tomate', '800g', 3),
(15, 'haricot', '300g', 3),
(25, 'Farine de lentilles corail', '50 grammes', 5),
(26, 'oeufs', '1', 5),
(27, 'sel', '5 grammes', 5),
(31, 'Farine de pois chiche méditerranéenne', '', 4),
(32, 'tomate', '3', 4),
(33, 'ail', '2', 4),
(40, 'Farine multicolore arc-en-ciel', '50', 6),
(41, 'Farine d’avoine du matin', '20', 6),
(42, 'tomate', '5', 6),
(43, 'oignon', '2', 6),
(44, 'haricot', '1', 6),
(45, 'comcombre', '', 6),
(46, 'Farine d’avoine du matin', '100g', 7);

-- --------------------------------------------------------

--
-- Structure de la table `note`
--

CREATE TABLE `note` (
  `id` int(11) NOT NULL,
  `id_recette` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `valeur` tinyint(4) NOT NULL,
  `date_creation` datetime NOT NULL DEFAULT current_timestamp(),
  `date_update` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `note`
--

INSERT INTO `note` (`id`, `id_recette`, `id_utilisateur`, `valeur`, `date_creation`, `date_update`) VALUES
(1, 3, 1, 2, '2025-08-27 16:17:05', '2025-08-27 20:11:26'),
(2, 1, 4, 2, '2025-08-27 20:04:24', '2025-08-27 20:40:22'),
(10, 4, 4, 2, '2025-08-27 21:00:08', '2025-08-27 21:00:22'),
(12, 4, 5, 2, '2025-09-02 19:47:02', '2025-09-02 19:47:02'),
(13, 6, 7, 4, '2025-09-08 18:15:48', '2025-09-08 18:16:01');

-- --------------------------------------------------------

--
-- Structure de la table `recette`
--

CREATE TABLE `recette` (
  `id` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `duree` int(11) NOT NULL COMMENT 'en minutes',
  `difficulte` enum('très facile','facile','difficile') NOT NULL,
  `date_creation` datetime NOT NULL DEFAULT current_timestamp(),
  `id_utilisateur` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `recette`
--

INSERT INTO `recette` (`id`, `titre`, `description`, `duree`, `difficulte`, `date_creation`, `id_utilisateur`) VALUES
(1, 'quinoa', 'tres bon pour la sante farine d quinoa', 3, 'facile', '2025-08-26 16:04:09', 1),
(3, 'tacos', 'jolie tacos a base de farine de maiis tres bon pour la sante', 2, 'difficile', '2025-08-27 09:59:25', 4),
(4, 'pois chiche', 'plat a base de pois chiche bon por la santer', 1, 'facile', '2025-08-27 20:52:30', 1),
(5, 'Gnocchi', 'Plat incontournable italien, les gnocchis sont versatiles. Cependant, il est facile de les acheter plutôt que de les faire sois même car le procédé peut paraître chronophage. Voici une recette qui vous donnera envie d\'en refaire sans perdre un temps précieux en cuisine avec des ingrédients du placard et innovante !', 30, 'facile', '2025-08-27 22:23:16', 5),
(6, 'mixte de 2 farine', 'melande deux farine avoine et multicolore', 52, 'très facile', '2025-09-02 22:54:12', 6),
(7, 'farine', 'avoine', 4, 'facile', '2025-09-08 18:17:07', 7);

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id` int(11) NOT NULL,
  `pseudo` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id`, `pseudo`, `email`, `password`) VALUES
(1, 'toto', 'toto@toto.fr', '$2y$10$LzSo6bcN0XVNDC5IEn.ZAe7l.xAQHMaPMqWp7pYekx7YfJdBYPMom'),
(4, 'tata', 'tata@tata.fr', '$2y$10$OZGu1f1d/QC8vxH9yaheGuSUogfV2OVdcBdlJ/EAk7.WwcmRpCDWS'),
(5, 'Mamacita', 'mamacita@mamacita.fr', '$2y$10$76yOrUq8UmhhS1I6BRjmtOQAeRNTr7kz7ftsbINIsLcfUyFhI1lI6'),
(6, 'toutou', 'toutou@toutou.fr', '$2y$10$c1KJooRyN4Og09gLGpqKQ.IsBWMBAdYVunT14lg08leL2eh62V2fW'),
(7, 'titi', 'titi@titi.fr', '$2y$10$zIa05odaS34PhMTohesXveQ17J1GSoFC82r18S9w7FnQEjU.uZL3u');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `commentaire`
--
ALTER TABLE `commentaire`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_commentaire_recette` (`id_recette`),
  ADD KEY `idx_commentaire_user` (`id_utilisateur`);

--
-- Index pour la table `ingredient`
--
ALTER TABLE `ingredient`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ingredient_recette` (`id_recette`);

--
-- Index pour la table `note`
--
ALTER TABLE `note`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_note` (`id_recette`,`id_utilisateur`),
  ADD KEY `idx_note_recette` (`id_recette`),
  ADD KEY `idx_note_user` (`id_utilisateur`);

--
-- Index pour la table `recette`
--
ALTER TABLE `recette`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_recette_user` (`id_utilisateur`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pseudo` (`pseudo`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `commentaire`
--
ALTER TABLE `commentaire`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `ingredient`
--
ALTER TABLE `ingredient`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT pour la table `note`
--
ALTER TABLE `note`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pour la table `recette`
--
ALTER TABLE `recette`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `commentaire`
--
ALTER TABLE `commentaire`
  ADD CONSTRAINT `fk_commentaire_recette` FOREIGN KEY (`id_recette`) REFERENCES `recette` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_commentaire_user` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `ingredient`
--
ALTER TABLE `ingredient`
  ADD CONSTRAINT `fk_ingredient_recette` FOREIGN KEY (`id_recette`) REFERENCES `recette` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `note`
--
ALTER TABLE `note`
  ADD CONSTRAINT `fk_note_recette` FOREIGN KEY (`id_recette`) REFERENCES `recette` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_note_user` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `recette`
--
ALTER TABLE `recette`
  ADD CONSTRAINT `fk_recette_user` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
