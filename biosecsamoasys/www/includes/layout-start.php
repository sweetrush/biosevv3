<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Samoa Biosecurity System - <?php echo $pageTitle ?? 'Biosecurity Management'; ?>">
    <meta name="theme-color" content="#667eea">
    <title><?php echo $pageTitle ?? 'Biosecurity System'; ?></title>
    <link rel="stylesheet" href="styles.css?v=<?php echo CSS_CACHE_BUST; ?>">
</head>
<body>
    <div class="app-wrapper">
        <?php require_once __DIR__ . '/sidebar.php'; ?>
        <!-- Main Content Area -->
        <main class="main-content">
            <div class="content-container">
