<?php
namespace WcLocations;


class Buckets
{
    public function __construct($dir, $option)
    {
        $this->dir = $dir;
        $this->option = $option;
    }

    /**
     * @return array
     */
    public function loadActive()
    {
        return $this->loadAndFilter($this->getActive(), false, false);
    }

    /**
     * @return array
     */
    public function loadMenu()
    {
        $buckets = $this->loadAndFilter(null, true, false, $this->getActive());

        uasort($buckets, function($a, $b) {
            return
                4 * ((int)(bool)@$a['obsolete'] - (int)(bool)@$b['obsolete']) +
                2 * ((int)(bool)@$a['legacy'] - (int)(bool)@$b['legacy']) +
                1 * sign(strcasecmp($a['title'], $b['title']));
        });

        return $buckets;
    }

    /**
     * @return string[]
     */
    public function getActive()
    {
        $ids = get_option($this->option, null);
        if (!isset($ids)) {
            $ids = array_keys($this->loadAndFilter(null, true, true));
            update_option($this->option, $ids);
        }

        $ids = apply_filters('wl_active_buckets', $ids, $this);

        return $ids;
    }

    /**
     * @param string[]|null $ids
     */
    public function setActive(array $ids = null)
    {
        update_option($this->option, $ids);
    }


    private $dir;
    private $option;

    private function getFile($id)
    {
        return apply_filters('wl_bucket_file', "{$this->dir}/{$id}.php", $id, $this);
    }

    /**
     * @param string[]|null $ids
     * @param bool $skipObsolete
     * @param bool $skipLegacy
     * @return array
     */
    private function loadAndFilter($ids, $skipObsolete, $skipLegacy, array $preserveIds = array())
    {
        $buckets = array();

        if (!isset($ids)) {
            $ids = $this->loadAllIds();
        }

        foreach ($ids as $id) {

            $bucket = $this->loadOne($id);

            if (!in_array($id, $preserveIds, true)) {
                if ($skipObsolete && !empty($bucket['obsolete'])) {
                    continue;
                }
                if ($skipLegacy && !empty($bucket['legacy'])) {
                    continue;
                }
            }

            $buckets[$id] = $bucket;
        }

        return $buckets;
    }

    /**
     * @return string[]
     */
    private function loadAllIds()
    {
        $ids = array();
        foreach (glob($this->getFile('*')) as $file) {
            $ids[] = pathinfo($file, PATHINFO_FILENAME);
        }

        $ids = apply_filters('wl_available_buckets', $ids, $this);

        return $ids;
    }

    /**
     * @param string $id
     * @return array|null
     */
    private function loadOne($id)
    {
        if (!file_exists($file = $this->getFile($id))) {
            return null;
        }

        /** @noinspection PhpIncludeInspection */
        if (!($bucket = include($file))) {
            return null;
        }

        $bucket = apply_filters('wl_load_bucket', $bucket, $id, $this);

        $bucket['obsolete'] =
            (isset($bucket['obsolete']) && $bucket['obsolete']) ||
            (isset($bucket['obsolete_since']) && version_compare(wc()->version, $bucket['obsolete_since'], '>='));

        $bucket['title'] = __($bucket['title'], 'wcloc');

        foreach ($bucket['items'] as &$item) {
            $item = __($item, 'wcloc');
        }

        return $bucket;
    }
}