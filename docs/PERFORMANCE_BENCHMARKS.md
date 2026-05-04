# Performance and Benchmarking

## Scope

This document defines benchmark procedure, baseline expectations, and optimization checklist for the Dynamic Quiz System.

## 1. Test Environment

- Docker Compose stack: Nginx + PHP-FPM + MySQL 8
- Local URL: `http://localhost:8000`
- API base: `http://localhost:8000/api/v1`

## 2. Benchmark Targets

These targets are practical acceptance criteria for local and small staging environments:

- Health endpoint p95 < 100 ms
- Quiz listing endpoint p95 < 250 ms
- Quiz detail endpoint p95 < 350 ms
- Attempt submission endpoint p95 < 500 ms
- Error rate < 1%

## 3. Quick Latency Check with curl

```bash
curl -s -o /dev/null -w "health: %{time_total}s\n" http://localhost:8000/api/health
curl -s -o /dev/null -w "quizzes: %{time_total}s\n" http://localhost:8000/api/v1/quizzes
```

## 4. Load Test Example with k6

Install k6, then run:

```javascript
import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
  vus: 20,
  duration: '30s',
};

export default function () {
  const health = http.get('http://localhost:8000/api/health');
  check(health, { 'health 200': (r) => r.status === 200 });

  const quizzes = http.get('http://localhost:8000/api/v1/quizzes');
  check(quizzes, { 'quizzes 200': (r) => r.status === 200 });

  sleep(1);
}
```

## 5. Application-Level Profiling Checklist

- Enable Laravel query logging only in debug sessions.
- Watch for N+1 query patterns in quiz detail and attempt detail endpoints.
- Validate indexes on foreign keys (`quiz_id`, `question_id`, `attempt_id`).

## 6. Test Execution Baseline

Primary correctness baseline:

```bash
docker-compose exec -T php php artisan test
```

Performance sanity checks before release:

1. Health endpoint under target latency.
2. Quiz list and detail under target latency.
3. Attempt submission remains stable under moderate concurrent load.

## 7. Optimization Playbook

1. Use eager loading for nested relations in high-traffic endpoints.
2. Cache low-churn lookup responses where appropriate.
3. Keep payloads compact in list endpoints.
4. Use pagination with bounded `per_page`.
5. Keep DB statistics and indexes healthy.

## 8. Capacity Planning Heuristics

Start from observed p95 latency and CPU utilization under representative load.

- If p95 latency increases with low DB CPU, scale PHP workers.
- If DB CPU saturates, optimize queries and indexes before scaling DB.
- If network overhead dominates, reduce response payload and compress responses.

## 9. Release Gate for Performance

A release is performance-ready when:

- Core endpoints meet latency targets.
- Error rate is stable and below threshold.
- No sustained resource saturation in php/mysql containers.
- Integration and unit test suites pass.
