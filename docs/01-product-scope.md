# Perimetre produit

## Objectif

UpChecker est une application Laravel / Livewire a usage personnel ou mono-utilisateur. Elle surveille des URLs HTTP publiques, conserve l'historique des verifications pendant un mois et alerte le proprietaire par email lorsqu'une URL passe d'un etat disponible a un etat indisponible.

L'application doit rester volontairement simple en V1. Les notions d'equipe, de roles, d'organisation, d'invitation et de collaboration sont hors perimetre.

## Decisions actees

- L'application est mono-tenant et pensee pour un usage solo.
- Un utilisateur possede ses projets.
- Un projet possede une ou plusieurs URLs surveillees.
- Les entites metier utilisent des UUID v7.
- La base de donnees cible est SQLite, y compris en production.
- L'interface est en francais.
- L'inscription doit etre desactivable via configuration.
- L'authentification utilise Fortify avec email et mot de passe.
- Livewire 4 et Flux UI sont utilises pour l'interface applicative.

## Fonctionnalites V1

- Gerer ses projets.
- Gerer les URLs surveillees d'un projet.
- Definir une regle de succes simple pour chaque URL.
- Choisir un intervalle de verification parmi 1, 2, 5, 15, 30 ou 60 minutes.
- Executer les verifications via le scheduler Laravel et des jobs en queue.
- Enregistrer chaque resultat de verification pendant un mois.
- Envoyer un email au proprietaire lors du passage d'un etat OK vers un etat d'erreur.
- Ne pas repeter l'alerte tant que le monitor n'est pas revenu OK entre temps.

## Hors perimetre V1

- Equipes, roles et partage de projets.
- Webhooks, notifications mobiles, SMS ou Slack.
- Headers HTTP personnalises, authentification HTTP, bearer token, payload POST.
- Methodes autres que GET.
- Surveillance TCP, DNS, ping ICMP ou certificats SSL dedies.
- JSONPath complet.
- Fenetres de maintenance, snooze et escalade d'alertes.
- Limites commerciales par utilisateur ou par projet.

## Principes produit

- Le statut doit etre comprehensible en un coup d'oeil.
- La configuration d'un monitor doit rester rapide.
- Les alertes doivent etre rares et utiles.
- L'historique doit permettre de comprendre ce qui s'est passe sans stocker indefiniment les donnees.
- Les choix techniques doivent favoriser la maintenabilite plutot qu'une architecture distribuee prematuree.
