<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Redirect any request that carries only tracking / marketing query parameters
 * (trk, utm_*, fbclid, gclid, msclkid, …) to the same URL without those params.
 *
 * If a request mixes tracking params with legitimate app params the tracking
 * ones are stripped and only the meaningful params are preserved.
 */
class StripTrackingParamsFilter implements FilterInterface
{
    /** Parameters that are pure tracking noise and carry no page meaning. */
    private const TRACKING_PARAMS = [
        // LinkedIn
        'trk', 'trkInfo',
        // Google Ads / Analytics
        'gclid', 'gclsrc', 'dclid',
        // Meta / Facebook
        'fbclid', 'fb_action_ids', 'fb_action_types', 'fb_source',
        // Microsoft / Bing Ads
        'msclkid',
        // UTM (standard analytics campaign tags)
        'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'utm_id',
        // Misc
        'ref', 'mc_cid', 'mc_eid', '_ga',
    ];

    public function before(RequestInterface $request, $arguments = null)
    {
        $queryParams = $request->getGet();

        if (empty($queryParams)) {
            return;
        }

        $trackingKeys = array_map('strtolower', self::TRACKING_PARAMS);
        $dirty = false;

        foreach (array_keys($queryParams) as $key) {
            if (in_array(strtolower((string) $key), $trackingKeys, true)) {
                unset($queryParams[$key]);
                $dirty = true;
            }
        }

        if (!$dirty) {
            return;
        }

        // Build the clean URL
        $uri = clone $request->getUri();
        $uri->setQuery(http_build_query($queryParams));

        $cleanUrl = (string) $uri;

        return redirect()->to($cleanUrl, 301);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing to do after the response.
    }
}

