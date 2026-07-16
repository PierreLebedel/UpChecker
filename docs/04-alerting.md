# Alertes

## Objectif

Envoyer une alerte email utile et non repetitive au proprietaire lorsqu'un monitor precedemment OK devient indisponible.

## Canal V1

Le seul canal implemente en V1 est l'email Laravel Notification.

Les futurs canaux possibles, hors V1 :

- webhook
- notification mobile
- Slack
- SMS

Le modele `AlertDelivery` garde un champ `channel` pour ne pas bloquer ces evolutions.

## Destinataire

Le destinataire est toujours le proprietaire du projet :

`Monitor -> Project -> User`

Pas de liste d'adresses par monitor en V1.

## Regle de declenchement

Une alerte est envoyee uniquement lors de la transition :

- ancien statut : `up`
- nouveau statut : `down`, `timeout` ou `invalid`

Aucune alerte n'est envoyee :

- si le monitor etait deja en erreur
- lors du retour a `up`
- lors d'une premiere verification depuis `unknown`

Cette regle garantit qu'un serveur en panne ne relance pas d'email a chaque verification.

## Contenu de l'email

L'email doit contenir :

- nom du projet
- nom du monitor
- URL verifiee
- nouveau statut
- code HTTP si disponible
- temps de reponse si disponible
- erreur ou raison de l'echec
- date et heure de verification
- lien vers le detail du monitor

Le message doit etre court, en francais, et actionnable.

## Queue

Les notifications email doivent etre queuees. Elles ne doivent pas bloquer le job de verification plus que necessaire.

La notification doit etre compatible avec les tests Laravel :

- `Notification::fake()`
- assertions sur le destinataire
- assertions sur le contenu cle

## Traces d'envoi

`AlertDelivery` sert a garder une trace des emails envoyes ou echoues.

Flux propose :

1. Creer une ligne `AlertDelivery` en `pending`.
2. Envoyer la notification.
3. Marquer `sent` lorsque l'envoi est accepte.
4. Marquer `failed` en cas d'erreur connue.

Si ce suivi s'avere trop lourd en V1, on peut commencer avec une creation apres envoi reussi seulement. Le plan recommande toutefois de garder la table des le depart, car elle facilitera les futurs canaux.

## Pas de recovery alert en V1

Le retour a la normale met a jour le statut et l'historique, mais n'envoie pas d'email. Cette decision pourra etre revue apres usage reel.
