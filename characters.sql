-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : sam. 30 mai 2020 à 20:27
-- Version du serveur :  5.7.24
-- Version de PHP : 7.2.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `fight-game`
--

-- --------------------------------------------------------

--
-- Structure de la table `characters`
--

CREATE TABLE `characters` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `health` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `level` int(11) DEFAULT NULL,
  `xp` int(11) DEFAULT NULL,
  `strength` int(11) DEFAULT NULL,
  `magic` int(11) DEFAULT NULL,
  `hitsCount` int(11) DEFAULT NULL,
  `lastHit` datetime DEFAULT NULL,
  `nextHit` datetime DEFAULT NULL,
  `classType` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `characters`
--

INSERT INTO `characters` (`id`, `name`, `health`, `level`, `xp`, `strength`, `magic`, `hitsCount`, `lastHit`, `nextHit`, `classType`) VALUES
(29, 'Florent', 53, 3, 2, 3, 5, 1, '2020-05-30 21:52:05', NULL, 'mage'),
(31, 'Pedro', 81, 8, 2, 8, 15, 1, '2020-05-30 22:08:59', NULL, 'mage'),
(33, 'Alexandre', 24, 7, 5, 7, 7, 5, '2020-05-30 22:06:47', NULL, 'archer'),
(42, 'Gael', 46, 5, 5, 9, 5, 2, '2020-05-30 22:02:46', NULL, 'warrior');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `characters`
--
ALTER TABLE `characters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `characters`
--
ALTER TABLE `characters`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
