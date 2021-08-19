<form method="post" id="listing_form_<?= $from ?>" class="listing-form">
    <input type="hidden" name="from" value="<?= $from ?>" />

    <div>
        <label for="filter_bulk_<?= $from ?>" style="display:none;">Bulk Actions:</label>
        <select class="filter-bulk" id="filter_bulk_<?= $from ?>" name="bulk_action" disabled>
            <option value="">-- Bulk Actions --</option>
            <option value="push" disabled>Push</option>
            <option value="delete" disabled>Delete</option>
            <option value="ignore" disabled>Ignore</option>
        </select>
        <input class="bulk-sub" type="submit" name="sub_bulk" value="Submit" disabled />
    </div>

    &nbsp;

    <table class="table <?= $position ?>">
        <thead>
            <tr>
                <td colspan="9999">
                    <?= $header ?>

                    <? if ($from == 'stag' && $allow_push) : ?>
                    <div class="r">
                        <input type="submit" name="sub_push_all_newer" value="Push New and Recently Modified" title="Pushes only new files and files that have newer modified dates" />
                        &nbsp;
                        <input type="submit" name="sub_push_all" value="Push" title="Pushes all files. Does not delete files." />
                    </div>
                    <? endif; ?>

                    <? if ($from == 'prod' && $allow_push) : ?>
                        <div class="r">
                            &nbsp;
                            <input type="submit" name="sub_sync" value="Sync Staging" title="Pushes files and deletes files on staging that do not exist on production." />
                        </div>
                    <? endif; ?>
                </td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="category"></td>
                <td class="category">File</td>
                <td class="category">Date Modified</td>
                <td class="category"></td>
                <td class="category"></td>
                <td class="category"></td>
                <td class="category"></td>
            </tr>
            <? if ($changed_files) : ?>
                <?= listing_rows($changed_files, $from) ?>
            <? else : ?>
                <tr>
                    <td colspan="9999"><i>No changed files.</i></td>
                </tr>
            <? endif; ?>
        </tbody>
    </table>
</form>