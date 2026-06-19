<!DOCTYPE html>
<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" lang="en">

<head>
    <title>Vaše Ulaznice</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet" type="text/css">
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            padding: 0;
        }
        a[x-apple-data-detectors] {
            color: inherit !important;
            text-decoration: inherit !important;
        }
        #MessageViewBody a {
            color: inherit;
            text-decoration: none;
        }
        p {
            line-height: inherit
        }
        .desktop_hide,
        .desktop_hide table {
            mso-hide: all;
            display: none;
            max-height: 0px;
            overflow: hidden;
        }
        .image_block img+div {
            display: none;
        }
        sup, sub {
            font-size: 75%;
            line-height: 0;
            position: relative; /* Added for better control */
            vertical-align: baseline; /* Added for better control */
        }
        sup { top: -0.5em; } /* Adjust as needed */
        sub { bottom: -0.25em; } /* Adjust as needed */
        .im {
            color: white !important !important !important;
        }

        @media (max-width:700px) {
            .desktop_hide table.icons-inner,
            .social_block.desktop_hide .social-table {
                display: inline-block !important;
            }
            .icons-inner {
                text-align: center;
            }
            .icons-inner td {
                margin: 0 auto;
            }
            .mobile_hide {
                display: none;
            }
            .row-content {
                width: 100% !important;
            }
            .stack .column {
                width: 100%;
                display: block;
            }
            .mobile_hide {
                min-height: 0;
                max-height: 0;
                max-width: 0;
                overflow: hidden;
                font-size: 0px;
            }
            .desktop_hide,
            .desktop_hide table {
                display: table !important;
                max-height: none !important;
            }
        }
    </style>
</head>

<body class="body" style="margin: 0; background-color: #000000; padding: 0; -webkit-text-size-adjust: none; text-size-adjust: none; color: white !important">
    <table class="nl-container" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #000000;">
        <tbody>
            <tr>
                <td>
                    <table class="row row-1" align="center" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
                        <tbody>
                            <tr>
                                <td>
                                    <table class="row-content stack" align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; color: #000000; width: 680px; margin: 0 auto;" width="680">
                                        <tbody>
                                            <tr>
                                                <td class="column column-1" width="100%" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; vertical-align: top;">
                                                    {{-- STATIC Event Hero Image --}}
                                                    <table class="image_block block-1" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
                                                        <tr>
                                                            <td class="pad" style="width:100%;">
                                                                <div class="alignment" align="center">
                                                                    <div style="max-width: 680px;"><img src="https://b87b903020.imgdist.com/pub/bfra/rnkqn4di/gjz/swz/zpy/Untitled-22222.png" style="display: block; height: auto; border: 0; width: 100%;" width="680" alt="REFEST" title="REFEST"></div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    {{-- STATIC Event Title --}}
                                                    <table class="paragraph_block block-2" width="100%" border="0" cellpadding="10" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;">
                                                        <tr>
                                                            <td class="pad">
                                                                <div style="color:#ffffff;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:30px;line-height:1.2;text-align:center;mso-line-height-alt:36px;">
                                                                    <p style="margin: 0; word-break: break-word;"><strong>VIDIMO SE NA ZLATIBORU!</strong></p>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <div class="spacer_block block-3" style="height:30px;line-height:30px;font-size:1px;">&#8202;</div>
                                                    <table class="paragraph_block block-4" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;">
                                                        <tr>
                                                            <td class="pad" style="padding-bottom:10px;padding-left:25px;padding-right:25px;padding-top:10px;">
                                                                <div style="color:#ffffff;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:16px;line-height:1.5;text-align:left;mso-line-height-alt:24px;">
                                                                    {{-- DYNAMIC Greeting --}}
                                                                    <p style="margin: 0;">Hej{{ $order->customer_name ? ' ' . $order->customer_name : '' }}!<br><br>Hvala ti na interesovanju za <strong>REFEST 2025</strong>! {{-- STATIC Event Name --}} <br><br>Tvoja ulaznica je <strong>u prilogu ovog mejla</strong>.</p>
                                                                    <p style="margin: 0;">&nbsp;</p>
                                                                    <p style="margin: 0;"><strong>Važne napomene</strong>:</p>
                                                                    {{-- DYNAMIC based on quantity --}}
                                                                    @if($order->items->sum('quantity') > 1)
                                                                    <p style="margin: 0;">- Ukoliko se na mejlu nalazi više ulaznica, molimo te da ih proslediš ostatku ekipe.</p>
                                                                    <p style="margin: 0;">- Svako mora da ima svoju ulaznicu (sliku na telefonu ili odštampanu) i da se sa njom čekira na ulazu.</p>
                                                                    @else
                                                                    <p style="margin: 0;">- Molimo te da pripremiš svoju ulaznicu (sliku na telefonu ili odštampanu) kako bi se čekirao/la na ulazu.</p>
                                                                    @endif
                                                                    <p style="margin: 0;">- Email adresa nije dovoljna za ulazak. Neophodno je da pokažeš ulaznicu.</p>
                                                                    <p style="margin: 0;">- QR kod na ulaznici je ključan – on će se skenirati prilikom ulaska.<br><br>Vidimo se 24. JULA! {{-- STATIC Event Date --}}<br><br>REFEST Tim {{-- STATIC Team Name --}}</p>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <div class="spacer_block block-5" style="height:30px;line-height:30px;font-size:1px;">&#8202;</div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    {{-- STATIC Event Details Header with Background --}}
                    <table class="row row-2" align="center" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
                        <tbody>
                            <tr>
                                <td>
                                    <table class="row-content stack" align="center" border="0" cellpadding="0" cellspacing="0" role="presentation"  width="680">
                                        <tbody>
                                            <tr>
                                                <td class="column column-1" width="100%" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; vertical-align: top;">
                                                    <table class="paragraph_block block-1" width="100%" border="0" cellpadding="10" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;">
                                                        <tr>
                                                            <td class="pad">
                                                                <div style="color:#ffffff;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:30px;line-height:1.2;text-align:left;mso-line-height-alt:36px;">
                                                                    <p style="margin: 0;"><strong>Datum i mesto održavanja:</strong></p>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    {{-- STATIC Event Date and Location Info --}}
                    <table class="row row-3" align="center" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
                        <tbody>
                            <tr>
                                <td>
                                    <table class="row-content stack" align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; color: #000000; width: 680px; margin: 0 auto;" width="680">
                                        <tbody>
                                            <tr>
                                                <td class="column column-1" width="100%" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; padding-bottom: 5px; padding-top: 5px; vertical-align: top;">
                                                    <table class="icons_block block-1" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; text-align: left; line-height: 0;">
                                                        <tr>
                                                            <td class="pad" style="vertical-align: middle; color: #ffffff; font-family: 'Montserrat', 'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif; font-size: 16px; text-align: left;">
                                                                <table class="icons-inner" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; display: inline-block; padding-left: 0px; padding-right: 0px;" cellpadding="0" cellspacing="0" role="presentation">
                                                                    <tr>
                                                                        <td style="vertical-align: middle; text-align: center; padding-top: 5px; padding-bottom: 5px; padding-left: 25px; padding-right: 25px;"><img class="icon" alt="Calendar Icon" src="https://d1oco4z2z1fhwp.cloudfront.net/templates/default/3296/MCC_confirmation_icon_calendar.png" height="auto" width="32" align="center" style="display: block; height: auto; margin: 0 auto; border: 0;"></td>
                                                                        <td style="font-family: 'Montserrat', 'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif; font-size: 16px; color: #ffffff; vertical-align: middle; text-align: left;">24/07/2025 </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <table class="icons_block block-2" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; text-align: left; line-height: 0;">
                                                        <tr>
                                                            <td class="pad" style="vertical-align: middle; color: #ffffff; font-family: 'Montserrat', 'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif; font-size: 16px; text-align: left;">
                                                                <table class="icons-inner" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; display: inline-block; padding-left: 0px; padding-right: 0px;" cellpadding="0" cellspacing="0" role="presentation">
                                                                    <tr>
                                                                        <td style="vertical-align: middle; text-align: center; padding-top: 5px; padding-bottom: 5px; padding-left: 25px; padding-right: 25px;"><a href="https://maps.app.goo.gl/zy9WKAEM28M5qUcg7" target="_self" style="text-decoration: none;"><img class="icon" alt="Location Icon" src="https://d1oco4z2z1fhwp.cloudfront.net/templates/default/3296/MCC_confirmation_icon_location.png" height="auto" width="32" align="center" style="display: block; height: auto; margin: 0 auto; border: 0;"></a></td>
                                                                        <td style="font-family: 'Montserrat', 'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif; font-size: 16px; color: #ffffff; vertical-align: middle; text-align: left;"><a href="https://maps.app.goo.gl/zy9WKAEM28M5qUcg7" target="_self" style="color: #ffffff; text-decoration: none;">Zlatibor, Tić Polje</a></td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <div class="spacer_block block-3" style="height:30px;line-height:30px;font-size:1px;">&#8202;</div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    {{-- DYNAMIC Embedded Ticket Images --}}
                    <table class="row row-tickets-title" align="center" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
                        <tbody><tr><td>
                            <table class="row-content stack" align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; color: #000000; width: 680px; margin: 0 auto;" width="680">
                                <tbody><tr><td class="column column-1" width="100%" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; padding-top: 30px; padding-bottom: 5px; vertical-align: top;">
                                    <table class="paragraph_block block-1" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;">
                                        <tr><td class="pad" style="padding-left:25px; padding-right:25px;"><div style="color:#ffffff;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:24px;line-height:1.2;text-align:left;"><p style="margin: 0;"><strong>Vaše ulaznice:</strong></p></div></td></tr>
                                    </table>
                                </td></tr></tbody>
                            </table>
                        </td></tr></tbody>
                    </table>

@if($order->tickets && $order->tickets->count() > 0)
    @php
        $ticketTypeCounts = []; // Initialize an array to keep track of counts per ticket type
    @endphp

    @foreach($order->tickets as $individualTicket)
        @if($individualTicket->image_path)
            @php
                // Get the ticket_type_id for the current ticket
                $ticketTypeId = $individualTicket->ticket_type_id;

                // Initialize count for this ticket type if it's the first time we see it
                if (!isset($ticketTypeCounts[$ticketTypeId])) {
                    $ticketTypeCounts[$ticketTypeId] = 0;
                }
                // Increment the count for this specific ticket type
                $ticketTypeCounts[$ticketTypeId]++;
                $currentTicketNumberForType = $ticketTypeCounts[$ticketTypeId];
            @endphp
            <table class="row row-ticket-image-item" align="center" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
                <tbody><tr><td>
                    <table class="row-content stack" align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; color: #000000; width: 680px; margin: 0 auto;" width="680">
                        <tbody><tr><td class="column column-1" width="100%" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; vertical-align: top;">
                            <table class="paragraph_block" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;">
                                <tr><td class="pad" style="padding-left:25px;padding-right:25px;padding-top:5px;"><div style="color:#dddddd;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:14px;line-height:1.5;text-align:left;"><p style="margin: 0;">
                                    Ulaznica: <strong>{{ $individualTicket->ticketType->name ?? 'Ulaznica' }}</strong> broj {{ $currentTicketNumberForType }}  {{-- Changed from $loop->index --}}
                                </p></div></td></tr>
                            </table>
                            <table class="image_block" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
                                <tr><td class="pad" style="padding:15px 25px 15px 25px; width:100%;"><div class="alignment" align="center">
                                    {{-- Using your existing asset() helper structure --}}
                                    {{-- This assumes $individualTicket->image_path is "generated_tickets/EVENT_ID/ticket_filename.png" --}}
                                    {{-- and your APP_URL is correct, and storage:link is set up. --}}
                                    <img src="{{ asset('storage/' . ltrim($individualTicket->image_path, '/')) }}" style="display: block; height: auto; border: 0; width: 100%; max-width: 600px;" alt="Ulaznica tipa {{ $individualTicket->ticketType->name ?? 'Ulaznica' }} broj {{ $currentTicketNumberForType }}"> {{-- Updated alt text --}}
                                </div></td></tr>
                            </table>
                            @if(!$loop->last)
                            <table class="divider_block" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation"><tr><td class="pad" style="padding-top:10px;padding-bottom:10px;"><div class="alignment" align="center"><table border="0" cellpadding="0" cellspacing="0" role="presentation" width="90%" style="border-top: 1px solid #444444;"><tr><td><span></span></td></tr></table></div></td></tr></table>
                            @endif
                        </td></tr></tbody>
                    </table>
                </td></tr></tbody>
            </table>
        @else
            <p style="color:#ffdddd; text-align:center; padding:10px;">Slika ulaznice (Kod: {{ $individualTicket->code }}) nije mogla biti generisana ili pronađena.</p>
        @endif
    @endforeach
@else
    <p style="color:#ffffff; text-align:center; padding:20px;">Kontaktirajte podrsku na info@refest.rs</p> {{-- Consider a more generic "No tickets to display" message if appropriate --}}
@endif


                    <table class="row row-8" align="center" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;"> {{-- Original Row 8 for spacer --}}
                        <tbody><tr><td><table class="row-content stack" align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; color: #000000; width: 680px; margin: 0 auto;" width="680"><tbody><tr><td class="column column-1" width="100%" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; padding-bottom: 5px; padding-top: 5px; vertical-align: top;"><div class="spacer_block block-1" style="height:50px;line-height:50px;font-size:1px;">&#8202;</div></td></tr></tbody></table></td></tr></tbody>
                    </table>

                    {{-- DYNAMIC Order Info Footer --}}
                    <table class="row row-9" align="center" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #222222;">
                        <tbody>
                            <tr>
                                <td>
                                    <table class="row-content stack" align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; color: #000000; width: 680px; margin: 0 auto;" width="680">
                                        <tbody>
                                            <tr>
                                                <td class="column column-2" width="33.333333333333336%" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; padding-bottom: 5px; padding-top: 5px; vertical-align: top;">
                                                    <table class="paragraph_block block-1" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;"><tr><td class="pad" style="padding-bottom:10px;padding-left:25px;padding-right:25px;padding-top:10px;"><div style="color:#ffffff;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:16px;line-height:1.5;text-align:left;"><p style="margin: 0;">Datum:</p></div></td></tr></table>
                                                    <table class="paragraph_block block-2" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;"><tr><td class="pad" style="padding-bottom:15px;padding-left:25px;padding-right:25px;"><div style="color:#ffffff;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:16px;line-height:1.5;text-align:left;"><p style="margin: 0;"><strong>{{ $order->created_at->format('d/m/Y') }}</strong></p></div></td></tr></table>
                                                </td>
                                                <td class="column column-3" width="33.333333333333336%" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; padding-bottom: 5px; padding-top: 5px; vertical-align: top;">
                                                    <table class="paragraph_block block-1" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;"><tr><td class="pad" style="padding-bottom:10px;padding-left:25px;padding-right:25px;padding-top:10px;"><div style="color:#ffffff;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:16px;line-height:1.5;text-align:left;"><p style="margin: 0;">Email adresa:</p></div></td></tr></table>
                                                    <table class="paragraph_block block-2" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;"><tr><td class="pad" style="padding-bottom:15px;padding-left:25px;padding-right:25px;"><div style="color:#ffffff;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:16px;line-height:1.5;text-align:left;"><p style="margin: 0;"><strong>{{ $order->email }}</strong></p></div></td></tr></table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <table class="row row-10" align="center" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;"> {{-- Original Row 10 for spacer --}}
                         <tbody><tr><td><table class="row-content stack" align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; color: #000000; width: 680px; margin: 0 auto;" width="680"><tbody><tr><td class="column column-1" width="100%" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; padding-bottom: 5px; padding-top: 5px; vertical-align: top;"><div class="spacer_block block-1" style="height:30px;line-height:30px;font-size:1px;">&#8202;</div></td></tr></tbody></table></td></tr></tbody>
                    </table>

                    {{-- STATIC Footer --}}
                    <table class="row row-12" align="center" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
                        <tbody>
                            <tr>
                                <td>
                                    <table class="row-content stack" align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #000000; color: #000000; width: 680px; margin: 0 auto;" width="680">
                                        <tbody>
                                            <tr>
                                                <td class="column column-1" width="100%" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; vertical-align: top;">
                                                    <table class="image_block block-1" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;"><tr><td class="pad" style="width:100%;padding-right:0px;padding-left:0px; padding-top:20px;"><div class="alignment" align="center"><div style="max-width: 136px;"><img src="https://b87b903020.imgdist.com/pub/bfra/rnkqn4di/jin/hxy/ftn/Logo%20REFEST%20White.png" style="display: block; height: auto; border: 0; width: 100%;" width="136" alt="REFEST Logo" title="REFEST Logo"></div></div></td></tr></table>
                                                    <table class="social_block block-2" width="100%" border="0" cellpadding="10" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;"><tr><td class="pad"><div class="alignment" align="center"><table class="social-table" width="72px" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; display: inline-block;"><tr><td style="padding:0 2px 0 2px;"><a href="https://www.instagram.com/refest.rs/" target="_blank"><img src="https://app-rsrc.getbee.io/public/resources/social-networks-icon-sets/t-only-logo-white/instagram@2x.png" width="32" height="auto" alt="Instagram" title="instagram" style="display: block; height: auto; border: 0;"></a></td><td style="padding:0 2px 0 2px;"><a href="https://refest.rs/" target="_blank"><img src="https://app-rsrc.getbee.io/public/resources/social-networks-icon-sets/t-only-logo-white/website@2x.png" width="32" height="auto" alt="Web Site" title="Web Site" style="display: block; height: auto; border: 0;"></a></td></tr></table></div></td></tr></table>
                                                    <table class="paragraph_block block-3" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;"><tr><td class="pad" style="padding-left:30px;padding-right:30px;"><div style="color:#ffffff;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:14px;line-height:1.2;text-align:center;"><p style="margin: 0;">Pripremite se za još jedno nezaboravno leto na Zlatiboru!</p><p style="margin: 0;">GO WILD FOR A WHILE!</p></div></td></tr></table>
                                                    <div class="spacer_block block-4" style="height:50px;line-height:50px;font-size:1px;">&#8202;</div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table></body>
</html>
