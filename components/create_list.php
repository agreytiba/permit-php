<?php
function generateHtmlListFromJson($data)
{
    echo '<ul class="list-disc list-inside">';
    if (is_array($data)) {
        foreach ($data as $item) {
            echo '<li>' . htmlspecialchars($item) . '</li>';
        }
    } elseif (is_string($data)) {
        $dataArray = json_decode($data, true);
        if (is_array($dataArray)) {
            foreach ($dataArray as $item) {
                echo '<li>' . htmlspecialchars($item) . '</li>';
            }
        }
    }
    echo '</ul>';
}
