# Modele de domaine

## Vue d'ensemble

Le domaine est centre sur quatre entites principales :

- `User` : proprietaire unique des projets et destinataire des alertes.
- `Project` : regroupement logique de monitors.
- `Monitor` : URL HTTP publique a verifier selon une regle de succes.
- `CheckResult` : resultat historise d'une verification.

Une entite secondaire, `AlertDelivery`, trace les alertes envoyees.

## Identifiants

Toutes les entites metier doivent utiliser des UUID v7 :

- `users.id`
- `projects.id`
- `monitors.id`
- `check_results.id`
- `alert_deliveries.id`

Les relations utilisent donc des foreign keys UUID. Les modeles Laravel doivent exposer des relations Eloquent classiques et des casts explicites pour les enums et les dates.

## Tables proposees

### users

Table Fortify standard adaptee aux UUID.

Champs notables :

- `id`
- `name`
- `email`
- `password`
- `email_verified_at`
- `remember_token`
- timestamps

### projects

Un projet appartient a un utilisateur.

Champs proposes :

- `id`
- `user_id`
- `name`
- `description`, nullable
- timestamps

Index :

- `user_id`
- `user_id, name`

### monitors

Un monitor represente une URL HTTP GET publique a verifier.

Champs proposes :

- `id`
- `project_id`
- `name`
- `url`
- `enabled`, defaut `true`
- `interval_minutes`, valeurs autorisees : `1`, `5`, `15`, `30`, `60`
- `timeout_seconds`, defaut `10`
- `expected_http_status`, defaut `200`
- `expected_json_key`, nullable
- `expected_json_value`, nullable
- `expected_body_contains`, nullable
- `max_response_time_ms`, nullable
- `current_status`, enum : `unknown`, `up`, `down`, `timeout`, `invalid`
- `last_checked_at`, nullable
- `last_success_at`, nullable
- `last_failure_at`, nullable
- `last_alerted_at`, nullable
- `next_check_at`, nullable
- timestamps

Index :

- `project_id`
- `enabled, next_check_at`
- `current_status`

### check_results

Un resultat est cree a chaque verification. Les donnees sont conservees un mois.

Champs proposes :

- `id`
- `monitor_id`
- `status`, enum : `up`, `down`, `timeout`, `invalid`
- `http_status`, nullable
- `response_time_ms`, nullable
- `error_message`, nullable
- `checked_url`
- `checked_at`
- `response_excerpt`, nullable
- timestamps

Le champ `response_excerpt` permet de conserver un extrait utile du body ou de l'erreur sans faire grossir indefiniment SQLite. Si l'on decide finalement de stocker le body complet, il faudra fixer une taille maximale applicative.

Index :

- `monitor_id, checked_at`
- `checked_at`
- `status`

### alert_deliveries

Trace des alertes envoyees.

Champs proposes :

- `id`
- `monitor_id`
- `check_result_id`
- `channel`, enum V1 : `mail`
- `recipient`
- `status`, enum : `pending`, `sent`, `failed`
- `sent_at`, nullable
- `error_message`, nullable
- timestamps

Index :

- `monitor_id, created_at`
- `check_result_id`

## Enums proposes

- `MonitorStatus` : `Unknown`, `Up`, `Down`, `Timeout`, `Invalid`
- `CheckStatus` : `Up`, `Down`, `Timeout`, `Invalid`
- `AlertChannel` : `Mail`
- `AlertDeliveryStatus` : `Pending`, `Sent`, `Failed`

## Relations Eloquent

- `User hasMany Project`
- `Project belongsTo User`
- `Project hasMany Monitor`
- `Monitor belongsTo Project`
- `Monitor hasMany CheckResult`
- `Monitor hasMany AlertDelivery`
- `CheckResult belongsTo Monitor`
- `AlertDelivery belongsTo Monitor`
- `AlertDelivery belongsTo CheckResult`

## Politique de retention

Un job planifie supprime les `check_results` plus anciens qu'un mois. Les `alert_deliveries` peuvent etre conservees plus longtemps si elles restent peu volumineuses, mais la V1 peut appliquer la meme retention pour garder le modele simple.
