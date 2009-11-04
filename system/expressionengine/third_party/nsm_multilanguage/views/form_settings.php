<?= form_open('C=addons_extensions&M=extension_settings&file=&file=nsm_multilanguage', '',
		array(
			"file" => "nsm_multilanguage",
			"Nsm_multilanguage_ext[check_for_updates]" => 0
		)
	)
?>

<?php if(validation_errors()) : ?>
	<div class="mor alert error">
		<?= validation_errors() ?>
	</div>
<?php endif; ?>

<?php if($message) : ?>
	<div class="mor alert success">
		<p><?php print($message); ?></p>
	</div>
<?php endif; ?>

<div class="nsm tg">
	<div class="info">
		<?= str_replace("{addon_name}", $addon_name, lang('enable_extension_info')); ?>
	</div>
	<table>
		<tbody>
			<tr class="even">
				<th scope="row">
					<?= lang('enable_extension_label', 'enabled') ?>
				</th>
				<td>
					<select name="Nsm_multilanguage_ext[enabled]" id='enabled' class='toggle'>
						<option value="1"<?= $settings['enabled'] ? ' selected="selected"' : '' ?>><?=lang('yes')?></option>
						<option value="0"<?= !$settings['enabled'] ? ' selected="selected"' : '' ?>><?=lang('no')?></option>
					</select>
				</td>
			</tr>
			<tr class="odd">
				<th scope="row">
					<?= lang('nsm_multilanguage_default_language_label')?>
				</th>
				<td<?form_error('Nsm_multilanguage_ext[default_language]') ? ' class="error"' : ''?>>
					<?= form_error('Nsm_multilanguage_ext[languages]'); ?>
					<select name="Nsm_multilanguage_ext[default_language]" id='default_language' class='toggle'>
						<?php
							$count = 0;
						
							foreach ($languages as $language)
							{
								$current_file_is_default_lang = FALSE;
								if (!isset($settings['default_language']) AND $count == 0)
								{
									$current_file_is_default_lang = TRUE;
								}
								else
								{
									$current_file_is_default_lang = (($language['id'] == $settings['default_language']) === TRUE);
								}
								?><option value="<?= $language['id'] ?>"<?= $current_file_is_default_lang === TRUE ? ' selected="selected"' : '' ?>><?= isset($language['name']) ? $language['name'] : $language['id'] ?></option><?php
								
								$count++;
							}
						?>
					</select>
				</td>
			</tr>
			<tr class="even">
				<th scope="row">
					<?= lang('nsm_multilanguage_languages_path_label', 'languages_path_label') ?>
				</th>
				<td>
					<input type="text" name="Nsm_multilanguage_ext[languages_path]" value="<?= $settings['languages_path'] ?>" id="languages_path"/>
				</td>
			</tr>
			
		</tbody>
	</table>
</div>

<input type='submit' value='<?= lang('save_extension_settings'); ?>' />

<?= form_close(); ?>