# ADR-003: Domain Event Bus Architecture

**Status:** Accepted  
**Date:** June 2025  
**Required before:** Milestone 4 (Trading Engine)

---

## Context

The architecture mandates that modules communicate via domain events, not direct service calls. The event bus implementation determines how events are delivered, retried, and ordered.

## Decision

Use **Laravel Events + Database Queue** for the V1 event bus.

**Queue driver:** `database` (V1) → `redis` (100K users).  
**Listener contract:** All listeners implement `ShouldQueue`.  
**Idempotency key schema:** `{event_class}:{source_id}:{user_id}` stored as a cache key with 24h TTL.

## Queue Partitioning Strategy

**V1 (database queue):**
- Single queue: all listeners share `default` queue.
- Queue worker triggered by Hostinger cron: `php artisan queue:work --max-jobs=100 --stop-when-empty`.

**100K users (Redis queues):**
- `trades` queue: Portfolio listener only — highest priority.
- `game` queue: XP, Level, League, Reward engines.
- `notifications` queue: All notification listeners.
- `analytics` queue: Analytics and anti-cheat listeners.

**1M users (Kafka/SQS):**
- Each bounded context gets its own topic.
- User ID used as partition key to guarantee per-user event ordering.

## Idempotency Contract

Every listener that mutates state must:
1. Receive a unique `source_id` in the event payload.
2. Check `idempotency_key = "{EventClass}:{source_id}"` in cache/DB before processing.
3. Store the key atomically with the mutation (single transaction).
4. Return early (no error) if the key already exists.

## Dead Letter Queue (DLQ)

Failed listeners after 3 retries are moved to the `failed_jobs` table.  
Admin is alerted via email on failed job accumulation.  
Recovery: `php artisan queue:retry all` after root cause is fixed.

## Consequences

- Async event processing means XP/coin updates arrive ~1–2s after trade confirmation.
- Flutter uses optimistic UI for XP bar updates; polls `/api/v1/game/state` for accuracy.
- Event ordering within a user is eventual, not strict, in V1.
