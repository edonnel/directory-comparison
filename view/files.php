<table class="table <?= $position ?>">
	<thead>
		<tr>
			<td colspan="9999">
				<?= $header ?>

                <? if ($from == 'stag' && $allow_push) : ?>
                <div class="r">
                    <input type="button" onclick="window.location.href = '<?= THIS_URL_FULL ?>&act=push_all_newer';" value="Push New and Recently Modified" title="Pushes only new files and files that have newer modified dates" />
                    &nbsp;
                    <input type="button" onclick="window.location.href = '<?= THIS_URL_FULL ?>&act=push_all';" value="Push" title="Pushes all files. Does not delete files." />
                </div>
                <? endif; ?>

                <? if ($from == 'prod' && $allow_push) : ?>
                    <div class="r">
                        &nbsp;
                        <input type="button" onclick="window.location.href = '<?= THIS_URL_FULL ?>&act=sync_staging';" value="Sync Staging" title="Pushes files and deletes files on staging that do not exist on production." />
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