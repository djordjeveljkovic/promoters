# Deprecated middleware

This directory contains middleware classes that were replaced by newer
implementations. They are kept around **for reference only** and are
**not registered** in `bootstrap/app.php`.

| File | Replaced by | Notes |
| ---- | ----------- | ----- |
| `SetLocaleMiddleware.php` | `SetLocale.php` (one level up) | The new version supports `?lang=` query-string override, `Accept-Language` header negotiation, and a more permissive fallback chain. The old one only checked `Session::get('locale')`. |

If you ever need to remove these files, do so in a single commit — they
have no live references.