<? namespace directory_comparison; ?>

<input type="hidden" id="<?= $from ?>_load_all" value="<?= $all_loaded ?>" />

<table class="listing-files-table dc-table <?= $position ?>" data-from="<?= $from ?>">
    <thead>
        <tr>
            <td colspan="9999">
                <?= $header ?>

                <? if ($from == 'stag' && $allow_push) : ?>
                <div style="float:right;">
                    <input type="submit" name="sub_push_all_newer" value="Push New and Recently Modified" title="Pushes only new files and files that have newer modified dates" />
                    &nbsp;
                    <input type="submit" name="sub_push_all" value="Push" title="Pushes all files. Does not delete files." />
                </div>
                <? endif; ?>

                <? if ($from == 'prod' && $allow_push) : ?>
                    <div style="float:right;">
                        &nbsp;
                        <input type="submit" name="sub_sync" value="Sync Staging" title="Pushes files and deletes files on staging that do not exist on production." />
                    </div>
                <? endif; ?>
            </td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="table-header"></td>
            <td class="table-header">File</td>
            <td class="table-header col-order order-date" data-type="date" data-value="<?= $order_type == 'date' ? $order_value : '' ?>">
                <span>Date Modified</span>
                <span class="col-order-icon">
                    <?
                        if ($order_value == 'asc')
                            echo $svg['sort_numeric_up_alt'];
                        elseif ($order_value == 'desc')
                            echo $svg['sort_numeric_down_alt'];
                    ?>
                </span>
            </td>
            <td class="table-header"></td>
            <td class="table-header"></td>
            <td class="table-header"></td>
            <td class="table-header"></td>
            <td class="table-header"></td>
        </tr>
        <? if ($changed_files) : ?>
            <?= listing_rows($changed_files, $from) ?>

            <? if ($limit) ?>
            <tr class="load-more" title="Load All">
                <td colspan="9999"><?= $svg['ellipsis'] ?></td>
            </tr>
        <? else : ?>
            <tr>
                <td colspan="9999"><i>No changed files.</i></td>
            </tr>
        <? endif; ?>
    </tbody>
</table>