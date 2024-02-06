<?php

namespace App\Twig\Extensions;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class WebpackCompatibilityExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('encore_entry_link_tags', [$this, 'renderEntryLinkTags'], ['is_safe' => ['html']]),
            new TwigFunction('encore_entry_script_tags', [$this, 'renderEntryScriptTags'], ['is_safe' => ['html']]),
            new TwigFunction('asset', [$this, 'getAssetPath'], ['is_safe' => ['html']]),
        ];
    }

    protected array $entryPointContent;

    protected function entryPointsWithName($name): ?array
    {
        if (!isset($this->entryPointContent)) {
            $this->entryPointContent = json_decode(file_get_contents(__DIR__.'/../../../public/static/entrypoints.json'), true);
        }

        return $this->entryPointContent['entrypoints'][$name] ?? null;
    }

    public function renderEntryLinkTags($entryName): string
    {
        $buffer = [];

        foreach ($this->entryPointsWithName($entryName)['css'] ?? [] as $entryPointPath) {
            $buffer[] = '<link href="'.$entryPointPath.'" rel="stylesheet">';
        }

        return implode("\n", $buffer);
    }

    public function renderEntryScriptTags($entryName): string
    {
        $buffer = [];

        foreach ($this->entryPointsWithName($entryName)['js'] ?? [] as $entryPointPath) {
            $buffer[] = '<script src="'.$entryPointPath.'"></script>';
        }

        return implode("\n", $buffer);
    }

    protected array $manifest;

    public function getAssetPath($assetName): string
    {
        if (!isset($this->manifest)) {
            $this->manifest = json_decode(file_get_contents(__DIR__.'/../../../public/static/manifest.json'), true);
        }

        return $this->manifest[$assetName] ?? 'ERROR404/'.$assetName;
    }
}