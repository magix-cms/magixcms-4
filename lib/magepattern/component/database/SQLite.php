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

class SQLite extends Connector
{
    /**
     * Establish a PDO database connection.
     *
     * @param array $config
     * @return \PDO
     */
    public function connect(array $config)
    {
        $options = $this->options($config);

        // SQLite provides supported for "in-memory" databases, which exist only for
        // lifetime of the request. Any given in-memory database may only have one
        // PDO connection open to it at a time. These are mainly for tests.

        if ($config['database'] == ':memory:') return new \PDO('sqlite::memory:', null, null, $options);

        $path = path('storage').'database'.DS.$config['database'].'.sqlite';

        return new \PDO('sqlite:'.$path, null, null, $options);
    }
}