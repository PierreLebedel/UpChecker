# Plan interface Livewire

## Principes

L'interface est une application de monitoring, pas une landing page. Elle doit etre dense, lisible et rapide a utiliser.

Choix V1 :

- Livewire 4 pour les pages et composants interactifs.
- Flux UI pour les formulaires, boutons, badges, modales et tables.
- Interface en francais.
- Navigation simple : dashboard, projets, monitors.
- Polling Livewire leger sur les zones de statut si utile.

## Pages proposees

### Dashboard

Objectif : voir l'etat global immediatement.

Contenu :

- nombre de monitors OK
- nombre de monitors en erreur
- nombre de monitors jamais verifies
- derniers incidents
- monitors les plus recents en erreur
- lien rapide vers la creation d'un projet ou monitor

### Liste des projets

Contenu :

- nom du projet
- nombre de monitors
- statut agrege
- derniere verification
- actions : voir, modifier, supprimer

### Detail projet

Contenu :

- informations du projet
- liste des monitors du projet
- statut courant par monitor
- intervalle
- derniere verification
- prochaine verification
- actions : activer/desactiver, modifier, lancer une verification manuelle

### Formulaire projet

Champs :

- nom
- description optionnelle

Validation Livewire cote serveur.

### Formulaire monitor

Champs :

- nom
- URL
- actif/inactif
- intervalle : `1`, `5`, `15`, `30`, `60`
- timeout en secondes, defaut `10`
- status HTTP attendu, defaut `200`
- cle JSON attendue optionnelle
- valeur JSON attendue optionnelle
- texte attendu optionnel
- temps de reponse maximal optionnel

Le formulaire doit expliquer peu, mais les labels doivent etre explicites.

### Detail monitor

Contenu :

- statut courant
- URL
- regle de succes
- derniere verification
- dernier succes
- dernier echec
- historique recent
- temps de reponse recent
- dernieres erreurs

Graphiques simples possibles apres la V1 initiale.

## Composants Livewire

Composants candidats :

- `Pages\Dashboard`
- `Pages\Projects\Index`
- `Pages\Projects\Show`
- `Pages\Projects\Form`
- `Pages\Monitors\Form`
- `Pages\Monitors\Show`

Avant implementation, verifier la configuration Livewire 4 du projet pour respecter le format choisi par `make:livewire`.

## Etats visuels

Badges proposes :

- `OK` pour `up`
- `Indisponible` pour `down`
- `Timeout` pour `timeout`
- `Reponse invalide` pour `invalid`
- `Jamais verifie` pour `unknown`

Les couleurs doivent rester sobres et accessibles :

- vert pour OK
- rouge pour down
- orange pour timeout
- violet ou gris pour invalid
- gris pour unknown

## Interactions

Actions V1 :

- creer / modifier / supprimer un projet
- creer / modifier / supprimer un monitor
- activer / desactiver un monitor
- lancer une verification manuelle

La verification manuelle doit dispatcher le meme job que le scheduler pour eviter deux chemins metier differents.

## Autorisation

Meme en usage solo, les actions Livewire doivent verifier que le projet ou monitor appartient a l'utilisateur connecte. Les policies ou checks explicites evitent de creer des failles en cas d'ouverture future.
