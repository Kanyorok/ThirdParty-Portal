# Baseline migrations (0_baseline)

This folder contains a one-time consolidated baseline of your current database schema, generated on 2025-11-03 using kitloong/laravel-migrations-generator.

How it works
- Fresh installs: `php artisan migrate` will apply these baseline migrations first, then any new migrations you add later.
- Existing environments: Do NOT re-run `migrate:fresh` unless you intend to rebuild the database from scratch.

Workflow
- Keep these baseline files immutable. For schema changes, create new standard migrations outside `0_baseline/`.
- Old, fragmented migrations have been moved to `_archive/` for history reference. They are not executed.

Regenerate (optional)
- If the live schema changes drastically and you want a new baseline, regenerate into a new timestamped folder, archive the old baseline, and test on a fresh DB.

Troubleshooting
- `php artisan migrate:status` shows nothing for baseline files because they are new and not yet executed. On a fresh DB, they will become recorded after running `php artisan migrate`.
- If you need to seed, use `php artisan db:seed` or include seeder calls in DatabaseSeeder.
