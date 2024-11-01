                <style>
                    <?php include(__DIR__.'/style.css') ?>
                </style>

                <br>

                <a class="wl-select-all" href="#" id="wl_select_all">Select all</a>
                <span id="wc_select_delimiter">/</span>
                <a class="wl-select-none" href="#" id="wl_select_none">none</a>

                <ul class="menu nav-menus-php wl-menu">
<?php /** @noinspection PhpUndefinedVariableInspection */
foreach ($buckets as $bucketId => $bucket): ?>
                    <li class="menu-item menu-item-edit-inactive <?php echo esc_html((!empty($bucket['obsolete']) || !empty($bucket['legacy'])) ? 'menu-item--obsolete' : null) ?>">
                        <div class="menu-item-bar">
                            <div class="menu-item-handle">
                                <input class="item-enable" type="checkbox" data-wl-bucket-id="<?php echo esc_html($bucketId) ?>">
                                <span class="item-title">
                                    <span class="menu-item-title">
                                        <?php echo esc_html(@$bucket['title'] ?: $bucketId) ?>
                                        <?php if (!empty($bucket['obsolete'])): ?>
                                            (overrides built-in states)
                                        <?php endif; ?>
                                        <?php if (!empty($bucket['legacy'])): ?>
                                            (legacy version)
                                        <?php endif; ?>
                                    </span>
                                </span>
                                <span class="item-controls">
                                    <a class="item-edit">&lt;&gt;</a>
                                </span>
                            </div>
                        </div>

                        <div class="menu-item-settings">
                            <ul>
    <?php foreach ($bucket['items'] as $item): ?>
                                <li>
                                    <?php echo esc_html($item) ?>
                                </li>
    <?php endforeach; ?>
                            </ul>
                        </div>
                    </li>
<?php endforeach; ?>
                </ul>

                <input type="hidden" name="wl_active_buckets" id="wl_active_buckets"
                       value="<?php /** @noinspection PhpUndefinedVariableInspection */
                                echo esc_html(json_encode($activeBucketIds)) ?>"
                >

<script>
    (function($) {
        'use strict';

        const $form = $('#mainform');
        const $settings = $form.find('#wl_active_buckets');
        const $checkboxes = $form.find('.item-enable');

        const activeLocationSets = JSON.parse($settings.val());
        $checkboxes.each(function() {
            this.checked = activeLocationSets.indexOf($(this).data('wl-bucket-id')) > -1;
        });

        $form.submit(function() {
            $settings.val(JSON.stringify($checkboxes.map(function() {
                return this.checked ? $(this).data('wl-bucket-id') : null;
            }).get()));
        });

        $form.find('.menu-item-handle').click(function(e) {

            if ($(e.target).is(':input')) {
                return;
            }

            const $item = $(this).closest('.menu-item');
            $item.find('.menu-item-settings').slideToggle();
            $item.toggleClass('menu-item-edit-active menu-item-edit-inactive');

            return false;
        });


        const $selectAll = $form.find('#wl_select_all');
        const $selectNone = $form.find('#wl_select_none');
        const $selectDelimiter = $form.find('#wc_select_delimiter');

        const updateSelectAllNone = function() {

            let allSelected = true;
            let noneSelected = true;
            $checkboxes.each(function() {
                allSelected = allSelected && this.checked;
                noneSelected = noneSelected && !this.checked;
                if (!allSelected && !noneSelected) return false;
            });

            $selectAll.toggle(!allSelected);
            $selectNone.toggle(!noneSelected);
            $selectDelimiter.toggle(!allSelected && !noneSelected);
            $selectNone.text(!allSelected ? 'none' : 'Select none');
        };

        $selectAll.add($selectNone).click(function(e) {
            e.preventDefault();
            $checkboxes.prop('checked', this === $selectAll[0]);
            updateSelectAllNone();
        });

        $checkboxes.change(function() {
            updateSelectAllNone();
        });

        updateSelectAllNone();
    })(jQuery);
</script>