# Changelog

All notable changes to `sla-timer` will be documented in this file

## v0.2.0 - 2026-08-15

## Bug fixes

- Fixed SLA durations dropping the final day's time when the subject ended before the start time-of-day (closes #4 and #6)
- Fixed overnight (midnight-crossing) schedules silently returning zero — `from('22:00:00')->to('02:00:00')` now counts correctly across midnight, and equal from/to times (`09:00:00` -> `09:00:00`) now mean 24/7 coverage

## Performance

- Fixed quadratic runtime for durations over a month with pauses/holidays: a year-long SLA with a holiday on every day previously exhausted 128MB of memory and now calculates in ~30ms; 10-year calculations run in ~90ms
- The hot path no longer constructs `CarbonPeriod` objects per period — `AgendaInterface::toPeriods()` now returns start/end Carbon pairs instead of `CarbonPeriod[]` (**breaking change** for direct consumers)

## Dependencies

- PHP floor raised to `^8.3` (8.1/8.2 are EOL)
- `nesbot/carbon` `^2` → `^3`, `illuminate/collections` → `^11|^12|^13`
- Dev tooling: Pest `^4`, PHPStan `^2`, Pint `^1.30`, spatie/invade `^2`, spatie/pest-plugin-test-time `^2.2`
- npm: vitepress 1.6.4, tailwindcss 4.3.3, vue 3.5.41

## Housekeeping

- Added `phpstan.neon.dist` and fixed all level-8 findings (50 errors, incl. a latent `Weekly::addTimePeriods` flattening bug and dead code)
- CI modernised to first-party actions; test matrix now covers PHP 8.3–8.5
- 28 new tests covering schedules, boundaries, overnight windows, pauses/holidays and breaches (98.4% coverage)
## v0.1.7 - 2025-07-26

### What's Changed

* Bump vite from 2.9.14 to 2.9.16 by @dependabot[bot] in https://github.com/sifex/sla-timer/pull/3
* Bump postcss from 8.4.14 to 8.4.31 by @dependabot[bot] in https://github.com/sifex/sla-timer/pull/5
* Bump vite from 2.9.16 to 2.9.17 by @dependabot[bot] in https://github.com/sifex/sla-timer/pull/7
* Bump vite from 2.9.17 to 2.9.18 by @dependabot[bot] in https://github.com/sifex/sla-timer/pull/8
* Bump braces from 3.0.2 to 3.0.3 by @dependabot[bot] in https://github.com/sifex/sla-timer/pull/9
* Bump micromatch from 4.0.5 to 4.0.8 by @dependabot[bot] in https://github.com/sifex/sla-timer/pull/10
* Bump nanoid from 3.3.6 to 3.3.8 by @dependabot[bot] in https://github.com/sifex/sla-timer/pull/13

### New Contributors

* @dependabot[bot] made their first contribution in https://github.com/sifex/sla-timer/pull/3

**Full Changelog**: https://github.com/sifex/sla-timer/compare/v0.1.6.5...v0.1.7

## v0.0.2-beta

- Imported `spatie/period` to help with time overlap problems
- Published documentation to GH Pages

## v0.0.1-beta

- Initial dev release
