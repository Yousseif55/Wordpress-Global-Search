<?php
/* 
Plugin Name: Global Search
Description: Global search is a powerful global utility search plugin for WordPress Dashboard - it is an advancement of the default WordPress dashboard search.
Author: Yousseif Ahmed
Version: 1.1.1

*/

if (!defined('WPINC')) {
    die;
}

class codecruze
{

    public function __construct()
    {
        $this->init();
    }

    private function init()
    {
        $this->constants();
        $this->hooks();
        $this->add_action();
    }

    private function constants()
    {
        define('CODECRUZE_SEARCH_VERSION', '1.1.1');
        define('CODECRUZE_SEARCH_NAME', 'codecruze-search');
        define('CODECRUZE_SEARCH_URL', plugin_dir_url(__FILE__));
        define('CODECRUZE_SEARCH_DIR', dirname(__FILE__));
        define('CODECRUZE_SEARCH__FILE__', __FILE__);
        define('CODECRUZE_SEARCH_PLUGIN_BASE', plugin_basename(CODECRUZE_SEARCH__FILE__));
    }

    private function hooks()
    {

        require_once CODECRUZE_SEARCH_DIR . '/includes/core.php';

        register_activation_hook(__FILE__, array($this, 'activation'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

    }

    public function activation()
    {
        set_transient('_codecruze_setting_redirect_on_activation', true, 30);
    }

    public function deactivate()
    {

    }

    private function add_action()
    {
        add_action('admin_notices', array($this, 'admin_notices'));
        add_action('admin_menu', array($this, 'codecruze_search_menu'));
        add_action('admin_enqueue_scripts', array($this, 'codecruze_enqueue'));
        add_action('wp_before_admin_bar_render', array($this, 'codecruze_add_toolbar_items'), 999999999);
        add_action('admin_footer', array($this, 'send_source_to_admin'), 999999999);
        add_action('admin_init', array($this, 'setting_page_redirect_on_activation'));
        add_action('admin_bar_menu', array($this, 'codecruze_add_toolbar_items'), 999999999);

    }

    public function codecruze_search_menu()
    {
        add_menu_page('Global Search Setting', 'Global Search', 'manage_options', 'codecruze_search_menu', array($this, 'codecruze_search_menu_page'), 'dashicons-search');
    }

    public function codecruze_search_menu_page()
    {
        CodeCruze_Core::codecruze_save_settings($_POST);
        CodeCruze_Core::codecruze_save_admin_notice();
        CodeCruze_Core::codecruze_save_update_notice();
        require_once dirname(__FILE__) . '/admin/view/settings.php';
    }

    public function codecruze_add_toolbar_items($admin_bar)
    {
        global $wp_admin_bar;
        $form = '<div id="searchContainer" class="ui category search focus" style="background-color: rgba(0, 0, 0, 0);position: relative;">
                  <div class="ui left icon input">
                    <input class="prompt" type="text" id="codecruze_search_box" autocorrect="on" placeholder="ctrl + f to search ..." autofocus style="border-radius: 20px !important; line-height: 12px !important;">
                    <div style="right:0; padding-right:13px; height:15px;position:absolute;opacity: .5;">
                    <input class="ui radio checkbox"  type="checkbox" style="height:15px; border-radius: 20px" ' . (get_option('codecruze_quick_search_status') === 'true' ? 'checked' : '') . ' id="quick_search_checkbox"></div>
                    <img src="' . CODECRUZE_SEARCH_URL . '/assets/images/search.svg" style="height: 13px;padding: 11px;position: absolute;opacity: .5;">
                  </div>
                </div>
                ';
        $wp_admin_bar->add_menu(
            array(
                'id' => 'codecruze-search',
                'title' => $form,
                'meta' => array(
                    'title' => __('Codcruze Search'),
                ),
            )
        );
        echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            var checkbox = document.getElementById("quick_search_checkbox");
            var searchContainer = document.getElementById("searchContainer");
            checkbox.addEventListener("change", function() {
                searchContainer.classList.add("disabled");

                jQuery.ajax({
                    url: ajaxurl, 
                    type: "POST",
                    data: {
                        action: "save_quick_search_status",
                        quick_search_status: this.checked ? "true" : "false",
                    },
                    success: function() {
                        location.reload();
                    },
                });
            });
        });
    </script>';
    }

    public function codecruze_enqueue($hook)
    {

        wp_enqueue_script('codecruze_shortcut_js', plugin_dir_url(__FILE__) . 'assets/js/keyboardShortcut.js');
        wp_enqueue_script('codecruze_custom_script', plugin_dir_url(__FILE__) . 'assets/js/init.js');
        wp_enqueue_script('codecruze_sematic_js', plugin_dir_url(__FILE__) . 'assets/js/semantic.min.js');
        wp_enqueue_style('codecruze_sematic_css', plugin_dir_url(__FILE__) . 'assets/css/semantic.min.css');
        wp_enqueue_style('codecruze_setting_css', plugin_dir_url(__FILE__) . 'assets/css/settings.css');
    }

    public function admin_notices()
    {
        $admin_notices = CodeCruze_Core::codecruze_admin_notice();
        if ($admin_notices == false) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p>
                    <?php _e('Yay! You made your search smarter by installing <span style="font-weight: 700;">Global search.</span></a>', CODECRUZE_SEARCH_NAME); ?>
                </p>
            </div>
            <?php
        }

        $admin_notices = CodeCruze_Core::codecruze_update_notice();

        if ($admin_notices == false && !(isset($_GET['page']) && $_GET['page'] == 'codecruze_search_menu')) {
            ?>
            <div class="notice notice-success">
                <p>
                    <?php _e('Yay! The new option brings you to search.! Check your <span style="font-weight: 700;">Global search</span> preferences <a href="admin.php?page=codecruze_search_menu">here</a>', CODECRUZE_SEARCH_NAME); ?>
                </p>
            </div>
            <?php
        }


    }

    public function send_source_to_admin()
    {
        $data = CodeCruze_Core::get_search_content();

        ob_start()
            ?>
        <script type="text/javascript">
            var codecruze_full_menu = <?php echo json_encode($data) ?>;
        </script>
        <?php

        $content = ob_get_clean();
        print $content;
    }

    public function setting_page_redirect_on_activation()
    {
        if (!get_transient('_codecruze_setting_redirect_on_activation')) {
            return;
        }

        delete_transient('_codecruze_setting_redirect_on_activation');

        if (is_network_admin() || isset($_GET['activate-multi'])) {
            return;
        }

        wp_redirect(admin_url('admin.php?page=codecruze_search_menu'));
    }
}

new codecruze();

function save_quick_search_status()
{
    if (isset($_POST['quick_search_status'])) {
        $status = sanitize_text_field($_POST['quick_search_status']);
        update_option('codecruze_quick_search_status', $status);
    }

    wp_die(); // this is required to terminate immediately and return a proper response
}

add_action('wp_ajax_save_quick_search_status', 'save_quick_search_status');
