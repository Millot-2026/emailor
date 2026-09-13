<?php
/* ============================================================
   EMAILOR — Page de présentation complète du projet
   Utilise le template générique partials/page-detail.php
   ============================================================ */

$slug      = 'emailor';
$title     = 'emailor';
$subtitle  = 'Plateforme de conception et d\'envoi d\'emails et de newsletters';
$statusKey = 'progress';
$technos   = ['PHP', 'JavaScript', 'HTML Email', 'Composer'];
$screenshot = '/images/accueil/photo-640x480.png';
$appHref   = 'emailor/';
$isStatic  = false;
$basePath  = '../';

$pitch = 'Outil dédié à la conception, la prévisualisation et l\'envoi d\'emails transactionnels et de newsletters. Il combine un éditeur visuel performant et un annuaire de contacts intégré.';

$sections = [
    [
        'title'      => 'Conception de Campagnes',
        'body'       => '<p>Emailor met à disposition un éditeur intuitif pour créer des gabarits d\'emails responsives, garantissant une lisibilité parfaite sur tous les clients de messagerie.</p>
<ul>
<li>Création de templates HTML optimisés</li>
<li>Gestion des carnets d\'adresses (Directory)</li>
<li>Prévisualisation et tests d\'envoi</li>
</ul>',
    ],
    [
        'title'      => 'Gestion Autonome',
        'body'       => '<p>S\'inscrivant dans la philosophie de l\'atelier nomade, la solution fonctionne sans dépendance à des services tiers onéreux, permettant un contrôle total sur les données de contact et les envois.</p>',
    ],
];

$isStatic = defined('FIREBASE_STATIC') && FIREBASE_STATIC;
$basePath = '../';

require $_SERVER['DOCUMENT_ROOT'] . '/partials/page-detail.php';
