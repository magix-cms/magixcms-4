<?php
declare(strict_types=1);

namespace Magepattern\Component\Security;

class CryptTool
{
    private static ?string $secretKey = null;
    private static string $contextName = 'Magepattern';
    private static ?string $keyStoragePath = null;

    /**
     * Permet à l'application parente de configurer le comportement de l'outil
     */
    public static function configure(?string $key = null, ?string $storagePath = null, string $contextName = 'Magepattern'): void
    {
        self::$secretKey = $key;
        if ($storagePath !== null) {
            self::$keyStoragePath = rtrim($storagePath, '\\/');
        }
        self::$contextName = $contextName;
    }

    /**
     * Récupère ou génère la clé de chiffrement
     */
    private static function getKey(): string
    {
        // 1. Clé passée dynamiquement par l'application
        if (self::$secretKey !== null) {
            return self::$secretKey;
        }

        // 2. Constante PHP globale
        if (defined('SECRET_KEY')) {
            return constant('SECRET_KEY');
        }

        // 3. Fichier persistant
        // Par défaut, on le place dans le dossier de la classe si l'application n'a rien configuré
        $dir = self::$keyStoragePath ?? __DIR__;

        // 🛡️ SÉCURITÉ : Fichier PHP au lieu de .txt ou .key
        // Empêche la lecture depuis un navigateur web.
        $keyFile = $dir . DIRECTORY_SEPARATOR . 'crypt_secret.php';

        if (file_exists($keyFile)) {
            $key = include $keyFile;
            if (is_string($key) && $key !== '') {
                return self::$secretKey = $key;
            }
        }

        // 4. Génération d'une nouvelle clé
        try {
            $newKey = base64_encode(random_bytes(32));
        } catch (\Throwable $e) {
            $newKey = md5(uniqid(self::$contextName, true));
        }

        $phpCode = "<?php\n// Fichier auto-généré par Magepattern.\n// Ne pas modifier.\nreturn '{$newKey}';\n";

        if (@file_put_contents($keyFile, $phpCode) !== false) {
            @chmod($keyFile, 0600); // Lecture/écriture uniquement pour l'utilisateur système
            return self::$secretKey = $newKey;
        }

        // 5. Fallback ultime
        return md5(__FILE__ . '_' . self::$contextName);
    }

    /**
     * Chiffre une chaîne de caractères (AES-256-CBC)
     */
    public static function encrypt(string $data): string
    {
        if (empty($data)) return '';

        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', self::getKey(), 0, $iv);

        return base64_encode($encrypted . '::' . base64_encode($iv));
    }

    /**
     * Déchiffre une chaîne de caractères
     */
    public static function decrypt(string $data): string
    {
        if (empty($data)) return '';

        $decoded = base64_decode($data, true);

        if ($decoded === false || strpos($decoded, '::') === false) {
            return $data; // Rétro-compatibilité
        }

        list($encrypted_data, $iv_base64) = explode('::', $decoded, 2);
        $iv = base64_decode($iv_base64);

        $decrypted = openssl_decrypt($encrypted_data, 'aes-256-cbc', self::getKey(), 0, $iv);

        return $decrypted !== false ? $decrypted : $data;
    }
}