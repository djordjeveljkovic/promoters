# Deprecated files

This directory contains files from earlier iterations of the project
that are kept around **for reference only**. None of them are autoloaded,
none are referenced by routes or other code.

If you're cleaning up, these can be removed in a single commit. They are
**not** part of the running app.

| File | Reason it's here |
| ---- | ---------------- |
| `OrderController1.php` | Identical to `app/Http/Controllers/OrderController.php` from before the multi-festival refactor. No route references it. The file's namespace (`App\Http\Controllers\_deprecated`) is intentionally not under the PSR-4 autoload path so it can't shadow the real controller. |