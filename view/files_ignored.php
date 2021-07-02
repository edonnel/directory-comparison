<table class="table condensed">
	<thead>
		<tr>
			<td colspan="9999">
				Ignored Files & Directories

                <div class="r">
                    &nbsp;
                    <input id="add_ignore" type="button" value="Add Ignore" />
                </div>
			</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="category" style="width:1px;white-space:nowrap;"></td>
			<td class="category">File</td>
			<td class="category"></td>
            <td class="category"></td>
            <td class="category"></td>
		</tr>
		<? if ($ignored_files) : ?>
			<? foreach ($ignored_files as $file) : ?>
				<tr class="inactive <?= !$file['file_path'] ? 'more' : '' ?>" data-parent="<?= $file['inherited_parent'] ?>">
					<td style="width:1px;white-space:nowrap;">
						<? if ($file['type'] === 'dir') : ?>
							<i class="icon fa fa-folder-open" aria-hidden="true"></i>
						<? elseif ($file['type'] === 'file') : ?>
							<i class="icon fa fa-file" aria-hidden="true"></i>
                        <? else : ?>
                            <i class="icon fa fa-question" aria-hidden="true" title="Ignore Manually Added"></i>
						<? endif; ?>
					</td>
                    <td style="width:1px;white-space:nowrap;">
                        <?= $file['file_path'] ?>

                        <? if ($file['children'] > 0) : ?>
                            <div style="font-weight:600;">+<?= $file['children'] ?> more</div>
                        <? endif; ?>
                    </td>
                    <td></td>
                    <td style="width:1px;white-space:nowrap;">
                        <? if ($file['file_path'] && isset($file['inherit']) && $file['inherit']) : ?>
                            <a href="<?= THIS_URL ?>&act=ignore&file=<?= $file['file_path'] ?>" title="Ignore">
                                <i class="icon action ignore fa fa-ban" aria-hidden="true"></i>
                            </a>
                        <? endif; ?>
                    </td>
					<td style="width:1px;white-space:nowrap;">
                        <? if ($file['file_path'] && isset($file['inherit']) && !$file['inherit']) : ?>
						<a href="<?= THIS_URL ?>&act=unignore&file=<?= $file['file_path'] ?>" title="Unignore">
                            <i class="icon action unignore fa fa-undo" aria-hidden="true"></i>
						</a>
                        <? endif; ?>
					</td>
				</tr>
			<? endforeach; ?>
		<? else : ?>
			<tr>
				<td colspan="9999"><i>No ignored files.</i></td>
			</tr>
		<? endif; ?>
	</tbody>
</table>