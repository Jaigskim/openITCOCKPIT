<?php
// Copyright (C) <2015>  <it-novum GmbH>
//
// This file is dual licensed
//
// 1.
//	This program is free software: you can redistribute it and/or modify
//	it under the terms of the GNU General Public License as published by
//	the Free Software Foundation, version 3 of the License.
//
//	This program is distributed in the hope that it will be useful,
//	but WITHOUT ANY WARRANTY; without even the implied warranty of
//	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//	GNU General Public License for more details.
//
//	You should have received a copy of the GNU General Public License
//	along with this program.  If not, see <http://www.gnu.org/licenses/>.
//

// 2.
//	If you purchased an openITCOCKPIT Enterprise Edition you can use this file
//	under the terms of the openITCOCKPIT Enterprise Edition license agreement.
//	License agreement and license key will be shipped with the order
//	confirmation.

declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\FilterBookmark;
use App\Model\Table\FilterBookmarksTable;
use Cake\Http\Exception\MethodNotAllowedException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Exception\InternalErrorException;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use itnovum\openITCOCKPIT\Core\UUID;
use itnovum\openITCOCKPIT\Core\ValueObjects\User;

/**
 * Class FilterBookmarksController
 * @package App\Controller
 */
class FilterBookmarksController extends AppController {

    public function index() {
        if (!$this->isApiRequest()) {
            throw new MethodNotAllowedException();
        }
        $plugin = $this->request->getQuery('plugin', null);
        $controller = $this->request->getQuery('controller', null);
        if (!$controller) {
            throw new NotFoundException('Missing type param');
        }
        $action = $this->request->getQuery('action', null);
        if (!$action) {
            throw new NotFoundException('Missing type param');
        }
        $queryFilter = $this->request->getQuery('queryFilter', null);
        /** @var User $user */
        $User = new User($this->getUser());
        /** @var FilterBookmarksTable $FilterBookmarksTable */
        $FilterBookmarksTable = TableRegistry::getTableLocator()->get('FilterBookmarks');
        if ($queryFilter) {
            $QueryBookmark = $FilterBookmarksTable->getFilterByUuid($queryFilter);
        }
        $this->set('bookmark', $QueryBookmark ?? null);
        $filterBookmarks = $FilterBookmarksTable->getFilterByUser($User->getId(), $plugin, $controller, $action);
        $this->set('bookmarks', $filterBookmarks);
        $this->viewBuilder()->setOption('serialize', ['bookmarks', 'bookmark']);
    }

    public function add() {
        if (!$this->isApiRequest() && !$this->request->is('post')) {
            throw new MethodNotAllowedException();
        }
        $data = $this->request->getData();
        $data['filter'] = json_encode($data['filter'] ?? '');

        /** @var User $user */
        $User = new User($this->getUser());
        /** @var FilterBookmarksTable $FilterBookmarksTable */
        $FilterBookmarksTable = TableRegistry::getTableLocator()->get('FilterBookmarks');

        //existing bookmark returns
        if (!empty($data['id']) && !empty($data['uuid']) && !empty($data['user_id']) && $data['user_id'] == $User->getId()) {
            /** @var FilterBookmark $FilterBookmark */
            $FilterBookmark = $FilterBookmarksTable->get($data['id']);
            //if a existing bookmark with the same name, than update the existing bookmark
            if ($FilterBookmark->get('name') == $data['name']) {
                $FilterBookmark = $FilterBookmarksTable->patchEntity($FilterBookmark, $data);
            } //if existing bookmark with new name, then create new bookmark (new id, new uuid) from existing bookmark
            else {
                unset($data['id']);
                $data['uuid'] = UUID::v4();
                $FilterBookmark = $FilterBookmarksTable->newEntity($data);
            }
        } // create complete new bookmark
        else {
            $data['uuid'] = UUID::v4();
            $data['name'] = $this->request->getData('name');
            $data['user_id'] = $User->getId();
            $data['filter'] = json_encode($this->request->getData('filter'));
            /** @var FilterBookmark $FilterBookmark */
            $FilterBookmark = $FilterBookmarksTable->newEntity($data);
        }
        $FilterBookmarksTable->save($FilterBookmark);
        if ($FilterBookmark->hasErrors()) {
            $this->response = $this->response->withStatus(400);
            $this->set('error', $FilterBookmark->getErrors());
            $this->viewBuilder()->setOption('serialize', ['error']);
            return;
        }
        //if bookmark should be default, look for and unset old default
        if ($FilterBookmark->default) {
            $FilterBookmarksTable->updateAll([
                'default' => false
            ], [
                'id !='      => $FilterBookmark->id,
                'user_id'    => $User->getId(),
                'plugin'     => $data['plugin'],
                'controller' => $data['controller'],
                'action'     => $data['action']
            ]);
        }
        $filterBookmarks = $FilterBookmarksTable->getFilterByUser($User->getId(), $data['plugin'], $data['controller'], $data['action']);
        $this->set('bookmarks', $filterBookmarks);
        $this->set('lastBookmarkId', $FilterBookmark->id);
        $this->viewBuilder()->setOption('serialize', ['bookmarks', 'lastBookmarkId']);
    }

    public function delete() {
        if (!$this->isApiRequest()) {
            throw new MethodNotAllowedException();
        }
        /** @var User $User */
        $User = new User($this->getUser());
        $data = $this->request->getData();
        if (empty($data['id'])) {
            throw new NotFoundException('No id to delete');
        }
        if ($User->getId() != $data['user_id']) {
            throw new MethodNotAllowedException('Deletion not allowed, wrong User');
        }
        /** @var FilterBookmarksTable $FilterBookmarksTable */
        $FilterBookmarksTable = TableRegistry::getTableLocator()->get('FilterBookmarks');
        $FilterBookmark = $FilterBookmarksTable->get($data['id']);
        $FilterBookmarksTable->delete($FilterBookmark);
        $filterBookmarks = $FilterBookmarksTable->getFilterByUser($User->getId(), $data['plugin'], $data['controller'], $data['action']);
        $this->set('bookmarks', $filterBookmarks);
        $this->viewBuilder()->setOption('serialize', ['bookmarks']);
    }

    public function directive() {
        // Only ship HTML template

    }

}
