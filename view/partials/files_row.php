<?
    namespace directory_comparison;

    $file       = $changed_file->get_object();
	$file_path  = $file->get_path();
	$file_other = $changed_file->get_object_other();

	$reasons = array();

	if ($changed_file->has_reason('dne'))
	    $reasons[] = 'no';
    elseif ($changed_file->has_reason('new'))
        $reasons[] = 'new';

    if ($changed_file->has_reason('newer')) {
        $reasons[] = 'newer';
        $reasons[] = 'diff';
    } elseif ($changed_file->has_reason('older')) {
        $reasons[] = 'older';
        $reasons[] = 'diff';
    }
?>

<tr
    class="row-file <?= implode(' ', $reasons) ?>"
    data-types="<?= implode(',', $reasons) ?>"
    data-selected="false"
    data-file="<?= $file_path ?>"
>
	<td class="col-icon-dir-file nowrap">
		<? if ($file->is_dir()) : ?>
            <span class="icon icon-dir-file"><?= $svg['folder'] ?></span>
		<? elseif ($file->is_file()) : ?>
            <span class="icon icon-dir-file"><?= $svg['file'] ?></span>
		<? endif; ?>
        <div class="custom-checkbox-container">
            <label>
                <input class="file-checkbox" type="checkbox" name="file_paths[]" value="<?= $file_path ?>" />
                <span class="custom-checkbox"></span>
            </label>
        </div>
	</td>
	<td class="col-file-path" style="/*width:1px;white-space:nowrap;*/word-break:break-all;">
        <div class="file-path-scroll">
            <?= $file_path ?>
        </div>
    </td>
	<td class="dates <?= in_array('newer', $reasons) || in_array('older', $reasons)  ? 'diff-dates' : '' ?>" style="width:1px;white-space:nowrap;">
        <? if (!in_array('no', $reasons)) : ?>
            <table class="inner-table">
                <tr>
                    <td class="date date-stag" style="width:1px;white-space:nowrap;"><?= date('m/d/Y G:i:s', $file->get_date()) ?></td>
                    <td style="width:1px;white-space:nowrap;padding:0 12px 0 0;"><?= in_array('newer', $reasons) ? '>' : '' ?> <?= in_array('older', $reasons)  ? '<' : '' ?></td>
                </tr>
            </table>
        <? endif; ?>
	</td>
	<td style="width:1px;padding:0;"></td>
	<td class="col-action">
        <div class="listing-action" data-act="ignore" title="Ignore">
            <span class="icon action ignore"><?= $svg['ignore'] ?></span>
        </div>
	</td>
	<td class="col-action">
		<? if (in_array('new', $reasons)) : ?>
            <div class="listing-action" data-act="delete" title="Delete From <?= $from == 'prod' ? 'Production' : 'Staging' ?>">
                <span class="icon action delete"><?= $svg['trash'] ?></span>
            </div>
		<? endif; ?>
	</td>
	<td class="col-action">
		<? if (in_array('new', $reasons) || in_array('older', $reasons)  || in_array('newer', $reasons)) : ?>
            <div class="listing-action" data-act="push" title="Push To <?= $from == 'prod' ? 'Staging' : 'Production' ?>">
                <span class="icon action push"><?= $svg['arrow'] ?></span>
            </div>
		<? endif; ?>
	</td>
</tr>