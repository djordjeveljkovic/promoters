<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Festival;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class FestivalController extends Controller
{
    /** Superadmin landing page — list every festival with quick stats. */
    public function index(Request $request)
    {
        $query = Festival::query()->withCount(['ticketTypes', 'orders', 'tickets']);

        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('slug', 'like', "%{$term}%")
                    ->orWhere('year', 'like', "%{$term}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $festivals = $query->orderByDesc('year')->orderBy('name')->paginate(15)->withQueryString();

        $statusColors = [
            'draft'    => 'bg-yellow-100 text-yellow-800',
            'active'   => 'bg-green-100 text-green-800',
            'archived' => 'bg-gray-100 text-gray-800',
        ];

        return view('pages.superadmin.festivals.index', compact('festivals', 'statusColors'));
    }

    public function create()
    {
        $festival = new Festival([
            'primary_color'   => '#ff2d92',
            'secondary_color' => '#5ce1ff',
            'status'          => 'draft',
            'is_public'       => true,
            'year'            => (int) date('Y'),
        ]);

        return view('pages.superadmin.festivals.create', compact('festival'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        // B-003: the columns are NOT NULL in the migration, so we must NOT
        // overwrite them with an explicit null.  Only assign when the
        // normalised value is a valid colour; otherwise let the column
        // default kick in.
        $primary = Festival::normaliseColor($data['primary_color'] ?? null);
        $secondary = Festival::normaliseColor($data['secondary_color'] ?? null);
        if ($primary)   $data['primary_color']   = $primary;
        else            unset($data['primary_color']);
        if ($secondary) $data['secondary_color'] = $secondary;
        else            unset($data['secondary_color']);

        DB::transaction(function () use ($data, $request) {
            if ($request->hasFile('logo')) {
                $data['logo_path'] = $this->storeLogo($request);
            }
            $data['created_by'] = Auth::id();
            $data['slug'] = Festival::makeUniqueSlug(new Festival($data));
            Festival::create($data);
        });

        return redirect()
            ->route('superadmin.festivals.index')
            ->with('success', __('alert.festival_created'));
    }

    public function edit(Festival $festival)
    {
        return view('pages.superadmin.festivals.edit', compact('festival'));
    }

    public function update(Request $request, Festival $festival)
    {
        $data = $this->validated($request, $festival);
        // B-003: see note in store() — preserve the column default when
        // the admin submits an empty value.
        $primary = Festival::normaliseColor($data['primary_color'] ?? null);
        $secondary = Festival::normaliseColor($data['secondary_color'] ?? null);
        if ($primary)   $data['primary_color']   = $primary;
        else            unset($data['primary_color']);
        if ($secondary) $data['secondary_color'] = $secondary;
        else            unset($data['secondary_color']);

        DB::transaction(function () use ($data, $request, $festival) {
            if ($request->hasFile('logo')) {
                if ($festival->logo_path && Storage::disk('public')->exists($festival->logo_path)) {
                    Storage::disk('public')->delete($festival->logo_path);
                }
                $data['logo_path'] = $this->storeLogo($request);
            }
            $festival->update($data);
        });

        return redirect()
            ->route('superadmin.festivals.index')
            ->with('success', __('alert.festival_updated'));
    }

    public function destroy(Festival $festival)
    {
        // Only allow deletion of draft festivals — protecting real data.
        if ($festival->status !== 'draft') {
            return back()->with('error', __('alert.festival_cannot_delete_active'));
        }

        DB::transaction(function () use ($festival) {
            $festival->delete();
        });

        return redirect()
            ->route('superadmin.festivals.index')
            ->with('success', __('alert.festival_deleted'));
    }

    /**
     * P-022: archive an active festival.  Archived festivals stay in
     * the database (so historical orders and reports keep working) but
     * are hidden from the active festival picker and from public views.
     */
    public function archive(Festival $festival)
    {
        if ($festival->status === 'archived') {
            return back()->with('info', __('alert.festival_already_archived'));
        }
        $festival->update(['status' => 'archived']);
        return back()->with('success', __('alert.festival_archived'));
    }

    /**
     * P-022: restore an archived festival back to active.
     */
    public function restore(Festival $festival)
    {
        if ($festival->status !== 'archived') {
            return back()->with('info', __('alert.festival_not_archived'));
        }
        $festival->update(['status' => 'active']);
        return back()->with('success', __('alert.festival_restored'));
    }

    /**
     * P-024: flip the public-visibility flag (show / hide on the
     * public landing page).  No re-fetch, just an immediate toggle.
     */
    public function togglePublic(Festival $festival)
    {
        $festival->update(['is_public' => !$festival->is_public]);
        return back()->with('success', $festival->is_public
            ? __('alert.festival_made_public')
            : __('alert.festival_made_private'));
    }

    /* ---------------------- Helpers ---------------------- */

    private function validated(Request $request, ?Festival $festival = null): array
    {
        return $request->validate([
            'name'           => ['required', 'string', 'max:120'],
            'year'           => ['required', 'integer', 'min:2000', 'max:2100'],
            'tagline'        => ['nullable', 'string', 'max:160'],
            'description'    => ['nullable', 'string', 'max:5000'],
            'location'       => ['nullable', 'string', 'max:160'],
            'start_date'     => ['nullable', 'date'],
            'end_date'       => ['nullable', 'date', 'after_or_equal:start_date'],
            'primary_color'  => ['nullable', 'string', 'regex:/^#?[0-9a-fA-F]{6}$/'],
            'secondary_color'=> ['nullable', 'string', 'regex:/^#?[0-9a-fA-F]{6}$/'],
            'status'         => ['required', Rule::in(['draft', 'active', 'archived'])],
            'is_public'      => ['nullable', 'boolean'],
            'logo'           => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ], [], [
            'start_date' => 'start date',
            'end_date'   => 'end date',
        ]);
    }

    /**
     * Strip the leading "#" from the user-submitted colour values so
     * they match the format the database migration expects (#rrggbb).
     * If the user omits the prefix (e.g. "ff2d92"), add it back.
     */
    private function normaliseColor(?string $value): ?string
    {
        return Festival::normaliseColor($value);
    }

    private function storeLogo(Request $request): string
    {
        $file = $request->file('logo');
        $dir  = public_path('img/festival_logos');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $name = $file->hashName();
        $file->move($dir, $name);
        return 'img/festival_logos/' . $name;
    }
}