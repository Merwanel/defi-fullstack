# 🚆 Défi Full stack - Routage de Train & Statistiques

| | Tests | Coverage |
|---------|-------|----------|
| **Backend** | [![Backend Tests](https://github.com/Merwanel/defi-fullstack/actions/workflows/backend-test-build-push.yaml/badge.svg)](https://github.com/Merwanel/defi-fullstack/actions/workflows/backend-test-build-push.yaml) | [![codecov](https://codecov.io/gh/Merwanel/defi-fullstack/branch/main/graph/badge.svg?flag=backend)](https://codecov.io/gh/Merwanel/defi-fullstack) |
| **Frontend** | [![Frontend Tests](https://github.com/Merwanel/defi-fullstack/actions/workflows/frontend-test-build-push.yaml/badge.svg)](https://github.com/Merwanel/defi-fullstack/actions/workflows/frontend-test-build-push.yaml) | [![codecov](https://codecov.io/gh/Merwanel/defi-fullstack/branch/main/graph/badge.svg?flag=frontend)](https://codecov.io/gh/Merwanel/defi-fullstack) |

## Résumé du défi :
Coder un PHP backend et un frontend Vue.js utilisant une API spécifiée dans `openapi.yaml`.

## Sommaire
- [1. Instructions](#1-instructions)
- [2. Architecture & Choix Techniques](#2-architecture--choix-techniques)
- [3. Améliorations](#3-améliorations)
- [4. Outils & Méthodologie](#4-outils--méthodologie)

## 1. Instructions

Pour lancer l'application complète (Backend + Frontend + Base de données + Redis) :

```bash
docker compose up -d
```

L'application sera accessible aux adresses suivantes :
*   **Frontend :** http://localhost:5173
*   **Backend API :** http://localhost:8080

Pour arrêter l'application :

```bash
docker compose down
```

Pour reconstruire les images (au lieu de les télécharger depuis ghcr.io) :

```bash
docker compose up -d --build
```

## 2. Architecture & Choix Techniques

*   **Stack** : **Vue.js 3** , **TypeScript** ,  **PHP (Slim)** , **MariaDB** , **Redis**
*   **Endpoints** :
    *   `GET /api/v1/status` : Healthcheck.
    *   `GET /api/v1/stations` : Liste des stations.
    *   `POST /api/v1/routes` : Calcul d'itinéraire.
    *   `GET /api/v1/stats/distances` : Statistiques agrégées.

*   **Slim Framework** : Nécessite moins de boilerplate que PHP vanilla et présente moins d'overhead comparé à un framework complet comme Laravel.

*   **CI / CD :** Le frontend et backend disposent de workflows (CI/CD) différents. Tant que le contrat d'interface (API) est respecté, les deux peuvent évoluer indépendamment.

*   **Docker Compose :** Les services backend et frontend essaieront de pull leur image depuis ghcr.io/merwanel/ . Si elles ne sont pas disponibles, elles seront construites localement. 

*   **Résilience Redis :** Le cache gère les échecs de connexion de manière silencieuse et retente la connexion à chaque utilisation. Par conséquent, dans le `docker-compose.yml` le service redis peut être lancé en parallèle des autres services.

*   **GET stations/** `openapi.yaml` ne spécifie pas de endpoint pour récupérer la liste des stations. `stations.json` pourrait être rajouté au frontend, mais ça ferait deux sources de vérité. C'est pourquoi j'ai ajouté un endpoint `GET stations/` qui retourne la liste des stations.

*   **DataLoader** : Le frontend a besoin des stations et le backend a besoin du graph des distances. Pour cela, un dataloader charge ces données au démarrage de l'application.

*   **PHP est Stateless** : Le bon côté est que PHP est résilient aux mauvais codes, le mauvais côté est que le dataloader communique avec la base de données à chaque requête. Donc j'ai mis en cache ces données avec **Redis**, sans TTL puisque celles-ci sont statiques.

*   **Dijkstra** : Redis était déjà implémenté, j'en ai profité pour mettre en cache les itinéraires calculés.

*   **Commentaires** : J'ai pris le parti de mettre peu de commentaires dans le code. Je préfère un code qui se documente par lui-même qu'un amas de commentaires.

## 3. Améliorations

*   **Meilleure UX:**
    *   Actuellement, dans le frontend, n'importe quel couple de stations de départ et d'arrivée est proposé. Par conséquent, l'utilisateur peut choisir une station de départ et une station d'arrivée sans itinéraire possible. Il faudrait pré-calculer les stations atteignables depuis chaque station pour améliorer l'expérience utilisateur.

*   **Optimisation Connexion Redis :** PHP se reconnecte en TCP à chaque requête, `pconnect` pourrait réduire les ressources gaspillées.

*   **Stratégies de Scalabilité & Algorithmes :**
    *   **Graphe Statique :** Actuellement, le graphe que dessine le réseau de train est statique. J'aurais donc pu pré-calculer tous les plus courts chemins avec l'algorithme de Floyd-Warshall. Néanmoins, en utilisant Dijkstra couplé à Redis, comme je le fais, cela revient au même. Dijkstra + Redis est en quelque sorte une version lazy de Floyd-Warshall.
    *   **Graphe Dynamique (Travaux, Ajouts) :** Si le graphe est dynamique ( à cause de fermeture temporaire de station, grêves, etc ), le problème devient plus complexe. Il faut notamment, une stratégie d'invalidation du cache.
        *   *Stratégie Naïve :* Tout invalider au moindre changement.
        *   *Fine :* Si une station est modifiée/supprimée, on invalide uniquement les trajets qui passent par cette station. Cela nécessite de stocker un index inversé (pour chaque station -> liste des trajets l'empruntant).
    *   **Dijkstra** : Si le calcul de l'itinéraire devenait un problème de performance, on pourrait réécrire Dijkstra en C++.
    *   **Sécurité (Production)** : **Docker Secrets** pour les mots de passe de Redis et MariaDB.

## 4. Outils & Méthodologie

*   **Développement :** 
    *   **TDD / DDD** : pas utilisés.
    *   __Backend__ : `nodemon` (voir `nodemon.json`) .
    *   __Frontend__ : Le hot reloading est géré par Vite.
    *   __Tests__ : **PHPUnit** pour le backend et **Vitest** pour le frontend, avec reporting de couverture via **Codecov**.
*   **IA Generative :** Je maîtrise moins PHP que Vue , donc pour PHP, j'ai utilisé l'IA pour spécifier le besoin et générer le squelette du code, puis j'ai fait des modifications manuelles.