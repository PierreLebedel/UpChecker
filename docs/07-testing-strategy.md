# Strategie de tests

## Objectif

Chaque changement fonctionnel doit etre couvert par des tests Pest. L'application surveille des services externes, donc les tests ne doivent jamais faire de vraies requetes HTTP.

## Principes

- Utiliser Pest 4.
- Preferer les tests unitaires pour le moteur de verification.
- Utiliser les tests feature pour Livewire, auth, scheduler et notifications.
- Utiliser `Http::fake()` et `Http::preventStrayRequests()`.
- Utiliser `Notification::fake()` pour les alertes.
- Utiliser les factories pour `User`, `Project`, `Monitor` et `CheckResult`.
- Tester les transitions d'etat, pas seulement les statuts finaux.

## Tests unitaires prioritaires

### Evaluation de reponse

Cas a couvrir :

- status HTTP attendu
- status HTTP inattendu
- JSON simple valide
- JSON simple absent
- JSON invalide
- body contains present
- body contains absent
- temps de reponse sous le maximum
- temps de reponse au-dessus du maximum

### Transitions

Cas a couvrir :

- `unknown -> up`
- `unknown -> down`
- `up -> down`
- `up -> timeout`
- `up -> invalid`
- `down -> down`
- `down -> up`
- `timeout -> up`
- `invalid -> up`

### Planification

Cas a couvrir :

- intervalle 1 minute
- intervalle 5 minutes
- intervalle 15 minutes
- intervalle 30 minutes
- intervalle 60 minutes
- monitor desactive ignore

## Tests feature prioritaires

### Commande de dispatch

Verifier que la commande :

- dispatch les monitors dus
- ignore les monitors non dus
- ignore les monitors desactives
- ne casse pas si aucun monitor n'est du

### Job de verification

Verifier que le job :

- cree un `CheckResult`
- met a jour `current_status`
- met a jour `last_checked_at`
- met a jour `last_success_at` ou `last_failure_at`
- calcule `next_check_at`
- respecte le timeout configure

### Alertes

Verifier que :

- une notification est envoyee sur `up -> erreur`
- aucune notification n'est envoyee sur erreur persistante
- aucune notification n'est envoyee sur recovery
- `AlertDelivery` est cree correctement

### Livewire

Verifier que :

- l'utilisateur doit etre connecte
- un projet peut etre cree, modifie et supprime
- un monitor peut etre cree, modifie, active/desactive et supprime
- les validations affichent les erreurs attendues
- un utilisateur ne peut pas voir ou modifier les donnees d'un autre utilisateur

## Donnees de test

Factories recommandees :

- `UserFactory`
- `ProjectFactory`
- `MonitorFactory`
- `CheckResultFactory`
- `AlertDeliveryFactory`

States utiles :

- `MonitorFactory::enabled()`
- `MonitorFactory::disabled()`
- `MonitorFactory::due()`
- `MonitorFactory::notDue()`
- `MonitorFactory::up()`
- `MonitorFactory::down()`

## Verification manuelle minimale

Avant une utilisation reelle :

- lancer le scheduler localement
- lancer un worker de queue
- creer un monitor vers une URL OK
- creer un monitor vers une URL invalide
- verifier la reception d'un email en environnement local
- verifier la suppression des resultats anciens avec une base de test

## Commandes attendues

Commandes usuelles pendant le developpement :

```bash
php artisan test --compact
vendor/bin/pint --dirty --format agent
```

Pour les tests cibles, preferer :

```bash
php artisan test --compact tests/Unit/...
php artisan test --compact tests/Feature/...
```
