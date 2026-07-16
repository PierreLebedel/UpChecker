# Roadmap de developpement

## Phase 0 - Preparation

Objectif : verrouiller les bases du projet.

Taches :

- Confirmer la configuration Fortify.
- Rendre l'inscription desactivable par config/env.
- Confirmer la strategie UUID v7 sur `users`.
- Verifier la configuration queue pour SQLite.
- Verifier la configuration mail locale.
- Definir les enums metier.

Tests :

- inscription active/desactivee selon config
- creation utilisateur UUID

## Phase 1 - Modele de donnees

Objectif : creer les tables et relations.

Taches :

- Migration `projects`.
- Migration `monitors`.
- Migration `check_results`.
- Migration `alert_deliveries`.
- Modeles Eloquent et relations.
- Factories pour les tests.

Tests :

- relations utilisateur/projets/monitors
- valeurs par defaut des monitors
- casts des enums et dates

## Phase 2 - Moteur de verification

Objectif : verifier une URL et produire un resultat fiable.

Taches :

- Action `CheckMonitorAction`.
- Evaluation status HTTP.
- Evaluation JSON simple.
- Evaluation body contains.
- Evaluation temps de reponse maximal.
- Gestion des timeouts.
- Creation `CheckResult`.
- Mise a jour du `Monitor`.

Tests :

- reponse HTTP OK
- status HTTP inattendu
- JSON attendu valide
- JSON attendu invalide
- timeout
- body contains
- temps de reponse trop long
- aucune requete externe reelle

## Phase 3 - Scheduler et queue

Objectif : executer automatiquement les monitors dus.

Taches :

- Job `CheckMonitorJob`.
- Commande `monitors:dispatch-due-checks`.
- Calcul `next_check_at`.
- Lock par monitor.
- Commande `monitors:prune-check-results`.
- Planification dans le scheduler.

Tests :

- selection des monitors dus
- monitors desactives ignores
- `next_check_at` mis a jour
- anciens resultats supprimes

## Phase 4 - Alertes email

Objectif : alerter uniquement au bon moment.

Taches :

- Notification email `MonitorDownNotification`.
- Creation `AlertDelivery`.
- Declenchement sur transition `up -> erreur`.
- Pas de repetition tant que le monitor reste en erreur.
- Pas d'alerte de recovery.

Tests :

- alerte envoyee sur `up -> down`
- alerte envoyee sur `up -> timeout`
- alerte envoyee sur `up -> invalid`
- pas d'alerte sur `down -> down`
- pas d'alerte sur `unknown -> down`
- pas d'alerte sur `down -> up`

## Phase 5 - Interface Livewire

Objectif : rendre l'application utilisable.

Taches :

- Dashboard.
- CRUD projets.
- CRUD monitors.
- Detail monitor avec historique.
- Action de verification manuelle.
- Badges de statut.
- Validation et autorisation dans les actions Livewire.

Tests :

- acces authentifie requis
- CRUD projet
- CRUD monitor
- verification manuelle dispatch le job
- un utilisateur ne peut pas acceder aux donnees d'un autre utilisateur

## Phase 6 - Stabilisation

Objectif : durcir avant usage quotidien.

Taches :

- Ameliorer les messages d'erreur.
- Ajouter logs utiles.
- Verifier les indexes SQLite.
- Verifier le comportement queue/scheduler en production.
- Ajouter une commande de diagnostic.
- Relire les emails.

Tests :

- suite feature ciblee
- tests unitaires du moteur de verification
- formatage Pint
- analyse Larastan si configuree

## Evolutions possibles apres V1

- Alerte de retour a la normale.
- Webhooks.
- Fenetres de maintenance.
- Headers HTTP et authentification.
- Methode HEAD ou POST.
- Captures plus riches de l'historique.
- Graphiques de disponibilite.
- Export CSV.
- Multi-utilisateurs ou equipes.
