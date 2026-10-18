<?php
/*
 * Plugin Name: Archive redirect
 * Description: Redirects users trying to access an article of a specific publishing date to the archive website.
 * Author: Noah Pinales
 */

function ancile_archive_settings_init()
{
    register_setting("ancile_archive", "ancile_archive_options");

    // Register a new section in the "ancile" page.
    add_settings_section(
        "ancile_archive_section_general",
        __("General Settings.", "ancile_archive"),
        "ancile_archive_section_general_callback",
        "ancile_archive",
    );

    // Register a new field in the "ancile_archive_section_general" section, inside the "ancile" page.
    add_settings_field(
        "ancile_archive_field_enable",
        __("Enable", "ancile_archive"),
        "ancile_archive_field_enable_cb",
        "ancile_archive",
        "ancile_archive_section_general",
        [
            "label_for" => "ancile_archive_field_enable",
            "class" => "ancile_archive_row",
            "ancile_archive_custom_data" => "custom",
        ],
    );
}
add_action("admin_init", "ancile_archive_settings_init");

/**
 * Custom option and settings:
 *  - callback functions
 */

/**
 * General section callback function.
 *
 * @param array $args  The settings array, defining title, id, callback.
 */
function ancile_archive_section_general_callback($args)
{
    ?>
	<p id="<?php echo esc_attr(
     $args["id"],
 ); ?>"><?php esc_html_e("General Settings.", "ancile_archive"); ?></p>
	<?php
}

/**
 * Enable field callbakc function.
 *
 * WordPress has magic interaction with the following keys: label_for, class.
 * - the "label_for" key value is used for the "for" attribute of the <label>.
 * - the "class" key value is used for the "class" attribute of the <tr> containing the field.
 * Note: you can add custom key value pairs to be used inside your callbacks.
 *
 * @param array $args
 */
function ancile_archive_field_enable_cb($args)
{
    // Get the value of the setting we've registered with register_setting()
    $options = get_option("ancile_archive_options"); ?>
	<select
			id="<?php echo esc_attr($args["label_for"]); ?>"
			data-custom="<?php echo esc_attr($args["ancile_archive_custom_data"]); ?>"
			name="ancile_archive_options[<?php echo esc_attr($args["label_for"]); ?>]">
		<option value="red" <?php echo isset($options[$args["label_for"]])
      ? selected($options[$args["label_for"]], "red", false)
      : ""; ?>>
			<?php esc_html_e("red pill", "ancile_archive"); ?>
		</option>
 		<option value="blue" <?php echo isset($options[$args["label_for"]])
       ? selected($options[$args["label_for"]], "blue", false)
       : ""; ?>>
			<?php esc_html_e("blue pill", "ancile_archive"); ?>
		</option>
	</select>
	<p class="description">
		<?php esc_html_e(
      "You take the blue pill and the story ends. You wake in your bed and you believe whatever you want to believe.",
      "ancile_archive",
  ); ?>
	</p>
	<p class="description">
		<?php esc_html_e(
      "You take the red pill and you stay in Wonderland and I show you how deep the rabbit-hole goes.",
      "ancile_archive",
  ); ?>
	</p>
	<?php
}

/**
 * Add the top level menu page.
 */
function ancile_archive_options_page()
{
    add_submenu_page(
        "tools.php",
        "Ancile Archive",
        "Ancile Archive Options",
        "manage_options",
        "ancile_archive",
        "ancile_archive_options_page_html",
    );
}

/**
 * Register ancile options page to the admin menu hook.
 */
add_action("admin_menu", "ancile_archive_options_page");

/**
 * Top level menu callback function
 */
function ancile_archive_options_page_html()
{
    // check user capabilities
    if (!current_user_can("manage_options")) {
        return;
    }

    // add error/update messages

    // check if the user have submitted the settings
    // WordPress will add the "settings-updated" $_GET parameter to the url
    if (isset($_GET["settings-updated"])) {
        // add settings saved message with the class of "updated"
        add_settings_error(
            "ancile_archive_messages",
            "ancile_archive_message",
            __("Settings Saved", "ancile_archive"),
            "updated",
        );
    }

    // show error/update messages
    settings_errors("ancile_archive_messages");
    ?>
	<div class="wrap">
		<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
		<form action="options.php" method="post">
			<?php
   // output security fields for the registered setting "ancile"
   settings_fields("ancile_archive");
   // output setting sections and their fields
   // (sections are registered for "ancile_archive", each field is registered to a specific section)
   do_settings_sections("ancile_archive");
   // output save settings button
   submit_button("Save Settings");?>
		</form>
	</div>
	<?php
}
