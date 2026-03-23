# GoogleRecaptcha

[![Release](https://img.shields.io/github/release/magix-cms/google-recaptcha.svg)](https://github.com/magix-cms/google-recaptcha/releases/latest)
[![License](https://img.shields.io/github/license/magix-cms/google-recaptcha.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D%208.2-blue.svg)](https://php.net/)
[![Magix CMS](https://img.shields.io/badge/Magix%20CMS-4.x-success.svg)](https://www.magix-cms.com/)

**GoogleRecaptcha** est un plugin de sécurité anti-spam de pointe conçu spécifiquement pour **Magix CMS 4**. Il intègre de manière invisible la technologie Google reCAPTCHA v3 pour protéger vos formulaires frontend (contact, commentaires, etc.) contre les robots et les abus, sans jamais dégrader l'expérience de vos visiteurs.

## 🚀 Installation

### Option 1 : Via Composer (Recommandé)

C'est la méthode la plus propre pour gérer vos extensions et leurs mises à jour.

1. Vérifiez que votre fichier `composer.json` (à la racine de votre CMS) autorise l'installation des plugins Magix dans le dossier `/plugins/` :

```json
"extra": {
    "installer-paths": {
      "plugins/GoogleRecaptcha/": ["magix-cms/google-recaptcha"]
    }
}
```
2. Exécutez la commande suivante à la racine de votre site :

```bash
composer require magix-cms/google-recaptcha
```
3. Rendez-vous dans l'administration : **Extensions** > **Gestionnaire** et cliquez sur le bouton d'installation.

### Option 2 : Installation Manuelle

1. Téléchargez et décompressez l'archive du plugin.
2. Placez le dossier `GoogleRecaptcha` dans le répertoire `plugins/` de votre installation.
3. Connectez-vous à l'administration de votre site.
4. Rendez-vous dans **Extensions** > **Gestionnaire**.
5. Cliquez sur le bouton d'installation automatique pour **GoogleRecaptcha**.

## 🛠 Configuration & Utilisation

Contrairement aux modules d'affichage (comme le Slideshow), ce plugin opère de manière globale et silencieuse en arrière-plan.

* **Clés API :** Une fois installé, rendez-vous dans la configuration du plugin pour y renseigner votre **Clé de site (Publique)** et votre **Clé secrète** fournies par la console Google reCAPTCHA (v3).
* **Liaison aux modules :** Le plugin vous permet de choisir précisément quels formulaires du site sécuriser (ex: module Contact). Si un module est lié, le reCAPTCHA s'activera automatiquement sur sa page.
* **Intégration Frontend :** Le système injecte dynamiquement un script "Just-In-Time" couplé à la classe Vanilla JS globale `MagixFrontForms`. Vous n'avez aucune balise HTML à ajouter manuellement dans vos formulaires.

## ✨ Fonctionnalités

* **Technologie v3 Invisible :** Fini les cases à cocher et les grilles d'images agaçantes pour vos utilisateurs. L'analyse comportementale se fait en arrière-plan.
* **Architecture Just-In-Time (JIT) :** Optimisation extrême des performances. Le jeton de sécurité n'est généré par le navigateur qu'à la milliseconde où le visiteur clique sur "Envoyer", évitant toute expiration prématurée et toute requête réseau inutile.
* **Vérification Backend cURL (Fail-Safe) :** Utilisation de cURL avec gestion stricte des Timeouts. Si l'API de Google ne répond pas, le plugin laisse passer le message pour ne jamais bloquer un contact légitime. Code moderne et optimisé, 100% compatible PHP 8.2 à 8.5+.
* **Détection CSRF / AJAX :** Compatible nativement avec les soumissions de formulaires asynchrones (fetch) sans rechargement de page.
* **Feedback Utilisateur :** Notifications automatisées (Toasts) en cas d'échec de la validation sécuritaire.

## 📄 Licence

Ce projet est sous licence **GPLv3**. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

Copyright (C) 2008 - 2026 Gerits Aurelien (Magix CMS)

Ce programme est un logiciel libre ; vous pouvez le redistribuer et/ou le modifier selon les termes de la Licence Publique Générale GNU telle que publiée par la Free Software Foundation ; soit la version 3 de la Licence, ou (à votre discrétion) toute version ultérieure.
`