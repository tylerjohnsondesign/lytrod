<?php
/**
 * Lytrod email stylesheet.
 *
 * Loaded by WC_Email::apply_inline_style() (woocommerce/includes/emails/class-wc-email.php:921)
 * and inlined into the markup by the vendored Emogrifier. Two consequences shape
 * this file:
 *
 * 1. It must be a SUPERSET of every selector the stock WooCommerce, Subscriptions,
 *    Gifting and Stripe templates use — including both the `email_improvements`-on
 *    and `email_improvements`-off class sets — because the templates this plugin
 *    does not override still render through it.
 * 2. `@media` rules survive only if their selectors match the rendered DOM
 *    (Pelago\Emogrifier\CssInliner::determineMatchingUninlinableCssRules), so the
 *    responsive block targets only IDs this plugin's header always emits.
 *
 * Brand colours are hardcoded from Lytrod_Emails_Brand rather than read from the
 * `woocommerce_email_*_color` options, so an admin edit cannot drift the design.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

$surface     = Lytrod_Emails_Brand::c( 'surface' );
$surface_alt = Lytrod_Emails_Brand::c( 'surface_alt' );
$ink         = Lytrod_Emails_Brand::c( 'ink' );
$ink_muted   = Lytrod_Emails_Brand::c( 'ink_muted' );
$primary     = Lytrod_Emails_Brand::c( 'primary' );
$accent      = Lytrod_Emails_Brand::c( 'accent' );
$hairline    = Lytrod_Emails_Brand::c( 'hairline' );
$danger      = Lytrod_Emails_Brand::c( 'danger' );
$on_primary  = Lytrod_Emails_Brand::c( 'on_primary' );
$radius      = Lytrod_Emails_Brand::c( 'radius' );
$radius_pill = Lytrod_Emails_Brand::c( 'radius_pill' );
$font        = Lytrod_Emails_Brand::c( 'font' );
$font_mono   = Lytrod_Emails_Brand::c( 'font_mono' );
$pad         = Lytrod_Emails_Brand::c( 'pad' );

// Fonts are echoed unescaped so their single quotes survive; both values are
// static stacks from Lytrod_Emails_Brand, never user input.
?>
/* ---------- Reset / shell ---------- */

body {
    margin: 0;
    padding: 0;
    background-color: <?php echo esc_attr( $surface_alt ); ?>;
    color: <?php echo esc_attr( $ink ); ?>;
    font-family: <?php echo $font; ?>;
    -webkit-text-size-adjust: none !important;
    -ms-text-size-adjust: none;
    text-align: center;
}

#outer_wrapper {
    background-color: <?php echo esc_attr( $surface_alt ); ?>;
}

#wrapper {
    margin: 0 auto;
    padding: 32px 0 40px;
    width: 100%;
    max-width: <?php echo (int) Lytrod_Emails_Brand::c( 'width' ); ?>px;
}

#inner_wrapper {
    background-color: transparent;
}

#template_container {
    background-color: <?php echo esc_attr( $surface ); ?>;
    border: 0;
    border-radius: <?php echo esc_attr( $radius ); ?> !important;
    overflow: hidden;
}

#lytrod_rail {
    background-color: <?php echo esc_attr( $primary ); ?>;
    height: <?php echo esc_attr( Lytrod_Emails_Brand::c( 'rail' ) ); ?>;
    line-height: 0;
    font-size: 0;
}

/* ---------- Header ---------- */

#template_header {
    background-color: <?php echo esc_attr( $surface ); ?>;
    border-bottom: 0;
    vertical-align: middle;
    font-family: <?php echo $font; ?>;
}

#template_header_image {
    padding: <?php echo esc_attr( $pad ); ?> <?php echo esc_attr( $pad ); ?> 0;
}

#template_header_image img {
    display: block;
    border: 0;
    outline: none;
    height: auto;
    max-width: 100%;
    margin: 0;
    text-decoration: none;
}

#template_header_image p {
    margin: 0;
}

.email-logo-text {
    color: <?php echo esc_attr( $primary ); ?>;
    font-family: <?php echo $font; ?>;
    font-size: 18px;
    font-weight: 700;
}

#header_wrapper {
    padding: 24px <?php echo esc_attr( $pad ); ?> 0;
    display: block;
}

/*
 * No `background-color: inherit` here. WooCommerce's stock stylesheet uses it, but
 * Emogrifier's CssToAttributeConverter turns it into an invalid `bgcolor="inherit"`
 * attribute on every matched element. The header background is set explicitly on
 * #template_header instead.
 */
#template_header h1,
#template_header h1 a {
    color: <?php echo esc_attr( $ink ); ?>;
}

/* ---------- Body ---------- */

#template_body {
    background-color: <?php echo esc_attr( $surface ); ?>;
}

#body_content {
    background-color: <?php echo esc_attr( $surface ); ?>;
}

#body_content_inner_cell {
    padding: 20px <?php echo esc_attr( $pad ); ?> <?php echo esc_attr( $pad ); ?>;
}

#body_content_inner {
    color: <?php echo esc_attr( $ink ); ?>;
    font-family: <?php echo $font; ?>;
    font-size: 15px;
    line-height: 165%;
    text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}

#body_content p {
    margin: 0 0 16px;
}

/* ---------- Typography ---------- */

h1 {
    margin: 0 0 8px;
    color: <?php echo esc_attr( $ink ); ?>;
    font-family: <?php echo $font; ?>;
    font-size: 28px;
    font-weight: 700;
    line-height: 125%;
    letter-spacing: -0.4px;
    text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}

h2 {
    display: block;
    margin: 28px 0 12px;
    color: <?php echo esc_attr( $ink ); ?>;
    font-family: <?php echo $font; ?>;
    font-size: 18px;
    font-weight: 700;
    line-height: 140%;
    text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}

h3 {
    display: block;
    margin: 20px 0 8px;
    color: <?php echo esc_attr( $ink ); ?>;
    font-family: <?php echo $font; ?>;
    font-size: 15px;
    font-weight: 700;
    line-height: 140%;
    text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}

a {
    color: <?php echo esc_attr( $primary ); ?>;
    font-weight: 600;
    text-decoration: underline;
}

strong {
    color: <?php echo esc_attr( $ink ); ?>;
    font-weight: 700;
}

img {
    border: none;
    display: inline-block;
    height: auto;
    outline: none;
    text-decoration: none;
    vertical-align: middle;
    max-width: 100%;
}

.font-family {
    font-family: <?php echo $font; ?>;
}

.text {
    color: <?php echo esc_attr( $ink ); ?>;
    font-family: <?php echo $font; ?>;
}

.link,
a.link {
    color: <?php echo esc_attr( $primary ); ?>;
}

.text-align-left {
    text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}

.text-align-right {
    text-align: <?php echo is_rtl() ? 'left' : 'right'; ?>;
}

/* ---------- Lytrod components ---------- */

.lytrod-eyebrow {
    margin: 0 0 6px !important;
    color: <?php echo esc_attr( $ink_muted ); ?>;
    font-family: <?php echo $font; ?>;
    font-size: 11px;
    font-weight: 700;
    line-height: 14px;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.lytrod-panel {
    margin: 0 0 20px;
    background-color: <?php echo esc_attr( $surface_alt ); ?>;
    border-radius: <?php echo esc_attr( $radius ); ?>;
}

.lytrod-panel-cell {
    padding: 18px 20px;
    border-radius: <?php echo esc_attr( $radius ); ?>;
}

.lytrod-panel-text {
    margin: 0 0 8px !important;
    color: <?php echo esc_attr( $ink ); ?>;
    font-family: <?php echo $font; ?>;
    font-size: 14px;
    line-height: 155%;
}

.lytrod-mono {
    margin: 0 !important;
    color: <?php echo esc_attr( $primary ); ?>;
    font-family: <?php echo $font_mono; ?>;
    font-size: 16px;
    font-weight: 700;
    line-height: 20px;
    letter-spacing: .04em;
    word-break: break-all;
}

.lytrod-lede {
    margin: 0 0 24px !important;
    color: <?php echo esc_attr( $ink_muted ); ?>;
    font-family: <?php echo $font; ?>;
    font-size: 16px;
    line-height: 160%;
}

.lytrod-meta {
    color: <?php echo esc_attr( $ink_muted ); ?>;
    font-family: <?php echo $font; ?>;
    font-size: 13px;
    line-height: 150%;
}

.lytrod-section-title {
    margin: 32px 0 12px;
    padding-bottom: 10px;
    border-bottom: 1px solid <?php echo esc_attr( $hairline ); ?>;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: <?php echo esc_attr( $ink_muted ); ?>;
}

.lytrod-section-meta {
    display: block;
    margin-top: 4px;
    font-size: 13px;
    font-weight: 400;
    letter-spacing: 0;
    text-transform: none;
    color: <?php echo esc_attr( $ink_muted ); ?>;
}

.lytrod-cta {
    margin: 4px 0 24px;
}

.lytrod-cta-link,
a.lytrod-cta-link {
    color: <?php echo esc_attr( $on_primary ); ?>;
    font-weight: 700;
    text-decoration: none;
}

.lytrod-success-title {
    color: <?php echo esc_attr( $primary ); ?>;
    font-family: <?php echo $font; ?>;
    font-size: 24px;
    font-weight: 700;
    line-height: 125%;
    margin: 0;
}

.lytrod-success-body {
    margin: 0 0 24px !important;
    color: <?php echo esc_attr( $ink ); ?>;
    font-family: <?php echo $font; ?>;
    font-size: 15px;
    line-height: 165%;
}

/*
 * Wide tables get their own horizontal scroller so the email body itself never
 * scrolls sideways on a narrow viewport.
 */
.lytrod-scroll {
    width: 100%;
    overflow-x: auto;
}

.lytrod-divider {
    height: 1px;
    line-height: 1px;
    font-size: 0;
    background-color: <?php echo esc_attr( $hairline ); ?>;
}

.lytrod-danger {
    color: <?php echo esc_attr( $danger ); ?>;
}

.lytrod-accent {
    color: <?php echo esc_attr( $accent ); ?>;
}

/* ---------- Order tables ---------- */

.lytrod-table {
    border: 1px solid <?php echo esc_attr( $hairline ); ?>;
    border-radius: <?php echo esc_attr( $radius ); ?>;
    border-collapse: separate;
    overflow: hidden;
}

.lytrod-table th {
    padding: 10px 16px;
    background-color: <?php echo esc_attr( $surface_alt ); ?>;
    border-bottom: 1px solid <?php echo esc_attr( $hairline ); ?>;
    color: <?php echo esc_attr( $ink_muted ); ?>;
    font-family: <?php echo $font; ?>;
    font-size: 11px;
    font-weight: 700;
    line-height: 14px;
    letter-spacing: .08em;
    text-transform: uppercase;
    text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
    vertical-align: middle;
}

.lytrod-table td {
    padding: 14px 16px;
    border-bottom: 1px solid <?php echo esc_attr( $hairline ); ?>;
    color: <?php echo esc_attr( $ink ); ?>;
    font-family: <?php echo $font; ?>;
    font-size: 14px;
    line-height: 150%;
    vertical-align: top;
}

.lytrod-table tfoot td,
.lytrod-table tfoot th {
    padding: 10px 16px;
    background-color: <?php echo esc_attr( $surface ); ?>;
    border-bottom: 0;
    border-top: 1px solid <?php echo esc_attr( $hairline ); ?>;
    color: <?php echo esc_attr( $ink_muted ); ?>;
    font-size: 13px;
    font-weight: 400;
    line-height: 150%;
    text-transform: none;
    letter-spacing: 0;
}

.lytrod-table tfoot .order-totals-total td,
.lytrod-table tfoot .order-totals-total th {
    color: <?php echo esc_attr( $ink ); ?>;
    font-size: 16px;
    font-weight: 700;
}

.lytrod-numeric {
    text-align: <?php echo is_rtl() ? 'left' : 'right'; ?>;
    white-space: nowrap;
}

/*
 * Stock WooCommerce / Subscriptions order-table cells.
 *
 * `.td` is the stock class and carries a full cell border, which is what the
 * templates this plugin does NOT override expect — the vendor
 * subscription-info.php table, the Gifting emails, and anything a future
 * WooCommerce release adds. This plugin's own tables deliberately do not use
 * `.td`: they use `.lytrod-table`, whose cells carry only a bottom hairline. Adding
 * `.td` to them would reinstate the full grid and defeat the card treatment.
 */
.td {
    padding: 12px;
    color: <?php echo esc_attr( $ink ); ?>;
    /* !important is required: woocommerce-subscriptions/templates/emails/subscription-info.php:22
       hardcodes an inline Helvetica stack, and inline attributes beat Emogrifier. */
    font-family: <?php echo $font; ?> !important;
    font-size: 14px;
    border: 1px solid <?php echo esc_attr( $hairline ); ?>;
    vertical-align: middle;
}

#body_content table .email-order-details td,
#body_content table .email-order-details th {
    padding: 10px 16px;
}

.email-order-details {
    border: 1px solid <?php echo esc_attr( $hairline ); ?>;
    border-radius: <?php echo esc_attr( $radius ); ?>;
}

.email-order-detail-heading {
    color: <?php echo esc_attr( $ink ); ?>;
}

h2.email-order-detail-heading span,
.email-order-detail-heading span {
    display: block;
    color: <?php echo esc_attr( $ink_muted ); ?>;
    font-size: 13px;
    font-weight: 400;
}

h2.email-order-detail-heading span a {
    text-decoration: none;
}

.email-order-item-meta {
    color: <?php echo esc_attr( $ink_muted ); ?>;
    font-size: 13px;
    line-height: 150%;
}

.email-introduction {
    padding-bottom: 8px;
}

.email-additional-content,
#body_content table td td.email-additional-content {
    padding: 24px 0 0;
    color: <?php echo esc_attr( $ink_muted ); ?>;
    font-family: <?php echo $font; ?>;
    font-size: 14px;
}

.email-additional-content p,
.email-additional-content-aligned p {
    text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}

.order_item td,
.order_item th {
    color: <?php echo esc_attr( $ink ); ?>;
    vertical-align: top;
}

.order-item-data {
    color: <?php echo esc_attr( $ink ); ?>;
    font-family: <?php echo $font; ?>;
}

#body_content .order-item-data td {
    border: 0 !important;
    padding: 0 !important;
    vertical-align: top;
}

.order-totals td,
.order-totals th {
    font-weight: 400;
    padding-top: 6px;
    padding-bottom: 6px;
    color: <?php echo esc_attr( $ink_muted ); ?>;
}

.order-totals-total th {
    color: <?php echo esc_attr( $ink ); ?>;
    font-weight: 700;
}

.order-totals-total td {
    color: <?php echo esc_attr( $ink ); ?>;
    font-weight: 700;
    font-size: 18px;
}

.order-totals .includes_tax {
    display: block;
    color: <?php echo esc_attr( $ink_muted ); ?>;
    font-size: 12px;
}

.order-totals-last td,
.order-totals-last th {
    border-bottom: 1px solid <?php echo esc_attr( $hairline ); ?>;
    padding-bottom: 16px;
}

.order-customer-note td {
    border-top: 1px solid <?php echo esc_attr( $hairline ); ?>;
    padding-top: 16px;
    color: <?php echo esc_attr( $ink_muted ); ?>;
}

ul.wc-item-meta,
#body_content td ul.wc-item-meta {
    margin: 8px 0 0;
    padding: 0;
    list-style: none;
    font-size: 13px;
    color: <?php echo esc_attr( $ink_muted ); ?>;
}

ul.wc-item-meta li,
#body_content td ul.wc-item-meta li {
    margin: 4px 0 0;
    padding: 0;
}

ul.wc-item-meta li p,
#body_content td ul.wc-item-meta li p {
    margin: 0 !important;
}

.wc-item-meta-label {
    clear: both;
    float: <?php echo is_rtl() ? 'right' : 'left'; ?>;
    font-weight: 700;
    color: <?php echo esc_attr( $ink ); ?>;
    margin-<?php echo is_rtl() ? 'left' : 'right'; ?>: .25em;
}

.wc-item-download-label {
    font-weight: 700;
    color: <?php echo esc_attr( $ink ); ?>;
}

/* ---------- Addresses ---------- */

#addresses {
    width: 100%;
}

#addresses td + td {
    padding-<?php echo is_rtl() ? 'right' : 'left'; ?>: 12px !important;
}

.address-title {
    color: <?php echo esc_attr( $ink_muted ); ?>;
    font-family: <?php echo $font; ?>;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.address {
    padding: 14px 16px;
    background-color: <?php echo esc_attr( $surface_alt ); ?>;
    border: 0;
    border-radius: <?php echo esc_attr( $radius ); ?>;
    color: <?php echo esc_attr( $ink ); ?>;
    font-family: <?php echo $font; ?>;
    font-size: 14px;
    font-style: normal;
    line-height: 155%;
    word-break: break-word;
}

.additional-fields {
    margin: 0;
    padding: 14px 16px 4px;
    background-color: <?php echo esc_attr( $surface_alt ); ?>;
    border: 0;
    border-radius: <?php echo esc_attr( $radius ); ?>;
    color: <?php echo esc_attr( $ink ); ?>;
    font-size: 14px;
    list-style: none outside;
}

.additional-fields li {
    margin: 0 0 10px 0;
}

/* ---------- Downloads ---------- */

.woocommerce-order-downloads__title {
    color: <?php echo esc_attr( $ink ); ?>;
}

.woocommerce-MyAccount-downloads-file,
a.woocommerce-MyAccount-downloads-file,
.button,
a.button {
    display: inline-block;
    padding: 11px 20px;
    background-color: <?php echo esc_attr( $surface ); ?>;
    border: 1px solid <?php echo esc_attr( $primary ); ?>;
    border-radius: <?php echo esc_attr( $radius ); ?>;
    color: <?php echo esc_attr( $primary ); ?>;
    font-family: <?php echo $font; ?>;
    font-size: 14px;
    font-weight: 700;
    line-height: 18px;
    text-decoration: none;
}

.button.alt,
a.button.alt {
    background-color: <?php echo esc_attr( $primary ); ?>;
    color: <?php echo esc_attr( $on_primary ); ?>;
}

/* ---------- Rules WooCommerce emits when email_improvements is on ---------- */

.hr {
    height: 1px;
    line-height: 1px;
    font-size: 0;
    border-bottom: 1px solid <?php echo esc_attr( $hairline ); ?>;
    margin: 20px 0;
}

.hr-top {
    margin-top: 32px;
}

.hr-bottom {
    margin-bottom: 32px;
}

/* ---------- Point of Sale ---------- */

.pos-store-information,
.refund-returns-policy {
    color: <?php echo esc_attr( $ink_muted ); ?>;
    font-family: <?php echo $font; ?>;
    font-size: 13px;
    line-height: 155%;
}

/* ---------- Stock notification emails ---------- */

#notification__container {
    background-color: <?php echo esc_attr( $surface ); ?>;
}

#notification__into_content,
#notification__product,
#notification__verification_expiration,
#notification__footer {
    color: <?php echo esc_attr( $ink ); ?>;
    font-family: <?php echo $font; ?>;
    font-size: 14px;
    line-height: 155%;
}

#notification__verification_expiration,
#notification__footer,
#notification__unsubscribe_link {
    color: <?php echo esc_attr( $ink_muted ); ?>;
    font-size: 13px;
}

#notification__action_button a {
    display: inline-block;
    padding: 14px 28px;
    background-color: <?php echo esc_attr( $primary ); ?>;
    border-radius: <?php echo esc_attr( $radius ); ?>;
    color: <?php echo esc_attr( $on_primary ); ?>;
    font-weight: 700;
    text-decoration: none;
}

/* ---------- Footer ---------- */

#template_footer {
    background-color: <?php echo esc_attr( $primary ); ?>;
    border-radius: <?php echo esc_attr( $radius ); ?>;
}

#template_footer td {
    padding: 0;
    border-radius: <?php echo esc_attr( $radius ); ?>;
}

#template_footer #credit {
    padding: <?php echo esc_attr( $pad ); ?>;
    border: 0;
    background-color: <?php echo esc_attr( $primary ); ?>;
    color: <?php echo esc_attr( $on_primary ); ?>;
    font-family: <?php echo $font; ?>;
    font-size: 12px;
    line-height: 165%;
    text-align: center;
}

#template_footer #credit p {
    margin: 0 0 8px;
}

#template_footer #credit a,
#template_footer #credit a:link,
#template_footer #credit a:visited {
    color: <?php echo esc_attr( $on_primary ); ?>;
    font-weight: 600;
    text-decoration: none;
}

.lytrod-footer-logo img {
    display: block;
    margin: 0 auto 18px;
    border: 0;
    outline: none;
    height: auto;
}

.lytrod-footer-links {
    margin: 0 0 14px !important;
    font-size: 13px;
    font-weight: 600;
}

.lytrod-footer-sep {
    color: <?php echo esc_attr( $accent ); ?>;
    padding: 0 8px;
}

.lytrod-footer-note {
    margin: 0 !important;
    color: <?php echo esc_attr( $accent ); ?>;
    font-size: 12px;
    line-height: 160%;
}

/*
 * Media queries are dropped by Emogrifier unless their selectors match the
 * rendered document, so every selector below is an ID this plugin's
 * email-header.php always emits.
 */
@media screen and (max-width: 600px) {
    #wrapper {
        padding: 16px 0 24px !important;
    }

    #template_header_image {
        padding: <?php echo esc_attr( Lytrod_Emails_Brand::c( 'pad_mobile' ) ); ?> <?php echo esc_attr( Lytrod_Emails_Brand::c( 'pad_mobile' ) ); ?> 0 !important;
    }

    #header_wrapper {
        padding: 18px <?php echo esc_attr( Lytrod_Emails_Brand::c( 'pad_mobile' ) ); ?> 0 !important;
    }

    #header_wrapper h1 {
        font-size: 24px !important;
        letter-spacing: -0.2px !important;
    }

    #body_content_inner_cell {
        padding: 16px <?php echo esc_attr( Lytrod_Emails_Brand::c( 'pad_mobile' ) ); ?> <?php echo esc_attr( Lytrod_Emails_Brand::c( 'pad_mobile' ) ); ?> !important;
    }

    #body_content_inner {
        font-size: 14px !important;
    }

    #template_footer #credit {
        padding: <?php echo esc_attr( Lytrod_Emails_Brand::c( 'pad_mobile' ) ); ?> !important;
    }

    .lytrod-success-title {
        font-size: 20px !important;
    }

    /*
     * Stack the Payment Summary / Important Links pair. Forcing table cells to
     * display:block is the standard responsive-email technique — but note
     * HtmlPruner::removeElementsWithDisplayNone() runs on this content, so these must
     * never be display:none.
     */
    .lytrod-col {
        display: block !important;
        width: 100% !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    .lytrod-col + .lytrod-col {
        padding-top: 24px !important;
    }
}
<?php
