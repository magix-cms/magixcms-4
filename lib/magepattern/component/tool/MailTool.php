<?php

/*
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Mage Pattern.
# The toolkit PHP for developer
# Copyright (C) 2012 - 2026 Gerits Aurelien contact[at]gerits-aurelien[dot]be
#
# OFFICIAL TEAM MAGE PATTERN:
#
#   * Gerits Aurelien (Author - Developer) contact[at]gerits-aurelien[dot]be
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.

# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Redistributions of source code must retain the above copyright notice,
# this list of conditions and the following disclaimer.
#
# Redistributions in binary form must reproduce the above copyright notice,
# this list of conditions and the following disclaimer in the documentation
# and/or other materials provided with the distribution.
#
# DISCLAIMER
*/

namespace Magepattern\Component\Tool;

use Magepattern\Bootstrap;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Magepattern\Component\Debug\Logger;
use Magepattern\Component\Tool\FormTool;

/**
 * MailTool - Moteur d'envoi d'emails pour Magepattern 3
 * Remplace l'ancienne implémentation SwiftMailer par Symfony Mailer.
 */
class MailTool
{
    private Mailer $mailer;

    /**
     * @param string $dsnOrType DSN (ex: smtp://...) ou type ('mail'/'smtp')
     * @param array $options Options pour compatibilité ascendante
     */
    public function __construct(string $dsnOrType, array $options = [])
    {
        // Chargement de l'autoloader externe via le Bootstrap de Magepattern
        Bootstrap::getInstance()->load('mailer');

        $dsn = (strpos($dsnOrType, '://') !== false)
            ? $dsnOrType
            : $this->buildDsnFromLegacy($dsnOrType, $options);

        $transport = Transport::fromDsn($dsn);
        $this->mailer = new Mailer($transport);
    }

    /**
     * Convertit les anciens paramètres SwiftMailer en chaîne DSN moderne
     */
    protected function buildDsnFromLegacy(string $type, array $options): string
    {
        if ($type === 'mail') return 'sendmail://default';

        $user = urlencode($options['setUsername'] ?? '');
        $pass = urlencode($options['setPassword'] ?? '');
        $host = $options['setHost'] ?? 'localhost';
        $port = $options['setPort'] ?? 25;
        $encryption = $options['setEncryption'] ?? null;

        $proto = ($encryption === 'ssl') ? 'smtps' : 'smtp';
        return "{$proto}://{$user}:{$pass}@{$host}:{$port}";
    }

    /**
     * Initialise un objet Email
     */
    public function createMessage(string $subject, string $from, string $reply, array $recipients, string $body, string $readReceipt = ''): Email
    {
        $email = (new Email())
            ->subject($subject)
            ->from($from)
            ->replyTo($reply)
            ->html($body)
            ->text(FormTool::tagClean($body));

        foreach ($recipients as $address => $name) {
            if (is_int($address)) {
                $email->addTo($name);
            } else {
                $email->addTo(new Address($address, $name));
            }
        }

        if ($readReceipt) {
            $email->getHeaders()->addMailboxHeader('Disposition-Notification-To', $readReceipt);
        }

        return $email;
    }

    /**
     * Ajout de pièces jointes (Optionnel)
     */
    public function attachFiles(Email $email, array $files): void
    {
        foreach ($files as $file) {
            if (is_array($file) && isset($file['path'])) {
                $email->attachFromPath($file['path'], $file['name'] ?? null, $file['mime'] ?? null);
            } else if (is_string($file) && file_exists($file)) {
                $email->attachFromPath($file);
            }
        }
    }

    /**
     * Exécute l'envoi
     */
    public function send(Email $email): bool
    {
        try {
            $this->mailer->send($email);
            return true;
        } catch (\Exception $e) {
            Logger::getInstance()->log($e, "php", "error", Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);
            return false;
        }
    }

    /**
     * Génère un mail via un template Smarty et l'envoie avec options (pièces jointes).
     *
     * @param string $context    Contexte Smarty (ex: 'frontend')
     * @param string $template   Fichier .tpl
     * @param array  $data       Variables Smarty
     * @param string $subject    Sujet
     * @param string $from       Email expéditeur
     * @param array  $to         Destinataires [email => Nom]
     * @param array  $files      [Optionnel] Liste des pièces jointes
     * @return bool
     *
     * @Example :
     *
     * $mailer = new MailTool('smtp://localhost:1025');
     *
     * $mailer->sendTemplate(
     * 'frontend',
     * 'emails/ticket.tpl',
     * ['event_name' => 'Concert 2026'],
     * "Vos tickets pour le concert",
     * "billetterie@site.be",
     * ["client@test.be" => "Aurélien"],
     * [
     * '/data/pdf/ticket_A12.pdf', // Pièce jointe simple
     * ['path' => '/data/docs/plan_acces.pdf', 'name' => 'Plan-Acces.pdf'] // Pièce jointe renommée
     * ]
     * );
     */
    public function sendTemplate(
        string $context,
        string $template,
        array $data,
        string $subject,
        string $from,
        array $to,
        array $files = []
    ): bool {
        try {
            // 1. Rendu HTML via Smarty
            $smarty = SmartyTool::getInstance($context);
            $smarty->assign($data);
            $htmlBody = $smarty->fetch($template);

            // 2. Création du message de base
            $email = $this->createMessage($subject, $from, $from, $to, $htmlBody);

            // 3. Ajout des pièces jointes si présentes
            if (!empty($files)) {
                $this->attachFiles($email, $files);
            }

            // 4. Envoi
            return $this->send($email);

        } catch (\Exception $e) {
            Logger::getInstance()->log($e,"php", "error", Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);
            return false;
        }
    }
}