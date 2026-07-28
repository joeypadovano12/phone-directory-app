<?php
function render_nav()
{
    require(__DIR__ . "/../partials/nav.php");
}
function render_flash_messages()
{
    require(__DIR__ . "/../partials/flash.php");
}
/**
 * Renders the shared document metadata and styles for one page.
 *
 * @param string $title Text displayed in the browser tab.
 */
function render_head(string $title): void
{
    require(__DIR__ . "/../partials/head.php");
}

function render_scripts(): void
{
    require(__DIR__ . "/../partials/scripts.php");
}

/**
 * Builds escaped HTML attributes from a small configuration array.
 *
 * @param array $attributes Attribute names and their values.
 * @return string Escaped attributes ready for an HTML element.
 */
function render_html_attributes(array $attributes): string
{
    $parts = [];

    foreach ($attributes as $name => $value) {
        if (!is_string($name) || !preg_match("/^[A-Za-z_:][A-Za-z0-9:_.-]*$/", $name)) {
            continue;
        }

        if ($value === false || $value === null) {
            continue;
        }

        $safe_name = htmlspecialchars($name);
        if ($value === true) {
            $parts[] = $safe_name;
            continue;
        }

        $safe_value = htmlspecialchars((string) $value);
        $parts[] = "$safe_name=\"$safe_value\"";
    }

    return implode(" ", $parts);
}

/**
 * Renders one form field from its configuration.
 *
 * @param array $field Label, name, type, value, and HTML attributes.
 */
function render_input(array $field): void
{
    require(__DIR__ . "/../partials/input.php");
}

/**
 * Renders one button from its configuration.
 *
 * @param array $button Text, type, variant, and HTML attributes.
 */
function render_button(array $button): void
{
    require(__DIR__ . "/../partials/button.php");
}

/**
 * Renders rows as a table with optional authorized actions.
 *
 * @param array $rows Database or API rows to display.
 * @param array $columns Row keys mapped to visible column headings.
 * @param array $actions Allowed links or forms for each row.
 * @param string $empty_message Message displayed when there are no rows.
 */
function render_table(
    array $rows,
    array $columns,
    array $actions = [],
    string $empty_message = "No records found."
): void {
    require(__DIR__ . "/../partials/table.php");
}

/**
 * Renders the reference guide-list filters used by public and Admin guide pages.
 *
 * @param array $filters Current title, category, game, and race filters.
 * @param int $limit Valid maximum number of guide rows to display.
 */
function render_guide_search(array $filters, int $limit): void
{
    require(__DIR__ . "/../partials/guide_search.php");
}
?>