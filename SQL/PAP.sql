-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 20-Mar-2026 às 02:07
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `pap`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `carts`
--

CREATE TABLE `carts` (
  `ID` int(11) NOT NULL,
  `User_ID` int(11) NOT NULL,
  `Status` varchar(24) NOT NULL DEFAULT 'active',
  `Created_At` timestamp NOT NULL DEFAULT current_timestamp(),
  `Updated_At` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `carts`
--

INSERT INTO `carts` (`ID`, `User_ID`, `Status`, `Created_At`, `Updated_At`) VALUES
(1, 2, 'active', '2026-03-17 02:04:11', '2026-03-17 02:04:11'),
(2, 7, 'active', '2026-03-20 01:04:03', '2026-03-20 01:04:03');

-- --------------------------------------------------------

--
-- Estrutura da tabela `cart_items`
--

CREATE TABLE `cart_items` (
  `ID` int(11) NOT NULL,
  `Cart_ID` int(11) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `Quantity` int(11) NOT NULL DEFAULT 1,
  `Created_At` timestamp NOT NULL DEFAULT current_timestamp(),
  `Updated_At` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `categories`
--

CREATE TABLE `categories` (
  `ID` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Description` text DEFAULT NULL,
  `Created_At` datetime DEFAULT current_timestamp(),
  `Icon` varchar(64) NOT NULL DEFAULT 'bi-list-ul'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `categories`
--

INSERT INTO `categories` (`ID`, `Name`, `Description`, `Created_At`, `Icon`) VALUES
(1, 'Vestuário', 'Produtos de roupa e acessórios para diferentes estilos e ocasiões, combinando conforto, qualidade e moda.', '2026-03-15 02:40:09', 'bi-list-ul'),
(2, 'Eletrónicos', 'Dispositivos e equipamentos tecnológicos pensados para facilitar o dia a dia, com inovação, desempenho e utilidade.', '2026-03-15 02:40:33', 'bi-list-ul'),
(3, 'Beleza e Cosmética', 'Produtos de cuidado pessoal, maquilhagem e bem-estar para todos os estilos.', '2026-03-20 00:28:20', 'bi-list-ul'),
(4, 'Casa e Decoração', 'Artigos e acessórios para tornar qualquer espaço mais acolhedor e funcional.', '2026-03-20 00:28:51', 'bi-list-ul'),
(5, 'Desporto e Fitness', 'Equipamentos e acessórios para manteres-te ativo e saudável.', '2026-03-20 00:29:12', 'bi-list-ul'),
(6, 'Brinquedos e Jogos', 'Jogos e brinquedos educativos e divertidos para todas as idades.', '2026-03-20 00:30:31', 'bi-list-ul'),
(7, 'Livros e Papelaria', 'Livros, cadernos e material de escritório para aprender, criar e organizar.', '2026-03-20 00:30:50', 'bi-list-ul'),
(8, 'Automóvel e Motociclo', 'Peças, acessórios e equipamentos para carros e motos com segurança e qualidade.', '2026-03-20 00:31:59', 'bi-list-ul');

-- --------------------------------------------------------

--
-- Estrutura da tabela `products`
--

CREATE TABLE `products` (
  `ID` int(11) NOT NULL,
  `Category_ID` int(11) NOT NULL,
  `Publisher_ID` int(11) DEFAULT NULL,
  `Name` varchar(100) NOT NULL,
  `Description` text DEFAULT NULL,
  `Price` decimal(10,2) NOT NULL,
  `Stock` int(11) DEFAULT 0,
  `Image` longblob DEFAULT NULL,
  `Created_At` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `users`
--

CREATE TABLE `users` (
  `ID` int(11) NOT NULL,
  `Email` varchar(150) NOT NULL,
  `Username` varchar(100) NOT NULL,
  `Phone_number` varchar(9) DEFAULT NULL,
  `Password` varchar(255) NOT NULL,
  `Registered_Date` datetime DEFAULT current_timestamp(),
  `Is_Admin` tinyint(1) DEFAULT 0,
  `Balance` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `users`
--

INSERT INTO `users` (`ID`, `Email`, `Username`, `Phone_number`, `Password`, `Registered_Date`, `Is_Admin`, `Balance`) VALUES
(1, 'Tiago@gmail.com', 'Tiago', '966396732', '$2y$10$ZwcJyXwGlJXlDBYNxaCmH.YNzTpopUNOtklRkI9oVJ0eWNn015fYu', '2026-03-07 16:25:43', 1, 0.00),
(2, 'Mariana@gmail.com', 'Mariana', '967486836', '$2y$10$tbnIBTsZhXKV1zL/2gPcceb8tdz1FFHyrPtMyYtsafyYsPHxQ7CBS', '2026-03-07 16:15:31', 0, 588.00),
(3, 'Fabio@gmail.com', 'Fabio', NULL, '$2y$10$rIwc6fHaZVQI0JBI0dVJneA9rlgLIF76JdUAiC9c8se5hqEZ.vie2', '2026-03-13 00:00:50', 0, 0.00),
(4, 'Enzo@gmail.com', 'Enzo', NULL, '$2y$10$JtGM/RcHXJGJeLzkBB.LKu63uvj3l1zGU5593wKinFgyhDzQwldC2', '2026-03-13 00:01:39', 0, 0.00),
(5, 'Francisco@gmail.com', 'Francisco', NULL, '$2y$10$w8vOtGWEsVjh9vRWW2xYHO5tW.1ZPjT17ZDGYXkKladKYdWC.Uyj2', '2026-03-13 00:03:23', 0, 0.00),
(6, 'Goncalo@gmail.com', 'Gonçalo', NULL, '$2y$10$6wxeCEYW0IWAvYpjwfViQeqt2NeC6k8nDlEKdc08o1I3WgJjwKLi.', '2026-03-13 00:03:49', 0, 0.00),
(7, 'Martim@gmail.com', 'Martim', NULL, '$2y$10$mKjuMU.04Vxal1xiR1Wet.gzZ5CDt/BLRhE4Tckj62iS8oAIvcysm', '2026-03-13 00:18:03', 0, 0.00),
(8, 'Tiago2@gmail.com', 'Tiago2', '924386890', '$2y$10$9et25gfLkx9BxbDtusilJuK09wPqs6QJrvbBEqGJGkd0g0/./1hYi', '2026-03-19 22:30:50', 0, 0.00),
(9, 'Tiago3@gmail.com', 'Tiago3', '924387397', '$2y$10$AmfVPdD6I6YDHMMdAtQN7Ok08SWGa976Ul88/I04DR9mMpvPMGD3K', '2026-03-19 22:37:30', 0, 0.00);

-- --------------------------------------------------------

--
-- Estrutura da tabela `user_transactions`
--

CREATE TABLE `user_transactions` (
  `ID` int(11) NOT NULL,
  `User_ID` int(11) NOT NULL,
  `Amount` decimal(10,2) NOT NULL,
  `Type` enum('credit','debit') NOT NULL,
  `Description` varchar(255) DEFAULT NULL,
  `Created_At` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `User_ID` (`User_ID`);

--
-- Índices para tabela `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `Cart_ID` (`Cart_ID`),
  ADD KEY `Product_ID` (`Product_ID`);

--
-- Índices para tabela `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Name` (`Name`);

--
-- Índices para tabela `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `Category_ID` (`Category_ID`),
  ADD KEY `products_ibfk_publisher` (`Publisher_ID`);

--
-- Índices para tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD UNIQUE KEY `Username` (`Username`);

--
-- Índices para tabela `user_transactions`
--
ALTER TABLE `user_transactions`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `User_ID` (`User_ID`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `carts`
--
ALTER TABLE `carts`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `categories`
--
ALTER TABLE `categories`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `products`
--
ALTER TABLE `products`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `user_transactions`
--
ALTER TABLE `user_transactions`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `users` (`ID`);

--
-- Limitadores para a tabela `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`Cart_ID`) REFERENCES `carts` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`Product_ID`) REFERENCES `products` (`ID`);

--
-- Limitadores para a tabela `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`Category_ID`) REFERENCES `categories` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `products_ibfk_publisher` FOREIGN KEY (`Publisher_ID`) REFERENCES `users` (`ID`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Limitadores para a tabela `user_transactions`
--
ALTER TABLE `user_transactions`
  ADD CONSTRAINT `user_transactions_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `users` (`ID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
