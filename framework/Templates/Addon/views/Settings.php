<?php
echo ee('CP/Alert')->get('settings-save');
if (isset($form)) {
    echo ee('View')->make('ee:_shared/form')->render($form);
}