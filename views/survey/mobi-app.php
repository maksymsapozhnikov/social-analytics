<?php
/**
 * @var string $info
 */
?>
window.rmsApp = window.rmsApp || {};
var optin = JSON.parse(base64.decode('<?= $info ?>'))
if (window.rmsApp.saveData === undefined) {
    window.rmsApp.optin = optin;
} else {
    window.rmsApp.saveData && window.rmsApp.saveData(optin);
}
