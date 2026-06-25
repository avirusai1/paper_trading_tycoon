# ADR-001: Technology Stack Selection

**Status:** Accepted  
**Date:** June 2025  
**Deciders:** Technical Lead

---

## Context

Paper Trading Tycoon requires a mobile-first application for the Indian market with a server-authoritative backend. The stack must enable rapid MVP development while providing a credible path to 1M+ users.

## Decisions

### Frontend: Flutter

**Decision:** Flutter (latest stable) with Riverpod state management.

**Rationale:**
- Single codebase for iOS and Android reduces 2-person team overhead.
- Riverpod mandated in `project_rules.md` — excellent testability and composability.
- Go Router for declarative navigation with deep link support.
- Dio for HTTP — mature, interceptor-friendly, widely supported.
- Hive for local storage — fast, typed, Dart-native.

**Alternatives rejected:**
- React Native: Less smooth for finance/game-hybrid UIs.
- Native iOS + Android: 2× development cost for 2-person team.

### Backend: Laravel 12

**Decision:** Laravel 12 monolith with Sanctum API authentication.

**Rationale:**
- Hostinger shared hosting supports PHP 8.3 — no additional infrastructure.
- Laravel's event system maps directly to the domain event architecture.
- Eloquent ORM and built-in queue system reduce boilerplate.
- Sanctum is purpose-built for SPA/mobile token auth.
- PHPStan + Pint enforce production code quality.

**Alternatives rejected:**
- Node.js/Express: Less mature queue system; team PHP experience.
- Go microservices: Premature for V1 scale; significant boilerplate overhead.

### Database: MySQL

**Decision:** MySQL 8.0.

**Rationale:**
- Hostinger default; no additional cost for V1.
- Sufficient for 10K–100K users with proper indexing.
- Coin ledger append-only pattern works well on MySQL.

**Migration path:** Managed MySQL (PlanetScale/RDS) at 100K users.

### Infrastructure: Hostinger Shared Hosting

**Decision:** V1 on Hostinger shared PHP hosting.

**Rationale:**
- Zero additional monthly cost during bootstrapping phase.
- Acceptable for < 50K users with optimized queries and aggressive caching.

**Migration trigger:** VPS migration planned before 50K users per Section 8 risk register.

## Consequences

- Architecture must be cloud-agnostic from day 1 (no Hostinger-specific APIs).
- Queue workers limited to cron-triggered execution on shared hosting.
- Redis unavailable in V1 — file cache and database queue.
- PHPStan level 8 enforced — all code must be strictly typed.
