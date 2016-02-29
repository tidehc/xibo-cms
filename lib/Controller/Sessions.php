<?php
/*
 * Xibo - Digital Signage - http://www.xibo.org.uk
 * Copyright (C) 2006-2013 Daniel Garner
 *
 * This file is part of Xibo.
 *
 * Xibo is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version. 
 *
 * Xibo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Xibo.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace Xibo\Controller;

use Xibo\Exception\AccessDeniedException;
use Xibo\Factory\SessionFactory;
use Xibo\Helper\Help;
use Xibo\Helper\Sanitize;
use Xibo\Storage\PDOConnect;


class Sessions extends Base
{

    function displayPage()
    {
        $this->getState()->template = 'sessions-page';
    }

    function grid()
    {
        $sessions = SessionFactory::query($this->gridRenderSort(), $this->gridRenderFilter([
            'type' => Sanitize::getString('type'),
            'fromDt' => Sanitize::getString('fromDt')
        ]));

        foreach ($sessions as $row) {
            /* @var \Xibo\Entity\Session $row */

            // Edit
            $row->buttons[] = array(
                'id' => 'sessions_button_logout',
                'url' => $this->urlFor('sessions.confirm.logout.form', ['id' => $row->userId]),
                'text' => __('Logout')
            );
        }

        $this->getState()->template = 'grid';
        $this->getState()->recordsTotal = SessionFactory::countLast();
        $this->getState()->setData($sessions);
    }

    /**
     * Confirm Logout Form
     * @param int $userId
     */
    function confirmLogoutForm($userId)
    {
        if ($this->getUser()->userTypeId != 1)
            throw new AccessDeniedException();

        $this->getState()->template = 'sessions-form-confirm-logout';
        $this->getState()->setData([
            'userId' => $userId,
            'help' => Help::Link('Sessions', 'Logout')
        ]);
    }

    /**
     * Logout
     * @param int $userId
     */
    function logout($userId)
    {
        if ($this->getUser()->userTypeId != 1)
            throw new AccessDeniedException();

        PDOConnect::update('UPDATE `session` SET IsExpired = 1 WHERE userID = :userId ', ['userId' => $userId]);

        // Return
        $this->getState()->hydrate([
            'message' => __('User Logged Out.')
        ]);
    }
}
