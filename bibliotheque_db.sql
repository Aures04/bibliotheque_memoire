-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 26 fév. 2026 à 13:55
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `bibliotheque_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `auteurs`
--

CREATE TABLE `auteurs` (
  `id_auteur` int(10) UNSIGNED NOT NULL,
  `nom_auteur` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `auteurs`
--

INSERT INTO `auteurs` (`id_auteur`, `nom_auteur`) VALUES
(1, 'LAFONTAINE FABLE'),
(2, 'OLIVER JACK');

-- --------------------------------------------------------

--
-- Structure de la table `details_emprunt`
--

CREATE TABLE `details_emprunt` (
  `id_detail_emprunt` int(10) UNSIGNED NOT NULL,
  `num_emp` int(10) UNSIGNED NOT NULL,
  `cod_ouv` int(10) UNSIGNED NOT NULL,
  `date_retour_reelle` date DEFAULT NULL,
  `statut_ouvrage` enum('emprunte','retourne','mauvais_etat') NOT NULL DEFAULT 'emprunte'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `domaines`
--

CREATE TABLE `domaines` (
  `cod_Dom` int(10) UNSIGNED NOT NULL,
  `nom_domaine` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `domaines`
--

INSERT INTO `domaines` (`cod_Dom`, `nom_domaine`) VALUES
(2, 'ARGZNT'),
(1, 'SPORT');

-- --------------------------------------------------------

--
-- Structure de la table `emprunts`
--

CREATE TABLE `emprunts` (
  `num_rmp` int(10) UNSIGNED NOT NULL,
  `num_memb` int(10) UNSIGNED NOT NULL,
  `date_emprunt` date NOT NULL,
  `date_retour_prevue` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `langues`
--

CREATE TABLE `langues` (
  `cod_lang` varchar(10) NOT NULL,
  `nom_lang` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `langues`
--

INSERT INTO `langues` (`cod_lang`, `nom_lang`) VALUES
('', 'FRANCAIS');

-- --------------------------------------------------------

--
-- Structure de la table `membres`
--

CREATE TABLE `membres` (
  `num_memb` int(10) UNSIGNED NOT NULL,
  `nom_membre` varchar(255) NOT NULL,
  `email_membre` varchar(255) NOT NULL,
  `tel_membre` varchar(50) DEFAULT NULL,
  `est_indelicat` tinyint(1) NOT NULL DEFAULT 0,
  `num_compte` varchar(255) DEFAULT NULL,
  `banque_indelicat` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `membres`
--

INSERT INTO `membres` (`num_memb`, `nom_membre`, `email_membre`, `tel_membre`, `est_indelicat`, `num_compte`, `banque_indelicat`) VALUES
(1, 'andré', 'abc@gmail.com', '0191111111', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `ouvrages`
--

CREATE TABLE `ouvrages` (
  `cod_ouv` int(10) UNSIGNED NOT NULL,
  `titre` varchar(255) NOT NULL,
  `nb_exemplaire` int(11) NOT NULL DEFAULT 0,
  `type_ouivrage` enum('livre','revue','journal') NOT NULL DEFAULT 'livre',
  `periodicité` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ouvrage_auteur`
--

CREATE TABLE `ouvrage_auteur` (
  `cod_ouv` int(10) UNSIGNED NOT NULL,
  `id_auteur` int(10) UNSIGNED NOT NULL,
  `type_auteur` enum('principal','secondaire') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ouvrage_domaine`
--

CREATE TABLE `ouvrage_domaine` (
  `cod_ouv` int(10) UNSIGNED NOT NULL,
  `cod_dom` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ouvrage_langue`
--

CREATE TABLE `ouvrage_langue` (
  `cod_ouv` int(10) UNSIGNED NOT NULL,
  `cod_lang` varchar(10) NOT NULL,
  `date_parution` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `role` enum('admin','bibliothèque') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `email`, `mot_de_passe`, `role`) VALUES
(2, 'admin@example.com', '$2y$10$Zwa8wkF5yhrmIsMTY1CqYeYvhQ86fN5vFW8Rmu3swELtS32q34uGC', 'admin'),
(3, 'auresagbonoukon@gmail.com', '$2y$10$JFMe6hnF505XFKSH.YJYKO4eF4ugg7X5.p4kv2jYwsJUGwqs.AqHu', 'admin'),
(4, 'sedomonagbonoukon@gmail.com', '$2y$10$DEIRtn8EFK7EMpen.zXHV.o4W9Kpi9A0RsNt1/KLkbY9VoEZ5dmym', '');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `auteurs`
--
ALTER TABLE `auteurs`
  ADD PRIMARY KEY (`id_auteur`);

--
-- Index pour la table `details_emprunt`
--
ALTER TABLE `details_emprunt`
  ADD PRIMARY KEY (`id_detail_emprunt`),
  ADD KEY `num_emp` (`num_emp`),
  ADD KEY `cod_ouv` (`cod_ouv`);

--
-- Index pour la table `domaines`
--
ALTER TABLE `domaines`
  ADD PRIMARY KEY (`cod_Dom`),
  ADD UNIQUE KEY `nom_domaine` (`nom_domaine`);

--
-- Index pour la table `emprunts`
--
ALTER TABLE `emprunts`
  ADD PRIMARY KEY (`num_rmp`);

--
-- Index pour la table `langues`
--
ALTER TABLE `langues`
  ADD PRIMARY KEY (`cod_lang`),
  ADD UNIQUE KEY `nom_lang` (`nom_lang`);

--
-- Index pour la table `membres`
--
ALTER TABLE `membres`
  ADD PRIMARY KEY (`num_memb`),
  ADD UNIQUE KEY `email_membre` (`email_membre`);

--
-- Index pour la table `ouvrages`
--
ALTER TABLE `ouvrages`
  ADD PRIMARY KEY (`cod_ouv`);

--
-- Index pour la table `ouvrage_auteur`
--
ALTER TABLE `ouvrage_auteur`
  ADD PRIMARY KEY (`cod_ouv`,`id_auteur`),
  ADD KEY `id_auteur` (`id_auteur`);

--
-- Index pour la table `ouvrage_domaine`
--
ALTER TABLE `ouvrage_domaine`
  ADD PRIMARY KEY (`cod_ouv`,`cod_dom`),
  ADD KEY `cod_dom` (`cod_dom`);

--
-- Index pour la table `ouvrage_langue`
--
ALTER TABLE `ouvrage_langue`
  ADD PRIMARY KEY (`cod_ouv`,`cod_lang`),
  ADD KEY `cod_lang` (`cod_lang`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `auteurs`
--
ALTER TABLE `auteurs`
  MODIFY `id_auteur` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `details_emprunt`
--
ALTER TABLE `details_emprunt`
  MODIFY `id_detail_emprunt` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `domaines`
--
ALTER TABLE `domaines`
  MODIFY `cod_Dom` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `emprunts`
--
ALTER TABLE `emprunts`
  MODIFY `num_rmp` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `membres`
--
ALTER TABLE `membres`
  MODIFY `num_memb` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `ouvrages`
--
ALTER TABLE `ouvrages`
  MODIFY `cod_ouv` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `details_emprunt`
--
ALTER TABLE `details_emprunt`
  ADD CONSTRAINT `details_emprunt_ibfk_1` FOREIGN KEY (`num_emp`) REFERENCES `emprunts` (`num_rmp`) ON DELETE CASCADE,
  ADD CONSTRAINT `details_emprunt_ibfk_2` FOREIGN KEY (`cod_ouv`) REFERENCES `ouvrages` (`cod_ouv`) ON DELETE CASCADE;

--
-- Contraintes pour la table `ouvrage_auteur`
--
ALTER TABLE `ouvrage_auteur`
  ADD CONSTRAINT `ouvrage_auteur_ibfk_1` FOREIGN KEY (`cod_ouv`) REFERENCES `ouvrages` (`cod_ouv`) ON DELETE CASCADE,
  ADD CONSTRAINT `ouvrage_auteur_ibfk_2` FOREIGN KEY (`id_auteur`) REFERENCES `auteurs` (`id_auteur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `ouvrage_domaine`
--
ALTER TABLE `ouvrage_domaine`
  ADD CONSTRAINT `ouvrage_domaine_ibfk_1` FOREIGN KEY (`cod_ouv`) REFERENCES `ouvrages` (`cod_ouv`) ON DELETE CASCADE,
  ADD CONSTRAINT `ouvrage_domaine_ibfk_2` FOREIGN KEY (`cod_dom`) REFERENCES `domaines` (`cod_Dom`) ON DELETE CASCADE;

--
-- Contraintes pour la table `ouvrage_langue`
--
ALTER TABLE `ouvrage_langue`
  ADD CONSTRAINT `ouvrage_langue_ibfk_1` FOREIGN KEY (`cod_ouv`) REFERENCES `ouvrages` (`cod_ouv`) ON DELETE CASCADE,
  ADD CONSTRAINT `ouvrage_langue_ibfk_2` FOREIGN KEY (`cod_lang`) REFERENCES `langues` (`cod_lang`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
