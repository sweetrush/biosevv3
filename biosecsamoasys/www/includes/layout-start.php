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
    <div class="app-wrapper"<?php echo ($GLOBALS['voyage_edit_id'] ?? null) ? ' data-voyage-id="' . htmlspecialchars($GLOBALS['voyage_edit_id']) . '"' : ''; ?><?php echo ($GLOBALS['voyage_edit_no'] ?? null) ? ' data-voyage-no="' . htmlspecialchars($GLOBALS['voyage_edit_no']) . '"' : ''; ?><?php echo ($GLOBALS['voyage_edit_vessel'] ?? null) ? ' data-voyage-vessel="' . htmlspecialchars($GLOBALS['voyage_edit_vessel']) . '"' : ''; ?>>
        <?php require_once __DIR__ . '/sidebar.php'; ?>
        <!-- Main Content Area -->
        <main class="main-content">
            <div class="content-container">
