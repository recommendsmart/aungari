@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../geeks4change/composer-pin/composer-pin-apply
bash "%BIN_TARGET%" %*
