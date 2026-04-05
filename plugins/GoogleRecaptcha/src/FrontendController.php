<?php
declare(strict_types=1);

namespace Plugins\GoogleRecaptcha\src;

use Magepattern\Component\Tool\SmartyTool;
use Plugins\GoogleRecaptcha\db\FrontendDb;

class FrontendController
{
    /**
     * Méthode appelée par le hook 'displayHead' pour injecter le JS
     */
    public static function injectScript(array $params = []): string
    {
        $currentModule = strtolower($_GET['controller'] ?? 'home');
        $db = new FrontendDb();

        if (!$db->isLinkedToModule($currentModule)) {
            return '';
        }

        $keys = $db->getKeys();
        if (empty($keys['site_key'])) {
            return '';
        }

        $smarty = SmartyTool::getInstance('front');
        $file = ROOT_DIR . 'plugins' . DS . 'GoogleRecaptcha' . DS . 'views' . DS . 'front' . DS . 'hooks' . DS . 'script.tpl';

        return $smarty->fetch($file, ['recaptcha_site_key' => $keys['site_key']]);
    }

    /**
     * Méthode métier appelée par d'autres plugins (ex: Contact)
     */
    public function verify(string $moduleName): bool
    {
        $db = new FrontendDb();

        if (!$db->isLinkedToModule($moduleName)) {
            return true;
        }

        $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
        if (empty($recaptchaResponse)) {
            return false;
        }

        $keys = $db->getKeys();
        $secretKey = $keys['secret_key'] ?? '';
        if (empty($secretKey)) {
            return true;
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
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($result === false || $httpCode !== 200) {
            return true;
        }

        $responseData = json_decode($result, true);

        return isset($responseData['success']) && $responseData['success'] === true;
    }
}