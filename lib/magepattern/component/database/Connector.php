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

namespace Magepattern\Component\Database;
abstract class Connector
{
    /**
     * @access protected
     *
     * @var string $host SGBD host
     * @var string $dbname SGBD Name
     * @var string $user SGBD User
     * @var string $pass SFBD Pass
     */
    protected static string
        $host = MP_DBHOST,
        $dbname = MP_DBNAME,
        $user = MP_DBUSER,
        $pass = MP_DBPASSWORD;

    /**
     * Establish a PDO database connection.
     *
     * @param array $config
     * @return \PDO
     */
    abstract public function connect(array $config);

    /**
     * Get the PDO connection options for the configuration.
     *
     * Developer specified options will override the default connection options.
     *
     * @param array $config
     * @return array
     */
    protected function options(array $config = []): array
    {
        $options = $config['options'] ?? [];

        return isset($this->options) ? array_merge($this->options, $options) : $options;
    }
}