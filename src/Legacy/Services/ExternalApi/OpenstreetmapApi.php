<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Services\ExternalApi;

// https://wiki.openstreetmap.org/wiki/API_v0.6

use App\ExternalApi\Exception;
use App\Legacy\Services\ConfigProvider;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[Autoconfigure]
class OpenstreetmapApi extends ExternalApi
{
    public $name = 'openstreetmap';
    public $format = 'xml';
    public $testQuery = 'user/gpx_files';

    public function __construct(
        #[Autowire(param: 'kernel.project_dir')]
        private readonly string $projectDir,
        ConfigProvider $configProvider,
    ) {
        parent::__construct($this->projectDir, $configProvider);

        $config = $configProvider->getConfig();

        if ($config['openstreetmap'] == false) {
            throw new Exception('OpenStreetMap API is disabled or undefined!');
        }
        $this->apiUrl = $config['openstreetmap']['apiurl'].'/api/0.6/'; // dev and prod is different
        $this->userpwd = $config['openstreetmap']['user:pwd']; // dev and prod is different
        $this->curl_setopt(\CURLOPT_USERPWD, $this->userpwd);
    }

    public function buildQuery(): void
    {
        $this->rawQuery = $this->query;
    }

    public function prepareNewChangeset()
    {
        $changeset = new \SimpleXMLElement('<osm></osm>');
        $changeset->addChild('changeset');
        $tag = $changeset->changeset->addChild('tag');
        $tag->addAttribute('k', 'created_by');
        $tag->addAttribute('v', 'borazslo');
        $tag = $changeset->changeset->addChild('tag');
        $tag->addAttribute('k', 'comment');
        $tag->addAttribute('v', 'Changes made based on miserend.hu\'s users\' experiences.');

        // echo $changeset->asXML();
        return $changeset;
    }
}
