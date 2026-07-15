<?php if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Blacklisted File Extensions for Uploads
 *
 * Comprehensive list of extensions that could be executed as code
 * on common server configurations.
 *
 * @since 3.3.26
 */
return array(
    // PHP variants
    '.php',
    '.php3',
    '.php4',
    '.php5',
    '.php7',
    '.php8',
    '.phtml',
    '.phar',
    '.phps',

    // ASP/JSP
    '.asp',
    '.aspx',
    '.jsp',
    '.jspx',

    // CGI/Scripts
    '.cgi',
    '.pl',
    '.py',
    '.rb',

    // Shell scripts
    '.sh',
    '.bash',
    '.zsh',
    '.ksh',

    // Windows executables
    '.bat',
    '.cmd',
    '.com',
    '.exe',
    '.msi',
    '.scr',

    // PowerShell
    '.ps1',
    '.psm1',
    '.psd1',

    // Server configuration
    '.htaccess',
    '.htpasswd',

    // Server-side includes
    '.shtml',
    '.shtm',
    '.stm',

    // Windows scripting
    '.hta',
    '.vbs',
    '.vbe',
    '.wsf',
    '.wsh',

    // Java
    '.jar',
    '.war',

    // ColdFusion
    '.cfm',
    '.cfml',
);
