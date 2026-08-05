<?php
// UCID: jp239, Date: 7/13/26
/**
 * Builds a URL rooted at /project.
 *
 * @param string $path Path relative to public_html/project, or a root-relative web path.
 * @return string URL that can be used in href, action, or Location headers.
 */
function project_url(string $path = ""): string
{
    $path = trim($path);

    // If no path is passed, return the project folder itself.
    if ($path === "") {
        return "/public_html/project";
    }

    // If the path already starts at the web root, use it as-is.
    if ($path[0] === "/") {
        return $path;
    }

    return "/public_html/project/$path";
}
?>