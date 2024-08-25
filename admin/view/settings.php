<div><h1 class="codecruze-settings-main-title" style="display: inline-block;"><?php _e( 'Global Search Settings',  CODECRUZE_SEARCH_NAME); ?></h1>
</div>
	<h4>You can control all search functionalities here. You can search following items by ID, Title and Name.</h4>
<div class="codecruze-settings-table" id="codecruze-settings">

	<br><br>
	<form action="admin.php?page=codecruze_search_menu" method="post">
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e( 'Search options', CODECRUZE_SEARCH_NAME ); ?></th>
				<td>
					<?php echo CodeCruze_Core::get_searchable_checkbox();?>
				</td>
			</tr>
		</table>
		<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
	</form>
</div>