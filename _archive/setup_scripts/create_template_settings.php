<?php

$pdo = new PDO('mysql:host=localhost;dbname=tenant_nkprigorjem', 'root', '');

$pdo->exec('CREATE TABLE IF NOT EXISTS template_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    website_name VARCHAR(255) DEFAULT "NK Prigorjem",
    slogan VARCHAR(255) NULL,
    logo VARCHAR(255) NULL,
    logo_height INT DEFAULT 50,
    primary_color VARCHAR(20) DEFAULT "#1e40af",
    secondary_color VARCHAR(20) DEFAULT "#dc2626",
    accent_color VARCHAR(20) DEFAULT "#f59e0b",
    header_bg_color VARCHAR(20) DEFAULT "#1f2937",
    footer_bg_color VARCHAR(20) DEFAULT "#111827",
    text_color VARCHAR(20) DEFAULT "#1f2937",
    show_logo BOOLEAN DEFAULT TRUE,
    sticky_header BOOLEAN DEFAULT TRUE,
    header_style VARCHAR(20) DEFAULT "default",
    footer_about TEXT NULL,
    footer_email VARCHAR(255) NULL,
    footer_phone VARCHAR(255) NULL,
    footer_address VARCHAR(255) NULL,
    facebook_url VARCHAR(255) NULL,
    instagram_url VARCHAR(255) NULL,
    twitter_url VARCHAR(255) NULL,
    youtube_url VARCHAR(255) NULL,
    tiktok_url VARCHAR(255) NULL,
    show_next_match BOOLEAN DEFAULT TRUE,
    show_last_results BOOLEAN DEFAULT TRUE,
    show_standings BOOLEAN DEFAULT TRUE,
    show_top_scorers BOOLEAN DEFAULT TRUE,
    show_news BOOLEAN DEFAULT TRUE,
    news_count INT DEFAULT 3,
    enable_dark_mode BOOLEAN DEFAULT FALSE,
    enable_animations BOOLEAN DEFAULT TRUE,
    google_analytics_id VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

// Initialen Eintrag erstellen
$pdo->exec("INSERT INTO template_settings (website_name, slogan, footer_about, created_at, updated_at)
VALUES (
    'NK Prigorjem',
    'Nogometni Klub Prigorjem',
    'Dobrodošli na službenu web stranicu NK Prigorjem. Pratite naše utakmice, rezultate i vijesti.',
    NOW(),
    NOW()
)");

echo "✓ template_settings Tabelle erstellt und initialisiert!\n";
