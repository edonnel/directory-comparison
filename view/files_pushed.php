<table class="table condensed">
	<thead>
		<tr>
			<td colspan="9999">
				Pushed Files
			</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="category" style="width:1px;white-space:nowrap;"></td>
			<td class="category">File</td>
			<td class="category">From</td>
			<td class="category"></td>
			<td class="category">To</td>
			<td class="category"></td>
			<td class="category">Date Pushed</td>
		</tr>
		<? if ($pushed_files) : ?>
			<? foreach ($pushed_files as $file) : ?>
				<tr class="">
					<td style="width:1px;white-space:nowrap;">
						<? if ($file['type'] === 'dir') : ?>
							<i class="icon fa fa-folder-open" aria-hidden="true"></i>
						<? else : ?>
							<i class="icon fa fa-file" aria-hidden="true"></i>
						<? endif; ?>
					</td>
					<td style="width:1px;white-space:nowrap;"><?= $file['file_path'] ?></td>
					<td style="width:1px;white-space:nowrap;"><?= $file['from'] == 'stag' ? 'Staging' : 'Production' ?></td>
					<td style="width:1px;white-space:nowrap;padding-left:0;padding-right:0;"><i class="icon action fa fa-long-arrow-right" aria-hidden="true"></i></td>
					<td style="width:1px;white-space:nowrap;">
                        <? if (!$file['deleted']) : ?>
                            <?= $file['from'] == 'stag' ? 'Production' : 'Staging' ?>
                        <? else : ?>
                            <i class="icon action fa fa-trash" aria-hidden="true"></i>
                        <? endif; ?>
                    </td>
					<td></td>
					<td><?= $file['timestamp'] ?></td>
				</tr>
			<? endforeach; ?>
		<? else : ?>
			<tr>
				<td colspan="9999"><i>No pushed files.</i></td>
			</tr>
		<? endif; ?>
	</tbody>
</table>

<? listing_pag('pushed', $num_pages, $page) ?>