# Paper Trading Tycoon - Project Rules

## Project Overview

Paper Trading Tycoon is a gamified stock market simulator where users trade stocks using virtual money while progressing through levels, leagues, achievements, challenges, and leaderboards.

## Technology Stack

### Frontend
- **Framework**: Flutter (Latest Stable)
- **State Management**: Riverpod
- **Routing**: Go Router
- **HTTP Client**: Dio
- **Local Storage**: Hive

### Backend
- **Framework**: Laravel 12
- **API**: REST API
- **Authentication**: Sanctum
- **Database**: MySQL

### Infrastructure
- **Hosting**: Hostinger Shared Hosting
- **Database**: MySQL

## Architecture Rules

### Clean Architecture Principles
1. Follow Clean Architecture principles strictly
2. Separate layers:
   - **Presentation** (UI, widgets, state management)
   - **Domain** (entities, repositories interfaces, use cases)
   - **Data** (repository implementations, data sources, models)
3. Never place business logic inside UI screens
4. Use Repository Pattern for data access abstraction
5. Use Service Layer for API calls
6. Use Riverpod for state management
7. Use reusable widgets
7. Generate scalable, production-ready code only
8. Never generate temporary demo code
9. Never generate mock data unless specifically requested
10. Every generated file must contain comments explaining its purpose
11. Every API endpoint must include:
    - Validation
    - Error handling
    - Success response structure
12. Every database table must include:
    - id (primary key)
    - created_at
    - updated_at
13. Use snake_case for database fields
14. Use camelCase for Dart variables
15. Use PascalCase for classes
16. Always generate production-ready code
17. Never generate duplicate models
18. Never generate duplicate services
19. Before generating code, explain:
    - Files being created
    - Purpose of each file
    - Dependencies required

## Folder Structure Rules

### Flutter (lib/)
```
lib/
├── core/           # Core functionality (constants, utils, errors, themes)
├── features/       # Feature modules (each feature self-contained)
├── shared/         # Shared components (widgets, models, extensions)
├── routes/         # Routing configuration
├── services/       # Application services (DI, API client, storage)
```

### Laravel (app/)
```
app/
├── Http/           # Controllers, Requests, Resources, Middleware
├── Models/         # Eloquent Models
├── Services/       # Business logic services
├── Repositories/   # Repository implementations
├── Actions/        # Action classes (single responsibility)
```

## Development Method

1. Generate code module by module
2. Never generate more than one module unless requested
3. Wait for confirmation before moving to the next module

## Current Project Status

- Project initialized
- No code exists yet
- Future prompts will reference this file