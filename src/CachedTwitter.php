<?php

namespace alct\noapi;

use Apix\Cache;

/*
    Fork of https://github.com/alct/noapi
    to cache responses for a while
*/


class CachedTwitter extends \alct\noapi\Twitter
{
	public function __construct($ttl = 0) {
		// $ttl defines how many seconds requests are cached for
		$this->ttl = $ttl;
	}


    /**
     * Download and parse the twitter page corresponding to a query.
     *
     * @see doc/Twitter.md
     * @see Twitter::queryToMeta()
     *
     * @param string $query
     *
     * @return array|bool false on error
     */
    public function twitter($query)
    {
        $meta = $this->queryToMeta($query);

		if ($this->ttl >= 0) {
		  	$cache = new Cache\Apcu;
			$cacheKey = hash('sha256', $meta['url']);
		  	if ( !$page = $cache->load( $cacheKey )) {
				// If we don't find the page in the cache, we need to load it
				if (! $page = NoAPI::curl($meta['url'])) return false;

				$cache->save($data, $cacheKey, $this->ttl);
			  }
		} else {
			// if cache TTL is negative, we never cache
			if (! $page = NoAPI::curl($meta['url'])) return false;
		}

        return $this->parse($page, $meta);
    }
}
