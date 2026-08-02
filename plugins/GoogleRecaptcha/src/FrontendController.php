<?php
declare(strict_types=1);

namespace Plugins\GoogleRecaptcha\src;

use Magepattern\Component\Tool\SmartyTool;
use Plugins\GoogleRecaptcha\db\FrontendDb;

class FrontendController
{
    /** @var callable[] */
    private static array $injectConditions = [];
    /**
     * Permet à d'autres modules d'enregistrer une condition dynamique.
     * Le callback doit retourner un booléen (false pour bloquer l'injection).
     */
    public static function addInjectCondition(callable $callback): void
    {
        self::$injectConditions[] = $callback;
    }

    public static function injectScript(array $params = []): string
    {
        $currentModule = strtolower($_GET['controller'] ?? 'home');
        $db = new FrontendDb();

        if (!$db->isLinkedToModule($currentModule)) {
            return '';
        }

        // --- SYSTÈME DE VETO DYNAMIQUE ---
        // On vérifie si un autre plugin a demandé d'annuler l'injection sur cette page
        foreach (self::$injectConditions as $condition) {
            if ($condition($currentModule) === false) {
                return '';
            }
        }

        $keys = $db->getKeys();
        if (empty($keys['site_key'])) {
            return '';
        }

        $file = ROOT_DIR . 'plugins' . DS . 'GoogleRecaptcha' . DS . 'views' . DS . 'front' . DS . 'hooks' . DS . 'script.tpl';

        if (!file_exists($file)) {
            return '';
        }

        $smarty = SmartyTool::getInstance('front');
        $smarty->assign('recaptcha_site_key', $keys['site_key']);

        return $smarty->fetch($file);
    }

    public function verify(string $moduleName): bool
    {
        $db = new FrontendDb();

        if (!$db->isLinkedToModule($moduleName)) {
            return true; // Module non protégé, on laisse passer
        }

        $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
        if (empty($recaptchaResponse)) {
            return false;
        }

        $keys = $db->getKeys();
        $secretKey = $keys['secret_key'] ?? '';
        if (empty($secretKey)) {
            return false;
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
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($result === false || $httpCode !== 200) {
            return false;
        }

        $responseData = json_decode($result, true);

        if (!isset($responseData['success']) || $responseData['success'] !== true) {
            return false;
        }

        // Si Google renvoie un score (v3), on bloque tout ce qui est en dessous de 0.5 (les bots)
        if (isset($responseData['score']) && $responseData['score'] < 0.5) {
            return false;
        }

        return true;
    }
}