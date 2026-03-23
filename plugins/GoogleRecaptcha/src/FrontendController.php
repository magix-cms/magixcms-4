<?php
declare(strict_types=1);

namespace Plugins\GoogleRecaptcha\src;

use Plugins\GoogleRecaptcha\db\FrontendDb;

class FrontendController
{
    /**
     * @param string $moduleName Le nom du module appelant (ex: 'contact')
     */
    public function verify(string $moduleName): bool
    {
        $db = new FrontendDb();

        if (!$db->isLinkedToModule($moduleName)) {
            return true; // Non lié, on laisse passer
        }

        $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
        if (empty($recaptchaResponse)) {
            return false; // Lié mais pas de jeton, on bloque
        }

        $keys = $db->getKeys();
        $secretKey = $keys['secret_key'] ?? '';
        if (empty($secretKey)) {
            return true; // Mal configuré en admin, on ne pénalise pas le visiteur
        }

        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = [
            'secret'   => $secretKey,
            'response' => $recaptchaResponse,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Délai d'attente maximum strict (3 secondes)
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);

        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // 🟢 ON NE MET PLUS curl_close($ch) ICI ! PHP 8 s'en charge tout seul.

        if ($result === false || $httpCode !== 200) {
            return true;
        }

        $responseData = json_decode($result, true);

        return isset($responseData['success']) && $responseData['success'] === true;
    }
}