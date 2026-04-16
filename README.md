# Stubborn — Site e-commerce Symfony

Boutique en ligne de sweat-shirts pour la marque **Stubborn** (*Don't compromise on your look*).  
Projet réalisé avec **Symfony 7.2**, **MySQL 8** et **Stripe** (paiement en mode test).

---

## Prérequis

| Outil | Version minimale |
|-------|-----------------|
| PHP | 8.2 |
| Composer | 2.x |
| MySQL | 8.x |
| Symfony CLI | 5.x |

---

## Installation

```bash
# 1. Cloner le dépôt
git clone https://github.com/EleaDSB/CEF-site_ecommerce.git
cd CEF-site_ecommerce

# 2. Installer les dépendances
composer install

# 3. Configurer les variables d'environnement locales
cp .env .env.local
# Éditer .env.local et renseigner :
#   DATABASE_URL, STRIPE_PUBLIC_KEY, STRIPE_SECRET_KEY
```

---

## Configuration

Créer un fichier `.env.local` à la racine avec :

```dotenv
DATABASE_URL="mysql://root:motdepasse@127.0.0.1:3306/stubborn?serverVersion=8.4.7&charset=utf8mb4"
STRIPE_PUBLIC_KEY=pk_test_...
STRIPE_SECRET_KEY=sk_test_...
```

---

## Base de données

```bash
# Créer la base de données
php bin/console doctrine:database:create

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Charger les données de démonstration (10 produits + comptes de test)
php bin/console doctrine:fixtures:load
```

**Comptes créés par les fixtures :**

| Rôle | Email | Mot de passe |
|------|-------|-------------|
| Admin | admin@stubborn.com | Admin1234! |
| Client | john@example.com | Client1234! |

---

## Lancer le serveur

```bash
symfony server:start -d
# → http://127.0.0.1:8000
```

---

## Architecture

### Entités

| Entité | Description |
|--------|-------------|
| `User` | Utilisateur (client ou admin). Champs : name, email, password, delivery_address, roles |
| `Product` | Sweat-shirt. Champs : name, price, image, featured, stock par taille (XS→XL) |
| `CartItem` | Article dans le panier. Lié à User et Product, avec size et quantity |
| `Order` | Commande. Lié à User, avec total, status (pending/paid/cancelled), stripeSessionId |

### Routes

| Route | Accès | Description |
|-------|-------|-------------|
| `GET /` | Public | Page d'accueil avec produits mis en avant |
| `GET/POST /login` | Public | Formulaire de connexion |
| `GET/POST /register` | Public | Formulaire d'inscription + email de confirmation |
| `GET /logout` | Connecté | Déconnexion |
| `GET /products` | Public | Liste des sweat-shirts avec filtres prix |
| `GET /product/{id}` | ROLE_USER | Fiche produit + ajout au panier |
| `POST /product/{id}/add-to-cart` | ROLE_USER | Ajouter au panier |
| `GET /cart` | ROLE_USER | Page panier |
| `POST /cart/remove/{id}` | ROLE_USER | Retirer un article |
| `GET /payment/checkout` | ROLE_USER | Initier le paiement Stripe |
| `GET /payment/success` | ROLE_USER | Confirmation de paiement |
| `GET /payment/cancel` | ROLE_USER | Annulation de paiement |
| `GET /admin` | ROLE_ADMIN | Back-office (CRUD produits) |
| `POST /admin/edit/{id}` | ROLE_ADMIN | Modifier un produit |
| `POST /admin/delete/{id}` | ROLE_ADMIN | Supprimer un produit |

### Services

- **`StripeService`** : crée une session Checkout Stripe à partir des articles du panier.

---

## Tests

```bash
# Lancer tous les tests
php bin/phpunit --testdox
```

**Résultats attendus :**

- `CartTest` : 7 tests (ajout au panier, sous-total, total, suppression, panier vide, tailles)
- `StripeServiceTest` : 2 tests (création session, unicité ID) — nécessite `.env.test.local` avec les clés Stripe

> Les tests Stripe utilisent le **mode sandbox** de Stripe. Aucun vrai paiement n'est effectué.

---

## Paiement test (Stripe sandbox)

1. Ajouter des articles au panier
2. Cliquer **"Finaliser ma commande"**
3. Sur la page Stripe, utiliser la carte de test :
   - Numéro : `4242 4242 4242 4242`
   - Date : n'importe quelle date future
   - CVC : n'importe quel code à 3 chiffres
4. Le paiement est validé, le panier est vidé, une commande est créée en base

---

## Wireframes

Les wireframes de référence se trouvent dans `Ressources/wireframes/` :

- `page_accueil_user_non_connecte.png`
- `page_accueil_user_connecte.png`
- `page_connexion.png`
- `page_inscription.png`
- `page_produits.png`
- `page_produit.png`
- `page_panier.png`
- `page_back_office.png`
