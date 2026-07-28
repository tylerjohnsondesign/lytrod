<?php
/**
 * Lytrod email header — opens the document and the content card.
 *
 * Three things in here are load-bearing and must not be removed:
 *
 * 1. `<head>`. Pelago\Emogrifier\CssInliner::getHeadElement() throws without one;
 *    WC_Email::apply_inline_style() catches that at class-wc-email.php:958-961 and
 *    returns the content with ZERO CSS applied, logging only to the `emogrifier`
 *    WooCommerce log source. A missing `<head>` is a silent, total style failure.
 *
 * 2. The zero-dimension preheader. `display:none` cannot be used —
 *    HtmlPruner::removeElementsWithDisplayNone() runs whenever the block email
 *    editor is off (class-wc-email.php:950-953) and deletes the element outright.
 *    The same applies to the `screen-reader-text` class, which WooCommerce force-hides
 *    un-filterably in get_must_use_css_styles().
 *
 * 3. The stock layout IDs. Templates this plugin does not override — including the
 *    two ceded to subscriptions-upgrader and all of the Gifting emails — are styled
 *    through these selectors, and email-footer.php closes this markup symmetrically.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

$store_name    = $store_name ?? Lytrod_Emails_Brand::store_name();
$email_heading = $email_heading ?? '';
$lytrod_email  = Lytrod_Emails_Context::get();

$preheader = isset( $lytrod_preheader )
    ? (string) $lytrod_preheader
    : Lytrod_Emails_Content::preheader_for( $lytrod_email );

$pill = isset( $lytrod_pill ) ? $lytrod_pill : Lytrod_Emails_Content::pill_for( $lytrod_email );

/** This filter is documented in woocommerce/templates/emails/email-header.php */
$header_image_url = apply_filters( 'woocommerce_email_header_image_url', home_url( '/' ) );

$surface     = Lytrod_Emails_Brand::c( 'surface' );
$surface_alt = Lytrod_Emails_Brand::c( 'surface_alt' );
$primary     = Lytrod_Emails_Brand::c( 'primary' );
$width       = (int) Lytrod_Emails_Brand::c( 'width' );
$logo_width  = Lytrod_Emails_Brand::logo_width();

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo( 'charset' ); ?>" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="x-apple-disable-message-reformatting" />
        <?php
        /*
         * The design commits to a light appearance and the masthead logo is a
         * light-background asset, so declare light-only rather than let
         * Outlook.com / Apple Mail auto-invert and render the logo illegibly.
         */
        ?>
        <meta name="color-scheme" content="light" />
        <meta name="supported-color-schemes" content="light" />
        <title><?php echo esc_html( $store_name ); ?></title>
        <!--[if mso]>
        <style type="text/css">
            body, table, td, p, a, h1, h2, h3 { font-family: Helvetica, Arial, sans-serif !important; }
        </style>
        <![endif]-->
        <!--[if !mso]><!-- -->
        <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;700&amp;display=swap" rel="stylesheet" type="text/css" />
        <!--<![endif]-->
    </head>
    <body <?php echo is_rtl() ? 'rightmargin' : 'leftmargin'; ?>="0" marginwidth="0" topmargin="0" marginheight="0" offset="0" bgcolor="<?php echo esc_attr( $surface_alt ); ?>">
        <?php if ( '' !== $preheader ) : ?>
            <div class="lytrod-preheader" style="font-size:1px;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;mso-hide:all;color:transparent;">
                <?php echo esc_html( $preheader ); ?>
                <?php // Spacer entities stop clients pulling body copy into the preview line. ?>
                &#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;
            </div>
        <?php endif; ?>
        <table width="100%" cellpadding="0" cellspacing="0" border="0" id="outer_wrapper" role="presentation" bgcolor="<?php echo esc_attr( $surface_alt ); ?>">
            <tr>
                <td><!-- Deliberately empty to support consistent sizing and layout across multiple email clients. --></td>
                <td width="<?php echo esc_attr( $width ); ?>">
                    <div id="wrapper" dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">
                        <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="inner_wrapper" role="presentation">
                            <tr>
                                <td align="center" valign="top">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_container" role="presentation" bgcolor="<?php echo esc_attr( $surface ); ?>">
                                        <tr>
                                            <?php // Brand rail across the top edge of the card. ?>
                                            <td id="lytrod_rail" height="4" bgcolor="<?php echo esc_attr( $primary ); ?>" style="height:4px;line-height:0;font-size:0;">&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td align="center" valign="top">
                                                <!-- Header -->
                                                <table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_header" role="presentation" bgcolor="<?php echo esc_attr( $surface ); ?>">
                                                    <tr>
                                                        <td id="template_header_image">
                                                            <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
                                                                <tr>
                                                                    <td align="<?php echo is_rtl() ? 'right' : 'left'; ?>" valign="middle">
                                                                        <?php
                                                                        $logo_html = sprintf(
                                                                            '<img src="%1$s" alt="%2$s" width="%3$d" style="display:block;border:0;width:%3$dpx;max-width:%3$dpx;height:auto;" />',
                                                                            esc_url( Lytrod_Emails_Brand::logo_url() ),
                                                                            esc_attr( $store_name ),
                                                                            $logo_width
                                                                        );

                                                                        if ( $header_image_url ) {
                                                                            printf(
                                                                                '<a href="%1$s" target="_blank" style="display:inline-block;text-decoration:none;">%2$s</a>',
                                                                                esc_url( $header_image_url ),
                                                                                $logo_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from esc_url()/esc_attr() above.
                                                                            );
                                                                        } else {
                                                                            echo $logo_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from esc_url()/esc_attr() above.
                                                                        }
                                                                        ?>
                                                                    </td>
                                                                    <?php if ( ! empty( $pill['label'] ) ) : ?>
                                                                        <td align="<?php echo is_rtl() ? 'left' : 'right'; ?>" valign="middle">
                                                                            <?php echo lytrod_emails_pill( (string) $pill['label'], (string) ( $pill['tone'] ?? 'accent' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally. ?>
                                                                        </td>
                                                                    <?php endif; ?>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td id="header_wrapper"<?php echo '' === $email_heading ? ' style="padding-top:0;padding-bottom:0;"' : ''; ?>>
                                                            <?php if ( '' !== $email_heading ) : ?>
                                                                <h1><?php echo esc_html( $email_heading ); ?></h1>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <!-- End Header -->
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center" valign="top">
                                                <!-- Body -->
                                                <table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_body" role="presentation">
                                                    <tr>
                                                        <td valign="top" id="body_content" bgcolor="<?php echo esc_attr( $surface ); ?>">
                                                            <!-- Content -->
                                                            <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
                                                                <tr>
                                                                    <td valign="top" id="body_content_inner_cell">
                                                                        <div id="body_content_inner">
