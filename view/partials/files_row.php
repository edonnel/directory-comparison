<?
	$file       = $changed_file->get_object();
	$file_path  = $file->get_path();
	$file_other = $changed_file->get_object_other();
	$dne        = $changed_file->has_reason('dne');
	$new        = $changed_file->has_reason('new');
	$newer      = $changed_file->has_reason('newer');
	$older      = $changed_file->has_reason('older');
?>

<tr class="<?= $dne ? 'no' : '' ?> <?= $new ? 'new' : '' ?> <?= $newer || $older ? 'diff' : '' ?> <?= $newer ? 'newer' : '' ?> <?= $older ? 'older' : '' ?>">
	<td style="width:1px;white-space:nowrap;">
		<? if ($file->is_dir()) : ?>
			<i class="icon fa fa-folder-open" aria-hidden="true"></i>
		<? elseif ($file->is_file()) : ?>
			<i class="icon fa fa-file" aria-hidden="true"></i>
		<? endif; ?>
	</td>
	<td style="/*width:1px;white-space:nowrap;*/word-break:break-all;"><?= $file_path ?></td>
	<td class="dates <?= $newer || $older ? 'diff-dates' : '' ?>" style="width:1px;white-space:nowrap;">
		<table class="inner-table">
			<tr>
				<td class="date date-stag" style="width:1px;white-space:nowrap;"><?= date('m/d/Y h:i:s', $file->get_date()) ?></td>
				<td style="width:1px;white-space:nowrap;padding:0 12px 0 0;"><?= $newer ? '>' : '' ?> <?= $older ? '<' : '' ?></td>
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
		<? if ($new) : ?>
			<a class="push-link" href="<?= THIS_URL ?>&act=delete&from=<?= $from ?>&file=<?= $file_path ?>" title="Delete From <?= $from == 'prod' ? 'Production' : 'Staging' ?>">
				<i class="icon action delete fa fa-trash" aria-hidden="true"></i>
			</a>
		<? endif; ?>
	</td>
	<td style="padding-left:0;">
		<? if ($new || $older || $newer) : ?>
			<a class="push-link" href="<?= THIS_URL ?>&act=push&from=<?= $from ?>&file=<?= $file_path ?>" title="Push To <?= $from == 'prod' ? 'Staging' : 'Production' ?>">
				<i class="icon action push fa fa-long-arrow-<?= $from == 'prod' ? 'left' : 'right' ?>" aria-hidden="true"></i>
			</a>
		<? endif; ?>
	</td>
</tr>