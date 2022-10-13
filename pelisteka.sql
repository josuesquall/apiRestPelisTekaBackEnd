-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 13-10-2022 a las 18:11:32
-- Versión del servidor: 5.7.36
-- Versión de PHP: 7.4.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `pelisteka`
--

DELIMITER $$
--
-- Funciones
--
DROP FUNCTION IF EXISTS `getScore`$$
CREATE DEFINER=`root`@`localhost` FUNCTION `getScore` (`id_movie` INT(11)) RETURNS FLOAT BEGIN 
  DECLARE media FLOAT;
  DECLARE votos INT;
  DECLARE suma INT;
  SET votos = (SELECT COUNT(*) FROM scores WHERE idMovie=id_movie); 
  SET suma = (SELECT SUM(score) from scores WHERE idMovie=id_movie);
  SET media=(suma/votos);
  IF ISNULL(media) THEN
  	SET media=0;
  END IF;
  RETURN media;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comments`
--

DROP TABLE IF EXISTS `comments`;
CREATE TABLE IF NOT EXISTS `comments` (
  `idComment` int(11) NOT NULL AUTO_INCREMENT,
  `idUser` int(11) NOT NULL,
  `idMovie` int(11) NOT NULL,
  `ts` int(11) NOT NULL,
  `text` text COLLATE utf8_spanish_ci NOT NULL,
  PRIMARY KEY (`idComment`),
  KEY `idUser` (`idUser`),
  KEY `idMovie` (`idMovie`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `comments`
--

INSERT INTO `comments` (`idComment`, `idUser`, `idMovie`, `ts`, `text`) VALUES
(1, 7, 3, 1665679810, 'test'),
(2, 7, 3, 1665680072, 'test 2'),
(3, 7, 3, 1665680229, 'test'),
(4, 7, 3, 1665680244, 'test'),
(5, 7, 3, 1665680326, 'test'),
(6, 7, 3, 1665680367, 'yo ahora mismo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `favorites`
--

DROP TABLE IF EXISTS `favorites`;
CREATE TABLE IF NOT EXISTS `favorites` (
  `idFavorite` int(11) NOT NULL AUTO_INCREMENT,
  `idUser` int(11) NOT NULL,
  `idMovie` int(11) NOT NULL,
  PRIMARY KEY (`idFavorite`),
  KEY `idUser` (`idUser`),
  KEY `idMovie` (`idMovie`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `favorites`
--

INSERT INTO `favorites` (`idFavorite`, `idUser`, `idMovie`) VALUES
(1, 7, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movies`
--

DROP TABLE IF EXISTS `movies`;
CREATE TABLE IF NOT EXISTS `movies` (
  `idMovie` int(11) NOT NULL AUTO_INCREMENT,
  `id` int(11) NOT NULL,
  `metaData` json NOT NULL,
  PRIMARY KEY (`idMovie`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `movies`
--

INSERT INTO `movies` (`idMovie`, `id`, `metaData`) VALUES
(1, 960704, '{\"id\": 960704, \"adult\": false, \"title\": \"Fullmetal Alchemist: The Final Alchemy\", \"video\": false, \"overview\": \"The Elric brothers’ long and winding journey comes to a close in this epic finale, where they must face off against an unworldly, nationwide threat.\", \"genre_ids\": [28, 12, 14], \"popularity\": 4030.287, \"vote_count\": 47, \"poster_path\": \"/AeyiuQUUs78bPkz18FY3AzNFF8b.jpg\", \"release_date\": \"2022-06-24\", \"vote_average\": 6.7, \"backdrop_path\": \"/5hoS3nEkGGXUfmnu39yw1k52JX5.jpg\", \"original_title\": \"---\", \"original_language\": \"ja\"}'),
(2, 760161, '{\"id\": 760161, \"adult\": false, \"title\": \"Orphan: First Kill\", \"video\": false, \"overview\": \"After escaping from an Estonian psychiatric facility, Leena Klammer travels to America by impersonating Esther, the missing daughter of a wealthy family. But when her mask starts to slip, she is put against a mother who will protect her family from the murderous “child” at any cost.\", \"genre_ids\": [27, 53], \"popularity\": 3744.732, \"vote_count\": 964, \"poster_path\": \"/pHkKbIRoCe7zIFvqan9LFSaQAde.jpg\", \"release_date\": \"2022-07-27\", \"vote_average\": 6.9, \"backdrop_path\": \"/5GA3vV1aWWHTSDO5eno8V5zDo8r.jpg\", \"original_title\": \"Orphan: First Kill\", \"original_language\": \"en\"}'),
(3, 642885, '{\"id\": 642885, \"adult\": false, \"title\": \"Hocus Pocus 2\", \"video\": false, \"overview\": \"It’s been 29 years since someone lit the Black Flame Candle and resurrected the 17th-century sisters, and they are looking for revenge. Now it is up to three high-school students to stop the ravenous witches from wreaking a new kind of havoc on Salem before dawn on All Hallow’s Eve.\", \"genre_ids\": [14, 35, 10751], \"popularity\": 3389.879, \"vote_count\": 648, \"poster_path\": \"/7ze7YNmUaX81ufctGqt0AgHxRtL.jpg\", \"release_date\": \"2022-09-27\", \"vote_average\": 7.8, \"backdrop_path\": \"/iS9U3VHpPEjTWnwmW56CrBlpgLj.jpg\", \"original_title\": \"Hocus Pocus 2\", \"original_language\": \"en\"}'),
(4, 985939, '{\"id\": 985939, \"adult\": false, \"title\": \"Fall\", \"video\": false, \"overview\": \"For best friends Becky and Hunter, life is all about conquering fears and pushing limits. But after they climb 2,000 feet to the top of a remote, abandoned radio tower, they find themselves stranded with no way down. Now Becky and Hunter’s expert climbing skills will be put to the ultimate test as they desperately fight to survive the elements, a lack of supplies, and vertigo-inducing heights\", \"genre_ids\": [53], \"popularity\": 3302.231, \"vote_count\": 1363, \"poster_path\": \"/spCAxD99U1A6jsiePFoqdEcY0dG.jpg\", \"release_date\": \"2022-08-11\", \"vote_average\": 7.4, \"backdrop_path\": \"/hT3OqvzMqCQuJsUjZnQwA5NuxgK.jpg\", \"original_title\": \"Fall\", \"original_language\": \"en\"}'),
(5, 718930, '{\"id\": 718930, \"adult\": false, \"title\": \"Bullet Train\", \"video\": false, \"overview\": \"Unlucky assassin Ladybug is determined to do his job peacefully after one too many gigs gone off the rails. Fate, however, may have other plans, as Ladybug\'s latest mission puts him on a collision course with lethal adversaries from around the globe—all with connected, yet conflicting, objectives—on the world\'s fastest train.\", \"genre_ids\": [28, 35, 53], \"popularity\": 3103.725, \"vote_count\": 1680, \"poster_path\": \"/tVxDe01Zy3kZqaZRNiXFGDICdZk.jpg\", \"release_date\": \"2022-07-03\", \"vote_average\": 7.5, \"backdrop_path\": \"/83oeqwN64WtafGoITvsOzjKIQaM.jpg\", \"original_title\": \"Bullet Train\", \"original_language\": \"en\"}'),
(6, 791155, '{\"id\": 791155, \"adult\": false, \"title\": \"Secret Headquarters\", \"video\": false, \"overview\": \"While hanging out after school, Charlie and his friends discover the headquarters of the world’s most powerful superhero hidden beneath his home. When villains attack, they must team up to defend the headquarters and save the world.\", \"genre_ids\": [878, 12, 28], \"popularity\": 3117.231, \"vote_count\": 81, \"poster_path\": \"/8PsHogUfvjWPGdWAI5uslDhHDx7.jpg\", \"release_date\": \"2022-08-12\", \"vote_average\": 7, \"backdrop_path\": \"/aIkG2V4UXrfkxMdJZmq30xO0QQr.jpg\", \"original_title\": \"Secret Headquarters\", \"original_language\": \"en\"}'),
(7, 852046, '{\"id\": 852046, \"adult\": false, \"title\": \"Athena\", \"video\": false, \"overview\": \"Hours after the tragic death of their youngest brother in unexplained circumstances, three siblings have their lives thrown into chaos.\", \"genre_ids\": [18, 28, 53], \"popularity\": 2780.156, \"vote_count\": 250, \"poster_path\": \"/fenNPxVF5ERy0CSyVruuEg959Hg.jpg\", \"release_date\": \"2022-09-09\", \"vote_average\": 6.6, \"backdrop_path\": \"/ghsPsvM0sEztdNT4kUlTsBF2LEF.jpg\", \"original_title\": \"Athena\", \"original_language\": \"fr\"}'),
(8, 916605, '{\"id\": 916605, \"adult\": false, \"title\": \"The Infernal Machine\", \"video\": false, \"overview\": \"Reclusive and controversial author Bruce Cogburn is drawn out of hiding by an obsessive fan, forcing the novelist to confront a past that he thought he could escape, and to account for events set in motion by his bestseller decades earlier. Cogburn\'s search for who is behind the manipulation and mental torment he encounters leads to an emotional roller-coaster ride full of fear and danger, where things are not always as clear as they seem to be, and where past deeds can have dire consequences.\", \"genre_ids\": [53, 9648], \"popularity\": 2508.595, \"vote_count\": 65, \"poster_path\": \"/bSqpOGzaKBdGkBLmcm1JJIVryYy.jpg\", \"release_date\": \"2022-09-23\", \"vote_average\": 6.9, \"backdrop_path\": \"/7AiIrnDMaOhPrw9elJ5NNjijTW4.jpg\", \"original_title\": \"The Infernal Machine\", \"original_language\": \"en\"}'),
(9, 429473, '{\"id\": 429473, \"adult\": false, \"title\": \"Lou\", \"video\": false, \"overview\": \"A young girl is kidnapped during a powerful storm. Her mother joins forces with her mysterious neighbour to set off in pursuit of the kidnapper. Their journey will test their limits and expose the dark secrets of their past.\", \"genre_ids\": [28, 53, 18], \"popularity\": 1945.623, \"vote_count\": 243, \"poster_path\": \"/djM2s4wSaATn4jVB33cV05PEbV7.jpg\", \"release_date\": \"2022-09-23\", \"vote_average\": 6.5, \"backdrop_path\": \"/rgZ3hdzgMgYgzvBfwNEVW01bpK1.jpg\", \"original_title\": \"Lou\", \"original_language\": \"en\"}'),
(10, 744276, '{\"id\": 744276, \"adult\": false, \"title\": \"After Ever Happy\", \"video\": false, \"overview\": \"As a shocking truth about a couple\'s families emerges, the two lovers discover they are not so different from each other. Tessa is no longer the sweet, simple, good girl she was when she met Hardin — any more than he is the cruel, moody boy she fell so hard for.\", \"genre_ids\": [10749, 18], \"popularity\": 1740.669, \"vote_count\": 373, \"poster_path\": \"/6b7swg6DLqXCO3XUsMnv6RwDMW2.jpg\", \"release_date\": \"2022-08-24\", \"vote_average\": 6.9, \"backdrop_path\": \"/rwgmDkIEv8VjAsWx25ottJrFvpO.jpg\", \"original_title\": \"After Ever Happy\", \"original_language\": \"en\"}'),
(11, 852046, '{\"id\": 852046, \"adult\": false, \"title\": \"Athena\", \"video\": false, \"overview\": \"Hours after the tragic death of their youngest brother in unexplained circumstances, three siblings have their lives thrown into chaos.\", \"genre_ids\": [18, 28, 53], \"popularity\": 2780.156, \"vote_count\": 250, \"poster_path\": \"/fenNPxVF5ERy0CSyVruuEg959Hg.jpg\", \"release_date\": \"2022-09-09\", \"vote_average\": 6.6, \"backdrop_path\": \"/ghsPsvM0sEztdNT4kUlTsBF2LEF.jpg\", \"original_title\": \"Athena\", \"original_language\": \"fr\"}'),
(12, 916605, '{\"id\": 916605, \"adult\": false, \"title\": \"The Infernal Machine\", \"video\": false, \"overview\": \"Reclusive and controversial author Bruce Cogburn is drawn out of hiding by an obsessive fan, forcing the novelist to confront a past that he thought he could escape, and to account for events set in motion by his bestseller decades earlier. Cogburn\'s search for who is behind the manipulation and mental torment he encounters leads to an emotional roller-coaster ride full of fear and danger, where things are not always as clear as they seem to be, and where past deeds can have dire consequences.\", \"genre_ids\": [53, 9648], \"popularity\": 2508.595, \"vote_count\": 65, \"poster_path\": \"/bSqpOGzaKBdGkBLmcm1JJIVryYy.jpg\", \"release_date\": \"2022-09-23\", \"vote_average\": 6.9, \"backdrop_path\": \"/7AiIrnDMaOhPrw9elJ5NNjijTW4.jpg\", \"original_title\": \"The Infernal Machine\", \"original_language\": \"en\"}'),
(13, 429473, '{\"id\": 429473, \"adult\": false, \"title\": \"Lou\", \"video\": false, \"overview\": \"A young girl is kidnapped during a powerful storm. Her mother joins forces with her mysterious neighbour to set off in pursuit of the kidnapper. Their journey will test their limits and expose the dark secrets of their past.\", \"genre_ids\": [28, 53, 18], \"popularity\": 1945.623, \"vote_count\": 243, \"poster_path\": \"/djM2s4wSaATn4jVB33cV05PEbV7.jpg\", \"release_date\": \"2022-09-23\", \"vote_average\": 6.5, \"backdrop_path\": \"/rgZ3hdzgMgYgzvBfwNEVW01bpK1.jpg\", \"original_title\": \"Lou\", \"original_language\": \"en\"}'),
(14, 744276, '{\"id\": 744276, \"adult\": false, \"title\": \"After Ever Happy\", \"video\": false, \"overview\": \"As a shocking truth about a couple\'s families emerges, the two lovers discover they are not so different from each other. Tessa is no longer the sweet, simple, good girl she was when she met Hardin — any more than he is the cruel, moody boy she fell so hard for.\", \"genre_ids\": [10749, 18], \"popularity\": 1740.669, \"vote_count\": 373, \"poster_path\": \"/6b7swg6DLqXCO3XUsMnv6RwDMW2.jpg\", \"release_date\": \"2022-08-24\", \"vote_average\": 6.9, \"backdrop_path\": \"/rwgmDkIEv8VjAsWx25ottJrFvpO.jpg\", \"original_title\": \"After Ever Happy\", \"original_language\": \"en\"}'),
(15, 760741, '{\"id\": 760741, \"adult\": false, \"title\": \"Beast\", \"video\": false, \"overview\": \"A recently widowed man and his two teenage daughters travel to a game reserve in South Africa. However, their journey of healing soon turns into a fight for survival when a bloodthirsty lion starts to stalk them.\", \"genre_ids\": [53, 12, 27], \"popularity\": 1775.519, \"vote_count\": 621, \"poster_path\": \"/asRkNwbMuLheJ2nnnbRCIr2PdnI.jpg\", \"release_date\": \"2022-08-11\", \"vote_average\": 7.1, \"backdrop_path\": \"/2k9tBql5GYH328Krj66tDT9LtFZ.jpg\", \"original_title\": \"Beast\", \"original_language\": \"en\"}'),
(16, 532639, '{\"id\": 532639, \"adult\": false, \"title\": \"Pinocchio\", \"video\": false, \"overview\": \"A wooden puppet embarks on a thrilling adventure to become a real boy.\", \"genre_ids\": [14, 12, 10751], \"popularity\": 1908.354, \"vote_count\": 891, \"poster_path\": \"/g8sclIV4gj1TZqUpnL82hKOTK3B.jpg\", \"release_date\": \"2022-09-07\", \"vote_average\": 6.7, \"backdrop_path\": \"/nnUQqlVZeEGuCRx8SaoCU4XVHJN.jpg\", \"original_title\": \"Pinocchio\", \"original_language\": \"en\"}'),
(21, 993145, '{\"id\": 993145, \"adult\": false, \"title\": \"Bullet Proof\", \"video\": false, \"overview\": \"The Thief  pulls off the robbery of a lifetime when he robs the psychotic drug lord, Temple. The plan goes off without a hitch until the Thief discovers a stowaway in his getaway car - Temple\'s pregnant wife, Mia.\", \"genre_ids\": [28, 80], \"popularity\": 1604.404, \"vote_count\": 15, \"poster_path\": \"/cj6YmTAU7Jvn3w6d2NfjQzpX7Pw.jpg\", \"release_date\": \"2022-08-19\", \"vote_average\": 5.1, \"backdrop_path\": \"/5EzpTMkpg3DecNoP2DAOBlh0Fi6.jpg\", \"original_title\": \"Bullet Proof\", \"original_language\": \"en\"}'),
(22, 616037, '{\"id\": 616037, \"adult\": false, \"title\": \"Thor: Love and Thunder\", \"video\": false, \"overview\": \"After his retirement is interrupted by Gorr the God Butcher, a galactic killer who seeks the extinction of the gods, Thor Odinson enlists the help of King Valkyrie, Korg, and ex-girlfriend Jane Foster, who now wields Mjolnir as the Mighty Thor. Together they embark upon a harrowing cosmic adventure to uncover the mystery of the God Butcher’s vengeance and stop him before it’s too late.\", \"genre_ids\": [14, 28, 35], \"popularity\": 1488.579, \"vote_count\": 4147, \"poster_path\": \"/pIkRyD18kl4FhoCNQuWxWu5cBLM.jpg\", \"release_date\": \"2022-07-06\", \"vote_average\": 6.8, \"backdrop_path\": \"/jsoz1HlxczSuTx0mDl2h0lxy36l.jpg\", \"original_title\": \"Thor: Love and Thunder\", \"original_language\": \"en\"}'),
(23, 882598, '{\"id\": 882598, \"adult\": false, \"title\": \"Smile\", \"video\": false, \"overview\": \"After witnessing a bizarre, traumatic incident involving a patient, Dr. Rose Cotter starts experiencing frightening occurrences that she can\'t explain. As an overwhelming terror begins taking over her life, Rose must confront her troubling past in order to survive and escape her horrifying new reality.\", \"genre_ids\": [27, 9648], \"popularity\": 1300.464, \"vote_count\": 183, \"poster_path\": \"/aPqcQwu4VGEewPhagWNncDbJ9Xp.jpg\", \"release_date\": \"2022-09-23\", \"vote_average\": 6.7, \"backdrop_path\": \"/olPXihyFeeNvnaD6IOBltgIV1FU.jpg\", \"original_title\": \"Smile\", \"original_language\": \"en\"}'),
(24, 766507, '{\"id\": 766507, \"adult\": false, \"title\": \"Prey\", \"video\": false, \"overview\": \"When danger threatens her camp, the fierce and highly skilled Comanche warrior Naru sets out to protect her people. But the prey she stalks turns out to be a highly evolved alien predator with a technically advanced arsenal.\", \"genre_ids\": [53, 28, 878], \"popularity\": 1197.202, \"vote_count\": 4281, \"poster_path\": \"/ujr5pztc1oitbe7ViMUOilFaJ7s.jpg\", \"release_date\": \"2022-08-02\", \"vote_average\": 7.9, \"backdrop_path\": \"/7ZO9yoEU2fAHKhmJWfAc2QIPWJg.jpg\", \"original_title\": \"Prey\", \"original_language\": \"en\"}'),
(25, 361743, '{\"id\": 361743, \"adult\": false, \"title\": \"Top Gun: Maverick\", \"video\": false, \"overview\": \"After more than thirty years of service as one of the Navy’s top aviators, and dodging the advancement in rank that would ground him, Pete “Maverick” Mitchell finds himself training a detachment of TOP GUN graduates for a specialized mission the likes of which no living pilot has ever seen.\", \"genre_ids\": [28, 18], \"popularity\": 1169.032, \"vote_count\": 4245, \"poster_path\": \"/62HCnUTziyWcpDaBO2i1DX17ljH.jpg\", \"release_date\": \"2022-05-24\", \"vote_average\": 8.4, \"backdrop_path\": \"/odJ4hx6g6vBt4lBWKFD1tI8WS4x.jpg\", \"original_title\": \"Top Gun: Maverick\", \"original_language\": \"en\"}'),
(26, 539681, '{\"id\": 539681, \"adult\": false, \"title\": \"DC League of Super-Pets\", \"video\": false, \"overview\": \"When Superman and the rest of the Justice League are kidnapped, Krypto the Super-Dog must convince a rag-tag shelter pack - Ace the hound, PB the potbellied pig, Merton the turtle and Chip the squirrel - to master their own newfound powers and help him rescue the superheroes.\", \"genre_ids\": [16, 28, 10751, 35, 878], \"popularity\": 1207.154, \"vote_count\": 789, \"poster_path\": \"/r7XifzvtezNt31ypvsmb6Oqxw49.jpg\", \"release_date\": \"2022-07-27\", \"vote_average\": 7.5, \"backdrop_path\": \"/qaTzVAW1u16WFNsepjCrilBuInc.jpg\", \"original_title\": \"DC League of Super-Pets\", \"original_language\": \"en\"}');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `scores`
--

DROP TABLE IF EXISTS `scores`;
CREATE TABLE IF NOT EXISTS `scores` (
  `idScore` int(11) NOT NULL AUTO_INCREMENT,
  `idUser` int(11) NOT NULL,
  `idMovie` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  PRIMARY KEY (`idScore`),
  KEY `idUser` (`idUser`),
  KEY `idMovie` (`idMovie`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `scores`
--

INSERT INTO `scores` (`idScore`, `idUser`, `idMovie`, `score`) VALUES
(1, 7, 1, 2),
(2, 7, 2, 2),
(3, 7, 4, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `idUser` int(11) NOT NULL AUTO_INCREMENT,
  `name` text COLLATE utf8_spanish_ci,
  `password` varchar(32) COLLATE utf8_spanish_ci DEFAULT NULL,
  `email` text COLLATE utf8_spanish_ci,
  `status` int(11) NOT NULL,
  PRIMARY KEY (`idUser`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`idUser`, `name`, `password`, `email`, `status`) VALUES
(7, 'jalvarez', 'dXlTRmtFQXBCK2ZocGtqZE4vWVVVQT09', 'pixelartstudios@gmail.com', 1);

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`idUser`) REFERENCES `usuarios` (`idUser`),
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`idMovie`) REFERENCES `movies` (`idMovie`);

--
-- Filtros para la tabla `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`idMovie`) REFERENCES `movies` (`idMovie`),
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`idUser`) REFERENCES `usuarios` (`idUser`);

--
-- Filtros para la tabla `scores`
--
ALTER TABLE `scores`
  ADD CONSTRAINT `scores_ibfk_1` FOREIGN KEY (`idMovie`) REFERENCES `movies` (`idMovie`),
  ADD CONSTRAINT `scores_ibfk_2` FOREIGN KEY (`idUser`) REFERENCES `usuarios` (`idUser`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
