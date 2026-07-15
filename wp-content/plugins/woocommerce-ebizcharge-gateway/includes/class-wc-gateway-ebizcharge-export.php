<?php
/**
 * class csv
 */

if (!defined( 'ABSPATH')) 
{
	exit;
}

/**
 * CSV Exporter bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @since             1.0.0
 * @package           CSV Export
 *
 * @wordpress-plugin
 * Plugin Name:       CSV Export
 * Plugin URI:        http://example.com/plugin-name-uri/
 * Description:       exports csvs derrr
 * Version:           1.0.0
 * Author:            Your Name or Your Company
 * Author URI:        http://example.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       csv-export
 * Domain Path:       /languages
 */
class CSVExport
{
    /**
     * Constructor
     */
    public function __construct() {
        if (isset($_GET['report'])) {

            $csv = $this->generate_csv();
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private", false);
            header("Content-Type: application/octet-stream");
            header("Content-Disposition: attachment; filename=\"report.csv\";");
            header("Content-Transfer-Encoding: binary");

            echo $csv;
            exit;
        }

// Add extra menu items for admins
        add_action('admin_menu', array($this, 'admin_menu'));

// Create end-points
        add_filter('query_vars', array($this, 'query_vars'));
        add_action('parse_request', array($this, 'parse_request'));
    }

    /**
     * Add extra menu items for admins
     */
    public function admin_menu() {
        add_menu_page('Download Report', 'Download Report', 'manage_options', 'download_report', array($this, 'download_report'));
    }

    /**
     * Allow for custom query variables
     */
    public function query_vars($query_vars) {
        $query_vars[] = 'download_report';
        return $query_vars;
    }

    /**
     * Parse the request
     */
    public function parse_request(&$wp) {
        if (array_key_exists('download_report', $wp->query_vars)) {
            $this->download_report();
            exit;
        }
    }

    /**
     * Download report
     */
    public function download_report() {
        echo '<div class="wrap">';
        echo '<div id="icon-tools" class="icon32">
</div>';
        echo '<h2>Download Report</h2>';
        echo '<p><a href="?page=download_report&report=users">Export the Subscribers</a></p>';
    }

    /**
     * Converting data to CSV
     */
    public function generate_csv() {
        global $wpdb;
        $csv_output = '';
        $table = 'wp_users';
        $query= "select * from wp_users";
        //$result = $wpdb->get_row($query, 'ARRAY_A');

        $i = 0;
//        if (count($result) > 0) {
//            foreach ($result as $row) {
//                $csv_output = $csv_output . $row['ID'] . ",";
//                $i++;
//            }
//        }
//        $csv_output .= "\n";
//
        $result = $wpdb->get_results($query, 'ARRAY_A');

        foreach ($result as $row) {
                $csv_output .= $row['user_email'] . ",";

            $csv_output .= "\n";
        }

        //return $csv_output;
        $csv = $csv_output;
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"report.csv\";");
        header("Content-Transfer-Encoding: binary");
        echo $csv;
        exit;
    }
}