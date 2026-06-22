<?php

namespace App\Livewire;

use App\Models\Festival;
use App\Models\TicketOrder;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * P-069: Global search across festivals, promoters, orders and ticket
 * types — scoped to the festivals the acting user can access.
 *
 * Why Livewire: debounced input + reactive results are an instant fit
 * for the framework; the alternative (a debounced JS fetch into a
 * dedicated endpoint) needs more wiring for the same UX.
 *
 * Why a single component (not a single SQL `LIKE '%q%'` across all
 * tables): the search is per-entity with tailored columns + limits,
 * so users can scan by category. Anything we miss here is one
 * `groupBy`-aware query away.
 */
class GlobalSearch extends Component
{
    /** Bound to the search input. Bound on URL too so a search persists
     *  across a Livewire navigate / page refresh. */
    #[Url(as: 'q', except: '')]
    public string $q = '';

    /** Show at most N results per category. */
    public int $perCategory = 5;

    /** Open the panel on first keystroke and on initial load if `q` is set. */
    public bool $open = false;

    public function updatedQ(): void
    {
        $this->open = true;
    }

    public function close(): void
    {
        $this->open = false;
        $this->q = '';
    }

    /**
     * Per-category results. Returns an empty collection when the query is
     * too short (less than 2 chars) so we don't accidentally dump the
     * whole database on a stray key press.
     */
    public function results(): Collection
    {
        $q = trim($this->q);
        if (mb_strlen($q) < 2) {
            return collect();
        }

        $user = auth()->user();
        if (!$user) {
            return collect();
        }

        $festivalIds = $user->accessibleFestivals()->pluck('id');
        if ($festivalIds->isEmpty()) {
            return collect();
        }

        $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $q) . '%';

        return collect([
            'festivals' => Festival::query()
                ->whereIn('id', $festivalIds)
                ->where(function ($q2) use ($like) {
                    $q2->where('name', 'like', $like)
                        ->orWhere('location', 'like', $like)
                        ->orWhere('slug', 'like', $like);
                })
                ->limit($this->perCategory)
                ->get(['id', 'name', 'slug', 'year', 'status', 'primary_color']),

            'promoters' => User::query()
                ->whereIn('role', ['promoter', 'sub_promoter'])
                ->whereHas('festivals', fn ($q2) => $q2->whereIn('festivals.id', $festivalIds))
                ->where(function ($q2) use ($like) {
                    $q2->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like);
                })
                ->limit($this->perCategory)
                ->get(['id', 'name', 'email']),

            'orders' => TicketOrder::query()
                ->whereIn('festival_id', $festivalIds)
                ->whereIn('job_status', ['completed', 'sent', 'processing'])
                ->where(function ($q2) use ($like) {
                    $q2->where('order_number', 'like', $like)
                        ->orWhere('email', 'like', $like);
                })
                ->with('festival:id,slug,name')
                ->latest()
                ->limit($this->perCategory)
                ->get(['id', 'order_number', 'email', 'total', 'festival_id', 'job_status']),

            'ticket_types' => TicketType::query()
                ->whereIn('festival_id', $festivalIds)
                ->where('name', 'like', $like)
                ->with('festival:id,slug,name')
                ->limit($this->perCategory)
                ->get(['id', 'name', 'price', 'festival_id']),
        ])->filter(fn ($items) => $items->isNotEmpty());
    }

    /** Decide which URL each result row should link to. */
    public function urlFor(string $category, $row): ?string
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }
        switch ($category) {
            case 'festivals':
                if ($user->isAdmin() || $user->isSuperAdmin()) {
                    return route('admin.dashboard', ['festival' => $row->slug]);
                }
                if ($user->isPromoter() || $user->isSubPromoter()) {
                    return route('promoter.dashboard', ['festival' => $row->slug]);
                }
                return null;
            case 'promoters':
                if ($user->isFestivalAdmin($row->pivot->festival_id ?? null) || $user->isSuperAdmin()) {
                    // Try to find a festival the user can use as scope.
                    $festival = $row->festivals->first();
                    if ($festival) {
                        return route('admin.promoters.edit', ['festival' => $festival->slug, 'id' => $row->id]);
                    }
                }
                return null;
            case 'orders':
                return route('admin.orders.show', [
                    'festival' => $row->festival?->slug ?? $row->festival_id,
                    'order'    => $row->id,
                ]);
            case 'ticket_types':
                return route('admin.ticket-types.edit', [
                    'festival' => $row->festival?->slug ?? $row->festival_id,
                    'id'       => $row->id,
                ]);
        }
        return null;
    }

    public function render(): View
    {
        return view('livewire.global-search', [
            'groups' => $this->results(),
        ]);
    }
}
