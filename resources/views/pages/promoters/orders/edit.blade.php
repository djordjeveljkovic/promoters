{{--
    DEPRECATED: this view is no longer used.

    The promoter no longer edits orders after creation — instead, they
    go to the dedicated "show" page (`promoter.orders.show`) for full
    order details, or trigger the post-creation actions from there:

        - re-run image generation
        - re-send the customer email
        - cancel / refund (planned)

    If you ever need to surface this view, see the "show" view
    (`pages/promoters/orders/show.blade.php`) for the current pattern
    and rebuild the form there.
--}}
@php abort(404, 'Promoter order edit has been replaced by the show page.'); @endphp
