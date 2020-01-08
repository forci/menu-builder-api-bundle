<?php

namespace Forci\Bundle\MenuBuilderApi\Controller;

use Forci\Bundle\MenuBuilder\Entity\Menu;
use Forci\Bundle\MenuBuilder\Entity\MenuItem;
use Forci\Bundle\MenuBuilder\Manager\MenuManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class MenuController extends AbstractController {

    /** @var MenuManager */
    private $menuManager;

    /** @var string */
    private $secret;

    public function __construct(MenuManager $menuManager, string $secret) {
        $this->menuManager = $menuManager;
        $this->secret = $secret;
    }

    public function multiGetAction(Request $request) {
        if ($request->query->get('secret') !== $this->secret) {
            return $this->json([]);
        }

        $ids = $request->query->get('ids');

        if (!is_array($ids)) {
            return $this->json([]);
        }

        $data = [];

        foreach ($ids as $id) {
            /** @var Menu $menu */
            $menu = $this->menuManager->findOneById($id);

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
        if ($request->query->get('secret') !== $this->secret) {
            return $this->json([]);
        }

        $menu = $this->menuManager->findOneById($id);

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
            'name' => $menu->getName(),
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
        $data = [
            'name' => $item->getName(),
            'url' => $this->menuManager->generateMenuItemUrl($item),
            'children' => []
        ];
        foreach ($item->getChildren() as $child) {
            $data['children'][] = $this->fetchItem($child);
        }

        return $data;
    }

}