<? namespace directory_comparison; ?>

<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;700&family=Roboto:ital,wght@0,300;0,400;0,700;1,400&display=swap" rel="stylesheet">

<meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?>">

<style>
	<? require_once THIS_DIR.'/src/css/style.css'; ?>
    <? require_once THIS_DIR.'/src/css/modal.css'; ?>

    <?
        if (THEME) {
            if (file_exists($theme_path = (THIS_DIR.'/src/css/themes/'.THEME.'.css')))
                require_once $theme_path;
            else
                \alerts::push('Theme file <b>'.THEME.'.css</b> does not exist', 'Theme Error', 'error');
        }
    ?>
</style>

<div class="directory-comparison">
    <div class="dc-header">
        <div class="dc-header-left">
            <h1 class="dc-header-title">Directory Comparison</h1>
            <div class="dc-version"><?= version ?></div>
        </div>
        <div class="dc-header-right">
            <div class="dc-header-refresh">
                <span id="refresh" class="icon action push" style="font-size:24px;cursor:pointer;"><?= get_svg_icon('sync-alt') ?></span>
            </div>
            <div class="dc-header-last-updated">
                <div>Last Updated</div>
                <div><b><span id="last_updated"><?= date('m-d-Y g:ia') ?></span></b></div>
            </div>
        </div>
    </div>

    <? $critical = \alerts::are_critical(); ?>

    <?= \alerts::get(); ?>

    <? if (!$critical) : ?>

    <div class="listing">

        <div id="listing_files">

            <div class="listing-files-grid">

                <? foreach (['stag','prod'] as $from) : ?>

                    <div class="listing-files-grid-box">

                        <form method="post" id="listing_form_<?= $from ?>" class="listing-form">
                            <input type="hidden" name="from" value="<?= $from ?>" />
                            <input type="hidden" name="load_all" value="0" id="load_all" />

                            <div>
                                <label for="filter_bulk_<?= $from ?>" style="display:none;">Bulk Actions:</label>
                                <select class="filter-bulk" id="filter_bulk_<?= $from ?>" name="bulk_action" disabled>
                                    <option value="">-- Bulk Actions --</option>
                                    <option value="push" disabled>Push</option>
                                    <option value="delete" disabled>Delete</option>
                                    <option value="ignore">Ignore</option>
                                </select>
                                <input class="bulk-sub" type="submit" name="sub_bulk" value="Submit" disabled />
                            </div>

                            &nbsp;

                            <div class="listing-files" id="listing_files_<?= $from ?>"></div>

                        </form>

                    </div>

                <? endforeach; ?>

            </div>

        </div>

        &nbsp;

        <div id="listing_files_ignored"></div>

        &nbsp;

        <div id="listing_files_pushed"></div>

        &nbsp;

        <? require_once THIS_DIR.'/view/notes.php' ?>
    </div>

    <? require_once THIS_DIR.'/view/modal_ignore.html' ?>

</div>

<script>
    <? require_once THIS_DIR.'/src/js/modal.js'; ?>
    <? require_once THIS_DIR.'/src/js/javascript.js.php'; ?>
</script>

<? endif; ?>