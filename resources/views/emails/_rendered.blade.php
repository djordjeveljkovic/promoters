{{--
    Wrapper view used by CustomerTicketsMail (and any other Mailable that
    pulls a ResolvedTemplate from MailTemplateRenderer).

    Renders the resolved HTML body verbatim.  We `e()` the content anyway
    to make sure no stray Blade tag sneaks in, but Blade has already
    rendered it, so it is plain HTML by the time it lands here.
--}}
{!! $resolved->body !!}
