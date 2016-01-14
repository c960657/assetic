<?php

/*
 * This file is part of the Assetic package, an OpenSky project.
 *
 * (c) 2010-2014 OpenSky Project Inc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Assetic\Factory\Resource;

use Assetic\Asset\AssetInterface;

/**
 * A resource that wraps an asset.
 */
class AssetResource implements ResourceInterface
{
    private $asset;

    /**
     * Constructor.
     *
     * @param AssetInterface $path The path to a file
     */
    public function __construct(AssetInterface $asset)
    {
        $this->asset = $asset;
    }

    public function isFresh($timestamp)
    {
        return $this->asset->getLastModified() <= $timestamp;
    }

    public function getContent()
    {
        $this->asset->load();
        return $this->asset->getContent();
    }

    public function __toString()
    {
        $sourcePath = $this->asset->getSourcePath();
        if ($sourcePath) {
            return $sourcePath;
        } else {
            return spl_object_hash($this->asset);
        }
    }
}
