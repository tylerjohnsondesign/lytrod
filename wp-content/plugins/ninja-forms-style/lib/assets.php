<?php if ( ! defined( 'ABSPATH' ) ) exit;

if ( class_exists( 'NF_Layout_Styles_Assets', false ) ) return;

/**
 * Shared asset version helper for the Layout & Styles add-on.
 *
 * Both NF_Layouts and NF_Styles previously enqueued their assets against a
 * hand-maintained class constant. That constant drifted behind the released
 * plugin version, so browsers kept serving cached CSS and JS after the files
 * themselves had changed. Deriving the version from the released plugin
 * version plus each file's own modification time means any content change
 * busts the cache, per file, without a constant to remember to bump.
 *
 * @since 3.0.30
 */
final class NF_Layout_Styles_Assets
{
    /**
     * Build a cache-busting version string for a plugin asset.
     *
     * @since 3.0.30
     * @param string $path     Absolute filesystem path to the asset.
     * @param string $fallback Version to use when the plugin version is unavailable.
     * @return string Version string suitable for wp_enqueue_style/script.
     */
    public static function version( $path, $fallback = '' )
    {
        $version = defined( 'NINJA_FORMS_STYLE_VERSION' ) ? NINJA_FORMS_STYLE_VERSION : $fallback;

        if ( $path && file_exists( $path ) ) {
            $modified = filemtime( $path );

            if ( $modified ) {
                $version = $version ? $version . '.' . $modified : (string) $modified;
            }
        }

        return $version ? $version : $fallback;
    }
}
