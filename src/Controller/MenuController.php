<?php

/*
 * This file is part of the ForciMenuBuilderApiBundle package.
 *
 * (c) Martin Kirilov <wucdbm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Forci\Bundle\MenuBuilderApi\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Forci\Bundle\MenuBuilderBundle\Entity\MenuItem;

class MenuController extends Controller {

    public function getAction($id) {
        $manager = $this->container->get('forci_menu_builder.manager.menus');
        $menu = $manager->findOneById($id);

        $data = [
            'name' => $menu->getName()
        ];

        /** @var MenuItem $item */
        foreach ($menu->getItems() as $item) {
            if ($item->getParent()) {
                continue;
            }
            $data['items'][] = $this->fetchItem($item);
        }

        return $this->json($data);
    }

    protected function fetchItem(MenuItem $item) {
        $manager = $this->container->get('forci_menu_builder.manager.menus');
        $data = [
            'name' => $item->getName(),
            'url' => $manager->generateMenuItemUrl($item),
            'children' => []
        ];
        foreach ($item->getChildren() as $child) {
            $data['children'][] = $this->fetchItem($child);
        }

        return $data;
    }
}
