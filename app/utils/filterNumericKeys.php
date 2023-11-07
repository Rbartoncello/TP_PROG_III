<?php

function filterNumericKeys($item) {
    return array_filter($item, function ($key) {
        return !is_numeric($key);
    }, ARRAY_FILTER_USE_KEY);
}