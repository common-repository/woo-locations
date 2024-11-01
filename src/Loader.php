<?php
namespace WcLocations;


use Automattic\WooCommerce\Utilities\FeaturesUtil;

class Loader
{
    public static function load($pluginFile): void
    {
        if (!self::$loaded) {
            new self($pluginFile);
            self::$loaded = true;
        }
    }

    public function __construct(string $pluginFile)
    {
        require_once(__DIR__.'/functions.php');
        spl_autoload_register(array($this, 'loadClass'));

        self::declareHposCompat($pluginFile);
        self::loadTextDomain($pluginFile);
        $this->registerStates();
        if (is_admin()) {
            $this->registerSettings();
        }
    }

    public function loadClass($class): void
    {
        $replaced = 0;
        $class = preg_replace('/^WcLocations\\\\/', __DIR__.'/', $class, -1, $replaced);
        if ($replaced) {
            $class = str_replace('\\', '/', $class) . '.php';
            require($class);
        }
    }



    private $buckets;

    private function getBuckets(): Buckets
    {
        if (!isset($this->buckets)) {
            $this->buckets = new Buckets(__DIR__.'/../locations', 'wl_active_buckets');
        }

        return $this->buckets;
    }

    private function registerStates(): void
    {
        add_filter('woocommerce_states', function(array $states) {
            foreach ($this->getBuckets()->loadActive() as $bucket) {
                $states[$bucket['country']] = array_merge((array)@$states[$bucket['country']], $bucket['items']);
            }
            return $states;
        });
    }

    private function registerSettings(): void
    {
        add_action('woocommerce_init', function() {
            include_once(WC()->plugin_path().'/includes/admin/settings/class-wc-settings-page.php');
            new Settings($this->getBuckets());
        });
    }


    private static $loaded = false;

    private static function declareHposCompat(string $pluginFile): void {
        add_action('before_woocommerce_init', static function() use($pluginFile) {
            if (class_exists(FeaturesUtil::class)) {
                FeaturesUtil::declare_compatibility('custom_order_tables', $pluginFile, true);
            }
        });
    }

    private static function loadTextDomain(string $pluginFile): void {
        add_action('init', static function() use($pluginFile) {
            load_plugin_textdomain('woo-locations', false, basename(dirname($pluginFile)).'/languages/');
        }, 9999);
    }
}