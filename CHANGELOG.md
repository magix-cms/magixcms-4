## [4.0.3] - 2026-11-25

### Ajouté
- **Galeries d'images (`modal-img-meta.tpl`, `MagixGallery.js`)** : Ajout de compteurs de caractères dynamiques (badges Bootstrap 5.3 avec seuils d'alerte) et des attributs `maxlength` sur les champs `title_img` (150), `alt_img` (150) et `caption_img` (255).
- **Catalogue — Produits associés (`mc_catalog_product_rel`)** :
  - **Administration (`ProductController`, `ProductDb`, `product/edit.tpl`)** : Ajout d'un onglet dédié sur la fiche produit permettant d'associer des produits via recherche AJAX instantanée (nom, référence SKU ou ID), de définir leur ordre d'affichage par glisser-déposer (`Sortable.js`) et de purger automatiquement les liaisons à la suppression.
  - **Frontend (`ProductController`, `ProductDb`, `catalog/product.tpl`)** : Ajout de `ProductDb::getRelatedProducts()` (avec cache SQL et support du hook `extendProductList`) et affichage des produits associés via le composant de grille partagé `catalog/loop/product-grid.tpl` (`{#product_related_title#}`).
- **TinyMCE 7 — Sélecteur visuel de boutons (`mcbuttons`)** :
  - Ajout du modèle `App\Backend\Model\ButtonScanner` pour analyser dynamiquement le fichier `button.css` / `buttons.css` du thème actif (avec repli sur `default` et support des annotations `/* @label: ... */`).
  - Ajout de l'action `getButtons` dans `SettingController` exposant les variantes et tailles disponibles en JSON.
  - Création du plugin `mcbuttons` avec modale native TinyMCE 7 et aperçu isolé via **Shadow DOM** chargeant la feuille de style du thème courant.
  - Intégration de filtres dynamiques par groupe de couleur (`Main`, `SD`, `Dark`, `Green`, `White`...), recherche instantanée et bascule fond clair / fond sombre.
  - Ajout des sélecteurs Bootstrap 5.3 avec prévisualisation en direct : tailles (`btn-sm`, `btn-lg`), arrondis (`rounded-0` à `rounded-5`, `rounded-pill`, `rounded-circle`) et paddings (`px-0` à `px-5`, `py-0` à `py-5`).
  - Support complet du mode édition sur les liens `a.btn` existants (pré-sélection de la variante et de la pastille couleur, centrage automatique du défilement, conservation des classes utilitaires et des icônes `bsicons`).
  - Internationalisation complète du plugin (`langs/en_US.js` et `langs/fr_FR.js`).
- **TinyMCE 7 (`editor.js`)** : Génération dynamique des échelles complètes Bootstrap 5.3 pour les espacements internes (`p`, `px`, `py`, `pt`, `pb`, `ps`, `pe` de `0` à `5`) et les marges externes (`m`, `mx`, `my`, `mt`, `mb`, `ms`, `me` de `0` à `5` ainsi que `mx-auto`), applicables directement sur les balises `<a>`.
- **TinyMCE 7 (`editor.js`)** : Ajout des tailles de police sémantiques Bootstrap (`fs-1` à `fs-6`) et des couleurs de fond pastel Bootstrap 5.3 (`bg-primary-subtle`, `bg-success-subtle`, `bg-secondary-subtle`, `bg-warning-subtle`, `bg-danger-subtle`) dans `style_formats`.
- **Utilitaire de mots de passe (`MagixPasswordUtility.js`)** : Création d'une classe Vanilla JS générique permettant d'afficher ou masquer les mots de passe (`password` / `text`) et de générer des mots de passe robustes (16 caractères incluant minuscule, majuscule, chiffre et caractère spécial).
- **Gestion des administrateurs (`employee/add.tpl`, `employee/edit.tpl`)** : Intégration de l'affichage/masquage et du générateur de mot de passe sur le champ `passwd_admin`, ainsi que du bouton d'affichage sur le champ `current_passwd_admin`.
- **Configuration E-mails & SMTP (`mail_setting/index.tpl`)** : Intégration de `MagixPasswordUtility` (affichage/masquage et génération) sur le champ mot de passe SMTP (`settings[set_password]`), avec désactivation synchronisée via l'interrupteur `smtp_enabled`.
- **Installateur (`step3.tpl`)** : Intégration autonome (Vanilla JS compatible Smarty) de l'affichage/masquage sur les champs `admin_password` et `admin_password_conf`, ainsi que du générateur de mot de passe robuste (16 caractères) remplissant et validant automatiquement les deux champs.

### Modifié
- **Structure SQL — Métadonnées d'images (`install.sql`)** : Élargissement des colonnes `alt_img` et `title_img` à `varchar(150)` et `caption_img` à `varchar(255)` sur les tables `mc_cms_page_img_content`, `mc_catalog_product_img_content`, `mc_catalog_cat_img_content`, `mc_about_img_content` et `mc_news_img_content`.
- **Structure SQL (`mc_catalog_product_rel`)** : Passage de `id_rel` en `int UNSIGNED`, ajout de l'index unique `idx_product_rel_unique` (`id_product`, `id_product_2`) et ajout de la colonne `order_rel` pour le tri personnalisé.
- **Librairie interne (`Magepattern`)** : Mise à jour vers la version **3.1.0** intégrant `CryptTool` (AES-256-CBC), `SecurityTool` et `HTMLPurifier`, les timeouts de session (`Session`), le durcissement de `RedirectTool`, le Singleton PDO dans `Layer::connection()`, `QueryHelper`, les clauses `GROUP BY` / `HAVING` dans `QueryBuilder`, `FileTool::removeDirectory()` ainsi que `symfony/mailer` `7.4.22` (PHP 8.2+).
- **Moteur de templates (`Smarty`)** : Mise à jour de `smarty/smarty` de la version `5.8.4` vers la branche `5.9` (`composer.lock` et autoloader optimisé).
- **TinyMCE 7 (`editor.js`)** : Ajout d'un sélecteur ciblé (`a, button, h1, h2, h3, h4, h5, h6, span`) sur les formats de casse (`text-uppercase`, `text-lowercase`, `text-capitalize`) afin d'appliquer la classe directement sur la balise `<a>` ou le titre sans encapsuler dans un `<span>` ni cibler le `<p>` parent.
- **TinyMCE 7 (`editor.js`)** : Mise à jour des expressions régulières exclusives dans l'événement `FormatApply` (`exclusiveRegexes`) afin de remplacer proprement les classes d'une même famille (paddings, marges, tailles `fs-*`, fonds `bg-*-subtle`) sans empilement.

### Corrigé
- **TinyMCE 7 — Extension IA (`mc_ai_gemini`)** : Transmission du jeton CSRF (`X-CSRF-Token` / `hashtoken`) vers le contrôleur `GeminiAI`, ajout d'un timeout client (`AbortController` à 65 s avec annulation à la fermeture de la modale) et sécurisation de l'analyse des réponses JSON.
- **Modification du mot de passe administrateur (`EmployeeController`)** : Récupération effective de `$_POST['passwd_admin']` dans `processEdit()` permettant la mise à jour du mot de passe d'un autre administrateur, accompagnée du contrôle de ré-authentification (`current_passwd_admin`) lors de la modification de son propre compte.
- **Sauvegarde des métadonnées d'images (`MagixGallery.js`, `modal-img-meta.tpl`)** : Correction de l'échec d'enregistrement en mode SQL strict lors du dépassement des anciennes limites de 70/125 caractères et ajout d'un paramètre anti-cache lors du chargement de la modale (`getImgMeta`).
- **Exécution SQL (`Magepattern\Component\Database\Layer`)** : Vérification explicite du retour booléen de `PDOStatement::execute()` dans `execute()` et `transaction()` avec journalisation SQLSTATE (`Logger`) afin d'éviter tout faux positif lors d'un rejet SQL.
- **Édition d'administrateur (`employee/edit.tpl`)** : Ajout du champ de ré-authentification `current_passwd_admin` lors de la modification de son propre compte, affichage du champ `passwd_admin` avec générateur pour l'édition des autres comptes et suppression du champ de mot de passe dupliqué dans le formulaire.

## [4.0.3] - 2026-11-22

### Ajouté
- **Catalogue — Produits associés (`mc_catalog_product_rel`)** :
  - **Administration (`ProductController`, `ProductDb`, `product/edit.tpl`)** : Ajout d'un onglet dédié sur la fiche produit permettant d'associer des produits via recherche AJAX instantanée (nom, référence SKU ou ID), de définir leur ordre d'affichage par glisser-déposer (`Sortable.js`) et de purger automatiquement les liaisons à la suppression.
  - **Frontend (`ProductController`, `ProductDb`, `catalog/product.tpl`)** : Ajout de `ProductDb::getRelatedProducts()` (avec cache SQL et support du hook `extendProductList`) et affichage des produits associés via le composant de grille partagé `catalog/loop/product-grid.tpl` (`{#product_related_title#}`).
- **TinyMCE 7 — Sélecteur visuel de boutons (`mcbuttons`)** :
  - Ajout du modèle `App\Backend\Model\ButtonScanner` pour analyser dynamiquement le fichier `button.css` / `buttons.css` du thème actif (avec repli sur `default` et support des annotations `/* @label: ... */`).
  - Ajout de l'action `getButtons` dans `SettingController` exposant les variantes et tailles disponibles en JSON.
  - Création du plugin `mcbuttons` avec modale native TinyMCE 7 et aperçu isolé via **Shadow DOM** chargeant la feuille de style du thème courant.
  - Intégration de filtres dynamiques par groupe de couleur (`Main`, `SD`, `Dark`, `Green`, `White`...), recherche instantanée et bascule fond clair / fond sombre.
  - Ajout des sélecteurs Bootstrap 5.3 avec prévisualisation en direct : tailles (`btn-sm`, `btn-lg`), arrondis (`rounded-0` à `rounded-5`, `rounded-pill`, `rounded-circle`) et paddings (`px-0` à `px-5`, `py-0` à `py-5`).
  - Support complet du mode édition sur les liens `a.btn` existants (pré-sélection de la variante et de la pastille couleur, centrage automatique du défilement, conservation des classes utilitaires et des icônes `bsicons`).
  - Internationalisation complète du plugin (`langs/en_US.js` et `langs/fr_FR.js`).
- **TinyMCE 7 (`editor.js`)** : Génération dynamique des échelles complètes Bootstrap 5.3 pour les espacements internes (`p`, `px`, `py`, `pt`, `pb`, `ps`, `pe` de `0` à `5`) et les marges externes (`m`, `mx`, `my`, `mt`, `mb`, `ms`, `me` de `0` à `5` ainsi que `mx-auto`), applicables directement sur les balises `<a>`.
- **TinyMCE 7 (`editor.js`)** : Ajout des tailles de police sémantiques Bootstrap (`fs-1` à `fs-6`) et des couleurs de fond pastel Bootstrap 5.3 (`bg-primary-subtle`, `bg-success-subtle`, `bg-secondary-subtle`, `bg-warning-subtle`, `bg-danger-subtle`) dans `style_formats`.
- **Utilitaire de mots de passe (`MagixPasswordUtility.js`)** : Création d'une classe Vanilla JS générique permettant d'afficher ou masquer les mots de passe (`password` / `text`) et de générer des mots de passe robustes (16 caractères incluant minuscule, majuscule, chiffre et caractère spécial).
- **Gestion des administrateurs (`employee/add.tpl`, `employee/edit.tpl`)** : Intégration de l'affichage/masquage et du générateur de mot de passe sur le champ `passwd_admin`, ainsi que du bouton d'affichage sur le champ `current_passwd_admin`.
- **Configuration E-mails & SMTP (`mail_setting/index.tpl`)** : Intégration de `MagixPasswordUtility` (affichage/masquage et génération) sur le champ mot de passe SMTP (`settings[set_password]`), avec désactivation synchronisée via l'interrupteur `smtp_enabled`.
- **Installateur (`step3.tpl`)** : Intégration autonome (Vanilla JS compatible Smarty) de l'affichage/masquage sur les champs `admin_password` et `admin_password_conf`, ainsi que du générateur de mot de passe robuste (16 caractères) remplissant et validant automatiquement les deux champs.

### Modifié
- **Structure SQL (`mc_catalog_product_rel`)** : Passage de `id_rel` en `int UNSIGNED`, ajout de l'index unique `idx_product_rel_unique` (`id_product`, `id_product_2`) et ajout de la colonne `order_rel` pour le tri personnalisé.
- **Librairie interne (`Magepattern`)** : Mise à jour vers la version **3.1.0** intégrant `CryptTool` (AES-256-CBC), `SecurityTool` et `HTMLPurifier`, les timeouts de session (`Session`), le durcissement de `RedirectTool`, le Singleton PDO dans `Layer::connection()`, `QueryHelper`, les clauses `GROUP BY` / `HAVING` dans `QueryBuilder`, `FileTool::removeDirectory()` ainsi que `symfony/mailer` `7.4.22` (PHP 8.2+).
- **Moteur de templates (`Smarty`)** : Mise à jour de `smarty/smarty` de la version `5.8.4` vers la branche `5.9` (`composer.lock` et autoloader optimisé).
- **TinyMCE 7 (`editor.js`)** : Ajout d'un sélecteur ciblé (`a, button, h1, h2, h3, h4, h5, h6, span`) sur les formats de casse (`text-uppercase`, `text-lowercase`, `text-capitalize`) afin d'appliquer la classe directement sur la balise `<a>` ou le titre sans encapsuler dans un `<span>` ni cibler le `<p>` parent.
- **TinyMCE 7 (`editor.js`)** : Mise à jour des expressions régulières exclusives dans l'événement `FormatApply` (`exclusiveRegexes`) afin de remplacer proprement les classes d'une même famille (paddings, marges, tailles `fs-*`, fonds `bg-*-subtle`) sans empilement.

### Corrigé
- **Édition d'administrateur (`employee/edit.tpl`)** : Ajout du champ de ré-authentification `current_passwd_admin` lors de la modification de son propre compte, affichage du champ `passwd_admin` avec générateur pour l'édition des autres comptes et suppression du champ de mot de passe dupliqué dans le formulaire.

## [4.0.2] - 2026-11-12

### Ajouté
- **MagixClear** : Ajout de boutons d'action dédiés (« Tout cocher » / « Tout décocher ») séparés pour le **Site Public (Frontend)** et l'**Administration (Backend)**, accompagnés d'un bouton permettant de restaurer la sélection recommandée par défaut.
- **MagixClear** : Calcul et affichage asynchrone (AJAX) du poids des dossiers de cache (`templates_c`, `tpl_caches`, `caches`, `caches/sql`) et de logs, avec purge automatique d'OPcache après nettoyage.
- **Formulaires Backend (`MagixForms.js`)** : Support du comptage par mots (`count-words`) en plus du comptage par caractères pour les compteurs SEO, et mise à jour dynamique des champs d'URLs publiques après sauvegarde.

### Modifié
- **Gestionnaire de plugins** : Amélioration de l'extraction et de l'affichage du module cible pour les extensions de type `addon` (`PluginController` et `plugin/index.tpl`).

### Corrigé
- **Paramètres (`SettingController`)** : Ajout des clés manquantes (`content_css`, `analytics`, `product_per_page`, `vat_rate`, `price_display`, `news_per_page`, `holder_bgcolor`, `logo_percent`) dans la liste blanche (`$allowedKeys`) afin de rétablir leur enregistrement en base de données.
- **Accessibilité (`layout.tpl`)** : Libération automatique du focus actif (`blur()`) lors de l'événement `hide.bs.modal` pour corriger les erreurs WAI-ARIA à la fermeture des modales Bootstrap.
- **MagixClear** : Correction de l'imbrication des balises HTML et des listes Bootstrap dans la vue d'administration (`index.tpl`).

### Sécurité
- **Protection CSRF globale** : Récupération et injection dynamique du jeton `hashtoken` (via `<meta name="csrf-token">`) dans toutes les requêtes AJAX/Fetch de `MagixForms.js` (formulaires, traitement par lots, uploads XHR) et de `MagixPlugins.js` (installation et désinstallation des plugins).
- **Synchronisation de session** : Rechargement automatique de la page après l'installation ou la désinstallation d'un plugin afin d'éviter toute expiration ou désynchronisation du jeton CSRF.

## [4.0.2] - 2026-10-27
### Sécurité & Stabilité
- **Protection contre l'assignation en masse des réglages (F7) :**
  - Ajout d'une liste blanche stricte (`allowedKeys`) dans `SettingController` interdisant l'écrasement de clés système sensibles (notamment `theme`).
- **Assainissement des traductions & Neutralisation XSS :**
  - Ajout de la méthode `sanitizeTranslation()` dans `TranslationController` pour filtrer les balises actives et attributs JavaScript avant écriture dans les fichiers `.conf`.
  - Neutralisation des schémas malveillants (`javascript:`, `vbscript:`, `data:`) sur les variables d'URLs.
- **Résilience du thème et prévention DoS (DOS-1) :**
  - Validation stricte du chemin de thème dans `BaseController::initSkin()` pour bloquer toute traversée de dossier (Path Traversal).
  - Mise en place d'un repli (fallback) garanti sur le thème `default` si le dossier cible est manquant ou incomplet, protégeant l'affichage lors des purges de cache.
- **Sécurisation du module Logo & Accessibilité (#50) :**
  - Validation du jeton CSRF (`hashtoken`) dans `LogoController::updateContent()`.
  - Assainissement HTML des champs `alt_logo` et `title_logo` via `FormTool::simpleClean()`.
  - Transmission explicite du paramètre `title` au composant `img.tpl` dans le header frontend.
- **Chiffrement des clés d'API tierces (Google reCAPTCHA) :**
  - Stockage chiffré (AES-256-CBC) de la clé secrète reCAPTCHA en base de données et masquage visuel (`********`) dans l'administration.
  - Déchiffrement automatique et transparent lors de la vérification du score côté serveur dans `FrontendController`.
- **Homogénéisation de l'authentification SMTP :**
  - Déchiffrement à la volée du mot de passe SMTP dans le plugin Contact (`FrontendController::processSend()`).
  - Déchiffrement et prise en compte de la configuration SMTP globale lors de l'envoi de réinitialisation de mot de passe administrateur (`LoginController::processForgotPassword()`).
- **Prévention des fuites d'URL non-publiées (#68) :**
  - Ajout des conditions de publication (`published_pages = 1`, `published_about = 1`, `published_cat = 1`) dans les jointures de `MenuDb::getFrontendTree()`, empêchant l'exposition des slugs de brouillons dans le DOM du menu.

## [4.0.2] - 2026-10-26

### Sécurité (Correctifs d'audit)
- **Correction du Déni de Service Frontend (DOS-1) et Path Traversal :**
  - Refonte de la méthode `BaseController::initSkin()` côté Frontend.
  - Implémentation d'un assainissement strict de la variable `theme` (`basename` + regex alphanumérique) empêchant l'injection de chemins relatifs (ex: `../media`).
  - Validation absolue de l'existence du dossier du thème cible et de son fichier maître `layout.tpl`.
  - En cas d'anomalie (thème introuvable, dossier vide), mise en place d'un mécanisme de repli (fallback) garanti sur le thème `default`, assurant la continuité de service même en cas de purge du cache (qui menait à un DoS en cas d'erreur de thème).
  - Synchronisation de `BaseController::initTranslations()` pour exploiter ce paramètre sécurisé.
- **Protection contre l'assignation de masse des paramètres (F7) :**
  - Implémentation d'une liste blanche stricte (`allowedKeys`) dans `SettingController::processSave()`.
  - Blocage de la modification arbitraire de clés système sensibles (notamment l'écrasement de `theme`), empêchant les attaques par traversée de répertoire (Path Traversal) vers des dossiers de médias.
- **Assainissement des traductions & Prévention Stored XSS (i18n) :**
  - Ajout de la méthode `sanitizeTranslation()` dans `TranslationController::save()`.
  - Purgation systématique des balises actives (`<script>`, `<iframe>`, `<object>`, `<embed>`) et des gestionnaires d'événements JavaScript (`onload`, `onerror`, `onclick`) avant l'écriture dans les fichiers `.conf` (protégeant les templates utilisant le modificateur `nofilter`).
  - Validation stricte des clés contenant des URLs (`legal_url`, `footer_website_url`, etc.) avec neutralisation immédiate des protocoles exécutables (`javascript:`, `vbscript:`, `data:`).
- **Chiffrement des identifiants SMTP (#55) :**
    - Ajout du composant réversible `Magepattern\Component\Security\CryptTool` (chiffrement AES-256-CBC).
    - Gestion sécurisée et autonome de la clé de chiffrement (support de la constante `SECRET_KEY` ou génération persistante d'un fichier `crypt_secret.php` protégé).
    - Chiffrement du mot de passe SMTP au repos dans la table `mc_setting` et masquage systématique (`********`) dans l'interface d'administration.
- **Protection Brute-Force & Lockout Admin (#59) :**
    - Ajout des colonnes `failed_attempts` et `locked_until` dans la table `mc_admin_employee`.
    - Verrouillage temporaire automatique de 15 minutes après 5 tentatives de connexion consécutives infructueuses.
    - Temporisation anti-énumération sur les requêtes d'authentification invalides.
    - Blocage de la demande de réinitialisation de mot de passe sur un compte temporairement verrouillé.
- **Gestion de l'expiration et Timeout de Session (#60) :**
    - Implémentation d'un timeout d'inactivité (Idle Timeout : 1 heure) et d'un timeout absolu (Absolute Lifetime : 12 heures) au sein de la classe `Magepattern\Component\HTTP\Session`.
- **Révocation des sessions actives au changement de mot de passe (#61) :**
    - Régénération de `keyuniqid_admin` lors de toute modification de mot de passe dans `EmployeeController`.
    - Vérification continue de la concordance entre le jeton de session et la valeur en base de données dans `BaseController::checkAuthentication()`, déconnectant instantanément tout accès tiers concurrent.
- **Renforcement de la politique de mot de passe (#62) :**
    - Relèvement du seuil de robustesse à 12 caractères minimum (avec majuscule, minuscule, chiffre et caractère spécial) pour tous les comptes administrateurs.
    - Intégration de la validation stricte côté serveur dans `FinalizeController` afin d'interdire l'installation du CMS avec un mot de passe vide ou faible.
    - Mise à jour des formulaires de saisie et modèles de vues correspondants (`add.tpl`, `edit.tpl`, `step3.tpl`).
- **Prévention Stored XSS sur les fichiers SVG (XSS-2) :**
    - Interception des flux d'upload dans le connecteur elFinder avec assainissement automatique des fichiers au format `image/svg+xml`.
    - Éradication des balises `<script>`, des gestionnaires d'événements JavaScript en ligne (`onload`, `onclick`, `onmouseover`), des schémas `javascript:` et des conteneurs `<foreignObject>`.

## [4.0.2] - 2026-10-26

### Sécurité (Correctifs critiques)
- **Open Redirect :** Blocage des redirections externes non autorisées et des protocoles dangereux (`javascript:`, `data:`) au sein du composant de routage `RedirectTool`.
- **BOLA / Élévation de privilèges :** Renforcement de l'autorisation au niveau objet (Object-Level Authorization) dans la gestion des administrateurs. Les rôles restreints ne peuvent plus éditer un Super-Administrateur.
- **Ré-authentification :** Exigence du mot de passe actuel pour toute modification de l'adresse e-mail ou du mot de passe par l'utilisateur connecté (`EmployeeController`).
- **Path Traversal (Plugins) :** Assainissement strict (Regex) des paramètres d'URL et validation des chemins absolus (`realpath()`) lors de l'installation et la désinstallation des plugins.
- **CSRF (Plugins) :** Obligation de fournir un jeton de sécurité (`hashtoken`) valide pour exécuter les scripts SQL d'installation et de désinstallation des plugins.
- **Stored XSS (Contenu Riche) :** Intégration de la librairie `HTMLPurifier` dans le framework natif (`Magepattern\Component\Tool\SecurityTool`). Tout le contenu HTML issu des éditeurs WYSIWYG est désormais purgé des scripts malveillants avant affichage (Pages, News, Catalogue, About).
- **BFLA (Gestionnaire de médias) :** Verrouillage du connecteur elFinder. L'accès direct est désormais conditionné par la vérification stricte du rôle et exige la possession d'au moins un droit d'écriture (ajout ou modification) sur le CMS.
- **Upload elFinder :** Mise à jour des règles d'exclusion d'extensions pour bloquer les contournements par extensions multiples (`.phps`, `.phtml`, `.inc`, etc.).
- **JSON-LD (XSS) :** Ajout des drapeaux d'échappement hexadécimal (`JSON_HEX_TAG`, etc.) lors de la sérialisation des schémas Schema.org dans l'ensemble des `Presenter` et de `SeoHelper`.
- **Contrôle d'accès global (RBAC) :** Renforcement de `BaseController` pour vérifier dynamiquement les permissions granulaires (`append`, `edit`, `del`, `action`) au niveau du noyau, au-delà de la simple consultation (`view`).

### Ajouté
- **Sécurité Magepattern :** Nouveau composant `SecurityTool` intégré au `Bootstrap` pour centraliser les méthodes de purification et de sécurisation du CMS.
- **Interface Éditeur :** Ajout d'un champ "Mot de passe actuel" dynamique dans le formulaire d'édition du profil administrateur.

## [4.0.2] - 2026-10-24

### Sécurité
- **JSON-LD (XSS) :** Ajout des drapeaux d'échappement hexadécimal (`JSON_HEX_TAG`, `JSON_HEX_AMP`, `JSON_HEX_APOS`, `JSON_HEX_QUOT`) lors de la sérialisation des schémas Schema.org dans l'ensemble des `Presenter` et de `SeoHelper`.
- **Contrôle d'accès (RBAC) :** Renforcement de `BaseController` pour vérifier dynamiquement les permissions granulaires (`append`, `edit`, `del`, `action`) en plus de la consultation (`view`).
- **Élévation de privilèges :** Verrouillage dans `EmployeeController` interdisant l'édition du super-administrateur principal (ID 1) et l'attribution non autorisée du rôle super-admin.
- **Path Traversal :** Assainissement strict des paramètres d'URL et formulaires (`basename`, regex alphanumérique) dans `ThemeController` et `TranslationController`.
- **Upload elFinder :** Mise à jour des règles d'exclusion d'extensions pour bloquer les contournements par extensions multiples (`.phps`, `.phtml`, `.inc`, etc.).

### Corrigé
- **Routage AMP :** Priorisation de la redirection 301 des URL AMP dans le `.htaccess` pour conserver le préfixe de langue (`/fr/...`) avant la réécriture interne.
- **Tags MagixLastNews :** Nettoyage automatique des slugs avec `Url::clean` pour éliminer les accents dans les widgets d'accueil.
- **Filtres de recherche Admin :** Correction de la recherche par date partielle dans le listing des actualités.

### Ajouté
- **Fil d'Ariane Pages :** Prise en compte du parent direct dans le breadcrumb des pages de contenu.
- **Listing News Admin :** Ajout de la colonne type (Évènement / Article) avec badge visuel et filtre par menu déroulant, ainsi que l'affichage des dates d'évènement.