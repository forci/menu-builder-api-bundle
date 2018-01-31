<?php

namespace Forci\Bundle\MenuBuilderApi\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Forci\Bundle\MenuBuilder\Entity\Menu;
use Forci\Bundle\MenuBuilder\Entity\MenuItem;

class MenuController extends Controller {

    public function multiGetAction(Request $request) {
        if ($request->query->get('secret') != $this->container->getParameter('forci_menu_builder_api.secret')) {
            return $this->json([]);
        }

        $ids = $request->query->get('ids');

        if (!is_array($ids)) {
            return $this->json([]);
        }

        $manager = $this->container->get('forci_menu_builder.manager.menus');

        $data = [];

        foreach ($ids as $id) {
            /** @var Menu $menu */
            $menu = $manager->findOneById($id);

            if (!$menu) {
                $data[$id] = [];
                continue;
            }

            if (!$menu->getIsApiVisible()) {
                $data[$id] = [];
                continue;
            }

            $data[$id] = $this->fetchMenu($menu);
        }

        return $this->json($data);
    }

    public function getAction($id, Request $request) {
        if ($request->query->get('secret') != $this->container->getParameter('forci_menu_builder_api.secret')) {
            return $this->json([]);
        }

        $manager = $this->container->get('forci_menu_builder.manager.menus');

        $menu = $manager->findOneById($id);

        if (!$menu) {
            return $this->json([]);
        }

        if (!$menu->getIsApiVisible()) {
            return $this->json([]);
        }

        $data = $this->fetchMenu($menu);

        return $this->json($data);
    }

    protected function fetchMenu(Menu $menu) {
        $data = [
            'name'     => $menu->getName(),
            'modified' => $menu->getDateModified()->format('U')
        ];

        /** @var MenuItem $item */
        foreach ($menu->getItems() as $item) {
            if ($item->getParent()) {
                continue;
            }
            $data['items'][] = $this->fetchItem($item);
        }

        return $data;
    }

    protected function fetchItem(MenuItem $item) {
        $manager = $this->container->get('forci_menu_builder.manager.menus');
        $data = [
            'name'     => $item->getName(),
            'url'      => $manager->generateMenuItemUrl($item),
            'children' => []
        ];
        foreach ($item->getChildren() as $child) {
            $data['children'][] = $this->fetchItem($child);
        }

        return $data;
    }

}