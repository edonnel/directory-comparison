<?
    namespace directory_comparison;

    $conn   = get_conn();
	$stmt   = "SELECT `notes` FROM `staging_files_settings` WHERE `id` = 1 LIMIT 1";
	$query  = $conn->query($stmt);
	$notes  = $query->fetch_assoc()['notes'];
?>

<div class="notes-section">
	<label for="notes">Notes:</label>
	<textarea id="notes"><?= $notes ?></textarea>
	<input id="notes_submit" type="button" value="Save Notes" />
</div>