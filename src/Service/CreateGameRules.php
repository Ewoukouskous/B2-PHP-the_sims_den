<?php

class CreateGameRules {
    public const ALLOWED_EXTENSIONS = ['png', 'jpg', 'jpeg', 'webp'];
    public const MAX_SIZE_PER_FILE = 10 * 1024 * 1024;
    public const MAX_TOTAL_UPLOAD_SIZE = 50 * 1024 * 1024;
    public static function normalizePegiLabelToFileName(string $label): string {
        $normalized = trim(strtolower($label));

        // Remove common French accents to match image naming convention.
        $accentMap = [
            'à' => 'a', 'â' => 'a', 'ä' => 'a',
            'ç' => 'c',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'î' => 'i', 'ï' => 'i',
            'ô' => 'o', 'ö' => 'o',
            'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ÿ' => 'y',
            'œ' => 'oe', 'æ' => 'ae'
        ];

        $normalized = strtr($normalized, $accentMap);
        $normalized = preg_replace('/[^a-z0-9]+/', '-', $normalized) ?? '';
        $normalized = trim($normalized, '-');

        return ($normalized !== '' ? $normalized : 'descriptor') . '.jpg';
    }

    public static function normalizeMultipleUploadField($fileField): array {
        if (!is_array($fileField) || !isset($fileField['name']) || !is_array($fileField['name'])) {
            return [];
        }

        $names = $fileField['name'] ?? [];
        $types = $fileField['type'] ?? [];
        $tmpNames = $fileField['tmp_name'] ?? [];
        $errors = $fileField['error'] ?? [];
        $sizes = $fileField['size'] ?? [];

        $result = [];
        $count = count($names);

        for ($index = 0; $index < $count; $index++) {
            $result[] = [
                'name' => (string)($names[$index] ?? ''),
                'type' => (string)($types[$index] ?? ''),
                'tmp_name' => (string)($tmpNames[$index] ?? ''),
                'error' => (int)($errors[$index] ?? UPLOAD_ERR_NO_FILE),
                'size' => (int)($sizes[$index] ?? 0)
            ];
        }

        return $result;
    }

    public static function isUploadedFileMeaningful($file): bool {
        if (!is_array($file)) {
            return false;
        }

        $name = trim((string)($file['name'] ?? ''));
        $error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
        $size = (int)($file['size'] ?? 0);

        return $name !== '' && $error !== UPLOAD_ERR_NO_FILE && $size > 0;
    }

    public static function mapUploadErrorMessage(int $errorCode): string {
        return match ($errorCode) {
            UPLOAD_ERR_OK => 'televersement reussi.',
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'fichier trop volumineux.',
            UPLOAD_ERR_PARTIAL => 'televersement partiel.',
            UPLOAD_ERR_NO_FILE => 'aucun fichier selectionne.',
            UPLOAD_ERR_NO_TMP_DIR => 'dossier temporaire introuvable.',
            UPLOAD_ERR_CANT_WRITE => 'echec d\'ecriture sur le disque.',
            UPLOAD_ERR_EXTENSION => 'televersement bloque par une extension PHP.',
            default => 'erreur de televersement inconnue.'
        };
    }

    public static function extensionFromFileName(string $fileName): string {
        $extension = strtolower((string)pathinfo($fileName, PATHINFO_EXTENSION));
        return trim($extension);
    }
}

