BEGIN TRANSACTION;
CREATE TABLE IF NOT EXISTS "cache" ("key" varchar not null, "value" text not null, "expiration" integer not null, primary key ("key"));
CREATE TABLE IF NOT EXISTS "cache_locks" ("key" varchar not null, "owner" varchar not null, "expiration" integer not null, primary key ("key"));
CREATE TABLE IF NOT EXISTS "failed_jobs" ("id" integer primary key autoincrement not null, "uuid" varchar not null, "connection" text not null, "queue" text not null, "payload" text not null, "exception" text not null, "failed_at" datetime not null default CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS "job_batches" ("id" varchar not null, "name" varchar not null, "total_jobs" integer not null, "pending_jobs" integer not null, "failed_jobs" integer not null, "failed_job_ids" text not null, "options" text, "cancelled_at" integer, "created_at" integer not null, "finished_at" integer, primary key ("id"));
CREATE TABLE IF NOT EXISTS "jobs" ("id" integer primary key autoincrement not null, "queue" varchar not null, "payload" text not null, "attempts" integer not null, "reserved_at" integer, "available_at" integer not null, "created_at" integer not null);
CREATE TABLE IF NOT EXISTS "migrations" ("id" integer primary key autoincrement not null, "migration" varchar not null, "batch" integer not null);
CREATE TABLE IF NOT EXISTS "password_reset_tokens" ("email" varchar not null, "token" varchar not null, "created_at" datetime, primary key ("email"));
CREATE TABLE IF NOT EXISTS "sessions" ("id" varchar not null, "user_id" integer, "ip_address" varchar, "user_agent" text, "payload" text not null, "last_activity" integer not null, primary key ("id"));
CREATE TABLE IF NOT EXISTS "users" ("id" integer primary key autoincrement not null, "name" varchar not null, "email" varchar not null, "email_verified_at" datetime, "password" varchar not null, "remember_token" varchar, "created_at" datetime, "updated_at" datetime, "is_admin" tinyint(1) not null default '0');
INSERT INTO "migrations" ("id","migration","batch") VALUES (1,'0001_01_01_000000_create_users_table',1),
 (2,'0001_01_01_000001_create_cache_table',1),
 (3,'0001_01_01_000002_create_jobs_table',1),
 (4,'2025_11_27_212330_add_is_admin_to_users_table',2);
INSERT INTO "sessions" ("id","user_id","ip_address","user_agent","payload","last_activity") VALUES ('T5vsXPtgjQqP5wVthUZKirVOYQ53nViVt55vqX4C',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiZ2UwZ2pWZFppUnVROXF5MDdiSFpDclpLOEJidmpaWlI3TmtObUNYNyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9kYXNoYm9hcmQiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9',1765398000);
INSERT INTO "users" ("id","name","email","email_verified_at","password","remember_token","created_at","updated_at","is_admin") VALUES (1,'Edgar Moreno','edgar.moreno@go.com.hn',NULL,'$2y$12$FgbM1NQTd0jrmLEMv6FDT.tvhqkQ7wl1t1gb.aveRA.6swIYn671K',NULL,'2025-11-24 21:47:10','2025-12-10 18:58:56',1);
CREATE UNIQUE INDEX "failed_jobs_uuid_unique" on "failed_jobs" ("uuid");
CREATE INDEX "jobs_queue_index" on "jobs" ("queue");
CREATE INDEX "sessions_last_activity_index" on "sessions" ("last_activity");
CREATE INDEX "sessions_user_id_index" on "sessions" ("user_id");
CREATE UNIQUE INDEX "users_email_unique" on "users" ("email");
COMMIT;
