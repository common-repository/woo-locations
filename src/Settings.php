<?php
namespace WcLocations;

use WC_Settings_Page;


class Settings extends WC_Settings_Page
{
    public function __construct(Buckets $buckets)
    {
        $this->id = 'locations';
        $this->label = __('Locations', 'wcloc');

        $this->buckets = $buckets;

        parent::__construct();

        add_filter('woocommerce_settings_tabs_array', array($this, 'add_settings_page'), 100);
        add_filter('plugin_action_links_woo-locations/woo-locations.php', array($this, 'addSettingsLink'));
    }

    public function add_settings_page($pages)
    {
        unset($pages[$this->id]);
        $pages[$this->id] = $this->label;
        return $pages;
    }

    public function addSettingsLink($links)
    {
        $url = admin_url('admin.php?page=wc-settings&tab='.urlencode($this->id));
        array_unshift($links, '<a href="'.esc_html($url).'">'.__('Settings').'</a>');
        return $links;
    }

    public function output(): void
    {
        $activeBucketIds = $this->buckets->getActive();
        $buckets = $this->buckets->loadMenu();
        include(__DIR__.'/../assets/tpl.php');
    }

    public function save(): void
    {
        if (!($buckets = wp_unslash(@$_POST['wl_active_buckets'])) ||
            !is_array($buckets = json_decode($buckets, true))) {
            return;
        }

        $this->buckets->setActive($buckets);
    }

    private $buckets;
}