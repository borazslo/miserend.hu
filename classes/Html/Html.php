<?php

namespace Html;

class Html {

    public $template;
    public $menu = array();
    public $pageTitle = 'VPP - miserend';

    function render() {
        global $user, $twig;
        $this->user = $user;
        $this->loadMenu();
        $this->campaign = updatesCampaign();
        if ($this->user->loggedin AND ! $this->user->checkRole('miserend')) {
            $this->mychurches = feltoltes_block();
        }
        if ($this->user->checkRole('"any"')) {
            $this->chat = chat_load();
        }
                        
        $this->messages = getMessages();
        
        $this->html = $twig->render($this->template, (array) $this);
    }

    function loadMenu() {
        if ($this->user->checkRole("'any'")) {
            $this->loadAdminMenu();
        }
        if (count($user->responsible['diocese']) > 0 AND ! $user->checkRole('miserend')) {
            $this->loadResponsibleMenu();
        }
    }

    function loadAdminMenu() {
        $adminmenuitems = [
            ['title' => 'Miserend', 'url' => '?m_id=27', 'permission' => 'miserend', 'mid' => 27,
                'items' => [
                    ['title' => 'új templom', 'url' => '?m_id=27&m_op=addtemplom', 'permission' => ''],
                    ['title' => 'módosítás', 'url' => '?m_id=27&m_op=modtemplom', 'permission' => ''],
                    ['title' => 'egyházmegyei lista', 'url' => '?m_id=27&m_op=ehmlista', 'permission' => 'miserend'],
                    ['title' => 'kifejezések és dátumok', 'url' => '?m_id=27&m_op=events', 'permission' => 'miserend'],
                ]
            ],
            ['title' => 'Felhasználók', 'url' => '?m_id=21', 'permission' => 'user', 'mid' => 21,
                'items' => [
                    ['title' => 'új felhasználó', 'url' => '?m_id=28&m_op=edit', 'permission' => 'user'],
                    ['title' => 'módosítás', 'url' => '?m_id=28&m_op=list', 'permission' => 'user'],
                ]
            ],
        ];
        $adminmenuitems = $this->clearMenu($adminmenuitems);
        $this->menu = array_merge($this->menu, $adminmenuitems);
    }

    function loadResponsibleMenu() {
        $diocesemenuitems = [
            ['title' => 'Templomok', 'url' => '?m_id=27', 'mid' => 27,
                'items' => [
                    ['title' => 'módosítás', 'url' => '?m_id=27&m_op=modtemplom', 'permission' => ''],
                ]
            ],
        ];
        $this->menu = array_merge($this->menu, $diocesemenuitems);
    }

    function clearMenu($menuitems) {
        foreach ($menuitems as $key => $item) {
            if (isset($item['permission']) AND ! $this->user->checkRole($item['permission'])) {
                unset($menuitems[$key]);
            } else {
                if (isset($item['items']) AND is_array($item['items'])) {
                    foreach ($item ['items'] as $k => $i) {
                        if (isset($i['permission']) AND ! $this->user->checkRole($i['permission'])) {
                            unset($menuitems[$key][$k]);
                        } else {
                            
                        }
                    }
                }
            }
        }
        return $menuitems;
    }

    function setTitle($title) {
        $this->pageTitle = $title . " | Miserend";
    }

}
