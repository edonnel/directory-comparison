<?
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
    class="file-row <?= implode(' ', $reasons) ?>"
    data-types="<?= implode(',', $reasons) ?>"
    data-selected="false"
    data-file="<?= $file_path ?>"
>
	<td class="row-icon-dir-file nowrap">
		<? if ($file->is_dir()) : ?>
			<i class="icon icon-dir-file fa fa-folder-open" aria-hidden="true"></i>
		<? elseif ($file->is_file()) : ?>
			<i class="icon icon-dir-file fa fa-file" aria-hidden="true"></i>
		<? endif; ?>
        <div class="custom-checkbox-container">
            <label>
                <input class="file-checkbox" type="checkbox" name="file_paths[]" value="<?= $file_path ?>" />
                <span class="custom-checkbox"></span>
            </label>
        </div>
	</td>
	<td style="/*width:1px;white-space:nowrap;*/word-break:break-all;"><?= $file_path ?></td>
	<td class="dates <?= in_array('newer', $reasons) || in_array('older', $reasons)  ? 'diff-dates' : '' ?>" style="width:1px;white-space:nowrap;">
		<table class="inner-table">
			<tr>
				<td class="date date-stag" style="width:1px;white-space:nowrap;"><?= date('m/d/Y h:i:s', $file->get_date()) ?></td>
				<td style="width:1px;white-space:nowrap;padding:0 12px 0 0;"><?= in_array('newer', $reasons) ? '>' : '' ?> <?= in_array('older', $reasons)  ? '<' : '' ?></td>
				<?/*<td class="date date-prod" style="width:1px;white-space:nowrap;"><?= $file_other ? date('m/d/Y h:i:s', $file_other->get_date()) : '' ?></td>*/?>
			</tr>
		</table>
	</td>
	<td></td>
	<td style="width:1px;white-space:nowrap;">
		<a href="<?= THIS_URL ?>&act=ignore&file=<?= $file_path ?>" title="Ignore">
			<i class="icon action ignore fa fa-ban" aria-hidden="true"></i>
		</a>
	</td>
	<td style="width:1px;white-space:nowrap;padding-left:0;">
		<? if (in_array('new', $reasons)) : ?>
			<a class="push-link" href="<?= THIS_URL ?>&act=delete&from=<?= $from ?>&file=<?= $file_path ?>" title="Delete From <?= $from == 'prod' ? 'Production' : 'Staging' ?>">
				<i class="icon action delete fa fa-trash" aria-hidden="true"></i>
			</a>
		<? endif; ?>
	</td>
	<td style="padding-left:0;">
		<? if (in_array('new', $reasons) || in_array('older', $reasons)  || in_array('newer', $reasons)) : ?>
			<a class="push-link" href="<?= THIS_URL ?>&act=push&from=<?= $from ?>&file=<?= $file_path ?>" title="Push To <?= $from == 'prod' ? 'Staging' : 'Production' ?>">
				<i class="icon action push fa fa-long-arrow-<?= $from == 'prod' ? 'left' : 'right' ?>" aria-hidden="true"></i>
			</a>
		<? endif; ?>
	</td>
</tr>