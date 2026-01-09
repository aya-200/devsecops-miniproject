<?php
session_start();
require 'db_connection.php'; // Inclut $pdo

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Vérification CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token invalide');
    }

    // Récupération des données
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        die('Tous les champs sont requis.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die('Email invalide.');
    }

    if ($password !== $confirm_password) {
        die('Les mots de passe ne correspondent pas.');
    }

    // Vérifier si email déjà utilisé
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        die('Email déjà utilisé.');
    }

    // Hachage du mot de passe
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insertion sécurisée
    $stmt = $pdo->prepare(
        "INSERT INTO users (UserName, email, Password, is_admin) VALUES (?, ?, ?, 0)"
    );

    if ($stmt->execute([$username, $email, $password_hash])) {
        echo "Inscription réussie ! <a href='admin-login.php'>Se connecter</a>";
    } else {
        echo "Erreur lors de l'inscription.";
    }
}
