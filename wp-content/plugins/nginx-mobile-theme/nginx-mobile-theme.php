<?php
/*
Plugin Name: Nginx Mobile Theme
Plugin URI: http://ninjax.cc/
Description: This plugin allows you to switch theme according to the User Agent on the Nginx reverse proxy.
Author: miyauchi, megumithemes
Version: 1.6.0
Author URI: http://ninjax.cc/

Copyright 2013 Ninjax Team (email : info@ninjax.cc)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require(dirname(__FILE__).'/vendor/autoload.php');

$nginx_mobile_theme = new Nginx_Mobile_Theme();
$nginx_mobile_theme->init();

class Nginx_Mobile_Theme{

private $mobile_detects = array('@smartphone');
private $nginxcc = 'nginx-champuru/nginx-champuru.php';

public function init()
{
    add_action('plugins_loaded', array($this, 'plugins_loaded'), 9999);
}

/**
 * Fires when plugins_loaded hook.
 *
 * @access public
 * @since  1.1.0
 */
public function plugins_loaded()
{
    if (is_admin()) {
        add_action('admin_init', array($this, 'admin_init'));
        add_action(
            'customize_controls_print_scripts',
            array($this, 'customize_controls_print_scripts'),
            9999
        );
    }

    if (defined('IS_AMIMOTO') && IS_AMIMOTO === true) {
        $this->amimoto_support(); // see http://megumi-cloud.com/
    }

    if (!has_filter('nginxmobile_mobile_themes')) {
        add_action('customize_register', array($this, 'customize_register'));
    }

    $mobile_detect = $this->mobile_detect();
    if ($mobile_detect) {
        $mobile_theme = get_option("nginxmobile_mobile_themes");
        /**
         * Filter the theme slug for mobile
         *
         * @since 1.0.0
         * @param string $mobile_theme theme slug
         */
        $mobile_theme = apply_filters('nginxmobile_mobile_themes', $mobile_theme);
        $detect = str_replace('@', '', $mobile_detect);
        if (isset($mobile_theme[$detect]) && $mobile_theme[$detect]) {
            $this->switch_theme($mobile_theme[$detect]);
        }
        add_filter(
            'nginxchampuru_get_the_url',
            array($this, 'nginxchampuru_get_the_url')
        );
    } elseif (is_user_logged_in()) { // theme preview
        if (isset($_GET['nginx-mobile-theme']) && $_GET['nginx-mobile-theme']) {
            if (preg_match('/^[a-zA-Z0-9\-]+$/', $_GET['nginx-mobile-theme'])) {
                $this->switch_theme($_GET['nginx-mobile-theme']);
                add_filter('home_url', array($this, 'home_url'));
            }
        }
    }
}

/**
 * Filter the url to preview url.
 *
 * @access public
 * @since  1.2.0
 */
public function home_url($url)
{
    if (is_user_logged_in()) { // theme preview
        if (isset($_GET['nginx-mobile-theme']) && $_GET['nginx-mobile-theme']) {
            if (preg_match('/^[a-zA-Z0-9\-]+$/', $_GET['nginx-mobile-theme'])) {
                return add_query_arg(
                    array(
                        'nginx-mobile-theme' => $_GET['nginx-mobile-theme']
                    ),
                    $url
                );
            }
        }
    }
    return $url;
}

/**
 * Register script in the head of wp-admin/customize.php
 *
 * @access public
 * @since  1.2.0
 */
public function customize_controls_print_scripts()
{
?>
<script type="text/javascript">
jQuery(document).ready(function(){
    var $ = jQuery;
    $('.theme-preview').click(function(){
        var theme = $('select:first', $(this).parent().parent()).val();
        window.open().location.href = '<?php echo home_url('/'); ?>?nginx-mobile-theme='+theme;
    });
});
</script>
<?php
}

/**
 * Register action to the admin_notice when Nginx Cache Controller is not activated.
 *
 * @access public
 * @since  1.1.0
 */
public function admin_init()
{
    if (function_exists('is_plugin_inactive') && is_plugin_inactive($this->nginxcc)) {
        add_action('admin_notices', array($this, 'admin_notice'));
    }
}

/**
 * Warning Nginx Cache Controller is not activated.
 *
 * @access public
 * @since  1.0.0
 */
public function admin_notice()
{
    $install_url = admin_url('plugin-install.php?tab=search&s=nginx-champuru&plugin-search-input=Search+Plugins');
    ?>
    <div class="error">
        <p>Nginx Mobile Theme requires <strong>Nginx Cache Controller</strong>.
            <a href="<?php echo $install_url; ?>">Please click to install.</a></p>
    </div>
    <?php
}

/**
 * Register theme-selector to the theme-customizer.
 *
 * @access public
 * @since  1.0.0
 */
public function customize_register($wp_customize)
{
    $all_themes = wp_get_themes();
    $themes = array();
    foreach ($all_themes as $theme_name => $theme) {
        $themes[$theme_name] = $theme->get('Name');
    }

    $wp_customize->add_section('nginxmobile', array(
        'title'          => 'Mobile Theme',
        'priority'       => 9999,
    ));

    foreach ($this->get_mobile_detects() as $detect) {
        $detect = esc_html(str_replace('@', '', $detect));
        $current_theme = wp_get_theme();
        $wp_customize->add_setting('nginxmobile_mobile_themes['.$detect.']', array(
            'default'        => $current_theme->get_stylesheet(),
            'type'           => 'option',
            'capability'     => 'switch_themes',
        ));

        if ($detect === 'ktai') { // amimoto fix
            if (defined('WP_LANG') && WP_LANG === 'ja') {
                $label = ucfirst($detect).' theme';
            } else {
                $label = 'Cell-Phone theme';
            }
        } else {
            $label = ucfirst($detect).' theme';
        }

        $wp_customize->add_control(new Megumi_ThemeCustomizerControl(
            $wp_customize,
            'nginxmobile_mobile_themes-'.$detect,
            array(
                'settings'    => 'nginxmobile_mobile_themes['.$detect.']',
                'label'       => $label,
                'section'     => 'nginxmobile',
                'type'        => 'select',
                'choices'     => $themes,
                'label_after' => '<a href="javascript:void(0);" class="theme-preview">Theme Preview</a>',
            )
        ));
    }
}

/**
 * Filter for the nginxchampuru_get_the_url hook in the Nginx Cache Controller plugin.
 *
 * @access public
 * @since  1.0.0
 */
public function nginxchampuru_get_the_url($url)
{
    $mobile_detect = $this->mobile_detect();
    return sprintf(
        /**
         * Filter the proxy key for reverse proxy.
         *
         * @since 1.0.0
         * @param string $proxy_key An string for proxy key.
         * @param string $url       An original URL.
         */
        apply_filters(
            "nginxmobile_proxy_key",
            '%s'.str_replace('%', '%%', $url),
            str_replace('%', '%%', $url)
        ),
        $mobile_detect
    );
}

/**
 * Return an array of mobile detects with filter.
 *
 * @access private
 * @since  1.0.0
 */
private function get_mobile_detects()
{
    /**
     * Filter the mobile detects
     *
     * @since 1.0.0
     * @param array $mobile_detects An array of determined result of user agent
     */
    return apply_filters("nginxmobile_mobile_detects", $this->mobile_detects);
}

/**
 * Switch theme to $theme.
 *
 * @access private
 * @since  1.0.0
 */
private function switch_theme($theme)
{
    $switch_theme = new Megumi_SwitchTheme($theme);
    $switch_theme->apply();
}

/**
 * Return the determined user-agent from environments with filter.
 *
 * @access public
 * @since  1.0.0
 */
public function mobile_detect()
{
    $mobile_detect = '';

    if (isset($_SERVER['HTTP_X_UA_DETECT']) && $_SERVER['HTTP_X_UA_DETECT']) {
        $mobile_detect = $_SERVER['HTTP_X_UA_DETECT'];
    }

    /**
     * Filter the determined user-agent from nginx
     *
     * @since 1.0.0
     * @param string $mobile_detect  e.g. "@smartphone"
     */
    return apply_filters("nginxmobile_mobile_detect", $mobile_detect);
}

/**
 * Some functions for support Amimoto AMI.
 *
 * @access private
 * @since  1.0.0
 */
private function amimoto_support()
{
    if (defined('IS_AMIMOTO') && IS_AMIMOTO === true) {
        add_filter('nginxmobile_proxy_key', function($key, $url){
            return $url.'%s';
        }, 10, 2);
        add_filter('nginxmobile_mobile_detects', function(){
            return array('@ktai', '@smartphone');
        });
    }
}

} // end class


// EOF
