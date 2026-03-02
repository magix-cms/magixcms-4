# Magix CMS 4

![PHP Version](https://img.shields.io/badge/PHP-%3E%3D%208.2-8892bf.svg)
![Status](https://img.shields.io/badge/Status-Pre--Alpha-red.svg)

## 📌 À propos de Magix CMS

**Magix CMS** est un système de gestion de contenu (CMS) conçu pour être rapide, léger et hautement modulable. Développé par Aurélien Gerits, il s'adresse aux développeurs et aux agences qui recherchent une véritable « boîte à outils » plutôt qu'une solution lourde et rigide.

Il permet de propulser des sites vitrines, des catalogues et des applications web avec une empreinte serveur minimale et une flexibilité maximale.

---

## 🚀 Quoi de neuf dans la version 4 ? (vs Magix CMS 3)

La version 4 n'est pas une simple mise à jour, c'est une refonte architecturale majeure qui s'appuie sur le framework **Magepattern 3**. L'objectif est de moderniser le cœur du CMS tout en gardant son ADN de légèreté.

* **PHP 8.2+ Exclusif :** Le code tire pleinement parti des dernières fonctionnalités de PHP (typage strict, union types, propriétés readonly, match expressions) pour des performances et une sécurité accrues.
* **Architecture SOLID :** Fini les "God Classes". Le code est désormais découpé en composants à responsabilité unique (ex: `UploadTool`, `ImageTool`, `UrlTool`, `ConfigDb`).
* **Nouvelle couche Base de Données :** Intégration du nouveau `QueryBuilder` et du `Layer` de Magepattern 3 avec gestion des connexions en Singleton PDO, garantissant une meilleure sécurité contre les injections SQL et une réduction des connexions inutiles.
* **Médias & Traitement d'images :** Migration vers **Intervention Image v3** avec support natif du format **WebP**, gestion avancée des miniatures et optimisation des ressources (driver GD ou Imagick).
* **Interface Backend :** Refonte de l'interface d'administration pour un rendu plus moderne, réactif et épuré.

---

## 🔄 Rétrocompatibilité

La philosophie de Magix CMS est de ne pas laisser ses utilisateurs derrière. Bien que le moteur ait été réécrit, la transition depuis la version 3 a été pensée pour être la plus fluide possible :

* **Structure de la Base de Données :** Les schémas des tables principales (comme `mc_cms_page` ou `mc_config_img`) restent familiers et globalement compatibles pour faciliter la migration de vos contenus.
* **Moteur de Template :** L'écosystème conserve sa logique d'affichage (basée sur Smarty), permettant aux développeurs front-end de retrouver rapidement leurs marques et d'adapter les anciens thèmes sans tout réapprendre.

---

## 🛠️ Prérequis & Installation

Conformément à l'approche de Magix CMS, **l'installation ne nécessite pas Composer**. Toutes les dépendances (y compris l'autoloader et les librairies tierces) sont déjà pré-embarquées dans le dossier `lib/`.

### Prérequis système
* Serveur web (Apache/Nginx)
* **PHP 8.2** ou supérieur
* Extension PHP **GD** (ou Imagick)
* MySQL / MariaDB

### Installation
1. Téléchargez ou clonez ce dépôt GitHub.
2. Transférez l'ensemble des fichiers sur votre serveur (ou en local).
3. Assurez-vous que les dossiers `upload/` et `var/` disposent des droits d'écriture (CHMOD 755 ou 777 selon votre configuration serveur).
4. Configurez vos accès à la base de données dans le fichier de configuration dédié.

---

## ⚠️ Avertissement (État du Projet)

Ce projet est actuellement en phase **Pre-Alpha / Alpha**.
Il est en cours de développement actif. De nombreuses fonctionnalités sont encore en chantier, et l'architecture peut subir des modifications sans préavis.

**Il est strictement déconseillé d'utiliser cette version en production.** Elle est mise à disposition à des fins de tests, de développement et de retour d'expérience.