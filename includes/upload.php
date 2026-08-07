<?php
// includes/upload.php - Single place where user files reach disk.

/**
 * Validates and stores one uploaded file under /uploads.
 *
 * Returns the web-relative path ("uploads/<random>.pdf") on success, or null.
 * $error is set when the upload was attempted but rejected; it stays null when
 * no file was submitted at all.
 */
function save_upload(?array $file, array $allowedExt, int $maxBytes, ?string &$error): ?string
{
    $error = null;

    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE)
            ? 'The selected file is too large.'
            : 'The file could not be uploaded. Please try again.';
        return null;
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        $error = 'Invalid upload.';
        return null;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        $error = 'Unsupported file type. Allowed: ' . implode(', ', $allowedExt) . '.';
        return null;
    }

    if ($file['size'] > $maxBytes) {
        $error = 'File is too large. Maximum size is ' . (int)round($maxBytes / 1048576) . 'MB.';
        return null;
    }

    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
        $error = 'Upload folder could not be created.';
        return null;
    }

    // Refuse to let anything in /uploads be executed, whatever the extension.
    $guard = $uploadDir . '.htaccess';
    if (!file_exists($guard)) {
        file_put_contents($guard, "php_flag engine off\nRemoveHandler .php .phtml .php3 .php4 .php5 .php7 .phar\nAddType text/plain .php .phtml .phar\n");
    }

    // Random name: the original name is never trusted for the path.
    $fileName = bin2hex(random_bytes(16)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $fileName)) {
        $error = 'Failed to store the uploaded file.';
        return null;
    }

    return 'uploads/' . $fileName;
}

/** Removes a stored upload (used when the surrounding save fails). */
function delete_upload(?string $webPath): void
{
    if (!$webPath || strpos($webPath, 'uploads/') !== 0 || strpos($webPath, '..') !== false) {
        return;
    }
    $full = __DIR__ . '/../' . $webPath;
    if (is_file($full)) {
        unlink($full);
    }
}
