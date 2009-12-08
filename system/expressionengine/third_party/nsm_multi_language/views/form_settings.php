<?php
/**
 * View for Control Panel Settings Form
 * This file is responsible for displaying the user-configurable settings for the NSM Multi Language extension in the ExpressionEngine control panel.
 * 
 * @package Nsm_multi_language
 * @version 2.0.0
 * @author Leevi Graham  <http://newism.com.au>
 * @copyright Copyright (c) 2007-2009 Newism
 * @license Commercial - please see LICENSE file included with this distribution
 **/

?>

<?= form_open(
		'C=addons_extensions&M=extension_settings&file=&file=nsm_multi_language',
		'',
		array("file" => "nsm_multi_language")
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
					<?= lang('nsm_multi_language_default_language_label')?>
				</th>
				<td<?= form_error('Nsm_multi_language_ext[default_language]') ? ' class="error"' : ''?>>
					<?= form_error('Nsm_multi_language_ext[languages]'); ?>
					<select name="Nsm_multi_language_ext[default_language]" id='default_language' class='toggle'>
						<?php
							$count = 0;
							foreach ($languages as $language) :
								$selected = ($settings['default_language'] == $language["id"]) ? " selected='selected'" : "";
						?>
							<option value="<?= $language['id'] ?>"<?= $selected; ?>><?= isset($language['name']) ? $language['name'] : $language['id'] ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr class="odd">
				<th scope="row">
					<?= lang('nsm_multi_language_languages_path_label', 'languages_path_label') ?>
				</th>
				<td>
					<input type="text" name="Nsm_multi_language_ext[languages_path]" value="<?= $settings['languages_path'] ?>" id="languages_path" style="width:500px" />
				</td>
			</tr>

		</tbody>
	</table>
</div>

<input type='submit' value='<?= lang('save_extension_settings'); ?>' />

<?= form_close(); ?>