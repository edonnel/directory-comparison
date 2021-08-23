<meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?>">

<style>
	<? require_once THIS_DIR.'/src/css/style.css'; ?>
    <? require_once THIS_DIR.'/src/css/modal.css'; ?>

    <?
        if (THEME) {
            if (file_exists($theme_path = (THIS_DIR.'/src/css/themes/'.THEME.'.css')))
                require_once $theme_path;
            else
                push_alert('Theme file <b>'.THEME.'.css</b> does not exist', 'Theme Error', 'error');
        }
    ?>
</style>

<div class="ca title_box" style="margin-bottom:0;">
    <div class="l">
        <h1>Directory Comparison</h1>
    </div>
    <div style="display:flex;justify-content:center;align-items:center;" class="r tr">
        <div class="l p_r">
            <span id="refresh" class="icon action push" style="font-size:24px;cursor:pointer;"><?= get_svg_icon('sync-alt') ?></span>
        </div>
        <div class="l" style="color:rgba(0,0,0,0.75);">
            <div>Last Updated</div>
            <div class="l"><b><span id="last_updated"><?= date('m-d-Y g:ia') ?></span></b></div>
        </div>
    </div>
</div>

<? $critical = alerts_are_critical(); ?>

<?= get_alerts(); ?>

<? if (!$critical) : ?>

<div class="listing">

    <div id="listing_files" class="listing-files-two-col">

        <div class="listing-files-grid">

            <div class="listing-files" id="listing_files_stag"></div>

            <div class="listing-files" id="listing_files_prod"></div>

        </div>

        <div style="display:flex;justify-content:center;">
            <div class="load-more" id="load_more_files" data-i="<?= LIMIT_FILES ?>">
                <div style="padding:10px 15px;">
                    <span class="icon"><?= get_svg_icon('angle-double-down') ?></span>
                    Load More
                    <span class="icon"><?= get_svg_icon('angle-double-down') ?></span>
                </div>
            </div>
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

<script>
    <? require_once THIS_DIR.'/src/js/modal.js'; ?>
    <? require_once THIS_DIR.'/src/js/javascript.js.php'; ?>
</script>

<? endif; ?>