<meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?>">

<style>
    <? require_once THIS_DIR.'/src/css/fontawesome.v4.7.0.css'; ?>
	<? require_once THIS_DIR.'/src/css/style.css'; ?>
    <? require_once THIS_DIR.'/src/css/modal.css'; ?>
</style>

<div class="ca title_box" style="margin-bottom:0;">
    <div class="l">
        <h1>Directory Comparison</h1>
    </div>
    <div style="display:flex;justify-content:center;align-items:center;" class="r tr">
        <div class="l p_r">
            <i id="refresh" style="font-size:24px;cursor:pointer;" class="icon action push fa fa-refresh" aria-hidden="true"></i>
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

&nbsp;

<div class="listing">
    <div id="listing_files" class="listing-files">

        <div class="w_50 l p_r p_10">
            <div id="listing_files_stag"></div>
        </div>

        <div class="w_50 l p_l p_10">
            <div id="listing_files_prod"></div>
        </div>

        <div class="c"></div>

        <div style="display:flex;justify-content:center;">
            <div class="load-more" id="load_more_files" data-i="<?= LIMIT_FILES ?>">
                <i class="fa fa-angle-double-down" aria-hidden="true"></i>
                Load More
                <i class="fa fa-angle-double-down" aria-hidden="true"></i>
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