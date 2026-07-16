# Architecture de verification

## Objectif

Verifier regulierement les monitors actifs avec des jobs en queue, sans bloquer le scheduler Laravel. L'execution est "au mieux" : une verification peut avoir quelques secondes ou minutes de retard selon la charge, tant que les intervalles restent globalement respectes.

## Flux principal

1. Le scheduler execute une commande toutes les minutes.
2. La commande selectionne les monitors actifs dont `next_check_at` est passe.
3. Elle dispatch un job `CheckMonitorJob` par monitor.
4. Chaque job effectue un GET HTTP avec redirections suivies et timeout configure.
5. Le job evalue la reponse avec la regle du monitor.
6. Le job cree un `CheckResult`.
7. Le job met a jour l'etat courant du monitor.
8. Si l'etat passe de `up` a une erreur, une notification mail est queuee.
9. Le job calcule le prochain `next_check_at`.

## Scheduler

Plan V1 :

- `Schedule::command('monitors:dispatch-due-checks')->everyMinute()->withoutOverlapping()`
- En serveur unique, `onOneServer()` n'est pas necessaire en V1.
- La commande ne fait pas les appels HTTP elle-meme.
- La commande doit rester rapide et ne faire que selectionner puis dispatcher.

Laravel 13 permet aussi de planifier directement des jobs, mais une commande dediee est preferable ici car elle doit selectionner dynamiquement les monitors dus.

## Commande `monitors:dispatch-due-checks`

Responsabilites :

- Recuperer les monitors `enabled = true`.
- Filtrer `next_check_at <= now()` ou `next_check_at is null`.
- Dispatch un `CheckMonitorJob`.
- Eviter de dispatcher plusieurs fois le meme monitor si une verification est deja en cours.

Approche recommandee :

- Utiliser un verrou applicatif par monitor au niveau du job.
- Traiter les monitors par chunks pour garder une memoire stable.
- Trier par `next_check_at`.

## Job `CheckMonitorJob`

Responsabilites :

- Charger le monitor et son projet.
- Ignorer le monitor s'il a ete desactive entre temps.
- Acquerir un lock court par monitor.
- Effectuer l'appel HTTP.
- Evaluer la reponse.
- Persister le resultat.
- Mettre a jour l'etat courant.
- Declencher l'alerte si necessaire.
- Planifier la prochaine verification.

Parametres proposes :

- `tries = 1` pour eviter qu'un retry de queue ne cree une alerte tardive confuse.
- `timeout` legerement superieur a `timeout_seconds`.
- Queue dediee possible : `checks`.

## Client HTTP

Regles V1 :

- Methode unique : `GET`.
- URLs publiques HTTP ou HTTPS.
- Redirections suivies.
- Timeout configurable par monitor, defaut `10` secondes.
- Pas de headers personnalises.
- Pas d'authentification.
- Pas de payload.

Le client HTTP Laravel doit definir explicitement `timeout()` et `connectTimeout()`. Les tests doivent utiliser `Http::fake()` et `Http::preventStrayRequests()`.

## Evaluation de succes

Une verification est `up` si toutes les conditions configurees sont satisfaites.

Conditions V1 :

- Status HTTP attendu, par defaut `200`.
- Cle JSON simple optionnelle, par exemple `status`.
- Valeur JSON attendue optionnelle, par exemple `ok`.
- Texte optionnel present dans le body.
- Temps de reponse maximal optionnel.

La verification JSON reste simple : pas de JSONPath complet en V1. Une notation par cle simple ou par chemin a points peut etre prevue, par exemple `status` ou `data.status`, mais sans filtres ni expressions.

## Etats

- `unknown` : jamais verifie.
- `up` : toutes les conditions sont satisfaites.
- `down` : reponse HTTP recue mais condition non satisfaite.
- `timeout` : timeout reseau ou connexion trop lente.
- `invalid` : reponse inexploitable pour la regle configuree, par exemple JSON attendu mais body non JSON.

## Transition d'etat

La transition importante pour les alertes est :

- ancien etat `up`
- nouvel etat `down`, `timeout` ou `invalid`

Pas d'alerte si le monitor etait deja en erreur. Pas d'alerte de retour a la normale en V1.

Pour un monitor jamais verifie, une premiere verification en erreur ne doit pas envoyer d'alerte sauf decision contraire. Recommandation V1 : ne pas alerter depuis `unknown`, pour eviter un bruit immediat apres configuration.

## Nettoyage

Une commande planifiee supprime quotidiennement les resultats de verification plus anciens qu'un mois :

- `Schedule::command('monitors:prune-check-results')->daily()->withoutOverlapping()`

Elle peut egalement nettoyer les traces d'alertes anciennes si la retention commune est retenue.
