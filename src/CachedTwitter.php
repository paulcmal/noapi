<?php

namespace alct\noapi;

use Apix\Cache;

/*
    Fork of https://github.com/alct/noapi
    to cache responses for a while
*/


class CachedTwitter extends \alct\noapi\Twitter
{
	public function __construct($ttl = -1) {
		// $ttl defines how many seconds requests are cached for
		// defaults to -1, that is never cache
		$this->ttl = $ttl;
	}

    /*
        This helper method was introduced so that we don't have to parse the query
        To find out the meta params for noapi, when we know what we want to do already
			$meta = [
				'url' => 'https://twitter.com...', (full-formed URL)
				'query' => '', (query content)
				'type' => 'user', (can be one of user/hashtag/search)
			];
	*/
    public function fromTwitter($meta)
    {
		if ($this->ttl >= 0) {
		  	$cache = new Cache\Apcu;
			$cacheKey = hash('sha256', $meta['url']);
		  	if ( !$page = $cache->load( $cacheKey )) {
				// If we don't find the page in the cache, we need to load it
				if (! $page = NoAPI::curl($meta['url'])) return false;

				$cache->save($page, $cacheKey, [], $this->ttl);
			  }
		} else {
			// if cache TTL is negative, we never cache
			if (! $page = NoAPI::curl($meta['url'])) return false;
		}

        return $this->parse($page, $meta);
    }

    /*
        This method is still compatible with the original noapi
    */
	public function twitter ($query) {
        $meta = $this->queryToMeta($query);
        return $this->fromTwitter($meta);
    }
}
