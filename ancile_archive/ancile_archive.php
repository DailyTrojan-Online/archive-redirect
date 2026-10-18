<?php
/*
 * Plugin Name: Ancile Archive redirect
 * Description: Redirects users trying to access an article of a specific publishing date to the archive website.
 * Author: Noah Pinales
 */

function ancile_archive_settings_init()
{
    register_setting("ancile_archive", "ancile_archive_options", [
        "sanitize_callback" => "ancile_archive_sanitize_options",
    ]);

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
    add_settings_field(
        "ancile_archive_field_archive_before",
        __("Archive Before", "ancile_archive"),
        "ancile_archive_field_archive_before_cb",
        "ancile_archive",
        "ancile_archive_section_general",
        [
            "label_for" => "ancile_archive_field_archive_before",
            "class" => "ancile_archive_row",
            "ancile_archive_custom_data" => "custom",
        ],
    );
    add_settings_field(
        "ancile_archive_field_base_url",
        __("Base URL", "ancile_archive"),
        "ancile_archive_field_base_url_cb",
        "ancile_archive",
        "ancile_archive_section_general",
        [
            "label_for" => "ancile_archive_field_base_url",
            "class" => "ancile_archive_row",
            "ancile_archive_custom_data" => "custom",
        ],
    );
}
add_action("admin_init", "ancile_archive_settings_init");

/**
 * Sanitize the ancile_archive_options array before it is saved.
 *
 * @param mixed $input The raw submitted option value.
 * @return array
 */
function ancile_archive_sanitize_options($input)
{
    $input = is_array($input) ? $input : [];

    $sanitized = [];

    $sanitized["ancile_archive_field_enable"] =
        isset($input["ancile_archive_field_enable"]) &&
        "1" === $input["ancile_archive_field_enable"]
            ? "1"
            : "0";

    $archive_before = isset($input["ancile_archive_field_archive_before"])
        ? (string) $input["ancile_archive_field_archive_before"]
        : "";
    $date = DateTime::createFromFormat("Y-m-d", $archive_before);
    $sanitized["ancile_archive_field_archive_before"] =
        $date && $date->format("Y-m-d") === $archive_before
            ? $archive_before
            : "";

    $base_url = isset($input["ancile_archive_field_base_url"])
        ? untrailingslashit(
            trim((string) $input["ancile_archive_field_base_url"]),
        )
        : "";
    if (
        "" !== $base_url &&
        filter_var($base_url, FILTER_VALIDATE_URL) &&
        in_array(
            wp_parse_url($base_url, PHP_URL_SCHEME),
            ["http", "https"],
            true,
        )
    ) {
        $sanitized["ancile_archive_field_base_url"] = $base_url;
    } else {
        $sanitized["ancile_archive_field_base_url"] = "";

        if ("" !== $base_url) {
            add_settings_error(
                "ancile_archive_messages",
                "ancile_archive_invalid_base_url",
                __(
                    "Base URL must be a valid http:// or https:// URL. The value was cleared.",
                    "ancile_archive",
                ),
                "error",
            );
        }
    }

    return $sanitized;
}

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
 ); ?>"><?php esc_html_e("Control the archive.", "ancile_archive"); ?></p>
	<?php
}

function ancile_archive_field_archive_before_cb($args)
{
    $options = get_option("ancile_archive_options", []);
    $value = isset($options["ancile_archive_field_archive_before"])
        ? $options["ancile_archive_field_archive_before"]
        : "";
    echo '<input type="date" name="ancile_archive_options[ancile_archive_field_archive_before]" id="ancile_archive_field_archive_before" value="' .
        esc_attr($value) .
        '" class="regular-text" />';
}

function ancile_archive_field_base_url_cb($args)
{
    $options = get_option("ancile_archive_options", []);
    $value = isset($options["ancile_archive_field_base_url"])
        ? $options["ancile_archive_field_base_url"]
        : "";
    echo '<input type="url" name="ancile_archive_options[ancile_archive_field_base_url]" id="ancile_archive_field_base_url" value="' .
        esc_attr($value) .
        '" class="regular-text" />
        <p class="description">' .
        esc_html__(
            "Redirects to https://[base url]/[slug of article]. Do NOT include a trailing slash.",
            "ancile_archive",
        ) .
        "</p>";
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
    $options = get_option("ancile_archive_options", []);
    $enabled = isset($options[$args["label_for"]])
        ? $options[$args["label_for"]]
        : "";
    ?>
	<input type="hidden" name="ancile_archive_options[<?php echo esc_attr(
     $args["label_for"],
 ); ?>]" value="0" />
	<input
			type="checkbox"
			id="<?php echo esc_attr($args["label_for"]); ?>"
			data-custom="<?php echo esc_attr($args["ancile_archive_custom_data"]); ?>"
			name="ancile_archive_options[<?php echo esc_attr($args["label_for"]); ?>]"
			value="1"
			<?php checked("1", $enabled); ?> />
	<p class="description">
		<?php esc_html_e(
      "Enabling the archive redirector will automatically redirect every user who visits an article (Post) before the selected date to the archive URL specified.",
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

/*

REDIRECT FUNCTIONALITY

*/

/**
 * Allow wp_safe_redirect() to redirect to the archive domain.
 */
add_filter("allowed_redirect_hosts", function ($hosts) {
    $options = get_option("ancile_archive_options", []);
    $value = isset($options["ancile_archive_field_base_url"])
        ? $options["ancile_archive_field_base_url"]
        : "";
    $archive_host = wp_parse_url($value, PHP_URL_HOST);

    if ($archive_host) {
        $hosts[] = $archive_host;
    }

    return array_unique($hosts);
});

/**
 * Perform redirects before WordPress loads a theme template or runs its
 * normal canonical redirects.
 */
add_action(
    "template_redirect",
    function () {
        // Only redirect normal browser page requests.

        $options = get_option("ancile_archive_options", []);
        $enable_redirect = isset($options["ancile_archive_field_enable"])
            ? $options["ancile_archive_field_enable"]
            : false;
        $base_url = isset($options["ancile_archive_field_base_url"])
            ? $options["ancile_archive_field_base_url"]
            : "";
        $archive_before = isset($options["ancile_archive_field_archive_before"])
            ? $options["ancile_archive_field_archive_before"]
            : "";
        if (
            !$enable_redirect ||
            !$base_url ||
            !$archive_before ||
            is_admin() ||
            wp_doing_ajax() ||
            wp_doing_cron() ||
            is_feed() ||
            is_preview() ||
            !in_array(
                $_SERVER["REQUEST_METHOD"] ?? "GET",
                ["GET", "HEAD"],
                true,
            )
        ) {
            return;
        }

        $slug = "";

        /*
         * Case 1: An existing regular post published before January 1, 2016.
         */
        if (is_singular("post")) {
            $post = get_queried_object();

            if (
                $post instanceof WP_Post &&
                $post->post_date < $archive_before
            ) {
                $slug = $post->post_name;
            }
        }

        /*
         * Case 2: A missing URL using:
         *
         * /year/month/day/slug/
         *
         * Only dated, post-shaped 404 URLs are redirected. This avoids
         * redirecting missing images, scripts, pages, API endpoints, etc.
         */
        if (!$slug && is_404()) {
            $request_path = wp_parse_url(
                wp_unslash($_SERVER["REQUEST_URI"] ?? ""),
                PHP_URL_PATH,
            );

            if (
                is_string($request_path) &&
                preg_match(
                    '~/(\d{4})/(0[1-9]|1[0-2])/(0[1-9]|[12]\d|3[01])/([^/]+)/?$~',
                    $request_path,
                    $matches,
                )
            ) {
                $url_date =
                    $matches[1] . "-" . $matches[2] . "-" . $matches[3];

                // Only redirect dates the archive actually covers.
                if ($url_date < $archive_before) {
                    $slug = sanitize_title(rawurldecode($matches[4]));
                }
            }
        }

        if (!$slug) {
            return;
        }

        $destination =
            trailingslashit($base_url) . rawurlencode($slug) . "/";

        /*
         * Start with 302 while testing. Change to 301 once you have verified
         * that every redirect is correct.
         */
        if (wp_safe_redirect($destination, 302, "Archive Post Redirect")) {
            exit();
        }
    },
    1,
);
