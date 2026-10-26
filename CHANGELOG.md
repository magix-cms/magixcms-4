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