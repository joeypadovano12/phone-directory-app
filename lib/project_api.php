<?php
// UCID: jp2397, Date: 7/13/26
require_once(__DIR__ . "/api_helper.php");
function fetch_phone_data($search, $source, &$errors = []){
    if ($source === "sample") {
        $result = api_sample_response("project-api-sample.json");
    }
    else {
        $result = api_get(
            "https://gsmarenaparser.p.rapidapi.com/api/values/clean/getdevices/" . rawurlencode($search),
            [],
            ["key_name" => "RAPIDAPI_KEY", "host_name" => "RAPIDAPI_HOST"]
        );
    }
    $decoded = decode_api_response($result, null, $errors);
    $mapped_phones = [];
    if (is_array($decoded)){
        foreach ($decoded as $phone){
            if (is_array($phone)) {
                $mapped_phones[] = [
                    "phone_brand" => $phone["manufacturer"] ?? "Unknown",
                    "phone_model" => $phone["model"] ?? "Unknown",
                    "screen_size" => $phone["displaysize"] ?? "Unknown",
                    "camera_megapixels" => $phone["main_camera_mp"] ?? "Unknown"
                ];
            }
        }
    }
    return $mapped_phones;
}
?>