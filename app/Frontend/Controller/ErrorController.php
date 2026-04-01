<?php
declare(strict_types=1);

namespace App\Frontend\Controller;

class ErrorController extends BaseController
{
    public function run(): void
    {
        // On appelle simplement la méthode globale du parent !
        $this->render404();
    }
}